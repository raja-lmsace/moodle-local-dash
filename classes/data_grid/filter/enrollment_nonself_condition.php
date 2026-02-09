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
 * Limit dashboards to non-public (logged in dashboards only).
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use block_dash\local\data_grid\filter\filter;

/**
 * Limit dashboards to non-public (logged in dashboards only).
 *
 * @package local_dash
 */
class enrollment_nonself_condition extends enrollment_self_condition {
    /**
     * filter constructor.
     * @param string $name
     * @param string $select
     * @param string $label
     * @param string $clausetype
     */
    public function __construct($name, $select, $label = '', $clausetype = self::CLAUSE_TYPE_WHERE) {
        $this->set_operation(self::OPERATION_NOT_EQUAL);
        parent::__construct($name, $select, $label, $clausetype);
    }

    /**
     * Get the enrolment field filter label.
     * @return string
     */
    public function get_label() {
        return get_string('enrollmentsnotself', 'block_dash');
    }
}
