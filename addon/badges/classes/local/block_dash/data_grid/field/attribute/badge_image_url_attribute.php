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
 * Transforms data to badge image url.
 *
 * @package    dashaddon_badges
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_badges\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use core_badges\badge;
use stdClass;
use moodle_url;

/**
 * Transforms data to badge image url.
 */
class badge_image_url_attribute extends abstract_field_attribute {
    /**
     * Generate the badge image url based on the badge id.
     *
     * @param int $data
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {
        $badge = new badge($data);
        $context = $badge->get_context();
        $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
        return $imageurl;
    }
}
