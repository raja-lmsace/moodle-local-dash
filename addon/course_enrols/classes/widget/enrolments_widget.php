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
 * Enrolments widget class contains the layout information and generate the data for widget.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols\widget;

use block_dash\local\widget\abstract_widget;
use block_dash\local\data_grid\filter\filter_collection;
use context_block;
use block_dash\local\layout\layout_factory;
use block_dash\local\layout\layout_interface;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\paginator;
use local_dash\data_grid\filter\course_category_condition;
use dashaddon_course_enrols\local\block_dash\data_grid\filter\completion_filter;
use dashaddon_course_enrols\local\block_dash\data_grid\filter\sort_status_filter;
use dashaddon_course_enrols\local\block_dash\data_grid\filter\user_filter;
use dashaddon_course_enrols\info;

/**
 * Enrolments widget class contains the layout information and generate the data for widget.
 */
class enrolments_widget extends abstract_widget {
    /**
     * Enrolment sort method.
     * @var string
     */
    protected $enrolmentsort;

    /**
     * User enrolment status.
     *
     * @var string
     */
    protected $enrolstatus;

    /**
     * User id.
     *
     * @var int
     */
    protected $userid;

    /**
     * Dash add enrolments form before courses.
     */
    public const DASH_ENROLMENTS_ABOVEFORM = 1;

    /**
     * Dash add enrolments form after courses.
     */
    public const DASH_ENROLMENTS_BELOWFORM = 2;

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
        return 'dashaddon_course_enrols/enrolments';
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
        return new enrolments_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'enrolments',
            'layout' => 'enrolments',
            'addcourse' => 1,
            'progress' => 1,
            'expandable' => 1,
        ];
        return $preferences;
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {
        global $PAGE, $USER, $CFG;

        $this->enrolmentsort = $this->get_default_sorting();
        $this->enrolstatus = 'all';
        $this->userid = info::get_related_userid();

        [$enrolledcourses, $count] = $this->generate_available_course_report();
        $addcourse = $this->get_preferences('addcourse');
        $capviewprofiledash = true;
        if ($PAGE->pagelayout == 'mypublic') {
            if ($this->userid != $USER->id) {
                $capviewprofiledash = info::has_capability('dashaddon/course_enrols:viewotherprofiledash');
            } else {
                $capviewprofiledash = info::has_capability('dashaddon/course_enrols:viewprofiledash');
            }
        }
        $this->data = [
            'userid' => $this->userid,
            'sort' => $this->enrolmentsort,
            'enrolstatus' => $this->enrolstatus,
            'courses' => !empty($enrolledcourses) ? array_values($enrolledcourses) : [],
            'contextid' => $this->get_block_instance()->context->id,
            'uniqueid' => $this->get_block_instance()->instance->id,
            'availablecoursestoenrol' => $this->get_available_courses_for_enrol($enrolledcourses),
            'availablecoursestoenrolstatus' => !empty($this->get_available_courses_for_enrol($enrolledcourses)) ? true : false,
            'sortingmenus' => \dashaddon_course_enrols\info::get_sorting_menus(),
            'usersselector' => ($PAGE->pagelayout != 'mypublic'),
            'currentuser' => \dashaddon_course_enrols\info::get_related_userid(),
            'addcourseform' => $addcourse,
            'abovecourseform' => ($addcourse == self::DASH_ENROLMENTS_ABOVEFORM),
            'belowcourseform' => ($addcourse == self::DASH_ENROLMENTS_BELOWFORM),
            'progress' => $this->get_preferences('progress'),
            'expandable' => $this->get_preferences('expandable'),
            'coursescount' => $count,
            'hascapeditenrolment' => info::has_capability('dashaddon/course_enrols:editenrolment'),
            'hascapunenrol' => info::has_capability('dashaddon/course_enrols:unenrol'),
            'hascapenrol' => info::has_capability('dashaddon/course_enrols:enrol'),
            'hascapviewdetails' => info::has_capability('dashaddon/course_enrols:viewdetails'),
            'capviewprofiledash' => $capviewprofiledash,
            'datatoggle' => $CFG->branch >= 500 ? 'data-bs-toggle' : 'data-toggle',
            'datatarget' => $CFG->branch >= 500 ? 'data-bs-target' : 'data-target',
        ];
        return $this->data;
    }

    /**
     * Get table pagination class.
     * @return paginator
     */
    public function widget_data_count() {
        return $this->data['coursescount'];
    }

    /**
     * Get default sorting method.
     *
     * @return string
     */
    public function get_default_sorting() {
        $sort = $this->get_preferences('default_sort');
        $sortdirection = $this->get_preferences('default_sort_direction');
        $menus = \dashaddon_course_enrols\info::get_sorting_menus();
        return isset($menus[$sort . '_' . $sortdirection]) ? $sort . '_' . $sortdirection : 'enrolmentdate_asc';
    }

    /**
     * Get potential courses for enrol the user.
     *
     * @param int $enrolled
     * @return array $enrolcourses
     */
    public function get_available_courses_for_enrol($enrolled) {
        global $DB;

        $enrolcourses = [];
        $enrol = enrol_get_plugin('manual');
        $courses = (class_exists('\core_course_category'))
            ? \core_course_category::top()->get_courses(['recursive' => true])
            : \coursecat::get(0)->get_courses(['recursive' => true]);

        $enrolledcourses = array_column((array) $enrolled, 'id');
        foreach ($courses as $courseid => $course) {
            $enrolinstances = enrol_get_instances($courseid, true);
            foreach ($enrolinstances as $instance) {
                if ($instance->enrol == "manual" && !in_array($courseid, $enrolledcourses)) {
                    // Instance specified.
                    $enrolcourses[] = ['name' => $course->get_formatted_fullname(), 'id' => $courseid];
                }
            }
        }

        return $enrolcourses;
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_available_course_report() {
        global $DB;

        $paginator = (isset($this->paginator)) ? $this->paginator : null;
        $limitfrom = ($paginator != null) ? $paginator->get_limit_from() : 0;
        $limitnum = ($paginator != null) ? $paginator->get_per_page() : $this->get_preferences('perpage');

        $filters = $this->get_filter_collection()->get_filters();
        $categorysql = '';
        $params = [];
        foreach ($filters as $filter) {
            if (
                $filter->get_name() == 'c_course_categories_condition'
                && $filter->get_preferences('enabled') && $filter->get_preferences()
            ) {
                if (!$filter->get_preferences()['enabled']) {
                    continue;
                }

                [$sql, $sqlparams] = $filter->get_sql_and_params();
                if (!empty($sql)) {
                    $categorysql = "AND ($sql)";
                    $params = $sqlparams;
                }
            }

            if ($filter->get_name() == 'c_status') {
                [$insql, $inparams] = $filter->get_sql_and_params();
                $categorysql .= ($insql) ? " AND $insql " : '';
                $params += $inparams;
            }

            if ($filter->get_name() == 'c_sort') {
                $values = $filter->get_values();
                if (!empty($values)) {
                    $menus = \dashaddon_course_enrols\info::get_sort_sql();
                    $sort = reset($values);
                    $this->enrolmentsort = isset($menus[$sort]) ? $sort : $this->enrolmentsort;
                }
            }

            if ($filter->get_name() == 'c_mentees') {
                $values = $filter->get_values();
                if (!empty($values)) {
                    $user = reset($values);
                    $this->userid = $user ? $user : $this->userid;
                }
            }
        }

        return \dashaddon_course_enrols\info::get_courses_list(
            $this->userid,
            $this->enrolmentsort,
            $this->enrolstatus,
            $limitfrom,
            $limitnum,
            $categorysql,
            $params
        );
    }

    /**
     * Prefence form for widget. We make the fields disable other than the general.
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

        if ($form->get_tab() == preferences_form::TAB_FIELDS) {
            $mform->addElement('html', '<hr>');

            $strs = get_strings(['sortalpha', 'sortenrolmentdate', 'sortcoursestartdate'], 'block_dash');
            $sortablefields = [
                'alpha' => $strs->sortalpha,
                'enrolmentdate' => $strs->sortenrolmentdate,
                'coursestartdate' => $strs->sortcoursestartdate,
            ];

            $preferences = $this->get_all_preferences();
            $choices = [
                1 => get_string('enable', 'core'),
                0 => get_string('disable', 'core'),
            ];

            $mform->addElement(
                'select',
                'config_preferences[default_sort]',
                get_string('defaultsortfield', 'block_dash'),
                $sortablefields
            );
            $mform->setType('config_preferences[default_sort]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[default_sort]', 'defaultsortfield', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[default_sort_direction]',
                get_string('defaultsortdirection', 'block_dash'),
                [ 'asc' => 'ASC', 'desc' => 'DESC']
            );
            $mform->setType('config_preferences[default_sort_direction]', PARAM_TEXT);

            $mform->addElement('text', 'config_preferences[perpage]', get_string('perpage', 'block_dash'));
            $mform->setType('config_preferences[perpage]', PARAM_INT);
            $mform->addHelpButton('config_preferences[perpage]', 'perpage', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[progress]',
                get_string('course_enrolments:progress', 'block_dash'),
                $choices
            );
            $mform->setType('config_preferences[progress]', PARAM_INT);
            $mform->addHelpButton('config_preferences[progress]', 'progress', 'block_dash');
            if (!isset($preferences['progress'])) {
                $mform->setDefault('config_preferences[progress]', 1);
            }

            $mform->addElement(
                'select',
                'config_preferences[expandable]',
                get_string('course_enrolments:expandable', 'block_dash'),
                $choices
            );
            $mform->setType('config_preferences[expandable]', PARAM_INT);
            $mform->addHelpButton('config_preferences[expandable]', 'expandable', 'block_dash');
            if (!isset($preferences['expandable'])) {
                $mform->setDefault('config_preferences[expandable]', 1);
            }

            $choices = [
                0 => get_string('disable', 'core'),
                1 => get_string('course_enrolments:abovecourseform', 'block_dash'),
                2 => get_string('course_enrolments:belowcourseform', 'block_dash'),
            ];
            $mform->addElement(
                'select',
                'config_preferences[addcourse]',
                get_string('course_enrolments:displayaddcourse', 'block_dash'),
                $choices
            );
            $mform->setType('config_preferences[addcourse]', PARAM_INT);
            $mform->addHelpButton('config_preferences[addcourse]', 'addcourse', 'block_dash');
        } else {
            $mform->addElement('html', get_string('fieldalert', 'block_dash'), 'fieldalert');
        }
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {
        global $PAGE;

        $filtercollection = new filter_collection(get_class($this), $this->get_context());
        if ($PAGE->pagelayout != 'mypublic') {
            $filtercollection->add_filter(new user_filter('c_mentees', 'u.id', get_string('user')));
        }

        $filtercollection->add_filter(
            new course_category_condition('c_course_categories_condition', 'c.category')
        );

        $filtercollection->add_filter(new completion_filter('c_status', 'ue.status', get_string('status')));

        $filtercollection->add_filter(new sort_status_filter('c_sort', 'ue.sort', get_string('sort')));

        return $filtercollection;
    }
}
