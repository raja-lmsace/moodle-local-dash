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
 * Common functions.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use dashaddon_developer\model\custom_data_source;
use dashaddon_developer\data_source\persistent_data_source_factory;
use dashaddon_developer\model\custom_layout;
use dashaddon_developer\layout\persistent_layout_factory;

define('DASHADDON_DEVELOPER_MAIN_ALIAS', 'mnt');

/**
 * Register the field definitions.
 *
 * @return void
 */
function dashaddon_developer_register_field_definitions() {
    global $CFG;

    return require("$CFG->dirroot/local/dash/addon/developer/field_definitions.php");
}

/**
 * Register the datasources avialable in the developer addon.
 *
 * @return array
 */
function dashaddon_developer_register_data_sources() {
    $registered = [];
    foreach (custom_data_source::get_records() as $customdatasource) {
        $registered[] = [
            'identifier' => $customdatasource->get('idnumber'),
            'name' => $customdatasource->get('name'),
            'factory' => persistent_data_source_factory::class,
        ];
    }

    return $registered;
}

/**
 * Register the layouts avialable in the developer addon.
 *
 * @return array
 */
function dashaddon_developer_register_layouts() {
    $layouts = [];

    foreach (custom_layout::get_records() as $customlayout) {
        $layouts[] = [
            'identifier' => 'custom_' . $customlayout->get('id'),
            'name' => $customlayout->get('name'),
            'factory' => persistent_layout_factory::class,
        ];
    }

    return $layouts;
}
