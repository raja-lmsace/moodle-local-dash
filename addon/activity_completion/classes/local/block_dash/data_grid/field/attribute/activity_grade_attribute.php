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
 * Transform activity data into activity grade.
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
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/dash/addon/activity_completion/lib.php");
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_object.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/report/grader/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Activity grade action.
 *
 * @package dashaddon_activity_completion
 */
class activity_grade_attribute extends abstract_field_attribute {
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
        global $DB, $PAGE, $USER;

        $userid = $record->u_id;
        $context = \context_course::instance($record->c_id);
        $usercontext = \context_user::instance($userid);
        $cmid = $record->cm_id;
        $gradebutton = '';

        if (is_siteadmin($userid)) {
            return $gradebutton;
        }

        if (
            has_capability('moodle/grade:edit', $context, $USER->id) ||
            has_capability('dashaddon/activity_completion:editgrade', $usercontext, $USER->id)
        ) {
            [$course, $cm] = get_course_and_cm_from_cmid($cmid);

            // Set up completion object and check it is enabled.
            $completion = new \completion_info($course);
            if (!$completion->is_enabled()) {
                return $gradebutton;
            }

            if ($completion->is_tracked_user($userid)) {
                if ($record->gt_id != null) {
                    $gradebutton .= html_writer::link(
                        'javascript:void(0);',
                        get_string('grade', 'dashaddon_activity_completion'),
                        [   'class' => 'btn btn-secondary grade-activity-btn',
                        'data-userid' => $userid,
                        'data-cmid' => $cmid,
                        'data-contextid' => \context_system::instance()->id,
                        'data-currentgrade' => $record->gg_finalgrade ?? 0,
                        'data-gradeitemid' => ($record->gt_id != null) ? $record->gt_id : 0,
                        ]
                    );
                }
            }
            return $gradebutton;
        }
        return $gradebutton;
    }

    /**
     * Get the activity grade panel html form the course grades page.
     *
     * @param int $gradeitemid Grade item id.
     * @param int $courseid Course id.
     * @param int $userid User id.
     *
     * @return string
     */
    public function grade_panel_html($gradeitemid, $courseid, $userid) {
        global $DB, $CFG, $OUTPUT;

        $item = $DB->get_record('grade_items', ['id' => $gradeitemid]);
        $grade = $DB->get_record('grade_grades', ['itemid' => $gradeitemid, 'userid' => $userid]);
        $user = $DB->get_record('user', ['id' => $userid]);
        $graditemeobj = new \grade_item();
        $strgrade = get_string('gradenoun');

        $viewfullnames = has_capability('moodle/site:viewfullnames', \context_system::instance());
        $fullname = fullname($user, $viewfullnames);

        $context = new stdClass();
        $scaleid = $item->scaleid;

        $cache = \cache::make_from_params(\cache_store::MODE_REQUEST, 'gradereport_grader', 'scales');
        $scalesarray = $cache->get(get_class($this));
        if (!$scalesarray) {
            $scalesarray = $DB->get_record('scale', ['id' => $scaleid]);
            // Save to cache.
            $cache->set(get_class($this), $scalesarray);
        }

        // Grade value.
        $gradeval = $grade->finalgrade;

        // Get the decimal points preference for this item.
        $decimalpoints = $graditemeobj->get_decimals();

        if ($scaleid && $DB->record_exists('scale', ['id' => $scaleid])) {
            $context->scale = true;
            $scale = $DB->get_record('scale', ['id' => $scaleid]);

            $gradeval = (int)$gradeval; // Scales use only integers.
            $scales = explode(",", $scale->scale);

            // MDL-12104 some previous scales might have taken up part of the array
            // so this needs to be reset.
            $scaleopt = [];
            $i = 0;
            foreach ($scales as $scaleoption) {
                $i++;
                $scaleopt[$i] = $scaleoption;
            }

            $context->iseditable = true;
            if (empty($item->outcomeid)) {
                $nogradestr = get_string('nograde');
            } else {
                $nogradestr = get_string('nooutcome', 'grades');
            }
            $attributes = [
                'id' => 'grade_' . $userid . '_' . $item->id,
            ];
            $gradelabel = $fullname . ' ' . $item->itemname;

            $context->label = html_writer::label(
                get_string('useractivitygrade', 'gradereport_grader', $gradelabel),
                $attributes['id'],
                false,
                ['class' => 'accesshide']
            );
            $context->select = html_writer::select(
                $scaleopt,
                'grade[' . $userid . '][' . $item->id . ']',
                $gradeval,
                [-1 => $nogradestr],
                $attributes
            );
        } else if ($item->gradetype != GRADE_TYPE_TEXT) {
            // Value type.
            $context->iseditable = true;

            // Set this input field with type="number" if the decimal separator for current language is set to
            // a period. Other decimal separators may not be recognised by browsers yet which may cause issues
            // when entering grades.
            $decsep = get_string('decsep', 'core_langconfig');
            $context->isnumeric = $decsep === '.';
            // If we're rendering this as a number field, set min/max attributes, if applicable.
            if ($context->isnumeric) {
                $context->minvalue = $item->grademin ?? null;
                if (empty($CFG->unlimitedgrades)) {
                    $context->maxvalue = $item->grademax ?? null;
                }
            }

            $value = format_float($gradeval, $decimalpoints);
            $gradelabel = $fullname . ' ' . $item->itemname;

            $context->id = 'grade_' . $userid . '_' . $item->id;
            $context->name = 'grade[' . $userid . '][' . $item->id . ']';
            $context->value = $value;
            $context->label = get_string('useractivitygrade', 'gradereport_grader', $gradelabel);
            $context->title = $strgrade;
            $context->extraclasses = 'form-control';
        } else {
            if ($item->gradetype == GRADE_TYPE_TEXT && !empty($grade->feedback)) {
                $context->text = html_writer::span(
                    shorten_text(strip_tags($grade->feedback), 20),
                    '',
                    ['data-action' => 'feedback', 'role' => 'button', 'data-courseid' => $courseid]
                );
            }
        }

        return $OUTPUT->render_from_template('dashaddon_activity_completion/gradingpanel', $context);
    }
}
