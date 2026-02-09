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
 * Filters results to current category only.
 *
 * @package    dashaddon_learningpath
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace dashaddon_learningpath\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;

/**
 * Filters results to prerequisites of the current course.
 *
 * @package    dashaddon_learningpath
 */
class current_course_prerequisites_condition extends condition {
    /**
     * Return WHERE SQL and params for placeholders.
     *
     * @return array|false
     * @throws \dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        $courseid = 0;

        // If we are inside a course context, get the current course id.
        if ($coursecontext = $this->get_context()->get_course_context(false)) {
            $courseid = $coursecontext->instanceid;
        } else if ($this->get_context()->contextlevel == CONTEXT_COURSE) {
            $courseid = $this->get_context()->instanceid;
        }

        if ($courseid) {
            $records = $DB->get_records(
                'course_completion_criteria',
                ['criteriatype' => COMPLETION_CRITERIA_TYPE_COURSE, 'course' => $courseid],
                '',
                'courseinstance'
            );

            $prerequisitecourseids = array_map(function ($r) {
                return $r->courseinstance;
            }, $records);

            $prerequisitecourseids = array_unique($prerequisitecourseids);

            if (!empty($prerequisitecourseids)) {
                [$insql, $inparams] = $DB->get_in_or_equal($prerequisitecourseids, SQL_PARAMS_NAMED, 'prereq');
                $sql = " c.id $insql ";
                return [$sql, $inparams];
            }
        }

        return false;
    }

    /**
     * Get condition label.
     *
     * @return string
     */
    public function get_label() {
        return get_string('currentcourse', 'block_dash');
    }
}
