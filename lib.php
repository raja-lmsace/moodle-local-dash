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
 * Lib file contains dash available layouts and data sources.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_dash\layout\accordion_layout;
use local_dash\layout\accordion_layout2;
use local_dash\layout\one_stat_layout;
use local_dash\layout\timeline_layout;
use local_dash\layout\two_stat_layout;
use local_dash\layout\cards_layout;
use local_dash\data_grid\filter\course_customfield_condition;


/**
 * Register field definitions used in the layouts.
 *
 * @return void
 */
function local_dash_register_field_definitions() {
    global $CFG;

    if (PHPUNIT_TEST) {
        require("$CFG->dirroot/local/dash/field_definitions.php");
    }

    return require("$CFG->dirroot/local/dash/field_definitions.php");
}

/**
 * Register the layouts this plugin contains.
 *
 * @return array List of layouts.
 */
function local_dash_register_layouts() {
    return [
        [
            'name' => get_string('layoutcards', 'block_dash'),
            'identifier' => cards_layout::class,
        ],
        [
            'name' => get_string('layoutaccordion', 'block_dash'),
            'identifier' => accordion_layout::class,
        ],
        [
            'name' => get_string('layoutaccordion2', 'block_dash'),
            'identifier' => accordion_layout2::class,
        ],
        [
            'name' => get_string('layoutonestat', 'block_dash'),
            'identifier' => one_stat_layout::class,
        ],
        [
            'name' => get_string('layouttwostat', 'block_dash'),
            'identifier' => two_stat_layout::class,
        ],
        [
            'name' => get_string('layouttimeline', 'block_dash'),
            'identifier' => timeline_layout::class,
        ],
    ];
}

/**
 * Register the layouts this plugin contains.
 *
 * @return array List of layouts.
 */
function local_dash_register_widgets() {
    return [];
}

/**
 * Get icon mapping for font-awesome.
 *
 * @return array
 */
function local_dash_get_fontawesome_icon_map() {
    return [
        'local_dash:completed' => 'fa-check',
        'local_dash:viewed' => 'fa-eye',
        'local_dash:default' => 'fa-bell',
        'local_dash:updated' => 'fa-pencil',
        'local_dash:deleted' => 'fa-trash',
    ];
}

/**
 * Dash plugin file definitions, List of fileareas used in local_dash plugin.
 *
 * @param stdclass $course
 * @param stdclass $cm
 * @param stdclass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 */
function local_dash_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'courseimage' || $filearea === 'programbg')
        || $filearea === 'dashthumbnailimage' || $filearea === 'dashbgimage' || $filearea === 'calendareventsimage') {
        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = 0;

        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if ($filearea === 'programbg' || $filearea === 'dashthumbnailimage'
            || $filearea === 'dashbgimage' || $filearea === 'calendareventsimage') {
            $filepath = '/';
        } else {
            if (!$args) {
                $filepath = '/';
            } else {
                $filepath = '/'.implode('/', $args).'/';
            }
        }

        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'local_dash', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false; // The file does not exist.
        }

        // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
        send_stored_file($file, 86400, 0, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Extend the navigation to implement the course category page redirections.
 *
 * @param  stdclass $settingsnav
 * @param  stdclass $context
 * @return void
 */
function local_dash_extend_settings_navigation($settingsnav, $context) {
    global $PAGE, $CFG, $OUTPUT, $DB;
    if ($PAGE->pagetype == 'my-index' && array_key_exists('dashboard', core_component::get_plugin_list('dashaddon'))) {
        require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");
        $dashboard = $DB->get_record('dashaddon_dashboard_dash', ['shortname' => 'coredashboard']);
        $dashbgimage = dashaddon_dashboard_get_dashboard_background($dashboard->id);
        if ($dashbgimage) {
            // Course background image style css content.
            $style = "body {
                        background-image: url('" . $dashbgimage . "');
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-position: center;
                    }";
            $CFG->additionalhtmltopofbody = html_writer::tag('style', $style);
        }
    }

    $hidecategory = get_config('local_dash', 'hidecoursecategory');
    if ($hidecategory && !is_siteadmin()) {
        $redirecturl = new moodle_url('/my');
        $url = get_config('local_dash', 'courseredirecturl');
        $redirecturl = ($url != '') ? $url : $redirecturl;

        if ($PAGE->bodyid == 'page-course-index-category') {
            if ($url != '' && strpos($url, $CFG->wwwroot) != true ) {
                $redirecturl = new moodle_url($url);
            }
            redirect($redirecturl);
        }
    }

    $manager = \core_plugin_manager::instance();
    if (($PAGE->bodyid == 'page-my-index'
        || substr($PAGE->bodyid, 0, 21) == 'page-totara-dashboard')
        && (is_siteadmin() || has_capability('local/dash:managedashboards', $PAGE->context))
        && $PAGE->user_is_editing()) {
        $dashaddondash = $manager->get_plugin_info('dashaddon_dashboard');
        if ($dashaddondash && $dashaddondash->get_status() != core_plugin_manager::PLUGIN_STATUS_MISSING) {
            $currentbtn = $PAGE->button;
            $url = new moodle_url('/local/dash/addon/dashboard/dashboard_list.php');
            $currentbtn .= $OUTPUT->single_button($url, get_string('managedashboards', 'block_dash'));
            $PAGE->set_button($currentbtn);
        }
    }

    if ($PAGE->context->contextlevel == CONTEXT_COURSECAT && $PAGE->pagetype == 'course-index-category') {
        $category = core_course_category::get($PAGE->context->instanceid);
        if ($category->can_create_course() || $category->has_manage_capability()) {
            $url = new moodle_url('/local/dash/addon/dashboard/dashboard_list.php', ['contextid' => $PAGE->context->id]);
            $currentbtn = $OUTPUT->single_button($url, get_string('managedashboards', 'block_dash'), 'get');
            $PAGE->set_button($currentbtn);
        }
    }
}

/**
 * Check moodle supports the secondary navigation method.
 *
 * @return bool
 */
function local_dash_secondarynav() {
    return class_exists('\core\navigation\views\secondary');
}

/**
 * load the customfield conditions to the datasource.
 *
 * @param filter_collection $filter
 * @return void
 */
function local_dash_customfield_conditions(&$filter) {
    if (class_exists('\core_course\customfield\course_handler')) {
        $coursehandler = \core_course\customfield\course_handler::create();
        foreach ($coursehandler->get_fields() as $field) {
            if (!in_array($field->get('type'), ['select', 'text', 'textarea'])) {
                continue;
            }
            $alias = $field->get('shortname');
            $select = $alias . '.value';
            $filter->add_filter(new course_customfield_condition($alias, $select, $field->get_formatted_name()));
        }

    } else {
        global $DB;
        foreach ($DB->get_records('course_info_field') as $field) {
            $alias = strtolower($field->shortname);
            $select = $alias . '.data';
            $filter->add_filter(new course_customfield_condition($alias, $select, $field->fullname));
        }
    }
}

/**
 * Get the list of course fields and generate them as menu item for dropdown.
 *
 * @return array
 */
function local_dash_get_coursefields() {
    if (class_exists('\core_course\customfield\course_handler')) {
        $coursehandler = \core_course\customfield\course_handler::create();
        $fields = [0 => get_string('choose')];
        foreach ($coursehandler->get_fields() as $field) {
            $fieldid = $field->get('id');
            $fields[$fieldid] = $field->get('name');
        }
    } else {
        global $DB;
        foreach ($DB->get_records('course_info_field') as $field) {
            $fields[$field->id] = $field->fullname;
        }
    }
    return $fields;
}





/**
 * Get card block column class.
 * @param int $column
 * @return string
 */
function local_dash_get_card_column_customclass($column) {
    switch ($column) {
        case 12:
            return 'one-column-block';
        case 6:
            return 'two-column-block';
        case 4:
            return 'three-column-block';
        case 3:
            return 'four-column-block';
        case 25:
            return 'five-column-block';
        case 2:
            return 'six-column-block';
        case 1:
            return 'twelve-column-block';
        default:
          return '';
    }
}
/**
 * Fetches the list of icons and creates an icon suggestion list to be sent to a fragment.
 *
 * @param array $args An array of arguments.
 * @return string The rendered HTML of the icon suggestion list.
 */
function local_dash_output_fragment_icons_list($args) {
    global $OUTPUT, $PAGE;

    // Proceed only if a context was given as argument.
    if ($args['context']) {
        // Initialize rendered icon list.
        $icons = [];

        // Load the theme config.
        $theme = \theme_config::load($PAGE->theme->name);

        // Get the FA system.
        $faiconsystem = \core\output\icon_system_fontawesome::instance($theme->get_icon_system());

        // Get the icon list.
        $iconlist = $faiconsystem->get_core_icon_map();

        // Add an empty element to the beginning of the icon list.
        array_unshift($iconlist, '');

        // Iterate over the icons.
        foreach ($iconlist as $iconkey => $icontxt) {
            // Split the component from the icon key.
            $icon = explode(':', $iconkey);

            // Pick the icon key.
            $iconstr = isset($icon[1]) ? $icon[1] : 'moodle';

            // Pick the component.
            $component = isset($icon[0]) ? $icon[0] : '';

            // Render the pix icon.
            $icon = new \pix_icon($iconstr,  "", $component);
            $icons[] = [
                'icon' => $faiconsystem->render_pix_icon($OUTPUT, $icon),
                'value' => $iconkey,
                'label' => $icontxt,
            ];
        }

        // Return the rendered icon list.
        return $OUTPUT->render_from_template('local_dash/fontawesome-iconpicker-popover', ['options' => $icons]);
    }
}


/**
 * Upgrade the dashboard in to the new block.
 *
 * @return bool
 */
function local_dash_upgrade_blocks_data_source_idnumber() {
    global $DB;
    $changedatasources = [
        'local_dash\local\block_dash\logstore_data_source' => 'dashaddon_logstore\local\block_dash\logstore_data_source',
        'block_dash\local\data_source\categories_data_source' => 'dashaddon_categories\local\block_dash\categories_data_source',
        'local_dash\local\block_dash\courses_data_source' => 'dashaddon_courses\local\block_dash\courses_data_source',
        'local_dash\local\block_dash\dashboard_data_source' => 'dashaddon_dashboard\local\block_dash\dashboard_data_source',
        'local_dash\local\block_dash\completions_data_source' =>
            'dashaddon_course_completions\local\block_dash\completions_data_source',
    ];
    $blockinstances = $DB->get_records('block_instances', ['blockname' => 'dash']);
    foreach ($blockinstances as $blockinstance) {
        $block = block_instance($blockinstance->blockname, $blockinstance);
        if (!empty($block->config)) {
            $config = clone($block->config);
            $datasource = $config->data_source_idnumber;
            if (isset($changedatasources[$datasource])) {
                $config->data_source_idnumber = $changedatasources[$datasource];
                // Save the content preference to block instance config.
                $block->instance_config_save($config);
            }
        }
    }
    return true;
}
