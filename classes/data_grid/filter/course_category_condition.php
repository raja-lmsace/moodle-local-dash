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
 * Filters results to specific course categories.
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
 * Filters results to specific course categories.
 *
 * @package local_dash
 */
class course_category_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        // Use custom operation when multicategory plugin is available and we're in course context.
        if ($this->is_course_context() && class_exists('\customfield_multicategory\condition_helper')) {
            return self::OPERATION_CUSTOM;
        }
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Check if this condition is being used in a course context (not categories).
     *
     * @return bool True if filtering courses, false if filtering categories.
     */
    protected function is_course_context(): bool {
        $select = $this->get_select();
        return strpos($select, 'cc.id') === false;
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     */
    public function get_sql_and_params() {
        global $DB;

        $categoryids = $this->get_values();
        if (empty($categoryids)) {
            return ['', []];
        }

        $select = $this->get_select();
        $name = $this->get_name();

        // Get the IN clause for main category.
        [$insql, $params] = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, $name . '_cat');
        $basesql = "$select $insql";

        // Only extend with multicategory logic in course context.
        if ($this->is_course_context() && class_exists('\customfield_multicategory\condition_helper')) {
            $result = \customfield_multicategory\condition_helper::extend_category_sql(
                $basesql,
                $params,
                $categoryids,
                $name . '_mcat'
            );
            return [$result['sql'], $result['params']];
        }

        return [$basesql, $params];
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

        return get_string('coursecategories', 'block_dash');
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

        $categoryids = [];
        if (isset($this->get_preferences()['coursecategories']) && is_array($this->get_preferences()['coursecategories'])) {
            $rootcategoryids = $this->get_preferences()['coursecategories'];
            if (is_array($rootcategoryids)) {
                foreach ($rootcategoryids as $categoryid) {
                    $categoryids[] = $categoryid;

                    if (
                        isset($this->get_preferences()['includesubcategories'])
                        && $this->get_preferences()['includesubcategories']
                    ) {
                        if (class_exists("\core_course_category")) {
                            if ($coursecat = \core_course_category::get($categoryid, IGNORE_MISSING)) {
                                $categoryids = array_merge($categoryids, $coursecat->get_all_children_ids());
                            }
                        } else {
                            // Moodle 3.5 compatibility.
                            require_once("$CFG->dirroot/lib/coursecatlib.php");
                            if ($coursecat = \coursecat::get($categoryid, IGNORE_MISSING)) {
                                foreach ($coursecat->get_children() as $category) {
                                    $categoryids[] = $category->id;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $categoryids;
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

        $options = [];
        foreach (get_roles_for_contextlevels(CONTEXT_COURSE) as $roleid) {
            if ($role = $DB->get_record('role', ['id' => $roleid])) {
                $options[$roleid] = role_get_name($role);
            }
        }

        if (class_exists("\core_course_category")) {
            $categories = \core_course_category::make_categories_list('moodle/course:create');
        } else {
            // Moodle 3.5 compatibility.
            require_once("$CFG->dirroot/lib/coursecatlib.php");
            $categories = \coursecat::make_categories_list('moodle/course:create');
        }

        $mform->addElement('advcheckbox', $fieldname . '[includesubcategories]', get_string('includesubcategories', 'block_dash'));
        $mform->setType($fieldname . '[includesubcategories]', PARAM_BOOL);
        $mform->addHelpButton($fieldname . '[includesubcategories]', 'includesubcategories', 'block_dash');
        $mform->hideIf($fieldname . '[includesubcategories]', $fieldname . '[enabled]');

        $select = $mform->addElement('select', $fieldname . '[coursecategories]', '', $categories, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[coursecategories]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }
}
