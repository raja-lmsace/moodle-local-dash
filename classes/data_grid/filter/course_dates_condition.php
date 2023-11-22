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
 * Filters results to specific course completion status.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;
/**
 * Filters results to specific course completion status.
 *
 * @package local_dash
 */
class course_dates_condition extends condition {

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
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('status');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_value() {
        if (isset($this->get_preferences()['coursedates']) && is_array($this->get_preferences()['coursedates'])) {
            $status = $this->get_preferences()['coursedates'];
            switch ($status) {
                case 'past':
                    $sql = 'c.enddate <> 0 AND c.enddate < :now';
                    $params = ['now' => time()];

                    break;
                case 'present':
                    $sql = 'c.startdate < :startdate AND ( c.enddate == 0 OR c.enddate > :endtime)';
                    $params = ['enddate' => time(), 'startdate' => time()];
                    break;
                case 'future':
                    $sql = 'c.startdate > :now';
                    $params = ['now' => time()];
                    break;
            }
            return [$sql, $params];
        }

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
        $fieldnameformat = 'filters[%s]'): void {
        global $DB, $CFG;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'past' => get_string('coursedata:past', 'block_dash'),
            'present' => get_string('coursedate:present', 'block_dash'),
            'future' => get_string('coursedate:future', 'block_dash'),
        ];

        $select = $mform->addElement('select', $fieldname . '[coursedates]',
            get_string('coursedates', 'block_dash'), $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[coursedates]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        if (isset($this->get_preferences()['coursedates']) && is_array($this->get_preferences()['coursedates'])) {
            $dates = $this->get_preferences()['coursedates'];
            $sql = [];
            $params = [];
            foreach ($dates as $key => $date) {
                switch ($date) {
                    case 'past':
                        $sql[] = "c.enddate <> 0 AND c.enddate < :now_$key";
                        $params += ['now_'.$key => time()];
                        break;
                    case 'present':
                        $sql[] = "(c.startdate < :startdate_$key AND ( c.enddate = 0 OR c.enddate > :enddate_$key) )";
                        $params += ['enddate_'.$key => time(), 'startdate_'.$key => time()];
                        break;
                    case 'future':
                        $sql[] = "c.startdate > :now_$key";
                        $params += ['now_'.$key => time()];
                        break;
                }
            }

            return ['('.implode(' OR ', $sql).')', $params];
        }
        return false;
    }
}
