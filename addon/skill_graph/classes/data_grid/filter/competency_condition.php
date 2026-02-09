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
 * Filters results to specific competencies.
 *
 * @package    dashaddon_skill_graph
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_skill_graph\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;
use core_competency\api;

/**
 * Filters results to specific competencies.
 *
 * @package dashaddon_skill_graph
 */
class competency_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_EQUAL;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('competencyframework', 'block_dash');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        global $CFG;

        if (isset($this->get_preferences()['competencyframework']) && is_array($this->get_preferences()['competencyframework'])) {
            $competencyframework = $this->get_preferences()['competencyframework'];
            return $competencyframework;
        }
        return '';
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

        $competencies = api::list_frameworks('shortname', 'ASC', 0, 0, $this->get_context(), 'parents');
        $list = [];
        foreach ($competencies as $key => $competency) {
            $list[$competency->get('id')] = format_string($competency->get('shortname'));
        }

        $select = $mform->addElement('select', $fieldname . '[competencyframework]', '', $list, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[competencyframework]', $fieldname . '[enabled]');
    }
}
