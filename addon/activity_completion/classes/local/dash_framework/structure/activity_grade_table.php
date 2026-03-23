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
 * Activity grade table.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_currentgrade_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_grademax_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_gradepass_attribute;
use lang_string;

/**
 * Activities grade table structure definitions for activity completion datasource.
 */
class activity_grade_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('dashaddon_activity_completion_grade', 'cmg');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_cmg', 'dashaddon_activity_completion');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        global $DB;
        $fields = [
            new field(
                'grademax',
                new lang_string('grademax', 'dashaddon_activity_completion'),
                $this,
                'gt.grademax',
                [new activity_grademax_attribute()]
            ),

            new field(
                'gradepass',
                new lang_string('gradepass', 'dashaddon_activity_completion'),
                $this,
                'gt.gradepass',
                [new activity_gradepass_attribute()]
            ),

            new field(
                'currentgrade',
                new lang_string('currentgrade', 'dashaddon_activity_completion'),
                $this,
                'gg.finalgrade',
                [new activity_currentgrade_attribute()]
            ),

        ];
        return $fields;
    }
}
