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
 * Filters results to specific event day condition status.
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
 * Filters results to specific event day condition status.
 */
class event_day_condition extends condition {
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
        return get_string('event:filterday', 'block_dash');
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
            'monday' => get_string('filter:monday', 'block_dash'),
            'tuesday' => get_string('filter:tuesday', 'block_dash'),
            'wednesday' => get_string('filter:wednesday', 'block_dash'),
            'thursday' => get_string('filter:thursday', 'block_dash'),
            'friday' => get_string('filter:friday', 'block_dash'),
            'saturday' => get_string('filter:saturday', 'block_dash'),
            'sunday' => get_string('filter:sunday', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[eventday]',
            get_string('event:filterday', 'block_dash'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[eventday]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {

        if (isset($this->get_preferences()['eventday']) && is_array($this->get_preferences()['eventday'])) {
            $types = $this->get_preferences()['eventday'];

            $sql = [];
            $params = [];

            $timezone = core_date::get_user_timezone_object();
            foreach ($types as $key => $type) {
                $param1 = 'cedayc_startday_' . $key;
                $param2 = 'cedayc_endday_' . $key;

                switch ($type) {
                    case 'tuesday':
                        $startofday = (new DateTime('tuesday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('tuesday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'monday':
                        $startofday = (new DateTime('monday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('monday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'thursday':
                        $startofday = (new DateTime('thursday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('thursday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'wednesday':
                        $startofday = (new DateTime('wednesday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('wednesday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'friday':
                        $startofday = (new DateTime('friday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('friday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'sunday':
                        $startofday = (new DateTime('sunday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('sunday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;

                    case 'saturday':
                        $startofday = (new DateTime('saturday this week', $timezone))->getTimestamp();
                        $endofday = (new DateTime('saturday this week 23:59:59', $timezone))->getTimestamp();

                        $params += [$param1 => $startofday, $param2 => $endofday];
                        $sql[] = "ce.timestart >= :$param1 and ce.timestart <= :$param2";
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
    }
}
