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
 * Filters results to specific calendar events days.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use core_date;
use DateTime;

/**
 * Filters results to specific calendar events days (Days of the week).
 */
class day_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     */
    public function init() {
        global $DB;

        $choices = [
            'monday' => get_string('filter:monday', 'block_dash'),
            'tuesday' => get_string('filter:tuesday', 'block_dash'),
            'wednesday' => get_string('filter:wednesday', 'block_dash'),
            'thursday' => get_string('filter:thursday', 'block_dash'),
            'friday' => get_string('filter:friday', 'block_dash'),
            'saturday' => get_string('filter:saturday', 'block_dash'),
            'sunday' => get_string('filter:sunday', 'block_dash'),

        ];
        $this->add_options($choices);

        parent::init();
    }

    /**
     * Get filter config label.
     *
     * @return string
     */
    public function get_label() {

        return get_string('event:filterday', 'block_dash');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {

        [$sql, $types] = parent::get_sql_and_params();

        if (is_array($types)) {
            $sql = [];
            $params = [];

            // User timezone.
            $timezone = core_date::get_user_timezone_object();
            foreach ($types as $key => $type) {
                $param1 = 'ceday_startday_' . $key;
                $param2 = 'ceday_endday_' . $key;

                switch ($type) {
                    case 'monday':
                        $startofday = (new DateTime('monday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('monday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'tuesday':
                        $startofday = (new DateTime('tuesday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('tuesday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'wednesday':
                        $startofday = (new DateTime('wednesday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('wednesday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'thursday':
                        $startofday = (new DateTime('thursday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('thursday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'friday':
                        $startofday = (new DateTime('friday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('friday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'saturday':
                        $startofday = (new DateTime('saturday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('saturday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'sunday':
                        $startofday = (new DateTime('sunday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('sunday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
        return false;
    }
}
