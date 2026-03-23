<?php
// This file is part of The Bootstrap Moodle theme
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
 * Course custom fields based filter to filter the records.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Course custom fields based filter to filter the records.
 */
class customfield_filter extends select_filter {
    /**
     * @var string Record ID of custom profile field.
     */
    private $field;

    /**
     * Contructor
     * @param string $name
     * @param string $select
     * @param \core_customfield\field_controller|\stdClass $field
     * @param string $label
     */
    public function __construct($name, $select, $field, $label = '') {
        $this->field = $field;

        parent::__construct($name, $select, $label);
    }

    /**
     * Return a list of operations this filter can handle.
     *
     * @return array
     */
    public function get_supported_operations() {
        return [
            self::OPERATION_EQUAL,
            self::OPERATION_IN_OR_EQUAL,
        ];
    }

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        if ($this->field instanceof \stdClass && dashaddon_activities_is_local_metadata_installed()) {
            $params['fieldid'] = $this->field->id;
            $metafield = $DB->get_record('local_metadata_field', ['id' => $this->field->id]);
            $options = $DB->get_records_sql_menu("SELECT cd.data AS key1, cd.data AS key2 FROM {local_metadata} cd
                                              WHERE cd.fieldid = :fieldid
                                              GROUP BY cd.data", $params);
            if ($metafield->datatype == 'menu') {
                $selectoptions = explode("\n", $metafield->param1);
                foreach ($options as $key => $option) {
                    if (in_array($option, $selectoptions)) {
                        $this->add_option($key, format_string($option));
                    }
                }
            } else {
                foreach ($options as $key => $option) {
                    $this->add_option($key, $option);
                }
            }
        } else if (class_exists('\core_course\customfield\course_handler')) {
            $params['fieldid'] = $this->field->get('id');

            $options = $DB->get_records_sql_menu("SELECT cd.value AS key1, cd.value AS key2 FROM {customfield_data} cd
                                              WHERE cd.fieldid = :fieldid
                                              GROUP BY cd.value", $params);
            if ($this->field instanceof \customfield_select\field_controller) {
                if (method_exists($this->field, 'get_options')) {
                    // Moodle 3.10 and up.
                    $selectoptions = $this->field->get_options();
                } else {
                    // Moodle 3.9 and earlier.
                    $selectoptions = \customfield_select\field_controller::get_options_array($this->field);
                }
                foreach ($options as $key => $option) {
                    if (isset($selectoptions[$option])) {
                        $this->add_option($key, format_string($selectoptions[$option]));
                    }
                }
            } else {
                foreach ($options as $key => $option) {
                    $this->add_option($key, format_string($option));
                }
            }
        }

        parent::init();
    }
}
