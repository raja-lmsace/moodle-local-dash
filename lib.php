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

use local_dash\data_grid\filter\course_customfield_condition;


/**
 * Check if the customfield_multicategory plugin is installed.
 *
 * @return bool
 */
function local_dash_is_multicategory_installed() {
    return array_key_exists('multicategory', core_component::get_plugin_list('customfield'));
}

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
    // Layouts have been moved to block_dash. This function returns an empty array
    // to avoid duplicate registration. The local_dash layout classes are now thin
    // wrappers that extend the block_dash versions for backward compatibility.
    return [];
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

    if (
        $context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'courseimage' || $filearea === 'programbg')
        || $filearea === 'dashthumbnailimage' || $filearea === 'dashbgimage' || $filearea === 'calendareventsimage'
    ) {
        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = 0;

        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if (
            $filearea === 'programbg' || $filearea === 'dashthumbnailimage'
            || $filearea === 'dashbgimage' || $filearea === 'calendareventsimage'
        ) {
            $filepath = '/';
        } else {
            if (!$args) {
                $filepath = '/';
            } else {
                $filepath = '/' . implode('/', $args) . '/';
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
        if (isset($dashboard->id)) {
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
    }

    $hidecategory = get_config('local_dash', 'hidecoursecategory');
    if ($hidecategory && !is_siteadmin() && !has_capability('moodle/category:manage', context_system::instance())) {
        $redirecturl = new moodle_url('/my');
        $url = get_config('local_dash', 'courseredirecturl');
        $redirecturl = ($url != '') ? $url : $redirecturl;
        if ($PAGE->bodyid == 'page-course-index-category') {
            if ($url != '' && strpos($url, $CFG->wwwroot) !== false) {
                $redirecturl = new moodle_url($url);
            }
            redirect($redirecturl);
        }
    }

    $manager = \core_plugin_manager::instance();
    if (
        ($PAGE->bodyid == 'page-my-index'
        || substr($PAGE->bodyid, 0, 21) == 'page-totara-dashboard')
        && (is_siteadmin() || has_capability('local/dash:managedashboards', $PAGE->context))
        && $PAGE->user_is_editing()
    ) {
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
 * Get custom course fields.
 *
 * @return array Array of custom course fields
 */
function local_dash_get_custom_coursefields() {
    $fields = [0 => get_string('choose', 'local_dash')];
    if (class_exists('\core_course\customfield\course_handler')) {
        $coursehandler = \core_course\customfield\course_handler::create();
        foreach ($coursehandler->get_fields() as $customfield) {
            if ($customfield->get('type') === 'select') {
                $fields[$customfield->get('id')] = $customfield->get('name');
            }
        }
    } else {
        global $DB;
        foreach ($DB->get_records('course_info_field') as $customfield) {
            $fields[$customfield->id] = $customfield->fullname;
        }
    }

    return $fields;
}

/**
 * Get custom field options.
 *
 * @param int $fieldid Field ID
 * @return array Array of field options
 */
function local_dash_get_custom_field_options($fieldid) {
    $options = [0 => get_string('choose', 'local_dash')];
    if (empty($fieldid)) {
        return $options;
    }
    $field = \core_customfield\field_controller::create($fieldid);
    if ($field && $field->get('type') === 'select') {
        $configdata = $field->get_configdata_property('options');

        if (!empty($configdata)) {
            $lines = explode("\n", trim($configdata));
            foreach ($lines as $key => $val) {
                $val = trim($val);
                if ($val !== '') {
                    $options[$key + 1] = format_string($val);
                }
            }
        }
    }
    return $options;
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
            $icon = new \pix_icon($iconstr, "", $component);
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
    global $DB, $CFG;
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
    if (function_exists('dashaddon_dashboard_change_pagetypepattern')) {
        require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");
        dashaddon_dashboard_change_pagetypepattern();
    } else {
        $likepattern = $DB->sql_like('pagetypepattern', ':pattern');
        $sql = "SELECT *
                FROM {block_instances}
                WHERE {$likepattern}";
        $params = [
            'pattern' => 'local-dash-dashboard%',
        ];

        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $pagetypepattern = $record->pagetypepattern;
            $prefix = 'local-dash';
            $replacement = 'dashaddon';
            $modifiypattern = str_replace($prefix, $replacement, $pagetypepattern);
            $record->pagetypepattern = $modifiypattern;
            $DB->update_record('block_instances', $record);
        }
    }
}



/**
 * Helper function to build the map of FA icons to be used in the smart menu item icon autocomplete setting.
 * It returns both the Moodle core icon mappings and all other available FontAwesome icons.
 *
 * @return array An array which holds the full icon map.
 */
function local_dash_build_fa_icon_map() {
    global $CFG, $PAGE;
    // Check if we have the icon map in the cache.
    $cache = \cache::make('local_dash', 'fontawesomeicons');
    $iconmap = $cache->get('iconmap');

    // If the icon map is already in the cache, return it.
    if ($iconmap !== false) {
        return $iconmap;
    }

    // Initialize icon map if not in cache.
    $iconmap = [];

    // Step 1: Get all Moodle core icon mappings.

    // Load the theme config.
    $theme = \core\output\theme_config::load($PAGE->theme->name);

    // Get the FA system.
    $faiconsystem = \core\output\icon_system_fontawesome::instance($theme->get_icon_system());

    // Get the raw icon map.
    $iconmapraw = $faiconsystem->get_core_icon_map();

    // Iterate over the raw icon map.
    foreach ($iconmapraw as $iconname => $faname) {
        // Fill the icon into the icon list.
        $iconmap[$iconname] = [
            'class' => $faname,
            'source' => 'core',
        ];
    }

    // Define the FontAwesome variables file path first.
    $variablesfile = $CFG->dirroot . '/theme/boost/scss/fontawesome/_variables.scss';

    // If the variables file exists.
    if (file_exists($variablesfile)) {
        // Read the variables file content.
        $content = file_get_contents($variablesfile);

        // Step 2: Add all available FontAwesome solid icons from $fa-icons array.

        // Extract the $fa-icons section using a quite simple approach.
        // Find the beginning of $fa-icons array.
        $faiconsstart = strpos($content, '$fa-icons:');
        if ($faiconsstart !== false) {
            // Find the end of $fa-icons array (right before $fa-brand-icons starts).
            $fabrandstart = strpos($content, '$fa-brand-icons:', $faiconsstart);
            if ($fabrandstart !== false) {
                // Extract just the $fa-icons section.
                $faiconsection = substr($content, $faiconsstart, $fabrandstart - $faiconsstart);

                // Extract all icon names from the $fa-icons array with a simple pattern.
                preg_match_all('/"([a-z0-9\-]+)"/', $faiconsection, $solidmatches);

                // If we found any icon names.
                if (!empty($solidmatches[1])) {
                    // Process the icons.
                    foreach ($solidmatches[1] as $iconname) {
                        $fasolidclass = 'fa-' . $iconname;

                        // Add icon to the icon map, ignoring the fact by purpose that the icon could already be there from core.
                        $iconmap['local_dash:fa-' . $iconname] = [
                            'class' => $fasolidclass,
                            'source' => 'fasolid',
                        ];
                    }
                }
            }
        }

        // Step 3: Add all available FontAwesome brand icons from $fa-brand-icons array.

        // Find the beginning of $fa-brand-icons array.
        $fabrandstart = strpos($content, '$fa-brand-icons:');
        if ($fabrandstart !== false) {
            // Extract the $fa-brand-icons section.
            $fabrandsection = substr($content, $fabrandstart);

            // Extract all brand icon names from the $fa-brand-icons array with a simple pattern.
            preg_match_all('/"([a-z0-9\-]+)"/', $fabrandsection, $brandmatches);

            // If we found any brand icon names.
            if (!empty($brandmatches[1])) {
                // Process the brand icons.
                foreach ($brandmatches[1] as $brandname) {
                    $fabrandclass = 'fa-' . $brandname;

                    // Add brand icon to the icon map.
                    $iconmap['local_dash:fa-' . $brandname] = [
                        'class' => $fabrandclass,
                        'source' => 'fabrand',
                    ];
                }
            }
        }
    }

    // Sort the icons array by key.
    asort($iconmap);

    // Step 4: Add the blank FontAwesome icon to the very beginning of the icon map.
    // This icon is not contained in the FontAwesome variables file, but should be usable as smart menu item.
    $blankicon = [
        'class' => 'fa-fw',
        'source' => 'fablank',
    ];
    $iconmap = ['local_dash:fa-fw' => $blankicon] + $iconmap;

    // Store the icon map in cache for future requests.
    $cache->set('iconmap', $iconmap);

    // Return icon map.
    return $iconmap;
}



/**
 * Map icons for font-awesome themes.
 * This function is only processed when the Moodle cache is cleared and not on every page load.
 * That's why we created the local_dash_reset_fontawesome_icon_map function and call it everytime a smart menu item
 * is saved with an icon.
 */
function local_dash_get_fontawesome_icon_map() {
    // Init icon mapping with icons which are included in any case.
    $iconmapping = [
        'local_dash:info' => 'fa-info-circle',
    ];

    // Get the FontAwesome icons which are used by smart menus currently.
    $faicons = local_dash_get_all_fa_icons();

    // Get the list of all Font Awesome icons.
    $allicons = local_dash_build_fa_icon_map();

    // Process the icons one by one.
    foreach ($faicons as $i) {
        // Determine the fa class.
        $faclass = str_replace('local_dash:', '', $i);

        // Append known icon source.
        if ($allicons[$i]['source'] == 'fasolid') {
            $faclass .= ' fas';
        } else if ($allicons[$i]['source'] == 'fabrand') {
            $faclass .= ' fab';
        }

        // Add the icon to the mapping.
        $iconmapping[$i] = $faclass;
    }

    // Return.
    return $iconmapping;
}

/**
 * Get all Font Awesome icons used in custom visual icons.
 *
 * @return array Array of FontAwesome icon identifiers
 */
function local_dash_get_all_fa_icons() {
    global $DB;

    // Define the query to search for icons in the config_plugins table.
    $sql = "SELECT DISTINCT value
            FROM {config_plugins}
            WHERE plugin = 'local_dash'
              AND " . $DB->sql_like('name', ':pattern') . "
              AND value IS NOT NULL
              AND value != ''";

    // Get the icons from the database.
    $icons = $DB->get_fieldset_sql($sql, ['pattern' => 'customvisualicon_%']);

    // Drop non-FA icons.
    $icons = array_filter($icons, function ($icon) {
        // Check if the icon is a Font Awesome icon.
        return (strpos($icon, 'local_dash:fa-') === 0);
    });

    return $icons;
}

/**
 * Reset Font Awesome icon map cache.
 *
 * @return void
 */
function local_dash_reset_fontawesome_icon_map() {
    $instance = \core\output\icon_system::instance(\core\output\icon_system::FONTAWESOME);
    $cache = \cache::make('core', 'fontawesomeiconmapping');
    $mapkey = 'mapping_' . preg_replace('/[^a-zA-Z0-9_]/', '_', get_class($instance));
    $cache->delete($mapkey);
    // And rebuild it brutally.
    $instance->get_icon_name_map();
}
