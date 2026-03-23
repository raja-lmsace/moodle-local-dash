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
 * Badges table.
 *
 * @package    dashaddon_badges
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_badges\local\dash_framework\structure;

use moodle_url;
use lang_string;
use core_badges\form\badge;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use dashaddon_badges\local\block_dash\data_grid\field\attribute\badge_origin_attribute;
use dashaddon_badges\local\block_dash\data_grid\field\attribute\badge_image_url_attribute;
use block_dash\local\dash_framework\structure\field_interface;

/**
 * Badges table structure definitions for badge datasource.
 */
class badge_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('badge', 'bd');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_bd', 'dashaddon_badges');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        $fields = [
            new field('id', new \lang_string('badge', 'dashaddon_badges'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('name', new \lang_string('badge', 'dashaddon_badges'), $this),

            new field('dateissued', new \lang_string('dateissued', 'dashaddon_badges'), $this, [
                'select' => '(SELECT bi.dateissued FROM {badge_issued} bi where bi.badgeid = bd.id AND bi.userid = u.id)',
            ], [
                new date_attribute(),
            ], [], field_interface::VISIBILITY_VISIBLE, ''),

            new field('image', new lang_string('badgeimage', 'dashaddon_badges'), $this, 'bd.id', [
                new badge_image_url_attribute(), new image_attribute(),
            ]),

            new field('imageurl', new lang_string('badgeimageurl', 'dashaddon_badges'), $this, 'bd.id', [
                new badge_image_url_attribute(), new image_url_attribute(),
            ]),

            new field('badgeurl', new lang_string('badgeurl', 'dashaddon_badges'), $this, 'bd.id', [
                new moodle_url_attribute(['url' => new moodle_url('/badges/overview.php', ['id' => 'bd_id']) ]),
            ]),

            new field('badgebutton', new lang_string('badgebutton', 'dashaddon_badges'), $this, 'bd.id', [
                new moodle_url_attribute(['url' => new moodle_url('/badges/overview.php', ['id' => 'bd_id']) ]),
                new button_attribute(['label' => new lang_string('viewbadge', 'dashaddon_badges')]),
            ]),

            new field('image_link', new lang_string('badgeimagelink', 'dashaddon_badges'), $this, 'bd.id', [
                new badge_image_url_attribute(), new image_attribute(),
                new linked_data_attribute(['url' => new moodle_url('/badges/overview.php', ['id' => 'bd_id'])]),
            ]),

            new field('origin', new lang_string('origin', 'dashaddon_badges'), $this, 'coalesce(bd.courseid, 1)', [
                new badge_origin_attribute(),
            ]),
        ];
        return $fields;
    }
}
