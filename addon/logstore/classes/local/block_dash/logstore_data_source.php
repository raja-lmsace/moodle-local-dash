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
 * Logstore data source.
 * @package    dashaddon_logstore
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_logstore\local\block_dash;

use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\structure\user_table;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\date_filter;
use block_dash\local\data_grid\filter\filter;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_grid\filter\group_filter;
use block_dash\local\data_grid\filter\logged_in_user_condition;
use block_dash\local\data_grid\filter\my_groups_condition;
use block_dash\local\data_source\abstract_data_source;
use core\output\block;
use local_dash\data_grid\filter\category_field_filter;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\course_field_filter;
use local_dash\data_grid\filter\course_format_field_filter;
use block_dash\local\data_grid\filter\current_course_condition;
use local_dash\data_grid\filter\customfield_filter;
use local_dash\data_grid\filter\enrollment_method_field_filter;
use local_dash\data_grid\filter\enrollment_nonself_condition;
use local_dash\data_grid\filter\enrollment_self_condition;
use local_dash\data_grid\filter\enrollment_status_field_filter;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use local_dash\data_grid\filter\relations_role_condition;
use local_dash\data_grid\filter\tags_field_filter;
use dashaddon_categories\local\dash_framework\structure\course_category_table;
use dashaddon_courses\local\dash_framework\structure\course_table;
use dashaddon_logstore\local\dash_framework\structure\site_logs_table;
use local_dash\data_grid\filter\event_condition;

/**
 * Logstore data source.
 */
class logstore_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct(\context $context) {
        $this->add_table(new site_logs_table());
        $this->add_table(new course_table());
        $this->add_table(new course_category_table());
        $this->add_table(new user_table());

        parent::__construct($context);
    }

    /**
     * Return query template for retrieving user info.
     * @return builder
     */
    public function get_query_template(): builder {
        global $DB;
        $builder = new builder();
        $builder->select('sl.id', 'sl_id')
            ->from('logstore_standard_log', 'sl')
            ->join('user', 'u', 'id', 'sl.relateduserid')
            ->join('course', 'c', 'id', 'sl.courseid')
            ->join('course_categories', 'cc', 'id', 'c.category')
            ->join('enrol', 'e', 'courseid', 'c.id')
            ->join('groups_members', 'gm', 'userid', 'u.id', join::TYPE_LEFT_JOIN)
            ->join('groups', 'g', 'id', 'gm.groupid', join::TYPE_LEFT_JOIN)
            ->join('user_enrolments', 'ue', 'userid', 'u.id', join::TYPE_LEFT_JOIN)
            ->join_condition('ue', 'ue.enrolid = e.id');

        if (class_exists('\core_course\customfield\course_handler')) {
            $loghandler = \core_course\customfield\course_handler::create();
            foreach ($loghandler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$alias]) && $filterpreferences[$alias]['enabled']) {
                    $builder->join('customfield_data', $alias, 'instanceid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        } else if (block_dash_is_totara()) {
            $records = $DB->get_records('course_info_field');
            foreach ($records as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($filterpreferences[$alias]) && $filterpreferences[$alias]['enabled']) {
                    $builder->join('course_info_data', $alias, 'courseid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        }
        $builder->rawcondition('u.deleted = 0');

        return $builder;
    }

    /**
     * Build and return filter collection.
     * @return filter_collection_interface
     */
    public function build_filter_collection() {
        $logfiltercollection = new filter_collection(get_class($this), $this->get_context());

        $logfiltercollection->add_filter(new category_field_filter('cc_id', 'cc.id', get_string('category')));

        $logfiltercollection->add_filter(new course_field_filter('c_course', 'c.id'));

        $filter = new date_filter(
            'c_startdate',
            'c.startdate',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('startdate')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $logfiltercollection->add_filter($filter);

        $filter = new date_filter(
            'c_enddate',
            'c.enddate',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('enddate')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $logfiltercollection->add_filter($filter);

        $logfiltercollection->add_filter(new course_format_field_filter('c_format', 'c.format'));

        $logfiltercollection->add_filter(new tags_field_filter(
            'c_tags',
            'c.id',
            'core',
            'course',
            get_string('coursetags', 'tag')
        ));

        $logfiltercollection->add_filter(new relations_role_condition('parentrole', 'u.id'));

        if (class_exists('\core_course\customfield\course_handler')) {
            $loghandler = \core_course\customfield\course_handler::create();
            foreach ($loghandler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                $select = $alias . '.value';

                switch ($field->get('type')) {
                    case 'checkbox':
                        $definitions[] = new bool_filter($alias, $select, $field->get_formatted_name());
                        break;
                    case 'date':
                        $logfiltercollection->add_filter(new date_filter(
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
                        $logfiltercollection->add_filter(new customfield_filter(
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
                        $logfiltercollection->add_filter(new date_filter(
                            $alias,
                            $select,
                            date_filter::DATE_FUNCTION_FLOOR,
                            $field->fullname
                        ));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $logfiltercollection->add_filter(new customfield_filter(
                            $alias,
                            $select,
                            $field,
                            $field->fullname
                        ));
                        break;
                }
            }
        }

        $logfiltercollection->add_filter(new group_filter('group', 'g.id'));

        $logfiltercollection->add_filter(new enrollment_method_field_filter('enrol_method', 'e.enrol'));

        $logfiltercollection->add_filter(new enrollment_status_field_filter('enrolment_status', 'ue.status'));

        $filter = new date_filter(
            'ue_timestart',
            'ue.timestart',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('enrollmenttimestart', 'block_dash')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $logfiltercollection->add_filter($filter);

        $filter = new date_filter(
            'ue_timeend',
            'ue.timeend',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('enrollmenttimeend', 'block_dash')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $logfiltercollection->add_filter($filter);

        $logfiltercollection->add_filter(new current_course_condition('c_current_course', 'c.id'));

        $logfiltercollection->add_filter(new my_groups_condition('my_groups', 'gm300.groupid'));

        $logfiltercollection->add_filter(new enrollment_self_condition('self_enrollments', 'e.enrol'));
        $logfiltercollection->add_filter(new enrollment_nonself_condition('nonself_enrollments', 'e.enrol'));

        $logfiltercollection->add_filter(new course_condition('c_course_condition', 'c.id'));

        $logfiltercollection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        $logfiltercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $logfiltercollection->add_filter(new logged_in_user_condition('loggedin', 'u.id'));

        $logfiltercollection->add_filter(new event_condition('eventname', 'sl.eventname', get_string('events', 'block_dash')));

        return $logfiltercollection;
    }

    /**
     * Set the default preferences of the Course datasource, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['available_fields']['sl_eventname']['visible'] = true;
        $configpreferences['available_fields']['sl_eventdescription']['visible'] = true;
        $configpreferences['available_fields']['sl_timecreated']['visible'] = true;
        $configpreferences['available_fields']['sl_timeago']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }
}
