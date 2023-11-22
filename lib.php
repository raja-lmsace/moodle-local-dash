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

use local_dash\data_source\courses_data_source;
use local_dash\data_source\completions_data_source;
use local_dash\data_source\dashboard_data_source;
use local_dash\data_source\logstore_data_source;
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
 * Register data sources.
 *
 * @return array
 * @throws coding_exception
 */
function local_dash_register_data_sources() {
    global $CFG;

    require_once("$CFG->dirroot/blocks/dash/lib.php");

    // Totara 12.12 doesn't support the correct name spacing (e.g. component\block_dash\data_source.php).
    // Use legacy register instead.
    if (block_dash_is_totara()) {
        return [
            [
                'name' => get_string('datasource:completions_data_source', 'block_dash'),
                'identifier' => \local_dash\local\block_dash\completions_data_source::class
            ],
            [
                'name' => get_string('datasource:courses_data_source', 'block_dash'),
                'identifier' => \local_dash\local\block_dash\courses_data_source::class
            ],
            [
                'name' => get_string('datasource:dashboard_data_source', 'block_dash'),
                'identifier' => \local_dash\local\block_dash\dashboard_data_source::class
            ],
            [
                'name' => get_string('datasource:logstore_data_source', 'block_dash'),
                'identifier' => \local_dash\local\block_dash\logstore_data_source::class
            ]
        ];
    }
    return [];
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
            'identifier' => cards_layout::class
        ],
        [
            'name' => get_string('layoutaccordion', 'block_dash'),
            'identifier' => accordion_layout::class
        ],
        [
            'name' => get_string('layoutaccordion2', 'block_dash'),
            'identifier' => accordion_layout2::class
        ],
        [
            'name' => get_string('layoutonestat', 'block_dash'),
            'identifier' => one_stat_layout::class
        ],
        [
            'name' => get_string('layouttwostat', 'block_dash'),
            'identifier' => two_stat_layout::class
        ],
        [
            'name' => get_string('layouttimeline', 'block_dash'),
            'identifier' => timeline_layout::class
        ]
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
function local_dash_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'courseimage')) {
        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = 0;

        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $args).'/';
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
    global $PAGE, $CFG, $OUTPUT;
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

    if (($PAGE->bodyid == 'page-my-index'
        || substr($PAGE->bodyid, 0, 21) == 'page-totara-dashboard')
        && (is_siteadmin() || has_capability('local/dash:managedashboards', $PAGE->context))
        && $PAGE->user_is_editing()) {
        $currentbtn = $PAGE->button;
        $url = new moodle_url('/local/dash/dashboard_list.php');
        $currentbtn .= $OUTPUT->single_button($url, get_string('managedashboards', 'block_dash'));
        $PAGE->set_button($currentbtn);
    }
}

/**
 * Extend the course navigation, then added the course context dashboard link in secondary menu.
 *
 * @param \navigation_node $coursenode
 * @param stdclass $course
 * @param \context_course $coursecontext
 * @return void
 */
function local_dash_extend_navigation_course($coursenode, $course, $coursecontext) {
    global $PAGE, $USER, $DB;
    if ($PAGE->context instanceof \context_course ) {
        $context = $PAGE->context;
        if ($records = $DB->get_records('local_dash_dashboard', ['contextid' => $context->id, 'secondarynav' => 1])) {
            foreach ($records as $id => $record) {
                $dashboard = new \local_dash\model\dashboard($record->id);
                if ($dashboard->has_access($USER)) {
                    $url = new moodle_url('/local/dash/dashboard.php', array('id' => $record->id));
                    $node = navigation_node::create(
                        $record->name,
                        $url,
                        navigation_node::TYPE_SETTING, '',
                        $record->shortname, new pix_icon('i/dashboard', '')
                    );
                    $node->add_class('dash-course-dashboard');
                    $coursenode->add_node($node);
                    $nodes[] = $record->shortname;

                }
            }

            if (isset($nodes) && !empty($nodes)) {
                $PAGE->requires->js_amd_inline("
                    require(['jquery', 'core/moremenu'], function($, moremenu) {
                        window.onload=() => {
                            var secondarynav = document.querySelector('.secondary-navigation ul.nav-tabs');
                            secondarynav.querySelector('.nav-link.active').classList.remove('active');
                            var dashDashboard = document.querySelectorAll('.dash-course-dashboard');
                            dashDashboard.forEach((e) => {
                                e.classList.remove('dropdown-item');
                                e.classList.add('nav-link');
                                parent = e.parentNode;
                                parent.setAttribute('data-forceintomoremenu', 'false');
                                secondarynav.insertBefore(parent, secondarynav.children[1]);
                            })
                            moremenu(secondarynav);
                        }
                    })
                ");
            }
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
 * Create the user profile field.
 * @return void
 */
function local_dash_create_user_custom_fields() {
    global $DB;
    // Create a new profile field category.
    $category = $DB->get_record('user_info_category', ['name' => get_string('masonrycustomfield', 'block_dash')]);
    if (empty($category)) {
        $category = new stdClass();
        $category->name = get_string('masonrycustomfield', 'block_dash');
        $category->sortorder = $DB->count_records('user_info_category') + 1;
        $categoryid = $DB->insert_record('user_info_category', $category);
    } else {
        $categoryid = $category->id;
    }

    // Create profile fields.
    $records = local_dash_get_user_profile_fields();
    foreach ($records as $record) {
        $record = (object) $record;
        if (!$DB->record_exists('user_info_field', array('shortname' => $record->shortname))) {
            $record->action = 'editfield';
            $record->required = 0;
            $record->locked = 0;
            $record->forceunique = 0;
            $record->signup = 0;
            $record->visible = 2;
            $record->categoryid = $categoryid;
            $record->id = $DB->insert_record('user_info_field', $record);
            $field = $DB->get_record('user_info_field', array('id' => $record->id));
            \core\event\user_info_field_created::create_from_field($field)->trigger();
        }
    }
}
/**
 * Get user profile fields.
 * @return array
 */
function local_dash_get_user_profile_fields() {
    return array (
        [
            'datatype' => 'menu',
            'shortname' => 'gridsize',
            'name' => get_string('strgridsize', "block_dash"),
            'description' => '',
            'param1' => get_string('gridsizeoptions', 'block_dash'),
            'defaultdata' => 'Wide',
            'descriptionformat' => 1,
            'sortorder' => 1,
        ],
        [
            'datatype' => 'menu',
            'shortname' => 'promotion',
            'name' => get_string('strpromotion', "block_dash"),
            'description' => '',
            'param1' => get_string('promotionoptions', 'block_dash'),
            'defaultdata' => 'Featured',
            'descriptionformat' => 1,
            'sortorder' => 1,
        ],
        [
            'datatype' => 'text',
            'shortname' => 'cssclass',
            'name' => get_string('strcssclass', "block_dash"),
            'description' => '',
            'defaultdata' => '',
            'param1' => 30,
            'param2' => 2048,
            'param3' => 0,
            'descriptionformat' => 1,
            'sortorder' => 1,
        ],
    );
}


/**
 * Create the course customfields for the masonry layout.
 */
function local_dash_create_customfields() {
    global $DB;
    local_dash_create_user_custom_fields();
    $handler = \core_customfield\handler::get_handler('core_course', 'course', 0);
    if (!$category = $DB->get_record('customfield_category', array('name' => get_string('masonrycustomfield', "block_dash")))) {
        // Create category.
        $categoryid = $handler->create_category(get_string('masonrycustomfield', "block_dash"));
    } else {
        $categoryid = $category->id;
    }
    // Fetch the custom created category section.
    $category = \core_customfield\category_controller::create($categoryid);
    // Create the custom field in the custom category.
    $records = local_dash_import_customfields($categoryid);
    foreach ($records as $data) {
        $data = (object) $data;
        if (!$DB->record_exists('customfield_field', array('shortname' => $data->shortname))) {
            $field = \core_customfield\field_controller::create(0, (object)['type' => $data->type], $category);
            $handler = $field->get_handler();
            $data->submitbutton = get_string('savechanges');
            $data->description_editor = array(
                'text' => '',
                'format' => 1,
                'itemid' => 0
            );
            $data->configdata = array(
                'required' => 0,
                'options' => isset($data->options) ? $data->options : '',
                'uniquevalues' => 0,
                'defaultvalue' => $data->defaultvalue,
                'displaysize' => 50,
                'maxlength' => 1333,
                'ispassword' => 0,
                'link' => '',
                'locked' => 0,
                'visibility' => 2
            );
            $handler->save_field_configuration($field, $data);
        }
    }
}

/**
 * Get the course custom fields.
 * @param int $categoryid
 * @return array
 */
function local_dash_import_customfields($categoryid) {
    return [
        [
            'name' => get_string('strgridsize', "block_dash"),
            'shortname' => 'gridsize',
            'id' => '',
            'categoryid' => $categoryid,
            'type' => 'select',
            'options' => get_string('gridsizeoptions', 'block_dash'),
            'defaultvalue' => 'Wide'
        ],
        [
            'name' => get_string('strpromotion', "block_dash"),
            'shortname' => 'promotion',
            'id' => '',
            'categoryid' => $categoryid,
            'type' => 'select',
            'options' => get_string('promotionoptions', 'block_dash'),
            'defaultvalue' => 'Featured'
        ],
        [
            'name' => get_string('strcssclass', "block_dash"),
            'shortname' => 'cssclass',
            'id' => '',
            'categoryid' => $categoryid,
            'type' => 'text',
            'defaultvalue' => ''
        ],
    ];
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
