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
 * List the cohorts assigned to the programs
 *
 * @package    dashaddon_programs
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use enrol_programs\local\management;
use stdClass;

/**
 * List the cohorts assigned to the programs
 */
class program_cohort_attribute extends abstract_field_attribute {
    /**
     * List the cohorts assigned to the programs
     *
     * @param int $data
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {
        global $CFG;

         $cohorts = management::fetch_current_cohorts_menu($data);
        if ($cohorts) {
            $result = implode(', ', array_map('ucwords', array_map('format_string', $cohorts)));
        } else {
            $result = '-';
        }

        return $result;
    }
}
