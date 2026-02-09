<?php
// This file is part of The Bootstrap Moodle theme
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
 * Parent role condition.
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;

/**
 * Parent role condition.
 */
class context_level_condition extends condition {
    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['contextlevels'])) {
            $contextlevels = $this->get_preferences()['contextlevels'];

            if (is_array($contextlevels)) {
                return $contextlevels;
            } else {
                return [$contextlevels];
            }
        }

        return [];
    }

    /**
     * Get condition label.
     * @return string
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('contextlevel', 'block_dash');
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

        $choices = [
            CONTEXT_SYSTEM => get_string('systemcontext', 'block_dash'),
            CONTEXT_USER => get_string('usercontext', 'block_dash'),
            CONTEXT_COURSECAT => get_string('coursecatcontext', 'block_dash'),
            CONTEXT_COURSE => get_string('coursecontext', 'block_dash'),
            CONTEXT_MODULE => get_string('modulecontext', 'block_dash'),
            CONTEXT_BLOCK => get_string('blockcontext', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[contextlevels]',
            get_string('contextlevel', 'block_dash'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[contextlevels]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }
}
