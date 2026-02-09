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
 * My profile - dashaddon widget. Contains functions to verify the due and overdues from the learning tools timemanagement.
 *
 * @package    dashaddon_myprofile
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get user due activies.
 *
 * @param int $courseid
 * @param int $userid User id.
 * @return int count due activites.
 */
function dashaddon_myprofile_get_user_dueactivities($courseid, $userid) {
    global $DB, $CFG;

    $duecount = 0;
    $overduecount = 0;

    require_once($CFG->dirroot . '/lib/completionlib.php');

    $modinfo = get_fast_modinfo($courseid);
    $completion = new \completion_info($modinfo->get_course());
    if (!empty($modinfo->sections)) {
        foreach ($modinfo->sections as $modnumbers) {
            if (!empty($modnumbers)) {
                foreach ($modnumbers as $modnumber) {
                    $mod = $modinfo->cms[$modnumber];
                    if (
                        $DB->record_exists('course_modules', ['id' => $mod->id, 'deletioninprogress' => 0])
                        && !empty($mod) && $mod->uservisible
                    ) {
                        $data = $completion->get_data($mod, true, $userid);
                        if ($data->completionstate != COMPLETION_COMPLETE) {
                            $cmcompletion = new cm_completion($mod, $userid);
                            $overduecount = ($cmcompletion->is_overdue()) ? $overduecount + 1 : $overduecount;
                            $duecount = ($cmcompletion->is_due_today()) ? $duecount + 1 : $duecount;
                        }
                    }
                }
            }
        }
    }
    return [$duecount, $overduecount];
}

/**
 * Get module user duedate.
 *
 * @param object $mod
 * @param int $userid
 * @param bool $duestatus
 * @return int|bool Mod due date if available otherwiser returns false.
 */
function dashaddon_myprofile_get_mod_user_duedate($mod, $userid, $duestatus = false) {
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
            $duedate = $moduledates['duedate'] ?? false;
        }
    }
    return $duedate ?? false;
}

/**
 * Course module completion, helps to fetch the due and overdue of the course module.
 *
 * Modified from format_designer.
 */
class cm_completion {
    /**
     * @var cm_info
     */
    private $cm;

    /**
     * Id of the user to verify with.
     *
     * @var int
     */
    private $userid;

    /**
     * @var completion_info[]
     */
    private static $completioninfos = [];

    /**
     * Constructor.
     *
     * @param cm_info $cm
     * @param int $userid
     */
    public function __construct(cm_info $cm, $userid) {
        $this->cm = $cm;
        $this->userid = $userid;
    }

    /**
     * Get course module.
     *
     * @return cm_info
     */
    final protected function get_cm(): cm_info {
        return $this->cm;
    }

    /**
     * Get when cm must be completed by. Check is timemanagement tool contains any duedates for this module.
     *
     * @return int
     */
    final public function get_completion_expected(): int {
        if ($duedate = self::timetool_duedate($this->cm, $this->userid)) {
            return $duedate;
        }
        return $this->cm->completionexpected;
    }

    /**
     * Get timemanagement tools due date for the module.
     *
     * @param cm_info $cm
     * @param int $userid
     * @param bool $timemanagement
     *
     * @return int|bool Mod due date if available otherwiser returns false.
     */
    public static function timetool_duedate($cm, $userid, $timemanagement = false) {

        if (self::is_timemanagement_installed()) {
            $duedate = dashaddon_myprofile_get_mod_user_duedate($cm, $userid);
            return $duedate ?? false;
        }
        return false;
    }

    /**
     * Check if cm is overdue for user.
     *
     * @return bool
     */
    final public function is_overdue(): bool {
        return $this->get_completion_expected() > 0 && $this->get_completion_expected() < strtotime("-1 day");
    }

    /**
     * Check if cm is due within a day.
     *
     * @return bool
     */
    final public function is_due_today(): bool {
        return $this->get_completion_expected() > 0 &&
            (date('y-m-d', $this->get_completion_expected()) == date('y-m-d'));
    }

    /**
     * Verify the time managment plugin is installed.
     *
     * @return bool
     */
    public static function is_timemanagement_installed() {
        global $CFG;
        static $result;

        if ($result == null) {
            if (array_key_exists('timetable', \core_component::get_plugin_list('tool'))) {
                require_once($CFG->dirroot . '/admin/tool/timetable/classes/time_management.php');
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }
}
