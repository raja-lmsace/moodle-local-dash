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
 * Filters results to specific sections.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;

/**
 * Filters results to specific sections.
 *
 * @package local_dash
 */
class users_mycohort_condition extends condition {
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

        return get_string('users_mycohort', 'block_dash');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {

        if (isset($this->get_preferences()['enabled']) && ($this->get_preferences()['enabled'])) {
            return true;
        }
        return false;
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \Exception
     */
    public function get_sql_and_params() {
        global $DB, $USER;

        $sql = ''; // SQL Query.
        $inparams = []; // IN params.

        $mycohort = $this->get_values();

        if (!$mycohort) {
            return [$sql, $inparams];
        }

        $select = $this->get_select();
        $cohortids = $DB->get_records_menu("cohort_members", ['userid' => $USER->id], '', 'id,cohortid');
        if ($cohortids) {
            [$cohortsql, $cohortparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED);
            $sql = "$select IN(SELECT cm.userid
                            FROM {cohort} ch
                            JOIN {cohort_members} cm ON cm.cohortid = ch.id
                            WHERE ch.id $cohortsql)";
            return [$sql, $cohortparams];
        }
        $sql = "$select = :userid";
        return [$sql, ['userid' => 0]];
    }
}
