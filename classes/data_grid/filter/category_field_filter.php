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
 * Category name based filter option.
 *
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter_collection_interface;
use core_course_category;

/**
 * Category filter that restricts options to categories in the actual result set.
 */
class category_field_filter extends select_filter {
    /**
     * Initialize the filter. Load only categories that have at least one course.
     */
    public function init() {
        $this->add_options($this->get_categories_with_courses());
        parent::init();
    }

    /**
     * Get categories that have at least one course, plus any additional categories
     *
     * @return array id => name
     */
    protected function get_categories_with_courses(): array {
        global $DB;

        $categories = $DB->get_records_sql_menu(
            "SELECT DISTINCT cc.id, cc.name
               FROM {course_categories} cc
               INNER JOIN {course} c ON c.category = cc.id
               WHERE cc.visible = 1 AND c.format != 'site'"
        );

        $extraids = local_dash_is_multicategory_installed() ?
            component_callback('customfield_multicategory', 'dash_category_extra_init_ids', [], []) : [];
        if (!empty($extraids)) {
            $missing = array_diff($extraids, array_keys($categories));
            if (!empty($missing)) {
                [$in, $params] = $DB->get_in_or_equal($missing, SQL_PARAMS_NAMED, 'mcfcat');
                $extra = $DB->get_records_sql_menu(
                    "SELECT id, name FROM {course_categories} WHERE visible = 1 AND id $in",
                    $params
                );
                $categories += $extra;
            }
        }

        return $categories;
    }

    /**
     * Get category IDs that appear in the actual current result set.
     *
     * @param filter_collection_interface $filtercollection
     * @return int[] Category IDs present in the result set.
     */
    protected function get_result_category_ids(filter_collection_interface $filtercollection): array {
        global $DB;

        $wheresql = [];
        $params   = [];
        foreach ($filtercollection->get_filters() as $filter) {
            if ($filter->get_name() === $this->get_name()) {
                continue;
            }
            if (!$filter->has_raw_value()) {
                continue;
            }

            if (!$this->is_select_safe($filter->get_select())) {
                continue;
            }

            $result = $filter->get_sql_and_params();
            if (!is_array($result) || count($result) < 2) {
                continue;
            }
            [$sql, $filterparams] = $result;
            if (empty($sql) || empty($filterparams)) {
                continue;
            }
            $wheresql[] = $sql;
            $params = array_merge($params, $filterparams);
        }

        $where = !empty($wheresql) ? 'AND ' . implode(' AND ', $wheresql) : '';

        $categoryids = array_map('intval', $DB->get_fieldset_sql(
            "SELECT DISTINCT c.category
                FROM {course} c
                LEFT JOIN {course_categories} cc ON cc.id = c.category
                WHERE c.format != 'site'
                $where",
            $params
        ));

        $extraids = local_dash_is_multicategory_installed() ?
            component_callback('customfield_multicategory', 'dash_category_extra_result_ids', [$where, $params], []) : [];
        $categoryids = array_merge($categoryids, array_map('intval', $extraids));

        return array_unique($categoryids);
    }

    /**
     * Check if the select column alias is safe to use in our SQL generation.
     *
     * @param string $select The select column alias to check.
     * @return bool
     */
    protected function is_select_safe(string $select): bool {
        $parts = explode('.', $select, 2);
        return count($parts) === 2 && in_array($parts[0], ['c', 'cc'], true);
    }

    /**
     * Build filter options sorted in tree order with depth-based indentation.
     *
     * @param int[] $activecategoryids Category IDs to show.
     * @return array
     */
    protected function build_sorted_options(array $activecategoryids = []): array {
        if (empty($activecategoryids)) {
            return [];
        }

        $allcategories = core_course_category::get_all(['returnhidden' => false]);
        $neededids = [];
        foreach ($activecategoryids as $catid) {
            $catid = (int) $catid;
            $neededids[$catid] = true;
            if (isset($allcategories[$catid])) {
                foreach (explode('/', trim($allcategories[$catid]->path, '/')) as $ancestorid) {
                    $ancestorid = (int) $ancestorid;
                    if ($ancestorid > 0) {
                        $neededids[$ancestorid] = true;
                    }
                }
            }
        }

        $filtered = [];
        foreach ($allcategories as $cat) {
            if ($cat->visible && isset($neededids[$cat->id])) {
                $filtered[] = $cat;
            }
        }
        usort($filtered, function ($a, $b) {
            return strcmp($a->path, $b->path);
        });

        $options = [];
        foreach ($filtered as $cat) {
            $depth = substr_count(trim($cat->path, '/'), '/');
            $indent = str_repeat('— ', $depth);
            $options[$cat->id] = $indent . format_string($cat->name);
        }

        return $options;
    }

    /**
     * Get all subcategory IDs nested under a given category.
     *
     * @param int $categoryid
     * @return int[]
     */
    protected function get_subcategory_ids(int $categoryid): array {
        global $DB;

        $likepath = $DB->sql_like('path', ':pathpattern');
        return $DB->get_fieldset_select(
            'course_categories',
            'id',
            "$likepath AND id != :catid AND visible = 1",
            [
                'pathpattern' => '%/' . $categoryid . '/%',
                'catid' => $categoryid,
            ]
        );
    }

    /**
     * Get values, expanding selected categories to include all descendants.
     *
     * @return array
     */
    public function get_values() {
        $values = parent::get_values();

        if (empty($values)) {
            return [];
        }

        $expandedvalues = [];
        foreach ($values as $catid) {
            $expandedvalues[] = (int) $catid;
            $subcategoryids = $this->get_subcategory_ids((int) $catid);
            $expandedvalues = array_merge($expandedvalues, array_map('intval', $subcategoryids));
        }

        return array_unique($expandedvalues);
    }

    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        if (
            local_dash_is_multicategory_installed() &&
            component_callback('customfield_multicategory', 'dash_has_custom_sql', [], false)
        ) {
            return self::OPERATION_CUSTOM;
        }
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Return where SQL and params.
     *
     * @return array
     */
    public function get_sql_and_params() {
        global $DB;

        $values = $this->get_values();
        if (empty($values)) {
            return ['', []];
        }

        $select = $this->get_select();
        $name = $this->get_name();

        [$insql, $params] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED, $name . '_fcat');
        $basesql = "$select $insql";

        $result = local_dash_is_multicategory_installed() ?
            component_callback(
                'customfield_multicategory',
                'dash_category_extend_sql',
                [
                    $basesql,
                    $params,
                    $values,
                    $name . '_mfcat',
                ],
                null
            ) : null;
        if (!empty($result['sql'])) {
            $basesql = $result['sql'];
            $params  = $result['params'];
        }

        return [$basesql, $params];
    }

    /**
     * Restrict options to categories present in the result set, in tree order.
     *
     * @param filter_collection_interface $filtercollection filter collection
     * @return bool|array
     */
    protected function get_active_option_values(filter_collection_interface $filtercollection): ?array {
        $resultcategoryids = $this->get_result_category_ids($filtercollection);
        $this->directcategoryids = $resultcategoryids;
        foreach ($this->get_selected_options() as $selectedid) {
            $selectedid = (int) $selectedid;
            if ($selectedid > 0 && !in_array($selectedid, $resultcategoryids, true)) {
                $resultcategoryids[] = $selectedid;
            }
        }

        $newoptions = $this->build_sorted_options($resultcategoryids);

        $this->options = [];
        $this->add_all_option();
        $this->add_options($newoptions);

        return null;
    }

    /**
     * IDs of categories that directly contain courses in the current result set.
     *
     * @var int[]
     */
    protected array $directcategoryids = [];

    /**
     * Collapse any single-child ancestor chain at the root level.
     *
     * @param array $nodes Category tree nodes.
     * @return array Collapsed category tree nodes.
     */
    protected function collapse_single_path(array $nodes): array {
        if (
            count($nodes) === 1 &&
            count($nodes[0]['children']) === 1 &&
            !in_array($nodes[0]['id'], $this->directcategoryids, true)
        ) {
            return $this->collapse_single_path($nodes[0]['children']);
        }
        return $nodes;
    }

    /**
     * Render category filter.
     *
     * @param filter_collection_interface $filtercollection Current filter collection
     * @param string $elementnameprefix Prefix to add to the form element name.
     * @return string Rendered HTML.
     */
    public function create_form_element(filter_collection_interface $filtercollection, $elementnameprefix = '') {
        global $OUTPUT, $PAGE, $CFG;

        $this->get_active_option_values($filtercollection);

        $name = $elementnameprefix . $this->get_name();
        $selectedoptions = $this->get_selected_options();

        $selectoptions = [];
        foreach ($this->options as $value => $label) {
            $selectoptions[] = [
                'value'    => $value,
                'label'    => $label,
                'selected' => in_array($value, $selectedoptions),
            ];
        }

        $tree = $this->build_category_tree_from_options($this->options);
        $tree = $this->collapse_single_path($tree);
        $PAGE->requires->js_call_amd('local_dash/category_mega_filter', 'init', [$name]);

        // Determine current button label.
        $labelall = get_string('all') . ' ' . $this->get_label();
        $label = $this->get_label();
        if (!empty($selectedoptions) && !in_array(self::ALL_OPTION, $selectedoptions)) {
            $firstid = reset($selectedoptions);
            if (isset($this->options[$firstid])) {
                $label = trim(preg_replace('/^(—\s*)+/', '', $this->options[$firstid]));
            }
        }

        // If the tree has only one node with no children, show it as a single option.
        $hastree = !empty($tree);
        if (count($tree) === 1 && empty($tree[0]['children'])) {
            $label   = $tree[0]['name'];
            $hastree = false;
        }

        // Bootstrap version compatibility.
        $isbs5      = $CFG->branch >= 500;
        $datatoggle = $isbs5 ? 'data-bs-toggle' : 'data-toggle';
        $marginend   = $isbs5 ? 'me-2' : 'mr-2';
        $marginstart = $isbs5 ? 'ms-1' : 'ml-1';

        $showclear = !empty($selectedoptions) && !in_array(self::ALL_OPTION, $selectedoptions);

        return $OUTPUT->render_from_template('local_dash/filter_category', [
            'name'          => $name,
            'label'         => $label,
            'labelall'      => $labelall,
            'selectoptions' => $selectoptions,
            'treejson'      => json_encode($tree),
            'hastree'       => $hastree,
            'isbs5'         => $isbs5,
            'datatoggle'    => $datatoggle,
            'marginend'     => $marginend,
            'marginstart'   => $marginstart,
            'showclear'     => $showclear,
        ]);
    }

    /**
     * Build a nested category tree from the flat options array.
     *
     * @param array $options Categories
     * @return array
     */
    protected function build_category_tree_from_options(array $options): array {
        $catids = array_filter(array_keys($options), fn($id) => (int)$id > 0);

        if (empty($catids)) {
            return [];
        }

        $allcats = [];
        foreach ($catids as $id) {
            $cat = core_course_category::get((int)$id, IGNORE_MISSING);
            if ($cat) {
                $allcats[(int)$id] = $cat;
            }
        }

        $bypathkey = [];
        foreach ($catids as $id) {
            $id = (int) $id;
            if (isset($allcats[$id])) {
                $bypathkey[$id] = $allcats[$id]->path;
            }
        }
        asort($bypathkey);

        $nodes = [];
        foreach (array_keys($bypathkey) as $id) {
            $nodes[$id] = [
                'id'           => $id,
                'name'         => format_string($allcats[$id]->name),
                'has_children' => false,
                'children'     => [],
            ];
        }

        $roots = [];
        foreach (array_keys($bypathkey) as $id) {
            $parentid = (int) $allcats[$id]->parent;
            if ($parentid > 0 && isset($nodes[$parentid])) {
                $nodes[$parentid]['children'][] = $id;
                $nodes[$parentid]['has_children'] = true;
            } else {
                $roots[] = $id;
            }
        }

        // Recursively convert id-based child lists to full node arrays.
        $buildnode = function (int $id) use (&$buildnode, &$nodes): array {
            $node = $nodes[$id];
            $children = [];
            foreach ($node['children'] as $childid) {
                $children[] = $buildnode($childid);
            }
            $node['children'] = $children;
            return $node;
        };

        $tree = [];
        foreach ($roots as $rootid) {
            $tree[] = $buildnode($rootid);
        }

        return $tree;
    }
}
