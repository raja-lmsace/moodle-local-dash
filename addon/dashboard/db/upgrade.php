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
    require_once($CFG->dirroot."/local/dash/addon/dashboard/lib.php");
    $dbman = $DB->get_manager();

    if ($oldversion < 2024050202) {

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
        upgrade_plugin_savepoint(true, 2024050202, 'dashaddon', 'dashboard');
    }

    dashaddon_dashboard_create_core_dashboard();
    return true;
}
