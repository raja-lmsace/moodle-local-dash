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
 * @package    dashaddon_learningpath
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade dashaddon_learningpath.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_dashaddon_learningpath_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024042500) {
        set_config('enabled', 1, 'dashaddon_learningpath');
        upgrade_plugin_savepoint(true, 2024042500, 'dashaddon', 'learningpath');
    }

    if ($oldversion < 2024100301) {
        // Define table dashaddon_learningpath_zones to be created.
        $table = new xmldb_table('dashaddon_learningpath_zones');

        // Adding fields to table dashaddon_learningpath_zones.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('blockid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('svgtype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zoneid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zonetype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table dashaddon_learningpath_zones.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('blockid', XMLDB_KEY_FOREIGN, ['blockid'], 'block_instances', ['id']);

        // Adding indexes to table dashaddon_learningpath_zones.
        $table->add_index('blockid_svgtype', XMLDB_INDEX_NOTUNIQUE, ['blockid', 'svgtype']);
        $table->add_index('zoneid_blockid', XMLDB_INDEX_UNIQUE, ['zoneid', 'blockid', 'svgtype']);

        // Conditionally launch create table for dashaddon_learningpath_zones.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Learning path savepoint reached.
        upgrade_plugin_savepoint(true, 2024100301, 'dashaddon', 'learningpath');
    }

    if ($oldversion < 2025101800) {
        // Define field zoneindex to be added to local_dash_learningpath_zones.
        $table = new xmldb_table('dashaddon_learningpath_zones');

        // Add zoneindex field if it doesn't exist.
        $field = new xmldb_field('zoneindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0', 'zonetype');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025101800, 'dashaddon', 'learningpath');
    }

    return true;
}
