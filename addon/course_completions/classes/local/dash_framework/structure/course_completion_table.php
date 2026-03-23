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
 * Class course_completion_table.
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
use lang_string;
use local_dash\data_grid\field\attribute\activity_progress_attribute;
use local_dash\data_grid\field\attribute\activity_progress_bar_attribute;
use local_dash\data_grid\field\attribute\completion_status_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use block_dash\local\data_grid\field\attribute\course_information_url_attribute;

/**
 * Class course_completion_table.
 *
 * @package dashaddon_course_completions
 */
class course_completion_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('course_completions', 'ccp');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ccp', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('coursecompletion'), $this, null, [
                new identifier_attribute(),
            ]),

            new field(
                'total_activities',
                new lang_string('totalactivitiescompletion', 'block_dash'),
                $this,
                'ccc200.totalactivities',
                [],
                ['supports_sorting' => false],
                '',
                null,
                new join_raw('SELECT ccc.course, COUNT(*) AS totalactivities
                    FROM {course_completion_criteria} ccc
                    WHERE ccc.criteriatype = 4
                    GROUP BY ccc.course', 'ccc200', 'course', 'c.id', join_raw::TYPE_LEFT_JOIN),
                true, // Force join even if not visible.
            ),

            new field(
                'completed_activities',
                new lang_string('completedactivities', 'block_dash'),
                $this,
                '(SELECT COUNT(*) FROM {course_completion_crit_compl} cccc100
                join {course_completion_criteria} cccc200 ON cccc200.id = cccc100.criteriaid AND cccc200.criteriatype = 4
                WHERE cccc100.userid = u.id AND cccc100.course = c.id)',
                [],
                ['supports_sorting' => false]
            ),

            new field('completed', new lang_string('coursecompleted', 'completion'), $this, 'ccp.timecompleted', [
                new bool_attribute(),
            ]),
            new field('timecompleted', new lang_string('datecompleted', 'block_dash'), $this, null, [
                new date_attribute(),
            ]),
            new field('progress', new lang_string('activityprogress', 'block_dash'), $this, 'ccp.id', [
                new activity_progress_attribute(),
            ], ['supports_sorting' => false]),
            new field('progressbar', new lang_string('activityprogressbar', 'block_dash'), $this, 'ccp.id', [
                new activity_progress_attribute(),
                new activity_progress_bar_attribute(),
            ], ['supports_sorting' => false]),
            new field('courseinformation', new lang_string('courseinformation', 'block_dash'), $this, 'c.id', [
                new course_information_url_attribute(),
                new button_attribute(['label' => new lang_string('courseinformation', 'block_dash')]),
            ]),
        ];
    }
}
