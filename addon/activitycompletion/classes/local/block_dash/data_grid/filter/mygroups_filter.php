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
 * Filters results to specific user groups activity completion.
 *
 * @package    dashaddon_activitycompletion
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activitycompletion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter_collection_interface;

/**
 * Filters results to specific group status.
 */
class mygroups_filter extends select_filter {
    use filter_element;

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {

        $choices = [
            'any' => get_string('anygroup', 'block_dash'),
        ];

        $this->add_all_option();

        foreach ($choices as $key => $option) {
            $this->options[$key] = $option;
        }

        parent::init();
    }

    /**
     * Add the "All" option to the filter.
     */
    public function add_all_option() {
        $this->add_option(self::ALL_OPTION, get_string('allstudents', 'block_dash'));
    }

    /**
     * Return a list of groups this filter can handle for the courses.
     *
     * @param array $courses Array of course IDs or course objects.
     * @return array
     */
    public static function generate_dynamic_options_list($courses) {

        $choices = [];
        $mygroups = [];

        // Get current user accessed groups.
        if (!empty($courses)) {
            foreach ($courses as $courseid) {
                $mygroups += groups_get_course_data($courseid)->groups;
            }
        } else {
            $mygroups = groups_get_my_groups();
        }

        foreach ($mygroups as $mygroup) {
            $group = groups_get_group($mygroup->id);
            if ($group->name == null) {
                $groupname = get_string('group') . " " . $mygroup->id;
            } else {
                $groupname = $group->name;
            }
            $choices[$mygroup->id] = $groupname;
        }

        return $choices;
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        [$sql, $params] = parent::get_sql_and_params();
        if ($sql) {
            $value = $this->get_values();
            if ($value[0] == 'any') {
                $sql = 'EXISTS (SELECT * FROM {groups_members} gm300 WHERE gm300.userid = u.id)';
            } else {
                $sql = 'EXISTS (SELECT * FROM {groups_members} gm300 WHERE gm300.userid = u.id AND ' . $sql . ')';
            }
        }

        return [$sql, $params];
    }

    /**
     * Override this method and call it after creating a form element.
     *
     * @param filter_collection_interface $filtercollection
     * @param string $elementnameprefix
     * @throws \Exception
     * @return string
     */
    public function create_form_element(
        filter_collection_interface $filtercollection,
        $elementnameprefix = ''
    ) {
        $filter = $filtercollection->get_filter('my_groups')->get_preferences();
        $mygroups = groups_get_my_groups();
        if (!empty($filter) && $filter['enabled'] && (!empty($mygroups)) && (count($mygroups) > 1)) {
            return $this->create_filter_element($filtercollection, $elementnameprefix);
        }
    }
}
