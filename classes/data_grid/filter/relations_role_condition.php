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
 * Relation condition.
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Parent role condition.
 */
class relations_role_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        global $USER;

        $roleids = [];
        if (isset($this->get_preferences()['roleids']) && is_array($this->get_preferences()['roleids'])) {
            $roleids = $this->get_preferences()['roleids'];
            if (is_array($roleids)) {
                foreach ($roleids as $roleid) {
                    $roleids[] = $roleid;
                }
            }
        }
        return $roleids;
    }

    /**
     * Get condition label.
     * @return string
     */
    public function get_label() {
        return get_string('relations', 'block_dash');
    }

    /**
     * Get help text for this filter to help configuration.
     *
     * Return array[string_identifier, component], similar to the $mform->addHelpButton() call.
     *
     * @return array<string, string>
     */
    public function get_help() {
        return ['relations', 'block_dash'];
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function get_sql_and_params() {
        global $DB, $USER;

        $select = $this->get_select();
        $roleids = $this->get_values();
        $params = ['userid' => $USER->id];

        if ($roleids) {
            [$relationsql, $relationsparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
            $params = array_merge($params, $relationsparams);
            $rolecondition = "AND ra.roleid $relationsql";
        } else {
            $rolecondition = '';
        }

        $sql = "$select IN (
                    SELECT ctx.instanceid
                    FROM {role_assignments} ra
                    JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 30
                    WHERE ra.userid = :userid $rolecondition
                )";

        return [$sql, $params];
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
        $roles = [];
        foreach (get_roles_for_contextlevels(CONTEXT_USER) as $roleid) {
            if ($role = $DB->get_record('role', ['id' => $roleid])) {
                $roles[$roleid] = role_get_name($role);
            }
        }

        $select = $mform->addElement(
            'select',
            $fieldname . '[roleids]',
            get_string('withroles', 'block_dash'),
            $roles,
            ['class' => 'select2-form']
        );
        $mform->hideIf($fieldname . '[roleids]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }
}
