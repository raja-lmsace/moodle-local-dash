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
 * Transforms site log record ID to event object.
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute\event;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use core\event\course_module_viewed;
use core\event\course_viewed;
use local_dash\local\dash_framework\events\events_info;
use logstore_standard\log\store;

/**
 * Transforms site log record ID to event object.
 *
 * @package local_dash
 */
class event_object_attribute extends abstract_field_attribute {
    /**
     * List of Events data.
     *
     * @var array
     */
    public static $events = [];

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param int $data Site log Record ID.
     * @param \stdClass $record Entire row
     * @return mixed
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;

        if (!isset(self::$events[$data])) {
            $logstore = new store(get_log_manager());
            if ($record = $DB->get_record('logstore_standard_log', ['id' => $data])) {
                self::$events[$data] = $logstore->get_log_event($record);
            } else {
                self::$events[$data] = null;
            }
        }

        return self::$events[$data];
    }
}
