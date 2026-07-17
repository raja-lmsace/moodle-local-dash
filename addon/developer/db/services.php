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
 * Services defined - Developer dashaddon
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'dashaddon_developer_get_database_schema_structure' => [
        'classname'     => 'dashaddon_developer\external',
        'methodname'    => 'get_database_schema_structure',
        'description'   => 'Get database schema structure info, tables and fields.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'dashaddon_developer_get_field_edit_row' => [
        'classname'     => 'dashaddon_developer\external',
        'methodname'    => 'get_field_edit_row',
        'description'   => 'Get HTML for new field edit row.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'dashaddon_developer_get_fields_list' => [
        'classname'     => 'dashaddon_developer\external',
        'methodname'    => 'get_fields_list',
        'description'   => 'Get HTML for new field edit row.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],

];
