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
 * Transform activity data into activity icon.
 *
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;

/**
 * Transforms activity data to formatted activity icon.
 *
 * @package dashaddon_activities
 */
class activity_completion_status_attribute extends abstract_field_attribute {
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
        global $DB, $USER;

        $cm = cm_info::create(get_coursemodule_from_id('', $record->cm_id));

        if ($record->cm_completion == COMPLETION_TRACKING_NONE) {
            return "";
        }

        if ($cm == null) {
            return "";
        }

        $completion = new \completion_info($cm->get_course());

        if ($completion->is_tracked_user($record->u_id ?? $USER->id)) {
            $completiondata = $completion->get_data($cm, false, $record->u_id ?? $USER->id);
            if (
                $completiondata->completionstate == COMPLETION_COMPLETE ||
                $completiondata->completionstate == COMPLETION_COMPLETE_PASS
            ) {
                return get_string('completed');
            } else {
                return get_string('notcompleted', 'dashaddon_activities');
            }
        }
        return "";
    }
}
