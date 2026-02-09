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
 * Course enrolments library functions.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register the layouts this plugin contains.
 *
 * @return array List of layouts.
 */
function dashaddon_course_enrols_register_widget() {
    return [
        [
            'name' => get_string('widget:course_enrols', 'dashaddon_course_enrols'),
            'identifier' => dashaddon_course_enrols\widget\enrolments_widget::class,
            'help' => 'widget:course_enrols',
        ],
    ];
}

/**
 * Serve the user enrolment form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function dashaddon_course_enrols_output_fragment_user_enrolment_form($args) {
    global $CFG, $DB;

    $args = (object) $args;
    $context = $args->context;

    $ueid = $args->ueid;
    $userenrolment = $DB->get_record('user_enrolments', ['id' => $ueid], '*', MUST_EXIST);
    $instance = $DB->get_record('enrol', ['id' => $userenrolment->enrolid], '*', MUST_EXIST);
    $plugin = enrol_get_plugin($instance->enrol);
    $customdata = [
        'ue' => $userenrolment,
        'modal' => true,
        'enrolinstancename' => $plugin->get_instance_name($instance),
    ];

    // Set the data if applicable.
    $data = [];
    if (isset($args->formdata)) {
        $serialiseddata = json_decode($args->formdata);
        parse_str($serialiseddata, $data);
    }

    require_once("$CFG->dirroot/enrol/editenrolment_form.php");
    $mform = new \enrol_user_enrolment_form(null, $customdata, 'post', '', null, true, $data);

    if (!empty($data)) {
        $mform->set_data($data);
        $mform->is_validated();
    }

    return $mform->render();
}

/**
 * Returns list of courses user is enrolled into without performing any capability checks.
 *
 * The $fields param is a list of field names to ADD so name just the fields you really need,
 * which will be added and uniq'd.
 *
 * Cloned from enrollib enrol_get_all_users_courses
 *
 * @param int $userid User whose courses are returned, defaults to the current user.
 * @param bool $onlyactive Return only active enrolments in courses user may see.
 * @param string|null $sort Comma separated list of fields to sort by, defaults to respecting navsortmycoursessort.
 * @param string|null $status Course completion status based condition.
 * @param integer $limitfrom Limit the records from.
 * @param integer $limitnum Number of records needs to fetch.
 * @param string $condition Additional conditions.
 * @param array $conditionparams Additional condition params.
 * @return array list of courses and count of courses.
 */
function dashaddon_enrolments_get_all_users_courses(
    $userid,
    $onlyactive = false,
    $sort = null,
    $status = null,
    $limitfrom = 0,
    $limitnum = 5,
    $condition = '',
    $conditionparams = []
) {
    global $DB;

    // Guest account does not have any courses.
    if (isguestuser($userid) || empty($userid)) {
        return [];
    }

    $fields = ['*'];

    $orderby = "";
    if ($sort) {
        $sort    = trim($sort);
        $orderby = "ORDER BY $sort";
    }

    $params = ['siteid' => SITEID];

    if ($onlyactive) {
        $subwhere = "WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1
         AND (ue.timeend = 0 OR ue.timeend > :now2)";
        $params['now1']    = round(time(), -2); // Improves db caching.
        $params['now2']    = $params['now1'];
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;
    } else {
        $subwhere = "";
    }

    $completion = '';
    if ($status != null && $status != 'all') {
        $completion = " AND c.id IN (
            SELECT ue.courseid FROM (
                SELECT
                    CASE WHEN (mcm.progress/cms.total) * 100 = 100 THEN \"completed\"
                        WHEN mcm.progress > 0 THEN \"inprogress\"
                        WHEN ue.timestart !='' THEN \"enrolled\"
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
            WHERE ue.userid = :cueuserid
            ) ue WHERE ue.status = :completionstatus
        ) ";
        $params['completionstatus'] = $status;
        $params['cueuserid'] = $userid;
    }

    $coursefields = 'c.' . join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $ueselect = ', en.timestart as enroltimestart ';
    // Note: we can not use DISTINCT + text fields due to Oracle and MS limitations, 3
    // That is why we have the subselect there.
    $sql = "SELECT $coursefields $ccselect $ueselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid, MIN(ue.timestart) as timestart
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 $subwhere GROUP BY e.courseid
                   ) en ON (en.courseid = c.id)
            $ccjoin
            WHERE c.id <> :siteid $completion $condition
          $orderby ";

    $params['userid']  = $userid;
    $courses = $DB->get_records_sql($sql, $params + $conditionparams, $limitfrom, $limitnum);
    $countsql = "SELECT c.id
              FROM {course} c
              JOIN (SELECT e.courseid, MIN(ue.timestart) as timestart
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                 $subwhere GROUP BY e.courseid
                   ) en ON (en.courseid = c.id)
            $ccjoin
            WHERE c.id <> :siteid $completion $condition
          $orderby ";
    $count = $DB->get_records_sql($countsql, $params + $conditionparams);
    $count = count((array)$count);

    return [$courses, $count];
}
