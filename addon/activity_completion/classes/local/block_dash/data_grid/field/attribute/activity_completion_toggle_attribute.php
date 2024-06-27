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
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activity_completion\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use html_writer;
use moodle_url;
use cm_info;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/dash/addon/activity_completion/lib.php");

/**
 * Transforms activity data to formatted activity duedate.
 *
 * @package dashaddon_activity_completion
 */
class activity_completion_toggle_attribute extends abstract_field_attribute {

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
        global $PAGE, $USER, $OUTPUT;

        $cm = cm_info::create(get_coursemodule_from_id('', $record->cm_id));
        $context = \context_course::instance($record->c_id);
        $togglehbtn = '';
        if (has_capability('report/progress:view', $context)) {

            if ($record->cm_completion == COMPLETION_TRACKING_NONE) {
                return "";
            }

            $checked = ($record->cmc_completionstate != 0) ? ['checked' => 'checked'] : [];
            $checkbox = html_writer::div(
                html_writer::empty_tag('input', [ 'type' => 'checkbox', 'class' => 'custom-control-input'] + $checked) .
                html_writer::tag('span', '', ['class' => 'custom-control-label']),
                'custom-control custom-switch',
                [
                    'data-cmid' => $record->cm_id,
                    'data-userid' => $record->u_id,
                    'data-state' => $record->cmc_completionstate,
                    'data-overrideuser' => $USER->id,
                    'data-cmcid' => $record->cmc_id,
                ],
            );
            $togglehbtn .= html_writer::link($PAGE->url, $checkbox, ['class' => 'activity-completion-override']);
        }
        return $togglehbtn;
    }
}
