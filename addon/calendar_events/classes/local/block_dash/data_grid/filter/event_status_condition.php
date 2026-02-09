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
 * Filters results to specific calendar event current available status like past/preset/future.
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
 * Filters results to specific calendar event current available status like past/preset/future.
 */
class event_status_condition extends condition {
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
        return get_string('eventstatus', 'block_dash');
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
            'past' => get_string('coursedata:past', 'block_dash'),
            'present' => get_string('coursedate:present', 'block_dash'),
            'future' => get_string('coursedate:future', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[eventstatus]',
            get_string('eventstatus', 'block_dash'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[eventstatus]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {

        if (isset($this->get_preferences()['eventstatus']) && is_array($this->get_preferences()['eventstatus'])) {
            $dates = $this->get_preferences()['eventstatus'];
            $sql = [];
            $params = [];

            $starttimefield = 'ces.timestart';
            $endtimefield = 'ces.endtime';

            $timezone = core_date::get_user_timezone_object();
            $now = (new DateTime('now', $timezone))->getTimestamp();

            foreach ($dates as $key => $date) {
                $param1 = 'cestac_start_' . $key;
                $param2 = 'cestac_end_' . $key;

                switch ($date) {
                    case 'past':
                        $params += [$param1 => $now];
                        $sql[] = "(ces.timeduration > 0 AND $endtimefield < :$param1)";
                        break;

                    case 'present':
                        $params += [$param1 => $now, $param2 => $now];
                        $sql[] = "($starttimefield <= :$param1 AND ($endtimefield >= :$param2))";
                        break;

                    case 'future':
                        $params += [$param1 => $now];
                        $sql[] = "($starttimefield > :$param1)";
                        break;
                }
            }

            $combinedsql = '(' . implode(' OR ', $sql) . ')';

            $insql = "ce.id IN (
                SELECT ces.id FROM (
                    SELECT cess.id, cess.timestart, cess.timeduration, (cess.timestart + cess.timeduration) AS endtime
                    FROM mdl_event cess
                ) AS ces WHERE $combinedsql
            )";

            return [$insql, $params];
        }
    }
}
