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
        return get_string('coursedates', 'block_dash');
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
        global $DB, $CFG;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'past' => get_string('coursedata:past', 'block_dash'),
            'present' => get_string('coursedate:present', 'block_dash'),
            'future' => get_string('coursedate:future', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[coursedates]',
            get_string('coursedates', 'block_dash'),
            $choices,
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
                        $sql[] = "(c.enddate <> 0 AND c.enddate < :cdc_now_$key)";
                        $params += ['cdc_now_' . $key => time()];
                        break;
                    case 'present':
                        $sql[] = "(c.startdate < :cdc_startdate_$key AND ( c.enddate = 0 OR c.enddate > :cdc_enddate_$key) )";
                        $params += ['cdc_enddate_' . $key => time(), 'cdc_startdate_' . $key => time()];
                        break;
                    case 'future':
                        $sql[] = "(c.startdate > :cdc_now_$key)";
                        $params += ['cdc_now_' . $key => time()];
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
    }
}
