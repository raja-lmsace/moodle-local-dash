<?php
// This file is part of the local_kickstart_pro plugin for Moodle - http://moodle.org/
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

namespace dashaddon_dashboard\local\hooks\output;
use moodle_url;
use Exception;
use context_course;

/**
 * Hook callbacks for dashaddon_dashboard
 *
 * @package    dashaddon_dashboard
 * @copyright  2024 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_http_headers {
    /**
     * Callback allowing to add warning on the filter settings page
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function callback(\core\hook\output\before_http_headers $hook): void {
        global $PAGE, $DB;
        try {
            // First rule out conditions where a redirect should never happen.
            // Also skip is $CFG->kickstart_pro is false.
            if (AJAX_SCRIPT || is_siteadmin()) {
                return;
            }
            // Check course view or enrolment info page.
            if ($PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $courseid  = $PAGE->url->get_param('id');
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
