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
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filters results to specific course completion status
 */
class role_name_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     */
    public function init() {
        global $DB;

        // Only the roles actually used in assignments are worth listing. A site has
        // a handful of roles, so resolve their names from a single role lookup
        // instead of iterating every role assignment.
        $roleids = $DB->get_fieldset_sql('SELECT DISTINCT roleid FROM {role_assignments}');
        if ($roleids) {
            $systemcontext = \context_system::instance();
            $roles = $DB->get_records_list('role', 'id', $roleids);
            foreach ($roles as $role) {
                $this->add_option($role->id, role_get_name($role, $systemcontext));
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
