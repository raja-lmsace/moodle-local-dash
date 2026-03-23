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
 * Filters results to specific calendar events type (Site, course, category, group, user, other).
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Filters results to specific calendar events type (Site, course, category, group, user, other).
 */
class event_context_condition extends condition {
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
        return get_string('eventcontext', 'block_dash');
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

        // Always call parent.
        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat);

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'site' => get_string('event:typesite', 'block_dash'),
            'category' => get_string('event:typecategory', 'block_dash'),
            'course' => get_string('event:typecourse', 'block_dash'),
            'group' => get_string('event:typegroup', 'block_dash'),
            'user' => get_string('event:typeuser', 'block_dash'),
            'other' => get_string('event:typeother', 'block_dash'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[eventcontext]',
            get_string('eventcontext', 'block_dash'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[eventcontext]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {

        if (isset($this->get_preferences()['eventcontext']) && is_array($this->get_preferences()['eventcontext'])) {
            $types = $this->get_preferences()['eventcontext'];

            $sql = [];
            $params = [];

            foreach ($types as $key => $type) {
                switch ($type) {
                    case "site":
                        $sql[] = "(ce.eventtype = :cec_site$key) ";
                        $params += ['cec_site' . $key => 'site'];
                        break;
                    case "category":
                        $sql[] = "(ce.categoryid > :cec_cate$key)";
                        $params += ["cec_cate$key" => 0];
                        break;
                    case "group":
                        $sql[] = "(ce.groupid > :cec_group$key)";
                        $params += ['cec_group' . $key => 0];
                        break;
                    case "course":
                        $sql[] = "(ce.courseid > :cec_courseid$key AND ce.groupid <= 0)";
                        $params += ['cec_courseid' . $key => SITEID];
                        break;
                    case "user":
                        $sql[] = "(ce.eventtype = :cec_user$key) ";
                        $params += ['cec_user' . $key => 'user'];
                        break;
                    case "other":
                        $sql[] = "(
                            ce.eventtype <> :cec_user_$key
                            AND ce.eventtype <> :cec_site_$key
                            AND ce.eventtype <> :cec_course_$key
                            AND ce.categoryid = 0
                            AND ce.groupid = 0
                            AND NOT (ce.courseid > 1 AND (ce.modulename <> '' AND ce.instance > 0))
                        )";
                        $params += ['cec_user_' . $key => 'user', 'cec_site_' . $key => 'site', "cec_course_$key" => 'course'];
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
    }
}
