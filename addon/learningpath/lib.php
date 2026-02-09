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
 * Library functions defined for skill graph widget.
 *
 * @package    dashaddon_learningpath
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_course\external\course_summary_exporter;

/**
 * Register the skill graph as widget to dash.
 *
 * @return array List of widgets.
 */
function dashaddon_learningpath_register_widget(): array {
    return [
        [
            'name' => get_string('widget:learningpath', 'block_dash'),
            'identifier' => dashaddon_learningpath\widget\learningpath_widget::class,
            'help' => 'widget:learningpath',
        ],
    ];
}

/**
 * Learningpath plugin file definitions, List of fileareas used in local_dash plugin.
 *
 * @param stdclass $course
 * @param stdclass $cm
 * @param stdclass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 */
function dashaddon_learningpath_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    $fileareas = [
        'desktop_learningpath',
        'tablet_learningpath',
        'mobile_learningpath',
    ];

    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, $fileareas) !== false) {
        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = array_shift($args);
        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }

        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'dashaddon_learningpath', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false; // The file does not exist.
        }

        // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
        send_stored_file($file, 86400, 0, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Get the course details to display in the details area modal.
 *
 * @param array $args
 * @return string
 */
function dashaddon_learningpath_output_fragment_course_details_area($args) {
    global $OUTPUT, $USER;

    $course = get_course($args['courseid']);

    $template = [];
    $template['coursename'] = format_string($course->fullname);
    $template['summary'] = $course->summary;
    $template['courseurl'] = new moodle_url('/course/view.php', ['id' => $course->id]);
    $template['courseimg'] = dashaddon_learningpath_courseimage($course->id);
    $template += dashaddon_learningpath_generate_completion_stats($course->id, $USER->id);

    // Get timetable assignment override information.
    $assignment = dashaddon_learningpath_get_assignment_override($course->id, $USER->id);
    if ($assignment && $args['sidebar']) {
        $template['hasassignment'] = true;
        $template['assignment'] = $assignment;
    }

    if (!$args['sidebar']) {
        $coursenavid = $args['isgrid'] ? "grid-course-" : "circle-course-";
        if ($args['prevcourse']) {
            $prevcourse = get_course($args['prevcourse']);
            $template['prevcourse'] = format_string($prevcourse->fullname);
            $template['prevcoursecircle'] = $coursenavid . $prevcourse->id;
        }

        if ($args['nextcourse']) {
            $nextcourse = get_course($args['nextcourse']);
            $template['nextcourse'] = format_string($nextcourse->fullname);
            $template['nextcoursecircle'] = $coursenavid . $nextcourse->id;
        }
    }

    return $OUTPUT->render_from_template('dashaddon_learningpath/course_details', $template);
}

/**
 * Get timetable assignment override information for a user and course.
 *
 * @param int $courseid Course ID
 * @param int $userid User ID
 * @return array|null Assignment override information or null if not found
 */
function dashaddon_learningpath_get_assignment_override($courseid, $userid) {
    global $DB;

    // Check if timetable tool plugin is installed and enabled.
    $pluginmanager = \core_plugin_manager::instance();
    $plugininfo = $pluginmanager->get_plugin_info('tool_timetable');

    if (!$plugininfo || $plugininfo->get_status() === \core_plugin_manager::PLUGIN_STATUS_MISSING) {
        return null;
    }

    // Check if timetable table exists.
    if (!$DB->get_manager()->table_exists('tool_timetable_course_overrides')) {
        return null;
    }

    $override = $DB->get_record_sql(
        "SELECT * FROM {tool_timetable_course_overrides}
         WHERE courseid = :courseid
           AND overridetype = 'user'
           AND userid = :userid
         ORDER BY timemodified DESC",
        ['courseid' => $courseid, 'userid' => $userid],
        IGNORE_MULTIPLE
    );

    if ($override) {
        return dashaddon_learningpath_format_assignment_data($override);
    }

    $usergroups = groups_get_user_groups($courseid, $userid);
    if (!empty($usergroups[0])) {
        [$insql, $inparams] = $DB->get_in_or_equal($usergroups[0], SQL_PARAMS_NAMED);

        $override = $DB->get_record_sql(
            "SELECT * FROM {tool_timetable_course_overrides}
             WHERE courseid = :courseid
               AND overridetype = 'group'
               AND groupid $insql
             ORDER BY timemodified DESC",
            array_merge(['courseid' => $courseid], $inparams),
            IGNORE_MULTIPLE
        );

        if ($override) {
            return dashaddon_learningpath_format_assignment_data($override);
        }
    }

    $userenrolments = dashaddon_learningpath_get_user_enrolments($courseid, $userid);
    if (!empty($userenrolments)) {
        $enrolids = array_column($userenrolments, 'enrolid');

        if (!empty($enrolids)) {
            [$insql, $inparams] = $DB->get_in_or_equal($enrolids, SQL_PARAMS_NAMED);

            $override = $DB->get_record_sql(
                "SELECT * FROM {tool_timetable_course_overrides}
                 WHERE courseid = :courseid
                   AND overridetype = 'enrolment'
                   AND enrolmentid $insql
                 ORDER BY timemodified DESC",
                array_merge(['courseid' => $courseid], $inparams),
                IGNORE_MULTIPLE
            );

            if ($override) {
                return dashaddon_learningpath_format_assignment_data($override);
            }
        }
    }

    return null;
}

/**
 * Get user enrolments for a course.
 *
 * @param int $courseid Course ID
 * @param int $userid User ID
 * @return array Array of enrolment records
 */
function dashaddon_learningpath_get_user_enrolments($courseid, $userid) {
    global $DB;

    $sql = "SELECT ue.id, ue.enrolid, e.enrol
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            WHERE e.courseid = :courseid
              AND ue.userid = :userid
              AND ue.status = :active";

    $enrolments = $DB->get_records_sql($sql, [
        'courseid' => $courseid,
        'userid' => $userid,
        'active' => ENROL_USER_ACTIVE,
    ]);

    return $enrolments ? array_values($enrolments) : [];
}

/**
 * Format assignment override data for display.
 *
 * @param object $override Override record from database
 * @return array Formatted assignment data
 */
function dashaddon_learningpath_format_assignment_data($override) {
    global $USER;
    if (!$override) {
        return null;
    }

    $assignment = [];

    // Assignment start date.
    if (!empty($override->assignstartdate)) {
        $assignment['startdate'] = userdate($override->assignstartdate, get_string('strftimedatefullshort', 'core_langconfig'));
        $assignment['startdatetimestamp'] = $override->assignstartdate;
    }

    // Assignment due date.
    if (!empty($override->duedate)) {
        $assignment['duedate'] = userdate($override->duedate, get_string('strftimedatefullshort', 'core_langconfig'));
        $assignment['duedatetimestamp'] = $override->duedate;
    } else {
        $timemanagement = new \tool_timetable\time_management($override->courseid);
        $usercourseenrollinfo = $timemanagement->get_course_user_enrollment($USER->id);
        $startdate = $usercourseenrollinfo[0]['timestart'] ?? 0;
        $enddate = $usercourseenrollinfo[0]['timeend'] ?? 0;
        $coursduedate = $timemanagement->get_user_course_due_date($startdate, $enddate, $USER->id);
        $assignment['duedate'] = userdate($coursduedate, get_string('strftimedatefullshort', 'core_langconfig'));
        $assignment['duedatetimestamp'] = $coursduedate;
    }

    // Assignment end date.
    if (!empty($override->assignenddate)) {
        $assignment['enddate'] = userdate($override->assignenddate, get_string('strftimedatefullshort', 'core_langconfig'));
        $assignment['enddatetimestamp'] = $override->assignenddate;
    }

    // Assignment type.
    if (isset($override->type)) {
        $assignment['type'] = $override->type;
        $assignment['typename'] = $override->type == 0
            ? get_string('assignment_mandatory', 'block_dash')
            : get_string('assignment_optional', 'block_dash');
    }

    // Assignment priority.
    if (isset($override->priority)) {
        $assignment['priority'] = $override->priority;
        switch ($override->priority) {
            case 1:
                $assignment['priorityname'] = get_string('assignment_priority_low', 'block_dash');
                $assignment['priorityclass'] = 'priority-low';
                break;
            case 3:
                $assignment['priorityname'] = get_string('assignment_priority_high', 'block_dash');
                $assignment['priorityclass'] = 'priority-high';
                break;
            default:
                $assignment['priorityname'] = get_string('assignment_priority_normal', 'block_dash');
                $assignment['priorityclass'] = 'priority-normal';
                break;
        }
    }

    // Assignment tags.
    if (!empty($override->tags)) {
        $overridetags = json_decode($override->tags);
        $trimmedtags = array_map('trim', $overridetags);
        $assignment['tags'] = [];
        foreach ($trimmedtags as $tag) {
            if (!empty($tag)) {
                $assignment['tags'][] = ['name' => $tag];
            }
        }
        $assignment['tagslist'] = implode(', ', $trimmedtags);
    }

    return !empty($assignment) ? $assignment : null;
}

/**
 * Generate the course completion report.
 *
 * @param int $courseid Course id
 * @param int $userid User id
 *
 * @return array course completion report.
 */
function dashaddon_learningpath_generate_completion_stats($courseid, $userid) {
    global $DB, $PAGE, $CFG, $USER;
    require_once($CFG->dirroot . '/enrol/locallib.php');
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/grade/lib.php');
    require_once($CFG->dirroot . '/grade/querylib.php');
    // Filter the disabled enrollments.
    $context = \context_course::instance($courseid);
    $course = get_course($courseid);
    $courseprogress = \core_completion\progress::get_course_progress_percentage($course);
    $courseprogress = $courseprogress ? round($courseprogress) : 0;
    $completion = new completion_info($course);
    $report['notstarted'] = ($courseprogress == 0) ? true : false;
    if ($DB->record_exists('course_completion_crit_compl', ['course' => $courseid, 'userid' => $userid])) {
        $report['inprogress'] = true;
    } else {
        $report['inprogress'] = ($courseprogress > 0) ? true : false;
    }
    $report['completed'] = ($completion->is_course_complete($userid)) ? true : false;
    if (!$DB->record_exists('course_completion_criteria', ['course' => $courseid]) && $courseprogress == 100) {
        $report['completed'] = true;
    }
    $report['progress'] = $courseprogress;
    $report['available'] = false;

    $now = time();

    if (!$report['completed'] && !$report['inprogress']) {
        $enrolled = is_enrolled($context, $userid);

        if (!$enrolled) {
            $instances = enrol_get_instances($course->id, true);
            $hasselfenrol = false;

            foreach ($instances as $instance) {
                if ($instance->enrol == 'self' && $instance->status == ENROL_INSTANCE_ENABLED) {
                    $hasselfenrol = true;
                    break;
                }
            }

            if ($hasselfenrol) {
                $report['available'] = true;
            } else {
                $report['unavailable'] = true;
            }
        }
    }

    $manager = new \course_enrolment_manager($PAGE, $course);
    $userenrolments = $manager->get_user_enrolments($USER->id);
    foreach ($userenrolments as $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            $report['unavailable'] = true;
        }
        if ($ue->timestart > $now || ($ue->timeend > 0 && $ue->timeend < $now)) {
            $report['unavailable'] = true;
        }
    }

    if (
        !$report['completed'] && $record = $DB->get_record(
            'course_completion_criteria',
            [
                'course' => $courseid,
                'criteriatype' => COMPLETION_CRITERIA_TYPE_GRADE,
            ]
        )
    ) {
        if (
            $record && dashaddon_learningpath_is_possible_failed($course, $userid) &&
            !$DB->record_exists(
                'course_completion_crit_compl',
                ['userid' => $userid, 'criteriaid' => $record->id, 'course' => $courseid]
            )
        ) {
            $report['failed'] = true;
        }
    }
    return $report;
}

/**
 * Check if course is possibly failed for user.
 *
 * @param object $course Course object
 * @param int $userid User ID
 * @return bool True if possibly failed
 */
function dashaddon_learningpath_is_possible_failed($course, $userid) {
    global $DB;
    $coursegradeitems = $DB->get_records('grade_items', ['courseid' => $course->id, 'itemtype' => 'mod']);
    foreach ($coursegradeitems as $item) {
        $sql = "SELECT * FROM {grade_grades} WHERE itemid = :itemid AND userid = :userid AND finalgrade IS NOT NULL";
        if (!$DB->record_exists_sql($sql, ['itemid' => $item->id, 'userid' => $userid])) {
            return false;
        }
    }
    return true;
}

/**
 * Get course image.
 *
 * @param int $courseid
 * @return mixed
 * @throws \moodle_exception
 */
function dashaddon_learningpath_courseimage($courseid) {
    global $DB, $CFG, $OUTPUT, $PAGE;

    require_once("$CFG->dirroot/course/lib.php");
    require_once($CFG->dirroot . "/blocks/dash/lib.php");

    if ($course = $DB->get_record('course', ['id' => $courseid])) {
        $context = context_course::instance($courseid);
        $exporter = new course_summary_exporter($course, ['context' => $context]);
        $list = $exporter->export($PAGE->get_renderer('core'));
        $nocoursesurl = $OUTPUT->image_url('courses', 'block_recentlyaccessedcourses')->out(false);
        return ($list->courseimage) ? $list->courseimage : $nocoursesurl;
    }

    return false;
}


/**
 * Get available list of all activity mask images.
 *
 * @param string $filearea
 * @return array $results List of mask images.
 */
function dashaddon_learningpath_get_all_learning_paths($filearea) {
    global $CFG;
    $results = [ 0 => get_string('none') ];
    require_once($CFG->libdir . '/filelib.php');
    $fs = get_file_storage();
    $learingpaths = $fs->get_area_files(
        \context_system::instance()->id,
        'dashaddon_learningpath',
        $filearea,
        0,
        '',
        false
    );

    foreach ($learingpaths as $path) {
        $results[$path->get_filename()] = ucwords(explode('.', $path->get_filename())[0]);
    }

    return $results;
}

/**
 * Get filename path for learning path file.
 *
 * @param string $filearea File area
 * @param string $filename Filename
 * @return string File path or empty string
 */
function dashaddon_learningpath_get_filename_path($filearea, $filename) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');
    $fs = get_file_storage();
    $file = $fs->get_file(
        \context_system::instance()->id,
        'dashaddon_learningpath',
        $filearea,
        0,
        '/',
        $filename
    );

    if ($file) {
        return $file;
    }

    return null;
}


/**
 * Serve fragment content.
 * @param array $args
 * @return string
 */
function dashaddon_learningpath_output_fragment_handler($args) {
    $method = isset($args['method']) ? $args['method'] : '';
    switch ($method) {
        case 'zone_config':
            return \dashaddon_learningpath\output\fragment::zone_config($args);
        default:
            return '';
    }
}
