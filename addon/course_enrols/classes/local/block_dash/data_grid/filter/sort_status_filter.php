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
 * Filters results in order of different methods.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter_collection_interface;

/**
 * Filters results in order of different methods.
 */
class sort_status_filter extends select_filter {
    use filter_element;

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     */
    public function init() {
        global $DB;

        $sortmenus = \dashaddon_course_enrols\info::get_sorting_menus();
        foreach ($sortmenus as $key => $option) {
            $this->options[$key] = $option;
        }
        parent::init();
    }

    /**
     * Return a list of operations this filter can handle.
     *
     * @return array
     */
    public function get_supported_operations() {
        return [
            self::OPERATION_EQUAL,
        ];
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
        $filter = $filtercollection->get_filter('c_sort')->get_preferences();
        if (!empty($filter) && $filter['enabled']) {
            return $this->create_filter_element($filtercollection, $elementnameprefix, false);
        }
    }
}
