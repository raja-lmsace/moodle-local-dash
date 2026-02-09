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
 * Course module completion and date status filter option.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter;
use cm_info;

/**
 * Completed activity status based filter option.
 */
class activity_status_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB;

        $status = [
            'open' => get_string('open', 'dashaddon_activity_completion'),
            'due' => get_string('due', 'dashaddon_activity_completion'),
            'overdue' => get_string('overdue', 'dashaddon_activity_completion'),
            'complete' => get_string('complete', 'dashaddon_activity_completion'),
            'late' => get_string('late', 'dashaddon_activity_completion'),
        ];
        foreach ($status as $key => $option) {
            $this->options[$key] = $option;
        }

        parent::init();
    }

    /**
     * Get the activity date filter label.
     * @return string
     */
    public function get_label() {
        return get_string('status', 'dashaddon_activity_completion');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        [$sql, $params] = parent::get_sql_and_params();

        if (is_array($params)) {
            $sql = [];
            foreach ($params as $key => $status) {
                switch ($status) {
                    case 'open':
                        $sql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) > :now_$key + 86000) AND
                                cmc.completionstate = 0";
                        $params += ['now_' . $key => time()];
                        break;
                    case 'due':
                        $sql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) <= :now_$key + 86000) AND
                                cmc.completionstate = 0 ";
                        $params += ['now_' . $key => time(), 'now1_' . $key => time()];
                        break;
                    case 'overdue':
                        $sql[] = "(UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) IS NOT NULL AND
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')) <= :now_$key) AND
                                cmc.completionstate = 0 ";
                        $params += ['now_' . $key => time()];
                        break;
                    case 'complete':
                        $sql[] = "(cmc.completionstate <> 0 AND (cmc.timemodified <=
                                UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d'))))";
                        break;
                    case 'late':
                        $sql[] = "(cmc.completionstate <> 0 AND STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d') IS NOT NULL AND
                                cmc.timemodified >= UNIX_TIMESTAMP(STR_TO_DATE(tm.duedatecustom, '%Y/%m/%d')))";
                        break;
                }
            }
            return ['(' . implode(' OR ', $sql) . ')', $params];
        }
        return false;
    }
}
