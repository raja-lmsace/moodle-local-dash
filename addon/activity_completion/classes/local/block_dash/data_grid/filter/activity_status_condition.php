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
 * Filters results to specific activity completion and date based condition..
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;


/**
 * Filters results to specific activity completion and date based condition.
 *
 * @package dashaddon_activity_completion
 */
class activity_status_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get activity status condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('status', 'dashaddon_activity_completion');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['activitystatus']) && is_array($this->get_preferences()['activitystatus'])) {
            $status = $this->get_preferences()['activitystatus'];
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

        $status = [
            'open' => get_string('open', 'dashaddon_activity_completion'),
            'due' => get_string('due', 'dashaddon_activity_completion'),
            'overdue' => get_string('overdue', 'dashaddon_activity_completion'),
            'complete' => get_string('complete', 'dashaddon_activity_completion'),
            'late' => get_string('late', 'dashaddon_activity_completion'),
        ];

        $select = $mform->addElement('select', $fieldname . '[activitystatus]', '', $status, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[activitystatus]', $fieldname . '[enabled]');
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

        if (is_array($params)) {
            $conditionsql = [];
            foreach ($params as $key => $status) {
                switch ($status) {
                    case 'open':
                        $conditionsql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) > :now_$key + 86000) AND
                                cmc.completionstate = 0";
                        $params += ['now_' . $key => time()];
                        break;
                    case 'due':
                        $conditionsql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) <= :now_$key + 86000) AND
                                cmc.completionstate = 0 ";
                        $params += ['now_' . $key => time(), 'now1_' . $key => time()];
                        break;
                    case 'overdue':
                        $conditionsql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) <= :now_$key) AND
                                cmc.completionstate = 0 ";
                        $params += ['now_' . $key => time()];
                        break;
                    case 'complete':
                        $conditionsql[] = "(cmc.completionstate <> 0 AND (cmc.timemodified <=
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d'))))";
                        break;
                    case 'late':
                        $conditionsql[] = "(cmc.completionstate <> 0 AND STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d') IS NOT NULL AND
                                cmc.timemodified >= UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')))";
                        break;
                }
            }
            return ['(' . implode(' OR ', $conditionsql) . ')', $params];
        }
        return false;
    }
}
