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
 * Unit test for Smart course button.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash;

use dashaddon_courses\local\block_dash\courses_data_source;
use block_dash\local\layout\grid_layout;

/**
 * Unit test for widgets.
 *
 * @group block_dash
 * @group bdecent
 * @group widgets_test
 */
final class smart_button_test extends \advanced_testcase {
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
     * Smart button Test course 1
     *
     * @var stdClass
     */
    public $course1;

    /**
     * Smart button Test course 2
     *
     * @var stdClass
     */
    public $course2;

    /**
     * Smart button Test course 3
     *
     * @var stdClass
     */
    public $course3;

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
     * Create block instance instance and setup course datasource.
     *
     * @return stdclass
     */
    public function create_instance() {
        $datasource = new courses_data_source(\context_system::instance());
        $layout = new grid_layout($datasource);
        $datasource->set_layout($layout);
        $datasource->set_preferences([
            'available_fields' => ['c_shortname' => ['visible' => 1], 'c_smart_course_button' => ['visible' => 1]],
        ]);
        $data = $datasource->get_data();
        return $data;
    }

    /**
     * Test for local_dash\data_grid\field\attribute\smart_course_button_attribute()
     * to confirm the Smart button.
     *
     * @covers ::contacts_widget
     * @return void
     */
    public function test_smartbutton(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $user = self::getDataGenerator()->create_and_enrol($this->course1, 'student');
        $user2 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_and_enrol($this->course1, 'editingteacher');
        self::getDataGenerator()->enrol_user($user->id, $this->course1->id);
        self::getDataGenerator()->enrol_user($user->id, $this->course3->id);

        $selfplugin = enrol_get_plugin('self');
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $instanceid1 = $selfplugin->add_instance($this->course1, ['status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 1',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id,
                                                                ]);
        $this->setUser($user);

        $data = $this->create_instance();
        $courses = $data->get_child_collections('rows');
        $this->assertEquals(3, count($courses));
        $coursedata = $courses[0]->get_data();
        $this->assertArrayHasKey(3, $coursedata);
        $smartbutton = $coursedata[3];
        $this->assertEqualsIgnoringCase('c_smart_course_button', $smartbutton->get_name());
        $this->assertStringContainsStringIgnoringCase(get_string('viewcourse', 'block_dash'), $smartbutton->get_value());

        $this->setUser($user2);
        $data = $this->create_instance();
        $courses = $data->get_child_collections('rows');
        $coursedata = $courses[0]->get_data();
        $smartbutton = $coursedata[3]->get_value();
        $this->assertStringContainsStringIgnoringCase(get_string('enrolnow', 'block_dash'), $smartbutton);
    }
}
