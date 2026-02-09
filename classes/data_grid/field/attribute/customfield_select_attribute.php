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
 * Gerenerate custom field select option.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use customfield_select\field_controller;

/**
 * Transforms value from customfield select.
 *
 * @package local_dash
 */
class customfield_select_attribute extends abstract_field_attribute {
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
        global $DB;
        /** @var field_controller $field */
        $field = $this->get_option('field');

        if ($field instanceof \stdClass) {
            if ($DB->get_manager()->table_exists('local_metadata_field')) {
                $metafield = $DB->get_record("local_metadata_field", ['id' => $field->id]);
                $options = explode("\n", $metafield->param1);
            }
        } else if (method_exists($field, 'get_options')) {
            // Moodle 3.10 and up.
            $options = $field->get_options();
        } else {
            // Moodle 3.9 and earlier.
            $options = field_controller::get_options_array($field);
        }

        if (isset($options[$data])) {
            $data = $options[$data];
        }

        return $data;
    }
}
