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
 * External methods helps to manage user course enrolment
 *
 * @package     dashaddon_course_enrols
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/dash/addon/course_enrols/locallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

/**
 * External methods helps to manage user course enrolment.
 */
class external extends external_api {
    /**
     * Returns description of submit_user_enrolment_form parameters.
     *
     * @return external_function_parameters.
     */
    public static function submit_user_enrolment_form_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'The data from the event form'),
        ]);
    }

    /**
     * External function that handles the user enrolment form submission.
     *
     * @param string $formdata The user enrolment form data in s URI encoded param string
     * @return array An array consisting of the processing result and error flag, if available
     */
    public static function submit_user_enrolment_form($formdata) {
        global $CFG, $DB, $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::submit_user_enrolment_form_parameters(), ['formdata' => $formdata]);

        $data = [];
        parse_str($params['formdata'], $data);

        $userenrolment = $DB->get_record('user_enrolments', ['id' => $data['ue']], '*', MUST_EXIST);
        $instance = $DB->get_record('enrol', ['id' => $userenrolment->enrolid], '*', MUST_EXIST);
        $plugin = enrol_get_plugin($instance->enrol);
        $course = get_course($instance->courseid);
        $context = \context_course::instance($course->id);
        $PAGE->set_context($context);

        require_once("$CFG->dirroot/enrol/editenrolment_form.php");
        $customformdata = [
            'ue' => $userenrolment,
            'modal' => true,
            'enrolinstancename' => $plugin->get_instance_name($instance),
        ];
        $mform = new \enrol_user_enrolment_form(null, $customformdata, 'post', '', null, true, $data);

        if ($validateddata = $mform->get_data()) {
            if (!empty($validateddata->duration) && $validateddata->timeend == 0) {
                $validateddata->timeend = $validateddata->timestart + $validateddata->duration;
            }
            require_once($CFG->dirroot . '/enrol/locallib.php');
            $manager = new \dash_course_enrolments($PAGE, $course);
            $result = $manager->edit_enrolment($userenrolment, $validateddata);

            return ['result' => $result];
        } else {
            return ['result' => false, 'validationerror' => true];
        }
    }

    /**
     * Returns description of submit_user_enrolment_form() result value
     *
     * @return external_description
     */
    public static function submit_user_enrolment_form_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'True if the user\'s enrolment was successfully updated'),
            'validationerror' => new external_value(PARAM_BOOL, 'Indicates invalid form data', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Returns description of unenrol_user_enrolment() parameters
     *
     * @return external_function_parameters
     */
    public static function unenrol_user_enrolment_parameters() {
        return new external_function_parameters(
            [
                'ueid' => new external_value(PARAM_INT, 'User enrolment ID'),
            ]
        );
    }

    /**
     * External function that unenrols a given user enrolment.
     *
     * @param int $ueid The user enrolment ID.
     * @return array An array consisting of the processing result, errors.
     */
    public static function unenrol_user_enrolment($ueid) {
        global $CFG, $DB, $PAGE;

        $params = self::validate_parameters(self::unenrol_user_enrolment_parameters(), [
            'ueid' => $ueid,
        ]);

        $result = false;
        $errors = [];

        $userenrolment = $DB->get_record('user_enrolments', ['id' => $params['ueid']], '*');
        if ($userenrolment) {
            $userid = $userenrolment->userid;
            $enrolid = $userenrolment->enrolid;
            $enrol = $DB->get_record('enrol', ['id' => $enrolid], '*', MUST_EXIST);
            $courseid = $enrol->courseid;
            $course = get_course($courseid);
            $context = \context_course::instance($course->id);
        } else {
            $validationerrors['invalidrequest'] = get_string('invalidrequest', 'enrol');
        }

        // If the userenrolment exists, unenrol the user.
        if (!isset($validationerrors)) {
            require_once($CFG->dirroot . '/enrol/locallib.php');
            $manager = new \dash_course_enrolments($PAGE, $course);
            $result = $manager->unenrol_user($userenrolment);
        } else {
            foreach ($validationerrors as $key => $errormessage) {
                $errors[] = (object)[
                    'key' => $key,
                    'message' => $errormessage,
                ];
            }
        }

        return [
            'result' => $result,
            'errors' => $errors,
        ];
    }

    /**
     * Returns description of unenrol_user_enrolment() result value
     *
     * @return external_description
     */
    public static function unenrol_user_enrolment_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'True if the user\'s enrolment was successfully updated'),
                'errors' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'key' => new external_value(PARAM_TEXT, 'The data that failed the validation'),
                            'message' => new external_value(PARAM_TEXT, 'The error message'),
                        ]
                    ),
                    'List of validation errors'
                ),
            ]
        );
    }


    /**
     * Returns description of submit_user_enrolment_form parameters.
     *
     * @return external_function_parameters.
     */
    public static function enrol_courses_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'The data from the event form'),
        ]);
    }

    /**
     * External function that handles the user enrolment form submission.
     *
     * @param string $formdata The user enrolment form data in s URI encoded param string
     * @return array An array consisting of the processing result and error flag, if available
     */
    public static function enrol_courses($formdata) {
        global $CFG, $DB, $PAGE, $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::submit_user_enrolment_form_parameters(), ['formdata' => $formdata]);

        $data = [];
        parse_str($params['formdata'], $data);

        if (empty($data['courses']) || empty($data['enroluserid'])) {
            return ['result' => false, 'validationerror' => true];
        }
        $courses = $data['courses'];
        $enrolplugin = enrol_get_plugin('manual');
        $roleid = get_config('local_dash', 'course_enrol_role');
        if (!$roleid) {
            $student = array_keys(get_archetype_roles('student'));
            $roleid = current($student);
        }

        foreach ($courses as $courseid) {
            $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
            $result = $enrolplugin->enrol_user($instance, $data['enroluserid'], $roleid);
        }
        return ['result' => true];
    }

    /**
     * Returns description of submit_user_enrolment_form() result value
     *
     * @return external_description
     */
    public static function enrol_courses_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'True if the user\'s enrolment was successfully updated'),
            'validationerror' => new external_value(PARAM_BOOL, 'Indicates invalid form data', VALUE_DEFAULT, false),
        ]);
    }
}
