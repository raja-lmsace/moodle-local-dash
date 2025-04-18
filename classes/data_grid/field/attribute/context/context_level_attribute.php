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
 * Transform the data into activity progress level.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute\context;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Transforms data to plugin name of course format.
 *
 * @package local_dash
 */
class context_level_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        $context = \context::instance_by_id($data);
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            return get_string('systemcontext', 'block_dash');
        } else if ($context->contextlevel == CONTEXT_USER) {
            return get_string('usercontext', 'block_dash');
        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            return get_string('coursecatcontext', 'block_dash');
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            return get_string('coursecontext', 'block_dash');
        } else if ($context->contextlevel == CONTEXT_MODULE) {
            return get_string('modulecontext', 'block_dash');
        } else if ($context->contextlevel == CONTEXT_BLOCK) {
            return get_string('blockcontext', 'block_dash');
        }
    }
}
