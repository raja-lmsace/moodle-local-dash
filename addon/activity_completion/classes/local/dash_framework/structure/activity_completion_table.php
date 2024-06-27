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
 * Activities table.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\completion_overrideby_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\completion_overridedate_attribute;

use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_currentgrade_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_grademax_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_gradepass_attribute;
use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_url_attribute;
use dashaddon_activity_completion\local\data_grid\field\attribute;


defined('MOODLE_INTERNAL') || die();


use lang_string;

/**
 * Activities table structure definitions for activities datasource.
 */
class activity_completion_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('dashaddon_activity_completion', 'cmc');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_cmc', 'dashaddon_activity_completion');
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
            new field('completionoverrideby', new lang_string('completionoverride', 'dashaddon_activity_completion'), $this, 'cmc.overrideby',
                [new completion_overrideby_attribute()]),
            new field('completionoverridedate', new lang_string('completionoverridedate', 'dashaddon_activity_completion'), $this, 'cmc.timemodified', [
                new completion_overridedate_attribute(),
            ]),
            new field('grademax', new lang_string('grademax', 'dashaddon_activity_completion'), $this, 'gg.rawgrademax', [new activity_currentgrade_attribute()]),
            new field('gradepass', new lang_string('gradepass', 'dashaddon_activity_completion'), $this, 'gt.gradepass', [new activity_gradepass_attribute()]),
            new field('currentgrade', new lang_string('currentgrade', 'dashaddon_activity_completion'), $this, 'gg.finalgrade', [new activity_grademax_attribute()]),
            new field('button', new lang_string('activitybutton', 'dashaddon_activity_completion'), $this, 'cmc.id', [
                new activity_url_attribute(['mod' => 'cm_modulename', 'cmid' => 'cm_id']),
                new button_attribute(['label' => new lang_string('viewactivity', 'dashaddon_activity_completion'), 'aria-label' => 'cm_name']),
            ]),
        ];
        return $fields;
    }
}
