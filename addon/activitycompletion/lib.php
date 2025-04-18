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
 * Library functions defined for dashaddon activity completion.
 *
 * @package    dashaddon_activitycompletion
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register the activity completion as widget to dash.
 *
 * @return array List of widgets.
 */
function dashaddon_activitycompletion_register_widget(): array {
    return [
        [
            'name' => get_string('widget:activitycompletion', 'block_dash'),
            'identifier' => \dashaddon_activitycompletion\widget\activitycompletion_widget::class,
            'help' => 'widget:activitycompletion',
        ],
    ];
}
