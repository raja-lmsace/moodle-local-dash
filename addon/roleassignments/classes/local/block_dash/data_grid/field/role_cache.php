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
 * Shared per-request cache of role records.
 *
 * @package    dashaddon_roleassignments
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_roleassignments\local\block_dash\data_grid\field\attribute;

/**
 * Provides a shared, lazily-loaded cache of all role records.
 *
 * Field attributes run transform_data() once per rendered/exported row. A Moodle
 * site only has a handful of roles, so loading them all once per request and
 * reusing the cached objects avoids an N+1 get_record('role') query per row.
 *
 * @package dashaddon_roleassignments
 */
trait role_cache {
    /**
     * Return a cached role record by id, loading all roles once per request.
     *
     * @param  int $roleid
     * @return \stdClass|null The role record, or null if it does not exist.
     */
    protected static function get_role(int $roleid): ?\stdClass {
        static $roles = null;
        if ($roles === null) {
            global $DB;
            $roles = $DB->get_records('role'); // One query for the whole request.
        }
        return $roles[$roleid] ?? null;
    }
}
