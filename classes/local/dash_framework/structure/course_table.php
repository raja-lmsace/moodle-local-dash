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
 * Class course_table.
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\local\dash_framework\structure;

use moodle_url;
use lang_string;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\table;
use local_dash\data_grid\field\attribute\tags_attribute;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use local_dash\data_grid\field\attribute\course_format_attribute;
use local_dash\data_grid\field\attribute\course_summary_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use local_dash\data_grid\field\attribute\course_image_url_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use local_dash\data_grid\field\attribute\activity_progress_attribute;
use local_dash\data_grid\field\attribute\customfield_select_attribute;
use local_dash\data_grid\field\attribute\completion_status_attribute;
use local_dash\data_grid\field\attribute\enrollment_options_attribute;
use local_dash\data_grid\field\attribute\smart_course_button_attribute;

/**
 * Class course_table.
 *
 * @package local_dash
 */
class course_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('course', 'c');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_c', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {
        global $USER;

        $fields = [
            new field('id', new lang_string('course'), $this, null, [
                new identifier_attribute()
            ]),
            new field('shortname', new lang_string('shortname'), $this),
            new field('fullname', new lang_string('fullname'), $this),
            new field('startdate', new lang_string('startdate'), $this, null, [
                new date_attribute()
            ]),
            new field('idnumber', new lang_string('idnumber'), $this),
            new field('summary', new lang_string('summary'), $this, 'c.id', [
                new course_summary_attribute()
            ]),
            new field('url', new lang_string('courseurl', 'block_dash'), $this, 'c.id', [
                new moodle_url_attribute(['url' => new moodle_url('/course/view.php', ['id' => 'c_id'])])
            ]),
            new field('button', new lang_string('coursebutton', 'block_dash'), $this, 'c.id', [
                new moodle_url_attribute(['url' => new moodle_url('/course/view.php', ['id' => 'c_id'])]),
                new button_attribute(['label' => new lang_string('viewcourse', 'block_dash'), 'aria-label' => 'c_fullname'])
            ]),
            new field('image_url', new lang_string('courseoverviewfilesurl', 'block_dash'), $this, 'c.id', [
                new image_url_attribute(),
                new course_image_url_attribute(),
            ]),
            new field('image', new lang_string('courseoverviewfiles'), $this, 'c.id', [
                new course_image_url_attribute(),
                new image_attribute()
            ]),
            // Course image link.
            new field('image_link', new lang_string('courseimagelink', 'block_dash'), $this, 'c.id', [
                new course_image_url_attribute(), new image_attribute(),
                new linked_data_attribute(['url' => new moodle_url('/course/view.php', ['id' => 'c_id'])])
            ]),

            new field('format', new lang_string('format'), $this, null, [
                new course_format_attribute()
            ]),
            new field('enablecompletion', new lang_string('enablecompletion', 'completion'), $this, null, [
                new bool_attribute()
            ]),
            new field('tags', new lang_string('coursetags', 'tag'), $this, 'c.id', [
                new tags_attribute(['component' => 'core', 'itemtype' => 'course'])
            ]),
            new field('total_activities', new lang_string('totalactivities', 'block_dash'), $this,
                '(SELECT COUNT(*) FROM {course_modules} cm100
                WHERE cm100.course = c.id AND cm100.visible = 1)', [], ['supports_sorting' => false]
            ),
            new field('users_completed', new lang_string('userscompleted', 'block_dash'), $this,
                '(SELECT COUNT(*) FROM {course_completions} ccp100
                WHERE timecompleted > 0 AND ccp100.course = c.id)', [], ['supports_sorting' => false]
            ),
            new field('users_not_completed', new lang_string('usersnotcompleted', 'block_dash'), $this,
                '(SELECT COUNT(*) FROM {course_completions} ccp200
                WHERE timecompleted IS NULL AND ccp200.course = c.id)', [], ['supports_sorting' => false]
            ),
            new field('status', new lang_string('status', 'block_dash'), $this, "
                (
                    SELECT ue.status FROM (
                        SELECT
                            DISTINCT ue.userid,
                            CASE WHEN cc.timecompleted > 0 THEN 'completed'
                                WHEN cc.timestarted > 0 THEN 'inprogress'
                                ELSE 'enrolled'
                                END AS status,
                            e.courseid AS courseid
                        FROM {user_enrolments} ue
                        LEFT JOIN {enrol} e ON ue.enrolid = e.id
                        LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND ue.userid = cc.userid
                        WHERE ue.userid = $USER->id
                    ) ue WHERE ue.courseid = c.id
                )",
                [ new completion_status_attribute() ]
            ),
            new field('enrollment_options', new lang_string('enrollment_options', 'block_dash'), $this, 'c.id', [
                new enrollment_options_attribute()
            ]),
            new field('smart_course_button', new lang_string('smart_coursebutton', 'block_dash'), $this, 'c.id', [
                new smart_course_button_attribute()
            ])
        ];

        if (class_exists('\core_course\customfield\course_handler')) {
            $i = 0;
            $handler = \core_course\customfield\course_handler::create();
            $coursefields = $handler->get_fields();
            foreach ($coursefields as $field) {
                $name = 'f_' . strtolower($field->get('shortname'));
                $select = "(SELECT coursecustom$i.value FROM {customfield_data} coursecustom$i
                   WHERE coursecustom$i.instanceid = c.id AND coursecustom$i.fieldid = " . $field->get('id') . ")";

                $attributes = [];

                switch ($field->get('type')) {
                    case 'checkbox':
                        $attributes[] = new bool_attribute();
                        break;
                    case 'date':
                        $attributes[] = new date_attribute();
                        break;
                    case 'textarea':
                        break;
                    case 'select':
                        $attributes[] = new customfield_select_attribute(['field' => $field]);
                        break;
                }

                $fields[] = new field(
                    $name,
                    new lang_string('customfield', 'block_dash', ['name' => format_string($field->get('name'))]),
                    $this,
                    $select,
                    $attributes,
                    ['supports_sorting' => false]
                );

                $i++;
            }
        } else if (block_dash_is_totara()) {
            global $DB;

            $i = 0;
            foreach ($DB->get_records('course_info_field') as $field) {
                $name = 'f_' . strtolower($field->shortname);
                $select = "(SELECT coursecustom$i.data FROM {course_info_data} coursecustom$i
                   WHERE coursecustom$i.courseid = c.id AND coursecustom$i.fieldid = " . $field->id . ")";

                $attributes = [];

                switch ($field->datatype) {
                    case 'checkbox':
                        $attributes[] = new bool_attribute();
                        break;
                    case 'date':
                        $attributes[] = new date_attribute();
                        break;
                    case 'textarea':
                        break;
                    case 'select':
                        $attributes[] = new customfield_select_attribute(['field' => $field]);
                        break;
                }

                $fields[] = new field(
                    $name,
                    new lang_string('customfield', 'block_dash', ['name' => $field->fullname]),
                    $this,
                    $select,
                    $attributes,
                    ['supports_sorting' => false]
                );

                $i++;
            }
        }

        return $fields;
    }
}
