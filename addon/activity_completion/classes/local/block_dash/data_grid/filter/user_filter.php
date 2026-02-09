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
 * Users based filter option.
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter;

/**
 * Users based filter option.
 */
class user_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        $data = $DB->get_records_sql_menu("SELECT DISTINCT id, username
                                        FROM {user} WHERE deleted != 1 AND suspended != 1");

        $this->add_options($data);

        parent::init();
    }

    /**
     * Get filter config label.
     *
     * @return string
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('user');
    }
}
