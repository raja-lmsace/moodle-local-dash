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
 * Learning path widget.
 *
 * @package    dashaddon_learningpath
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath\widget;

use block_dash\local\widget\abstract_widget;
use dashaddon_learningpath\widget\learningpath_layout;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use local_dash\data_grid\filter\course_category_condition;
use dashaddon_learningpath\local\block_dash\data_grid\filter\current_category_condition;
use dashaddon_learningpath\local\block_dash\data_grid\filter\course_prerequisites_condition;
use local_dash\data_grid\filter\tags_condition;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\date_filter;
use dashaddon_learningpath\local\block_dash\data_grid\filter\current_course_prerequisites_condition;
use dashaddon_learningpath\local\block_dash\data_grid\filter\assignment_tags_condition;
use local_dash\data_grid\filter\customfield_filter;
use block_dash\local\data_source\data_source_factory;
use MoodleQuickForm;

/**
 * Learning path widget.
 */
class learningpath_widget extends abstract_widget {
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
        return 'dashaddon_learningpath/widget_learningpath';
    }

    /**
     * Get the name of widget.
     *
     * @return void
     */
    public function get_name() {
        return get_string('widget:learningpath', 'block_dash');
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
        return new learningpath_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {

        $preferences = [
            'datasource' => 'learningpath',
            'layout' => 'learningpath',
        ];
        return $preferences;
    }

    /**
     * Widget data count.
     *
     * @return void
     */
    public function widget_data_count() {
        return isset($this->data['courses']) ? count($this->data['courses']) : 0;
    }

    /**
     * Check the learning path contains any data to render.
     *
     * @return bool
     */
    public function is_empty() {
        $this->build_widget();
        return isset($this->data['showlearningpath']) && $this->data['showlearningpath'] ? false : true;
    }

    /**
     * Get current page user. if block added in the profile page then the current profile user is releate user
     * Otherwise logged in user is current user.
     *
     * @return int $userid
     */
    public function get_current_userid() {
        global $PAGE, $USER;

        if ($PAGE->pagelayout == 'mypublic') {
            $userid = optional_param('id', 0, PARAM_INT);
        }

        return isset($userid) && $userid ? $userid : $USER->id;       // Owner of the page.
    }

    /**
     * Get available list of all activity mask images (SVGs) from block config and global settings.
     *
     * @param string $filearea
     * @return array $results List of mask images.
     */
    public function get_all_learning_paths($filearea): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $fs = get_file_storage();

        // System files.
        $results = [0 => get_string('none')];

        $systemfiles = $fs->get_area_files(
            \context_system::instance()->id,
            'dashaddon_learningpath',
            $filearea,
            0,
            '',
            false
        );

        foreach ($systemfiles as $file) {
            if (!$file->is_directory()) {
                $filename = $file->get_filename();
                $results[$filename] = ucwords(explode('.', $filename)[0]);
            }
        }

        // Block files.
        $blockinstance = $this->get_block_instance();
        if ($blockinstance) {
            $itemid = $blockinstance->config->orgparentblkcontextid ?? $blockinstance->context->id;
            $blockfiles = $fs->get_area_files(
                \context_system::instance()->id,
                'dashaddon_learningpath',
                'blk_' . $filearea,
                $itemid,
                '',
                false
            );

            foreach ($blockfiles as $file) {
                if (!$file->is_directory()) {
                    $filename = $file->get_filename();
                    $results[$filename] = ucwords(explode('.', $filename)[0]);
                }
            }
        }

        return $results;
    }

    /**
     * Get SVG file content for the given filename and filearea.
     *
     * It first checks the current block instance’s private files,
     * then falls back to system-wide SVGs if not found.
     *
     * @param string $filename
     * @param string $filearea
     * @return string SVG content or empty string if not found.
     */
    public function get_learningpath_svg(string $filename, string $filearea): string {
        $fs = get_file_storage();

        $blockinstance = $this->get_block_instance();
        $systemcontext = \context_system::instance();

        if ($blockinstance) {
            $itemid = $blockinstance->config->orgparentblkcontextid ?? $blockinstance->context->id;
            $file = $fs->get_file($systemcontext->id, 'dashaddon_learningpath', 'blk_' . $filearea, $itemid, '/', $filename);
            if ($file && !$file->is_directory()) {
                return $file->get_content();
            }
        }

        $file = $fs->get_file($systemcontext->id, 'dashaddon_learningpath', $filearea, 0, '/', $filename);

        if ($file && !$file->is_directory()) {
            return $file->get_content();
        }

        return '';
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {
        global $PAGE, $CFG, $USER, $DB;

        $this->data = [];

        $this->data['use_bs5'] = ($CFG->branch >= 500) ? true : false;

        static $jsincluded = false;

        // Current userid.
        $userid = $this->get_current_userid();
        $isgrid = true;

        if ($this->get_preferences('desktoppath')) {
            $this->data['pathdesktop'] = $this->get_learningpath_svg($this->get_preferences('desktoppath'), 'desktop_learningpath');
        }
        if ($this->get_preferences('tabletpath')) {
            $this->data['pathtablet'] = $this->get_learningpath_svg($this->get_preferences('tabletpath'), 'tablet_learningpath');
        }
        if ($this->get_preferences('mobilepath')) {
            $this->data['pathmobile'] = $this->get_learningpath_svg($this->get_preferences('mobilepath'), 'mobile_learningpath');
        }

        $this->data['isgrid'] = $isgrid;
        $this->data['pagecontextid'] = $PAGE->context->id;

        [$courses, $completedcourses, $nextcourse] = $this->get_possible_completion_courses();

        if (!isset($courses) || empty($courses)) {
            return false;
        }

        $dotimg = false;
        $imgsize = $this->get_preferences('courseimgsize');
        if (!$imgsize) {
            $imgsize = get_config('block_dash', 'defaultcourseimgsize');
        }
        if ($imgsize == "tiny") {
            $courseimgwidth = "35";
            $courseimgheight = "35";
        } else if ($imgsize == "small") {
            $courseimgwidth = "50";
            $courseimgheight = "50";
        } else if ($imgsize == "medium") {
            $courseimgwidth = "75";
            $courseimgheight = "75";
        } else if ($imgsize == "large") {
            $courseimgwidth = "100";
            $courseimgheight = "100";
        } else if ($imgsize == "extralarge") {
            $courseimgwidth = "150";
            $courseimgheight = "150";
        } else {
            $dotimg = true;
            $courseimgwidth = "20";
            $courseimgheight = "20";
        }

        $dataset = [];
        $dataset['imgsize'] = $imgsize;
        $dataset['blockid'] = $this->get_block_instance()->instance->id;
        $dataset['dotimg'] = $dotimg;
        $dataset['courseimgwidth'] = $courseimgwidth;
        $dataset['courseimgheight'] = $courseimgheight;
        $dataset['coursecircleradius'] = $courseimgwidth / 2;
        $dataset['courses'] = array_values($courses);
        $dataset['startelement'] = $this->get_preferences('startelement');
        $dataset['finishelement'] = $this->get_preferences('finishelement');
        $dataset['infoarea'] = $this->get_preferences('infoarea');
        $dataset['positioning'] = $this->get_preferences('positioning');
        $dataset['totalcourses'] = count($courses);
        $dataset['completedcourses'] = $completedcourses;
        $dataset['nextcourse'] = isset($nextcourse->fullname) ? format_string($nextcourse->fullname) : '';
        $dataset['defaultshape'] = $this->get_preferences('courseshape') ?:
            get_config('dashaddon_learningpath', 'defaultcourseshape') ?: 'circle';

        // Add coursevisual to dataset for JavaScript.
        $visual = $this->get_preferences('coursevisual') ?: get_config('dashaddon_learningpath', 'defaultcoursevisual');
        if ($dotimg) {
            $visual = null;
        }
        $dataset['coursevisual'] = $visual;
        $dataset['isvisualnumber'] = ($visual === 'number');

        // Load zone configurations if positioning is set to zones.
        if ($dataset['positioning'] === 'zones') {
            $blockid = $this->get_block_instance()->instance->id;
            $zoneconfigs = $DB->get_records(
                'dashaddon_learningpath_zones',
                ['blockid' => $blockid],
            );

            // Group zone configurations by viewport.
            $zoneconfigsbyviewport = [
                'desktop' => [],
                'tablet' => [],
                'mobile' => [],
            ];

            foreach ($zoneconfigs as $zone) {
                // Extract viewport from zoneid using regex.
                $viewportpattern = "/^zone_([a-z]+)_/";
                $viewport = 'desktop'; // Default fallback.

                if (preg_match($viewportpattern, $zone->zoneid, $matches)) {
                    $detectedviewport = $matches[1];
                    // Validate that it's a known viewport.
                    if (in_array($detectedviewport, ['desktop', 'tablet', 'mobile'])) {
                        $viewport = $detectedviewport;
                    }
                }

                // Add zone to the appropriate viewport array.
                $zoneconfigsbyviewport[$viewport][] = $zone;
            }

            // Process each viewport's zones.
            $dataset['zoneconfigs'] = [];
            foreach ($zoneconfigsbyviewport as $viewport => $zones) {
                if (!empty($zones)) {
                    // Convert to array for easier indexing.
                    $zoneconfigsarray = array_values($zones);

                    // Process zones for this viewport.
                    $dataset['zoneconfigs'][$viewport] = array_values(
                        array_map(
                            function ($zone, $index) use ($zoneconfigsarray, $viewport) {
                                $prevcourseid = 0;
                                $nextcourseid = 0;

                                // Get previous course ID.
                                if ($index > 0) {
                                    $prevcourseid = (int)$zoneconfigsarray[$index - 1]->courseid;
                                }

                                // Get next course ID.
                                if ($index < count($zoneconfigsarray) - 1) {
                                    $nextcourseid = (int)$zoneconfigsarray[$index + 1]->courseid;
                                }

                                return [
                                    'id' => $zone->id,
                                    'blockid' => $zone->blockid,
                                    'zoneid' => $zone->zoneid,
                                    'zonetype' => $zone->zonetype,
                                    'zoneindex' => (int)$zone->zoneindex,
                                    'courseid' => (int)$zone->courseid,
                                    'enabled' => (int)$zone->enabled,
                                    'viewport' => $viewport, // Add viewport information.
                                    'prevcourse' => $prevcourseid,
                                    'nextcourse' => $nextcourseid,
                                ];
                            },
                            $zoneconfigsarray,
                            array_keys($zoneconfigsarray)
                        )
                    );
                } else {
                    // Empty array for viewports with no zones.
                    $dataset['zoneconfigs'][$viewport] = [];
                }
            }
        } else {
            // For path-based positioning, set empty arrays for all viewports.
            $dataset['zoneconfigs'] = [
                'desktop' => [],
                'tablet' => [],
                'mobile' => [],
            ];
        }

        $dataset['strings'] = [
            'start' => get_string('learningpathstart', 'block_dash'),
            'finish' => get_string('learningpathfinish', 'block_dash'),
        ];
        if ($isgrid) {
            $this->data += $dataset;
            $this->data['dataset'] = json_encode($dataset);
        } else {
            $this->data['dataset'] = json_encode($dataset);
        }

        $this->data['zoneposition'] = $this->get_preferences('positioning') == 'zones' ? true : false;

        $this->data['infoarea'] = $this->get_preferences('infoarea');
        $this->data['blockid'] = $this->get_block_instance()->instance->id;

        // Info area: Top.
        $this->data['showinfotop'] = ($this->get_preferences('infoarea') &&
            $this->get_preferences('infoareaposition') == 'top') ? true : false;

        // Info area: Side bar.
        $this->data['showinfosidebar'] = ($this->get_preferences('infoarea') &&
            $this->get_preferences('infoareaposition') == 'sidebar') ? true : false;

        // Build info area data using info_area class.
        $this->data['showkpi'] = $this->get_preferences('infoarea');
        $infoarea = new \dashaddon_learningpath\info_area($this);
        $infodata = $infoarea->build_data($courses, $completedcourses, count($courses), array_keys($courses));
        $this->data = array_merge($this->data, $infodata);

        $this->data['showlearningpath'] = count($courses) > 0 ? true : false;
        $stringvar = [
            'completed' => $completedcourses,
            'total' => count($courses),
            'nextcourse' => isset($nextcourse->fullname) ? format_string($nextcourse->fullname) : '',
        ];

        $statuses = [
            'completed'   => '#11b56a',
            'inprogress'  => '#00b2ff',
            'unavailable' => '#CBCBCB',
            'notstarted'  => '#00008b',
            'available'   => '#808080',
            'failed'      => '#ff0000',
        ];

        foreach ($statuses as $status => $default) {
            $widgetcolor = $this->get_preferences($status . 'circlecolor');
            $generalcolor = get_config('dashaddon_learningpath', $status . 'circlecolor');
            $this->data[$status . 'circlecolor'] = $widgetcolor ?: $generalcolor ?: $default;
        }

        $imgsize = $this->get_preferences('courseimgsize') ?: get_config('block_dash', 'defaultcourseimgsize');
        $dotimg = ($imgsize === 'dot');
        $visual = $this->get_preferences('coursevisual') ?: get_config('dashaddon_learningpath', 'defaultcoursevisual');
        if ($dotimg) {
            $visual = null;
        }
        $this->data['isvisualnone'] = ($visual === 'none');
        $this->data['isvisualnumber'] = ($visual === 'number');
        $this->data['coursevisual'] = $visual;

        $completedlearingpath = $completedcourses / count($courses) == 1;
        $this->data['completedlearingpath'] = $completedlearingpath;
        $this->data['nextcourseurl'] = ($nextcourse) ? new \moodle_url('/course/view.php', ['id' => $nextcourse->id]) : '#';

        if (!$completedlearingpath) {
            $this->data['infocontent'] = get_string('leanringpath_infocontent', 'block_dash', $stringvar);
            $this->data['infobutton'] = get_string('resumelearningpath', 'block_dash');
            $this->data['helpbutton'] = get_string('testhelpbutton', 'block_dash');
        } else {
            $this->data['infocontent'] = get_string('completedlearningpath', 'block_dash');
        }
        return $this->data;
    }

    /**
     * Get completed courses count for a given course.
     *
     * @param int $courseid The course ID.
     * @return int The count of completed courses.
     */
    public function get_completed_courses_count($courseid) {
        [$courses, $completedcourses] = $this->get_possible_completion_courses();
        return $completedcourses;
    }


    /**
     * Get the icon class for a course.
     *
     * @param int $courseid The course ID.
     * @return string The icon class.
     */
    public function get_course_iconclass($courseid) {
        $coursevisual = $this->get_preferences('coursevisual');

        if ($coursevisual !== 'custom') {
            return null;
        }

        $visualfieldid = get_config('local_dash', 'customvisualfield');

        if (empty($visualfieldid) || $visualfieldid == 0) {
            return null;
        }

        // Get custom field value for this course.
        if (!class_exists('\core_course\customfield\course_handler')) {
            return null;
        }

        $handler = \core_course\customfield\course_handler::create();
        $datas = $handler->get_instance_data($courseid, true);

        foreach ($datas as $data) {
            $field = $data->get_field();
            if ($field->get('id') == $visualfieldid) {
                $value = $data->get_value();

                if ($value === '' || $value === null) {
                    return null;
                }

                $configdata = $field->get('configdata');

                if (!empty($configdata)) {
                    $config = is_array($configdata) ? $configdata : json_decode($configdata, true);

                    if (isset($config['options'])) {
                        $options = explode("\n", trim($config['options']));
                        $options = array_values(array_filter(array_map('trim', $options)));

                        $valueindex = null;

                        if (is_numeric($value)) {
                            $valueindex = ((int) $value) - 1;
                        } else {
                            foreach ($options as $index => $option) {
                                if (strcasecmp(trim($option), trim($value)) === 0) {
                                    $valueindex = $index;
                                    break;
                                }
                            }
                        }

                        if ($valueindex !== null && isset($options[$valueindex])) {
                            $displayindex = $valueindex + 1;
                            $mappingkey = 'customvisualicon_' . $visualfieldid . '_' . $displayindex;
                            $mappedshape = $this->get_preferences($mappingkey);

                            if (empty($mappedshape) || $mappedshape === '') {
                                $mappedshape = get_config('local_dash', $mappingkey);
                            }

                            if ($mappedshape !== null && $mappedshape !== '') {
                                return $mappedshape;
                            }
                        }
                    }
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Get the shape for a specific course based on custom field value.
     *
     * @param int $courseid Course ID
     * @return string|null Mapped shape name
     */
    public function get_course_shape($courseid) {
        // When courseimgsize is 'dot', don't use shapes - only circles.
        $courseimgsize = $this->get_preferences('courseimgsize');
        if (empty($courseimgsize)) {
            $courseimgsize = get_config('block_dash', 'defaultcourseimgsize');
        }
        if ($courseimgsize === 'dot') {
            return null;
        }

        $courseshape = $this->get_preferences('courseshape');

        if ($courseshape !== 'custom') {
            return $courseshape;
        }

        $shapefieldid = get_config('local_dash', 'customselectfield');

        if (empty($shapefieldid) || $shapefieldid == 0) {
            return null;
        }

        // Get custom field value for this course.
        if (!class_exists('\core_course\customfield\course_handler')) {
            return null;
        }

        $handler = \core_course\customfield\course_handler::create();
        $datas = $handler->get_instance_data($courseid, true);

        foreach ($datas as $data) {
            $field = $data->get_field();
            if ($field->get('id') == $shapefieldid) {
                $value = $data->get_value();

                if ($value === '' || $value === null) {
                    return null;
                }

                $configdata = $field->get('configdata');
                if (!empty($configdata)) {
                    $config = is_array($configdata) ? $configdata : json_decode($configdata, true);

                    if (isset($config['options'])) {
                        $options = explode("\n", trim($config['options']));
                        $options = array_values(array_filter(array_map('trim', $options)));

                        $valueindex = null;

                        if (is_numeric($value)) {
                            $valueindex = ((int) $value) - 1;
                        } else {
                            foreach ($options as $index => $option) {
                                if (strcasecmp(trim($option), trim($value)) === 0) {
                                    $valueindex = $index;
                                    break;
                                }
                            }
                        }

                        if ($valueindex !== null && isset($options[$valueindex])) {
                            $displayindex = $valueindex + 1;
                            $mappingkey = 'shapemap_' . $shapefieldid . '_' . $displayindex;
                            $mappedshape = $this->get_preferences($mappingkey);

                            if (empty($mappedshape) || $mappedshape === '') {
                                $mappedshape = get_config('local_dash', $mappingkey);
                            }

                            if ($mappedshape !== null && $mappedshape !== '') {
                                return $mappedshape;
                            }
                        }
                    }
                }

                return null;
            }
        }

        // Field not found for this course - use default shape.
        return null;
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_learningpath_filter() {
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

        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $filtercollection->add_filter(new current_category_condition('current_category', 'c.category'));

        $filtercollection->add_filter(new tags_condition(
            'course_tags',
            'c.id',
            'core',
            'course',
            get_string('coursetags', 'block_dash')
        ));

        $filtercollection->add_filter(new course_prerequisites_condition('course_prerequisites', 'c.id'));

        $filtercollection->add_filter(new current_course_prerequisites_condition('current_course_prerequisites', 'c.id'));

        // Only add assignment tags condition if tool_timetable plugin is installed.
        $manager = \core_plugin_manager::instance();
        $plugin = $manager->get_plugin_info('tool_timetable');
        if ($plugin && $plugin->get_status() !== \core_plugin_manager::PLUGIN_STATUS_MISSING) {
            $filtercollection->add_filter(new assignment_tags_condition(
                'assignment_tags',
                'c.id',
                get_string('assignmenttags', 'block_dash')
            ));
        }

        local_dash_customfield_conditions($filtercollection);
        return $filtercollection;
    }

    /**
     * Fetch the accessible courses based on the conditions and process the data to create doughnut chart using moodle chart api
     *
     * @return array
     */
    public function get_possible_completion_courses() {
        global $DB, $USER;

        [$conditionsql, $params] = $this->generate_learningpath_filter();
        $orderfield = "c.id";
        $orderby = "ASC";
        $preferorderfield = $this->get_preferences('orderby');
        $ordercustom = '';
        if ($preferorderfield) {
            if ($preferorderfield == 'custom') {
                $customvalues = $this->get_preferences('customorder');
                if (!empty($customvalues)) {
                    $customvalues = explode(",", $customvalues);
                    $ordercustom = "CASE ";
                    $i = 1;
                    foreach ($customvalues as $value) {
                        $ordercustom .= "WHEN c.id = $value THEN $i ";
                        $i++;
                    }
                    $ordercustom .= "ELSE 4 END,";
                }
                $orderfield = 'c.id';
            } else {
                $orderfield = $preferorderfield;
            }
        }
        $preferorderby = $this->get_preferences('orderdirection');
        if ($preferorderby) {
            $orderby = $preferorderby;
        }
        $courselimit = $this->get_preferences('limit');

        $endsql = "ORDER BY $ordercustom $orderfield $orderby";
        if ($courselimit) {
            $endsql .= " LIMIT $courselimit";
        }

        $preferencesfilter = $this->get_preferences('filters');

        $courses = [];

        $sql = "SELECT c.id, c.fullname, c.category
            FROM {course} c
            JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel ";
        if (class_exists('\core_course\customfield\course_handler')) {
            $coursehandler = \core_course\customfield\course_handler::create();
            foreach ($coursehandler->get_fields() as $field) {
                $alias = 'c_f_' . strtolower($field->get('shortname'));
                if (isset($preferencesfilter[$alias]) && $preferencesfilter[$alias]['enabled']) {
                    $alias = 'c_f_' . strtolower($field->get('shortname'));
                    $sql .= "LEFT JOIN {customfield_data} $alias $alias.instanceid = c.id AND $alias.fieldid = $field->get('id')";
                }
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            foreach ($DB->get_records('course_info_field') as $field) {
                $alias = 'c_f_' . strtolower($field->shortname);
                // Only join custom field table if the filter is enabled.
                if (isset($preferencesfilter[$alias]) && $preferencesfilter[$alias]['enabled']) {
                    $sql .= $sql .= "LEFT JOIN {course_info_field} $alias $alias.courseid = c.id AND $alias.fieldid =
                        $field->get('id')";
                }
            }
        }
        $sql .= "WHERE c.id > 1 AND c.visible = 1 $conditionsql $endsql";

        $params['userid'] = $this->get_current_userid();
        $params['contextlevel'] = CONTEXT_COURSE; // Course context level.
        $recordset = $DB->get_recordset_sql($sql, $params);

        foreach ($recordset as $record) {
            $courses[$record->id]['info'] = [
                'id' => $record->id,
                'fullname' => format_string($record->fullname),
                'url' => new \moodle_url('/course/view.php', ['id' => $record->id]),
            ];
        }

        $completedcourses = 0;
        $updatenextstartcourse = false;
        $coursesinfo = array_values($courses);
        $i = 0;
        array_walk($courses, function (&$course) use (&$completedcourses, &$updatenextstartcourse, &$i, $coursesinfo) {
            global $OUTPUT;
            $report = dashaddon_learningpath_generate_completion_stats($course['info']['id'], $this->get_current_userid());
            $course['report'] = $report;
            if ($report['completed']) {
                $completedcourses += 1;
            } else {
                if (!$updatenextstartcourse) {
                    $course['nextstartcourse'] = true;
                    $updatenextstartcourse = $course['info']['id'];
                }
            }

            // Current status.
            if (!empty($report['unavailable'])) {
                $completionstatus = 'unavailable';
            } else if (!empty($report['available'])) {
                $completionstatus = 'available';
            } else if (!empty($report['failed'])) {
                $completionstatus = 'failed';
            } else if (!empty($report['completed'])) {
                $completionstatus = 'completed';
            } else if (!empty($report['inprogress'])) {
                $completionstatus = 'inprogress';
            } else {
                $completionstatus = 'notstarted';
            }

            $statuses = [
                'completed'   => '#11b56a',
                'inprogress'  => '#00b2ff',
                'unavailable' => '#CBCBCB',
                'notstarted'  => '#00008b',
                'available'   => '#808080',
                'failed'      => '#ff0000',
            ];

            $course['completionstatus'] = $completionstatus;
            $course['completionpercentage'] = isset($report['completionpercentage']) ? (int) $report['completionpercentage'] : 0;
            $course['img'] = dashaddon_learningpath_courseimage($course['info']['id']);
            $course['shape'] = $this->get_course_shape($course['info']['id']);
            $course['iconclass'] = $this->get_course_iconclass($course['info']['id']);
            if (!empty($course['iconclass'])) {
                $course['icon'] = $this->get_course_icon($course['iconclass']);
            }

            $widgetcolor = $this->get_preferences($completionstatus . 'circlecolor');
            $generalcolor = get_config('dashaddon_learningpath', $completionstatus . 'circlecolor');

            $course['statuscolor'] = $widgetcolor ?: $generalcolor ?: $statuses[$completionstatus];
            // Make the enrollments empty to prevent the data limit reach issue for JS.
            $course['enrollments'] = [];
            // Set the nextcourse and prevcourse.
            $course['prevnavcourse'] = 0;
            if ($i > 0) {
                $course['prevnavcourse'] = $coursesinfo[$i - 1]['info']['id'];
            }

            $course['nextnavcourse'] = 0;
            if ($i < count($coursesinfo) - 1) {
                $course['nextnavcourse'] = $coursesinfo[$i + 1]['info']['id'];
            }
            $i++;
        });

        // Course order number.
        $order = 1;
        foreach ($courses as $key => $course) {
            $courses[$key]['coursenumber'] = $order++;
        }

        $nextcourse = '';
        if ($updatenextstartcourse) {
            $nextcourse = get_course($updatenextstartcourse);
        }

        return [$courses, $completedcourses, $nextcourse];
    }

    /**
     * Get the course icon based on the icon class.
     *
     * @param string $iconclass The icon class.
     * @return string The rendered icon.
     */
    public function get_course_icon($iconclass) {
        global $OUTPUT;
            $icon = explode(':', $iconclass);
            $iconstr = isset($icon[1]) ? $icon[1] : 'moodle';
            $component = isset($icon[0]) ? $icon[0] : '';
            // Render the pix icon.
            return $OUTPUT->pix_icon($iconstr, '', $component);
    }

    /**
     * Set the default configurations of the learning path, Make the infoarea, startelements, detailsarea are enabled by default.
     *
     * @param array $data
     * @return void
     */
    public function set_default_preferences(&$data) {
        $data['config_preferences']['infoarea'] = get_config('dashaddon_learningpath', 'infoarea') ?: 0;
        $data['config_preferences']['startelement'] = 1;
        $data['config_preferences']['finishelement'] = 1;
    }

    /**
     * Prefence form for widget. We make the fields disable other than the general.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        global $CFG, $PAGE;
        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            $mform->addElement('static', 'data_source_name', get_string('datasource', 'block_dash'), $this->get_name());
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }

        if ($form->get_tab() == preferences_form::TAB_FIELDS) {
            $mform->addElement('html', '<hr>');

            // Build info area form fields using info_area class.
            $infoarea = new \dashaddon_learningpath\info_area($this);
            $infoarea->build_form_fields($mform);

            $mform->addElement('html', '<hr>');

            // Add positioning field.
            $positioningoptions = [
                'path' => get_string('positioning_path', 'block_dash'),
                'zones' => get_string('positioning_zones', 'block_dash'),
            ];

            $mform->addElement(
                'select',
                'config_preferences[positioning]',
                get_string('field:positioning', 'block_dash'),
                $positioningoptions
            );
            $mform->setType('config_preferences[positioning]', PARAM_TEXT);
            $mform->setDefault('config_preferences[positioning]', 'path');
            $mform->addHelpButton('config_preferences[positioning]', 'field:positioning', 'block_dash');

            // Configure zones button (only shown when zones is selected).
            $configurezonesbtn = \html_writer::tag(
                'button',
                get_string('configure_zones', 'block_dash'),
                [
                    'type' => 'button',
                    'class' => 'btn btn-secondary',
                    'data-action' => 'configure-zones',
                    'data-blockid' => $this->get_block_instance()->instance->id,
                ]
            );
            $zoneuniqueid = uniqid("zone-click-action");

            $mform->addElement(
                'static',
                'configure_zones_wrapper',
                '',
                \html_writer::tag('div', $configurezonesbtn, ['id' => $zoneuniqueid])
            );

            // Hide configure zones button when positioning is not set to zones.
            $mform->hideIf('configure_zones_wrapper', 'config_preferences[positioning]', 'neq', 'zones');

            $desktoppaths = $this->get_all_learning_paths('desktop_learningpath');
            $mform->addElement(
                'select',
                'config_preferences[desktoppath]',
                get_string('field:learningpathdesktop', 'block_dash'),
                $desktoppaths
            );
            $mform->setType('config_preferences[desktoppath]', PARAM_TEXT);

            $tabletpaths = $this->get_all_learning_paths('tablet_learningpath');
            $mform->addElement(
                'select',
                'config_preferences[tabletpath]',
                get_string('field:learningpathtablet', 'block_dash'),
                $tabletpaths
            );
            $mform->setType('config_preferences[tabletpath]', PARAM_TEXT);

            $mobilepaths = $this->get_all_learning_paths('mobile_learningpath');
            $mform->addElement(
                'select',
                'config_preferences[mobilepath]',
                get_string('field:learningpathmobile', 'block_dash'),
                $mobilepaths
            );
            $mform->setType('config_preferences[mobilepath]', PARAM_TEXT);

            $courseimgsizes = [
                'dot' => get_string('dot', 'block_dash'),
                'tiny' => get_string('tinyimage', 'block_dash'),
                'small' => get_string('smallimage', 'block_dash'),
                'medium' => get_string('mediumimage', 'block_dash'),
                'large' => get_string('largeimage', 'block_dash'),
                'extralarge' => get_string('extralargeimage', 'block_dash'),
            ];

            // Course image size.
            $mform->addElement(
                'select',
                'config_preferences[courseimgsize]',
                get_string('field:courseimgsize', 'block_dash'),
                $courseimgsizes
            );
            $mform->setType('config_preferences[courseimgsize]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[courseimgsize]', 'field:courseimgsize', 'block_dash');
            $mform->setDefault('config_preferences[courseimgsize]', get_config('block_dash', 'defaultcourseimgsize'));
            $mform->hideIf('config_preferences[courseimgsize]', 'config_preferences[positioning]', 'eq', 'zones');

            $globalfieldid = get_config('local_dash', 'customselectfield');
            $globalfieldname = '';

            // Get the field name if customselectfield is configured.
            if ($globalfieldid && $globalfieldid != 0 && class_exists('\core_course\customfield\course_handler')) {
                $handler = \core_course\customfield\course_handler::create();
                $fields = $handler->get_fields();
                foreach ($fields as $field) {
                    if ($field->get('id') == $globalfieldid) {
                        $globalfieldname = $field->get('name');
                        $configdata = $field->get('configdata');
                        if (!empty($configdata)) {
                            $config = is_array($configdata) ? $configdata : json_decode($configdata, true);
                            if (isset($config['options'])) {
                                $options = explode("\n", trim($config['options']));
                                $fieldoptionsmap[$globalfieldid] = [
                                    'name' => $field->get('name'),
                                    'options' => array_values(array_filter(array_map('trim', $options))),
                                ];
                            }
                        }
                        break;
                    }
                }
            }

            $shapes = [
                'circle'   => get_string('shape:circle', 'block_dash'),
                'triangle' => get_string('shape:triangle', 'block_dash'),
                'hexagon'  => get_string('shape:hexagon', 'block_dash'),
                'diamond'  => get_string('shape:diamond', 'block_dash'),
                'star'     => get_string('shape:star', 'block_dash'),
            ];

            if (!empty($globalfieldname)) {
                $shapes['custom'] = get_string('shape:custom', 'block_dash', $globalfieldname);
            }

            $shapes['svgshape'] = get_string('shape:svgshape', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[courseshape]',
                get_string('field:courseshape', 'block_dash'),
                $shapes
            );
            $mform->setType('config_preferences[courseshape]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[courseshape]', 'field:courseshape', 'block_dash');
            $defaultshape = get_config('dashaddon_learningpath', 'defaultcourseshape');
            $mform->setDefault('config_preferences[courseshape]', $defaultshape);
            $mform->hideIf('config_preferences[courseshape]', 'config_preferences[courseimgsize]', 'eq', 'dot');

            $globalfieldid = get_config('local_dash', 'customvisualfield');
            $globalfieldname = '';

            // Get the field name if customselectfield is configured.
            if ($globalfieldid && $globalfieldid != 0 && class_exists('\core_course\customfield\course_handler')) {
                $handler = \core_course\customfield\course_handler::create();
                $fields = $handler->get_fields();

                foreach ($fields as $field) {
                    if ($field->get('id') == $globalfieldid) {
                        $globalfieldname = $field->get('name');
                        break;
                    }
                }
            }

            $visualoptions = [
                'none'       => get_string('visual:none', 'block_dash'),
                'number'     => get_string('visual:number', 'block_dash'),
                'courseimg'  => get_string('visual:courseimg', 'block_dash'),
            ];

            if (!empty($globalfieldname)) {
                $visualoptions['custom'] = get_string('visual:custom', 'block_dash', $globalfieldname);
            }

            // Visual dropdown.
            $mform->addElement(
                'select',
                'config_preferences[coursevisual]',
                get_string('field:coursevisual', 'block_dash'),
                $visualoptions
            );
            $mform->setType('config_preferences[coursevisual]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[coursevisual]', 'field:coursevisual', 'block_dash');
            $defaultvisual = get_config('dashaddon_learningpath', 'defaultcoursevisual');
            $mform->setDefault('config_preferences[coursevisual]', $defaultvisual);
            $mform->hideIf('config_preferences[coursevisual]', 'config_preferences[courseimgsize]', 'eq', 'dot');

            $mform->addElement(
                'advcheckbox',
                'config_preferences[startelement]',
                get_string('field:startelement', 'block_dash'),
                '',
                [0, 1]
            );
            $mform->addHelpButton('config_preferences[startelement]', 'field:startelement', 'block_dash');
            $mform->hideIf('config_preferences[startelement]', 'config_preferences[positioning]', 'eq', 'zones');

            $mform->addElement(
                'advcheckbox',
                'config_preferences[finishelement]',
                get_string('field:finishelement', 'block_dash'),
                '',
                [0, 1]
            );
            $mform->addHelpButton('config_preferences[finishelement]', 'field:finishelement', 'block_dash');
            $mform->hideIf('config_preferences[finishelement]', 'config_preferences[positioning]', 'eq', 'zones');

            $orderbyoptions = [
                'c.id' => get_string('menu', 'block_dash'),
                'c.shortname' => get_string('courseshortname', 'block_dash'),
                'c.fullname' => get_string('coursefullname', 'block_dash'),
                'c.idnumber' => get_string('courseidnumber', 'block_dash'),
                'c.startdate' => get_string('coursestartdate', 'block_dash'),
                'custom' => get_string('field:customorder', 'block_dash'),
            ];

            // Order by.
            $mform->addElement(
                'select',
                'config_preferences[orderby]',
                get_string('field:orderby', 'block_dash'),
                $orderbyoptions
            );
            $mform->addHelpButton('config_preferences[orderby]', 'field:orderby', 'block_dash');
            $mform->setType('config_preferences[orderby]', PARAM_TEXT);
            $mform->hideIf('config_preferences[orderby]', 'config_preferences[positioning]', 'eq', 'zones');

            $mform->addElement('text', 'config_preferences[customorder]', get_string(
                'field:customorder',
                'block_dash'
            ));
            $mform->addHelpButton('config_preferences[customorder]', 'field:customorder', 'block_dash');
            $mform->hideIf('config_preferences[customorder]', 'config_preferences[orderby]', 'neq', 'custom');
            $mform->setType('config_preferences[customorder]', PARAM_TEXT);
            $mform->hideIf('config_preferences[customorder]', 'config_preferences[positioning]', 'eq', 'zones');

            $orderbyoptions = [
                'ASC' => get_string('asc', 'block_dash'),
                'DESC' => get_string('desc', 'block_dash'),
            ];

            // Order direction.
            $mform->addElement(
                'select',
                'config_preferences[orderdirection]',
                get_string('field:orderdirection', 'block_dash'),
                $orderbyoptions
            );
            $mform->addHelpButton('config_preferences[orderdirection]', 'field:orderdirection', 'block_dash');
            $mform->setType('config_preferences[orderdirection]', PARAM_TEXT);
            $mform->hideIf('config_preferences[orderdirection]', 'config_preferences[positioning]', 'eq', 'zones');

            $mform->addElement('text', "config_preferences[limit]", get_string('field:limit', 'block_dash'));
            $mform->setType('config_preferences[limit]', PARAM_INT);
            $mform->addHelpButton('config_preferences[limit]', 'field:limit', 'block_dash');
            $mform->addRule('config_preferences[limit]', null, 'numeric', null, 'client');
            $mform->hideIf('config_preferences[limit]', 'config_preferences[positioning]', 'eq', 'zones');

            require_once($CFG->dirroot . '/blocks/dash/form/element-colorpicker.php');

            MoodleQuickForm::registerElementType(
                'dashcolorpicker',
                $CFG->dirroot . '/blocks/dash/form/element-colorpicker.php',
                'moodlequickform_dashcolorpicker'
            );

            // Not available circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[unavailablecirclecolor]',
                get_string('unavailablecirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[unavailablecirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[unavailablecirclecolor]', 'unavailablecirclecolor', 'block_dash');

            // Available circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[availablecirclecolor]',
                get_string('availablecirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[availablecirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[availablecirclecolor]', 'availablecirclecolor', 'block_dash');

            // Enrolled circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[notstartedcirclecolor]',
                get_string('notstartedcirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[notstartedcirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[notstartedcirclecolor]', 'notstartedcirclecolor', 'block_dash');

            // In-progress circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[inprogresscirclecolor]',
                get_string('inprogresscirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[inprogresscirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[inprogresscirclecolor]', 'inprogresscirclecolor', 'block_dash');

            // Completed circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[completedcirclecolor]',
                get_string('completedcirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[completedcirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[completedcirclecolor]', 'completedcirclecolor', 'block_dash');

            // Failed circle color.
            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[failedcirclecolor]',
                get_string('failedcirclecolor', 'block_dash')
            );
            $mform->setType('config_preferences[failedcirclecolor]', PARAM_RAW);
            $mform->addHelpButton('config_preferences[failedcirclecolor]', 'failedcirclecolor', 'block_dash');

            $PAGE->requires->js_call_amd(
                'dashaddon_learningpath/zoneConfig',
                'init',
                [
                    'zoneuniqueid' => $zoneuniqueid,
                    'blockid' => $this->get_block_instance()->instance->id,
                    'contextid' => $this->get_context()->id,
                ]
            );
        }
    }

    /**
     * Is the widget needs to load the js when it the content updated using JS.
     *
     * @return bool
     */
    public function supports_currentscript() {
        return true;
    }

    /**
     * Extend the configuration form for learning paths.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @param object $source The data source.
     * @param object $instance The block instance.
     * @return void
     */
    public static function extend_config_form($mform, $source, $instance) {
         // Resource.
        $mform->addElement('header', 'resourcesheader', get_string('resourcesheading', 'block_dash'));
        $ports = [
            'desktop_learningpath' => get_string('desktop_learningpath', 'block_dash'),
            'tablet_learningpath' => get_string('tablet_learningpath', 'block_dash'),
            'mobile_learningpath' => get_string('mobile_learningpath', 'block_dash'),
        ];
        $filemanageroptions = [
            'accepted_types' => ['.svg'],
            'maxfiles' => -1,
            'maxbytes' => 0,
            'subdirs' => 0,
            'return_types' => FILE_INTERNAL,
        ];

        foreach ($ports as $fieldname => $title) {
            $mform->addElement('filemanager', 'config_' . $fieldname, $title, null, $filemanageroptions);
            $mform->setType('config_' . $fieldname, PARAM_RAW);
            $mform->addHelpButton('config_' . $fieldname, $fieldname, 'block_dash');
        }
    }


    /**
     * Copy zone configurations when block is copied.
     *
     * @param int $frominstanceid Original block instance ID
     * @param int $currentcontextid New context ID
     * @return bool
     */
    public function instance_copy($frominstanceid, $currentcontextid) {
        global $DB;

        // Get the new block ID.
        $newblockid = $this->get_block_instance()->instance->id;

        // Get all zone configurations from the original block.
        $originalzones = $DB->get_records('dashaddon_learningpath_zones', ['blockid' => $frominstanceid]);

        // Copy each zone configuration to the new block.
        foreach ($originalzones as $zone) {
            $newzone = clone $zone;
            unset($newzone->id); // Remove the ID so a new record is created.
            $newzone->blockid = $newblockid; // Set the new block ID.
            $newzone->timecreated = time();
            $newzone->timemodified = time();

            // Insert the new zone configuration.
            $DB->insert_record('dashaddon_learningpath_zones', $newzone);
        }

        return true;
    }
}
