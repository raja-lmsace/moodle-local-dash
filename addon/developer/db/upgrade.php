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
 * Plugin upgrade code.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade db.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_dashaddon_developer_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020012303) {

        // Define table dash_custom_data_source to be created.
        $table = new xmldb_table('dash_custom_data_source');

        // Adding fields to table dash_custom_data_source.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('available_field_definitions', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('query_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('layout_type', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('layout_path', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('layout_mustache', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table dash_custom_data_source.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table dash_custom_data_source.
        $table->add_index('idnumber', XMLDB_INDEX_UNIQUE, ['idnumber']);

        // Conditionally launch create table for dash_custom_data_source.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Developer savepoint reached.
        upgrade_plugin_savepoint(true, 2020012303, 'dashaddon', 'developer');
    }

    if ($oldversion < 2020012400) {

        // Define field timecreated to be added to dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'layout_mustache');

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timemodified to be added to dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field usermodified to be added to dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'timemodified');

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Developer savepoint reached.
        upgrade_plugin_savepoint(true, 2020012400, 'dashaddon', 'developer');
    }

    if ($oldversion < 2020020400) {

        // Define table dash_custom_layout to be created.
        $table = new xmldb_table('dash_custom_layout');

        // Adding fields to table dash_custom_layout.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mustache_template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('supports_field_visibility', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('supports_filtering', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('supports_pagination', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('supports_sorting', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dash_custom_layout.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for dash_custom_layout.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Developer savepoint reached.
        upgrade_plugin_savepoint(true, 2020020400, 'dashaddon', 'developer');
    }

    if ($oldversion < 2020020600) {

        // Define field layout_type to be dropped from dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('layout_type');

        // Conditionally launch drop field layout_type.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field layout_path to be dropped from dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('layout_path');

        // Conditionally launch drop field layout_path.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field layout_mustache to be dropped from dash_custom_data_source.
        $table = new xmldb_table('dash_custom_data_source');
        $field = new xmldb_field('layout_mustache');

        // Conditionally launch drop field layout_mustache.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Developer savepoint reached.
        upgrade_plugin_savepoint(true, 2020020600, 'dashaddon', 'developer');
    }


    if ($oldversion < 2024042500) {
        set_config('enabled', 1, 'dashaddon_developer');
    }

    return true;
}
