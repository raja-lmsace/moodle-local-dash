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
 * Filters results to current course only.
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;
/**
 * Filters results to current course only.
 *
 * @package local_dash
 */
class event_condition extends condition {
    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['eventnames'])) {
            $eventnames = $this->get_preferences()['eventnames'];

            if (is_array($eventnames)) {
                return $eventnames;
            } else {
                return [$eventnames];
            }
        }

        return [];
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('courses');
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

        global $CFG;

        require_once("$CFG->dirroot/report/eventlist/classes/list_generator.php");

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $eventlist = \report_eventlist_list_generator::get_all_events_list();

        $options = [];
        foreach ($eventlist as $event) {
            $options[$event['eventname']] = $event['raweventname'];
        }

        $mform->addElement('autocomplete', $fieldname . '[eventnames]', get_string('events', 'block_dash'), $options, [
            'multiple' => true,
            'noselectionstring' => get_string('events', 'block_dash'),
        ]);
        $mform->hideIf($fieldname . '[eventnames]', $fieldname . '[enabled]');
    }
}
