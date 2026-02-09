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
 * Filters results to specific course completion status.
 *
 * @package    local_dash
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use coding_exception;
use dml_exception;
use forumreport_summary\output\filters;
use moodleform;
use MoodleQuickForm;


/**
 * Filters results to specific course completion status.
 *
 * @package local_dash
 */
class course_dates_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {
        global $DB;

        $choices = [
            'past' => get_string('coursedata:past', 'block_dash'),
            'present' => get_string('coursedate:present', 'block_dash'),
            'future' => get_string('coursedate:future', 'block_dash'),
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

        return get_string('coursedates', 'block_dash');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        [$sql, $dates] = parent::get_sql_and_params();

        if (is_array($dates)) {
            $sql = [];
            $params = [];
            foreach ($dates as $key => $date) {
                switch ($date) {
                    case 'past':
                        $sql[] = "(c.enddate <> 0 AND c.enddate < :cdf_now_$key)";
                        $params += ['cdf_now_' . $key => time()];
                        break;
                    case 'present':
                        $sql[] = "(c.startdate < :cdf_startdate_$key AND ( c.enddate = 0 OR c.enddate > :cdf_enddate_$key) )";
                        $params += ['cdf_enddate_' . $key => time(), 'cdf_startdate_' . $key => time()];
                        break;
                    case 'future':
                        $sql[] = "(c.startdate > :cdf_now_$key)";
                        $params += ['cdf_now_' . $key => time()];
                        break;
                }
            }

            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
        return false;
    }
}
