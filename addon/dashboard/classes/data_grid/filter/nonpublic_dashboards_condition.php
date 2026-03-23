<?php
// This file is part of The Bootstrap Moodle theme
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
 * Limit dashboards to non-public (logged in dashboards only).
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use dashaddon_dashboard\model\dashboard;

/**
 * Limit dashboards to non-public (logged in dashboards only).
 *
 * @package dashaddon_dashboard
 */
class nonpublic_dashboards_condition extends condition {
    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        $values = [];
        /** @var dashboard $dashboard */
        foreach (dashboard::get_records() as $dashboard) {
            if ($dashboard->get('permission') != dashboard::PERMISSION_PUBLIC) {
                $values[] = $dashboard->get('id');
            }
        }

        return $values;
    }

    /**
     * Get the nonpublic dashboard condition label.
     * @return string
     */
    public function get_label() {
        return get_string('nonpublicdashboards', 'block_dash');
    }
}
