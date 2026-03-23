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
 * Course enrolment manager.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/enrol/locallib.php');

/**
 * This class provides a targeted tied together means of interfacing the enrolment tasks together with a course.
 */
class dash_course_enrolments extends course_enrolment_manager {
    /**
     * Edits an enrolment
     *
     * @param stdClass $userenrolment
     * @param stdClass $data
     * @return bool
     */
    public function edit_enrolment($userenrolment, $data) {
        // Only allow editing if the user has the appropriate capability.
        // Already checked in /user/index.php but checking again in case this function is called from elsewhere.
        [$instance, $plugin] = $this->get_user_enrolment_components($userenrolment);
        if ($instance && $plugin && $plugin->allow_manage($instance)) {
            if (!isset($data->status)) {
                $data->status = $userenrolment->status;
            }
            $plugin->update_user_enrol(
                $instance,
                $userenrolment->userid,
                $data->status,
                $data->timestart,
                $data->timeend
            );
            return true;
        }
        return false;
    }

    /**
     * Unenrols a user from the course given the users ue entry
     *
     * @param stdClass $ue
     * @return bool
     */
    public function unenrol_user($ue) {
        global $DB;
         [$instance, $plugin] = $this->get_user_enrolment_components($ue);
        if ($instance && $plugin && $plugin->allow_unenrol_user($instance, $ue)) {
            $plugin->unenrol_user($instance, $ue->userid);
            return true;
        }
        return false;
    }
}
