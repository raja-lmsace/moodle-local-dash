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
 * Class course_category_table.
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use lang_string;
use local_dash\data_grid\field\attribute\activity_progress_attribute;
use local_dash\data_grid\field\attribute\course_format_attribute;
use local_dash\data_grid\field\attribute\course_image_url_attribute;
use local_dash\data_grid\field\attribute\course_summary_attribute;
use local_dash\data_grid\field\attribute\customfield_select_attribute;
use local_dash\data_grid\field\attribute\tags_attribute;
use moodle_url;
/**
 * Class course_category_table.
 *
 * @package local_dash
 */
class course_category_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('course_categories', 'cc');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_cc', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('category'), $this, null, [
                new identifier_attribute()
            ]),
            new field('name', new lang_string('categoryname'), $this),
        ];
    }
}
