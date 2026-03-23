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
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade dashaddon_activities.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_dashaddon_activities_upgrade($oldversion) {
    global $CFG;
    if ($oldversion < 2024042500) {
        require_once($CFG->dirroot . "/local/dash/addon/activities/lib.php");
        if (empty(dashaddon_activities_extend_added_dependencies())) {
            set_config('enabled', 1, 'dashaddon_activities');
        }
        upgrade_plugin_savepoint(true, 2024042500, 'dashaddon', 'activities');
    }
    return true;
}
