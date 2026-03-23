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
 * Get human readable "time ago"
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use DateTime;

/**
 * Get human readable "time ago".
 *
 * @package local_dash
 */
class timeago_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param string $data
     * @param \stdClass $record Entire row
     * @return mixed
     */
    public function transform_data($data, \stdClass $record) {

        if (is_numeric($data) && $data > 0) {
            $now = new DateTime();
            $ago = new DateTime('@' . $data);
            $diff = $now->diff($ago);

            $w = floor($diff->d / 7);
            $diff->d -= $w * 7;

            $string = [
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            ];
            foreach ($string as $k => &$v) {
                $diffvalue = (isset($$k)) ? $$k : $diff->$k;
                if ($diffvalue) {
                    $v = $diffvalue . ' ' . $v . ($diffvalue > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }

        return '-';
    }
}
