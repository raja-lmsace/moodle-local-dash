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
 * Convert a duration in seconds into a human-readable format (days, hours, and minutes).
 *
 * @package    local_dash
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use DateTime;

/**
 * Convert a duration in seconds into a human-readable format (days, hours, and minutes).
 *
 * @package local_dash
 */
class duration_attribute extends abstract_field_attribute {
    /**
     * Convert a duration in seconds into a human-readable format (days, hours, and minutes).
     *
     * Takes a duration in seconds and converts it to a string format showing the
     * number of days, hours, and minutes.
     *
     * @param mixed $data The duration in seconds.
     * @param \stdClass $record The record object.
     * @return string|null The formatted duration as a human-readable string, or null if the duration is invalid.
     */
    public function transform_data($data, \stdClass $record) {

        if (is_numeric($data) && $data > 0) {
            // Define the number of seconds in a minute, hour, and day.
            $secondsinaminute = 60;
            $secondsinanhour = 60 * $secondsinaminute;
            $secondsinaday = 24 * $secondsinanhour;

            // Extract the number of days from the total duration.
            $days = floor($data / $secondsinaday);

            // Extract the remaining seconds after accounting for days, and convert to hours.
            $hourseconds = $data % $secondsinaday;
            $hours = floor($hourseconds / $secondsinanhour);

            // Extract the remaining seconds after accounting for hours, and convert to minutes.
            $minuteseconds = $hourseconds % $secondsinanhour;
            $minutes = floor($minuteseconds / $secondsinaminute);

            // Format and return.
            $timeparts = [];
            $sections = [
                'day' => (int)$days,
                'hour' => (int)$hours,
                'minute' => (int)$minutes,
            ];

            foreach ($sections as $name => $value) {
                if ($value > 0) {
                    $timeparts[] = $value . ' ' . $name . ($value == 1 ? '' : 's');
                }
            }
            // Return the formatted duration as a comma-separated string.
            return implode(', ', $timeparts);
        }

        return '-';
    }
}
