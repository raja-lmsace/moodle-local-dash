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
 * Filters events to the current course and/or its mod_subcourse references,
 * with OR semantics inside a single condition (so the two scopes can combine
 * even though block_dash AND's separate conditions together).
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;

/**
 * Combined "current course + subcourses" condition with a multi-select scope picker.
 */
class current_course_with_subcourses_condition extends condition {
    /**
     * Use a custom SQL operation so we can OR multiple sub-clauses.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_CUSTOM;
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

        return get_string('currentcourse', 'block_dash');
    }

    /**
     * Settings form: when mod_subcourse is installed, expose a multi-select "Scope" picker
     * (Current course / Subcourses of current course). When it isn't installed, hide the
     * picker entirely — the condition then behaves as a plain "current course" filter.
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

        if (!$DB->get_manager()->table_exists('subcourse')) {
            return;
        }

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $choices = [
            'current' => get_string('scope:currentcourse', 'dashaddon_calendar_events'),
            'subcourses' => get_string('scope:subcourses', 'dashaddon_calendar_events'),
        ];

        $select = $mform->addElement(
            'select',
            $fieldname . '[scopes]',
            get_string('scope', 'dashaddon_calendar_events'),
            $choices,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[scopes]', $fieldname . '[enabled]');
        $select->setMultiple(true);
        if (!\array_key_exists('scopes', $this->get_preferences())) {
            $mform->setDefault("{$fieldname}[scopes]", ['current', 'subcourses']);
        }
    }

    /**
     * OR-combine the SQL fragments for each picked scope.
     *
     * @return array [sql, params]
     */
    public function get_sql_and_params() {
        global $DB;

        $select = $this->get_select();

        $coursecontext = $this->get_context()->get_course_context(false);
        if (!$coursecontext) {
            return ['1=0', []];
        }
        $courseid = $coursecontext->instanceid;

        // When mod_subcourse isn't installed the scope picker isn't rendered, so no "scopes"
        // preference is saved — fall back to current course only.
        $scopes = $this->get_preferences()['scopes'] ?? ['current'];
        if (!is_array($scopes)) {
            $scopes = [$scopes];
        }

        $clauses = [];
        $params = [];

        if (in_array('current', $scopes, true)) {
            $clauses[] = "$select = :ccwsc_currentid";
            $params['ccwsc_currentid'] = $courseid;
        }

        if (in_array('subcourses', $scopes, true) && $DB->get_manager()->table_exists('subcourse')) {
            $clauses[] = "$select IN (SELECT sc.refcourse
                                        FROM {subcourse} sc
                                        JOIN {modules} m ON m.name = 'subcourse'
                                        JOIN {course_modules} cm
                                          ON cm.module = m.id
                                         AND cm.instance = sc.id
                                       WHERE sc.course = :ccwsc_subcurid
                                         AND sc.refcourse > 0
                                         AND cm.visible = 1
                                         AND cm.deletioninprogress = 0)";
            $params['ccwsc_subcurid'] = $courseid;
        }

        if (empty($clauses)) {
            return ['1=0', []];
        }

        return ['(' . implode(' OR ', $clauses) . ')', $params];
    }
}
