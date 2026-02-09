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
 * Filters results to current category only.
 *
 * @package    dashaddon_learningpath
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;

/**
 * Filters results to current category only.
 *
 * @package dashaddon_learningpath
 */
class current_category_condition extends condition {
    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB, $PAGE;
        $categoryid = 0;
        if ($coursecontext = $this->get_context()->get_course_context(false)) {
            $course = get_course($coursecontext->instanceid);
            $categoryid = !empty($coursecontext) ? $course->category : 0;
        } else if ($this->get_context()->contextlevel == CONTEXT_COURSECAT) {
            $categoryid = $this->get_context()->instanceid;
        }
        if ($categoryid) {
            if ($categoryrecord = $DB->get_record('course_categories', ['id' => $categoryid])) {
                [$insql, $inparams] = $DB->get_in_or_equal($categoryrecord->id, SQL_PARAMS_NAMED);
                $sql = " c.category $insql ";
                return [$sql, $inparams];
            }
        }
        return false;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('currentcategory', 'block_dash');
    }
}
