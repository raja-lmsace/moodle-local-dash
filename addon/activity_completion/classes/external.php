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
 *
 * @package     dashaddon_activity_completion
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/externallib.php');

/**
 * Define external class.
 */
class external extends \external_api {


    /**
     * Parameters define to the override activity completion status.
     *
     * @return array list of option parameters
     */
    public static function override_completion_status_parameters() {
        return new external_function_parameters(
            [
                'cmid' => new external_value(PARAM_INT, 'Course module id', VALUE_REQUIRED),
                'userid' => new external_value(PARAM_INT, 'Userid', VALUE_REQUIRED),
                'state' => new external_value(PARAM_INT, 'New state of activity completion', VALUE_REQUIRED),
                'overrideuser' => new external_value(PARAM_INT, 'Override user id for activity completion', VALUE_REQUIRED),
                'cmcid' => new external_value(PARAM_INT, 'Course module completion ID', VALUE_REQUIRED),
            ],
        );
    }

    /**
     * Override the activity completion state for selected user.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param int $state New state of activity completion.
     * @param int $overrideuser Override user ID.
     * @param int $cmcid Course module completion ID.
     * @return bool Completion status.
     */
    public static function override_completion_status($cmid, $userid, $state, $overrideuser, $cmcid) {
        global $DB;

        $status = false;
        $result = [];
        $params = self::validate_parameters(self::override_completion_status_parameters(),
            ['cmid' => $cmid, 'userid' => $userid, 'state' => $state,
                'overrideuser' => $overrideuser, 'cmcid' => $cmcid
            ]);

        $transaction = $DB->start_delegated_transaction();

        $completiondata = $DB->record_exists('course_modules_completion',
            ['coursemoduleid' => $params['cmid'], 'userid' => $params['userid'], ]);
        if ($completiondata) {
            $completiondata->id = $params['cmcid'];
            $completiondata->completionstate = ($params['state'] == 0) ? 1 : 0;
            $completiondata->overrideby = $params['overrideuser'];
            $completiondata->timemodified = time();
            $DB->update_record('course_modules_completion', $completiondata);
            $status = true;
        } else {
            $record = new stdClass();
            $record->coursemoduleid = $params['cmid'];
            $record->userid = $params['userid'];
            $record->completionstate = ($params['state'] == 0) ? 1 : 0;
            $record->overrideby = $params['overrideuser'];
            $record->timemodified = time();
            $DB->insert_record('course_modules_completion', $record);
            $status = true;
        }
        $transaction->allow_commit();

        return $result[] = [
            'status' => $status,
        ];
    }

    /**
     * Return a message.
     * @return array message.
     */
    public static function override_completion_status_returns() {
        return new external_single_structure(
            array(
            'status' => new \external_value(PARAM_BOOL, 'Return status message'),
            )
        );
    }
}