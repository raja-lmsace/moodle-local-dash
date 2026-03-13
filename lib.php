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
 * Plugin callbacks for integrating with the Dash category filter.
 *
 * @package    customfield_multicategory
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provide extra category IDs for Dash category filter initialisation.
 *
 * @return int[] Category IDs referenced by any multicategory custom field value.
 */
function customfield_multicategory_dash_category_extra_init_ids(): array {
    return \customfield_multicategory\condition_helper::get_active_category_ids_from_fields();
}

/**
 * Provide extra result category IDs for the Dash category filter.
 *
 * @param string $where  Extra WHERE clause.
 * @param array  $params Bound parameters for $where.
 * @return int[] Additional category IDs found in multicategory field data for the result set.
 */
function customfield_multicategory_dash_category_extra_result_ids(string $where, array $params): array {
    global $DB;

    $fieldids = \customfield_multicategory\condition_helper::get_all_multicategory_field_ids();
    if (empty($fieldids)) {
        return [];
    }

    [$fieldin, $fieldparams] = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED, 'rcf_fid');
    $mcparams = array_merge($params, $fieldparams);

    $mcvalues = $DB->get_fieldset_sql(
        "SELECT DISTINCT mcf_data.value
           FROM {customfield_data} mcf_data
          WHERE mcf_data.fieldid $fieldin
            AND mcf_data.value IS NOT NULL
            AND mcf_data.value != '[]'
            AND mcf_data.instanceid IN (
                SELECT c.id
                  FROM {course} c
                  LEFT JOIN {course_categories} cc ON cc.id = c.category
                 WHERE c.format != 'site'
                 $where
            )",
        $mcparams
    );

    $categoryids = [];
    foreach ($mcvalues as $json) {
        $ids = json_decode($json, true);
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $categoryids[] = $id;
                }
            }
        }
    }

    return $categoryids;
}

/**
 * Indicate whether the Dash category filter should use a custom SQL operation to cover multicategory fields.
 *
 * @return bool True when multicategory fields are configured, requiring custom SQL.
 */
function customfield_multicategory_dash_has_custom_sql(): bool {
    return !empty(\customfield_multicategory\condition_helper::get_all_multicategory_field_ids());
}

/**
 * Extend the Dash category filter SQL to cover courses tagged via
 * multicategory custom fields.
 *
 * @param string $basesql Current SQL condition.
 * @param array  $baseparams Current bound parameters.
 * @param array  $values Category IDs being filtered.
 * @param string $prefix Parameter-name prefix to avoid collisions.
 * @return array
 */
function customfield_multicategory_dash_category_extend_sql(
    string $basesql,
    array $baseparams,
    array $values,
    string $prefix
): array {
    return \customfield_multicategory\condition_helper::extend_category_sql(
        $basesql,
        $baseparams,
        $values,
        $prefix
    );
}
