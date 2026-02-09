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
 * Info area class for learning path widget.
 *
 * @package    dashaddon_learningpath
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath;

/**
 * Info area class for learning path widget.
 */
class info_area {
    /**
     * @var object Widget instance
     */
    protected $widget;

    /**
     * Constructor.
     *
     * @param object $widget Widget instance
     */
    public function __construct($widget) {
        $this->widget = $widget;
    }

    /**
     * Get preference value with global config fallback.
     *
     * @param string $key Preference key
     * @return mixed Preference value or global config value
     */
    protected function get_preference($key) {
        $value = $this->widget->get_preferences($key);
        if ($value === null || $value === '') {
            $value = get_config('dashaddon_learningpath', $key);
        }
        return $value;
    }

    /**
     * Build form fields for info area settings.
     *
     * @param object $mform Moodle form object
     * @return void
     */
    public function build_form_fields($mform) {
        global $DB;
        // Info area checkbox.
        $mform->addElement(
            'advcheckbox',
            'config_preferences[infoarea]',
            get_string('field:infoarea', 'block_dash'),
            '',
            [0, 1]
        );
        $mform->setDefault('config_preferences[infoarea]', get_config('dashaddon_learningpath', 'infoarea') ?: 0);
        $mform->addHelpButton('config_preferences[infoarea]', 'field:infoarea', 'block_dash');

        // Info area position.
        $infoareapositions = [
            'top' => get_string('infoarea:top', 'block_dash'),
            'sidebar' => get_string('infoarea:sidebar', 'block_dash'),
        ];

        $mform->addElement(
            'select',
            'config_preferences[infoareaposition]',
            get_string('field:infoareaposition', 'block_dash'),
            $infoareapositions
        );
        $mform->setDefault(
            'config_preferences[infoareaposition]',
            get_config('dashaddon_learningpath', 'infoareaposition') ?: 'top'
        );
        $mform->setType('config_preferences[infoareaposition]', PARAM_TEXT);
        $mform->addHelpButton('config_preferences[infoareaposition]', 'field:infoareaposition', 'block_dash');
        $mform->hideIf('config_preferences[infoareaposition]', 'config_preferences[infoarea]', 'notchecked');

        // KPI options.
        $kpioptions = [
            'none' => get_string('none', 'block_dash'),
            'courses' => get_string('kpi:courses', 'block_dash'),
            'coursespercent' => get_string('kpi:coursespercent', 'block_dash'),
            'badges' => get_string('kpi:badges', 'block_dash'),
            'period' => get_string('kpi:period', 'block_dash'),
            'status' => get_string('kpi:status', 'block_dash'),
        ];

        // KPI.
        $mform->addElement('select', 'config_preferences[kpi1]', get_string('field:kpi1', 'block_dash'), $kpioptions);
        $mform->setType('config_preferences[kpi1]', PARAM_TEXT);
        $mform->setDefault('config_preferences[kpi1]', get_config('dashaddon_learningpath', 'kpi1') ?: 'none');
        $mform->hideIf('config_preferences[kpi1]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[kpi1]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        $mform->addElement('select', 'config_preferences[kpi2]', get_string('field:kpi2', 'block_dash'), $kpioptions);
        $mform->setType('config_preferences[kpi2]', PARAM_TEXT);
        $mform->setDefault('config_preferences[kpi2]', get_config('dashaddon_learningpath', 'kpi2') ?: 'none');
        $mform->hideIf('config_preferences[kpi2]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[kpi2]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        $mform->addElement('select', 'config_preferences[kpi3]', get_string('field:kpi3', 'block_dash'), $kpioptions);
        $mform->setType('config_preferences[kpi3]', PARAM_TEXT);
        $mform->setDefault('config_preferences[kpi3]', get_config('dashaddon_learningpath', 'kpi3') ?: 'none');
        $mform->hideIf('config_preferences[kpi3]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[kpi3]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        $mform->addElement('select', 'config_preferences[kpi4]', get_string('field:kpi4', 'block_dash'), $kpioptions);
        $mform->setType('config_preferences[kpi4]', PARAM_TEXT);
        $mform->setDefault('config_preferences[kpi4]', get_config('dashaddon_learningpath', 'kpi4') ?: 'none');
        $mform->hideIf('config_preferences[kpi4]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[kpi4]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        // Display path index.
        $mform->addElement(
            'advcheckbox',
            'config_preferences[displaypathindex]',
            get_string('field:displaypathindex', 'block_dash'),
            '',
            [0, 1]
        );
        $mform->setDefault('config_preferences[displaypathindex]', get_config('dashaddon_learningpath', 'displaypathindex') ?: 0);
        $mform->addHelpButton('config_preferences[displaypathindex]', 'field:displaypathindex', 'block_dash');
        $mform->hideIf('config_preferences[displaypathindex]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[displaypathindex]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        // Display faculty - only show course-level roles.
        $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
        [$insql, $inparams] = $DB->get_in_or_equal(array_values($courseroles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $rolesoptions = role_fix_names($roles, null, ROLENAME_ALIAS, true);
        asort($rolesoptions);

        $mform->addElement(
            'autocomplete',
            'config_preferences[displayfaculty]',
            get_string('field:displayfaculty', 'block_dash'),
            $rolesoptions,
            ['multiple' => true]
        );
        $mform->setType('config_preferences[displayfaculty]', PARAM_RAW);

        $widgetfaculty = $this->widget->get_preferences('displayfaculty');
        if ($widgetfaculty === null || $widgetfaculty === '') {
            $defaultfaculty = get_config('dashaddon_learningpath', 'displayfaculty');
            if ($defaultfaculty) {
                $mform->setDefault('config_preferences[displayfaculty]', explode(',', $defaultfaculty));
            }
        }
        $mform->addHelpButton('config_preferences[displayfaculty]', 'field:displayfaculty', 'block_dash');
        $mform->hideIf('config_preferences[displayfaculty]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[displayfaculty]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');

        // Display badges.
        $mform->addElement(
            'advcheckbox',
            'config_preferences[displaybadges]',
            get_string('field:displaybadges', 'block_dash'),
            '',
            [0, 1]
        );
        $mform->setDefault('config_preferences[displaybadges]', get_config('dashaddon_learningpath', 'displaybadges') ?: 0);
        $mform->addHelpButton('config_preferences[displaybadges]', 'field:displaybadges', 'block_dash');
        $mform->hideIf('config_preferences[displaybadges]', 'config_preferences[infoarea]', 'notchecked');
        $mform->hideIf('config_preferences[displaybadges]', 'config_preferences[infoareaposition]', 'neq', 'sidebar');
    }

    /**
     * Build info area data.
     *
     * @param array $courses Course list
     * @param int $completedcourses Completed courses count
     * @param int $totalcourses Total courses count
     * @param array $courseids Course IDs
     * @return array Info area data
     */
    public function build_data($courses, $completedcourses, $totalcourses, $courseids) {
        $data = [];

        // Info area position.
        $data['infoareaposition'] = $this->get_preference('infoareaposition') ?: 'top';

        $kpis = [];
        for ($i = 1; $i <= 4; $i++) {
            $kpitype = $this->get_preference('kpi' . $i);
            if (!empty($kpitype) && $kpitype != 'none') {
                $kpis['kpi' . $i] = $this->calculate_kpi($kpitype, $courses, $courseids, $completedcourses, $totalcourses);
            }
        }

        $data['kpis'] = $kpis;

        if (!empty($kpis['kpi1'])) {
            $data['kpivalue'] = $kpis['kpi1']['value'];
            $data['kpilabel'] = $kpis['kpi1']['label'];
        } else {
            $data['kpivalue'] = '';
            $data['kpilabel'] = '';
        }

        // Sidebar display options.
        if ($data['infoareaposition'] === 'sidebar') {
            $data['displaypathindex'] = $this->get_preference('displaypathindex');
            $data['displayfaculty'] = !empty($this->get_preference('displayfaculty')) ? true : false;
            $data['displaybadges'] = $this->get_preference('displaybadges');
            $data['learningpathname'] = $this->widget->get_block_instance()->get_title();

            // Get course progress data if path index is enabled.
            if ($data['displaypathindex']) {
                $data['coursesprogress'] = $this->get_courses_progress($courses);
            }

            // Get faculty members if enabled.
            if ($data['displayfaculty']) {
                $roleids = $this->get_preference('displayfaculty');

                if (!empty($roleids) && !is_array($roleids)) {
                    $roleids = explode(',', $roleids);
                }

                if (is_array($roleids)) {
                    $roleids = array_filter($roleids, function ($value) {
                        return is_numeric($value) && $value > 0;
                    });
                    $roleids = array_values($roleids);
                }

                if (!empty($roleids)) {
                    $faculty = $this->get_faculty_members($courseids, $roleids);
                    if (!empty($faculty)) {
                        $data['faculty'] = $faculty;
                        $data['displayfaculty'] = true;
                    } else {
                        $data['displayfaculty'] = false;
                    }
                } else {
                    $data['displayfaculty'] = false;
                }
            }

            // Get badges if enabled.
            if ($data['displaybadges'] && !empty($courseids)) {
                $badges = $this->get_path_badges($courseids);
                if (!empty($badges)) {
                    $data['badges'] = $badges;
                    $data['displaybadges'] = true;
                } else {
                    $data['displaybadges'] = false;
                }
            }
        }
        return $data;
    }

    /**
     * Calculate KPI based on type.
     *
     * @param string $kpitype KPI type
     * @param array $courses Course list
     * @param array $courseids Course IDs
     * @param int $completedcourses Completed courses count
     * @param int $totalcourses Total courses count
     * @return array KPI data with value and label
     */
    public function calculate_kpi($kpitype, $courses, $courseids, $completedcourses, $totalcourses) {
        global $DB, $USER, $CFG;

        $kpidata = ['value' => '', 'label' => ''];

        switch ($kpitype) {
            case 'courses':
                $kpidata['value'] = $completedcourses . ' / ' . $totalcourses;
                $kpidata['label'] = get_string('kpi:courses', 'block_dash');
                break;

            case 'coursespercent':
                $percentage = $totalcourses > 0 ? round(($completedcourses / $totalcourses) * 100) : 0;
                $kpidata['value'] = $percentage . '%';
                $kpidata['label'] = get_string('kpi:coursespercent', 'block_dash');
                break;

            case 'badges':
                if (!empty($courseids)) {
                    require_once($CFG->dirroot . '/lib/badgeslib.php');

                    $totalbadges = 0;
                    $earnedbadges = 0;
                    $allbadgeids = [];

                    // Get badges for each course.
                    foreach ($courseids as $courseid) {
                        $coursebadges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);
                        foreach ($coursebadges as $badge) {
                            // Only count active or active-locked badges.
                            if (
                                !in_array($badge->id, $allbadgeids) &&
                                ($badge->status == BADGE_STATUS_ACTIVE || $badge->status == BADGE_STATUS_ACTIVE_LOCKED)
                            ) {
                                $allbadgeids[] = $badge->id;
                                $totalbadges++;
                            }
                        }
                    }

                    // Get site badges with course criteria matching our courses.
                    $sitebadges = badges_get_badges(BADGE_TYPE_SITE);
                    foreach ($sitebadges as $badge) {
                        // Only process active or active-locked badges.
                        if (!($badge->status == BADGE_STATUS_ACTIVE || $badge->status == BADGE_STATUS_ACTIVE_LOCKED)) {
                            continue;
                        }

                        $criteria = $badge->get_criteria();
                        $matchescourses = false;

                        foreach ($criteria as $criterion) {
                            if ($criterion->criteriatype == BADGE_CRITERIA_TYPE_COURSESET) {
                                $params = $criterion->get_params($criterion->id);
                                if (!empty($params)) {
                                    foreach (array_keys($params) as $courseidkey) {
                                        if (in_array($courseidkey, $courseids)) {
                                            $matchescourses = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        if ($matchescourses && !in_array($badge->id, $allbadgeids)) {
                            $allbadgeids[] = $badge->id;
                            $totalbadges++;
                        }
                    }

                    // Get user's earned badges.
                    $userbadges = badges_get_user_badges($USER->id);
                    foreach ($userbadges as $userbadge) {
                        if (in_array($userbadge->id, $allbadgeids) && $userbadge->visible == 1) {
                            $earnedbadges++;
                        }
                    }

                    $kpidata['value'] = $earnedbadges . ' / ' . $totalbadges;
                } else {
                    $kpidata['value'] = '0 / 0';
                }
                $kpidata['label'] = get_string('kpi:badges', 'block_dash');
                break;

            case 'period':
                if (!empty($courseids)) {
                    [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
                    $params = array_merge(['userid' => $USER->id], $inparams);

                    // Get earliest enrolment start date and latest enrolment end date from user_enrolments.
                    $sql = "SELECT MIN(ue.timestart) as minstart, MAX(ue.timeend) as maxend
                              FROM {user_enrolments} ue
                              JOIN {enrol} e ON e.id = ue.enrolid
                             WHERE ue.userid = :userid
                               AND e.courseid $insql
                               AND ue.timestart > 0
                               AND ue.timeend > 0";

                    $period = $DB->get_record_sql($sql, $params);

                    if ($period && $period->minstart && $period->maxend) {
                        $startdate = userdate($period->minstart, get_string('strftimedateshort', 'core_langconfig'));
                        $enddate = userdate($period->maxend, get_string('strftimedateshort', 'core_langconfig'));
                        $kpidata['value'] = $startdate . ' - ' . $enddate;
                    } else {
                        $kpidata['value'] = get_string('notavailable', 'block_dash');
                    }
                } else {
                    $kpidata['value'] = get_string('notavailable', 'block_dash');
                }
                $kpidata['label'] = get_string('kpi:period', 'block_dash');
                break;

            case 'status':
                $status = $this->calculate_status_kpi($courseids);
                $kpidata['value'] = $status;
                $kpidata['label'] = get_string('kpi:status', 'block_dash');
                $statuslower = strtolower(str_replace(' ', '', $status));
                $kpidata['statusclass'] = $statuslower;
                break;
        }
        return $kpidata;
    }

    /**
     * Calculate status KPI based on timetable course duedates.
     *
     * @param array $courseids Course IDs
     * @return string Status value
     */
    protected function calculate_status_kpi($courseids) {
        global $USER;

        $manager = \core_plugin_manager::instance();
        $plugin = $manager->get_plugin_info('tool_timetable');
        if (!$plugin || $plugin->get_status() === \core_plugin_manager::PLUGIN_STATUS_MISSING) {
            return '';
        }

        if (empty($courseids)) {
            return get_string('ontrack', 'block_dash');
        }

        $now = time();
        $pastduecourses = [];
        $incompletepastduecourses = [];

        foreach ($courseids as $courseid) {
            try {
                $timemanagement = new \tool_timetable\time_management($courseid);
                $usercourseenrollinfo = $timemanagement->get_course_user_enrollment($USER->id);

                if (!empty($usercourseenrollinfo)) {
                    $startdate = $usercourseenrollinfo[0]['timestart'] ?? 0;
                    $enddate = $usercourseenrollinfo[0]['timeend'] ?? 0;
                    $coursduedate = $timemanagement->get_user_course_due_date($startdate, $enddate, $USER->id);

                    // If course has a due date and it's in the past.
                    if ($coursduedate > 0 && $coursduedate < $now) {
                        $pastduecourses[] = $courseid;

                        // Check if this past due course is incomplete.
                        $completion = new \completion_info(get_course($courseid));
                        if (!$completion->is_course_complete($USER->id)) {
                            $incompletepastduecourses[] = $courseid;
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($pastduecourses)) {
            return get_string('ontrack', 'block_dash');
        }

        if (!empty($incompletepastduecourses)) {
            return get_string('notontrack', 'block_dash');
        }

        return get_string('ontrack', 'block_dash');
    }

    /**
     * Get faculty members for courses.
     *
     * @param array $courseids Course IDs
     * @param array $roleids Role IDs
     * @return array Faculty members data
     */
    protected function get_faculty_members($courseids, $roleids) {
        global $DB, $PAGE;

        if (empty($courseids) || empty($roleids)) {
            return [];
        }

        [$courseinsql, $courseparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
        [$roleinsql, $roleparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'rid');

        $params = array_merge($courseparams, $roleparams);
        $params['contextlevel'] = CONTEXT_COURSE;

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.picture, u.imagealt, u.firstnamephonetic,
                       u.lastnamephonetic, u.middlename, u.alternatename
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {context} ctx ON ctx.id = ra.contextid
                 WHERE ctx.contextlevel = :contextlevel
                   AND ctx.instanceid $courseinsql
                   AND ra.roleid $roleinsql
                   AND u.deleted = 0
              ORDER BY u.lastname, u.firstname
                 LIMIT 20";

        $users = $DB->get_records_sql($sql, $params);

        $faculty = [];
        foreach ($users as $user) {
            $userpicture = new \user_picture($user);
            $userpicture->size = 100;

            $faculty[] = [
                'id' => $user->id,
                'fullname' => fullname($user),
                'profileurl' => new \moodle_url('/user/profile.php', ['id' => $user->id]),
                'pictureurl' => $userpicture->get_url($PAGE)->out(false),
            ];
        }

        return $faculty;
    }

    /**
     * Get badges for learning path courses.
     *
     * @param array $courseids Course IDs
     * @return array Badges data
     */
    protected function get_path_badges($courseids) {
        global $CFG, $USER;

        if (empty($courseids)) {
            return [];
        }

        require_once($CFG->dirroot . '/lib/badgeslib.php');

        $allbadges = [];

        foreach ($courseids as $courseid) {
            $coursebadges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);
            foreach ($coursebadges as $badge) {
                if (
                    !isset($allbadges[$badge->id]) &&
                    ($badge->status == BADGE_STATUS_ACTIVE || $badge->status == BADGE_STATUS_ACTIVE_LOCKED)
                ) {
                    $allbadges[$badge->id] = $badge;
                }
            }
        }

        $sitebadges = badges_get_badges(BADGE_TYPE_SITE);
        foreach ($sitebadges as $badge) {
            if (isset($allbadges[$badge->id])) {
                continue;
            }

            if (!($badge->status == BADGE_STATUS_ACTIVE || $badge->status == BADGE_STATUS_ACTIVE_LOCKED)) {
                continue;
            }

            $criteria = $badge->get_criteria();
            $matchescourses = false;

            foreach ($criteria as $criterion) {
                if ($criterion->criteriatype == BADGE_CRITERIA_TYPE_COURSESET) {
                    $params = $criterion->get_params($criterion->id);
                    if (!empty($params)) {
                        foreach (array_keys($params) as $courseidkey) {
                            if (in_array($courseidkey, $courseids)) {
                                $matchescourses = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($matchescourses) {
                $allbadges[$badge->id] = $badge;
            }
        }

        // Get user's earned badges.
        $userbadges = badges_get_user_badges($USER->id);
        $earnedbadgeids = [];
        foreach ($userbadges as $userbadge) {
            if ($userbadge->visible == 1) {
                $earnedbadgeids[] = $userbadge->id;
            }
        }

        // Build the return array.
        $pathbadges = [];
        foreach ($allbadges as $badge) {
            $pathbadges[] = [
                'id' => $badge->id,
                'name' => format_string($badge->name),
                'description' => format_text($badge->description, FORMAT_HTML),
                'imageurl' => \moodle_url::make_pluginfile_url(
                    $badge->get_context()->id,
                    'badges',
                    'badgeimage',
                    $badge->id,
                    '/',
                    'f1',
                    false
                )->out(false),
                'earned' => in_array($badge->id, $earnedbadgeids),
            ];
        }

        return $pathbadges;
    }

    /**
     * Get course progress data for path index display.
     *
     * @param array $courses Array of courses with completion data
     * @return array Array of course progress data
     */
    protected function get_courses_progress($courses) {
        $coursesprogress = [];

        foreach ($courses as $course) {
            $courseobj = get_course($course['info']['id']);
            $courseprogress = \core_completion\progress::get_course_progress_percentage($courseobj);
            $courseprogress = $courseprogress ? round($courseprogress) : 0;

            $courseprogress = [
                'id' => $course['info']['id'],
                'fullname' => $course['info']['fullname'],
                'url' => $course['info']['url'],
                'completionpercentage' => $courseprogress,
                'status' => isset($course['completionstatus']) ? $course['completionstatus'] : 'notstarted',
            ];

            $courseprogress['statusclass'] = $courseprogress['status'];

            switch ($courseprogress['status']) {
                case 'completed':
                    $courseprogress['progressclass'] = 'course-completed';
                    break;
                case 'inprogress':
                    $courseprogress['progressclass'] = 'course-inprogress';
                    break;
                case 'unavailable':
                    $courseprogress['progressclass'] = 'course-unavailable';
                    break;
                default:
                    $courseprogress['progressclass'] = 'course-unavailable';
                    break;
            }

            $coursesprogress[] = $courseprogress;
        }

        return $coursesprogress;
    }
}
