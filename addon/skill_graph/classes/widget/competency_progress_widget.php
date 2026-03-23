<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SkillGraph progress widget class contains the layout information and generate the data for widget.
 *
 * @package    dashaddon_skill_graph
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_skill_graph\widget;

use block_dash\local\widget\abstract_widget;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_source\form\preferences_form;
use dashaddon_skill_graph\data_grid\filter\competency_condition;


/**
 * SkillGraph widget class contains the layout information and generate the data for widget.
 */
class competency_progress_widget extends abstract_widget {
    /**
     * List of available competencies for the selected framework.
     *
     * @var array
     */
    protected $competencies = [];

    /**
     * Dataset build for chart using the $competencydata.
     *
     * @var array
     */
    protected $dataset = [];

    /**
     * Maintain the last index value of the competency row to insert the child competencies.
     *
     * @var array
     */
    protected $datacount = [];

    /**
     * Check the datasource is widget.
     *
     * @return bool
     */
    public function is_widget() {
        return true;
    }

    /**
     * Get template file name to renderer.
     */
    public function get_mustache_template_name() {
        return 'dashaddon_skill_graph/competency_progress';
    }

    /**
     * Check the widget support uses the query method to build the widget.
     *
     * @return bool
     */
    public function supports_query() {
        return false;
    }

    /**
     * Layout class widget will use to render the widget content.
     *
     * @return \abstract_layout
     */
    public function layout() {
        return new competency_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'competency_progress',
            'layout' => 'competency',
        ];
        return $preferences;
    }

    /**
     * Widget data count.
     *
     * @return void
     */
    public function widget_data_count() {
        return $this->data['datasetcount'] ?? 0;
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {

        // Find the competency framework to generate data.
        $filter = $this->get_filter_collection()->get_filter('competencyframeworks')->get_preferences();
        if (isset($filter['enabled']) && $filter['enabled']) {
            $frameworkid = $filter['competencyframework'];
        }

        if (!isset($frameworkid) || empty($frameworkid)) {
            return false;
        }

        // Fetch the list of main competencies with children list.
        $this->generate_competency_framework_tree($frameworkid);

        // Find the proficiency progress for the competency framework.
        $totalproficientcount = array_sum(array_column($this->dataset, 'progress'));
        $totalchildcount = count($this->dataset);
        // Total progress.
        $totalprogress = round(($totalproficientcount / $totalchildcount));
        $totalprogress = $totalprogress > 100 ? 100 : $totalprogress;

        // Calculate the dasharray level for the svg progress bar circle.
        $dasharray = 500;
        $progressarray = ($dasharray * $totalprogress) / 100; // Find the percentage of the progress for dasharray.

        $competenciesdata = [
            'contextid' => $this->get_block_instance()->context->id,
            'uniqueid' => $this->get_block_instance()->instance->id,
            'dataset' => array_values($this->dataset),
            'datasetcount' => count($this->dataset),
            'frameworkprogress' => [
                'totalprogress' => $totalprogress,
                'progressarray' => $progressarray,
                'dasharray' => $dasharray,
            ],
            'totalproficient' => $totalproficientcount,
            'totalchildren' => $totalchildcount,
            'progressgraph' => true,
        ];

        // Only loads the data if any of the main competency is available.
        $this->data = (!empty($this->dataset)) ? $competenciesdata : [];

        return $this->data;
    }

    /**
     * Generate the competency frameword tree with competency proficied and grade, name.
     * It converts the normal data to dataset which is used in the chart.
     *
     * @param int $frameworkid ID of the framework.
     * @return void
     */
    public function generate_competency_framework_tree(int $frameworkid) {
        global $USER;

        // Get the list of competencies available in the framework.
        $tree = \core_competency\competency::get_framework_tree($frameworkid);

        // Build the competency data.
        $data = $this->build_competency_data($tree);

        foreach ($data as $key => $maindata) {
            $proficiencycount = 0;
            $childcount = 0;
            // Find the count of child competencies and proficient competencies.
            foreach ($maindata->children as $child) {
                if ($child->proficiency) {
                    $proficiencycount++;
                }
                $childcount++;
            }
            $maindata->proficiencycount = $proficiencycount;
            $maindata->childcount = $childcount;
            if ($childcount) {
                $maindata->progress = round(($proficiencycount / $childcount) * 100);
            } else if ($maindata->proficiency) {
                $maindata->progress = 100;
            } else {
                $maindata->progress = 0;
            }
        }
        $this->dataset = $data;
    }

    /**
     * Build the competency data with image and color, also includes the childrens list.
     *
     * @param stdclass $competencies
     * @return array
     */
    protected function build_competency_data($competencies) {
        global $USER;

        foreach ($competencies as $key => $value) {
            $competency = $value->competency;

            $compid = $competency->get('id'); // Id of the competency.
            $bgimage = $this->get_background_image($compid); // Competency image configured using dash.
            $usercomp = \core_competency\api::get_user_competency($USER->id, $compid); // Get the competency data for the user.

            $data[] = (object) [
                'id' => $competency->get('id'),
                'shortname' => format_string($competency->get('shortname')),
                'idnumber' => $competency->get('idnumber'),
                'proficiency' => $usercomp->get('proficiency'),
                // Color of the competency set in dash.
                'color' => get_config('dashaddon_skill_graph', 'competencycolor_' . $compid) ?: '',
                'competencyimage' => $bgimage ?: '',
                'childrencount' => count($value->children),
                'children' => ($value->children) ? $this->build_competency_data($value->children) : [],
            ];
        }
        return $data ?? [];
    }

    /**
     * Get competency image.
     *
     * @param int $itemid
     * @return stdClass
     */
    protected function get_background_image($itemid) {

        $fs = get_file_storage(); // File storage instance.

        $contextid = \context_system::instance()->id;

        // Fetch list of area files.
        $files = $fs->get_area_files($contextid, 'dashaddon_skill_graph', 'competencyimage', $itemid, '', false);

        if (!empty($files)) {
            // Get the first file.
            $file = reset($files);

            $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                false
            )->out(false);
        }

        return $url ?? '';
    }

    /**
     * Prefence form for widget. We make the fields disable other than the general.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            $mform->addElement('static', 'data_source_name', get_string('datasource', 'block_dash'), $this->get_name());
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }

        $mform->addElement('html', get_string('fieldalert', 'block_dash'), 'fieldalert');
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {
        global $PAGE;

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(
            new competency_condition('competencyframeworks', 'c.competency')
        );

        return $filtercollection;
    }
}
