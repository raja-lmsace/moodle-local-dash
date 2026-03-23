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
 * Filters results to specific calendar events date.
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
 * Filters results to specific calendar events date.
 */
class date_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $choices = [
            'today' => get_string('filter:today', 'block_dash'),
            'upcoming' => get_string('filter:upcoming', 'block_dash'),
            'thisweek' => get_string('filter:thisweek', 'block_dash'),
            'thismonth' => get_string('filter:thismonth', 'block_dash'),
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

        return get_string('event:filterdate', 'block_dash');
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

            $timezone = core_date::get_user_timezone_object();  // User timezone.
            foreach ($types as $key => $type) {
                $param1 = 'cedate_starttime_' . $key;
                $param2 = 'cedate_endtime_' . $key;

                switch ($type) {
                    case "today":
                        $startoftoday = (new DateTime('today midnight', $timezone))->getTimestamp();
                        $endoftoday = (new DateTime('tomorrow midnight', $timezone))->getTimestamp();

                        $params += [$param1 => $startoftoday, $param2 => $endoftoday];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;

                    case "upcoming":
                        $startoftomorrow = (new DateTime('tomorrow midnight', $timezone))->getTimestamp();

                        $params += [$param2 => $startoftomorrow];
                        $sql[] = "(ce.timestart >= :$param2) ";
                        break;

                    case "thisweek":
                        $startofthisweek = (new DateTime('monday this week', $timezone))->getTimestamp();
                        $endofthisweek = (new DateTime('sunday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofthisweek, $param2 => $endofthisweek - 1];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;

                    case "thismonth":
                        $startofthismonth = (new DateTime('first day of this month midnight', $timezone))->getTimestamp();
                        $endofthismonth = (new DateTime('last day of this month 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofthismonth, $param2 => $endofthismonth];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
        return false;
    }
}
