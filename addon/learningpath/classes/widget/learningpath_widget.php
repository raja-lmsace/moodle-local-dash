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
use local_dash\data_grid\filter\tags_condition;
use block_dash\local\data_grid\filter\bool_filter;
use block_dash\local\data_grid\filter\date_filter;
use local_dash\data_grid\filter\customfield_filter;

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
     * Get available list of all activity mask images.
     *
     * @param string $filearea
     * @return array $results List of mask images.
     */
    public function get_all_learning_paths($filearea) {
        global $CFG;
        $results = [ 0 => get_string('none') ];
        require_once($CFG->libdir.'/filelib.php');
        $fs = get_file_storage();
        $learingpaths = $fs->get_area_files(
            \context_system::instance()->id, 'dashaddon_learningpath', $filearea, 0, '', false
        );

        foreach ($learingpaths as $path) {
            $results[$path->get_filename()] = ucwords(explode('.', $path->get_filename())[0]);
        }

        return $results;
    }


    /**
     * Get image file url of the given itemid.
     *
     * @param string $filename
     * @param string $filearea
     * @return void
     */
    public function get_learningpath_svg($filename, $filearea) {
        global $DB;
        $fs = get_file_storage();

        $fileid = $DB->get_field('files', 'id', ['filearea' => $filearea, 'filename' => $filename]);
        $file = $fs->get_file_by_id($fileid);

        if (empty($file)) {
            return '';
        }
        return $file->get_content();
    }

    /**
     * Build widget data and send to layout thene the layout will render the widget.
     *
     * @return void
     */
    public function build_widget() {
        global $PAGE;
        $this->data = [];

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

        list($courses, $completedcourses, $nextcourse) = $this->get_possible_completion_courses();

        if (!isset($courses) || empty($courses)) {
            return false;
        }

        $dotimg = false;
        $imgsize = $this->get_preferences('courseimgsize');
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
        $dataset['blockid'] = $this->get_block_instance()->instance->id;
        $dataset['dotimg'] = $dotimg;
        $dataset['courseimgwidth'] = $courseimgwidth;
        $dataset['courseimgheight'] = $courseimgheight;
        $dataset['coursecircleradius'] = $courseimgwidth / 2;
        $dataset['courses'] = array_values($courses);
        $dataset['startelement'] = $this->get_preferences('startelement');
        $dataset['finishelement'] = $this->get_preferences('finishelement');
        $dataset['detailsarea'] = $this->get_preferences('detailsarea');
        $dataset['totalcourses'] = count($courses);
        $dataset['completedcourses'] = $completedcourses;
        $dataset['nextcourse'] = isset($nextcourse->fullname) ? format_string($nextcourse->fullname) : '';
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

        $this->data['detailsarea'] = $this->get_preferences('detailsarea');
        $this->data['showinfo'] = $this->get_preferences('infoarea');
        $this->data['showlearningpath'] = count($courses) > 0 ? true : false;
        $stringvar = [
            'completed' => $completedcourses,
            'total' => count($courses),
            'nextcourse' => isset($nextcourse->fullname) ? format_string($nextcourse->fullname) : '',
        ];

        $completedlearingpath = $completedcourses / count($courses) == 1;
        $this->data['completedlearingpath'] = $completedlearingpath;
        $this->data['nextcourseurl'] = ($nextcourse) ? new \moodle_url('/course/view.php', ['id' => $nextcourse->id]) : '#';

        if (!$completedlearingpath) {
            $this->data['infocontent'] = get_string('leanringpath_infocontent', 'block_dash', $stringvar);
            $this->data['infobutton'] = get_string('resumelearningpath', 'block_dash');
        } else {
            $this->data['infocontent'] = get_string('completedlearningpath', 'block_dash');
        }
        return $this->data;
    }

    /**
     * Generate report for courses that are user enrolled.
     *
     * @return array $course List of user enroled courses.
     */
    public function generate_learningpath_filter() {
        $this->before_data();
        list($sql, $params) = $this->get_filter_collection()->get_sql_and_params();
        return $sql ? [" AND ".$sql[0], $params] : [];
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

        $filtercollection->add_filter(new tags_condition('course_tags', 'c.id', 'core', 'course',
            get_string('coursetags', 'dashaddon_learningpath')));

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

        list($conditionsql, $params) = $this->generate_learningpath_filter();
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
        array_walk($courses, function(&$course) use (&$completedcourses, &$updatenextstartcourse, &$i, $coursesinfo) {
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
            if (isset($report['unavailable']) && $report['unavailable']) {
                $completionstatus = 'unavailable';
            } else if ($report['completed']) {
                $completionstatus = 'completed';
            } else if ($report['inprogress']) {
                $completionstatus = 'inprogress';
            } else {
                $completionstatus = 'notstarted';
            }

            $course['completionstatus'] = $completionstatus;

            $course['completionpercentage'] = isset($report['completionpercentage']) ? (int) $report['completionpercentage'] : 0;
            $course['img'] = dashaddon_learningpath_courseimage($course['info']['id']);
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

        $nextcourse = '';
        if ($updatenextstartcourse) {
            $nextcourse = get_course($updatenextstartcourse);
        }
        return [$courses, $completedcourses, $nextcourse];
    }

    /**
     * Set the default configurations of the learning path, Make the infoarea, startelements, detailsarea are enabled by default.
     *
     * @param array $data
     * @return void
     */
    public function set_default_preferences(&$data) {
        $data['config_preferences']['infoarea'] = 1;
        $data['config_preferences']['startelement'] = 1;
        $data['config_preferences']['finishelement'] = 1;
        $data['config_preferences']['detailsarea'] = 1;
    }

    /**
     * Prefence form for widget. We make the fields disable other than the general.
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

        if ($form->get_tab() == preferences_form::TAB_FIELDS) {

            $mform->addElement('html', '<hr>');

            $mform->addElement('advcheckbox', 'config_preferences[infoarea]',
                get_string('field:infoarea', 'block_dash'), '', [0, 1]);
            $mform->addHelpButton('config_preferences[infoarea]', 'field:infoarea', 'block_dash');

            $desktoppaths = $this->get_all_learning_paths('desktop_learningpath');
            $mform->addElement('select', 'config_preferences[desktoppath]', get_string('field:learningpathdesktop', 'block_dash'),
                $desktoppaths);
            $mform->setType('config_preferences[desktoppath]', PARAM_TEXT);

            $tabletpaths = $this->get_all_learning_paths('tablet_learningpath');
            $mform->addElement('select', 'config_preferences[tabletpath]', get_string('field:learningpathtablet', 'block_dash'),
                $tabletpaths);
            $mform->setType('config_preferences[tabletpath]', PARAM_TEXT);

            $mobilepaths = $this->get_all_learning_paths('mobile_learningpath');
            $mform->addElement('select', 'config_preferences[mobilepath]', get_string('field:learningpathmobile', 'block_dash'),
                $mobilepaths);
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
            $mform->addElement('select', 'config_preferences[courseimgsize]', get_string('field:courseimgsize', 'block_dash'),
                $courseimgsizes);
            $mform->setType('config_preferences[courseimgsize]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[courseimgsize]', 'field:courseimgsize', 'block_dash');

            $mform->addElement('advcheckbox', 'config_preferences[startelement]',
                get_string('field:startelement', 'block_dash'), '', [0, 1]);
            $mform->addHelpButton('config_preferences[startelement]', 'field:startelement', 'block_dash');

            $mform->addElement('advcheckbox', 'config_preferences[finishelement]',
                get_string('field:finishelement', 'block_dash'), '', [0, 1]);
            $mform->addHelpButton('config_preferences[finishelement]', 'field:finishelement', 'block_dash');

            $mform->addElement('advcheckbox', 'config_preferences[detailsarea]',
                get_string('field:detailsarea', 'block_dash'), '', [0, 1]);
            $mform->addHelpButton('config_preferences[detailsarea]', 'field:detailsarea', 'block_dash');

            $orderbyoptions = [
                'c.id' => get_string('menu', 'block_dash'),
                'c.shortname' => get_string('courseshortname', 'block_dash'),
                'c.fullname' => get_string('coursefullname', 'block_dash'),
                'c.idnumber' => get_string('courseidnumber', 'block_dash'),
                'c.startdate' => get_string('coursestartdate', 'block_dash'),
                'custom' => get_string('field:customorder', 'dashaddon_learningpath'),
            ];

            // Order by.
            $mform->addElement('select', 'config_preferences[orderby]', get_string('field:orderby', 'block_dash'),
            $orderbyoptions);
            $mform->addHelpButton('config_preferences[orderby]', 'field:orderby', 'block_dash');
            $mform->setType('config_preferences[orderby]', PARAM_TEXT);

            $mform->addElement('text', 'config_preferences[customorder]', get_string('field:customorder',
                'dashaddon_learningpath'));
            $mform->addHelpButton('config_preferences[customorder]', 'field:customorder', 'dashaddon_learningpath');
            $mform->hideIf('config_preferences[customorder]', 'config_preferences[orderby]', 'neq', 'custom');
            $mform->setType('config_preferences[customorder]', PARAM_TEXT);

            $orderbyoptions = [
                'ASC' => get_string('asc', 'block_dash'),
                'DESC' => get_string('desc', 'block_dash'),
            ];

            // Order direction.
            $mform->addElement('select', 'config_preferences[orderdirection]', get_string('field:orderdirection', 'block_dash'),
            $orderbyoptions);
            $mform->addHelpButton('config_preferences[orderdirection]', 'field:orderdirection', 'block_dash');
            $mform->setType('config_preferences[orderdirection]', PARAM_TEXT);

            $mform->addElement('text', "config_preferences[limit]", get_string('field:limit', 'block_dash'));
            $mform->setType('config_preferences[limit]', PARAM_INT);
            $mform->addHelpButton('config_preferences[limit]', 'field:limit', 'block_dash');
            $mform->addRule('config_preferences[limit]', null, 'numeric', null, 'client');

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
     * Add the learningpath related global settings in dash global settings section.
     *
     * @param \admin_settingpage $page
     * @return void
     */
    public static function include_global_settings(\admin_settingpage &$page) {
        $ports = [
            'desktop_learningpath',
            'tablet_learningpath',
            'mobile_learningpath',
        ];
        foreach ($ports as $port) {
            $name = "dashaddon_learningpath/$port";
            $title = get_string($port, 'block_dash');
            $description = get_string($port . '_desc', 'block_dash');
            $setting = new \admin_setting_configstoredfile(
                $name, $title, $description, $port, 0, ['maxfiles' => -1, 'accepted_types' => ['.svg']]);
            $page->add($setting);
        }
    }
}
