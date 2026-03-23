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
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade dashaddon_dashboard.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_dashaddon_dashboard_upgrade($oldversion) {
    global $CFG, $DB;
    require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");
    dashaddon_dashboard_create_core_dashboard();
    $dbman = $DB->get_manager();

    if ($oldversion < 2024050900) {
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('secondarynav', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'dashbgimage');
        $dbman->change_field_type($table, $field);
        upgrade_plugin_savepoint(true, 2024050900, 'dashaddon', 'dashboard');
    }

    if ($oldversion < 2025010300) {
        $table = new xmldb_table('dashaddon_dashboard_dash');

        // Add context type field.
        $field = new xmldb_field('contexttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'system');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add category ID field.
        $field = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add course ID field.
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field1 = new xmldb_field('includedblocks', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'dashbgimage');
        $field2 = new xmldb_field('displaydashboardtitle', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'includedblocks');
        $field3 = new xmldb_field('displaycta', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'displaydashboardtitle');
        $field4 = new xmldb_field('ctalink', XMLDB_TYPE_CHAR, '255', null, null, null, 'enrolment', 'displaycta');
        $field5 = new xmldb_field('ctacampaignid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'ctalink');
        $field6 = new xmldb_field('ctacustomurl', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'ctacampaignid');

        // Conditionally launch add field includedblocks.
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Conditionally launch add field displaydashboardtitle.
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Conditionally launch add field displaycta.
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        // Conditionally launch add field ctalink.
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        // Conditionally launch add field ctacampaignid.
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }

        // Conditionally launch add field ctacustomurl.
        if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }

        // Define fields to be added to dash_addon_dashboard table.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('ctacustomurltext', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'ctacustomurl');
        // Conditionally launch add field includedblocks.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define fields to be added to dash_addon_dashboard table.
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('redirecttodashboard', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'ctacustomurltext');
        // Conditionally launch add field includedblocks.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025010300, 'dashaddon', 'dashboard');
    }
    return true;
}
