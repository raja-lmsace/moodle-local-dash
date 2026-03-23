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
 * Transform dash dashboard data into dash icon.
 *
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use moodle_url;
use html_writer;
use cm_info;

/**
 * Transforms dash dashboard data to formatted dash icon.
 *
 * @package dashaddon_dashboard
 */
class dashboard_dash_icon_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB, $OUTPUT;
        if ($record->dd_dashicon != null) {
            $icon = explode(':', $record->dd_dashicon);
            $iconstr = isset($icon[1]) ? $icon[1] : 'moodle';
            $component = isset($icon[0]) ? $icon[0] : '';

            // Render the pix icon.
            return $OUTPUT->pix_icon($iconstr, '', $component);
        } else {
            return '';
        }
    }
}
