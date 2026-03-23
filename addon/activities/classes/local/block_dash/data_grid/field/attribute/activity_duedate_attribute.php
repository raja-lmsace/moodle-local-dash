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
 * Transform activity data into activity duedate.
 *
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/dash/addon/activities/lib.php");

/**
 * Transforms activity data to formatted activity duedate.
 *
 * @package dashaddon_activities
 */
class activity_duedate_attribute extends abstract_field_attribute {
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
        global $USER;
        $cm = cm_info::create(get_coursemodule_from_id('', $record->cm_id));
        $duedate = "";
        // When the course complete the duedate attribute return empty string.
        if (
            ($record->cm_modcompletionstatus == COMPLETION_COMPLETE ||
            $record->cm_modcompletionstatus == COMPLETION_COMPLETE_PASS)
        ) {
                return $duedate;
        }

        if ($cm == null) {
            return $duedate;
        }

        if ($data) {
            $duedate = userdate($data, get_string('strftimedatefullshort'));
        }
        if (dashaddon_activities_is_timetable_installed()) {
            $duedate = dashaddon_activities_get_mod_user_duedate($cm, $record->u_id ?? $USER->id) ?
                userdate(
                    dashaddon_activities_get_mod_user_duedate($cm, $record->u_id ?? $USER->id),
                    get_string('strftimedatefullshort')
                )
                : $duedate;
        }
        return $duedate;
    }
}
