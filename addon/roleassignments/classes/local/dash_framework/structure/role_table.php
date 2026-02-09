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

namespace dashaddon_roleassignments\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_name_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_originalname_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_description_attribute;
use lang_string;

/**
 * Class role_table
 *
 * It extends the base table class to provide specific functionality for handling role assignments.
 */
class role_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('role', 'r');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_r', 'block_dash');
    }

    /**
     * Retrieves the fields for the role table.
     *
     * @return array An array of field objects, each representing a column in the role table
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('role'), $this, 'r.id', [
                new identifier_attribute(),
            ]),
            new field('ra_id', new lang_string('roleassignment', 'core_role'), $this, 'ra.id', [
                new identifier_attribute(),
            ]),
            new field(
                'rolename',
                new lang_string('rolename', 'block_dash'),
                $this,
                'r.id',
                [new role_name_attribute()]
            ),
            new field('roleoriginalname', new lang_string('originalrolename', 'block_dash'), $this, 'r.id', [
                new role_originalname_attribute(),
            ]),
            new field('shortname', new lang_string('shortname'), $this, 'r.shortname'),
            new field('description', new lang_string('description'), $this, 'r.id', [new role_description_attribute()]),
        ];
    }
}
