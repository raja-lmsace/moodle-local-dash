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
 * Smart programs button.
 *
 * @package    dashaddon_programs
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Smart program button.
 *
 * @package dashaddon_programs
 */
class smart_program_button_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     *
     * @param \int $isassigned
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($isassigned, \stdClass $record) {
        global $DB, $USER, $PAGE;

        $program = (array) clone $record;
        $updated = array_map(fn($field) => str_replace('epp_', '', $field), array_keys($program));
        $program = (object) array_combine(
            array_values($updated),
            array_values($program)
        );

        $failurereason = null;

        if ($isassigned) {
            $url = new \moodle_url('/enrol/programs/my/program.php', ['id' => $program->id]);
            return \html_writer::link($url, get_string('viewprogram', 'block_dash'), ['class' => 'btn btn-primary']);
        }

        // Signup by self.
        $source = $DB->get_record('enrol_programs_sources', ['programid' => $program->id, 'type' => 'selfallocation']);
        if (
            $source
            && \enrol_programs\local\source\selfallocation::can_user_request($program, $source, (int)$USER->id, $failurereason)
        ) {
            $classname = '\enrol_programs\local\source\selfallocation';
            $data = json_decode($source->datajson);
            $string = (isset($data->key) && $data->key != '')
                ? get_string('selfallocationwithkey', 'block_dash')
                : get_string('source_selfallocation_allocate', 'enrol_programs');

            $url = new \moodle_url('/enrol/programs/catalogue/source_selfallocation.php', ['sourceid' => $source->id]);
            $button = new \local_openlms\output\dialog_form\button($url, $string);

            /** @var \local_openlms\output\dialog_form\renderer $dialogformoutput */
            $dialogformoutput = $PAGE->get_renderer('local_openlms', 'dialog_form');
            $button = $dialogformoutput->render($button);

            return $button;
        }

        // Request for the approval for the program.
        $source = $DB->get_record('enrol_programs_sources', ['programid' => $program->id, 'type' => 'approval']);
        if (
            $source
            && \enrol_programs\local\source\approval::can_user_request($program, $source, (int)$USER->id, $failurereason)
        ) {
            $classname = '\enrol_programs\local\source\approval';
            $actions = $classname::get_catalogue_actions($program, $source);
            return $actions[0] ?? '';
        } else {
            return \html_writer::span(get_string('notavailable', 'block_dash'));
        }

        return '';
    }
}
