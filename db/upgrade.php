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
 * DB authentication plugin upgrade code
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade local_dash.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_dash_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    require_once($CFG->dirroot . '/local/dash/lib.php');

    if ($oldversion < 2019112402) {
        // Define table dash_data_source to be created.
        $datasourcetable = new xmldb_table('dash_data_source');
        // Adding fields to table dash_data_source.
        $datasourcetable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $datasourcetable->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $datasourcetable->add_field('idnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $datasourcetable->add_field('available_field_definitions', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $datasourcetable->add_field('query_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $datasourcetable->add_field('layout_type', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $datasourcetable->add_field('layout_path', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $datasourcetable->add_field('layout_mustache', XMLDB_TYPE_TEXT, null, null, null, null, null);
        // Adding keys to table dash_data_source.
        $datasourcetable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        // Adding indexes to table dash_data_source.
        $datasourcetable->add_index('idnumber', XMLDB_INDEX_UNIQUE, ['idnumber']);
        // Conditionally launch create table for dash_data_source.
        if (!$dbman->table_exists($datasourcetable)) {
            $dbman->create_table($datasourcetable);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2019112402, 'local', 'dash');
    }
    if ($oldversion < 2019121200) {
        // Define table dash_dashboard to be created.
        $table = new xmldb_table('dash_dashboard');
        // Adding fields to table dash_dashboard.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table dash_dashboard.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        // Adding indexes to table dash_dashboard.
        $table->add_index('contextid', XMLDB_INDEX_NOTUNIQUE, ['contextid']);
        // Conditionally launch create table for dash_dashboard.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2019121200, 'local', 'dash');
    }
    if ($oldversion < 2019121201) {
        // Define field timecreated to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'contextid');
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field timemodified to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');
        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field usermodified to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'timemodified');
        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2019121201, 'local', 'dash');
    }
    if ($oldversion < 2019121302) {
        // Define field permission to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('permission', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'usermodified');
        // Conditionally launch add field permission.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field cohort_id to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('cohort_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'permission');
        // Conditionally launch add field cohort_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2019121302, 'local', 'dash');
    }
    if ($oldversion < 2019121700) {
        // Define field shortname to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'cohort_id');
        // Conditionally launch add field shortname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Set unique values for shortname before adding index.
        $DB->execute('UPDATE {dash_dashboard} SET shortname = MD5(concat(name, id))');
        // Define index shortname (unique) to be added to dash_dashboard.
        $table = new xmldb_table('dash_dashboard');
        $index = new xmldb_index('shortname', XMLDB_INDEX_UNIQUE, ['shortname']);
        // Conditionally launch add index shortname.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2019121700, 'local', 'dash');
    }
    if ($oldversion < 2020012300) {
        // Remove old custom data source table. Moved to dashaddon_developer.
        if ($dbman->table_exists('dash_data_source')) {
            $table = new xmldb_table('dash_data_source');
            $dbman->drop_table($table);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2020012300, 'local', 'dash');
    }
    if ($oldversion < 2020101400) {
        // Data source classes have moved to `local` namespace. Update all instances of Dash that use a class name as
        // the data source idnumber.
        foreach ($DB->get_records('block_instances', ['blockname' => 'dash']) as $record) {
            $instance = block_instance('dash', $record);
            if (isset($instance->config->data_source_idnumber)) {
                $instance->config->data_source_idnumber = str_replace(
                    'local_dash\\data_source',
                    'local_dash\\local\\block_dash',
                    $instance->config->data_source_idnumber
                );
                $instance->instance_config_save($instance->config);
            }
        }
        upgrade_plugin_savepoint(true, 2020101400, 'local', 'dash');
    }
    if ($oldversion < 2021122300) {
        $table = new xmldb_table('dash_dashboard');
        $dbman->rename_table($table, 'local_dash_dashboard');
        upgrade_plugin_savepoint(true, 2021122300, 'local', 'dash');
    }

    if ($oldversion < 2022011903) {
        $table = new xmldb_table('local_dash_dashboard');
        $field = new xmldb_field('secondarynav', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'shortname');
        // Conditionally launch add field shortname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2022011903, 'local', 'dash');
    }

    if ($oldversion < 2023101406) {
        // Dash savepoint reached.
        $table = new xmldb_table('local_dash_dashboard');
        $dashaddondashboard = new xmldb_table('dashaddon_dashboard_dash');
        if ($dbman->table_exists($table) && !$dbman->table_exists($dashaddondashboard)) {
            $dbman->rename_table($table, 'dashaddon_dashboard_dash');
        }

        // Define field description to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'shortname');
        // Conditionally launch add field description.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field description format to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('descriptionformat', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'description');
        // Conditionally launch add field description format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field description to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('coredash', XMLDB_TYPE_INTEGER, '2', null, null, null, 0, 'secondarynav');
        // Conditionally launch add field cohort_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field dash icon to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('dashicon', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'descriptionformat');
        // Conditionally launch add field dashicon.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field dash icon to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('dashthumbnailimage', XMLDB_TYPE_INTEGER, '15', null, null, null, null, 'dashicon');
        // Conditionally launch add field dashicon.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field dash icon to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('dashbgimage', XMLDB_TYPE_INTEGER, '15', null, null, null, null, 'dashthumbnailimage');
        // Conditionally launch add field dashicon.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remove the role_id field.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('role_id');
        // Conditionally launch drop field role_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field roles to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('roles', XMLDB_TYPE_TEXT, null, null, null, null, null, 'cohort_id');
        // Conditionally launch add field roles.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field rolecontext to be added to dash_dashboard.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('rolecontext', XMLDB_TYPE_INTEGER, '9', null, null, null, null, 'roles');
        // Conditionally launch add field rolecontext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if ($dashboardrecord = $DB->get_record('dashaddon_dashboard_dash', ['shortname' => 'coredashboard'])) {
            $dashboardrecord->name = get_string('maindashboard', 'block_dash');
            $dashboardrecord->permission = 'public';
            $DB->update_record('dashaddon_dashboard_dash', $dashboardrecord);
        }
        upgrade_plugin_savepoint(true, 2024040428, 'local', 'dash');
    }

    if ($oldversion < 2024050304) {
        local_dash_upgrade_blocks_data_source_idnumber();
        // Dash savepoint reached.
        upgrade_plugin_savepoint(true, 2024050304, 'local', 'dash');
    }

    return true;
}
