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

namespace dashaddon_dashboard;

use core\hook\after_config;
use moodle_url;
use Exception;
use context_course;

/**
 * Callbacks for hooks.
 *
 * @package    dashaddon_dashboard
 * @copyright  bdecent gmbh 2023 <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Listener for the after_config hook.
     *
     * @param after_config $hook
     */
    public static function after_config(\core\hook\after_config $hook): void {

        global $PAGE, $DB;
        try {
            // First rule out conditions where a redirect should never happen.
            // Also skip is $CFG->kickstart_pro is false.
            if (AJAX_SCRIPT) {
                return;
            }
            // Check course view or enrolment info page.
            if ($PAGE->bodyid == 'page-course-view') {
                $courseid = optional_param('id', 0, PARAM_INT);
                $coursecontext = context_course::instance($courseid);
                if (!is_enrolled($coursecontext)) {
                    $coursedashboard = $DB->get_record('dashaddon_dashboard_dash', ['courseid' => $courseid,
                        'redirecttodashboard' => true, 'permission' => 'public'], '*', IGNORE_MULTIPLE);
                    if ($coursedashboard) {
                        redirect(new moodle_url('/local/dash/addon/dashboard/dashboard.php', ['id' => $coursedashboard->id]));
                    }
                }
            }
        } catch (Exception $e) {
            debugging($e->getMessage()); // Prevent any issues from breaking entire site.
        }
    }
}
