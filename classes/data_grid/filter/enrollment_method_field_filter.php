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
 * Enrolment method based table filter.
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Enrolment method based table filter.
 */
class enrollment_method_field_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $CFG;

        require_once("$CFG->dirroot/course/lib.php");

        $plugins = enrol_get_plugins(true);

        foreach ($plugins as $name => $plugin) {
            $this->add_option($name, get_string('pluginname', 'enrol_' . $name));
        }

        parent::init();
    }

    /**
     * Get the enrolment field filter label.
     * @return string
     */
    public function get_label() {
        return get_string('enrollmentmethod', 'block_dash');
    }
}
