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
 * Convert the event type into event icon classname.
 *
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute\event;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use core\event\base;
use core\event\course_module_viewed;
use core\event\course_viewed;
use local_dash\local\dash_framework\events\events_info;
use logstore_standard\log\store;

/**
 * Transforms data to plugin name of course format.
 *
 * @package local_dash
 */
class event_icon_attribute extends event_object_attribute
{
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param  string    $data
     * @param  \stdClass $record Entire row
     * @return mixed
     */
    public function transform_data($data, \stdClass $record) {
        global $OUTPUT;

        if ($event = parent::transform_data($data, $record)) {
            $icon = null;

            switch ($event->crud) {
                case 'c':
                    $icon = ['core', 't/add'];
                    break;
                case 'r':
                    $icon = ['core', 'i/hide'];
                    break;
                case 'u':
                    $icon = ['core', 'i/edit'];
                    break;
                case 'd':
                    $icon = ['core', 't/delete'];
                    break;
            }

            switch ($event->action) {
                case 'created':
                case 'enrolled':
                    $icon = ['core', 'i/enrolusers'];
                    break;
                case 'completed':
                    $icon = ['core', 'i/completion_self'];
                    break;
                case 'viewed':
                    $icon = ['core', 'i/hide'];
                    break;
                case 'assigned':
                    $icon = ['core', 'i/assignroles'];
                    break;
                case 'unassigned':
                case 'deleted':
                case 'unenrolled':
                    $icon = ['core', 't/delete'];
                    break;
                case 'updated':
                    $icon = ['core', 'i/edit'];
                    break;
            }

            if ($icon) {
                if (block_dash_is_totara()) {
                    // Convert to flex icon output.
                    return $OUTPUT->flex_icon($icon[1] . ':' . $icon[0]);
                } else {
                    return $OUTPUT->pix_icon($icon[1], '', $icon[0]);
                }
            }
        }

        return '';
    }
}
