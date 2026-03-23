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
 * @package    dashaddon_badges
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_badges\local\block_dash;

use block_dash\local\data_source\abstract_data_source;
use local_dash\data_grid\filter\category_field_filter;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use dashaddon_badges\local\dash_framework\structure\badge_table;
use dashaddon_badges\local\block_dash\data_grid\filter\badge_origin_filter;
use block_dash\local\dash_framework\query_builder\join;
use mod_forum\local\exporters\group;

/**
 * Badges data source template queries and filter conditions defined.
 */
class badge_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {
        $this->add_table(new badge_table());
        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the badges data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER;
        $builder = new builder();

        $builder->select('bd.id', 'bd_id')
            ->select('bd.courseid', 'courseid')
            ->select('bd.type', 'type')
            ->from('badge', 'bd')
            ->join('user', 'u', 'id', ':userid1', join::TYPE_LEFT_JOIN, ["userid1" => $USER->id]);
        // Any of the conditions or filters enabled then add relevent table queries.
        if ($conditions = $this->get_preferences('filters')) {
            $courseconditions = ['c_course', 'current_course', 'c_course_categories_condition', 'my_enrolled_courses'];
            foreach ($conditions as $key => $condition) {
                if (in_array($key, $courseconditions) && $condition['enabled']) {
                    $builder->join('course', 'c', 'id', 'bd.courseid');
                    $builder->join('context', 'ctx', 'instanceid', 'c.id', join::TYPE_INNER_JOIN, ['courselevel' => 50]);
                    $builder->join_condition('ctx', 'ctx.contextlevel=:courselevel');
                    break;
                }
            }
        }
        return $builder;
    }

    /**
     * Filter conditions are added to badges preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new badge_origin_filter('c_id', 'bd.courseid', get_string('origin', 'dashaddon_badges')));

        $filtercollection->add_filter(new current_course_condition('current_course', 'c.id'));

        $filtercollection->add_filter(new course_condition('c_course', 'c.id'));

        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $filtercollection->add_filter(new my_enrolled_courses_condition('my_enrolled_courses', 'c.id'));

        return $filtercollection;
    }

    /**
     * Set the default preferences of the Badge datasource, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['available_fields']['bd_name']['visible'] = true;
        $configpreferences['available_fields']['bd_image']['visible'] = true;
        $configpreferences['available_fields']['bd_origin']['visible'] = true;
        $configpreferences['available_fields']['bd_dateissued']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }
}
