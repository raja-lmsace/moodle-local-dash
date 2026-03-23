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
 * Filters results to specific calendar events based activities completion status.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use core_date;
use DateTime;
use dml_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Filters results to specific calendar events based activities completion status.
 */
class event_activities_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        return get_string('eventactivitycompletion', 'block_dash');
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

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'incomplete' => get_string('eventactivity:incomplete', 'block_dash'),
            'both' => get_string('eventactivity:both', 'block_dash'),
        ];

        $mform->addElement(
            'select',
            $fieldname . '[eventactivitycompletion]',
            get_string('eventactivitycompletion', 'block_dash'),
            $choices,
            ['class' => 'elect2-form']
        );
        $mform->hideIf($fieldname . '[eventactivitycompletion]', $fieldname . '[enabled]');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $SITE, $PAGE;

        // Get preferences.
        $preferences = $this->get_preferences();

        if (isset($preferences['eventactivitycompletion']) && $preferences['eventactivitycompletion'] && $preferences['enabled']) {
            // Confirm the dash is addon on user profile page, then use the profile page user as report user.
            $isprofilepage = $PAGE->pagelayout == 'mypublic' && $PAGE->pagetype == 'user-profile';
            $userid = $isprofilepage ? $PAGE->context->instanceid : $USER->id;

            // Activity completion option configured.
            $method = $this->get_preferences()['eventactivitycompletion'];

            $sql = [];
            $params = [];

            // Filter all module events.
            $sql[] = "ce.courseid > :eac_siteid AND ce.modulename <> '' AND ce.instance > 0";
            $params += ['eac_siteid' => $SITE->id];

            // Get the not completed activities.
            if ($method == 'incomplete') {
                // Completed modules by user.
                $completionsql = "(
                    SELECT coursemoduleid
                    FROM {course_modules_completion} cmc
                    WHERE cmc.userid = :eac_userid AND cmc.completionstate >= :eac_completionstate
                )";
                $params += ['eac_userid' => $userid, 'eac_completionstate' => COMPLETION_COMPLETE];

                // Completion sql.
                $sql[] = "cm.id NOT IN $completionsql";
            }

            return ['(' . implode(' AND ', $sql) . ')', $params];
        }

        return null;
    }
}
