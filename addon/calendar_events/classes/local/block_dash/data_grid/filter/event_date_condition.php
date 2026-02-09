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
 * Filters results to specific event date condition status.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use core_date;
use DateTime;
use dml_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Filters results to specific event date condition status.
 *
 * @package dashaddon_calendar_events
 */
class event_date_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        return get_string('event:filterdate', 'block_dash');
    }

    /**
     * Add form fields for this filter (and any settings related to this filter.)
     *
     * @param moodleform $moodleform
     * @param MoodleQuickForm $mform
     * @param string $fieldnameformat
     */
    public function build_settings_form_fields(
        moodleform $moodleform,
        MoodleQuickForm $mform,
        $fieldnameformat = 'filters[%s]'
    ): void {

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'today' => get_string('filter:today', 'block_dash'),
            'upcoming' => get_string('filter:upcoming', 'block_dash'),
            'thisweek' => get_string('filter:thisweek', 'block_dash'),
            'thismonth' => get_string('filter:thismonth', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[eventdate]',
            get_string('event:filterdate', 'block_dash'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[eventdate]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {

        if (isset($this->get_preferences()['eventdate']) && is_array($this->get_preferences()['eventdate'])) {
            $types = $this->get_preferences()['eventdate'];

            $sql = [];
            $params = [];

            $timezone = core_date::get_user_timezone_object();
            foreach ($types as $key => $type) {
                $param2 = 'cedatec_endtime_' . $key;
                $param1 = 'cedatec_starttime_' . $key;

                switch ($type) {
                    case "upcoming":
                        $startoftomorrow = (new DateTime('tomorrow midnight', $timezone))->getTimestamp();

                        $params += [$param2 => $startoftomorrow];
                        $sql[] = "(ce.timestart >= :$param2) ";
                        break;

                    case "today":
                        $startoftoday = (new DateTime('today midnight', $timezone))->getTimestamp();
                        $endoftoday = (new DateTime('tomorrow midnight', $timezone))->getTimestamp();

                        $params += [$param1 => $startoftoday, $param2 => $endoftoday];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;

                    case "thismonth":
                        $startofthismonth = (new DateTime('first day of this month midnight', $timezone))->getTimestamp();
                        $endofthismonth = (new DateTime('last day of this month 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofthismonth, $param2 => $endofthismonth];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;

                    case "thisweek":
                        $startofthisweek = (new DateTime('monday this week', $timezone))->getTimestamp();
                        $endofthisweek = (new DateTime('sunday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofthisweek, $param2 => $endofthisweek - 1];
                        $sql[] = "(ce.timestart >= :$param1 AND ce.timestart <= :$param2)";
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
    }
}
