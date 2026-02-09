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
 * @package    dashaddon_roleassignments
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_roleassignments\local\block_dash;

use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\structure\user_table;
use block_dash\local\data_grid\filter\filter;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_source\abstract_data_source;
use dashaddon_roleassignments\local\dash_framework\structure\role_table;
use dashaddon_roleassignments\local\dash_framework\structure\role_assignments_table;
use dashaddon_roleassignments\local\dash_framework\structure\role_context_table;
use local_dash\data_grid\filter\user_fullname_filter;
use local_dash\data_grid\filter\context_level_filter;
use local_dash\data_grid\filter\context_name_filter;
use local_dash\data_grid\filter\role_name_filter;
use local_dash\data_grid\filter\course_category_condition;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use dashaddon_learningpath\local\block_dash\data_grid\filter\current_category_condition;
use local_dash\data_grid\filter\role_condition;
use local_dash\data_grid\filter\context_level_condition;

/**
 * Class roleassignments_data_source
 *
 * This class extends the abstract_data_source class and is responsible for handling
 * role assignments data source within the block_dash addon in Moodle.
 */
class roleassignments_data_source extends abstract_data_source {
    /**
     * Constructor for the roleassignments_data_source class.
     * It then calls the parent constructor with the provided context.
     *
     * @param \context $context The context in which the data source is being used.
     */
    public function __construct(\context $context) {
        $this->add_table(new role_table());
        $this->add_table(new role_assignments_table());
        $this->add_table(new role_context_table());
        $this->add_table(new user_table());
        parent::__construct($context);
    }

    /**
     * Constructs and returns a query template for retrieving role assignments data.
     * The query includes a condition to exclude deleted users (u.deleted = 0).
     *
     * @return builder The query builder object with the constructed query.
     */
    public function get_query_template(): builder {
        $builder = new builder();
        $builder->select('ra.id', 'ra_id')
            ->select('u.id', 'user_id')
            ->select('ctx.id', 'context_id')
            ->select('ctx.contextlevel', 'context_level')
            ->select('r.id', 'role_id')
            ->from('role_assignments', 'ra')
            ->join('user', 'u', 'id', 'ra.userid')
            ->join('role', 'r', 'id', 'ra.roleid')
            ->join('context', 'ctx', 'id', 'ra.contextid')
            ->join(
                'course',
                'c',
                'id',
                'ctx.instanceid AND ctx.contextlevel = :coursecontextlevel',
                join::TYPE_LEFT_JOIN,
                ['coursecontextlevel' => CONTEXT_COURSE]
            );

        $builder->where_raw('u.deleted = 0');
        return $builder;
    }

    /**
     * Builds and returns a filter collection for role assignments data source.
     *
     *
     * @return filter_collection The constructed filter collection.
     */
    public function build_filter_collection() {
        $collection = new filter_collection(get_class($this), $this->get_context());
        $collection->add_filter(new user_fullname_filter('user_id', 'u.id'));
        $collection->add_filter(new context_level_filter('context_level', 'ctx.contextlevel'));
        $collection->add_filter(new context_name_filter('context_id', 'ctx.id'));
        $collection->add_filter(new role_name_filter('role_id', 'r.id'));

        $collection->add_filter(new role_condition('rolecondition', 'r.id'));
        $collection->add_filter(new context_level_condition('context_level_condition', 'ctx.contextlevel'));
        $collection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));
        $collection->add_filter(new course_condition('c_course', 'c.id'));
        $collection->add_filter(new current_course_condition('current_course', 'c.id'));
        $collection->add_filter(new current_category_condition('current_category', 'c.category'));

        return $collection;
    }

    /**
     * Sets the default preferences for role assignments data source.
     *
     * This function initializes the default preferences for the role assignments
     * data source by setting the visibility of various fields.
     *
     * @param array $data The data array containing configuration preferences
     */
    public function set_default_preferences(&$data) {
        $preferences = $data['config_preferences'];
        $preferences['available_fields'] = [
            'r_rolename' => ['visible' => true],
            'r_shortname' => ['visible' => true],
            'ctx_contextname' => ['visible' => true],
            'ctx_contexturl' => ['visible' => true],
            'ctx_contextlevel' => ['visible' => true],
            'ctx_context_name' => ['visible' => true],
        ];
        $data['config_preferences'] = $preferences;
    }
}
