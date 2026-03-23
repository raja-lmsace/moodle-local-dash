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
 * Unit test cases to test the accordion layout.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols;
use context_course;
use stdClass;
use dashaddon_course_enrols\info;

/**
 * Unit test for course_enrols
 */
final class course_enrols_test extends \advanced_testcase {
    /**
     * Student role.
     *
     * @var stdClass
     */
    public $studentrole;

    /**
     * Course 1 context.
     *
     * @var stdClass
     */
    public $coursecontext1;

    /**
     * Test course 1.
     *
     * @var stdClass
     */
    public $course1;

    /**
     * Test course 2 context.
     *
     * @var stdClass
     */
    public $coursecontext2;

    /**
     * Test user1.
     *
     * @var stdClass
     */
    public $user;

    /**
     * Test course 2.
     *
     * @var stdClass
     */
    public $course2;

    /**
     * Course context of course 3.
     *
     * @var stdClass
     */
    public $coursecontext3;

    /**
     * Test course 3.
     *
     * @var stdClass
     */
    public $course3;

    /**
     * Manager user.
     *
     * @var stdClass
     */
    public $dashenrolmanager;

    /**
     * Set the admin user as User.
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        global $DB, $CFG, $PAGE;
        require_once($CFG->dirroot . '/local/dash/addon/course_enrols/locallib.php');
        require_once($CFG->dirroot . '/local/dash/addon/course_enrols/lib.php');
        $this->studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->course1 = $this->getDataGenerator()->create_course([
            'name' => 'Course 1',
        ]);
        $this->coursecontext1 = \context_course::instance($this->course1->id);
        $this->getDataGenerator()->enrol_user(
            $this->user->id,
            $this->course1->id,
            'student',
            'manual',
            time(),
            strtotime("+10 days"),
            ENROL_USER_ACTIVE
        );
        $this->course2 = $this->getDataGenerator()->create_course([
            'name' => 'Course 2',
        ]);
        $this->coursecontext2 = \context_course::instance($this->course2->id);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course2->id, 'student');

        $this->course3 = $this->getDataGenerator()->create_course([
            'name' => 'Course 3',
        ]);
        $this->coursecontext3 = \context_course::instance($this->course3->id);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course3->id, 'student');
        $this->dashenrolmanager = new \dash_course_enrolments($PAGE, $this->course1);
    }

    /**
     * Test dashaddon_enrolments_get_all_users_courses.
     * @covers ::dashaddon_enrolments_get_all_users_courses
     */
    public function test_dashaddon_enrolments_get_all_users_courses(): void {

        [$courses, $count] = dashaddon_enrolments_get_all_users_courses(
            $this->user->id,
            false
        );
        $this->assertTrue(isset($courses[$this->course1->id]));
        $this->assertTrue(isset($courses[$this->course2->id]));
        $this->assertTrue(isset($courses[$this->course3->id]));
        $this->assertEquals(3, $count);
    }

    /**
     * Test edit_enrolment.
     * @covers ::edit_enrolment
     */
    public function test_edit_enrolment(): void {
        global $DB;
        $enrol = $DB->get_record('enrol', ['courseid' => $this->course1->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $userenrolment = $DB->get_record(
            'user_enrolments',
            [
                'enrolid' => $enrol->id,
                'userid' => $this->user->id,
            ],
            '*',
            MUST_EXIST
        );
        $data = new stdClass();
        $timestart = strtotime("+2days");
        $timeend = strtotime("+20days");
        $data->status = ENROL_USER_SUSPENDED;
        $data->timestart = $timestart;
        $data->timeend = $timeend;
        $this->dashenrolmanager->edit_enrolment($userenrolment, $data);
        $result = $DB->get_record(
            'user_enrolments',
            [
                'enrolid' => $enrol->id,
                'userid' => $this->user->id,
            ],
            '*',
            MUST_EXIST
        );
        $this->assertEquals($result->timestart, $timestart);
        $this->assertEquals($result->timeend, $timeend);
        $this->assertEquals(ENROL_USER_SUSPENDED, $result->status);
    }

    /**
     * Test course_enrols_unenrol_user
     * @covers ::course_enrols_unenrol_user
     */
    public function test_course_enrols_unenrol_user(): void {
        global $DB, $PAGE;
        $enrol = $DB->get_record('enrol', ['courseid' => $this->course1->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $userenrolment = $DB->get_record(
            'user_enrolments',
            [
                'enrolid' => $enrol->id,
                'userid' => $this->user->id,
            ],
            '*',
            MUST_EXIST
        );
        $this->assertTrue(!empty($userenrolment));
        $this->dashenrolmanager->unenrol_user($userenrolment);
        $result = $DB->get_record('user_enrolments', ['enrolid' => $enrol->id, 'userid' => $this->user->id]);
        $this->assertTrue(empty($result));
    }

    /**
     * Test get_mentess_user
     * @covers ::get_mentess_user
     */
    public function test_get_mentess_user(): void {
        $child = $this->getDataGenerator()->create_user();
        $this->setUser($child);
        $parent1 = $this->getDataGenerator()->create_user();
        $parent2 = $this->getDataGenerator()->create_user();

        $systemcontext = \context_system::instance();
        $parentrole = $this->getDataGenerator()->create_role();
        assign_capability('tool/dataprivacy:makedatarequestsforchildren', CAP_ALLOW, $parentrole, $systemcontext);
        $idblock = $this->getDataGenerator()->role_assign($parentrole, $child->id, \context_user::instance($parent1->id));
        $this->getDataGenerator()->role_assign($parentrole, $child->id, \context_user::instance($parent2->id));
        $users = info::get_mentess_user();
        $result = array_column($users, 'id');
        $this->assertTrue(in_array($parent1->id, $result));
        $this->assertTrue(in_array($parent2->id, $result));
    }

    /**
     * Test get_course_criteria
     * @covers ::get_course_criteria
     */
    public function test_get_course_criteria(): void {
        global $CFG;
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_course.php');
        $course = $this->getDataGenerator()->create_course();
        $cancompcourse1 = $this->getDataGenerator()->create_course();
        $cancompcourse2 = $this->getDataGenerator()->create_course();

        $criteriadata = (object) [
            'id' => $course->id,
            'criteria_course' => [$cancompcourse1->id, $cancompcourse2->id],
        ];
        $criterion = new \completion_criteria_course();
        $criterion->update_config($criteriadata);
        $courseinfo = new info($course, $this->user->id);
        $results = $courseinfo->get_course_criteria();
        $results = array_column($results, 'id');
        $this->assertTrue(in_array($cancompcourse1->id, $results));
        $this->assertTrue(in_array($cancompcourse2->id, $results));
    }

    /**
     * Test course_enrols_get_sections
     * @covers ::course_enrols_get_sections
     */
    public function test_course_enrols_get_sections(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1], ['createsections' => true]);
        $user = $this->getDataGenerator()->create_user();
        $assign1 = $this->getDataGenerator()->create_module(
            'assign',
            [
                'course' => $course->id,
                'section' => 1,
                'name' => 'Test 1',
            ],
            ['completion' => 1]
        );
        $assign2 = $this->getDataGenerator()->create_module(
            'assign',
            [
                'course' => $course->id,
                'section' => 1,
                'name' => 'Test 2',
            ],
            ['completion' => 1]
        );
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $this->studentrole->id);
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        $criteriadata = (object) [
            'id' => $course->id,
            'criteria_activity' => [$assign2->cmid => 1],
        ];
        $criterion = new \completion_criteria_activity();
        $criterion->update_config($criteriadata);

        $cmassign2 = get_coursemodule_from_id('assign', $assign2->cmid);
        $completion = new \completion_info($course);
        $completion->update_state($cmassign2, COMPLETION_COMPLETE, $user->id);

        $courseinfo = new info($course, $user->id);
        $result = $courseinfo->get_section_completion($section->id);
        $this->assertFalse($result);

        // Set completion criteria and mark the user to complete the criteria.
        $criteriadata = (object) [
            'id' => $course->id,
            'criteria_activity' => [$assign1->cmid => 1],
        ];
        $criterion = new \completion_criteria_activity();
        $criterion->update_config($criteriadata);

        $cmassign1 = get_coursemodule_from_id('assign', $assign1->cmid);
        $completion = new \completion_info($course);
        $completion->update_state($cmassign1, COMPLETION_COMPLETE, $user->id);

        $courseinfo = new info($course, $user->id);
        $result = $courseinfo->get_section_completion($section->id);
        $this->assertTrue($result);
    }
}
