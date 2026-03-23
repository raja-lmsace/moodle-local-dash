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
 * Filter option to filter the list records based on the course format.
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
/**
 * Filter option to filter the list records based on the course format.
 */
class course_format_field_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $CFG;

        require_once("$CFG->dirroot/course/lib.php");

        if ($formats = get_sorted_course_formats(true)) {
            foreach ($formats as $format) {
                $this->add_option($format, get_string('pluginname', 'format_' . $format));
            }
        }

        parent::init();
    }

    /**
     * Get filter option label.
     *
     * @return string
     */
    public function get_label() {
        return get_string('format');
    }
}
