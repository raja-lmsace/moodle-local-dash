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
 * Transforms data to badge type.
 * @package    dashaddon_badges
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_badges\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use lang_string;
use stdClass;

/**
 * Transforms data to badge type.
 */
class badge_origin_attribute extends abstract_field_attribute {
    /**
     * Convert the badge type value to human readable content.
     *
     * @param int $data
     * @param stdClass $record
     * @return string $data Type of the badge in text.
     */
    public function transform_data($data, stdClass $record) {
        if ($record->type == BADGE_TYPE_SITE) {
            $data = new lang_string('sitebadge', 'dashaddon_badges');
        } else if ($record->type == BADGE_TYPE_COURSE && $record->courseid != '') {
            $data = format_text(get_course($record->courseid)->fullname);
        }
        return $data;
    }
}
