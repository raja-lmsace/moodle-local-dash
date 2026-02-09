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
 * Dashaddon developer test data generator.
 *
 * @package   dashaddon_developer
 * @copyright 2025, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Dashaddon developer data generator.
 */
class dashaddon_developer_generator extends component_generator_base {
    /**
     * Create a custom data source record.
     *
     * @param array $data
     * @return int The new record ID.
     */
    public function create_custom_data_source($data) {
        global $DB;

        $time = time();
        $defaults = [
            'enablejoins' => 0,
            'enableconditions' => 0,
            'joinrepeats' => 0,
            'fieldrepeats' => 0,
            'conditionrepeats' => 0,
            'tablejoins' => '[]',
            'tablejoinsalias' => '[]',
            'tablejoinon' => '[]',
            'placeholderfields' => '[]',
            'selectfield' => '[]',
            'fieldattribute' => '[]',
            'attributevalue' => '[]',
            'conditionfield' => '',
            'operator' => '',
            'operatorcondition' => '',
            'conditionvalue' => '',
            'customcondition' => '',
            'timecreated' => $time,
            'timemodified' => $time,
            'usermodified' => 2,
        ];

        $record = array_merge($defaults, $data);
        return $DB->insert_record('dashaddon_developer_source', (object) $record);
    }
}
