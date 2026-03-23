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
 * Filters results to specific condition of calendar events type (Site, course, category, group, user, other)
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filters results to specific condition of calendar events type (Site, course, category, group, user, other)
 */
class event_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $choices = [
            'site' => get_string('event:typesite', 'block_dash'),
            'category' => get_string('event:typecategory', 'block_dash'),
            'course' => get_string('event:typecourse', 'block_dash'),
            'group' => get_string('event:typegroup', 'block_dash'),
            'user' => get_string('event:typeuser', 'block_dash'),
            'other' => get_string('event:typeother', 'block_dash'),
        ];
        $this->add_options($choices);

        parent::init();
    }

    /**
     * Get filter config label.
     *
     * @return string
     */
    public function get_label() {

        return get_string('event:typefilter', 'block_dash');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        [$sql, $types] = parent::get_sql_and_params();

        if (is_array($types)) {
            $sql = [];
            $params = [];

            foreach ($types as $key => $type) {
                switch ($type) {
                    case "site":
                        $sql[] = "(ce.eventtype = :cesite_$key) ";
                        $params += ['cesite_' . $key => 'site'];
                        break;
                    case "category":
                        $sql[] = "(ce.categoryid > :cecate$key)";
                        $params += ["cecate$key" => 0];
                        break;
                    case "course":
                        $sql[] = "(ce.courseid > :cecourseid$key AND ce.groupid <= 0)";
                        $params += ['cecourseid' . $key => 1];
                        break;
                    case "group":
                        $sql[] = "(ce.groupid > :cegroup$key)";
                        $params += ['cegroup' . $key => 0];
                        break;
                    case "user":
                        $sql[] = "(ce.eventtype = :ceuser$key) ";
                        $params += ['ceuser' . $key => 'user'];
                        break;
                    case "other":
                        $sql[] = "(
                            ce.eventtype <> :ceuser
                            AND ce.eventtype <> :cesite
                            AND ce.eventtype <> :cecourse
                            AND ce.categoryid = 0
                            AND ce.groupid = 0
                            AND NOT (ce.courseid > 1 AND (ce.modulename <> '' AND ce.instance > 0))
                        )";
                        $params += ['ceuser' => 'user', 'cesite' => 'site', 'cecourse' => 'course'];
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
        return false;
    }
}
