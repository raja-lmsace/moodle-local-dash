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
 * Create custom sql field definitions.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use dashaddon_developer\data_grid\field\custom_field_definition_factory;

$definitions = [];

for ($i = 0; $i < 20; $i++) {
    $definitions[] = [
        'name' => 'custom_sql_' . ($i + 1),
        'title' => get_string('customsqlfield', 'block_dash') . ($i + 1),
        'factory' => custom_field_definition_factory::class,
    ];
}

return $definitions;
