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
 * @package    dashaddon_course_enrols
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace dashaddon_course_enrols\local\block_dash\data_grid\filter;

 use block_dash\local\data_grid\filter\select_filter;
 use block_dash\local\data_grid\filter\filter_collection_interface;


/**
 * Filters results to specific course completion status
 */
class completion_filter extends select_filter {
    use filter_element;

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $choices = [
            '' => get_string('status:all', 'block_dash'),
            'completed' => get_string('status:completed', 'block_dash'),
            'inprogress' => get_string('status:inprogress', 'block_dash'),
            'enrolled' => get_string('status:enrolled', 'block_dash'),
        ];
        foreach ($choices as $key => $option) {
            $this->options[$key] = $option;
        }
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
                'SELECT ue.courseid FROM (
                    SELECT
                        CASE WHEN (mcm.progress/cms.total) * 100 = 100 THEN \'completed\'
                            WHEN mcm.progress > 0 THEN \'inprogress\'
                            WHEN ue.timestart > 0 THEN \'enrolled\'
                            ELSE NULL
                            END AS status,
                            e.courseid AS courseid
                    FROM {user_enrolments} ue
                    LEFT JOIN {enrol} e ON ue.enrolid = e.id
                    LEFT JOIN (
                        SELECT count(*) AS total, course FROM {course_modules}
                        WHERE completion >= 1 GROUP BY course
                    ) cms ON cms.course = e.courseid
                    LEFT JOIN (
                        SELECT count(*) as progress, cm.course, mc.userid FROM {course_modules} cm
                        JOIN {course_modules_completion} mc ON mc.coursemoduleid = cm.id
                        WHERE mc.completionstate > 0 GROUP BY mc.userid, cm.course
                    ) mcm ON mcm.course = e.courseid AND mcm.userid = ue.userid
                WHERE ue.userid = :fueuserid
                ) ue WHERE ' . $sql,
                $params
            );

            $courses = array_column((array) $courses, 'courseid');
            [$insql, $inparams] = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'f', true, true);
            $sql = ' c.id ' . $insql;
        }
        return [$sql, $params + $inparams];
    }

    /**
     * Override this method and call it after creating a form element.
     *
     * @param filter_collection_interface $filtercollection
     * @param string $elementnameprefix
     * @throws \Exception
     * @return string
     */
    public function create_form_element(
        filter_collection_interface $filtercollection,
        $elementnameprefix = ''
    ) {
        $filter = $filtercollection->get_filter('c_status')->get_preferences();
        if (!empty($filter) && $filter['enabled']) {
            return $this->create_filter_element($filtercollection, $elementnameprefix);
        }
    }
}
