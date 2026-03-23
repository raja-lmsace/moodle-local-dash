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
 * Transform activity data into activity path.
 *
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;
use core_course_category;

/**
 * Transforms activity data to formatted activity path.
 *
 * @package dashaddon_activities
 */
class activity_path_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;
        // Get category path.
        $category = core_course_category::get($record->cm_category, IGNORE_MISSING);
        $categorypath = $category ? $category->get_nested_name(false) : '';

        // Get course path.
        $course = get_course($record->cm_course);
        $courseinfo = new \core_course_list_element($course);
        $path = $categorypath . " / " . $courseinfo->get_formatted_fullname();

        // Get section path.
        $modinfo = get_fast_modinfo($course);
        $section = (object) $modinfo->get_section_info_by_id($record->cm_section, MUST_EXIST);
        $sectionname = get_section_name($modinfo->get_course(), $section);
        $path = $path . " / " . $sectionname;
        return $path;
    }
}
