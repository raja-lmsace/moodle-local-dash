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
 * Courses data source.
 * @package    dashaddon_courses
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_courses\local\block_dash;

use context;
use block_dash\local\data_grid\filter\filter;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\date_filter;
use local_dash\data_grid\filter\tags_field_filter;
use local_dash\data_grid\filter\customfield_filter;
use block_dash\local\data_source\abstract_data_source;
use local_dash\data_grid\filter\category_field_filter;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\dash_framework\query_builder\where;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\course_format_field_filter;
use dashaddon_courses\local\dash_framework\structure\course_table;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use block_dash\local\data_grid\filter\filter_collection_interface;
use dashaddon_categories\local\dash_framework\structure\course_category_table;
use local_dash\data_grid\filter\completion_status_filter;
use local_dash\data_grid\filter\completion_status_condition;
use local_dash\data_grid\filter\selfenrol_condition;
use local_dash\data_grid\filter\hide_enrolled_courses_condition;
use local_dash\data_grid\filter\show_hidden_courses_condition;
use local_dash\data_grid\filter\course_dates_condition;
use local_dash\data_grid\filter\course_dates_filter;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/dash/lib.php');

/**
 * Courses data source.
 */
class courses_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param context $context
     */
    public function __construct(context $context) {
        $this->add_table(new course_table());
        $this->add_table(new course_category_table());
        parent::__construct($context);
    }

    /**
     * Return query template for retrieving user info.
     * @return string
     */
    public function get_query_template(): builder {
        global $USER;

        $builder = new builder();
        $builder
            ->select('c.id', 'c_id')
            ->from('course', 'c')
            ->join('course_categories', 'cc', 'id', 'c.category', join::TYPE_LEFT_JOIN)
            ->where('format', ['site'], where::OPERATOR_NOT_EQUAL);

        $coursepreferences = $this->get_preferences('filters');

        if (class_exists('\core_course\customfield\course_handler')) {
            $coursehandler = \core_course\customfield\course_handler::create();
            foreach ($coursehandler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                // Only join custom field table if the filter is enabled.
                if (isset($coursepreferences[$alias]) && $coursepreferences[$alias]['enabled']) {
                    $builder->join('customfield_data', $alias, 'instanceid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($coursepreferences[$alias]) && $coursepreferences[$alias]['enabled']) {
                    $builder->join('course_info_data', $alias, 'courseid', 'c.id', join::TYPE_LEFT_JOIN)
                        ->join_condition($alias, "$alias.fieldid = " . $field->get('id'));
                }
            }
        }

        $builder->rawcondition("c.format != 'site'");

        if (isset($coursepreferences['show_hidden_courses']) && !$coursepreferences['show_hidden_courses']['enabled']) {
            $hidden = new show_hidden_courses_condition('show_hidden_courses', 'c.id');
            [$sql, $params] = $hidden->get_sql_and_params();
            $builder = $builder->where_raw($sql, $params);
        }

        return $builder;
    }

    /**
     * Build and return filter collection.
     * @return filter_collection_interface
     */
    public function build_filter_collection() {

        $coursefilter = new filter_collection(get_class($this), $this->get_context());

        $coursefilter->add_filter(new category_field_filter('cc_id', 'cc.id', get_string('category')));

        $filter = new date_filter(
            'c_startdate',
            'c.startdate',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('startdate')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $coursefilter->add_filter($filter);

        $filter = new date_filter(
            'c_enddate',
            'c.enddate',
            date_filter::DATE_FUNCTION_FLOOR,
            get_string('enddate')
        );
        $filter->set_operation(filter::OPERATION_GREATER_THAN_EQUAL);
        $coursefilter->add_filter($filter);

        $coursefilter->add_filter(new course_format_field_filter('c_format', 'c.format'));

        $coursefilter->add_filter(new tags_field_filter(
            'c_tags',
            'c.id',
            'core',
            'course',
            get_string('coursetags', 'tag')
        ));

        $coursefilter->add_filter(new completion_status_filter('c_status', 'ue.status', get_string('status')));

        // Course dates filter - past, present, future.
        $coursefilter->add_filter(new course_dates_filter('f_coursedates', 'c.id'));

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
                        $coursefilter->add_filter(new date_filter(
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
                        $coursefilter->add_filter(new customfield_filter(
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
                        $coursefilter->add_filter(new date_filter(
                            $alias,
                            $select,
                            date_filter::DATE_FUNCTION_FLOOR,
                            $field->fullname
                        ));
                        break;
                    case 'textarea':
                        break;
                    default:
                        $coursefilter->add_filter(new customfield_filter(
                            $alias,
                            $select,
                            $field,
                            $field->fullname
                        ));
                        break;
                }
            }
        }

        $coursefilter->add_filter(new current_course_condition('c_current_course', 'c.id'));

        $coursefilter->add_filter(new course_condition('c_course', 'c.id'));

        $coursefilter->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        $coursefilter->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $coursefilter->add_filter(new hide_enrolled_courses_condition('hide_enrolled_courses', 'c.id'));

        $coursefilter->add_filter(new selfenrol_condition('c_selfenrol', 'c.id'));

        $coursefilter->add_filter(new completion_status_condition('c_completion_status', 'ue.status'));

        $coursefilter->add_filter(new show_hidden_courses_condition('show_hidden_courses', 'c.id'));

        // Course dates condition - past, present, future.
        $coursefilter->add_filter(new course_dates_condition('c_coursedates', 'c.id'));

        local_dash_customfield_conditions($coursefilter);

        return $coursefilter;
    }

    /**
     * Set the default preferences of the Course datasource, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['available_fields']['c_fullname']['visible'] = true;
        $configpreferences['available_fields']['c_startdate']['visible'] = true;
        $configpreferences['available_fields']['cc_name']['visible'] = true;
        $configpreferences['available_fields']['c_button']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }
}
