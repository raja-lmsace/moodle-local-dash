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
 * Unit test for filtering.
 *
 * @package    dashaddon_course_completions
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_completions;

/**
 * Unit test for widgets.
 *
 * @group block_dash
 * @group bdecent
 * @group widgets_test
 */
final class completions_test extends \advanced_testcase {
    /**
     * Test course 1
     *
     * @var stdClass
     */
    public $course1;

    /**
     * Test course 2
     *
     * @var stdClass
     */
    public $course2;

    /**
     * Test course 3
     *
     * @var stdClass
     */
    public $course3;

    /**
     * Test user 1
     *
     * @var stdClass
     */
    public $user;

    /**
     * List of test users.
     *
     * @var array
     */
    public $users;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        global $USER;
        $this->user = $USER;
        $this->course1 = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->course2 = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->course3 = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        foreach (range(1, 5) as $user) {
            $this->users[$user] = self::getDataGenerator()->create_user();
        }
    }

    /**
     * Constructs a Page object for the User Dashboard.
     *
     * @param   \stdClass       $user User to create Dashboard for.
     * @return  \moodle_page
     */
    protected function construct_user_page(\stdClass $user) {
        $page = new \moodle_page();
        $page->set_context(\context_user::instance($user->id));
        $page->set_pagelayout('mydashboard');
        $page->set_pagetype('my-index');
        $page->blocks->load_blocks();
        return $page;
    }

    /**
     * Creates an HTML block on a user.
     *
     * @param   string  $title
     * @param   string  $widget
     * @return  \block_instance
     */
    protected function create_user_block($title, $widget) {
        global $USER;

        $configdata = (object) [
            'title' => $title,
            'data_source_idnumber' => $widget,
        ];

        $this->create_block($this->construct_user_page($USER));
        $block = $this->get_last_block_on_page($this->construct_user_page($USER));
        $block = block_instance('dash', $block->instance);
        $block->instance_config_save((object) $configdata);

        return $block;
    }

    /**
     * Get the last block on the page.
     *
     * @param \page $page Page
     * @return \block_html Block instance object
     */
    protected function get_last_block_on_page($page) {
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);

        return $block;
    }

    /**
     * Creates an HTML block on a page.
     *
     * @param \page $page Page
     * @return void
     */
    protected function create_block($page) {
        $page->blocks->add_block_at_end_of_default_region('dash');
    }

    /**
     * Test for dashaddon_course_completions\widget\completion_widget() to confirm the course completions loaded.
     *
     * @covers ::contacts_widget
     * @return void
     */
    public function test_coursecompletions(): void {
        global $CFG;
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $user = self::getDataGenerator()->create_and_enrol($this->course1, 'student');
        $user2 = self::getDataGenerator()->create_and_enrol($this->course1, 'student');

        $teacher = self::getDataGenerator()->create_and_enrol($this->course1, 'editingteacher');
        self::getDataGenerator()->enrol_user($user->id, $this->course1->id);
        self::getDataGenerator()->enrol_user($user->id, $this->course3->id);
        self::getDataGenerator()->enrol_user($user2->id, $this->course2->id);

        $this->setUser($user);

        $assign = $this->getDataGenerator()->create_module(
            'assign',
            ['course' => $this->course1->id],
            ['completion' => 1]
        );
        $data = $this->getDataGenerator()->create_module(
            'data',
            ['course' => $this->course1->id],
            ['completion' => 1]
        );
        $this->getDataGenerator()->create_module(
            'page',
            ['course' => $this->course1->id],
            ['completion' => 1]
        );
        $this->getDataGenerator()->create_module(
            'page',
            ['course' => $this->course1->id],
            ['completion' => 1]
        );

        // Mark two of them as completed for a user.
        $cmassign = get_coursemodule_from_id('assign', $assign->cmid);
        $cmdata = get_coursemodule_from_id('data', $data->cmid);
        // Handle overall aggregation.
        $aggdata = [
            'course'        => $this->course1->id,
            'criteriatype'  => COMPLETION_CRITERIA_TYPE_ACTIVITY,
        ];
        $criteriadata = new \stdClass();
        $criteriadata->id = $this->course1->id;
        $criteriadata->criteria_activity[$cmdata->id] = 1;
        $criteriadata->criteria_activity[$cmassign->id] = 1;

        $class = 'completion_criteria_activity';
        $criterion = new $class();
        $criterion->update_config($criteriadata);

        $aggregation = new \completion_aggregation($aggdata);
        $aggregation->setMethod(COMPLETION_AGGREGATION_ALL);
        $aggregation->save();

        $completion = new \completion_info($this->course1);
        $completion->update_state($cmassign, COMPLETION_COMPLETE, $user->id);
        $completion->update_state($cmdata, COMPLETION_COMPLETE, $user->id);

        $ccompletion = new \completion_completion(['course' => $this->course1->id,
                                                  'userid' => $user->id,
                                                  'timeenrolled' => time(),
                                                  'timestarted' => time(),
                                                ]);
        // Now, mark the course as completed.
        $ccompletion->mark_complete();

        $block = $this->create_user_block('Course completions', 'dashaddon_course_completions\widget\completion_widget');
        $context1 = \context_course::instance($this->course1->id);

        $widget = new \dashaddon_course_completions\widget\completion_widget($context1);
        $widget->set_block_instance($block);
        $data = $widget->build_widget();

        $endcourse = current($data['courses']);
        $report = $endcourse['report'];

        $this->assertEquals(3, count($data['courses']));
        $this->assertEquals(2, $report['enrolled']);
        $this->assertEquals(1, $report['notyetstarted']);
        $this->assertEquals(1, $report['completed']);
        $this->assertEquals(50, $report['completionpercentage']);
    }
}
