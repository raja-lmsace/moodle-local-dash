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
 * Enrolments widget class contains the layout information and generate the data for widget.
 *
 * @package    dashaddon_course_sections
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_sections\widget;

use block_dash\local\widget\abstract_widget;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\current_course_condition;
use block_dash\local\data_source\form\preferences_form;
use context_module;
use cm_info;
use html_writer;

/**
 * Course sections widget class contains the layout information and generate the data for widget.
 */
class sections_widget extends abstract_widget {
    /**
     * Check the datasource is widget.
     *
     * @return bool
     */
    public function is_widget() {
        return true;
    }

    /**
     * Get template file name to renderer.
     */
    public function get_mustache_template_name() {
        return 'dashaddon_course_sections/sections';
    }

    /**
     * Check the widget support uses the query method to build the widget.
     *
     * @return bool
     */
    public function supports_query() {
        return false;
    }

    /**
     * Layout class widget will use to render the widget content.
     *
     * @return \abstract_layout
     */
    public function layout() {
        return new sections_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'sections',
            'layout' => 'sections',
        ];
        return $preferences;
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {
        global $DB;

        $params = ['siteid' => SITEID, 'visible' => 1];
        [$conditionsql, $conditionparams] = $this->generate_course_sections_filter();

        $sql = "SELECT * FROM {course} c WHERE c.id <> :siteid $conditionsql AND visible = :visible ORDER BY c.sortorder ASC";
        $courses = $DB->get_records_sql($sql, $params + $conditionparams);

        if (empty($courses)) {
            return [];
        }

        $contents = [];

        foreach ($courses as $course) {
            $courseelement = (class_exists('\core_course_list_element'))
            ? new \core_course_list_element($course) : new \course_in_list($course);

            $contents[] = [
                'id' => $course->id,
                'fullname' => $courseelement->get_formatted_fullname(),
                'courseurl' => new \moodle_url('/course/view.php', ['id' => $course->id]),
                'coursecontent' => $this->get_course_section_contents($course),
            ];
        }
        $this->data = ['contents' => $contents];

        return $this->data;
    }

    /**
     * Get course section contents.
     *
     * @param stdClass $course
     * @return array
     */
    public function get_course_section_contents($course) {
        global $CFG, $DB, $USER, $PAGE;
        // Include library files.
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/externallib.php');
        // Retrieve the course.
        $course = $DB->get_record('course', ['id' => $course->id], '*', MUST_EXIST);

        if ($course->id != SITEID) {
            // Check course format exist.
            if (file_exists($CFG->dirroot . '/course/format/' . $course->format . '/lib.php')) {
                require_once($CFG->dirroot . '/course/format/' . $course->format . '/lib.php');
            }
        }

        $context = \context_course::instance($course->id);
        $canupdatecourse = true;

        // Create return value.
        $coursecontents = [];

        if ($canupdatecourse || $course->visible || has_capability('moodle/course:viewhiddencourses', $context)) {
            $modinfo = get_fast_modinfo($course);
            $modinfosections = $modinfo->get_sections();
            $sections = $modinfo->get_section_info_all();
            $courseformat = course_get_format($course);
            $coursenumsections = $courseformat->get_last_section_number();
            $completioninfo = new \completion_info($course);

            // Get the highlighted section marker.
            $highlightedsection = $courseformat->get_course()->marker;

            // Check if there are any sections.
            $hassections = count($sections) > 1; // More than 1 because the general section is always present.

            foreach ($sections as $key => $section) {
                $sectionvalues = [];
                $sectioncontents = [];
                $indexedrecords = [];
                $sectionvalues = [
                    'id' => $section->id,
                    'name' => get_section_name($course, $section),
                    'visible' => $section->visible,
                    'section' => $section->section,
                    'uservisible' => $section->uservisible,
                    'hiddenbynumsections' => $section->section > $coursenumsections ? 1 : 0,
                    'highlighted' => ($section->section == $highlightedsection) ? 1 : 0,
                    'notgeneral' => $section->section != 0 ? 1 : 0,
                    'expanded' => (!$hassections && $section->section == 0) ? 1 : 0,
                    'collapsible' => ($hassections || $section->section != 0),
                    'datatoggle' => $CFG->branch >= 500 ? 'data-bs-toggle' : 'data-toggle',
                    'datatarget' => $CFG->branch >= 500 ? 'data-bs-target' : 'data-target',
                ];

                if (!empty($section->availableinfo)) {
                    $sectionvalues['availabilityinfo'] = \core_availability\info::format_info($section->availableinfo, $course);
                }

                $options = (object) ['noclean' => true];

                [$sectionvalues['summary'], $sectionvalues['summaryformat']] =
                    external_format_text(
                        $section->summary,
                        $section->summaryformat,
                        $context->id,
                        'course',
                        'section',
                        $section->id,
                        $options
                    );

                if ($course->format == 'designer') {
                    $records = $DB->get_records('course_format_options', ['courseid' => $course->id, 'format' => 'designer',
                        'sectionid' => $section->id], '', 'id,name,value');

                    foreach ($records as $record) {
                        $indexedrecords[$record->name] = $record->value;
                    }

                    if (!empty($indexedrecords['categorisetitle'])) {
                        $sectionvalues['sectiontype'] = $indexedrecords['categorisetitle'] ?? '';
                    }

                    if (!empty($indexedrecords['categorisebackcolor'])) {
                        $sectionvalues['sectiontypebackcolor'] = $indexedrecords['categorisebackcolor'] ?? '';
                    }

                    if (!empty($indexedrecords['categorisetextcolor'])) {
                        $sectionvalues['sectiontypetextcolor'] = $indexedrecords['categorisetextcolor'] ?? '';
                    }

                    if (!empty($indexedrecords['sectionestimatetime'])) {
                        $time = get_string('strestimate', 'dashaddon_course_sections') . ": " .
                            $indexedrecords['sectionestimatetime'];
                        $sectionvalues['estimatedtime'] = html_writer::tag('p', $time, ['class' => 'estimatetime']);
                    }
                }

                if (!empty($modinfosections[$section->section])) {
                    foreach ($modinfosections[$section->section] as $cmid) {
                        $cm = $modinfo->cms[$cmid];
                        $cminfo = cm_info::create($cm);
                        // Stop here if the module is not visible to the user on the course main page:
                        // The user can't access the module and the user can't view the module on the course page.
                        if (!$cm->uservisible) {
                            continue;
                        }

                        // This becomes true when we are filtering and we found the value to filter with.
                        $modfound = false;
                        $module = [];
                        $modcontext = \context_module::instance($cm->id);

                        $module['id'] = $cm->id;
                        $module['name'] = external_format_string($cm->name, $modcontext->id);
                        $module['instance'] = $cm->instance;
                        $module['contextid'] = $modcontext->id;
                        $module['modname'] = (string) $cm->modname;
                        $module['modplural'] = (string) $cm->modplural;
                        $module['modicon'] = $cm->get_icon_url()->out(false);
                        $module['indent'] = $cm->indent;
                        $module['onclick'] = $cm->onclick;
                        $module['customdata'] = json_encode($cm->customdata);
                        $module['completion'] = $cm->completion;
                        $module['noviewlink'] = plugin_supports('mod', $cm->modname, FEATURE_NO_VIEW_LINK, false);

                        // Check module completion.
                        $completion = $completioninfo->is_enabled($cm);
                        if ($completion != COMPLETION_DISABLED) {
                            if (class_exists('\core_completion\cm_completion_details')) {
                                $cmcompletion = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
                                $exporter = new \core_completion\external\completion_info_exporter($course, $cm, $USER->id);
                                $renderer = $PAGE->get_renderer('core');
                                $modulecompletiondata = (array)$exporter->export($renderer);
                                $module['completiondata'] = $modulecompletiondata;
                            } else {
                                $data = $completioninfo->get_data($cm);
                                $module['completiondata']['state'] = $data->completionstate;
                            }
                        }

                        if (!empty($cm->showdescription) || $module['noviewlink']) {
                            // We want to use the external format. However from reading get_formatted_content(), $cm->content
                            // Format is always FORMAT_HTML.
                            $options = ['noclean' => true];
                            [$module['description'], $descriptionformat] = external_format_text(
                                $cm->content,
                                FORMAT_HTML,
                                $modcontext->id,
                                $cm->modname,
                                'intro',
                                $cm->id,
                                $options
                            );
                        }
                        // Url of the module.
                        $url = $cm->url;
                        if ($url) {
                            $module['url'] = $url->out(false);
                        } else {
                            $module['url'] = (new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]))->out(false);
                        }
                        $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $modcontext);
                        // User that can view hidden module should know about the visibility.
                        $module['visible'] = $cm->visible;
                        $module['visibleoncoursepage'] = $cm->visibleoncoursepage;
                        $module['uservisible'] = $cm->uservisible;
                        if (!empty($cm->availableinfo)) {
                            $module['availabilityinfo'] = \core_availability\info::format_info($cm->availableinfo, $course);
                        }
                        // Availability date (also send to user who can see hidden module).
                        if ($CFG->enableavailability && ($canviewhidden || $canupdatecourse)) {
                            $module['availability'] = $cm->availability;
                        }
                        $module['urlstatus'] = is_enrolled($context, $USER->id) ?? false;
                        $sectioncontents[] = $module;
                    }
                }

                $sectionvalues['modules'] = $sectioncontents;
                $sectionvalues['hidemodules'] = count($sectioncontents) > 0 ? false : true;
                $coursecontents[$key] = $sectionvalues;
            }

            foreach ($coursecontents as $sectionnumber => $sectioncontents) {
                $section = $sections[$sectionnumber];

                if ($CFG->branch >= 405) {
                    if ($section->component == 'mod_subsection') {
                        unset($coursecontents[$sectionnumber]);
                        continue;
                    }
                }

                // Check if this is the general section and has no activities.
                if ($section->section == 0 && empty($sectioncontents['modules'])) {
                    unset($coursecontents[$sectionnumber]); // Remove the general section if it has no activities.
                    continue;
                }

                if (!$section->visible) {
                    unset($coursecontents[$sectionnumber]);
                    continue;
                }
            }

            // Reset array keys after filtering hidden sections.
            $coursecontents = array_values($coursecontents);
        }
        return $coursecontents;
    }

    /**
     * Preference form for widget. We make the fields disable other than the general.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            $mform->addElement('static', 'data_source_name', get_string('datasource', 'block_dash'), $this->get_name());
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }

        $mform->addElement('html', get_string('fieldalert', 'block_dash'), 'fieldalert');
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_course_sections_filter() {

        $this->before_data();
        [$sql, $params] = $this->get_filter_collection()->get_sql_and_params();
        return $sql ? [" AND " . $sql[0], $params] : [];
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new course_condition('c_course', 'c.id'));
        $filtercollection->add_filter(new current_course_condition('current_course', 'c.id'));

        return $filtercollection;
    }
}
