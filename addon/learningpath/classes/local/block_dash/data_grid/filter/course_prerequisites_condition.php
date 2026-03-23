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
 * Filters results to current category only.
 *
 * @package    dashaddon_learningpath
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace dashaddon_learningpath\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Filter to include only courses that are prerequisites of a selected course.
 *
 * @package    dashaddon_learningpath
 */
class course_prerequisites_condition extends condition {
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
        if ($label = parent::get_label()) {
            return $label;
        }
        return get_string('courseprerequisites', 'block_dash');
    }

    /**
     * Get prerequisite course IDs based on user selection.
     *
     * @return array
     * @throws dml_exception
     */
    public function get_values() {
        global $DB;

        $prerequisitecourseids = [];

        if (
            isset($this->get_preferences()['prerequisitecourses']) &&
            is_array($this->get_preferences()['prerequisitecourses'])
        ) {
            $selectedcourseids = $this->get_preferences()['prerequisitecourses'];

            if (!empty($selectedcourseids)) {
                // Prepare SQL IN clause.
                [$insql, $inparams] = $DB->get_in_or_equal($selectedcourseids, SQL_PARAMS_NAMED, 'crs_');

                // Get all prerequisites for the selected courses.
                $records = $DB->get_records_select(
                    'course_completion_criteria',
                    "criteriatype = :ctype AND course $insql",
                    array_merge(['ctype' => COMPLETION_CRITERIA_TYPE_COURSE], $inparams)
                );

                foreach ($records as $record) {
                    // Actual prerequisite course.
                    $prerequisitecourseids[] = $record->courseinstance;
                }
            }
        }

        return array_unique($prerequisitecourseids);
    }

    /**
     * Add form fields for this filter.
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

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat);

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        // Get all courses for dropdown.
        $courses = $DB->get_records('course', null, 'fullname', 'id, fullname');
        $courseoptions = [0 => get_string('none')];

        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        // Add the course dropdown.
        $select = $mform->addElement(
            'select',
            $fieldname . '[prerequisitecourses]',
            get_string('selectprerequisitecourses', 'block_dash'),
            $courseoptions,
            ['class' => 'select2-form']
        );

        // Help button.
        $mform->addHelpButton(
            $fieldname . '[prerequisitecourses]',
            'selectprerequisitecourses',
            'dashaddon_learningpath'
        );

        // Hide dropdown if plugin/feature is disabled.
        $mform->hideIf($fieldname . '[prerequisitecourses]', $fieldname . '[enabled]');

        // Allow multiple selections.
        $select->setMultiple(true);
    }

    /**
     * Return WHERE SQL and params for placeholders.
     *
     * @return array|false
     * @throws dml_exception
     */
    public function get_sql_and_params() {
        $prerequisitecourseids = $this->get_values();

        if (!empty($prerequisitecourseids)) {
            [$insql, $inparams] = $this->get_in_or_equal_sql_and_params($prerequisitecourseids);
            $sql = "c.id $insql";
            return [$sql, $inparams];
        }

        return false;
    }

    /**
     * Helper method for IN or EQUAL SQL conditions.
     *
     * @param array $values
     * @return array
     */
    private function get_in_or_equal_sql_and_params(array $values) {
        global $DB;
        return $DB->get_in_or_equal($values, SQL_PARAMS_NAMED, 'prereq_' . $this->get_name());
    }
}
