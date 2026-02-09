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
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
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
class completion_status_condition extends condition {
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
    public function get_values() {
        if (isset($this->get_preferences()['completionstatus']) && is_array($this->get_preferences()['completionstatus'])) {
            $status = $this->get_preferences()['completionstatus'];
            return $status;
        }
        return [];
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
            'enrolled' => get_string('status:enrolled', 'block_dash'),
            'inprogress' => get_string('status:inprogress', 'block_dash'),
            'completed' => get_string('status:completed', 'block_dash'),
        ];

        $select = $mform->addElement('select', $fieldname . '[completionstatus]', '', $choices, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[completionstatus]', $fieldname . '[enabled]');
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

        [$sql, $params] = parent::get_sql_and_params();

        if ($sql) {
            $params['cueuserid'] = $USER->id;
            $courses = $DB->get_records_sql(
                "SELECT ue.courseid FROM (
                   SELECT
                        DISTINCT e.courseid,
                        CASE WHEN cc.timecompleted > 0 THEN 'completed'
                            WHEN cc.timestarted > 0 THEN 'inprogress'
                            ELSE 'enrolled'
                            END AS status
                    FROM {user_enrolments} ue
                    LEFT JOIN {enrol} e ON ue.enrolid = e.id
                    LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND ue.userid = cc.userid
                WHERE ue.userid = :cueuserid
                ) ue WHERE " . $sql,
                $params
            );

            $courses = array_column((array) $courses, 'courseid');

            [$insql, $inparams] = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'cs', true, true);
            $sql = ' c.id ' . $insql;
            $params = array_merge($params, $inparams);
        }

        return [$sql, $params];
    }
}
