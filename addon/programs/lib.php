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
 * @package    dashaddon_programs
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The require plugin dependencies added for the soft dependencies in the programs dash addon.
 *
 * @return string
 */
function dashaddon_programs_extend_added_dependencies() {
    global $OUTPUT;
    $manager = \core_plugin_manager::instance();
    $plugin = $manager->get_plugin_info('enrol_programs');
    if (!$plugin) {
        return $OUTPUT->render_from_template('dashaddon_programs/upgrade', []);
    } else if ($plugin->get_status() === core_plugin_manager::PLUGIN_STATUS_MISSING) {
        return $OUTPUT->render_from_template('dashaddon_programs/upgrade', []);
    }
    return '';
}
