<?php

namespace dashaddon_content;

use context;
use context_block;
use moodle_url;
use core_external\external_api;
use course_modinfo;
use dashaddon_content\local\block_dash\content_customtype;
use local_ludemy\widget_fields;
use local_ludemy\plugininfo\aceblock;

require_once($CFG->dirroot.'/lib/formslib.php');

class contentform extends \core_form\dynamic_form {

    /**
     * Content form elements defined.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform =& $this->_form;

        $blockid = $this->_customdata['blockid'] ?? '';
        $mform->addElement('hidden', 'blockid', $blockid);

        $layoutid = $this->_customdata['layoutid'] ?? '';
        $mform->addElement('hidden', 'layoutid', $layoutid);

        require_once($CFG->dirroot.'/local/dash/addon/content/element-colorpicker.php');
        \MoodleQuickForm::registerElementType(
            'dashaddon_content_colorpicker',
            $CFG->dirroot.'/local/dash/addon/content/element-colorpicker.php',
            'moodlequickform_dashaddon_content_colorpicker'
        );

        // Editor options.
        $editor = $this->get_editor_options($this->get_context_for_dynamic_submission());
        $mform->addElement('editor', 'content_preferences[content_editor]',
            get_string('contenteditor', 'block_dash'), [], $editor);

        // Dashaddon content.
        $mform->addElement('dashaddon_content_colorpicker', 'content_preferences[backgroundcolor]',
            get_string('backgroundcolor', 'block_dash'));
        $mform->setType('content_preferences[backgroundcolor]', PARAM_RAW);

        // Dashaddon content.
        $mform->addElement('dashaddon_content_colorpicker', 'content_preferences[textcolor]',
            get_string('textcolor', 'block_dash'));
        $mform->setType('content_preferences[textcolor]', PARAM_RAW);

        // Background image.
        $mform->addElement('filemanager', 'content_preferences[backgroundimage]', get_string('backgroundimage', 'block_dash'), null, $editor);

    }

    /**
     * Get the context related to this form.
     */
    protected function get_context_for_dynamic_submission(): context {
        // Block record id.
        $blockid = $this->optional_param('blockid', 0, PARAM_INT);

        return $blockid ? \context_block::instance($blockid) : \context_system::instance();
    }

    /**
     * Check the access of the current user for this form submission.
     */
    protected function check_access_for_dynamic_submission(): void {
        // TODO: Validatation of user capability goes here.
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

        $layoutid = $this->optional_param('layoutid', 'layout1', PARAM_ALPHANUMEXT);

        // Make sure the block id is available in form.
        if (!isset($formdata->blockid) || !isset($formdata->content_preferences)) {
            return false;
        }

        // Block id.
        $blockid = $formdata->blockid;

        // Get the block instance record using the blockid.
        $blockinstance = $DB->get_record('block_instances', ['id' => $blockid]);
        // Create the block instance object to get and store config.
        $block = block_instance($blockinstance->blockname, $blockinstance);
        if (!empty($block->config)) {
            $config = clone($block->config);
        } else {
            $config = new \stdClass;
        }

        if (!isset($config->preferences)) {
            $config->preferences = [];
        }

        // Remove the content preferences and blockid, Removed un used data before save.
        unset($formdata->content_preferences->content_editor);
        unset($formdata->blockid);

        // Merge the content preference with block preferences.
        $config->preferences['content_preferences'][$layoutid] = $formdata->content_preferences;

        // Save the content preference to block instance config.
        $block->instance_config_save($config);

        return true;
    }

    /**
     * Set the data to form. Need to call this method on direct data setup.
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $blockid = $this->optional_param('blockid', 0, PARAM_INT);
        $layoutid = $this->optional_param('layoutid', 'layout1', PARAM_ALPHANUMEXT);

        $defaults = [
            'blockid' => $blockid,
            'layoutid' => $layoutid,
        ];

        if ($blockid) {
            // Fetch the stored config options for this block using block_instance.
            $blockinstance = $DB->get_record('block_instances', ['id' => $blockid]);
            $block = block_instance($blockinstance->blockname, $blockinstance);
            if (!empty($block->config)) {
                $config = (array) clone($block->config);
                $defaults = array_merge($defaults, $config); // Merge the preferences with default values.
                if (isset($defaults['preferences']['content_preferences'][$layoutid])) {
                    $contentpreference = $defaults['preferences']['content_preferences'][$layoutid];
                    $defaults['content_preferences'] = (array) $contentpreference; // Setup default preference.
                    /* $defaults['content_preferences']['content_editor'] = [
                        'text' => $contentpreference->content, //  file_rewrite_pluginfile_urls($contentpreference->content, 'pluginfile.php', $this->get_context_for_dynamic_submission()->id, 'dashaddon_content', 'content', $blockid),
                        'format' => $contentpreference->contentformat
                    ]; */
                }
            }
        }

        // Setup the block config data to form.
        $this->set_data($defaults);
    }

    /**
     * Get the page ulr to submittit the form data.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/dash/content/contentform.php', []);
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
     * Process the pulse module data before set the default.
     *
     * @param  mixed $defaultvalues default values
     * @return void
     */
    public function data_preprocessing(&$defaultvalues) {

        // print_r($context);
        // Prepare the editor to support files.
        $defaultvalues = (object) $defaultvalues; // Convert to object, file manager methods require the objects.

        // Content preferences.
        $contentpreferences = (object) $defaultvalues->content_preferences;

        $blockid = $defaultvalues->blockid ?? 0; // Use the ludemy id as item id. Blockinstanceid

        // Block context.
        $context = \context_block::instance($blockid);

        $editors = [
            'content' => 'content',
            // Other filearea goes here in future.
        ];
        $filemanagers = [
            'backgroundimage' => 'backgroundimage'
        ];

        $layoutid = $this->optional_param('layoutid', 'layout1', PARAM_ALPHANUMEXT);

        // Prepare the editors to save the files placed in the editors.
        foreach ($editors as $configname => $filearea) {

            if (!isset($contentpreferences->$configname)) {
                $contentpreferences->$configname = '';
                $contentpreferences->{$configname."format"} = editors_get_preferred_format();
            }

            $filearea .= '_' . $layoutid; // Filearea with layout.
            $contentpreferences = file_prepare_standard_editor(
                $contentpreferences, $configname, $this->get_editor_options($context), $context, 'dashaddon_content', $filearea, $blockid
            );
        }

        $defaultvalues->content_preferences = (array) $contentpreferences;

        // Prepare the file manager fields to store images.
        foreach ($filemanagers as $configname => $filearea) {

            $filearea .= '_' . $layoutid;

            $draftitemid = file_get_submitted_draft_itemid($filearea);

            file_prepare_draft_area(
                $draftitemid, $context->id, 'dashaddon_content', $filearea, $blockid, [
                    'subdirs' => 0,
                    'accepted_types' => ['web_image'],
                ]
            );

            $defaultvalues->content_preferences[$configname] = $draftitemid;
        }

    }

    /**
     * Prepare the data after form was submited.
     *
     * Store all the editor files and update the structure and the file urls with placeholders.
     * It used the ludemy block instance id (ludemyid) as item id and uses the local_ludemy as component.
     * Also use the name of the editor as filearea.
     *
     * @param  mixed $data submitted data
     * @return void
     */
    public function data_postprocessing(&$data) {

        // Prepare the editor to support files.
        $data = (object) $data;

        $contentpreferences = (object) $data->content_preferences;

        $blockid = $data->blockid ?? 0; // Use the ludemy id as item id. blockinstanceid

        // Block context.
        $context = \context_block::instance($blockid);

        $editors = [
            'content' => 'content',
            // Other filearea goes here in future.
        ];

        $filemanagers = [
            'backgroundimage' => 'backgroundimage'
        ];

        $layoutid = $this->optional_param('layoutid', 'layout1', PARAM_ALPHANUMEXT);

        foreach ($editors as $configname => $filearea) {
            // Verify the element is editor.
           /*  if (!isset($contentpreferences->$configname) && !isset($contentpreferences->{$configname.'_editor'})) {
                continue;
            } */
            $filearea .= '_' . $layoutid;

            $contentpreferences = file_postupdate_standard_editor(
                $contentpreferences, $configname, $this->get_editor_options($context), $context,  'dashaddon_content', $filearea, $blockid
            );
        }

        $data->content_preferences = $contentpreferences;

        foreach ($filemanagers as $configname => $filearea) {
            // Now save the files in correct part of the File API.
            $filearea .= '_' . $layoutid;
            file_save_draft_area_files(
                $contentpreferences->$configname, $context->id, 'dashaddon_content', $filearea, $blockid, $this->get_editor_options($context)
            );
        }

    }

    /**
     * Editor form element options.
     *
     * @param context $context
     * @return array
     */
    protected function get_editor_options($context=null) {
        global $PAGE;

        return [
            // 'trusttext' => true,
            'subdirs' => true,
            'maxfiles' => 1,
            'maxbytes' => 1000000,
            'context' => $context ?: $PAGE->context,
            'accepted_types' => 'web_image'
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
    function set_data($defaultvalues) {

        $this->data_preprocessing($defaultvalues); // Include to store the files.

        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }
        // print_r($defaultvalues);
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
