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
 * External API class.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use block_dash\local\data_grid\field\field_definition_factory;
use core_external\external_single_structure;
use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;

/**
 * External API class. - NOT USED SHOULD removed in next version.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    // Region get_database_schema_structure.

    /**
     * Returns description of get_database_schema_structure() parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_database_schema_structure_parameters() {
        return new \external_function_parameters([]);
    }

    /**
     * Create a new competency framework
     *
     * @return array
     */
    public static function get_database_schema_structure() {
        global $DB, $CFG;

        $dbman = $DB->get_manager();

        $schema = new \xmldb_structure('export');
        $schema->setVersion($CFG->version);

        $tables = [];
        foreach ($dbman->get_install_xml_files() as $filename) {
            $xmldbfile = new \xmldb_file($filename);
            if (!$xmldbfile->loadXMLStructure()) {
                continue;
            }
            $structure = $xmldbfile->getStructure();
            foreach ($structure->getTables() as $table) {
                $tablename = '{' . $table->getName() . '}';
                $tables[$tablename] = [];
                foreach ($table->getFields() as $field) {
                    $tables[$tablename][] = $field->getName();
                }
            }
        }

        return ['schema' => json_encode($tables)];
    }

    /**
     * Returns description of get_database_schema_structure() result value.
     *
     * @return \external_description
     */
    public static function get_database_schema_structure_returns() {
        return new \external_single_structure([
            'schema' => new \external_value(PARAM_RAW),
        ]);
    }

    /**
     * Returns description of get_database_schema_structure() parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_field_edit_row_parameters() {
        return new \external_function_parameters([
            'name' => new \external_value(PARAM_TEXT, 'Name of field definition'),
        ]);
    }

    /**
     * Create a new competency framework
     *
     * @param string $name
     * @return array
     * @throws \moodle_exception | \coding_exception | \invalid_parameter_exception
     */
    public static function get_field_edit_row($name) {
        global $OUTPUT;

        $params = self::validate_parameters(self::get_field_edit_row_parameters(), [
            'name' => $name,
        ]);

        self::validate_context(\context_system::instance());

        if ($fielddefinition = field_definition_factory::get_field_definition($params['name'])) {
            return ['html' => $OUTPUT->render_from_template('dashaddon_developer/field_edit_row', $fielddefinition)];
        }

        throw new \moodle_exception('fieldnotfound', 'block_dash');
    }

    /**
     * Returns description of get_database_schema_structure() result value.
     *
     * @return \external_description
     */
    public static function get_field_edit_row_returns() {
        return new \external_single_structure([
            'html' => new \external_value(PARAM_RAW),
        ]);
    }

    /**
     * Get list of chapters for the book module function parameters.
     *
     * @return object type of the badge type.
     */
    public static function get_fields_list_parameters() {

        return new \external_function_parameters(
            [
                'tables' => new \external_single_structure(
                    [
                        'main' => new external_value(PARAM_ALPHANUMEXT, 'Main table'),
                        'joins' => new \external_multiple_structure(
                            new external_value(PARAM_ALPHANUMEXT, 'Course id'),
                            'List of tables joined',
                            VALUE_OPTIONAL
                        ),
                    ],
                    'Tables defined'
                ),
                'query' => new external_value(PARAM_ALPHANUMEXT, 'Main table', VALUE_DEFAULT, null),
            ]
        );
    }

    /**
     * Get list of badges based on the requested type.
     *
     * @param string $tables List of tables main and joins.
     * @param string $query
     * @return array $type List of badge types.
     */
    public static function get_fields_list($tables, $query = null) {
        global $DB;

        if (!empty($tables)) {
            $main = $tables['main'];
            $columns = $DB->get_columns($main);
            $columns = array_keys($columns);
            $alias = "mnt";
            foreach ($columns as $index => $column) {
                $list[] = ['value' => $alias . '.' . $column, 'label' => $main . ':' . $column];
            }

            // Build the fields list for the join tables.
            $tables = $tables['joins'];

            if (empty($tables)) {
                return $list ?? [];
            }
            // Tables.
            $i = 1;
            foreach ($tables as $table) {
                $columns = $DB->get_columns($table);
                $columns = array_keys($columns);

                foreach ($columns as $index => $column) {
                    $list[] = ['value' => $table . '.' . $column, 'label' => $table . ':' . $column];
                }
                $i++;
            }
        }

        return $list ?? [];
    }

    /**
     * Return chapters list data definition.
     *
     * @return array list of chapaters.
     */
    public static function get_fields_list_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                [
                    'value' => new \external_value(PARAM_TEXT, 'Field selector', VALUE_OPTIONAL),
                    'label' => new \external_value(PARAM_TEXT, 'Field label', VALUE_OPTIONAL),
                ]
            ),
            '',
            VALUE_OPTIONAL
        );
    }
}
