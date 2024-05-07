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
 * Datasource of the custom persistent, creates custom persistent records as seperate unique datasource.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\data_source;

use block_dash\local\data_grid\field\field_definition_interface;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_source\abstract_data_source;
use dashaddon_developer\model\custom_data_source as custom_data_source_model;
use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\query_builder\where;
use dashaddon_developer\data_source\persistent_data_table;

/**
 * Datasource of the custom persistent, creates custom persistent records as seperate unique datasource.
 */
class persistent_data_source extends abstract_data_source {

    /**
     * Model handler of this datasource.
     *
     * @var custom_data_source_model
     */
    private $instance;

    /**
     * Construtor, creates the persitent table.
     *
     * @param custom_data_source_model $instance
     * @param \context $context
     */
    public function __construct(custom_data_source_model $instance, \context $context) {
        // Persistent modal data for the datasource.
        $this->instance = $instance;

        // Add the persistent table.
        $table = new persistent_data_table($instance);
        $this->add_table($table);

        parent::__construct($context);
    }

    /**
     * Get human readable name of template.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return $this->instance->get('name');
    }

    /**
     * Build the querys based on the datasource field configurations like main and joins and fields.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_query_template(): builder {

        // Find the main table.
        $table = $this->instance->get('maintable');

        // Builder.
        $builder = new builder();
        $builder
            ->select('mnt.id', 'mnt_id')
            ->from($table, 'mnt');

        // Table joins.
        $joins = $this->instance->get('tablejoins');
        $joins = $joins ? json_decode($joins) : [];

        // Table joins conditions for ON.
        $joinon = $this->instance->get('tablejoinon');
        $joinon = $joinon ? json_decode($joinon) : [];

        // Table joins alias for ON.
        $joinalias = $this->instance->get('tablejoinsalias');
        $joinalias = $joinalias ? json_decode($joinalias) : [];

        // Join the table to the builder.
        if ($joins && $this->instance->get('enablejoins')) {
            foreach ($joins as $key => $join) {
                if (empty($join) || empty($joinalias[$key])) {
                    continue;
                }

                $alias = $joinalias[$key]; // Table alias.
                $builder->join($join, $alias, '', '');
                $builder->join_condition($alias, $joinon[$key]);
            }
        }

        // Include the placeholder fields in selection query.
        $placeholders = $this->instance->get_placeholders();

        if ($placeholders) {
            foreach ($placeholders as $key => $field) {
                $alias = str_replace('.', '_', $field);
                $builder->select($field, $alias);
            }
        }

        // Build query conditions.
        $conditionfields = $this->instance->get('conditionfield');
        $conditionfields = $conditionfields ? json_decode($conditionfields) : [];
        // Condition operator.
        $operator = $this->instance->get('operator');
        $operator = $operator ? json_decode($operator) : [];
        // Condition value.
        $conditionvalue = $this->instance->get('conditionvalue');
        $conditionvalue = $conditionvalue ? json_decode($conditionvalue) : [];
        // Conjuctive operator condition.
        $conjunctiveoperator = $this->instance->get('operatorcondition');
        $conjunctiveoperator = $conjunctiveoperator ? json_decode($conjunctiveoperator) : [];

        if ($conditionfields && $this->instance->get('enableconditions')) {
            foreach ($conditionfields as $key => $field) {

                if (!isset($operator[$key]) || !isset($conditionvalue[$key]) || empty($field) || is_array($field)) {
                    continue;
                }

                $values = clean_param($conditionvalue[$key], PARAM_NOTAGS);
                $values = explode(',', $values);

                // Update the field table name with its alias.
                $field = $this->instance->update_field_alias($field);
                if (!$field) {
                    continue;
                }
                // Include the where condition in the builder.
                $builder->where($field, $values,
                    $operator[$key] ?? where::OPERATOR_IN, $conjunctiveoperator[$key] ?? where::CONJUNCTIVE_OPERATOR_AND);
            }
        }

        // Raw condition.
        $customcondition = $this->instance->get('customcondition');
        if ($customcondition) {
            $builder->where_raw($customcondition);
        }

        return $builder;
    }

    /**
     * Filter collections for this datasource, Currently developer not contains any datasources.
     *
     * @return filter_collection_interface
     * @throws \coding_exception
     */
    public function build_filter_collection() {
        return new filter_collection('filter' . $this->instance->get('id'), $this->get_context());
    }
}
