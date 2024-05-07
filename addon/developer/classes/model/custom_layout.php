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
 * Represents a user created layout.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\model;

use block_dash\local\data_grid\field\field_definition_factory;
use core\persistent;
use dashaddon_developer\data_grid\field\custom_sql_field_definition;

/**
 * Represents a user created layout.
 *
 * @package dashaddon_developer
 */
class custom_layout extends persistent {

    /**
     * Table to store the layout data.
     */
    const TABLE = 'dashaddon_developer_layout';

    /**
     * Define the fields used in the persistent form of the custom layout.
     *
     * @return array
     */
    protected static function define_properties() {
        global $CFG;

        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'mustache_template' => [
                'type' => PARAM_RAW,
                'null' => NULL_NOT_ALLOWED,
                'default' =>
                    file_get_contents("$CFG->dirroot/local/dash/addon/developer/templates/layout_example.mustache"),
            ],
            'supports_field_visibility' => [
                'type' => PARAM_BOOL,
            ],
            'supports_filtering' => [
                'type' => PARAM_BOOL,
            ],
            'supports_pagination' => [
                'type' => PARAM_BOOL,
            ],
            'supports_sorting' => [
                'type' => PARAM_BOOL,
            ],
        ];
    }
}
