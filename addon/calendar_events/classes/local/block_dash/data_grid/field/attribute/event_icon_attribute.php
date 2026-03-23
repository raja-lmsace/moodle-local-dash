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
 * Event icon attribute - Dash attribute to convert the data of calendar events into icon/image.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\image_attribute;
use stdClass;

/**
 * List the cohorts assigned to the programs
 */
class event_icon_attribute extends image_attribute {
    /**
     * List the cohorts assigned to the programs
     *
     * @param array $data
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {
        $this->set_options($data);
        return parent::transform_data($data['customurl'], $record);
    }
}
