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
 * Filters results to remove the hidden courses from the users.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;
/**
 * Allow users to view the hidden courses.
 *
 * @package local_dash
 */
class show_hidden_courses_condition extends condition {
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

        return get_string('showhiddencourses', 'block_dash');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function get_sql_and_params() {
        global $DB, $USER;

        // Enabled to condition to view the hidden courses.
        if (isset($this->get_preferences()['enabled']) && $this->get_preferences()['enabled']) {
            return false;
        }

        $select = $this->get_select();

        // Generate like condition for capability checks.
        $capabilitylike = $DB->sql_like('capability', ':capability');

        // Build the query to check the course is visible or the current user has capability to view the course.
        $sql = " (c.visible = 1 OR $select IN (
            SELECT ctx.instanceid FROM {context} ctx
            JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
            WHERE ra.userid = :userid AND ra.roleid IN (
                SELECT roleid FROM {role_capabilities} WHERE {$capabilitylike}
            ))
        )";

        // Parameters for the conditions query.
        $params = ['userid' => $USER->id, 'capability' => 'moodle/course:viewhiddencourses'];

        return [$sql, $params];
    }
}
