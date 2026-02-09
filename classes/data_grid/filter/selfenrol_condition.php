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
 * Limit the courses based on the enrollment options course has.
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
 * Filters results to specific course categories.
 *
 * @package local_dash
 */
class selfenrol_condition extends condition {
    /**
     * Operation in where clause of the condition.
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
     * @throws \coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('enrollment_options', 'block_dash');
    }

    /**
     * Get values of available self enrolment courses.
     *
     * @return void
     */
    public function get_value() {
        global $USER;

        if (isset($this->get_preferences()['enrollmentoptions']) && is_array($this->get_preferences()['enrollmentoptions'])) {
            $enrolcourses = [];
            $enrollmentoptions = $this->get_preferences()['enrollmentoptions'];
            $courses = (class_exists('\core_course_category'))
            ? \core_course_category::top()->get_courses(['recursive' => true])
            : \coursecat::get(0)->get_courses(['recursive' => true]);
            // Remove enrolled courses from list.
            $enrolledcourses = enrol_get_users_courses($USER->id, true);

            foreach ($courses as $courseid => $course) {
                if (in_array($courseid, array_keys($enrolledcourses))) {
                    continue;
                }
                $enrolinstances = enrol_get_instances($courseid, true);
                $customchecks = ['self', 'autoenrol', 'credit', 'fee'];
                foreach ($enrolinstances as $instance) {
                    if (in_array($instance->enrol, $enrollmentoptions)) {
                        if (\local_dash\data_grid\field\attribute\enrollment_options_attribute::is_self_enrollment($instance)) {
                            $enrolcourses[] = $courseid;
                        }
                    }
                }
            }
            return $enrolcourses;
        }
        return false; // Not configured fully.
    }

    /**
     * Get sql to limit the courses based on the enrollment options.
     *
     * @return array
     */
    public function get_sql_and_params() {
        global $DB;

        $values = $this->get_value();
        if ($values !== false) {
            [$insql, $inparams] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED, 'eo', true, true);
            $sql = " c.id $insql ";
            return [$sql, $inparams];
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
        $plugins = enrol_get_plugins(true);
        $list = [];
        $selfenrolments = ['self', 'credit', 'autoenrol', 'guest', 'fee'];
        foreach ($plugins as $name => $plugin) {
            if (in_array($name, $selfenrolments)) {
                $list[$name] = get_string('pluginname', 'enrol_' . $name);
            }
        }

        $select = $mform->addElement(
            'autocomplete',
            $fieldname . '[enrollmentoptions]',
            get_string('enrollmentoptions', 'block_dash'),
            $list,
            ['multiple' => true]
        );
        $mform->hideIf($fieldname . '[enrollmentoptions]', $fieldname . '[enabled]');
    }
}
