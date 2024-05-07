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
 * Handle the local dash before the uninstall of local dash.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to uninstall local_dash.
 *
 * @return bool result
 */
function xmldb_local_dash_uninstall() {
    global $DB;

    $pagetypepattern = $DB->sql_like('pagetypepattern', ':pattern');
    $conditions = [
        "pattern" => "dashaddon-dashboard-%",
    ];
    $DB->delete_records_select('block_instances', $pagetypepattern, $conditions);
}
