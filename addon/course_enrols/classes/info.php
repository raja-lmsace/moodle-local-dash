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
 * Fetch and process the course informations for the current user.
 *
 * @package     dashaddon_course_enrols
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols;

use dashaddon_course_enrols\output\status_field;
use moodle_url;
use pix_icon;

/** Edit enrolment action. */
define('COURSE_ENROLS_ACTION_EDIT', 'editenrolment');

/** Unenrol action. */
define('COURSE_ENROLS_ACTION_UNENROL', 'unenrol');

/**
 * Fetch and process the course informations for the current user. Also works based on selected filters.
 */
class info {
    /**
     * Course format instance object
     *
     * @var object
     */
    public $format;

    /**
     * Course instance.
     *
     * @var object
     */
    public $course;

    /**
     * Completion info object.
     *
     * @var object
     */
    public $completioninfo;

    /**
     * Course context.
     *
     * @var context_course
     */
    public $context;

    /**
     * User id looking for.
     *
     * @var int
     */
    public $userid;

    /**
     * Setup the course and course completion.
     *
     * @param stdclass $course
     * @param int|null $userid
     */
    public function __construct($course, $userid = null) {
        global $USER;

        $this->format = course_get_format($course);
        $this->course = $this->format->get_course();
        $this->completioninfo = new \completion_info($this->course);
        $this->context = \context_course::instance($this->course->id);
        $this->userid = ($userid) ? $userid : $USER->id;
    }

    /**
     * Get sql for selected sort menu.
     *
     * @return void
     */
    public static function get_sort_sql() {
        return [
            'enrolmentdate_asc' => 'en.timestart ASC',
            'enrolmentdate_desc' => 'en.timestart DESC',
            'alpha_asc' => 'c.fullname ASC',
            'alpha_desc' => 'c.fullname DESC',
            'coursestartdate_asc' => 'c.startdate ASC',
            'coursestartdate_desc' => 'c.startdate DESC',
        ];
    }

    /**
     * Get sort filter menus options.
     *
     * @return array
     */
    public static function get_sorting_menus() {
        return [
            'enrolmentdate_asc' => get_string('enroldate_asc', 'block_dash'),
            'enrolmentdate_desc' => get_string('enroldate_desc', 'block_dash'),
            'alpha_asc' => get_string('alpha_asc', 'block_dash'),
            'alpha_desc' => get_string('alpha_desc', 'block_dash'),
            'coursestartdate_asc' => get_string('coursestartdate_asc', 'block_dash'),
            'coursestartdate_desc' => get_string('coursestartdate_desc', 'block_dash'),
        ];
    }

    /**
     * Check the user has capability to use the enrolment options.
     *
     * @param string $capability
     * @param int $courseid
     * @return bool
     */
    public static function has_capability($capability, $courseid = null) {
        global $USER;
        $user = self::get_related_userid();
        return has_capability($capability, \context_system::instance()) ||
            ($courseid && has_capability($capability, \context_course::instance($courseid))) ||
            ($user && has_capability($capability, \context_user::instance($user)) );
    }

    /**
     * Get list of users assigned to the current user.
     *
     * @return void
     */
    public static function get_mentess_user() {
        global $DB, $USER;

        if (
            $usercontexts = $DB->get_records_sql("SELECT c.instanceid, c.instanceid
                                                FROM {role_assignments} ra, {context} c, {user} u
                                                WHERE ra.userid = ?
                                                        AND ra.contextid = c.id
                                                        AND c.instanceid = u.id
                                                        AND c.contextlevel = " . CONTEXT_USER, [$USER->id])
        ) {
            $users = [];
            foreach ($usercontexts as $usercontext) {
                $userid = $usercontext->instanceid;
                $user = \core_user::get_user($userid);
                $users[] = ['name' => fullname($user), 'id' => $user->id];
            }
            return $users;
        }
        return [];
    }

    /**
     * Get current page user. if block added in the profile page then the current profile user is releate user
     * Otherwise logged in user is current user.
     *
     * @return int $userid
     */
    public static function get_related_userid() {
        global $PAGE, $USER;

        if ($PAGE->pagelayout == 'mypublic') {
            $userid = optional_param('id', 0, PARAM_INT);
        }

        return isset($userid) && $userid ? $userid : $USER->id;       // Owner of the page.
    }

    /**
     * Get course completion criteria for completion of other courses.
     *
     * @param int $endsection
     * @return array $course List of all available other completion courses.
     */
    public function get_course_criteria($endsection = 0) {
        $criterias = $this->completioninfo->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE);
        foreach ($criterias as $criteria) {
            $course = get_course($criteria->courseinstance);
            if ($course) {
                $endsection++;
                $modinfo = get_fast_modinfo($course, $this->userid);
                $courseinfo = new self($modinfo->get_course());
                $sections = self::get_sections($modinfo, $courseinfo, $endsection);
                foreach ($sections as $section) {
                    $course->cms[] = ['cm' => $section, 'iscompleted' => $section->iscompleted];
                }
                $course->sectionnum = $endsection;
                $course->iscompleted = $courseinfo->completioninfo->is_course_complete($this->userid);
                $course->name = format_string($course->fullname);
                $courses[] = $course;
            }
        }

        return isset($courses) ? $courses : [];
    }

    /**
     * Get course sections and course modules with completion details.
     *
     * @param modinfo $modinfo
     * @param info $courseinfo
     * @return void
     */
    public static function get_sections($modinfo, $courseinfo) {
        $sectionsnum = 0;
        $sections = [];
        foreach ($modinfo->get_section_info_all() as $sectionnum => $thissection) {
            $section = new \stdClass();
            $section->iscompleted = $courseinfo->get_section_completion($thissection->id);
            $section->name = $courseinfo->format->get_section_name($thissection);
            // Added support for multiple available info in a section.
            if (
                $thissection->availableinfo && is_object($thissection->availableinfo)
                && isset($thissection->availableinfo->items)
            ) {
                $availableitems = $thissection->availableinfo->items;
                $availableinfo = \html_writer::start_tag('ul');
                foreach ($availableitems as $item) {
                    $availableinfo .= \html_writer::tag('li', $item);
                }
                $availableinfo = \html_writer::end_tag('ul');
                $section->availableinfo = $availableinfo;
            }
            $cms = [];
            if (isset($modinfo->sections[$thissection->section])) {
                foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                    $mod = clone $modinfo->cms[$modnumber];
                    $iscompleted = $courseinfo->get_module_completion($mod);
                    $cms[] = ['cm' => $mod, 'cmname' => format_string($mod->name), 'iscompleted' => $iscompleted];
                }
            }
            $section->cms = $cms;
            if ($cms) {
                $section->sectionnum = $sectionsnum += 1;
                $sections[] = $section;
            }
        }
        return $sections;
    }

    /**
     * Get list of user enroled courses with completion progress and completion of other course.
     *
     * @param int|null $userid
     * @param string $sort
     * @param string $status
     * @param int|null $limitfrom
     * @param int|null $limitnum
     * @param string $condition
     * @param array $conditionparams
     * @return array
     */
    public static function get_courses_list(
        $userid = null,
        string $sort = "alpha_asc",
        string $status = "all",
        $limitfrom = null,
        $limitnum = null,
        $condition = null,
        $conditionparams = []
    ) {

        global $CFG, $USER, $DB;
        require_once($CFG->dirroot . '/lib/enrollib.php');
        require_once($CFG->dirroot . '/course/renderer.php');
        require_once($CFG->dirroot . '/enrol/locallib.php');

        $userid = ($userid) ? $userid : $USER->id;
        $sortqueries = self::get_sort_sql();
        $sort = isset($sortqueries[$sort]) ? $sortqueries[$sort] : '';

        $alluserscourses = dashaddon_enrolments_get_all_users_courses(
            $userid,
            false,
            $sort,
            $status,
            $limitfrom,
            $limitnum,
            $condition,
            $conditionparams
        );

        if (empty($alluserscourses)) {
            return [[], 0];
        }

        [$courses, $count] = $alluserscourses;

        foreach ($courses as $courseid => &$course) {
            $modinfo = get_fast_modinfo($course, $userid);
            $courseinfo = new self($modinfo->get_course(), $userid);

            $course->fullname = format_string($course->fullname);
            $course->sections = self::get_sections($modinfo, $courseinfo);
            $endsection = (!empty($course->sections)) ? end($course->sections)->sectionnum : 0;
            $course->sections = array_merge($course->sections, $courseinfo->get_course_criteria($endsection));

            $category = (class_exists('\core_course_category'))
            ? \core_course_category::get($course->category, IGNORE_MISSING) : \coursecat::get($course->category, IGNORE_MISSING);
            $course->courseimage = $courseinfo->courseimage();
            $course->categoryname = ($category && isset($category->name)) ? format_string($category->name) : '';
            $courseelement = (class_exists('\core_course_list_element'))
            ? new \core_course_list_element($course) : new \course_in_list($course);
            $summary = (new \coursecat_helper($course))->get_course_formatted_summary($courseelement);
            $course->summary = shorten_text($summary, 200);
            $course->courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $user = \core_user::get_user($userid);
            $course->menu = $courseinfo->enrolment_menus($user);
            $course->context = \context_course::instance($course->id)->id;
        }

        return [$courses, $count];
    }

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @return mixed
     * @throws \moodle_exception
     */
    public function courseimage() {
        global $DB, $CFG, $OUTPUT;

        require_once("$CFG->dirroot/course/lib.php");

        if ($course = $DB->get_record('course', ['id' => $this->course->id])) {
            if (block_dash_is_totara()) {
                $image = course_get_image($course->id)->out();
            } else {
                $image = \core_course\external\course_summary_exporter::get_course_image($course);
            }

            if ($image == '') {
                $courseimage = get_config('local_dash', 'courseimage');
                if ($courseimage != '') {
                    $image = moodle_url::make_pluginfile_url(
                        \context_system::instance()->id,
                        'local_dash',
                        'courseimage',
                        null,
                        null,
                        $courseimage
                    );
                }
            }
            $nocoursesurl = $OUTPUT->image_url('courses', 'block_recentlyaccessedcourses')->out(false);
            return ($image) ? $image : $nocoursesurl;
        }

        return false;
    }

    /**
     * Find all the modules inside the given sections are completed by the logged in user.
     * If result is not true it will return the progress and current completion details of section.
     *
     * @param int $sectionid Section id.
     * @return bool|array Result of section completion or Current progress data.
     */
    public function get_section_completion($sectionid) {
        global $DB;

        $completionstatus = true;
        $completionlist = 0;
        $completioninfo = new \completion_info($this->course);
        if ($sectionid) {
            $modinfo = get_fast_modinfo($this->course);
            $section = $DB->get_record('course_sections', ['id' => $sectionid]);
            if (isset($section)) {
                if (isset($modinfo->sections[$section->section])) {
                    foreach ($modinfo->sections[$section->section] as $modnumber) {
                        $module = $modinfo->cms[$modnumber];
                        $completiondata = $completioninfo->get_data($module, false, $this->userid);
                        if (!$completioninfo->is_enabled($module)) {
                            continue;
                        }
                        switch ($completiondata->completionstate) {
                            case COMPLETION_COMPLETE:
                            case COMPLETION_COMPLETE_FAIL:
                            case COMPLETION_COMPLETE_PASS:
                                break;
                            default:
                                $completionstatus = false;
                        }
                        $completionlist += 1;
                    }
                }
            }
        }

        return ($completionlist > 0 ) ? $completionstatus : false;
    }

    /**
     * Get Module completion status for the course.
     *
     * @param cm_info $module
     * @return bool
     */
    public function get_module_completion($module) {
        $completioninfo = new \completion_info($this->course);
        if ($completioninfo->is_enabled($module)) {
            $completiondata = $completioninfo->get_data($module);
            switch ($completiondata->completionstate) {
                case COMPLETION_COMPLETE:
                case COMPLETION_COMPLETE_PASS:
                    $completionstatus = true;
                    break;
                default:
                    $completionstatus = false;
            }
        }
        return $completionstatus ?? false;
    }

    /**
     * Options for course enrolment menu.
     *
     * @param stdclass $user
     * @return string $enrolstatusoutput
     */
    public function enrolment_menus($user) {
        global $CFG, $OUTPUT, $PAGE;

        $enrolstatusoutput = '';
        $canreviewenrol = self::has_capability('dashaddon/course_enrols:viewdetails', $this->course->id);
        if ($canreviewenrol) {
            $canviewfullnames = 1;
            $fullname = fullname($user, $canviewfullnames);
            $coursename = format_string($this->course->fullname, true, ['context' => $this->context]);
            require_once($CFG->dirroot . '/enrol/locallib.php');
            $manager = new \course_enrolment_manager($PAGE, $this->course);
            $userenrolments = $manager->get_user_enrolments($user->id);

            foreach ($userenrolments as $ue) {
                if ($ue->enrolmentinstance->enrol != 'manual') {
                    continue;
                }
                $timestart = $ue->timestart;
                $timeend = $ue->timeend;
                $timeenrolled = $ue->timecreated;
                $actions = $this->get_user_enrolment_actions($manager, $ue);
                $instancename = $ue->enrolmentinstancename;

                // Default status field label and value.
                $status = get_string('participationactive', 'enrol');
                $statusval = status_field::STATUS_ACTIVE;
                switch ($ue->status) {
                    case ENROL_USER_ACTIVE:
                        $currentdate = new \DateTime();
                        $now = $currentdate->getTimestamp();
                        $isexpired = $timestart > $now || ($timeend > 0 && $timeend < $now);
                        $enrolmentdisabled = $ue->enrolmentinstance->status == ENROL_INSTANCE_DISABLED;
                        // If user enrolment status has not yet started/already ended or the enrolment instance is disabled.
                        if ($isexpired || $enrolmentdisabled) {
                            $status = get_string('participationnotcurrent', 'block_dash');
                            $statusval = status_field::STATUS_NOT_CURRENT;
                        }
                        break;
                    case ENROL_USER_SUSPENDED:
                        $status = get_string('participationsuspended', 'enrol');
                        $statusval = status_field::STATUS_SUSPENDED;
                        break;
                }

                $statusfield = new status_field(
                    $instancename,
                    $coursename,
                    $fullname,
                    $status,
                    $timestart,
                    $timeend,
                    $actions,
                    $timeenrolled
                );
                $statusfielddata = $statusfield->set_status($statusval)->export_for_template($OUTPUT);
                array_walk($statusfielddata->enrolactions, function (&$value) {
                    array_walk($value->attributes, function ($val) use (&$value) {
                        if ($val->name == 'title') {
                            $value->title = $val->value;
                        }
                    });
                });
                $statusfielddata->hascapviewdetails = self::has_capability(
                    'dashaddon/course_enrols:viewdetails',
                    $this->course->id
                );
                $docicon = $OUTPUT->pix_icon(
                    'docs',
                    $statusfielddata->enrolinstancename,
                    'core',
                    ['class' => 'iconhelp icon-pre', 'role' => 'presentation']
                );
                $statusfielddata->docicon = $docicon;
                $statusfielddata->datatoggle = $CFG->branch >= 500 ? 'data-bs-toggle' : 'data-toggle';
                $enrolstatusoutput .= $OUTPUT->render_from_template(
                    'dashaddon_course_enrols/status_field',
                    $statusfielddata
                );
            }
        }

        return $enrolstatusoutput;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(\course_enrolment_manager $manager, $ue) {
        $actions = [];
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        $params['id'] = $this->course->id;

        // Edit enrolment action.
        if (self::has_capability("dashaddon/course_enrols:editenrolment", $this->course->id)) {
            $title = get_string('editenrolment', 'enrol');
            $icon = new pix_icon('t/edit', $title);
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actionparams = [
                'class' => 'editenrollink menu-item',
                'rel' => $ue->id,
                'data-action' => COURSE_ENROLS_ACTION_EDIT,
            ];
            $actions[] = new \user_enrolment_action($icon, $title, $url, $actionparams);
        }

        // Unenrol action.
        if (self::has_capability("dashaddon/course_enrols:unenrol", $this->course->id)) {
            $title = get_string('unenrol', 'enrol');
            $icon = new pix_icon('t/delete', $title);
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actionparams = [
                'class' => 'unenrollink menu-item',
                'rel' => $ue->id,
                'data-action' => COURSE_ENROLS_ACTION_UNENROL,
            ];
            $actions[] = new \user_enrolment_action($icon, $title, $url, $actionparams);
        }

        return $actions;
    }
}
