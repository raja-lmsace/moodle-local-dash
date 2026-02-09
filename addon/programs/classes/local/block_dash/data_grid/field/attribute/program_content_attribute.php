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
 * Generate the content of the program.
 *
 * @package    dashaddon_programs
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use stdClass;
use moodle_url;

/**
 * Generate the content of the program.
 */
class program_content_attribute extends abstract_field_attribute {
    /**
     * Generate the content of the program.
     *
     * @param int $data
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {
        global $PAGE, $DB, $USER;

        $program = (array) clone $record;
        $updated = array_map(fn($field) => str_replace('epp_', '', $field), array_keys($program));
        $program = (object) array_combine(array_values($updated), array_values($program));

        $myouput = $PAGE->get_renderer('dashaddon_programs', 'catelogue');
        $allocation = $DB->get_record('enrol_programs_allocations', ['programid' => $program->id, 'userid' => $USER->id]);

        return \html_writer::tag('div', $myouput->get_program_content($program), ['class' => 'dashaddon-programs-content']);
    }
}
