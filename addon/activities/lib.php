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
 * Library functions defined for dashaddon content widget.
 *
 * @package    dashaddon_activities
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use dashaddon_activities\local\block_dash\data_grid\filter\activity_customfield_condition;

/**
 * Checks if the Timetable plugin is installed and enabled.
 *
 * @return bool True if the Timetable plugin is installed, false otherwise.
 */
function dashaddon_activities_is_timetable_installed() {
    global $CFG;
    static $result;

    if ($result == null) {
        if (array_key_exists('timetable', \core_component::get_plugin_list('tool'))) {
            require_once($CFG->dirroot . '/admin/tool/timetable/classes/time_management.php');
            $result = true;
        } else {
            $result = false;
        }
    }

    return $result;
}

/**
 * Checks if the designer plugin is installed.
 *
 * @return bool True if the Dash designer plugin is installed, false otherwise.
 */
function dashaddon_activities_is_designer_installed() {
    return array_key_exists('designer', core_component::get_plugin_list('format'));
}

/**
 * Checks if the designer_pro plugin is installed.
 *
 * @return bool True if the Dash designer plugin is installed, false otherwise.
 */
function dashaddon_activities_is_designer_pro_installed() {
    return array_key_exists('designer', core_component::get_plugin_list('local'));
}

/**
 * Get module user duedate.
 *
 * @param object $mod
 * @param int $userid
 * @return int|bool Mod due date if available otherwiser returns false.
 */
function dashaddon_activities_get_mod_user_duedate($mod, $userid) {
    global $DB;
    $course = $mod->get_course();
    $record = $DB->get_record('tool_timetable_modules', ['cmid' => $mod->id ?? 0]);
    $timemanagement = new \tool_timetable\time_management($course->id);
    $userenrolments = $timemanagement->get_course_user_enrollment($userid, $course->id);
    if (!empty($userenrolments)) {
        $timestarted = $userenrolments[0]['timestart'] ?? 0;
        $timeended = $userenrolments[0]['timeend'] ?? 0;
        if ($record) {
            $moduledates = $timemanagement->calculate_coursemodule_managedates($record, $timestarted, $timeended);
            $duedate = $moduledates['duedate'] ?? false;
        }
    }
    return $duedate ?? false;
}

/**
 * Get the activities and resources modules.
 *
 * @return void
 */
function dashaddon_activities_get_resources_activities() {
    global $DB, $CFG;
    // Get list of all resource-like modules.
    $allmodules = $DB->get_records('modules');
    $resources = [];
    $activities = [];
    foreach ($allmodules as $module) {
        $modname = $module->name;
        $archetype = plugin_supports('mod', $modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
        if ($archetype == MOD_ARCHETYPE_RESOURCE) {
            $resources[] = $modname;
        } else {
            $activities[] = $modname;
        }
    }
    return [$activities, $resources];
}

/**
 * Checks if a metadata plugin is available or not.
 *
 * @return void
 */
function dashaddon_activities_is_local_metadata_installed() {
    return array_key_exists('metadata', core_component::get_plugin_list('local'));
}

/**
 * Added the course module metedata fields to the activities datasource.
 *
 * @param [object] $filter
 * @return void
 */
function dashaddon_activities_customfield_conditions($filter) {
    global $DB;

    if (!dashaddon_activities_is_local_metadata_installed()) {
        return false;
    }

    $modulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
    foreach ($modulefields as $field) {
        if (!in_array($field->datatype, ['menu', 'text'])) {
            continue;
        }
        $alias = $field->shortname;
        $select = $alias . '.data';
        $filter->add_filter(new activity_customfield_condition($alias, $select, format_string($field->name)));
    }
}

/**
 * Get the module purposes.
 *
 * @param [array] $purposes
 * @return array
 */
function dashaddon_activities_get_purpose_module($purposes) {
    global $DB;
    $values = [];
    $modules = $DB->get_records_menu('modules', null, '', 'id,name');
    foreach ($modules as $module) {
        $modpurpose = ucfirst(plugin_supports('mod', $module, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER));
        if (in_array($modpurpose, $purposes)) {
            $values[] = $module;
        }
    }
    return $values;
}

/**
 * Get the designer course modules purposes.
 *
 * @param [array] $purposes
 * @return array
 */
function dashaddon_activities_get_designer_purpose($purposes) {
    global $DB;
    $values = [];
    $modules = $DB->get_records('modules');
    foreach ($modules as $module) {
        $modpurpose = get_config('local_designer', 'purpose_' . $module->name);
        if (in_array($modpurpose, $purposes)) {
            $values[] = $module->name;
        }
    }
    return $values;
}

/**
 * The require plugin dependencies added for the soft dependencies in the activities dash addon.
 *
 * @return string
 */
function dashaddon_activities_extend_added_dependencies() {
    global $OUTPUT;
    $manager = \core_plugin_manager::instance();
    $dependencies = [
        'dashaddon_courses',
        'dashaddon_categories',
    ];
    foreach ($dependencies as $dependency) {
        $plugin = $manager->get_plugin_info($dependency);
        if (!$plugin) {
            return $OUTPUT->render_from_template('dashaddon_activities/upgrade', ['plugin' => $dependency]);
        } else if ($plugin->get_status() === core_plugin_manager::PLUGIN_STATUS_MISSING) {
            return $OUTPUT->render_from_template('dashaddon_activities/upgrade', ['plugin' => $dependency]);
        }
    }
    return '';
}
