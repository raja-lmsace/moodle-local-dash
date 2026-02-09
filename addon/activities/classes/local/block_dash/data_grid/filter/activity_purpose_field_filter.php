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
 * Module type based filter option.
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter;

/**
 * Modulename based filter option.
 */
class activity_purpose_field_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        $purposes = [
            'Administration' => 'Administration',
            'Assessment' => 'Assessment',
            'Collaboration' => 'Collaboration',
            'Communication' => 'Communication',
            'Content' => 'Content',
            'Interface' => 'Interface',
            'Other' => 'Other',
        ];

        $designerpurposes = [];

        if (dashaddon_activities_is_designer_pro_installed()) {
            $values = $DB->get_records_sql_menu("SELECT DISTINCT id, name
                FROM {local_designer_purposes} WHERE custom = 1");

            $designerpurposes = array_combine(array_values($values), array_values($values));
        }
        $purposes = array_merge($purposes, $designerpurposes);
        $this->add_options($purposes);

        parent::init();
    }

    /**
     * Get the enrolment status filter label.
     * @return string
     */
    public function get_label() {
        return get_string('modulepurpose', 'dashaddon_activities');
    }


    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws coding_exception|dml_exception
     */
    public function get_sql_and_params() {
        global $DB;
        $values = $this->get_values();
        $lists = dashaddon_activities_get_purpose_module($values);
        $coursemodules = [];
        if ($lists) {
            [$moduleinsql, $moduleinparams] = $DB->get_in_or_equal($lists, SQL_PARAMS_NAMED);
            $coursemodules = $DB->get_records_sql_menu("
            SELECT cm.id AS key1, cm.id AS key2 FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            JOIN {course} c ON c.id = cm.course
            WHERE c.format != 'designer' AND m.name $moduleinsql
            ", $moduleinparams);
        }

        if (dashaddon_activities_is_designer_pro_installed()) {
            $lists = dashaddon_activities_get_designer_purpose($values);
            if ($lists) {
                [$moduleinsql, $moduleinparams] = $DB->get_in_or_equal($lists, SQL_PARAMS_NAMED);
                $sql = "SELECT cm.id AS key1, cm.id AS key2 FROM {course} c
                        JOIN {course_modules} cm ON cm.course = c.id
                        JOIN {modules} m ON m.id = cm.module
                        LEFT JOIN {format_designer_options} fdo ON fdo.cmid = cm.id
                        WHERE c.format = 'designer' AND fdo.name = 'purpose' AND fdo.value = NULL";
                $records = $DB->get_records_sql_menu($sql, $moduleinparams);
                $coursemodules = array_merge($coursemodules, $records);
            }

            [$purposesinsql, $purposesinparams] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED);

            $records = $DB->get_records_sql_menu("SELECT cm.id AS key1, cm.id AS key2 FROM {course} c
                        JOIN {course_modules} cm ON cm.course = c.id
                        JOIN {modules} m ON m.id = cm.module
                        JOIN {format_designer_options} fdo ON fdo.cmid = cm.id
                        WHERE c.format = 'designer' AND fdo.name = 'purpose' AND fdo.value $purposesinsql", $purposesinparams);
             $coursemodules = array_merge($coursemodules, $records);
        }
        if ($coursemodules) {
            [$filtersql, $filterparam] = $DB->get_in_or_equal($coursemodules, SQL_PARAMS_NAMED);
            $sql = "cm.id $filtersql";
            return [$sql, $filterparam];
        } else {
            $sql = "cm.id = :cmid";
            return [$sql, ['cmid' => 0]];
        }
    }
}
