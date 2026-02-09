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
 * Library functions defined for dashaddon activity completion.
 *
 * @package    dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use dashaddon_activity_completion\local\block_dash\data_grid\field\attribute\activity_grade_attribute;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * The require plugin dependencies added for the soft dependencies in the activities completion dash addon.
 *
 * @return string
 */
function dashaddon_activity_completion_extend_added_dependencies() {
    global $OUTPUT;
    $manager = \core_plugin_manager::instance();
    $dependencies = [
        'dashaddon_activities',
        'dashaddon_courses',
        'dashaddon_categories',
    ];
    foreach ($dependencies as $dependency) {
        $plugin = $manager->get_plugin_info($dependency);
        if (!$plugin) {
            return $OUTPUT->render_from_template('dashaddon_activity_completion/upgrade', ['plugin' => $dependency]);
        } else if ($plugin->get_status() === core_plugin_manager::PLUGIN_STATUS_MISSING) {
            return $OUTPUT->render_from_template('dashaddon_activity_completion/upgrade', ['plugin' => $dependency]);
        }
    }
    return '';
}

/**
 * Checks if the Time Management plugin is installed and enabled.
 *
 * @return bool True if the Time Management plugin is installed and enabled, false otherwise.
 */
function dashaddon_activity_completion_is_timetable_installed() {
    global $CFG;
    static $result;

    if ($result == null) {
        if (array_key_exists('timetable', \core_component::get_plugin_list('tool'))) {
            require_once($CFG->dirroot . '/admin/tool/timetable/classes/time_management.php');
            $result = true;
        } else {
            $result = false;
        }
    }

    return $result;
}

/**
 * Display the activity grade form to the fragment.
 *
 * @param array $args
 * @return string $gradeform.
 */
function dashaddon_activity_completion_output_fragment_grade_activity_form($args) {
    global $DB;
    [$course, $cm] = get_course_and_cm_from_cmid($args['cmid']);

    $params['gradeitemid'] = $args['gradeitemid'];
    $params['userid'] = $args['userid'];
    $params['courseid'] = $course->id;

    $gradeform = html_writer::start_tag('div', ['id' => 'activity-grade-action']);
    $mform = new activitygrade_form(null, $params);
    $gradeform .= $mform->render();
    $gradeform .= html_writer::end_tag('div');

    return $gradeform;
}

/**
 * Set a activity grade form.
 */
class activitygrade_form extends \moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        global $DB, $COURSE, $USER;
        $mform = $this->_form;

        $gradeitemid = !empty($this->_customdata['gradeitemid']) ? $this->_customdata['gradeitemid'] : 0;
        $courseid = !empty($this->_customdata['courseid']) ? $this->_customdata['courseid'] : $COURSE->id;
        $userid = !empty($this->_customdata['userid']) ? $this->_customdata['userid'] : $USER->id;

        $grade = new activity_grade_attribute();
        $gradeitemhtml = $grade->grade_panel_html($gradeitemid, $courseid, $userid);

        $mform->addElement('html', "<div class='activity-grade-panel row'>");
        $mform->addElement('html', "<div class='col-md-3'>");
        $mform->addElement('html', "<p> Grade </p>");
        $mform->addElement('html', "</div>");
        $mform->addElement('html', "<div class='col-md-9'>");
        $mform->addElement('html', $gradeitemhtml);
        $mform->addElement('html', "</div>");
        $mform->addElement('html', "</div>");
    }
}
