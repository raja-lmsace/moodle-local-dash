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
    dashaddon_dashboard_create_core_dashboard();
    $dbman = $DB->get_manager();

    if ($oldversion < 2024050900) {
        $table = new xmldb_table('dashaddon_dashboard_dash');
        $field = new xmldb_field('secondarynav', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'dashbgimage');
        $dbman->change_field_type($table, $field);
        upgrade_plugin_savepoint(true, 2024050900, 'dashaddon', 'dashboard');
    }

    return true;
}
