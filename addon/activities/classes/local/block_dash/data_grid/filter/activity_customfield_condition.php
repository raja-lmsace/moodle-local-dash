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
 * @package    dashaddon_activities
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;


/**
 * Filters results to specific course completion status.
 *
 * @package dashaddon_activities
 */
class activity_customfield_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get activities condition label.
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
            $mform->hideIf($fieldname . "[value][text]", $fieldname . '[enabled]');
            $mform->hideIf($fieldname . "[value]", $fieldname . '[enabled]');
        }
    }

    /**
     * Get custom field instance data. it contains the field_controller instance data.
     * @param \moodle_form $mform
     * @return field_data
     */
    public function get_field_data(&$mform) {
        global $DB;
        $field = $DB->get_record('local_metadata_field', ['shortname' => $this->get_name(),
            'contextlevel' => CONTEXT_MODULE,
        ]);
        if ($field) {
            $newfield = "\\metadatafieldtype_{$field->datatype}\\metadata";
            $data = new $newfield($field->id, $this->instance->id);
            $data->edit_field_add($mform);
            return $data->inputname;
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
        $fieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => $this->get_name()]);
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
            $name = strtolower($this->get_name());
            $value = $this->get_preferences()['value'];
            $valuecheck = $DB->sql_compare_text(':value_' . $name);
            $sql = "cm.id IN (
                SELECT instanceid FROM {local_metadata} mfd WHERE mfd.fieldid = :fieldid_$name AND mfd.data=$valuecheck
            )";
            $params = ['fieldid_' . $name => $fieldid, 'value_' . $name => $value];
            return [$sql, $params];
        }
    }
}
