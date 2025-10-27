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
            $filepath = '/'.implode('/', $args).'/';
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

    return $OUTPUT->render_from_template('dashaddon_learningpath/course_details', $template);
}

/**
 * Generate the course completion report.
 *
 * @param int $courseid Course id
 * @param int $userid User id
 *
 * @return array course completion report.
 */
function  dashaddon_learningpath_generate_completion_stats($courseid, $userid) {
    global $DB, $PAGE, $CFG, $USER;
    require_once($CFG->dirroot . '/enrol/locallib.php');
    // Filter the disabled enrollments.
    $context = \context_course::instance($courseid);
    $course = get_course($courseid);
    $courseprogress = \core_completion\progress::get_course_progress_percentage($course);
    $courseprogress = $courseprogress ? round($courseprogress) : 0;
    $report['completed'] = ($courseprogress == 100) ? true : false;
    $report['inprogress'] = ($courseprogress != 100 && $courseprogress > 0) ? true : false;
    $report['notstarted'] = ($courseprogress == 0) ? true : false;
    $report['progress'] = $courseprogress;
    $now = time();

    if (($course->startdate && $course->startdate > $now) || ($course->enddate && $course->enddate < $now)
        || !is_enrolled($context, $userid)) {
        $report['unavailable'] = true;
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
    return $report;
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
    require_once($CFG->dirroot. "/blocks/dash/lib.php");

    if ($course = $DB->get_record('course', ['id' => $courseid])) {

        $context = context_course::instance($courseid);
        $exporter = new course_summary_exporter($course, ['context' => $context]);
        $list = $exporter->export($PAGE->get_renderer('core'));
        $nocoursesurl = $OUTPUT->image_url('courses', 'block_recentlyaccessedcourses')->out(false);
        return ($list->courseimage) ? $list->courseimage : $nocoursesurl;

    }

    return false;
}
