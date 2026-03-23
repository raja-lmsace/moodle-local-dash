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
 * Filters results to specific course completion status
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filters results to specific course completion status
 */
class role_name_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;
        $choices = [];
        $records = $DB->get_records_select_menu('role_assignments', null, null, '', 'id,roleid');
        foreach ($records as $id => $roleid) {
            $role = $DB->get_record('role', ['id' => $roleid]);
            $contextid = $DB->get_field('role_assignments', 'contextid', ['id' => $id]);
            $context = \context::instance_by_id($contextid, IGNORE_MISSING);
            if ($context && $DB->record_exists('role_names', ['roleid' => $roleid, 'contextid' => $context->id])) {
                $this->add_option($roleid, $DB->get_field(
                    'role_names',
                    'name',
                    ['roleid' => $roleid, 'contextid' => $context->id]
                ));
            } else {
                $this->add_option($roleid, role_get_name($role, $context));
            }
        }
        parent::init();
    }

    /**
     * Get filter option label.
     *
     * @return string
     */
    public function get_label() {
        return get_string('rolename', 'block_dash');
    }
}
