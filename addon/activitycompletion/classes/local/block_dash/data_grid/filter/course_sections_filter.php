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
 * Filters results to specific course sections activity completion.
 *
 * @package    dashaddon_activitycompletion
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activitycompletion\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;
use block_dash\local\data_grid\filter\filter_collection_interface;

/**
 * Filters results to specific course sections activity completion status.
 */
class course_sections_filter extends select_filter {
    use filter_element;

    /**
     * Highlight option value.
     */
    const HIGHLIGHT_OPTION = -2;

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     *
     */
    public function init() {

        $choices = [];

        $this->add_all_option();
        $this->add_option(self::HIGHLIGHT_OPTION, get_string('highlightsection', 'block_dash'));

        foreach ($choices as $key => $option) {
            $this->options[$key] = $option;
        }

        parent::init();
    }

    /**
     * Add the "All" option to the filter.
     */
    public function add_all_option() {
        $this->add_option(self::ALL_OPTION, get_string('allcoursesections', 'block_dash'));
    }

    /**
     * Return a list of sections this filter can handle for the courses.
     *
     * @param array $courses Array of course IDs or course objects.
     * @return array
     */
    public static function generate_dynamic_options_list($courses) {
        global $DB;

        // Get all sections from the course.
        $courses = $courses ?: $DB->get_records('course', ['visible' => 1]);
        $choices = [];
        foreach ($courses as $course) {
            $sections = $DB->get_records('course_sections', ['course' => $course->id ?? $course]);
            foreach ($sections as $section) {
                if ($section->name == null) {
                    $sectionname = get_string('section') . " " . $section->section;
                } else {
                    $sectionname = $section->name;
                }

                $choices[$section->id] = $sectionname;
            }
        }

        return $choices;
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        [$sql, $params] = parent::get_sql_and_params();
        $inparams = [];

        if ($sql) {
            $selectedsection = $this->get_values();

            if ($selectedsection[0] == -2) {
                $modules = [];
                $courses = $DB->get_records_sql('SELECT * FROM {course} WHERE visible = 1 AND marker != 0');

                foreach ($courses as $course) {
                    $section = $DB->get_record("course_sections", ['course' => $course->id, 'section' => $course->marker]);
                    $coursemodules = $DB->get_records(
                        "course_modules",
                        ['section' => $section->id, 'course' => $course->id],
                        null,
                        'id, course'
                    );
                    foreach ($coursemodules as $cm) {
                        $modules[$cm->id] = [
                            'course' => $course->id,
                            'cmid' => $cm->id,
                            'section' => $section->id,
                        ];
                    }
                }

                if (empty($modules)) {
                    return false;
                }

                $cmids = array_keys($modules);
                [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);

                $sql = 'cm.id ' . $insql;
            } else {
                $modules = $DB->get_records("course_modules", ['section' => $selectedsection[0]], null, 'id, module');
                if (empty($modules)) {
                    return false;
                }

                $cmids = array_keys($modules);
                [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);

                $sql = 'cm.id ' . $insql;
            }
        }

        return [$sql, $params + $inparams];
    }

    /**
     * Override this method and call it after creating a form element.
     *
     * @param filter_collection_interface $filtercollection
     * @param string $elementnameprefix
     * @throws \Exception
     * @return string
     */
    public function create_form_element(
        filter_collection_interface $filtercollection,
        $elementnameprefix = ''
    ) {
        $filter = $filtercollection->get_filter('c_sections')->get_preferences();
        if (!empty($filter) && $filter['enabled']) {
            return $this->create_filter_element($filtercollection, $elementnameprefix);
        }
    }
}
