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
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use block_dash\local\data_grid\filter\course_condition;
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
     * @param \int $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
    global $DB, $USER;
    if (!$data) {
        return '';
    }
    $coursecontext = \context_course::instance($data);
    $enrolled = is_enrolled($coursecontext, $USER, false, false);
    $isactiveenrolment = is_enrolled($coursecontext, $USER, false, true);
    $canselfenrol = $this->can_selfenrol($data);

    // Check if user is site admin or has category manage capability.
    $canmanagecategory = is_siteadmin() || has_capability('moodle/category:manage', \context_system::instance());

    // Get shop url.
    $shopurl = $this->get_shopurl($data);

    if ($isactiveenrolment || $this->is_guestaccess($data) || $canmanagecategory) {
        $url = new \moodle_url('/course/view.php', ['id' => $data]);
        return \html_writer::link(
            $url,
            get_string('viewcourse', 'block_dash'),
            ['class' => 'btn btn-primary',
            'label' => get_string('viewcourse', 'block_dash'),
            'aria-label' => get_string('smart_coursebutton', 'block_dash')]
        );
    } else if ($shopurl && !$enrolled && !$canselfenrol) {
        // Buy now.
        return html_writer::link(
            $shopurl,
            get_string('buynow', 'block_dash'),
            ['class' => 'btn btn-primary',
            'label' => get_string('buynow', 'block_dash'),
            'aria-label' => get_string('smart_coursebutton', 'block_dash')]
        );
    } else if (!$enrolled && $canselfenrol) {
        // Enrol Now.
        $url = new \moodle_url('/enrol/index.php', ['id' => $data]);
        return html_writer::link(
            $url,
            get_string('enrolnow', 'block_dash'),
            ['class' => 'btn btn-primary',
            'label' => get_string('enrolnow', 'block_dash'),
            'aria-label' => get_string('smart_coursebutton', 'block_dash')]
        );
    } else if (!$isactiveenrolment || !$canselfenrol) {
        // Not available.
        return \html_writer::span(get_string('notavailable', 'block_dash'));
    }
    return '';
    }

    /**
     * Fetch the configured shop url from the course customfield. cusotmfield will mentioned in the general settings.
     *
     * @param int $courseid
     * @return bool
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
     * Check the course has guest access enabled.
     *
     * @param int $courseid
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
     * @param int $courseid
     * @return bool
     */
    public function can_selfenrol($courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        // Filter the instance basecd on the availability.
        global $USER;
        foreach ($enrolinstances as $instance) {
            if (!in_array($instance->enrol, ['self', 'credit', 'autoenrol'])) {
                continue;
            }
            $enrol = enrol_get_plugin($instance->enrol);
            $selfenrolstatus = ($instance->enrol === 'self' && $enrol->can_self_enrol($instance) === true);
            $autoenrol = ($instance->enrol === 'autoenrol' && $enrol->enrol_allowed($instance, $USER));
            $creditenrolstatus = ($instance->enrol === 'credit' && $enrol->can_self_enrol($instance) === true);

            if ($selfenrolstatus || $autoenrol || $creditenrolstatus) {
                return true;
            }
        }
        return false;
    }
}
