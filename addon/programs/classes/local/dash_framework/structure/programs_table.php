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
 * Programs - dashaddon programs table.
 *
 * @package    dashaddon_programs
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\dash_framework\structure;

use moodle_url;
use lang_string;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\smart_program_button_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_cohort_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_content_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_context_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_date_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_description_attribute;
use dashaddon_programs\local\block_dash\data_grid\field\attribute\program_image_url_attribute;
use local_dash\data_grid\field\attribute\tags_attribute;

/**
 * Enrol programs table structure definitions for programs datasource.
 */
class programs_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('enrol_programs_programs', 'epp');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_epp', 'dashaddon_programs');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        global $DB, $USER;

        // Concat the type of programs.
        $sqlgroupconcat = $DB->sql_group_concat("eps.type", ",", "eps.type");

        $fields = [
            new field('id', new \lang_string('programs', 'dashaddon_programs'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('fullname', new lang_string('fullname'), $this),
            new field('fullname_link', new lang_string('fullnamelinked', 'block_dash'), $this, 'epp.fullname', [
                new linked_data_attribute(['url' => new moodle_url('/enrol/programs/catalogue/program.php', ['id' => 'epp_id'])]),
            ]),

            new field('idnumber', new lang_string('idnumber'), $this),

            new field('image', new lang_string('programimage', 'enrol_programs'), $this, 'epp.presentationjson, epp.contextid', [
                new program_image_url_attribute(), new image_attribute(),
            ]),

            new field('image_link', new lang_string('programimagelink', 'block_dash'), $this, 'epp.id', [
                new program_image_url_attribute(), new image_attribute(),
                new linked_data_attribute(['url' => new moodle_url('/enrol/programs/catalogue/program.php', ['id' => 'epp_id'])]),
            ]),

            new field('contextid', new lang_string('contextid', 'block_dash'), $this, null, [
                new program_context_attribute(),
            ]),

            new field('context_linked', new lang_string('contextlinked', 'block_dash'), $this, 'epp.contextid', [
                new program_context_attribute(),
                new linked_data_attribute(['url' => new moodle_url('/enrol/programs/my/index.php', ['contextid' => 'epp_ctx'])]),
            ]),

            new field('tags', new lang_string('tags', 'core'), $this, 'epp.id', [
                new tags_attribute(['component' => 'enrol_programs', 'itemtype' => 'program']),
            ]),

            new field(
                'description',
                new lang_string('description', 'block_dash'),
                $this,
                'epp.descriptionformat, epp.description',
                [
                    new program_description_attribute(),
                ]
            ),

            new field('archived', new lang_string('archived', 'enrol_programs'), $this, 'epp.archived', [
                new bool_attribute(),
            ]),

            new field('public', new lang_string('public', 'enrol_programs'), $this, null, [
                new bool_attribute(),
            ]),

            new field('creategroups', new lang_string('creategroups', 'enrol_programs'), $this, null, [
                new bool_attribute(),
            ]),

            new field('content', new lang_string('tabcontent', 'enrol_programs'), $this, 'epp.id', [
                new program_content_attribute(),
            ]),

            new field('timeallocationstart', new lang_string('allocationstart', 'enrol_programs'), $this, null, [
                new date_attribute(),
            ]),

            new field('timeallocationend', new lang_string('allocationend', 'enrol_programs'), $this, null, [
                new date_attribute(),
            ]),

            new field('startdatejson', new lang_string('programstart', 'enrol_programs'), $this, null, [
                new program_date_attribute(),
            ]),

            new field('duedatejson', new lang_string('programdue', 'enrol_programs'), $this, null, [
                new program_date_attribute(),
            ]),

            new field('enddatejson', new lang_string('programend', 'enrol_programs'), $this, null, [
                new program_date_attribute(),
            ]),

            new field('cohorts', new lang_string('cohorts', 'enrol_programs'), $this, 'epp.id', [
                new program_cohort_attribute(),
            ]),

            new field('allocations', new lang_string('allocation', 'enrol_programs'), $this, [
                'select' => "(SELECT $sqlgroupconcat AS allocations FROM {enrol_programs_sources} eps WHERE eps.programid=epp.id)",
            ]),

            new field('button', new lang_string('programs:viewcatalogue', 'enrol_programs'), $this, 'epp.id', [
                new moodle_url_attribute(['url' => new moodle_url('/enrol/programs/my/program.php', ['id' => 'epp_id'])]),
                new button_attribute(['label' => new lang_string('programs:view', 'block_dash')]),
            ]),

            new field('smart_program_button', new lang_string('smart_coursebutton', 'block_dash'), $this, [
                'select' => '(SELECT id FROM {enrol_programs_allocations}
                    WHERE programid = epp.id AND userid = ' . $USER->id . ')',
            ], [
                new smart_program_button_attribute(),
            ]),

        ];
        return $fields;
    }
}
