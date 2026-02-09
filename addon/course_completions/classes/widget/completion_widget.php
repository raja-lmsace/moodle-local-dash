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
 * Course completion widget class which contains the layout information and generate the data for widget.
 *
 * @package    dashaddon_course_completions
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_completions\widget;

use block_dash\local\widget\abstract_widget;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\filter\course_condition;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use local_dash\data_grid\filter\relations_role_condition;
use local_dash\data_grid\filter\course_dates_condition;
use local_dash\data_grid\filter\course_customfield_condition;

/**
 * Course completion report widget class contains the layout information and generate the data for widget.
 */
class completion_widget extends abstract_widget {
    /**
     * Color Hex code for inprogress users dataset.
     *
     * @var string
     */
    const COLORINPROGRESS = '#00a1ff';

    /**
     * Color Hex code for completed users dataset.
     *
     * @var string
     */
    const COLORCOMPLETED = '#60d837';

    /**
     * Color Hex code for not started users dataset.
     *
     * @var string
     */
    const COLORNOTSTARTED = '#929292';

    /**
     * Font color hex used in course names.
     *
     * @var string
     */
    const COLORFONT = '#929292';

    /**
     * Check the datasource is widget.
     *
     * @return bool
     */
    public function is_widget() {
        return true;
    }

    /**
     * Get template file name to renderer.
     */
    public function get_mustache_template_name() {
        return 'dashaddon_course_completions/completion_report';
    }

    /**
     * Check the widget support uses the query method to build the widget.
     *
     * @return bool
     */
    public function supports_query() {
        return false;
    }

    /**
     * Layout class widget will use to render the widget content.
     *
     * @return \abstract_layout
     */
    public function layout() {
        return new completion_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'course_completion',
            'layout' => 'completion',
        ];
        return $preferences;
    }

    /**
     * Widget data count.
     *
     * @return void
     */
    public function widget_data_count() {
        return $this->data['coursecount'] ?? 0;
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {
        global $PAGE, $USER, $CFG;

        $courses = $this->generate_course_completion_report();

        if (!isset($courses) || empty($courses)) {
            return false;
        }

        $labels = ['status:completed', 'status:inprogress', 'status:notyetstarted'];
        $strings = (array) get_strings($labels, 'block_dash');

        $completiondata = [
            'contextid'   => $this->get_block_instance()->context->id,
            'uniqueid'    => $this->get_block_instance()->instance->id,
            'courses'     => array_values($courses),
            'dataset'     => json_encode(array_values($courses)),
            'coursecount' => count($courses),
            'istesting'   => defined('BEHAT_SITE_RUNNING') ?? false,
            'colors'      => [
                'fontcolor'  => self::COLORFONT,
                "inprogress" => self::COLORINPROGRESS,
                "completed"  => self::COLORCOMPLETED,
                "notstarted" => self::COLORNOTSTARTED,
                "white"      => '#FFFFFF',
            ],
            'branch'     => $CFG->branch > 400 ? 'mooodle-chatimage' : '',
        ];

        // Can't able to use the global variables for multiple instance.
        $PAGE->requires->data_for_js('dashCourseCompletionData', [
            'colors' => $completiondata['colors'],
            'datalabels' => $strings,
        ]);

        $this->data = (!empty($courses)) ? $completiondata : [];

        return $this->data;
    }

    /**
     * Fetch the accessible courses based on the conditions and process the data to create doughnut chart using moodle chart api.
     *
     * @return array
     */
    private function generate_course_completion_report() {
        global $DB;

        [$conditionsql, $params] = $this->generate_course_completion_filter();

        $rolesql = "SELECT rc.id, rc.roleid FROM {role_capabilities} rc
            JOIN {capabilities} cap ON rc.capability = cap.name
            JOIN {context} ctx on rc.contextid = ctx.id
            WHERE rc.permission = 1 AND rc.capability = :capability ";
        $roles = $DB->get_records_sql($rolesql, ['capability' => 'dashaddon/course_completions:reportuser']);
        $roles = array_column($roles, 'roleid');
        [$roleinsql, $roleinparams] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'rl');

        $courses = [];

        $sql = "SELECT ue.*, e.courseid, cp.timeenrolled, cp.timecompleted, cp.timestarted, ue.userid, c.fullname, ra.userid
            from {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = :contextlevel
            JOIN (SELECT DISTINCT userid, contextid
                FROM {role_assignments}
                WHERE roleid $roleinsql
            ) ra ON (ra.userid = ue.userid) AND ra.contextid = ctx.id
            JOIN {user} u ON u.id = ue.userid  AND u.deleted != 1
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {course_completions} cp ON cp.course = e.courseid AND cp.userid = ue.userid
            WHERE c.enablecompletion = 1 AND (ra.userid = ue.userid) $conditionsql ORDER BY c.id ASC";

        $params['contextlevel'] = CONTEXT_COURSE; // Course context level.
        $recordset = $DB->get_recordset_sql($sql, $roleinparams + $params);

        foreach ($recordset as $record) {
            $courses[$record->courseid]['info'] = ['id' => $record->courseid, 'fullname' => format_string($record->fullname)];
            $courses[$record->courseid]['enrollments'][] = $record;
        }

        array_walk($courses, function (&$course) {
            global $OUTPUT;
            $report = $this->generate_completion_stats($course['info']['id'], $course['enrollments']);
            $course['report'] = $report;
            $course['completionpercentage'] = isset($report['completionpercentage']) ? (int) $report['completionpercentage'] : 0;
            $course['dataset'] = [
                $report['completed'],
                $report['inprogress'],
                $report['notyetstarted'],
            ];
            // Make the enrollments empty to prevent the data limit reach issue for JS.
            $course['enrollments'] = [];
        });

        return $courses;
    }

    /**
     * Generate the course completion report and convert the data to chart dataset.
     *
     * @param int $courseid Course id
     * @param array $enrollments Enrollments in the course.
     * @return array course completion report.
     */
    private function generate_completion_stats($courseid, $enrollments) {
        global $DB;

        // Filter the disabled enrollments.
        $context = \context_course::instance($courseid);
        $userslist = []; // Remove the user's multiple enrolment in one course.
        $enrollments = array_filter($enrollments, function ($enrol) use ($context, &$userslist) {
            $enrolled = is_enrolled($context, $enrol->userid, '', true);
            if ($enrolled && !in_array($enrol->userid, $userslist)) {
                $userslist[] = $enrol->userid;
                return true;
            }
            return false;
        });

        // List of active enrollments.
        $report['enrolled'] = count($enrollments);
        $report['completed'] = count(array_unique(array_filter(array_column($enrollments, 'timecompleted'))));
        $report['completionpercentage'] = $report['completed']
            ? ($report['completed'] / $report['enrolled']) * 100 : '0';
        // Remove the completed enrol from enrolled and started course completions.
        $report['inprogress'] = count(array_unique(array_filter(array_column($enrollments, 'timestarted')))) - $report['completed'];
        $report['notyetstarted'] = $report['enrolled'] - ($report['inprogress'] + $report['completed']);

        return $report;
    }

    /**
     * Preference form for widget. We make the fields disable other than the general.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            $mform->addElement('static', 'data_source_name', get_string('datasource', 'block_dash'), $this->get_name());
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }

        $mform->addElement('html', get_string('fieldalert', 'block_dash'), 'fieldalert');
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_course_completion_filter() {

        $this->before_data();
        [$sql, $params] = $this->get_filter_collection()->get_sql_and_params();
        return $sql ? [" AND " . $sql[0], $params] : [];
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new course_condition('c_course', 'c.id'));

        $filtercollection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $filtercollection->add_filter(new relations_role_condition('parentrole', 'u.id'));

        $filtercollection->add_filter(new course_dates_condition('coursedates', 'c.id'));

        // Attach the custom course field conditions.
        local_dash_customfield_conditions($filtercollection);

        return $filtercollection;
    }

    /**
     * Is the widget needs to load the js when it the content updated using JS.
     *
     * @return bool
     */
    public function supports_currentscript() {
        return true;
    }
}
