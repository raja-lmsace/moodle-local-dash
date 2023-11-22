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
 * Replace the course completion status data to string.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use block_dash\local\data_grid\filter\course_condition;

/**
 * Replace enrolment status data to string.
 *
 * @package local_dash
 */
class enrollment_options_attribute extends abstract_field_attribute {

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \int $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB, $USER;
        if ($data) {
            return $this->get_course_enrollment_options($data);
        } else {
            return '-';
        }
    }

    /**
     * Get enrollment options available for course has "self, fee, credits" enrolments enabled.
     *
     * @param int $courseid Course id.
     * @return string|null
     */
    public function get_course_enrollment_options(int $courseid): ?string {
        $enrolinstances = enrol_get_instances($courseid, true);
        // Filter the instance basecd on the availability.
        $instances = array_filter($enrolinstances, function($instance) {
            global $USER;

            if (!in_array($instance->enrol, ['self', 'credit', 'autoenrol', 'guest', 'fee'])) {
                return false;
            }

            return self::is_self_enrollment($instance) ? true : false;

        });

        $credits = '';
        $creditcount = 0;
        foreach ($instances as $instance) {
            if ($instance->enrol == 'credit') {
                $credits = ($instance->customint7 && (!$credits || $credits > $instance->customint7))
                    ? $instance->customint7 : $credits; // Min credit.
                $creditcount++;
                continue;
            }
        }

        $enrols = array_column($instances, 'enrol');
        $unique = array_unique($enrols);

        if (count($unique) > 1) {
            return \html_writer::link(
                new \moodle_url('/enrol/index.php', ['id' => $courseid]),
                get_string('enrollmentoptions:seeoptions', 'block_dash')
            );
        }
        foreach ($instances as $instance) {
            switch ($instance->enrol) :
                case "self":
                case "guest":
                case "autoenrol":
                    return get_string('enrollmentoptions:free', 'block_dash');
                    break;
                case "credit":
                    return ($creditcount > 1)
                        ? get_string('enrollmentoptions:fromcredits', 'block_dash', $credits)
                        : get_string('enrollmentoptions:credits', 'block_dash', $credits);
                    break;
                case "fee":
                    return get_string('enrollmentoptions:cost', 'block_dash',
                        ['cost' => $instance->cost, 'currency' => $instance->currency]);
                    break;
                default:
                    return '-';
            endswitch;
        }
        return '-';
    }

    /**
     * Verify is self enrollment enabled.
     *
     * @param \stdclass $instance
     * @return bool
     */
    public static function is_self_enrollment($instance) {
        global $USER;

        $enrol = enrol_get_plugin($instance->enrol);

        $selfenrolstatus = ($instance->enrol === 'self' && $enrol->can_self_enrol($instance) === true);
        $autoenrol = ($instance->enrol === 'autoenrol' && $enrol->enrol_allowed($instance, $USER));
        $creditenrolstatus = ($instance->enrol === 'credit' && $enrol->can_self_enrol($instance) === true);
        $tryguestaccess = ($instance->enrol === 'guest' && $enrol->try_guestaccess($instance) !== false);
        $feeenrol = ($instance->enrol == 'fee' && $instance->cost > 0
            && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
            && (!$instance->enrolenddate || $instance->enrolenddate > time())
        );

        $enrolhook = (!in_array($instance->enrol, ['self', 'autoenrol', 'credit', 'fee'])
            && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
            && (!$instance->enrolenddate || $instance->enrolenddate > time()));

        // Confirm the user is have access to enrol into using the enrolment instance.
        if ($selfenrolstatus || $autoenrol || $enrolhook || $creditenrolstatus || $tryguestaccess || $feeenrol) {
            return true;
        }
        return false;
    }
}
