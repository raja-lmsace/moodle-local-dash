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
 * Filters results to specific module name condition.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;


/**
 * Filters results to specific module name condition.
 *
 * @package local_dash
 */
class activity_modulename_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get filter config label.
     *
     * @return string
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('modulename', 'block_dash');
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

        global $DB;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $modules = $DB->get_records_sql_menu("SELECT DISTINCT id, name FROM {modules} WHERE visible = 1");
        $select = $mform->addElement('select', $fieldname . '[modules]', '', $modules, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[modules]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['modules']) && is_array($this->get_preferences()['modules'])) {
            $status = $this->get_preferences()['modules'];
            return $status;
        }
        return [];
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        [$sql, $params] = parent::get_sql_and_params();

        if ($sql) {
            [$insql, $inparams] = $DB->get_in_or_equal($this->get_preferences()['modules'], SQL_PARAMS_NAMED, 'm', true, true);
            $sql = ' m.id ' . $insql;
            $params = array_merge($params, $inparams);
        }
        return [$sql, $params];
    }
}
