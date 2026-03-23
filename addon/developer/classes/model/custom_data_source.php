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
 * Represents a user created data source.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\model;

use stdClass;
use core\persistent;
use block_dash\local\data_grid\field\field_definition_factory;
use dashaddon_developer\data_grid\field\custom_sql_field_definition;

/**
 * Represents a user created data source.
 *
 * @package dashaddon_developer
 */
class custom_data_source extends persistent {
    /**
     * Source table for the developer addon.
     */
    const TABLE = 'dashaddon_developer_source';

    /**
     * Define the fields used in the persistent form of the custom datasource.
     *
     * @return array
     */
    protected static function define_properties() {

        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'idnumber' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
            'maintable' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
            'fieldrepeats' => [
                'type' => PARAM_INT,
            ],
            'selectfield' => [
                'type' => PARAM_RAW,
            ],
            'fieldattribute' => [
                'type' => PARAM_RAW,
            ],
            'attributevalue' => [
                'type' => PARAM_RAW,
            ],
            'conditionrepeats' => [
                'type' => PARAM_INT,
            ],
            'conditionfield' => [
                'type' => PARAM_RAW_TRIMMED,
            ],
            'operator' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'operatorcondition' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'conditionvalue' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'customcondition' => [
                'type' => PARAM_RAW,
            ],
            'tablejoins' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'tablejoinon' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'joinrepeats' => [
                'type' => PARAM_INT,
            ],
            'placeholderfields' => [
                'type' => PARAM_RAW,
            ],
            'enablejoins' => [
                'type' => PARAM_BOOL,
            ],
            'tablejoinsalias' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'enableconditions' => [
                'type' => PARAM_BOOL,
            ],

        ];
    }

    /**
     * Json fields.
     *
     * @return array
     */
    public static function get_json_fields() {
        // Fields to encode, array fields.
        $jsonfields = [
            'selectfield', 'fieldattribute', 'conditionfield', 'operator', 'operatorcondition', 'conditionvalue',
            'attributevalue', 'tablejoins', 'tablejoinon', 'placeholderfields', 'tablejoinsalias',
        ];

        return $jsonfields;
    }

    /**
     * Update properties format
     *
     * @param mixed $data
     * @return void
     */
    public function update_properties_format(&$data) {
        // Fields to encode, array fields.
        $jsonfields = self::get_json_fields();

        foreach ($jsonfields as $property) {
            $value = $data->$property;
            if (is_null($value)) {
                continue;
            }
            $data->$property = in_array($property, ['fieldattribute', 'attributevalue']) ?
                $this->revise_fieldattr($value) : json_decode($value);
        }
    }

    /**
     * Make the field attribute and attribute value in array format for the loop in field definition.
     *
     * @param string $value
     * @return array
     */
    public function revise_fieldattr($value) {

        $value = json_decode($value) ?: [];
        array_walk($value, function (&$item) {
            if (!is_array($item)) {
                $item = [$item];
            }

            $item = array_filter($item);
        });

        return $value;
    }

    /**
     * Update the data format.
     *
     * @return void
     */
    public function update_data_format() {
        // Fields to encode, array fields.
        $jsonfields = self::get_json_fields();

        foreach ($jsonfields as $property) {
            $value = $this->raw_get($property);

            if (is_null($value)) {
                continue;
            }
            $this->raw_set($property, json_decode($value));
        }
    }

    /**
     * Encode the array fields to json before insert into DB.
     *
     * @return void
     */
    protected function before_validate() {
        // Fields to encode, array fields.
        $jsonfields = self::get_json_fields();

        foreach ($jsonfields as $property) {
            $value = $this->raw_get($property);
            // Not array then no need to encode.
            if (!is_array($value)) {
                continue;
            }
            $this->raw_set($property, json_encode($value));
        }
    }

    /**
     * Validate the idnumber
     *
     * @param int $value The value.
     * @return true|\lang_string
     * @throws \coding_exception
     */
    protected function validate_idnumber($value) {
        if (self::record_exists_select('idnumber = ? AND id != ?', [$value, $this->get('id')])) {
            return new \lang_string('invalididnumberunique', 'block_dash');
        }

        return true;
    }

    /**
     * Returns example query to demonstrate all nuances with dash queries.
     *
     * @return string
     */
    public static function get_example_query() {
        return "SELECT %%SELECT%% FROM {course} c\n" .
               "JOIN {course_categories} cc ON cc.id = c.category\n" .
               "%%WHERE%% %%GROUPBY%% %%ORDERBY%%";
    }

    /**
     * Find and return the list alias keyword for the main table and joined tables.
     *
     * @return array
     */
    public function get_table_alias() {
        // Table joins.
        $joins = $this->get('tablejoins');
        $joins = $joins ? json_decode($joins) : [];
        // Table joins alias for ON.
        $joinalias = $this->get('tablejoinsalias');
        $joinalias = $joinalias ? json_decode($joinalias) : [];
        // Join the table to the builder.
        if ($joins) {
            foreach ($joins as $key => $join) {
                if (empty($join) || empty($joinalias[$key])) {
                    continue;
                }
                $alias = $joinalias[$key]; // Table alias.
                $tablesalias[$join] = $alias; // Alias for the tables.
            }
        }

        return $tablesalias ?? [];
    }

    /**
     * Update the placeholders tables with its alias.
     *
     * @param string $field
     * @return string
     */
    public function update_field_alias($field) {

        $tablesalias = $this->get_table_alias();

        $expfieldtable = explode('.', $field);
        // This field doesn't contains any table as alias, this is raise the ambious error.
        if (!isset($expfieldtable[1])) {
            return;
        }

        // Name of the field used in the DB table strucutes. without any alias.
        $fieldtable = $expfieldtable[0]; // First element is the table.

        if (isset($tablesalias[$fieldtable])) {
            // Replace the table name with its alias.
            $field = $tablesalias[$fieldtable] . '.' . $expfieldtable[1];
        }

        return $field;
    }

    /**
     * Get the placeholders for the fields.
     *
     * @return array
     */
    public function get_placeholders(): array {

        // Include the placeholder fields in selection query.
        $placeholders = $this->get('placeholderfields');
        $placeholders = $placeholders ? json_decode($placeholders) : [];

        if ($placeholders) {
            foreach ($placeholders as $key => $field) {
                $updatedlist[] = $this->update_field_alias($field);
            }
        }

        return $updatedlist ?? [];
    }
}
