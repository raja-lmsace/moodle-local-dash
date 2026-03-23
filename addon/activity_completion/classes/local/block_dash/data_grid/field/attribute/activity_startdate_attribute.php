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
 * Transform activity data into activity startdate.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/dash/addon/activity_completion/lib.php");

/**
 * Transforms activity data to formatted activity startdate.
 *
 * @package dashaddon_activity_completion
 */
class activity_startdate_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        $cm = cm_info::create(get_coursemodule_from_id('', $record->cm_id));
        $userid = $record->u_id;
        $startdate = "";

        if (is_siteadmin($userid)) {
            return $startdate;
        }

        if ($data) {
            $startdate = userdate($data, get_string('strftimedatefullshort'));
        }
        if (dashaddon_activity_completion_is_timetable_installed()) {
            $startdate = $this->get_mod_user_startdate($cm, $userid) ?
                userdate($this->get_mod_user_startdate($cm, $userid), get_string('strftimedatefullshort'))
                : $startdate;
        }
        return $startdate;
    }

    /**
     * Get module user startdate.
     *
     * @param object $mod
     * @param int $userid
     * @return int|bool Mod start date if available otherwiser returns false.
     */
    public function get_mod_user_startdate($mod, $userid) {
        global $DB;
        $course = $mod->get_course();
        $record = $DB->get_record('tool_timetable_modules', ['cmid' => $mod->id ?? 0]);
        $timemanagement = new \tool_timetable\time_management($course->id);
        $userenrolments = $timemanagement->get_course_user_enrollment($userid, $course->id);
        if (!empty($userenrolments)) {
            $timestarted = $userenrolments[0]['timestart'] ?? 0;
            $timeended = $userenrolments[0]['timeend'] ?? 0;
            if ($record) {
                $moduledates = $timemanagement->calculate_coursemodule_managedates($record, $timestarted, $timeended);
                $startdate = $moduledates['startdate'] ?? false;
            }
        }
        return $startdate ?? false;
    }
}
