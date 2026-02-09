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
use lang_string;
use block_dash\local\data_grid\field\attribute\date_attribute;

/**
 * Class role_assignments_table
 *
 * This class represents a table structure for role assignments within the Moodle dashboard framework.
 * It extends the base table class to provide specific functionality for handling role assignments.
 */
class role_assignments_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('role_assignments', 'ra');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ra', 'block_dash');
    }

    /**
     * Retrieves the fields for the role assignments table.
     *
     * @return array An array containing the fields for the role assignments table.
     */
    public function get_fields(): array {
        return [
            new field('timemodified', new lang_string('timemodified', 'block_dash'), $this, 'ra.timemodified', [
                new date_attribute(),
            ]),
        ];
    }
}
