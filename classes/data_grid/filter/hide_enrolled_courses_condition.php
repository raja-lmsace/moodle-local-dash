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
 * Filters results to enrolled courses, optionally with specific role(s).
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
 * Filters results to enrolled courses, optionally with specific role(s).
 *
 * @package local_dash
 */
class hide_enrolled_courses_condition extends my_enrolled_courses_condition {
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

        return get_string('hidemycourses', 'block_dash');
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

        $sql = "$select NOT IN( SELECT ctx.instanceid
                           FROM {role_assignments} roa
                           JOIN {context} ctx ON ctx.id = roa.contextid AND ctx.contextlevel = " . CONTEXT_COURSE . "
                           WHERE roa.userid = :roauserid";

        $params = ['roauserid' => $USER->id];
        if (
            isset($this->get_preferences()['roleids'])
            && is_array($this->get_preferences()['roleids']) && count($this->get_preferences()['roleids']) > 0
        ) {
            [$rsql, $rparams] = $DB->get_in_or_equal($this->get_preferences()['roleids'], SQL_PARAMS_NAMED, 'rls');
            $sql .= " AND roa.roleid $rsql";
            $params = array_merge($params, $rparams);
        }

        $sql .= ')'; // Close subquery.

        return [$sql, $params];
    }
}
