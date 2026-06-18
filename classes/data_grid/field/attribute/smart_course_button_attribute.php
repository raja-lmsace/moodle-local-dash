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
 * Smart course button.
 *
 * @package   local_dash
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use block_dash\local\data_grid\field\attribute\details_link_attribute;
use block_dash\local\data_grid\filter\course_condition;
use local_dash\util\enrolment_state;
use html_writer;
use moodle_url;

/**
 * Smart course button.
 *
 * @package local_dash
 */
class smart_course_button_attribute extends abstract_field_attribute {
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
            return '';
        }
        $courseid = $data;
        $coursecontext = \context_course::instance($courseid);
        $enrolled = is_enrolled($coursecontext, $USER, false, false);
        $isactiveenrolment = is_enrolled($coursecontext, $USER, false, true);
        $canselfenrol = $this->can_selfenrol($courseid);

        // Check if user is site admin or has category manage capability.
        $canmanagecategory = is_siteadmin() || has_capability('moodle/category:manage', \context_system::instance());

        // Determine granular enrolment state.
        $state = enrolment_state::get_user_enrolment_state($courseid, $USER->id);

        // Enrolled users.
        if ($state !== enrolment_state::STATE_NOT_ENROLLED) {
            $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            $label = get_string('viewcourse', 'block_dash');

            if ($state === enrolment_state::STATE_ACTIVE) {
                // Active enrolment -> btn-primary.
                $btnclass = 'btn btn-primary';
            } else {
                // Suspended/Future/Expired -> btn-secondary (muted).
                $btnclass = 'btn btn-secondary text-muted';
            }

            return \html_writer::link($url, $label, [
                'class' => $btnclass,
                'aria-label' => get_string('smart_coursebutton', 'block_dash'),
            ]);
        }

        // Not enrolled - check guest access.
        if ($this->is_guestaccess($courseid)) {
            $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            return \html_writer::link($url, get_string('viewcourse', 'block_dash'), [
                'class' => 'btn btn-primary',
                'aria-label' => get_string('smart_coursebutton', 'block_dash'),
            ]);
        }

        // Not enrolled, no guest - determine available enrolment methods.
        $canselfenrol = $this->can_selfenrol($courseid);

        if ($canselfenrol) {
            // Any self-enrolment method available.
            if ($this->is_only_autoenrol($courseid)) {
                // Auto enrolment only -> Course page redirect.
                $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            } else {
                // Enrolment options page.
                $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            }
            return \html_writer::link($url, get_string('enrolnow', 'block_dash'), [
                'class' => 'btn btn-primary',
                'aria-label' => get_string('smart_coursebutton', 'block_dash'),
            ]);
        }

        // No self-enrolment available - check external purchase options.
        $shopurl = $this->get_shopurl($courseid);
        if ($shopurl) {
            // Shop URL -> "Buy now".
            return \html_writer::link($shopurl, get_string('buynow', 'block_dash'), [
                'class' => 'btn btn-primary',
                'aria-label' => get_string('smart_coursebutton', 'block_dash'),
            ]);
        }

        if ($this->has_custom_content_booking()) {
            // Block has details area custom content -> "Book now" (opens details area).
            $blockinstanceid = (int) $this->get_option('blockinstanceid');
            $detailid = details_link_attribute::build_detail_id($record, $blockinstanceid);
            return \html_writer::link('#' . $detailid, get_string('booknow', 'block_dash'), [
                'class' => 'btn btn-primary dash-details-open-link',
                'data-action' => 'open-details-modal',
                'data-detail-id' => $detailid,
                'aria-label' => get_string('smart_coursebutton', 'block_dash'),
            ]);
        }

        // No methods, no shop URL, no custom content -> "View course" (muted), redirects to enrolment options.
        if (is_viewing($coursecontext, $USER)) {
             $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
        } else {
            $url = new \moodle_url('/enrol/index.php', ['id' => $courseid]);
        }
        return \html_writer::link($url, get_string('viewcourse', 'block_dash'), [
            'class' => 'btn btn-secondary text-muted',
            'aria-label' => get_string('smart_coursebutton', 'block_dash'),
        ]);
    }

    /**
     * Fetch the configured shop url from the course customfield. customfield will mentioned in the general settings.
     *
     * @param int $courseid
     * @return string|false
     */
    public function get_shopurl($courseid) {
        global $DB;

        $fieldid = get_config('local_dash', 'courseshopurl');
        if ($fieldid) {
            if (class_exists('\core_customfield\field_controller')) {
                // Confirm the selected custom field is available.
                if (!$record = $DB->get_record(\core_customfield\field::TABLE, ['id' => $fieldid], '*', IGNORE_MISSING)) {
                    return false;
                }
                $field = \core_customfield\field_controller::create($fieldid);
                $data = \core_customfield\api::get_instance_fields_data([$fieldid => $field], $courseid);
                return !empty($data) ? current($data)->export_value() : false;
            } else if (block_dash_is_totara()) {
                global $DB;
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
     * check so that "Book now" / "Available for booking" is determined by whether
     * the block's details area has custom content, not by a global admin setting.
     *
     * @return bool True if the block has details area custom content configured.
     */
    public function has_custom_content_booking(): bool {
        return !empty($this->get_option('has_details_custom_content'));
    }

    /**
     * Check the course has guest access enabled.
     *
     * @param  int $courseid
     * @return bool
     */
    public function is_guestaccess($courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach ($enrolinstances as $key => $instance) {
            if ($instance->enrol == 'guest') {
                return true;
            }
        }
        return false;
    }

    /**
     * Verify the course has enabled enrollment method to enrol by self.
     *
     * @param  int $courseid
     * @return bool
     */
    public function can_selfenrol($courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        global $USER;
        foreach ($enrolinstances as $instance) {
            if (!in_array($instance->enrol, ['self', 'credit', 'autoenrol', 'fee'])) {
                continue;
            }
            $enrol = enrol_get_plugin($instance->enrol);
            $selfenrolstatus = ($instance->enrol === 'self' && $enrol->can_self_enrol($instance) === true);
            $autoenrol = ($instance->enrol === 'autoenrol' && $enrol->enrol_allowed($instance, $USER));
            $creditenrolstatus = ($instance->enrol === 'credit');
            $feeenrol = ($instance->enrol === 'fee' && $instance->cost > 0
                && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
                && (!$instance->enrolenddate || $instance->enrolenddate > time()));

            if ($selfenrolstatus || $autoenrol || $creditenrolstatus || $feeenrol) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the only available self-enrolment method for the course is autoenrol.
     *
     * @param int $courseid
     * @return bool True if autoenrol is the only available self-enrol method.
     */
    public function is_only_autoenrol($courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        $availablemethods = [];
        global $USER;
        foreach ($enrolinstances as $instance) {
            if (!in_array($instance->enrol, ['self', 'credit', 'autoenrol', 'fee'])) {
                continue;
            }
            $enrol = enrol_get_plugin($instance->enrol);
            $selfenrolstatus = ($instance->enrol === 'self' && $enrol->can_self_enrol($instance) === true);
            $autoenrol = ($instance->enrol === 'autoenrol' && $enrol->enrol_allowed($instance, $USER));
            $creditenrolstatus = ($instance->enrol === 'credit' && $enrol->can_self_enrol($instance) === true);
            $feeenrol = ($instance->enrol === 'fee' && $instance->cost > 0
                && (!$instance->enrolstartdate || $instance->enrolstartdate < time())
                && (!$instance->enrolenddate || $instance->enrolenddate > time()));

            if ($selfenrolstatus || $creditenrolstatus || $feeenrol) {
                return false; // There are non-autoenrol methods.
            }
            if ($autoenrol) {
                $availablemethods[] = 'autoenrol';
            }
        }
        return !empty($availablemethods);
    }
}
