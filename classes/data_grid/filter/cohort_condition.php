<?php
// This file is part of The Bootstrap Moodle theme
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
 * Parent role condition.
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;
use coding_exception;

/**
 * Parent role condition.
 */
class cohort_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_CUSTOM;
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

        return get_string('cohorts', 'block_dash');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        global $CFG;

        $cohortids = [];
        if (isset($this->get_preferences()['cohorts']) && is_array($this->get_preferences()['cohorts'])) {
            $cohortids = $this->get_preferences()['cohorts'];
            if (is_array($cohortids)) {
                foreach ($cohortids as $cohortid) {
                    $cohortids[] = $cohortid;
                }
            }
        }
        return $cohortids;
    }


    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function get_sql_and_params() {
        global $DB, $USER;

        $select = $this->get_select();
        $cohortids = $this->get_values();
        if ($cohortids) {
            [$cohortsql, $cohortparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED);
            $sql = "$select IN(SELECT cm.userid
                            FROM {cohort} ch
                            JOIN {cohort_members} cm ON cm.cohortid = ch.id
                            WHERE ch.id $cohortsql)";

            return [$sql, $cohortparams];
        }
        return false;
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

        $cohorts = $DB->get_records_menu("cohort", null, '', 'id,name');

        $select = $mform->addElement('select', $fieldname . '[cohorts]', '', $cohorts, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[cohorts]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }
}
