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
 * SkillGraph widget class contains the layout information and generate the data for widget.
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
class competency_widget extends abstract_widget {
    /**
     * List of available competencies for the selected framework.
     *
     * @var array
     */
    protected $competencies = [];

    /**
     * List of competencies for the course which is available for user.
     *
     * @var array
     */
    protected $coursecompetencies = [];

    /**
     * Competency data like id, shortname, proficiency, grade and other.
     *
     * @var array
     */
    protected $competencydata = [];

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
     * List of competencies names used in the chart as labels.
     *
     * @var array
     */
    protected $competencylabels = [];

    /**
     * Depth of the competency level for the graph.
     */
    const COMPETENCY_DEPTH = 2;

    /**
     * Color of proficient ticks in chart.
     *
     * @var string
     */
    const COLOR_PROFICIENT = 'rgb(235, 0, 247)'; // Proficient.

    /**
     * Color of achived ticks in chart.
     *
     * @var string
     */
    const COLOR_ACHIEVED = 'rgb(74, 233, 149)'; // Achieved.

    /**
     * Color of not achived ticks in chart.
     *
     * @var string
     */
    const COLOR_NOTACHIEVED = 'rgb(200, 248, 223)'; // Not Achieved.

    /**
     * Color of labels in chart.
     *
     * @var string
     */
    const COLOR_FILLSTYLE = 'rgb(0, 0, 0)';

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
        return 'dashaddon_skill_graph/competency';
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
            'datasource' => 'competency',
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
        global $PAGE, $USER;

        $filter = $this->get_filter_collection()->get_filter('competencyframeworks')->get_preferences();
        if (isset($filter['enabled']) && $filter['enabled']) {
            $frameworkid = isset($filter['competencyframework']) ? $filter['competencyframework'] : null;
        }

        if (!isset($frameworkid) || empty($frameworkid)) {
            return false;
        }
        $this->generate_competency_framework_tree($frameworkid);

        $competenciesdata = [
            'contextid' => $this->get_block_instance()->context->id,
            'uniqueid' => $this->get_block_instance()->instance->id,
            'dataset' => json_encode(array_values($this->dataset)),
            'datasetcount' => count($this->dataset),
            'datalabels' => json_encode($this->competencylabels),
            'colors' => [
                'proficient' => self::COLOR_PROFICIENT,
                'achieved' => self::COLOR_ACHIEVED,
                'notachieved' => self::COLOR_NOTACHIEVED,
                'fillStyle' => self::COLOR_FILLSTYLE,
            ],
        ];

        $PAGE->requires->data_for_js('skillGraphData', $competenciesdata['colors']);

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

        $tree = \core_competency\competency::get_framework_tree($frameworkid);
        $list = [];
        $data = $this->build_competency_data($tree);
        if (!empty($this->coursecompetencies)) {
            foreach ($this->coursecompetencies as $courseid => $competencies) {
                $results = \core_competency\user_competency_course::get_multiple($USER->id, $courseid, $competencies);
                // Generate the data for the competency.
                $this->generate_competency_data($results);
            }
            // Contvert the results to the chart data.
            $this->update_results_todata($data);
            $this->get_top_level_competencies($data);
        }
    }

    /**
     * Get the top level competencies and its child competencies. child levels are fetched upto the self::COMPETENCY_DEPTH.
     *
     * @param array $data Record of the competency tree.
     * @param int $level Level depth of competency tree.
     * @return void
     */
    protected function get_top_level_competencies($data, $level = 0) {
        if (!empty($data)) {
            $setcount = 1;
            foreach ($data as $row => $competency) {
                $this->datacount[$row] = 0;
                $this->insert_child_dataset($competency->children, $level + 1, $row, $row);
                $this->datacount[$row]++;
                $nextindex = $this->datacount[$row];
                $this->dataset[$nextindex]['data'][$row] = 1;
                $this->dataset[$nextindex]['backgroundColor'][$row] = $this->competency_colors($competency);
                $this->competencylabels[] = format_string($competency->shortname);
            }

            // Fill the missing competency rows.
            $rangearr = range(0, count($data));
            array_pop($rangearr); // Remove the last element.
            $count = 3;
            foreach ($this->dataset as $key => $value) {
                $rangearr = array_combine(array_keys($rangearr), array_fill(0, count($rangearr), 0));
                $updateddataset = array_replace($rangearr, $value['data']);
                // Increase the data to setup the content.
                array_walk($updateddataset, function (&$value) use ($count) {
                    $value = $value * $count;
                });
                $count = $count + 3;
                $this->dataset[$key]['data'] = $updateddataset;

                // Update the background color.
                $rangearr = array_combine(array_keys($rangearr), array_fill(0, count($rangearr), '#fff'));
                $updatedcolors = array_replace($rangearr, $value['backgroundColor']);
                $this->dataset[$key]['backgroundColor'] = $updatedcolors;
                $this->dataset[$key] = array_merge($this->dataset[$key], $this->default_chart_data());
            }
        }
    }

    /**
     * Generate the data which is used for chart like bgcolor, proficient status and more.
     * Insert the child dataset to the dataset global varaible upto the depth.
     *
     * @param array $data Framework children tree with data.
     * @param int $level Depth of the child.
     * @param int $row It represets the index number of its parent in the dataset array.
     * @return void
     */
    protected function insert_child_dataset($data, $level, $row) {
        if (self::COMPETENCY_DEPTH >= $level) {
            $childcount = range(0, count($data));
            array_pop($childcount); // Remove the last element.
            foreach ($childcount as $key => $value) {
                if (!isset($data[$key])) {
                    continue;
                }
                if (!empty($data[$key]->children)) {
                    $this->insert_child_dataset($data[$key]->children, $level + 1, $row);
                }
                $this->datacount[$row]++;
                $nextindex = $this->datacount[$row];
                $this->dataset[$nextindex]['data'][$row] = 1;
                $this->dataset[$nextindex]['backgroundColor'][$row] = $this->competency_colors($data[$key]);
            }
        }
    }

    /**
     * Default values for the chart data.
     *
     * @return array
     */
    protected function default_chart_data() {
        return [
            'pointBackgroundColor' => "rgba(255,99,132,1)",
            'pointBorderColor' => "red",
            'pointHoverBackgroundColor' => "#fff",
            'pointHoverBorderColor' => "rgba(255,99,132,1)",
        ];
    }

    /**
     * Competency colors based the user proficiency and grade values.
     *
     * @param stdclass $competency competency data.
     * @return string
     */
    protected function competency_colors($competency) {

        if (empty($competency)) {
            return '';
        }
        $grade = $competency->grade ?? '';
        $proficiency = $competency->proficiency ?? '';
        if ($proficiency) {
            return self::COLOR_PROFICIENT; // Proficient.
        } else if ($grade) {
            return self::COLOR_ACHIEVED;// Achieved.
        } else {
            return self::COLOR_NOTACHIEVED; // Not Achived.
        }
    }

    /**
     * Merge the results of the competency into chart dataset.
     *
     * @param array $data
     * @return void
     */
    protected function update_results_todata(&$data) {

        foreach ($data as $row => $competency) {
            $id = $competency->id;
            if (isset($this->competencydata[$id])) {
                $competency = (object) array_merge((array) $competency, (array) $this->competencydata[$id]);
            }

            if ($competency->children) {
                $this->update_results_todata($competency->children);
            }
            $data[$row] = $competency;
        }
    }


    /**
     * Generate the competency proficient and grade levels from multiple courses the competency are added.
     *
     * @param array $results
     * @return void
     */
    protected function generate_competency_data($results) {
        foreach ($results as $row => $result) {
            $competencyid = $result->get('competencyid');
            // Compentency setup for the previous course then test the previous result and updated the current course status.

            if (isset($this->competencydata[$competencyid])) {
                $prevresult = (array) $this->competencydata[$competencyid];

                // Don't need to update the status if the competencies is not achecieved in the previous course.
                if (isset($prevresult['proficiency']) && $prevresult['proficiency'] > 0) {
                    $this->competencydata[$competencyid]->proficiency = $result->get('proficiency');
                }

                if (isset($prevresult['proficiency']) && $prevresult['proficiency'] > 0) {
                    $this->competencydata[$competencyid]->grade = $result->get('grade');
                }
            } else {
                $this->competencydata[$competencyid] = (object) [
                    'proficiency' => $result->get('proficiency'),
                    'grade'       => $result->get('grade'),
                ];
            }
        }
    }

    /**
     * Build the competency data from the record set.
     *
     * @param stdclass $competencies
     * @return void
     */
    protected function build_competency_data($competencies) {

        foreach ($competencies as $key => $value) {
            $competency = $value->competency;
            // Create seperate list of competencies and courses which is related to the user.
            $this->find_user_competency_result($competency);

            $data[] = (object) [
                'id' => $competency->get('id'),
                'shortname' => $competency->get('shortname'),
                'idnumber' => $competency->get('idnumber'),
                'childrencount' => count($value->children),
                'children' => ($value->children) ? $this->build_competency_data($value->children) : [],
            ];
        }
        return $data;
    }

    /**
     * Find the users result in the given competency.
     *
     * @param stdclass $competency
     * @return void
     */
    protected function find_user_competency_result($competency) {
        global $USER;

        $competencyid = $competency->get('id');
        $courses = \core_competency\course_competency::get_courses_with_competency_and_user($competencyid, $USER->id);

        foreach ($courses as $key => $course) {
            if (isset($this->coursecompetencies[$course->id]) && !empty($this->coursecompetencies[$course->id])) {
                array_push($this->coursecompetencies[$course->id], $competencyid);
            } else {
                $this->coursecompetencies[$course->id] = [$competencyid];
            }
        }
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
