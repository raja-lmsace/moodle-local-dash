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
 * Enable plugin for new install.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Enable this plugin for new installs
 * @return bool
 */
function xmldb_dashaddon_activity_completion_install() {
    global $CFG;
    require_once($CFG->dirroot . "/local/dash/addon/activity_completion/lib.php");
    if (empty(dashaddon_activity_completion_extend_added_dependencies())) {
        set_config('enabled', 1, 'dashaddon_activity_completion');
    }
    return true;
}
