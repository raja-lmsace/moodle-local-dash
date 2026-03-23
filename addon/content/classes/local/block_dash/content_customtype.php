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
 * Dash content widget - Datasource defined.
 *
 * @package    dashaddon_content
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_content\local\block_dash;

use dashaddon_content\local\block_dash\data_grid\filter\sectiondisplay_condition;
use block_dash\local\data_custom\abstract_custom_type;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\filter\filter_collection;
use renderer_base;
use html_writer;

/**
 * Datasource and method of the dash content widget definitions.
 */
class content_customtype extends abstract_custom_type {
    /**
     * Represets the layout width is full width.
     * @var int
     */
    public const LAYOUTFULL = 1;

    /**
     * Represets the layout width is double, both are equal width.
     * @var int
     */
    public const LAYOUTDOUBLEEQUAL = 2;

    /**
     * Represets the layout width is double layout, left layout is 3/2 .
     * @var int
     */
    public const LAYOUTDOUBLELEFT = 3;

    /**
     * Represets the layout width is double layout, right layout is 3/2.
     * @var int
     */
    public const LAYOUTDOUBLERIGHT = 4;

    /**
     * Represets the layout width is triple layout.
     * @var int
     */
    public const LAYOUTTRIPLE = 5;

    /**
     * Represets the content display state to display on all pages.
     * @var int
     */
    public const DISPLAYALL = 0;

    /**
     * Represets the content display state to display only on section pages.
     * @var int
     */
    public const DISPLAYSECTION = 1;

    /**
     * Get template file name to renderer.
     */
    public function get_mustache_template_name(): string {
        return 'dashaddon_content/content';
    }

    /**
     * Check the custom type has capability.
     *
     * @param \context $context
     * @return bool
     */
    public static function has_capbility($context): bool {
        global $COURSE;

        // Content is only available for the users with this capability and only for course pages not system pages.
        return has_capability('dashaddon/content:managecontent', $context);
    }

    /**
     * Build the features config to display in the block to select after insert the dash block.
     *
     * @param  mixed $mform
     * @param  mixed $source
     * @return void
     */
    public static function get_features_config(&$mform, $source) {
        global $PAGE;
        $layouts = [
            self::LAYOUTFULL => get_string('layoutfull', 'block_dash'),
            self::LAYOUTDOUBLEEQUAL => get_string('layoutdoubleequal', 'block_dash'),
            self::LAYOUTDOUBLELEFT => get_string('layoutdoubleleft', 'block_dash'),
            self::LAYOUTDOUBLERIGHT => get_string('layoutdoubleright', 'block_dash'),
            self::LAYOUTTRIPLE => get_string('layouttriple', 'block_dash'),
        ];

        $customoptions[] = $mform->createElement('html', html_writer::tag(
            'p',
            get_string('dashaddoncontentdesc', 'block_dash'),
            ['class' => 'dash-source-desc']
        ));
        $customoptions[] = $mform->createElement('html', html_writer::start_div('custom-addon'));
        $customoptions[] = $mform->createElement('html', html_writer::start_div('custom-addon-item'));
        $customoptions[] = $mform->createElement('html', html_writer::start_div('addon-config hide'));
        $customoptions[] = $mform->createElement('radio', 'config_data_source_idnumber', '', $source['name'], self::class);
        $customoptions[] = $mform->createElement('html', html_writer::end_div());

        if (self::get_single_section()) {
            $customoptions[] = $mform->createElement('hidden', 'config_preferences[singlesection]', self::get_single_section());
            $mform->setType('config_preferences[singlesection]', PARAM_INT);
        }

        foreach ($layouts as $key => $value) {
            $customoptions[] = $mform->createElement('html', \html_writer::start_div(
                'content-layout-item addon-suboptions',
                ['data-target' => 'subsource-config']
            ));
            $customoptions[] = $mform->createElement('radio', 'config_preferences[contentlayout]', '', $value, $key);
            $customoptions[] = $mform->createElement('html', \html_writer::end_div());
        }

        $customoptions[] = $mform->createElement('html', html_writer::end_div());

        $mform->addGroup($customoptions, 'customfeature', get_string('content', 'dashaddon_content'), [' '], false);
        $mform->setType('customfeature', PARAM_TEXT);

        return $customoptions ?? [];
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'content',
            'layout' => 'custom',
        ];
        return $preferences;
    }

    /**
     * Check the content contains any data to render.
     *
     * @return bool
     */
    public function is_empty() {
        $this->build_widget();
        return isset($this->data['configured']) && $this->data['configured'] ? false : true;
    }

    /**
     * Build the widget data.
     *
     * @return void
     */
    public function build_widget() {
        global $PAGE;

        $blockid = $this->get_block_instance()->instance->id;
        $content = (object) $this->get_preferences('content_preferences');

        $layouts = $this->generate_layouts($content);

        foreach ($layouts as $config) {
            if (isset($config['edit'])) {
                $configured = true;
            }
        }

        $this->data = [
            'layout' => $this->get_preferences("contentlayout"),
            'layouts' => array_values($layouts),
            'configured' => isset($configured) || $PAGE->user_is_editing() ? true : false,
            'contextid' => $this->get_block_instance()->context->id,
            'uniqueid' => $this->get_block_instance()->instance->id,
        ];
    }

    /**
     * Generate the layout contents.
     *
     * @param stdclass $content
     * @return array
     */
    protected function generate_layouts($content): array {
        global $PAGE;

        // Find this layout is available to display on this page.
        if (!$this->layout_is_available()) {
            return [];
        }

        // Layout not selected.
        $layout = $this->get_preferences('contentlayout');
        if (!$layout) {
            return [];
        }

        $blockid = $this->get_block_instance()->instance->id;

        // Generate column classes for layouts.
        $count = (in_array($layout, [self::LAYOUTDOUBLEEQUAL, self::LAYOUTDOUBLELEFT, self::LAYOUTDOUBLERIGHT])) ? 2 : 1;
        $count = ($layout == self::LAYOUTTRIPLE) ? 3 : $count;

        $layouts = [];
        for ($i = 1; $i <= $count; $i++) {
            $layoutname = 'layout' . $i;
            $layoutoptions = $content->$layoutname ?? [];

            $layoutdata = $this->generate_layout_content($layoutname, $layoutoptions, $blockid);
            if (empty($layoutdata) && !$PAGE->user_is_editing()) {
                continue;
            }

            $layouts[$layoutname] = ['layoutid' => $layoutname];
            $layouts[$layoutname] += $layoutdata;
        }

        // Update the count, based on available layouts. and create classes.
        $count = count($layouts);
        if ($count == 2) {
            $colclasses = ['col-sm-6', 'col-sm-6']; // Double equal.
            $colclasses = ($layout == self::LAYOUTDOUBLELEFT) ? ['col-sm-4', 'col-sm-8'] : $colclasses; // Double left.
            $colclasses = ($layout == self::LAYOUTDOUBLERIGHT) ? ['col-sm-8', 'col-sm-4'] : $colclasses; // Double right.
        } else if ($count == 3) {
            $colclasses = ['col-sm-4', 'col-sm-4', 'col-sm-4']; // Triple same.
        } else {
            $colclasses = ['col-sm-12']; // Full one column.
        }

        // Add the column classes to the layouts.
        $i = 1;
        foreach ($layouts as $layoutname => $val) {
            $layouts[$layoutname]['colclass'] = $colclasses[$i - 1] ?? '';
            $i++;
        }

        return $layouts ?? [];
    }

    /**
     * Verify the block is configured to display on this page.
     *
     * @return void
     */
    protected function layout_is_available() {
        global $PAGE;

        // Show all the block on editing.
        if ($PAGE->user_is_editing()) {
            return true;
        }

        $filter = $this->get_filter_collection()->get_filter('sectiondisplay')->layout_is_available();

        return $filter;
    }

    /**
     * Generate the layout contents based on user preferences.
     *
     * @param string $layoutid
     * @param stdClass $content Layout preferences
     * @param int $blockid
     * @return array
     */
    protected function generate_layout_content($layoutid, $content, $blockid) {
        global $PAGE;

        if (empty($content) || !$this->layout_is_available()) {
            return [];
        }
        $context = \context_block::instance($this->get_block_instance()->instance->id);

        // Background image of the content.
        $bgimage = $this->get_background_image('backgroundimage_' . $layoutid, 'dashaddon_content', $blockid);
        $bgimageurl = $bgimage ? $bgimage->out(false) : '';

        $contenttext = $content->content ?? '';
        $bgstyle = $content->backgroundcolor ? "background-color:" . $content->backgroundcolor . ";" : '';
        // Text color.
        $bgstyle .= isset($content->textcolor) && $content->textcolor ? "color:" . $content->textcolor . ";" : "";

        // Remove the empty p tags from content.
        $pattern = "/<p[^>]*><br><\\/p[^>]*>/";
        $emptyremoved = preg_replace($pattern, '', $contenttext);

        if (empty($emptyremoved)) {
            $contenttext = '';
        }

        // Image and content text is not implemented.
        if (empty($bgimageurl) && empty($contenttext) && !$PAGE->user_is_editing()) {
            return [];
        }

        // Add the background image as background of block.
        if ($bgimage) {
            $bgstyle .= !empty(html_to_text($contenttext)) ? "background-image: url(" . $bgimage . ");" : '';
        }
        $context = \context_block::instance($this->get_block_instance()->instance->id);

        $contenttext = file_rewrite_pluginfile_urls(
            $contenttext,
            'pluginfile.php',
            $context->id,
            'dashaddon_content',
            'content_' . $layoutid,
            $blockid
        );

        return [
            'edit' => ($bgimageurl || $context) ? true : false, // Content added to the layout, show the edit icon.
            'style' => $bgstyle,
            'backgroundimage' => $bgimageurl,
            'content' => $contenttext
                ? format_text($contenttext, $content->contentformat, ['overflowdiv' => false, 'noclean' => true]) : '',
            'showimage' => $contenttext ? false : true,
        ];
    }

    /**
     * Get background image.
     *
     * @param string $filearea
     * @param string $component
     * @param int $itemid
     * @return stdClass
     */
    protected function get_background_image($filearea, $component, $itemid) {

        $fs = get_file_storage(); // File storage instance.

        $contextid = $this->get_block_instance()->context->id; // Current block instance.

        $files = $fs->get_area_files($contextid, $component, $filearea, $itemid, '', false); // Fetch list of area files.

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
            );
        }

        return $url ?? '';
    }

    /**
     * Set the default preferences of the content addon, force the content to sectiondisplay to the current section.
     *
     * @param array $data
     * @return void
     */
    public function set_default_preferences(&$data) {
        // Set the section default for single section in dashaddon_content.
        if (get_config("local_dash", "restrictcurrentsection")) {
            $configpreferences = $data['config_preferences'];
            if (
                isset($data['config_data_source_idnumber']) &&
                $data['config_data_source_idnumber'] == 'dashaddon_content\local\block_dash\content_customtype'
            ) {
                if (
                    isset($configpreferences['singlesection'])
                    && $configpreferences['singlesection'] && !isset($configpreferences['sectiondisplay'])
                ) {
                    $configpreferences['filters']['sectiondisplay']['enabled'] = 1;
                    $configpreferences['filters']['sectiondisplay']['sections'] = [$configpreferences['singlesection']];
                }
            }
            $data['config_preferences'] = $configpreferences;
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
        global $PAGE;

        // Add content layout selector on General tab.
        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            parent::build_preferences_form($form, $mform);

            $layouts = [
                self::LAYOUTFULL => get_string('layoutfull', 'block_dash'),
                self::LAYOUTDOUBLEEQUAL => get_string('layoutdoubleequal', 'block_dash'),
                self::LAYOUTDOUBLELEFT => get_string('layoutdoubleleft', 'block_dash'),
                self::LAYOUTDOUBLERIGHT => get_string('layoutdoubleright', 'block_dash'),
                self::LAYOUTTRIPLE => get_string('layouttriple', 'block_dash'),
            ];
            $mform->addElement('select', 'config_preferences[contentlayout]', get_string('contentlayout', 'block_dash'), $layouts);
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }

        $mform->addElement('html', get_string('fieldalert', 'block_dash'), 'fieldalert');
    }

    /**
     * Get the section of the page, if the page is single section.
     *
     * @return int|bool
     */
    public static function get_single_section() {
        global $PAGE;
        $courseid = $PAGE->course->id;
        if ($courseid != SITEID) {
            $format = course_get_format($PAGE->course->id);
            $course = $format->get_course();
            if (isset($course->coursedisplay) && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $params = $PAGE->url->params();
                if (isset($params['section'])) {
                    return $params['section'];
                }
            }
        }
        return false;
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(
            new sectiondisplay_condition('sectiondisplay', '')
        );

        return $filtercollection;
    }

    /**
     * This data soruce supports the current script.
     *
     * @return bool
     */
    public function supports_currentscript() {
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     *
     * @param int $frominstanceid the id number of the block instance to copy from.
     * @param int $currentcontextid
     *
     * @return bool
     */
    public function instance_copy($frominstanceid, $currentcontextid) {

        // Blockid.
        $blockid = $this->get_block_instance()->instance->id;

        // From context.
        $fromcontext = \context_block::instance($frominstanceid);

        // Find the layout counts.
        $layout = $this->get_preferences('contentlayout');
        $count = (in_array($layout, [self::LAYOUTDOUBLEEQUAL, self::LAYOUTDOUBLELEFT, self::LAYOUTDOUBLERIGHT])) ? 2 : 1;
        $count = ($layout == self::LAYOUTTRIPLE) ? 3 : $count;

        $fs = get_file_storage();
        // Do not use draft files hacks outside of forms.
        for ($i = 1; $i <= $count; $i++) {
            $fileareas = ['backgroundimage_', 'content_'];
            foreach ($fileareas as $filearea) {
                $filearea = $filearea . 'layout' . $i; // Filearea name.

                $files = $fs->get_area_files($fromcontext->id, 'dashaddon_content', $filearea, $frominstanceid, 'id ASC', false);

                foreach ($files as $file) {
                    $filerecord = ['contextid' => $currentcontextid, 'itemid' => $blockid];
                    $fs->create_file_from_storedfile($filerecord, $file);
                }
            }
        }

        return true;
    }

    /**
     * Include the flag to confirm this block is show on this page.
     *
     * @param array $data
     * @return void
     */
    public function update_data_before_render(&$data) {
        $data['showcollapseblock'] = $this->is_section_expand_content_addon($data);
    }

    /**
     * Determines if the section content should be expanded based on the provided data.
     *
     * This function checks if the 'collapseaction' key in the provided data is set to true.
     * If so, it retrieves the current section parameter and checks if the section is restricted
     * based on the block instance's configuration preferences and filter collection.
     *
     * @param array $data The data array containing the 'collapseaction' key.
     * @return bool Returns true if the section content should be expanded, false otherwise.
     */
    public function is_section_expand_content_addon($data) {

        if (isset($data['collapseaction']) && $data['collapseaction'] === true) {
            $currentsection = optional_param('section', 0, PARAM_INT);

            if (isset($this->get_block_instance()->config->preferences)) {
                if ($this->get_filter_collection()->get_filter('sectiondisplay') === null) {
                    return false;
                }
                $restrictedsections = $this->get_filter_collection()->get_filter('sectiondisplay')->get_values();
                if (in_array((int)$currentsection, $restrictedsections)) {
                    return true;
                }
            }
        }

        return false;
    }
}
