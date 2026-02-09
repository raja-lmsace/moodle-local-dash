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
 * Class dashboard_table.
 *
 * @package    dashaddon_dashboard
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\context_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use lang_string;
use dashaddon_dashboard\local\block_dash\data_grid\field\attribute\dash_dashboardlink_attribute;
use dashaddon_dashboard\local\block_dash\data_grid\field\attribute\dashboard_dash_icon_attribute;
use dashaddon_dashboard\local\block_dash\data_grid\field\attribute\dashboard_dash_thumbnail_image_attribute;
use dashaddon_dashboard\local\block_dash\data_grid\field\attribute\dashboard_dash_background_image_attribute;
use dashaddon_dashboard\local\block_dash\data_grid\field\attribute\dashboard_dash_description;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use moodle_url;

/**
 * Class dashboard_table.
 *
 * @package dashaddon_dashboard
 */
class dashboard_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('dashaddon_dashboard_dash', 'dd');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_dd', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('dashboard', 'block_dash'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('contextid', new lang_string('contextid', 'block_dash'), $this, null, [
                new context_attribute(),
            ]),
            new field('name', new lang_string('name'), $this),
            new field('description', new lang_string('description'), $this, 'dd.description', [
                new dashboard_dash_description(),
            ]),
            new field('link', new lang_string('dashboardlink', 'block_dash'), $this, 'dd.id', [
                new dash_dashboardlink_attribute(),
                new link_attribute(['label_field' => 'dd_name']),
            ]),
            new field('dashicon', new lang_string('dashicon', 'block_dash'), $this, 'dd.dashicon', [
                new dashboard_dash_icon_attribute(),
            ]),
            new field('dashthumbnailimg', new lang_string('dashthumbnailimg', 'block_dash'), $this, 'dd.id', [
                new dashboard_dash_thumbnail_image_attribute(),
                new image_attribute(),
                new linked_data_attribute([
                    'url' => new moodle_url(
                        '/local/dash/addon/dashboard/dashboard.php',
                        ['id' => 'dd_id']
                    ),
                ]),
            ]),
            new field('dashthumbnailimgurl', new lang_string('dashthumbnailimgurl', 'block_dash'), $this, 'dd.id', [
                new dashboard_dash_thumbnail_image_attribute(), new image_url_attribute(),
            ]),
            new field('dashbgimage', new lang_string('backgroundimage', 'block_dash'), $this, 'dd.id', [
                new dashboard_dash_background_image_attribute(), new image_attribute(),
            ]),
            new field('dashbgimageurl', new lang_string('backgroundimageurl', 'block_dash'), $this, 'dd.id', [
                new dashboard_dash_background_image_attribute(), new image_url_attribute(),
            ]),
        ];
    }
}
