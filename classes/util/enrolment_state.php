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
 * Enrolment state utility class for determining granular user enrolment states.
 *
 * @package    local_dash
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\util;

/**
 * Utility class for determining the granular enrolment state of a user in a course.
 *
 * @package local_dash
 */
class enrolment_state {
    /** User is not enrolled in the course. */
    const STATE_NOT_ENROLLED = 'not_enrolled';

    /** User has an active enrolment. */
    const STATE_ACTIVE = 'active';

    /** User enrolment is suspended. */
    const STATE_SUSPENDED = 'suspended';

    /** User enrolment has not yet started (timestart is in the future). */
    const STATE_FUTURE = 'future';

    /** User enrolment has expired (timeend is in the past). */
    const STATE_EXPIRED = 'expired';

    /**
     * Determine the most favorable enrolment state for a user in a course.
     *
     * When a user has multiple enrolments (via different methods), the most
     * favorable state is returned. Priority order: active > future > suspended > expired.
     *
     * @param int $courseid The course ID.
     * @param int|null $userid The user ID. Defaults to current user.
     * @return string One of the STATE_* constants.
     */
    public static function get_user_enrolment_state(int $courseid, ?int $userid = null): string {
        global $DB, $USER;

        $userid = $userid ?? $USER->id;
        $now = time();

        $sql = "SELECT ue.id, ue.status, ue.timestart, ue.timeend
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.userid = :userid
                   AND e.status = :enrolstatus";
        $params = [
            'courseid' => $courseid,
            'userid' => $userid,
            'enrolstatus' => ENROL_INSTANCE_ENABLED,
        ];

        $enrolments = $DB->get_records_sql($sql, $params);
        if (empty($enrolments)) {
            return self::STATE_NOT_ENROLLED;
        }

        $states = [];
        foreach ($enrolments as $ue) {
            if ($ue->status == ENROL_USER_SUSPENDED) {
                $states[] = self::STATE_SUSPENDED;
            } else {
                // ENROL_USER_ACTIVE.
                $started = ($ue->timestart <= $now);
                $notexpired = ($ue->timeend == 0 || $ue->timeend > $now);
                if ($started && $notexpired) {
                    $states[] = self::STATE_ACTIVE;
                } else if (!$started) {
                    $states[] = self::STATE_FUTURE;
                } else {
                    $states[] = self::STATE_EXPIRED;
                }
            }
        }

        // Return the most favorable state.
        $priority = [
            self::STATE_ACTIVE => 1,
            self::STATE_FUTURE => 2,
            self::STATE_SUSPENDED => 3,
            self::STATE_EXPIRED => 4,
        ];

        usort($states, function ($a, $b) use ($priority) {
            return ($priority[$a] ?? 99) - ($priority[$b] ?? 99);
        });

        return $states[0];
    }
}
