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
 * Helper class for Dash conditions to support multicategory filtering.
 *
 * @package    customfield_multicategory
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_multicategory;

/**
 * Helper class for Dash conditions to support multicategory filtering.
 *
 * @package customfield_multicategory
 */
class condition_helper {
    /**
     * Check if a field type should skip the default filter in data sources.
     *
     * @param string $fieldtype The custom field type.
     * @return bool True if the field should skip default filter.
     */
    public static function should_skip_default_filter(string $fieldtype): bool {
        return $fieldtype === 'multicategory';
    }

    /**
     * Get all multicategory custom field IDs.
     * Supports sites with more than one multicategory custom field.
     *
     * @return int[] Array of field IDs.
     */
    public static function get_all_multicategory_field_ids(): array {
        global $DB;

        static $fieldids = null;

        if ($fieldids === null) {
            $ids = $DB->get_fieldset_select('customfield_field', 'id', "type = 'multicategory'");
            $fieldids = array_map('intval', $ids);
        }

        return $fieldids;
    }

    /**
     * Get the first multicategory custom field ID if it exists.
     *
     * @return int|null Field ID or null if not found.
     */
    public static function get_multicategory_field_id(): ?int {
        $ids = self::get_all_multicategory_field_ids();
        return !empty($ids) ? $ids[0] : null;
    }

    /**
     * Get all category IDs that are referenced by any multicategory custom field value.
     *
     * @return int[] Unique category IDs across all multicategory field values.
     */
    public static function get_active_category_ids_from_fields(): array {
        global $DB;

        $fieldids = self::get_all_multicategory_field_ids();
        if (empty($fieldids)) {
            return [];
        }

        [$in, $params] = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED, 'mcat_fid');
        $values = $DB->get_fieldset_sql(
            "SELECT DISTINCT cd.value
               FROM {customfield_data} cd
              WHERE cd.fieldid $in
                AND cd.value IS NOT NULL
                AND cd.value != '[]'",
            $params
        );

        $categoryids = [];
        foreach ($values as $json) {
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

        return array_unique($categoryids);
    }

    /**
     * Build SQL to extend category condition with multicategory field support.
     *
     * @param string $basesql The base SQL condition.
     * @param array $baseparams The base SQL parameters.
     * @param array $categoryids The category IDs to filter by.
     * @param string $prefix Parameter name prefix to avoid collisions.
     * @return array
     */
    public static function extend_category_sql(
        string $basesql,
        array $baseparams,
        array $categoryids,
        string $prefix = 'mcat'
    ): array {
        global $DB;

        if (empty($categoryids)) {
            return ['sql' => $basesql, 'params' => $baseparams];
        }

        $fieldids = self::get_all_multicategory_field_ids();
        if (empty($fieldids)) {
            return ['sql' => $basesql, 'params' => $baseparams];
        }

        $multicatsql = self::build_multicategory_sql($categoryids, $fieldids, $prefix);

        $sql = "($basesql OR {$multicatsql['sql']})";
        $params = array_merge($baseparams, $multicatsql['params']);

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build SQL to check if a course has any of the category IDs in its multicategory field(s).
     *
     * @param array $categoryids The category IDs to check.
     * @param array $fieldids The multicategory field IDs.
     * @param string $prefix Param name prefix.
     * @return array
     */
    protected static function build_multicategory_sql(array $categoryids, array $fieldids, string $prefix): array {
        global $DB;

        $params = [];

        [$fieldin, $fieldparams] = $DB->get_in_or_equal($fieldids, SQL_PARAMS_NAMED, $prefix . '_fid');
        $params = array_merge($params, $fieldparams);

        $transformedsql = "REPLACE(REPLACE(cd.value, '[', ','), ']', ',')";

        $likeconditions = [];
        foreach ($categoryids as $index => $catid) {
            $catid = (int) $catid;
            $paramname = $prefix . '_cat' . $index;

            $likeconditions[] = $DB->sql_like($transformedsql, ':' . $paramname, false);
            $params[$paramname] = '%,' . $catid . ',%';
        }

        $likesql = implode(' OR ', $likeconditions);

        $sql = "c.id IN (
            SELECT cd.instanceid
              FROM {customfield_data} cd
             WHERE cd.fieldid $fieldin
               AND ($likesql)
        )";

        return ['sql' => $sql, 'params' => $params];
    }
}
