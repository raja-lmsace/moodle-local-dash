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
 * Activity completion widget.
 *
 * @package    dashaddon_activitycompletion
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activitycompletion\widget;

use block_dash\local\widget\abstract_widget;
use dashaddon_activitycompletion\widget\activitycompletion_layout;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use local_dash\data_grid\filter\users_mycohort_condition;
use local_dash\data_grid\filter\relations_role_condition;
use block_dash\local\data_grid\filter\my_groups_condition;
use local_dash\data_grid\filter\cohort_condition;
use dashaddon_activitycompletion\local\block_dash\data_grid\filter\course_sections_filter;
use dashaddon_activitycompletion\local\block_dash\data_grid\filter\mygroups_filter;
use Exception;

/**
 * Activity completion progress widget.
 */
class activitycompletion_widget extends abstract_widget {
    /**
     * Color Hex code for notcompleted activity progress dataset.
     *
     * @var string
     */
    const COLORNOTCOMPLETED = '#FF6384';

    /**
     * Color Hex code for completed activity progress dataset.
     *
     * @var string
     */
    const COLORCOMPLETED = '#51E100';

    /**
     * Color Hex code for passed activity progress dataset.
     *
     * @var string
     */
    const COLORPASSED = '#36A2EB';

    /**
     * Color Hex code for failed activity progress dataset.
     *
     * @var string
     */
    const COLORFAILED = '#FF9800';

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
        return 'dashaddon_activitycompletion/activitycompletion';
    }

    /**
     * Get the name of widget.
     *
     * @return void
     */
    public function get_name() {
        return get_string('widget:activitycompletion', 'block_dash');
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
        return new activitycompletion_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {

        $preferences = [
            'datasource' => 'activitycompletion',
            'layout' => 'activitycompletion',
        ];
        return $preferences;
    }

    /**
     * Build widget data for activity completion reports.
     *
     * @return void|array
     */
    public function build_widget() {

        $coursemodules = $this->generate_modules();
        // Store widget data.
        $this->data = [
            'contextid' => $this->get_block_instance()->context->id,
            'uniqueid' => $this->get_block_instance()->instance->id,
            'charthtml' => (!empty($coursemodules)) ? $this->generate_activity_completion_progress_chart($coursemodules) : '',
            'status' => (!empty($coursemodules)) ? true : false,
        ];

        return $this->data;
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
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_activitycompletion_filter() {

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

        $filtercollection->add_filter(new current_course_condition('current_course', 'c.id'));

        $filtercollection->add_filter(new relations_role_condition('parentrole', 'u.id'));

        $filtercollection->add_filter(new my_groups_condition('group', 'gm300.groupid'));

        $filtercollection->add_filter(new users_mycohort_condition('users_mycohort', 'u.id'));

        $filtercollection->add_filter(new cohort_condition('cohort', 'u.id'));

        $filtercollection->add_filter(new course_sections_filter(
            'c_sections',
            'cs.id',
            get_string('widget:course_sections', 'block_dash')
        ));

        $filtercollection->add_filter(new mygroups_filter('my_groups', 'gm300.groupid', get_string('groups')));

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

    /**
     * Generate the course modules data.
     *
     * @return array
     */
    public function generate_modules() {
        global $DB, $COURSE, $USER;

        $coursemodules = [];
        [$conditionsql, $params] = $this->generate_activitycompletion_filter();
        $insql = '';
        $insparams = [];

        if (has_capability('moodle/course:viewhiddencourses', $this->get_context(), $USER) && !is_siteadmin($USER->id)) {
            $insql = "AND c.id = :courseid";
            $insparams = ['courseid' => $COURSE->id];
        }

        $filteredcourses = [];

        $rolesql = "SELECT rc.id, rc.roleid FROM {role_capabilities} rc
            JOIN {capabilities} cap ON rc.capability = cap.name
            JOIN {context} ctx on rc.contextid = ctx.id
            WHERE rc.permission = 1 AND rc.capability = :capability ";
        $roles = $DB->get_records_sql($rolesql, ['capability' => 'dashaddon/activitycompletion:reportuser']);
        $roles = array_column($roles, 'roleid');
        [$roleinsql, $roleinparams] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'rl');

        $sql = "SELECT ue.*, cm.id as cmid, cm.module as moduleid, cm.completion as cmcompletion, cm.section as cmsection,
            c.fullname as coursename, c.id as courseid, u.id as userid, u.firstname as firstname
            from {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            JOIN {course} c ON c.id = cm.course
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = :contextlevel
            JOIN (SELECT DISTINCT userid, contextid
                FROM {role_assignments}
                WHERE roleid $roleinsql
            ) ra ON ra.userid = ue.userid AND ra.contextid = ctx.id
            JOIN {user} u ON u.id = ue.userid AND u.deleted != 1
            JOIN {course_sections} cs ON cs.id = cm.section
            LEFT JOIN {groups_members} gm ON gm.userid = u.id
            LEFT JOIN {groups} g ON g.id = gm.groupid
            WHERE c.enablecompletion = 1 AND cm.deletioninprogress = 0 AND (cm.visible = 1)
            AND (ra.userid = ue.userid) $conditionsql $insql ORDER BY c.id ASC";

        $params['contextlevel'] = CONTEXT_COURSE; // Course context level.
        $recordset = $DB->get_recordset_sql($sql, $roleinparams + $params + $insparams);

        foreach ($recordset as $record) {
            $coursemodules[$record->cmid]['courseinfo'] = [
                'id' => $record->courseid,
                'fullname' => format_string($record->coursename),
            ];
            $coursemodules[$record->cmid]['enrollments'][] = $record;

            // Include the list of filtered courses.
            $filteredcourses[] = $record->courseid;
        }

        if ($this->get_filter_collection()->has_filter('c_sections')) {
            $sectionfilter = $this->get_filter_collection()->get_filter('c_sections');
            $values = $sectionfilter->get_values();
            if (!empty($values) && ($values[0] == course_sections_filter::HIGHLIGHT_OPTION)) {
                if (!$DB->record_exists('course', ['id' => $COURSE->id, 'visible' => 1, 'marker' => 1])) {
                    $filteredcourses[] = $COURSE->id;
                }
            }
        }

        // Set the filter section for the course modules.
        // Need to the filter the sections in the course section filter based on the filtered courses.
        $filteredcourses = array_unique($filteredcourses);
        if ($this->get_filter_collection()->has_filter('c_sections')) {
            // Course section filter.
            $sectionfilter = $this->get_filter_collection()->get_filter('c_sections');
            // Generates the options list for the course section filter.
            $choices = $sectionfilter::generate_dynamic_options_list($filteredcourses);
            foreach ($choices as $key => $value) {
                $sectionfilter->add_option($key, $value);
            }
        }

        // Set the filter groups for the course modules.
        // Need to the filter the groups in the course groups filter based on the filtered courses.
        if ($this->get_filter_collection()->has_filter('my_groups')) {
            // Course section filter.
            $groupfilter = $this->get_filter_collection()->get_filter('my_groups');
            // Generates the options list for the course section filter.
            $choices = $groupfilter::generate_dynamic_options_list($filteredcourses);
            foreach ($choices as $key => $value) {
                $groupfilter->add_option($key, $value);
            }
        }

        if (!isset($coursemodules) || empty($coursemodules)) {
            return [];
        }

        return $coursemodules;
    }

    /**
     * Generate the activity completion progress chart data.
     *
     * @param array $coursemodules course modules data
     * @return string
     */
    public function generate_activity_completion_progress_chart($coursemodules) {
        global $OUTPUT;

        $count = 0;
        $activitynames = [];
        $completeddata = [];
        $notcompleteddata = [];
        $passeddata = [];
        $faileddata = [];

        $colors = $this->get_completion_data_colors();
        foreach ($coursemodules as $cmid => $record) {
            try {
                $cm = \cm_info::create(get_coursemodule_from_id('', $cmid));
            } catch (Exception $e) {
                $cm = null;
            }

            if (!$cm) {
                continue;
            }

            $completioninfo = new \completion_info($cm->get_course());
            $enrolledusers = array_unique($record['enrollments'], SORT_REGULAR);

            if ($completioninfo->is_enabled($cm)) {
                $totalstudents = count($enrolledusers);

                // Initialise counts.
                $completedcount = 0;
                $notcompletedcount = 0;
                $passedcount = 0;
                $failedcount = 0;

                foreach ($enrolledusers as $user) {
                    $completiondata = $completioninfo->get_data($cm, false, $user->userid);
                    switch ($completiondata->completionstate) {
                        case COMPLETION_COMPLETE:
                            $completedcount++;
                            break;
                        case COMPLETION_INCOMPLETE:
                            $notcompletedcount++;
                            break;
                        case COMPLETION_COMPLETE_PASS:
                            $passedcount++;
                            break;
                        case COMPLETION_COMPLETE_FAIL:
                            $failedcount++;
                            break;
                    }
                }

                // Avoid division by zero, round to nearest whole number.
                $completedpercent = $totalstudents ? round(($completedcount / $totalstudents) * 100) : 0;
                $notcompletedpercent = $totalstudents ? round(($notcompletedcount / $totalstudents) * 100) : 0;
                $passedpercent = $totalstudents ? round(($passedcount / $totalstudents) * 100) : 0;
                $failedpercent = $totalstudents ? round(($failedcount / $totalstudents) * 100) : 0;

                $activitynames[] = $cm->name;
                $notcompleteddata['series'][$count] = $notcompletedpercent;
                $notcompleteddata['series_label'][$count] = $notcompletedpercent . '%';
                $completeddata['series'][$count] = $completedpercent;
                $completeddata['series_label'][$count] = $completedpercent . '%';
                $passeddata['series'][$count] = $passedpercent;
                $passeddata['series_label'][$count] = $passedpercent . '%';
                $faileddata['series'][$count] = $failedpercent;
                $faileddata['series_label'][$count] = $failedpercent . '%';
                $count++;
            }
        }

        // Prepare the stacked bar chart.
        $chart = new \core\chart_bar();
        $chart->set_title(get_string('activityprogresstitle', 'block_dash'));
        $chart->set_stacked(true);
        $chart->set_labels($activitynames);

        // Not Completed series.
        if (!empty($notcompleteddata['series']) && array_sum($notcompleteddata['series']) > 0) {
            $notcompletedseries = new \core\chart_series(
                get_string('status:notcompleted', 'block_dash'),
                $notcompleteddata['series']
            );
            $notcompletedseries->set_labels($notcompleteddata['series_label']);
            $notcompletedseries->set_color($colors['notcompleted']);
            $chart->add_series($notcompletedseries);
        }

        // Completed series.
        if (!empty($completeddata['series']) && array_sum($completeddata['series']) > 0) {
            $completedseries = new \core\chart_series(get_string('status:completed', 'block_dash'), $completeddata['series']);
            $completedseries->set_labels($completeddata['series_label']);
            $completedseries->set_color($colors['completed']);
            $chart->add_series($completedseries);
        }

        // Passed series.
        if (!empty($passeddata['series']) && array_sum($passeddata['series']) > 0) {
            $passedseries = new \core\chart_series(get_string('passed', 'block_dash'), $passeddata['series']);
            $passedseries->set_labels($passeddata['series_label']);
            $passedseries->set_color($colors['passed']);
            $chart->add_series($passedseries);
        }

        // Failed series.
        if (!empty($faileddata['series']) && array_sum($faileddata['series']) > 0) {
            $failedseries = new \core\chart_series(get_string('failed', 'block_dash'), $faileddata['series']);
            $failedseries->set_labels($faileddata['series_label']);
            $failedseries->set_color($colors['failed']);
            $chart->add_series($failedseries);
        }

        return $OUTPUT->render_chart($chart, true);
    }

    /**
     * Get the activity completion progress date colors from the general settings.
     *
     * @return array
     */
    public function get_completion_data_colors() {

        $notcompletedcolor = get_config('dashaddon_activitycompletion', 'activitynotcompletedcolor');
        $completedcolor = get_config('dashaddon_activitycompletion', 'activitycompletedcolor');
        $passedcolor = get_config('dashaddon_activitycompletion', 'activitypassedcolor');
        $failedcolor = get_config('dashaddon_activitycompletion', 'activityfailedcolor');

        // Define default colors for completion statuses.
        return [
            'notcompleted' => !empty($notcompletedcolor) ? $notcompletedcolor : self::COLORNOTCOMPLETED, // Pink for Incomplete.
            'completed' => !empty($completedcolor) ? $completedcolor : self::COLORCOMPLETED, // Green for Complete.
            'passed' => !empty($passedcolor) ? $passedcolor : self::COLORPASSED, // Blue for Passed.
            'failed' => !empty($failedcolor) ? $failedcolor : self::COLORFAILED, // Orange for Failed.
        ];
    }

    /**
     * Set the default preferences of the activity completion widget, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['filters']['current_course']['enabled'] = 1;
        $data['config_preferences'] = $configpreferences;
    }
}
