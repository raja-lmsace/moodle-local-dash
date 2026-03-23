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
 * Filters results to enrolled courses, optionally with specific role(s).
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
 * Filters results to enrolled courses, optionally with specific role(s).
 *
 * @package local_dash
 */
class my_enrolled_courses_condition extends condition {
    /**
     * Get filter SQL operation.
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
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('myenrolledcourses', 'block_dash');
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

        // Get the current userid, based on current page and datasource supports current page user.
        $userid = $this->get_userid();

        $sql = "$select IN(SELECT ctx.instanceid
                           FROM {role_assignments} ra
                           JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = " . CONTEXT_COURSE . "
                           WHERE ra.userid = :enrolleduserid";

        $params = ['enrolleduserid' => $userid];
        if (
            isset($this->get_preferences()['roleids'])
            && is_array($this->get_preferences()['roleids']) && count($this->get_preferences()['roleids']) > 0
        ) {
            [$rsql, $rparams] = $DB->get_in_or_equal($this->get_preferences()['roleids'], SQL_PARAMS_NAMED, 'roles');
            $sql .= " AND ra.roleid $rsql";
            $params = array_merge($params, $rparams);
        }

        $sql .= ')'; // Close subquery.

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

        $options = [];
        foreach (get_roles_for_contextlevels(CONTEXT_COURSE) as $roleid) {
            if ($role = $DB->get_record('role', ['id' => $roleid])) {
                $options[$roleid] = role_get_name($role);
            }
        }

        $select = $mform->addElement(
            'select',
            $fieldname . '[roleids]',
            get_string('withroles', 'block_dash'),
            $options,
            ['class' => 'select2-form']
        );

        $mform->hideIf($fieldname . '[roleids]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }
}
