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
 * Activity action table.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\data_grid\field\attribute\button_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_url_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_completion_toggle_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_grade_attribute;
use lang_string;


/**
 * Activities action table structure definitions for activity completion datasource.
 */
class activity_action_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('dashaddon_activity_action', 'cma');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_cma', 'dashaddon_activity_completion');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        global $PAGE;
        $fields = [

            new field('button', new lang_string('activitybutton', 'block_dash'), $this, 'cmc.id', [
                new activity_url_attribute(['mod' => 'cm_modulename', 'cmid' => 'cm_id']),
                new button_attribute(['label' => new lang_string('viewactivity', 'block_dash'), 'aria-label' => 'cm_name']),
            ], ['supports_sorting' => false]),

            new field('toggle', new lang_string('activityoverride', 'dashaddon_activity_completion'), $this, 'cmc.id', [
                new activity_completion_toggle_attribute(),
            ], ['supports_sorting' => false]),

            new field('grade', new lang_string('activitygrade', 'dashaddon_activity_completion'), $this, 'cm.id', [
                new activity_grade_attribute(),
            ], ['supports_sorting' => false]),

        ];
        return $fields;
    }
}
