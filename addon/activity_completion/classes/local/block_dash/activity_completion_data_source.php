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
 * Activity completion report source defined.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash;

use block_dash\local\data_source\abstract_data_source;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\date_filter;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\structure\user_table;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use local_dash\data_grid\filter\course_dates_condition;
use local_dash\data_grid\filter\relations_role_condition;
use block_dash\local\data_grid\filter\logged_in_user_condition;
use local_dash\data_grid\filter\tags_condition;
use local_dash\data_grid\filter\cohort_condition;
use local_dash\data_grid\filter\users_mycohort_condition;
use local_dash\data_grid\filter\customfield_filter;
use local_dash\data_grid\filter\category_field_filter;
use local_dash\data_grid\filter\course_field_filter;
use local_dash\data_grid\filter\tags_field_filter;
use dashaddon_activities\local\dash_framework\structure\activities_table;
use dashaddon_activities\local\block_dash\data_grid\filter\module_field_filter;
use dashaddon_activities\local\block_dash\data_grid\filter\activity_type_field_filter;
use dashaddon_activities\local\block_dash\data_grid\filter\activity_purpose_field_filter;
use dashaddon_activity_completion\local\dash_framework\structure\activity_completion_table;
use dashaddon_activity_completion\local\dash_framework\structure\activity_grade_table;
use dashaddon_activity_completion\local\dash_framework\structure\activity_action_table;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\user_filter;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_status_filter;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_name_filter;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_completion_status_condition;
use local_dash\data_grid\filter\activity_modulename_condition;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_status_condition;
use dashaddon_courses\local\dash_framework\structure\course_table;
use dashaddon_categories\local\dash_framework\structure\course_category_table;

/**
 * Activity completion data source template queries and filter conditions defined.
 */
class activity_completion_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {
        $this->add_table(new activities_table());
        $this->add_table(new user_table());
        $this->add_table(new activity_completion_table());
        $this->add_table(new activity_grade_table());
        $this->add_table(new activity_action_table());
        $this->add_table(new course_table());
        $this->add_table(new course_category_table());
        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the activity completion data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER, $DB, $USER, $PAGE;

        $builder = new \local\dash\addon\activity_completion\local\block_dash\builder();
        $builder
            ->select('uniqueid', 'unique_id')
            ->select('cm.id', 'cm_id')
            ->select('u.id', 'u_id')
            ->select('cmc.id', 'cmc_id')
            ->select('cm.instance', 'cm_instance')
            ->select('cm.module', 'cm_module')
            ->select('cm.completion', 'cm_completion')
            ->select('cmc.completionstate', 'cmc_completionstate')
            ->select('cs.id', 'cm_section')
            ->select('c.id', 'cm_course')
            ->select('cc.id', 'cm_category')
            ->select('gt.id', 'gt_id')
            ->select('gg.itemid', 'gg_itemid')
            ->select('gg.finalgrade', 'gg_finalgrade')
            ->select('ue.userid', 'ue_userid')
            ->from('course_modules', 'cm')
            ->join('modules', 'm', 'id', 'cm.module AND m.visible = 1')
            ->join('course', 'c', 'id', 'cm.course')
            ->join('course_categories', 'cc', 'id', 'c.category')
            ->join('course_sections', 'cs', 'id', 'cm.section')
            ->join('enrol', 'e', 'courseid', 'c.id')
            ->join('user_enrolments', 'ue', 'enrolid', 'e.id')
            ->join('user', 'u', 'id', 'ue.userid')
            ->join('course_modules_completion', 'cmc', 'coursemoduleid', 'cm.id AND cmc.userid = ue.userid', join::TYPE_LEFT_JOIN)
            ->join('grade_items', 'gt', 'itemmodule', 'm.name AND gt.iteminstance = cm.instance', join::TYPE_LEFT_JOIN)
            ->join('grade_grades', 'gg', 'itemid', 'gt.id AND gg.userid = ue.userid', join::TYPE_LEFT_JOIN);

        if (dashaddon_activity_completion_is_timetable_installed()) {
            $builder->select('tm.duedatecustom', 'tm_duedate');
            $builder->join('tool_timetable_modules', 'tm', 'cmid', 'cm.id', join::TYPE_LEFT_JOIN);
        }

        $activityfilterpreferences = $this->get_preferences('filters');
        if (dashaddon_activities_is_local_metadata_installed()) {
            $coursemodulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
            foreach ($coursemodulefields as $field) {
                $al = 'cm_mf_' . strtolower($field->shortname);
                if (isset($activityfilterpreferences[$al]) && $activityfilterpreferences[$al]['enabled']) {
                    $builder->join('local_metadata', $al, 'instanceid', 'cm.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($al, "$al.fieldid = " . $field->id);
                }
            }
        }

        if (class_exists('\core_course\customfield\course_handler')) {
            $coursemodulehandler = \core_course\customfield\course_handler::create();
            foreach ($coursemodulehandler->get_fields() as $field) {
                $al = 'c_f_' . strtolower($field->get('shortname'));
                // Only join custom field table if the filter is enabled.
                if (isset($activityfilterpreferences[$al]) && $activityfilterpreferences[$al]['enabled']) {
                    $builder->join('customfield_data', $al, 'instanceid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($al, "$al.fieldid = " . $field->get('id'));
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;
            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($activityfilterpreferences[$alias]) && $activityfilterpreferences[$alias]['enabled']) {
                    $builder->join('course_info_data', $alias, 'courseid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        }

        // Check if the user is an admin.
        $bypassadmin = 1;
        if (has_capability('moodle/course:viewhiddenactivities', \context_system::instance())) {
            $bypassadmin = 0;
        }

        $builder->rawcondition('u.deleted = 0');
        $builder->where_raw("cm.deletioninprogress = 0 AND (cm.visible = 1 OR cm.visible = $bypassadmin)");

        // Js include.
        $PAGE->requires->js_call_amd(
            'dashaddon_activity_completion/overrideactivitycompletion',
            'init',
            ['blockinstanceid' => $this->get_block_instance()->instance->id]
        );

        $PAGE->requires->js_call_amd(
            'dashaddon_activity_completion/activitygrade',
            'init',
            ['blockinstanceid' => $this->get_block_instance()->instance->id]
        );

        return $builder;
    }

    /**
     * Filter conditions are added to activity completion preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        global $DB;

        $cmfiltercollection = new filter_collection(get_class($this), $this->get_context());

        // Course category filter.
        $cmfiltercollection->add_filter(new category_field_filter('cc_id', 'cc.id', get_string('category')));

        // Course filter.
        $cmfiltercollection->add_filter(new course_field_filter('c_id', 'c.id', get_string('course')));

        // Module name filter.
        $cmfiltercollection->add_filter(new module_field_filter('m_id', 'm.id', get_string('modulename', 'block_dash')));

        // Activity tag filter.
        $cmfiltercollection->add_filter(new tags_field_filter(
            'cm_tags',
            'cm.id',
            'core',
            'course_modules',
            get_string('activitytags', 'dashaddon_activities')
        ));

        // Activity type filter.
        $cmfiltercollection->add_filter(new activity_type_field_filter('cm_type', ''));

        // Activity purpose filter.
        $cmfiltercollection->add_filter(new activity_purpose_field_filter('cm_purpose', ''));

        // Activity status filter.
        if (dashaddon_activity_completion_is_timetable_installed()) {
            $cmfiltercollection->add_filter(new activity_status_filter('activity_status', ''));
        }

        // User filter.
        $cmfiltercollection->add_filter(new user_filter('u_id', 'u.id', get_string('user')));

        // Activity filter.
        $cmfiltercollection->add_filter(new activity_name_filter('cm_id', 'cm.id'));

        if (dashaddon_activities_is_local_metadata_installed()) {
            $coursemodulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
            foreach ($coursemodulefields as $field) {
                $alias = 'cm_mf_' . strtolower($field->shortname);
                $select = $alias . '.data';

                switch ($field->datatype) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, format_string($field->name));
                        break;
                    case 'datetime':
                        $cmfiltercollection->add_filter(new date_filter(
                            $alias,
                            $select,
                            date_filter::DATE_FUNCTION_FLOOR,
                            format_string($field->name)
                        ));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $cmfiltercollection->add_filter(new customfield_filter(
                            $alias,
                            $select,
                            $field,
                            format_string($field->name)
                        ));
                        break;
                }
            }
        }

        if (class_exists('\core_course\customfield\course_handler')) {
            $coursemodulehandler = \core_course\customfield\course_handler::create();
            foreach ($coursemodulehandler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                $select = $alias . '.value';

                switch ($field->get('type')) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->get_formatted_name());
                        break;
                    case 'date':
                        $cmfiltercollection->add_filter(new date_filter(
                            $alias,
                            $select,
                            date_filter::DATE_FUNCTION_FLOOR,
                            $field->get_formatted_name()
                        ));
                        break;
                    case 'textarea':
                        break;
                    default:
                        if (
                            class_exists('\customfield_multicategory\condition_helper') &&
                            \customfield_multicategory\condition_helper::should_skip_default_filter($field->get('type'))
                        ) {
                            break;
                        }
                        $cmfiltercollection->add_filter(new customfield_filter(
                            $alias,
                            $select,
                            $field,
                            $field->get_formatted_name()
                        ));
                        break;
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                $select = $alias . '.data';

                switch ($field->datatype) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->fullname);
                        break;
                    case 'date':
                        $cmfiltercollection->add_filter(new date_filter(
                            $alias,
                            $select,
                            date_filter::DATE_FUNCTION_FLOOR,
                            $field->fullname
                        ));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $cmfiltercollection->add_filter(new customfield_filter(
                            $alias,
                            $select,
                            $field,
                            $field->fullname
                        ));
                        break;
                }
            }
        }

        // Course category condition.
        $cmfiltercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        // My enrolled course condition.
        $cmfiltercollection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        // Course condition.
        $cmfiltercollection->add_filter(new course_condition('c_course', 'c.id'));

        // Activity tag condition.
        $cmfiltercollection->add_filter(new tags_condition(
            'activity_tags',
            'cm.id',
            'core',
            'course_modules',
            get_string('activitytags', 'dashaddon_activities')
        ));

        // Course dates condition - past, present, future.
        $cmfiltercollection->add_filter(new course_dates_condition('c_coursedates', 'c.id'));

        // Partent role condition (Users i manage).
        $cmfiltercollection->add_filter(new relations_role_condition('parentrole', 'u.id'));

        // Current user.
        $cmfiltercollection->add_filter(new logged_in_user_condition('current_user', 'u.id'));

        // Cohorts condition.
        $cmfiltercollection->add_filter(new cohort_condition('cohort', 'u.id'));

        // Members of my cohorts condition.
        $cmfiltercollection->add_filter(new users_mycohort_condition('users_mycohort', 'u.id'));

        // Activity completion status.
        $cmfiltercollection->add_filter(new activity_completion_status_condition(
            'activitycompletion_status',
            'cmc.completionstate'
        ));

        // Module name condition.
        $cmfiltercollection->add_filter(new activity_modulename_condition('modulename', 'm.id'));

        // Activity status.
        if (dashaddon_activity_completion_is_timetable_installed()) {
            $cmfiltercollection->add_filter(new activity_status_condition('activitystatus', ''));
        }

        if (dashaddon_activities_is_local_metadata_installed()) {
            dashaddon_activities_customfield_conditions($cmfiltercollection);
        }
        return $cmfiltercollection;
    }

    /**
     * Set the default preferences of the activity completion datasource, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['available_fields']['cm_modicon']['visible'] = true;
        $configpreferences['available_fields']['u_firstname']['visible'] = true;
        $configpreferences['available_fields']['cm_name']['visible'] = true;
        $configpreferences['available_fields']['cm_modsection']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }

    /**
     * This activity completion data source counted by uniqueid.
     *
     * @return bool
     */
    public function count_by_uniqueid() {
        return true;
    }

    /**
     * Is the data source needs to load the js when it the content updated using JS.
     *
     * @return bool
     */
    public function supports_currentscript() {
        return false;
    }

    /**
     * Load the pagination via ajax.
     *
     * For the large data sets, it is better to load the pagination via ajax.
     */
    public function supports_ajax_pagination() {
        return true;
    }
}
