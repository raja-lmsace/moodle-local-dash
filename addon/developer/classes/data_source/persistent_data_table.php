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
 * Class persistent_data_table.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\data_source;

use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use dashaddon_developer\data_source\table\custom_data_source_table;
use lang_string;
use moodle_url;
use dashaddon_developer\model\custom_data_source as custom_data_source_modal;
use local_dash\plugininfo\dashaddon;
use moodle_exception;

/**
 * Class persistent_data_table.
 *
 * @package dashaddon_developer
 */
class persistent_data_table extends table {
    /**
     * Persistend modal to fetch the configured data of the datasoure tables and fields.
     *
     * @var core\persistent
     */
    private $modal;

    /**
     * Title of this datasoruce configured.
     *
     * @var string
     */
    private $title;

    /**
     * Build a new table.
     *
     * @param custom_data_source_modal $persistentmodal Persistend modal to fetch the configured datasoure tables and fields.
     */
    public function __construct(custom_data_source_modal $persistentmodal) {

        $this->modal = $persistentmodal;

        $maintable = $persistentmodal->get('maintable');

        if (empty($maintable)) {
            throw new moodle_exception('maintablenotconfigured', 'block_dash');
        }

        parent::__construct($maintable, DASHADDON_DEVELOPER_MAIN_ALIAS);
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title ?: get_string('persistenttablealias', 'block_dash');
    }

    /**
     * Set the title for the field.
     *
     * @param string $title
     * @return void
     */
    public function set_title(string $title) {
        $this->title = $title;
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {

        $fields = [
            new field('id', new lang_string('developerfieldid', 'block_dash'), $this, null, [
                new identifier_attribute(),
            ]),
        ];

        // Field repeats.
        $fieldrepeats = $this->modal->get('fieldrepeats');
        $selectfields = json_decode($this->modal->get('selectfield'));
        $fieldattributes = json_decode($this->modal->get('fieldattribute'));
        $customvalue = json_decode($this->modal->get('attributevalue'));

        // Make sure the field attributes and custom values are in array format, if not convert them into array for the loop.
        array_walk($customvalue, function (&$item) {
            $item = is_array($item) ? $item : [$item];
        });

        // Find the main table.
        $maintable = $this->modal->get('maintable');
        $tablesalias = [$maintable => DASHADDON_DEVELOPER_MAIN_ALIAS];
        // Table joins.
        $joins = $this->modal->get('tablejoins');
        $joins = $joins ? json_decode($joins) : [];
        // Table joins alias for ON.
        $joinalias = $this->modal->get('tablejoinsalias');
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

        // Placeholders.
        $placeholders = $this->modal->get_placeholders();

        for ($i = 0; $i < $fieldrepeats; $i++) {
            if (isset($selectfields[$i]) && !empty($selectfields[$i])) {
                $sfield = $selectfields[$i];

                $fieldtable = explode('.', $sfield);
                // This field doesn't contains any table as alias, this is raise the ambious error.
                if (!isset($fieldtable[1])) {
                    continue;
                }

                // Name of the field used in the DB table strucutes. without any alias.
                $realfield = ucfirst($fieldtable[1]);
                $fieldtable = reset($fieldtable); // First element is the table.

                if (isset($tablesalias[$fieldtable])) {
                    // Replace the table name with its alias.
                    $sfield = str_replace($fieldtable, $tablesalias[$fieldtable], $sfield);
                }
                // Remove the main table alias for the main table, alias will added in the field class.
                $fieldname = str_replace('.', '_', $sfield);
                $fieldname = str_replace(DASHADDON_DEVELOPER_MAIN_ALIAS . '_', '', $fieldname);

                // Create attribute to transform data.
                $fieldattribute = [];
                if ($fieldattributes[$i]) {
                    if (!is_array($fieldattributes[$i])) {
                        $fieldattributes[$i] = [$fieldattributes[$i]];
                    }

                    $availableattributes = $fieldattributes[$i];
                    $availableattributes = array_filter($availableattributes, fn($v) => !empty($v) && $v != false);

                    // Apply the field attributes to the field.
                    foreach ($availableattributes as $k => $attributeclass) {
                        if (empty($attributeclass)) {
                            continue;
                        }
                        if (!class_exists($attributeclass)) {
                            throw new \moodle_exception('invalidfieldattribute', 'block_dash', '', $attributeclass);
                        }

                        $attribute = new $attributeclass();

                        // Some attributes need the custom value to construct the data,
                        // For example, the linked data attribute needs the url to construct the link,
                        // So we need to set the custom value to the attribute before transform the data.
                        if ($attribute->is_needs_construct_data() && !empty($customvalue[$i][$k])) {
                            $attribute->set_transform_field(
                                DASHADDON_DEVELOPER_MAIN_ALIAS . '_' . $fieldname,
                                $customvalue[$i][$k]
                            );
                            $attribute->set_placeholders($placeholders);
                        } else if ($attribute->supports_direct_field()) {
                            // Attributes uses the direct field name to construct the data without receiving the data to transform,
                            // So we can set the field name directly to the attribute.
                            $attribute->set_transform_field(DASHADDON_DEVELOPER_MAIN_ALIAS . '_' . $fieldname);
                        }
                        $fieldattribute[] = $attribute;
                    }
                }

                $realtable = array_flip($tablesalias);

                $title = ucfirst($realtable[$fieldtable] ?? '');
                $this->set_title($title);

                $field = new field(
                    $fieldname,
                    new lang_string('developerfield', 'block_dash', $realfield),
                    $this,
                    $sfield,
                    $fieldattribute ?? []
                );

                $fields[] = $field;
            }
        }

        return $fields;
    }
}
