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
 * Filters results to specific course completion status
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filters results to specific course completion status
 */
class completion_status_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $choices = [
            'enrolled' => get_string('status:enrolled', 'block_dash'),
            'inprogress' => get_string('status:inprogress', 'block_dash'),
            'completed' => get_string('status:completed', 'block_dash'),
        ];
        $this->add_options($choices);
        parent::init();
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
        $inparams = [];
        if ($sql) {
            $params['fueuserid'] = $USER->id;

            $courses = $DB->get_records_sql(
                "SELECT ue.courseid FROM (
                    SELECT
                        DISTINCT ue.userid,
                        CASE WHEN cc.timecompleted > 0 THEN 'completed'
                            WHEN cc.timestarted > 0 THEN 'inprogress'
                            ELSE 'enrolled'
                            END AS status,
                        e.courseid AS courseid
                    FROM {user_enrolments} ue
                    LEFT JOIN {enrol} e ON ue.enrolid = e.id
                    LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND ue.userid = cc.userid
                WHERE ue.userid = :fueuserid
                ) ue WHERE " . $sql,
                $params
            );

            $courses = array_column((array) $courses, 'courseid');
            [$insql, $inparams] = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'f', true, true);
            $sql = ' c.id ' . $insql;
        }
        return [$sql, $params + $inparams];
    }
}
