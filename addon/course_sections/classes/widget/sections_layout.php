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
 * A widget layout contains information on how to display data.
 *
 * @package    dashaddon_course_sections
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_sections\widget;

use block_dash\local\widget\abstract_layout;

/**
 * Layout section for the enrolments widget.
 */
class sections_layout extends abstract_layout {
    /**
     * Tempalte mustache file name, the layout uses.
     *
     * @return string
     */
    public function get_mustache_template_name() {
        return 'dashaddon_course_sections/sections';
    }

    /**
     * Is the layout supports the fields method.
     *
     * @return bool
     */
    public function supports_field_visibility() {
        return false;
    }

    /**
     * Is the layout supports the filter method.
     *
     * @return bool
     */
    public function supports_filtering() {
        return true;
    }

    /**
     * Is the layout supports the pagination.
     *
     * @return bool
     */
    public function supports_pagination() {
        return true;
    }

    /**
     * Is the layout supports the sorting.
     *
     * @return bool
     */
    public function supports_sorting() {
        return true;
    }
}
