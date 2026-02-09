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
 * Badges report filters defined.
 *
 * @package    dashaddon_badges
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_badges\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Badge filters preference options.
 */
class badge_origin_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $badges = (array) $DB->get_records_sql_menu(
            "SELECT Distinct(c.id), c.fullname FROM {badge} b LEFT JOIN {course} c ON c.id = b.courseid"
        );
        $badges = [0 => 'sitebadges'] + array_filter($badges);
        $this->add_options($badges);
        parent::init();
    }

    /**
     * Set the site badges filter condition filter contains the site badges in user preference.
     *
     * @return array
     */
    public function get_sql_and_params() {
        [$sql, $params] = parent::get_sql_and_params();
        $values = $this->get_values();
        if (array_search('0', $values) !== false) {
            $sql = ' bd.type = 1 OR ' . $sql;
        }
        return [$sql, $params];
    }
}
