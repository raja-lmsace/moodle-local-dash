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
 * Filters results to specific activity completion status.
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
 * Filters results to specific activity completion status.
 *
 * @package dashaddon_activity_completion
 */
class activity_completion_status_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get activity completion status condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('cmstatus', 'dashaddon_activity_completion');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['cmcompletionstatus']) && is_array($this->get_preferences()['cmcompletionstatus'])) {
            $status = $this->get_preferences()['cmcompletionstatus'];
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
            'completed' => get_string('status:completed', 'block_dash'),
            'notcompleted' => get_string('status:notcompleted', 'block_dash'),
        ];

        $select = $mform->addElement('select', $fieldname . '[cmcompletionstatus]', '', $choices, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[cmcompletionstatus]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for the activity completion status.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        [$sql, $params] = parent::get_sql_and_params();

        if ($sql) {
            $filterparams = $this->get_values();

            if (count($filterparams) > 1) {
                return false;
            }

            foreach ($filterparams as $status) {
                if ($status == 'completed') {
                    $sql = '(cmc.completionstate IS NOT NULL
                        AND cmc.completionstate <> 0 AND cm.deletioninprogress = 0 AND cm.visible = 1)';
                } else {
                    $sql = '(cmc.completionstate IS NULL OR cmc.completionstate = 0 AND cm.deletioninprogress = 0 AND
                        cm.visible = 1) ';
                }
            }
            return [$sql, $params];
        }
        return false;
    }
}
