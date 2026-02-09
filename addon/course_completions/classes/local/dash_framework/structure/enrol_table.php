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
 * Class enrol_table.
 *
 * @package    dashaddon_course_completions
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_completions\local\dash_framework\structure;

use block_dash\local\dash_framework\query_builder\join_raw;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use lang_string;
use local_dash\data_grid\field\attribute\activity_progress_attribute;
use local_dash\data_grid\field\attribute\course_format_attribute;
use local_dash\data_grid\field\attribute\course_image_url_attribute;
use local_dash\data_grid\field\attribute\course_summary_attribute;
use local_dash\data_grid\field\attribute\customfield_select_attribute;
use local_dash\data_grid\field\attribute\enrol_name_attribute;
use local_dash\data_grid\field\attribute\enrol_status_attribute;
use local_dash\data_grid\field\attribute\tags_attribute;
use moodle_url;

/**
 * Class enrol_table.
 *
 * @package dashaddon_course_completions
 */
class enrol_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('enrol', 'e');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_e', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {
        $fields = [
            new field('id', new lang_string('enrollmentmethod', 'block_dash'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('enrol', new lang_string('enrollmentmethod', 'block_dash'), $this, null, [
                new enrol_name_attribute(),
            ]),
            new field('status', new lang_string('enrollmentmethodstatus', 'block_dash'), $this, null, [
                new enrol_status_attribute(),
            ]),
            new field(
                'enrolled_users',
                new lang_string('enrolledusers', 'enrol'),
                $this,
                'ue100.enrolledusers',
                [],
                ['supports_sorting' => false],
                '',
                null,
                new join_raw('SELECT ue.enrolid, COUNT(ue.id) AS enrolledusers
                    FROM {user_enrolments} ue
                    GROUP BY ue.enrolid', 'ue100', 'enrolid', 'e.id', join_raw::TYPE_LEFT_JOIN)
            ),
        ];

        return $fields;
    }
}
