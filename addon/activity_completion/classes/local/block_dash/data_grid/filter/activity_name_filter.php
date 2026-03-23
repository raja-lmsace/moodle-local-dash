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
 * Activity based filter option.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter;

/**
 * Activity name based filter option.
 */
class activity_name_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        $records = $DB->get_records_menu('modules', null, '', 'id, name');

        $cmsqlstart = '
            SELECT cm.id AS cmid,
                CASE m.name
        ';
        $cmnamesql = '';
        $cmsqlend = ' END AS activityname';

        foreach ($records as $record) {
            $cmnamesql .= " WHEN '$record' THEN (SELECT name FROM {" . $record . "} WHERE id = cm.instance)";
        }

        $sql = $cmsqlstart . $cmnamesql . $cmsqlend . " FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            WHERE cm.deletioninprogress = 0 AND (cm.visible = 1)
        ";

        $activities = $DB->get_records_sql($sql, []);
        foreach ($activities as $cmid => $activity) {
            $this->options[$cmid] = $activity->activityname;
        }

        parent::init();
    }

    /**
     * Get the activity filter label.
     * @return string
     */
    public function get_label() {
        return get_string('activity', 'dashaddon_activity_completion');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        $cmids = $this->get_values();
        [$sql, $params] = parent::get_sql_and_params();

        if ($sql) {
            [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cm', true, true);
            $sql = ' cm.id ' . $insql;
        }
        return [$sql, $inparams];
    }
}
