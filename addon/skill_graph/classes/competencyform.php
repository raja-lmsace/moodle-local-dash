<?php
// This file is part of The Bootstrap Moodle theme
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
 * Dash addon skill graph widget form to setup color and image of competencies.
 *
 * @package    dashaddon_skill_graph
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace dashaddon_skill_graph;

defined('MOODLE_INTERNAL') || die();

use context;
use moodle_url;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Dash addon skill graph dynamic form to setup color and image of competencies.
 */
class competencyform extends \core_form\dynamic_form {
    /**
     * Competency color config name.
     */
    public const COMPETENCYCOLOR = 'competencycolor';

    /**
     * Content form elements defined.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform =& $this->_form;

        $competencyid = $this->_customdata['competencyid'] ?? '';
        $mform->addElement('hidden', 'competencyid', $competencyid);

        require_once($CFG->dirroot . '/local/dash/element-colorpicker.php');
        \MoodleQuickForm::registerElementType(
            'local_dash_colorpicker',
            $CFG->dirroot . '/local/dash/element-colorpicker.php',
            'moodlequickform_local_dash_colorpicker'
        );

        // Competency color.
        $mform->addElement(
            'local_dash_colorpicker',
            'competency_preferences[color]',
            get_string('competencycolor', 'block_dash')
        );
        $mform->setType('competency_preferences[color]', PARAM_RAW);

        // Competency image.
        $mform->addElement(
            'filemanager',
            'competency_preferences[competencyimage]',
            get_string('competencyimage', 'block_dash'),
            null,
            $editor
        );
    }

    /**
     * Get the context related to this form.
     */
    protected function get_context_for_dynamic_submission(): context {
        // Block record id.
        $pagecontextid = $this->optional_param('pagecontextid', 0, PARAM_INT);

        return $pagecontextid ? \core\context::instance_by_id($pagecontextid) : \context_system::instance();
    }

    /**
     * Check the access of the current user for this form submission.
     */
    protected function check_access_for_dynamic_submission(): void {
        // Validatation of user capability goes here.
    }

    /**
     * Process the submission from AJAX.
     *
     * @return void
     */
    public function process_dynamic_submission() {
        global $DB;

        // Get the submitted content data.
        $formdata = (object) $this->get_data();

        $data = $formdata->competency_preferences;

        return true;
    }

    /**
     * Set the data to form. Need to call this method on direct data setup.
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {

        $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

        $defaults = [
            'competencyid' => $competencyid,
            'competency_preferences' => [
                'color' => get_config('dashaddon_skill_graph', self::COMPETENCYCOLOR . "_" . $competencyid) ?: '',
            ],
        ];

        // Setup the block config data to form.
        $this->set_data($defaults);
    }

    /**
     * Get the page ulr to submittit the form data.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/dash/addon/skill_graph/competencylist.php', []);
    }

    /**
     * Fetch the widget form elemants.
     *
     * @return \MoodleQuickForm
     */
    public function get_form() {
        return $this->_form;
    }

    /**
     * Process the competency data before set the default.
     *
     * @param  mixed $defaultvalues default values
     * @return void
     */
    public function data_preprocessing(&$defaultvalues) {

        // Prepare the editor to support files.
        $defaultvalues = (object) $defaultvalues; // Convert to object, file manager methods require the objects.

        // Block context.
        $context = \context_system::instance();

        $filemanagers = [
            'competencyimage' => 'competencyimage',
        ];

        $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

        // Prepare the file manager fields to store images.
        foreach ($filemanagers as $configname => $filearea) {
            $draftitemid = file_get_submitted_draft_itemid($filearea);

            file_prepare_draft_area(
                $draftitemid,
                $context->id,
                'dashaddon_skill_graph',
                $filearea,
                $competencyid,
                [
                    'subdirs' => 0,
                    'accepted_types' => ['web_image'],
                ]
            );

            $defaultvalues->competency_preferences[$configname] = $draftitemid;
        }
    }

    /**
     * Prepare the data after form was submited.
     *
     * Store all the editor files and use the name of the config as filearea.
     *
     * @param  mixed $data submitted data
     * @return void
     */
    public function data_postprocessing(&$data) {

        // Prepare the editor to support files.
        $data = (object) $data;

        $competencypreferences = (object) $data->competency_preferences;

        // Block context.
        $context = \context_system::instance();

        $filemanagers = [
            'competencyimage' => 'competencyimage',
        ];

        $competencyid = $this->optional_param('competencyid', 0, PARAM_INT);

        foreach ($filemanagers as $configname => $filearea) {
            // Now save the files in correct part of the File API.
            file_save_draft_area_files(
                $competencypreferences->$configname,
                $context->id,
                'dashaddon_skill_graph',
                $filearea,
                $competencyid,
                $this->get_editor_options($context)
            );
        }

        if (property_exists($competencypreferences, 'color')) {
            set_config(self::COMPETENCYCOLOR . "_" . $competencyid, $competencypreferences->color, 'dashaddon_skill_graph');
        }
    }

    /**
     * Editor form element options.
     *
     * @param context $context
     * @return array
     */
    protected function get_editor_options($context = null) {
        global $PAGE;

        return [
            'subdirs' => true,
            'maxfiles' => 1,
            'maxbytes' => 1000000,
            'context' => $context ?: $PAGE->context,
            'accepted_types' => 'web_image',
        ];
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * note: $slashed param removed
     *
     * @param stdClass|array $defaultvalues object or array of default values
     */
    public function set_data($defaultvalues) {

        $this->data_preprocessing($defaultvalues); // Include to store the files.

        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }

        $this->_form->setDefaults($defaultvalues);
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * Do not override this method, override data_postprocessing() instead.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            $this->data_postprocessing($data);
        }
        return $data;
    }
}
