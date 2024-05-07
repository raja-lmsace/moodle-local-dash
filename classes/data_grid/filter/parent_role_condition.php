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
 * Parent role condition.
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use block_dash\local\data_grid\filter\filter;
use coding_exception;

/**
 * Parent role condition.
 */
class parent_role_condition extends condition {

    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_CUSTOM;
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

        return [$USER->id];
    }

    /**
     * Get condition label.
     * @return string
     */
    public function get_label() {
        return get_string('parentrole', 'block_dash');
    }

    /**
     * Get help text for this filter to help configuration.
     *
     * Return array[string_identifier, component], similar to the $mform->addHelpButton() call.
     *
     * @return array<string, string>
     */
    public function get_help() {
        return ['parentrole', 'block_dash'];
    }

    /**
     * Return custom operation SQL.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_custom_operation(): string {
        $placeholder = $this->get_name();
        $select = $this->get_select();

        return "$select IN(SELECT ctx.instanceid
                           FROM {role_assignments} ra
                           JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 30
                           WHERE ra.userid = :$placeholder)";
    }
}
