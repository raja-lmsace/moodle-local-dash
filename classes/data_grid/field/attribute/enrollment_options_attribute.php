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
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use block_dash\local\data_grid\filter\course_condition;
use local_dash\util\enrolment_state;

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
     * @param  \int      $data
     * @param  \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB, $USER;

        if (!$data) {
            return '-';
        }

        $courseid = (int) $data;
        $coursecontext = \context_course::instance($courseid);

        // Determine granular enrolment state.
        $state = enrolment_state::get_user_enrolment_state($courseid, $USER->id);

        switch ($state) {
            case enrolment_state::STATE_ACTIVE:
                return get_string('enrollmentoptions:activeenrolment', 'block_dash');
            case enrolment_state::STATE_SUSPENDED:
                return get_string('enrollmentoptions:pendingenrolment', 'block_dash');
            case enrolment_state::STATE_FUTURE:
                return get_string('enrollmentoptions:upcomingenrolment', 'block_dash');
            case enrolment_state::STATE_EXPIRED:
                return get_string('enrollmentoptions:expiredenrolment', 'block_dash');
        }

        // Not enrolled - check guest access.
        if ($this->is_guestaccess($courseid)) {
            return get_string('enrollmentoptions:open', 'block_dash');
        }

        // Not enrolled, no guest - check available enrolment methods.
        return $this->get_course_enrollment_options($courseid);
    }

    /**
     * Get enrollment options available for course has "self, fee, credits" enrolments enabled.
     *
     * @param  int $courseid Course id.
     * @return string|null
     */
    public function get_course_enrollment_options(int $courseid): ?string {
        $enrolinstances = enrol_get_instances($courseid, true);
        // Filter the instance basecd on the availability.
        $instances = array_filter(
            $enrolinstances,
            function ($instance) {
                global $USER;

                if (!in_array($instance->enrol, ['self', 'credit', 'autoenrol', 'guest', 'fee'])) {
                    return false;
                }

                return self::is_self_enrollment($instance) ? true : false;
            }
        );

        // If no self-enrollment methods available, check external purchase options.
        if (empty($instances)) {
            // Check shop URL.
            $shopurl = $this->get_shopurl($courseid);
            if ($shopurl) {
                return get_string('enrollmentoptions:availableforpurchase', 'block_dash');
            }

            // Check if block has details area custom content configured.
            if ($this->has_custom_content_booking()) {
                return get_string('enrollmentoptions:availableforbooking', 'block_dash');
            }
            // Nothing available.
            return get_string('enrollmentoptions:notavailable', 'block_dash');
        }

        $credits = '';
        $creditcount = 0;
        $mincost = null;
        $mincurrency = '';
        $feecount = 0;
        foreach ($instances as $instance) {
            if ($instance->enrol == 'credit') {
                $credits = ($instance->customint7 && (!$credits || $credits > $instance->customint7))
                    ? $instance->customint7 : $credits; // Min credit.
                $creditcount++;
                continue;
            }
            if ($instance->enrol == 'fee' || $instance->enrol == 'paypal') {
                if ($mincost === null || $instance->cost < $mincost) {
                    $mincost = $instance->cost;
                    $mincurrency = $instance->currency;
                }
                $feecount++;
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
                case "paypal":
                    return ($feecount > 1)
                        ? get_string(
                            'enrollmentoptions:fromcost',
                            'block_dash',
                            ['cost' => $mincost, 'currency' => $mincurrency]
                        )
                        : get_string(
                            'enrollmentoptions:cost',
                            'block_dash',
                            ['cost' => $mincost, 'currency' => $mincurrency]
                        );
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
     * @param  \stdclass $instance
     * @return bool
     */
    public static function is_self_enrollment($instance) {
        global $USER;

        $enrol = enrol_get_plugin($instance->enrol);

        $selfenrolstatus = ($instance->enrol === 'self' && $enrol->can_self_enrol($instance) === true);
        $autoenrol = ($instance->enrol === 'autoenrol' && $enrol->enrol_allowed($instance, $USER));
        $creditenrolstatus = ($instance->enrol === 'credit');
        $tryguestaccess = ($instance->enrol === 'guest' && $enrol->try_guestaccess($instance) !== false);
        $feeenrol = ($instance->enrol == 'fee' && $instance->cost > 0
            && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
            && (!$instance->enrolenddate || $instance->enrolenddate > time())
        );
        $paypalenrol = ($instance->enrol == 'paypal' && $instance->cost > 0
            && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
            && (!$instance->enrolenddate || $instance->enrolenddate > time())
        );

        $enrolhook = (!in_array($instance->enrol, ['self', 'autoenrol', 'credit', 'fee', 'paypal'])
            && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
            && (!$instance->enrolenddate || $instance->enrolenddate > time()));

        // Confirm the user is have access to enrol into using the enrolment instance.
        if ($selfenrolstatus || $autoenrol || $enrolhook || $creditenrolstatus || $tryguestaccess || $feeenrol || $paypalenrol) {
            return true;
        }
        return false;
    }

    /**
     * Check the course has guest access enabled.
     *
     * @param int $courseid
     * @return bool
     */
    public function is_guestaccess($courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach ($enrolinstances as $instance) {
            if ($instance->enrol == 'guest') {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch the configured shop url from the course customfield.
     *
     * @param int $courseid
     * @return string|false
     */
    public function get_shopurl($courseid) {
        global $DB;

        $fieldid = get_config('local_dash', 'courseshopurl');
        if ($fieldid) {
            if (class_exists('\core_customfield\field_controller')) {
                if (!$record = $DB->get_record(\core_customfield\field::TABLE, ['id' => $fieldid], '*', IGNORE_MISSING)) {
                    return false;
                }
                $field = \core_customfield\field_controller::create($fieldid);
                $data = \core_customfield\api::get_instance_fields_data([$fieldid => $field], $courseid);
                return !empty($data) ? current($data)->export_value() : false;
            } else if (block_dash_is_totara()) {
                $sql = "SELECT * FROM {course_info_data} cd WHERE cd.fieldid = :fieldid AND cd.courseid = :courseid";
                $record = $DB->get_record_sql($sql, ['courseid' => $courseid, 'fieldid' => $fieldid]);
                return (isset($record->data)) ? $record->data : '';
            }
        }
        return false;
    }

    /**
     * Check whether the block instance has details area custom content configured.
     *
     * This replaces the old per-course custom field check with a per-block-instance
     * check so that "Available for booking" is determined by whether the block's
     * details area has custom content, not by a global admin setting.
     *
     * @return bool True if the block has details area custom content configured.
     */
    public function has_custom_content_booking(): bool {
        return !empty($this->get_option('has_details_custom_content'));
    }
}
