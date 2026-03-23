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
 * Module type based filter option.
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter;

/**
 * Modulename based filter option.
 */
class activity_type_field_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        $this->add_option(MOD_ARCHETYPE_RESOURCE, get_string('resource'));
        $this->add_option(MOD_ARCHETYPE_OTHER, get_string('activity'));

        parent::init();
    }

    /**
     * Get the enrolment status filter label.
     * @return string
     */
    public function get_label() {
        return get_string('activitytype', 'dashaddon_activities');
    }


    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function get_sql_and_params() {
        global $DB;
        $values = $this->get_values();
        if (count($values) == 1 && $values[0] != self::ALL_OPTION) {
            [$sql, $params] = parent::get_sql_and_params();
            $inparams = [];
            [$activities, $resources] = dashaddon_activities_get_resources_activities();
            if ($values[0] == MOD_ARCHETYPE_RESOURCE) {
                $list = $resources;
            } else {
                $list = $activities;
            }
            [$insql, $inparams] = $DB->get_in_or_equal($list, SQL_PARAMS_NAMED);
            $sql = ' m.name ' . $insql;
            return [$sql, $params + $inparams];
        }
        return false;
    }
}
