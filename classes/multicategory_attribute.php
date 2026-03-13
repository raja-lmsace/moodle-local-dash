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
 * Transform multicategory custom field value to comma-separated category names.
 *
 * @package    customfield_multicategory
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_multicategory;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Transforms value from multicategory custom field (JSON array of IDs to category names).
 *
 * @package customfield_multicategory
 */
class multicategory_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Converts JSON array of category IDs to comma-separated category names.
     *
     * @param mixed $data The raw data.
     * @param \stdClass $record Entire row.
     * @return string Comma-separated category names.
     */
    public function transform_data($data, \stdClass $record) {
        if (empty($data)) {
            return '';
        }

        $categoryids = json_decode($data, true);
        if (!is_array($categoryids) || empty($categoryids)) {
            return '';
        }

        // Get category names.
        $names = [];
        foreach ($categoryids as $catid) {
            $catid = (int) $catid;
            if ($catid <= 0) {
                continue;
            }

            $category = \core_course_category::get($catid, IGNORE_MISSING);
            if ($category) {
                $context = \context_coursecat::instance($category->id);
                $filtercontext = \context_helper::get_navigation_filter_context($context);
                $names[] = format_string($category->name, true, ['context' => $filtercontext]);
            }
        }

        return implode(', ', $names);
    }
}
