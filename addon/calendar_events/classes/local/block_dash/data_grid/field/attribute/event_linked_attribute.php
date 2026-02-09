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
 * Event linked attribute - Dash attribute to link the data of calendar events in the view.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use stdClass;

/**
 * Calendar events linked data attribute for dash.
 */
class event_linked_attribute extends linked_data_attribute {
    /**
     * Transform the label in data to linked element using the URL from options add to this attribute.
     * Label field may contains data in any format like image tag or plain strings.
     *
     * @param array $data [url => Link URL , label => Data/Image to link]
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {

        // Get the url from options.
        $url = $this->get_option('url');
        if (empty($url)) {
            // URL not defined via options, verify any url available in the data.
            $url = $data['url'];
        }

        if ($url) {
            $url = clone $url; // Deep clone the url.
            foreach ($url->params() as $key => $value) {
                if (isset($record->$value)) {
                    $url->param($key, $record->$value);
                }
            }
            $data = \html_writer::link($url, $data['label'] ?? '');

            return $data;
        }

        return '';
    }
}
