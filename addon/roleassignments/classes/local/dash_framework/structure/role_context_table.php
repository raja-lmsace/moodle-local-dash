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
use local_dash\data_grid\field\attribute\context\context_level_attribute;
use local_dash\data_grid\field\attribute\context\context_name_attribute;
use local_dash\data_grid\field\attribute\context\context_url_attribute;
use local_dash\data_grid\field\attribute\context\context_parent_attribute;

/**
 * Class role_context_table
 *
 * This class represents a table structure for role context assignments within the Moodle dashboard framework.
 * It extends the base table class to provide specific functionality for handling role context data.
 */
class role_context_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('context', 'ctx');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ctx', 'block_dash');
    }

    /**
     * Retrieves an array of field objects for the role context table.
     *
     * @return array An array of field objects, each representing a specific attribute of the role context
     */
    public function get_fields(): array {
        return [
            new field('contextname', new lang_string('contextname', 'block_dash'), $this, 'ctx.id', [new context_name_attribute()]),
            new field('contexturl', new lang_string('contexturl', 'block_dash'), $this, 'ctx.id', [new context_url_attribute()]),
            new field(
                'contextlevel',
                new lang_string('contextlevel', 'block_dash'),
                $this,
                'ctx.id',
                [new context_level_attribute()]
            ),
            new field('parent', new lang_string('parent', 'block_dash'), $this, 'ctx.id', [new context_parent_attribute()]),
        ];
    }
}
