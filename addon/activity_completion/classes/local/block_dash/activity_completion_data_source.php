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
 * Badges report source defined.
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
use local_dash\data_grid\filter\parent_role_condition;
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
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_dates_filter;
use dashaddon_activity_completion\local\block_dash\data_grid\filter\activity_completion_status_condition;

use dashaddon_courses\local\dash_framework\structure\course_table;
use dashaddon_categories\local\dash_framework\structure\course_category_table;

/**
 * Badges data source template queries and filter conditions defined.
 */
class activity_completion_data_source extends abstract_data_source {

    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {
        $this->add_table(new activities_table());
        $this->add_table(new activity_completion_table());
        $this->add_table(new activity_grade_table());
        $this->add_table(new activity_action_table());
        $this->add_table(new course_table());
        $this->add_table(new course_category_table());
        $this->add_table(new user_table());
        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the activity completion data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER, $DB, $USER;
        $builder = new builder();
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
            ->from('course_modules', 'cm')
            ->join('modules', 'm', 'id', 'cm.module')
            ->join('course', 'c', 'id', 'cm.course')
            ->join('context', 'ctx', 'instanceid', 'c.id', join::TYPE_INNER_JOIN, ['courselevel' => 50])
            ->join_condition('ctx', 'ctx.contextlevel=:courselevel')
            ->join('role_assignments', 'ra', 'contextid', 'ctx.id', join::TYPE_INNER_JOIN)
            //->join_condition('ra', 'ra.roleid=:roleid')
            ->join('user', 'u', 'id', 'ra.userid')
            ->join('course_modules_completion', 'cmc', 'coursemoduleid', 'cm.id AND cmc.userid = u.id', join::TYPE_LEFT_JOIN)
            ->join('course_categories', 'cc', 'id', 'c.category')
            ->join('course_sections', 'cs', 'id', 'cm.section')
            ->join('grade_items', 'gt', 'itemmodule', 'm.name AND gt.iteminstance = cm.instance', join::TYPE_LEFT_JOIN)
            ->join('grade_grades', 'gg', 'itemid', 'gt.id AND gg.userid = u.id', join::TYPE_LEFT_JOIN);

        $filterpreferences = $this->get_preferences('filters');
        if (dashaddon_activities_is_local_metadata_installed()) {
            $modulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
            foreach ($modulefields as $field) {
                $al = 'cm_mf_' . strtolower($field->shortname);
                if (isset($filterpreferences[$al]) && $filterpreferences[$al]['enabled']) {
                    $builder->join('local_metadata', $al, 'instanceid', 'cm.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($al, "$al.fieldid = " . $field->id);
                }
            }
        }

        if (class_exists('\core_course\customfield\course_handler')) {
            $coursehandler = \core_course\customfield\course_handler::create();
            foreach ($coursehandler->get_fields() as $field) {
                $al = 'c_f_' . strtolower($field->get('shortname'));
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$al]) && $filterpreferences[$al]['enabled']) {
                    $builder->join('customfield_data', $al, 'instanceid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($al, "$al.fieldid = " . $field->get('id'));
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;
            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$alias]) && $filterpreferences[$alias]['enabled']) {
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

        $builder->where_raw("cm.deletioninprogress = 0 AND (cm.visible = 1 OR cm.visible = $bypassadmin)");
        $builder->orderby('u.id', 'asc');
        return $builder;

    }

    /**
     * Filter conditions are added to activity completion preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        global $DB;

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        // Course category filter.
        $filtercollection->add_filter(new category_field_filter('cc_id', 'cc.id', get_string('category')));

        // Course filter.
        $filtercollection->add_filter(new course_field_filter('c_id', 'c.id', get_string('course')));

        // Module name filter.
        $filtercollection->add_filter(new module_field_filter('m_id', 'm.id', get_string('modulename', 'block_dash')));

        // Activity tag filter.
        $filtercollection->add_filter(new tags_field_filter('cm_tags', 'cm.id', 'core', 'course_modules',
            get_string('activitytags', 'dashaddon_activities')));

        // Activity type filter.
        $filtercollection->add_filter(new activity_type_field_filter('cm_type', ''));

        // Activity purpose filter.
        $filtercollection->add_filter(new activity_purpose_field_filter('cm_purpose', ''));

        // Activity status filter
        $filtercollection->add_filter(new activity_dates_filter('cm_id', ''));

        // User filter.
        $filtercollection->add_filter(new user_filter('u_id', 'u.id', get_string('user')));


        if (dashaddon_activities_is_local_metadata_installed()) {
            $modulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
            foreach ($modulefields as $field) {

                $alias = 'cm_mf_' . strtolower($field->shortname);
                $select = $alias . '.data';

                switch ($field->datatype) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, format_string($field->name));
                        break;
                    case 'datetime':
                        $filtercollection->add_filter(new date_filter($alias, $select, date_filter::DATE_FUNCTION_FLOOR,
                        format_string($field->name)));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $filtercollection->add_filter(new customfield_filter($alias, $select, $field,
                        format_string($field->name)));
                        break;
                }
            }
        }

        if (class_exists('\core_course\customfield\course_handler')) {
            $coursehandler = \core_course\customfield\course_handler::create();
            foreach ($coursehandler->get_fields() as $field) {

                $alias = 'c_f_' . strtolower($field->get('shortname'));
                $select = $alias . '.value';

                switch ($field->get('type')) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->get_formatted_name());
                        break;
                    case 'date':
                        $filtercollection->add_filter(new date_filter($alias, $select, date_filter::DATE_FUNCTION_FLOOR,
                            $field->get_formatted_name()));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $filtercollection->add_filter(new customfield_filter($alias, $select, $field,
                            $field->get_formatted_name()));
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
                        $filtercollection->add_filter(new date_filter($alias, $select, date_filter::DATE_FUNCTION_FLOOR,
                            $field->fullname));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $filtercollection->add_filter(new customfield_filter($alias, $select, $field,
                            $field->fullname));
                        break;
                }
            }
        }

        // Course category condition.
        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        // My enrolled course condition.
        $filtercollection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        // Course condition.
        $filtercollection->add_filter(new course_condition('c_course', 'c.id'));

        // Activity tag condition.
        $filtercollection->add_filter(new tags_condition('activity_tags', 'cm.id', 'core', 'course_modules',
            get_string('activitytags', 'dashaddon_activities')));

        // Course dates condition - past, present, future.
        $filtercollection->add_filter(new course_dates_condition('c_coursedates', 'c.id'));

        // Partent role condition (Users i manage).
        $filtercollection->add_filter(new parent_role_condition('parentrole', 'u.id'));

        // Current user.
        $filtercollection->add_filter(new logged_in_user_condition('current_user', 'u.id'));

        // Cohorts condition.
        $filtercollection->add_filter(new cohort_condition('cohort', 'u.id'));

        // Members of my cohorts condition.
        $filtercollection->add_filter(new users_mycohort_condition('users_mycohort', 'u.id'));

        // Activity completion status.
        $filtercollection->add_filter(new activity_completion_status_condition('activitycompletion_status', 'cm.id'));

        if (dashaddon_activities_is_local_metadata_installed()) {
            dashaddon_activities_customfield_conditions($filtercollection);
        }
        return $filtercollection;
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
        $configpreferences['available_fields']['cm_name']['visible'] = true;
        $configpreferences['available_fields']['c_fullname']['visible'] = true;
        $configpreferences['available_fields']['cm_modsection']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }

}
