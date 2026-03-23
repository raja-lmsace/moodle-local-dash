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
 * Activity completion external functions.
 *
 * @package     dashaddon_activity_completion
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use grade_plugin_return;
use grade_report_grader;
use external_function_parameters;
use external_single_structure;
use external_value;

require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_object.php');
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/report/grader/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Define external class.
 */
class external extends \external_api {
    /**
     * Parameters defintion to grade activity.
     *
     * @return array list of option parameters.
     */
    public static function grade_activity_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'User id'),
                'formdata' => new external_value(PARAM_RAW, 'The data from the grade activity'),
                'cmid' => new external_value(PARAM_INT, 'Course module id'),
                'gradeitemid' => new external_value(PARAM_INT, 'Grade item id'),
            ]
        );
    }

    /**
     * grade the activity.
     *
     * @param int $userid user id
     * @param array $formdata get a grade data
     * @param int $cmid Course module id
     * @param int $gradeitemid Grade item id
     *
     * @return array $message
     */
    public static function grade_activity($userid, $formdata, $cmid, $gradeitemid) {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $vaildparams = self::validate_parameters(
            self::grade_activity_parameters(),
            ['userid' => $userid, 'formdata' => $formdata, 'cmid' => $cmid, 'gradeitemid' => $gradeitemid]
        );
        parse_str($vaildparams['formdata'], $gradedata);

        $cmid = $vaildparams['cmid'];
        $userid = $vaildparams['userid'];

        $message = '';
        $status = false;
        [$course, $cm] = get_course_and_cm_from_cmid($cmid);

        $coursecontext = \context_course::instance($course->id);
        $gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'grader', 'courseid' => $course->id]);
        $report = new grade_report_grader($course->id, $gpr, $coursecontext);

        $data = new \stdClass();
        $data->id = $course->id;
        $data->report = 'grader';
        $data->timepageload = time();
        $data->grade = $gradedata['grade'];

        $warnings = $report->process_data($data);
        // Fetch the user grade instance.
        $gradeinstance = !empty($report->grades[$userid][$gradeitemid]) ? $report->grades[$userid][$gradeitemid] : [];
        // If grade instance is not empty, then notify the grade chnaged to trigger the module completion.
        if (!empty($gradeinstance)) {
            $gradeitem = $gradeinstance->grade_item;
            $completion = new \completion_info($course);
            if (!$completion->is_enabled($cm)) {
                return;
            }
            // Inform the grade has changed, inform the module completion.
            $completion->inform_grade_changed($cm, $gradeitem, $gradeinstance, false, false);
        }

        foreach ($warnings as $warning) {
            $message = $warning;
        }
        if (empty($message)) {
            $status = true;
        }

        return [
            'message' => $message,
            'status' => $status,
        ];
    }

    /**
     * Return a message.
     *
     * @return array message.
     */
    public static function grade_activity_returns() {
        return new external_single_structure(
            [
                'message' => new external_value(PARAM_TEXT, 'Return status message'),
                'status' => new external_value(PARAM_TEXT, 'Return status'),
            ]
        );
    }
}
