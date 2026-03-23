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
 * Filters results to specific course completion status.
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;
/**
 * Filters results to specific course completion status.
 *
 * @package local_dash
 */
class course_customfield_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Add form fields for this filter (and any settings related to this filter.)
     *
     * @param moodleform $moodleform
     * @param MoodleQuickForm $mform
     * @param string $fieldnameformat
     */
    public function build_settings_form_fields(
        moodleform $moodleform,
        MoodleQuickForm $mform,
        $fieldnameformat = 'filters[%s]'
    ): void {
        global $DB, $CFG;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $elementname = $this->get_field_data($mform);
        if (!empty($elementname)) {
            $mform->getElement($elementname)->updateAttributes(['name' => $fieldname . "[value]"]);
            // Hide textarea editor for totara.
            $mform->hideIf($fieldname . "[value][text]", $fieldname . '[enabled]');
            $mform->hideIf($fieldname . "[value]", $fieldname . '[enabled]');
        }
    }

    /**
     * Get course customfield condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('status');
    }

    /**
     * Get custom field instance data. it contains the field_controller instance data.
     * @param \moodle_form $mform
     * @return field_data
     */
    public function get_field_data(&$mform) {
        global $DB;

        if (class_exists('\core_course\customfield\course_handler')) {
            $handler = \core_course\customfield\course_handler::create();
            $fieldid = $DB->get_field('customfield_field', 'id', ['shortname' => $this->get_name()]);
            $field = \core_customfield\field_controller::create($fieldid);
            $data = \core_customfield\api::get_instance_fields_data([$fieldid => $field], 0);

            if (isset($data[$fieldid])) {
                $data = $data[$fieldid];
                $data->instance_form_definition($mform);
                return $data->get_form_element_name();
            }
        } else {
            $tableprefix = 'course';
            $params = [];
            $field = $DB->get_field('course_info_field', 'id', ['shortname' => $this->get_name()]);
            $item = [];
            $prefix = 'course';
            $formfield = customfield_get_field_instance((object)['id' => 1], $field, $tableprefix, $prefix, false, false);
            $formfield->edit_field($mform);
            return $formfield->inputname;
        }
        return false;
    }

    /**
     * Get custom field id from shortname.
     *
     * @return int
     */
    public function get_field_record() {
        global $DB;
        if (block_dash_is_totara()) {
            $fieldid = $DB->get_field('course_info_field', 'id', ['shortname' => $this->get_name()]);
        } else {
            $fieldid = $DB->get_field('customfield_field', 'id', ['shortname' => $this->get_name()]);
        }
        return $fieldid;
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        $fieldid = $this->get_field_record();
        if (isset($this->get_preferences()['value']) && !empty($fieldid)) {
            $name = $this->get_name();
            $value = $this->get_preferences()['value'];
            $valuecheck = $DB->sql_compare_text(':value_' . $name);
            if (block_dash_is_totara()) {
                $sql = "c.id IN (
                    SELECT courseid FROM {course_info_data} cd WHERE cd.fieldid = :fieldid_$name AND cd.data=$valuecheck
                )";
            } else {
                $sql = "c.id IN (
                    SELECT instanceid FROM {customfield_data} cd WHERE cd.fieldid = :fieldid_$name AND cd.value=$valuecheck
                )";
            }
            $params = ['fieldid_' . $name => $fieldid, 'value_' . $name => $value];
            return [$sql, $params];
        }
    }
}
