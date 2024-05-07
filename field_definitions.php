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
 * Field definitions.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    [
        'name' => 'c_id',
        'select' => 'c.id',
        'title' => get_string('course') . ' ID',
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_shortname',
        'select' => 'c.shortname',
        'title' => get_string('shortname'),
        'tables' => ['c'],
    ],
    [
        'name' => 'c_fullname',
        'select' => 'c.fullname',
        'title' => get_string('fullname'),
        'tables' => ['c'],
    ],
    [
        'name' => 'c_startdate',
        'select' => 'c.startdate',
        'title' => get_string('startdate'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_enddate',
        'select' => 'c.enddate',
        'title' => get_string('enddate'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_idnumber',
        'select' => 'c.idnumber',
        'title' => get_string('idnumber'),
        'tables' => ['c'],
    ],
    [
        'name' => 'c_summary',
        'select' => 'c.id',
        'title' => get_string('summary'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\course_summary_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_url',
        'select' => 'c.id',
        'title' => get_string('course') . ' URL',
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\moodle_url_attribute::class,
                'options' => [
                    'url' => new moodle_url('/course/view.php', ['id' => 'c_id']),
                ],
            ],
        ],
    ],
    [
        'name' => 'c_button',
        'select' => 'c.id',
        'title' => get_string('coursebutton', 'block_dash'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\moodle_url_attribute::class,
                'options' => [
                    'url' => new moodle_url('/course/view.php', ['id' => 'c_id']),
                ],
            ],
            [
                'type' => \block_dash\local\data_grid\field\attribute\button_attribute::class,
                'options' => [
                    'label' => get_string('viewcourse', 'block_dash'),
                ],
            ],
        ],
    ],
    [
        'name' => 'c_image_url',
        'select' => 'c.id',
        'title' => get_string('courseoverviewfiles') . ' URL',
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\image_url_attribute::class,
            ],
            [
                'type' => \local_dash\data_grid\field\attribute\course_image_url_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_image',
        'select' => 'c.id',
        'title' => get_string('courseoverviewfiles'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\course_image_url_attribute::class,
            ],
            [
                'type' => \block_dash\local\data_grid\field\attribute\image_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_format',
        'select' => 'c.format',
        'title' => get_string('format'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\course_format_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_enablecompletion',
        'select' => 'c.enablecompletion',
        'title' => get_string('coursecompletion', 'completion'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\bool_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'c_tags',
        'select' => 'c.id',
        'title' => get_string('coursetags', 'tag'),
        'tables' => ['c'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\tags_attribute::class,
                'options' => [
                    'component' => 'core',
                    'itemtype' => 'course',
                ],
            ],
        ],
    ],
    [
        'name' => 'c_total_activities',
        'select' => '(SELECT COUNT(*) FROM {course_modules} cm100 WHERE cm100.course = c.id AND cm100.visible = 1)',
        'title' => get_string('totalactivities', 'block_dash'),
        'tables' => ['c'],
        'options' => ['supports_sorting' => false],
    ],
    [
        'name' => 'c_users_completed',
        'select' => '(SELECT COUNT(*) FROM {course_completions} ccp100
                      WHERE timecompleted > 0 AND ccp100.course = c.id)',
        'title' => get_string('userscompleted', 'block_dash'),
        'tables' => ['c'],
        'options' => ['supports_sorting' => false],
    ],
    [
        'name' => 'c_users_not_completed',
        'select' => '(SELECT COUNT(*) FROM {course_completions} ccp200
                      WHERE timecompleted IS NULL AND ccp200.course = c.id)',
        'title' => get_string('usersnotcompleted', 'block_dash'),
        'tables' => ['c'],
        'options' => ['supports_sorting' => false],
    ],
];

if (class_exists('\core_course\customfield\course_handler')) {
    $i = 0;
    $handler = \core_course\customfield\course_handler::create();
    $fields = $handler->get_fields();
    foreach ($fields as $field) {
        $name = 'c_f_' . strtolower($field->get('shortname'));
        $select = "(SELECT coursecustom$i.value FROM {customfield_data} coursecustom$i
                   WHERE coursecustom$i.instanceid = c.id AND coursecustom$i.fieldid = " . $field->get('id') . ")";

        $attributes = [];

        switch ($field->get('type')) {
            case 'checkbox':
                $attributes[] = [
                    'type' => \block_dash\local\data_grid\field\attribute\bool_attribute::class,
                ];
                break;
            case 'date':
                $attributes[] = [
                    'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
                ];
                break;
            case 'textarea':
                break;
            case 'select':
                $attributes[] = [
                    'type' => \local_dash\data_grid\field\attribute\customfield_select_attribute::class,
                    'options' => [
                        'field' => $field,
                    ],
                ];
                break;
        }

        $definitions[] = [
            'name' => $name,
            'select' => $select,
            'title' => $field->get('name'),
            'tables' => ['c'],
            'attributes' => $attributes,
            'options' => ['supports_sorting' => false],
        ];

        $i++;
    }
} else if (block_dash_is_totara()) {
    global $DB;

    $i = 0;
    foreach ($DB->get_records('course_info_field') as $field) {
        $name = 'c_f_' . strtolower($field->shortname);
        $select = "(SELECT coursecustom$i.data FROM {course_info_data} coursecustom$i
                   WHERE coursecustom$i.courseid = c.id AND coursecustom$i.fieldid = " . $field->id . ")";

        $attributes = [];

        switch ($field->datatype) {
            case 'checkbox':
                $attributes[] = [
                    'type' => \block_dash\local\data_grid\field\attribute\bool_attribute::class,
                ];
                break;
            case 'date':
                $attributes[] = [
                    'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
                ];
                break;
            case 'textarea':
                break;
            case 'select':
                $attributes[] = [
                    'type' => \local_dash\data_grid\field\attribute\customfield_select_attribute::class,
                    'options' => [
                        'field' => $field,
                    ],
                ];
                break;
        }

        $definitions[] = [
            'name' => $name,
            'select' => $select,
            'title' => $field->fullname,
            'tables' => ['c'],
            'attributes' => $attributes,
            'options' => ['supports_sorting' => false],
        ];

        $i++;
    }
}

$definitions = array_merge($definitions, [
    // Course categories.
    [
        'name' => 'cc_id',
        'select' => 'cc.id',
        'title' => get_string('category') . ' ID',
        'tables' => ['cc'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'cc_name',
        'select' => 'cc.name',
        'title' => get_string('categoryname'),
        'tables' => ['cc'],
    ],

    // Course completions.
    [
        'name' => 'ccp_id',
        'select' => 'ccp.id',
        'title' => get_string('coursecompletion') . ' ID',
        'tables' => ['ccp'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ccp_total_activities',
        'select' => '(SELECT COUNT(*) FROM {course_completion_criteria} ccc200
                    WHERE ccc200.course = c.id AND ccc200.criteriatype = 4)',
        'title' => get_string('totalactivitiescompletion', 'block_dash'),
        'tables' => ['ccp'],
        'options' => ['supports_sorting' => false],
    ],
    [
        'name' => 'ccp_completed_activities',
        'select' => '(SELECT COUNT(*) FROM {course_completion_crit_compl} cccc100
                      WHERE cccc100.userid = u.id
                      AND cccc100.course = c.id)',
        'title' => get_string('completedactivities', 'block_dash'),
        'tables' => ['ccp'],
        'options' => ['supports_sorting' => false],
    ],
    [
        'name' => 'ccp_completed',
        'select' => 'ccp.timecompleted',
        'title' => get_string('coursecompleted', 'completion'),
        'tables' => ['ccp'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\bool_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ccp_timecompleted',
        'select' => 'ccp.timecompleted',
        'title' => get_string('datecompleted', 'block_dash'),
        'tables' => ['ccp'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ccp_progress',
        'select' => 'ccp.id',
        'title' => get_string('activityprogress', 'block_dash'),
        'tables' => ['ccp'],
        'options' => ['supports_sorting' => false],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\activity_progress_attribute::class,
            ],
        ],
    ],

    // Enrollment methods.
    [
        'name' => 'e_id',
        'select' => 'e.id',
        'title' => get_string('enrollmentmethod', 'block_dash') . ' ID',
        'tables' => ['e'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'e_enrol',
        'select' => 'e.enrol',
        'title' => get_string('enrollmentmethod', 'block_dash'),
        'tables' => ['e'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\enrol_name_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'e_status',
        'select' => 'e.status',
        'title' => get_string('enrollmentmethodstatus', 'block_dash'),
        'tables' => ['e'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\enrol_status_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'e_enrolled_users',
        'select' => '(SELECT COUNT(*) FROM {user_enrolments} ue100 WHERE ue100.enrolid = e.id)',
        'title' => get_string('enrolledusers', 'enrol'),
        'tables' => ['e'],
        'options' => ['supports_sorting' => false],
    ],

    // User enrollmments.
    [
        'name' => 'ue_id',
        'select' => 'ue.id',
        'title' => get_string('enrollment', 'block_dash') . ' ID',
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ue_timestart',
        'select' => 'ue.timestart',
        'title' => get_string('enrollmenttimestart', 'block_dash'),
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ue_timeend',
        'select' => 'ue.timeend',
        'title' => get_string('enrollmenttimeend', 'block_dash'),
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\date_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ue_daysuntilstart',
        'select' => 'ue.timestart',
        'title' => get_string('daysuntilstart', 'block_dash'),
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\days_until_start_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ue_daysuntilend',
        'select' => 'ue.timeend',
        'title' => get_string('daysuntilend', 'block_dash'),
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\days_until_end_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'ue_status',
        'select' => 'ue.status',
        'title' => get_string('status', 'block_dash'),
        'tables' => ['ue'],
        'attributes' => [
            [
                'type' => \local_dash\data_grid\field\attribute\enrol_status_attribute::class,
            ],
        ],
    ],

    // Dashboard fields.
    [
        'name' => 'dd_id',
        'select' => 'dd.id',
        'title' => get_string('dashboard', 'block_dash') . ' ID',
        'tables' => ['dd'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'dd_contextid',
        'select' => 'dd.contextid',
        'title' => get_string('contextid', 'block_dash'),
        'tables' => ['dd'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\context_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'dd_name',
        'select' => 'dd.name',
        'title' => get_string('name'),
        'tables' => ['dd'],
    ],
    [
        'name' => 'dd_link',
        'select' => 'dd.id',
        'title' => get_string('dashboardlink', 'block_dash'),
        'tables' => ['dd'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\moodle_url_attribute::class,
                'options' => [
                    'url' => new moodle_url('/local/dash/addon/dashboard/dashboard.php', ['id' => 'dd_id']),
                ],
            ],
            [
                'type' => \block_dash\local\data_grid\field\attribute\link_attribute::class,
                'options' => [
                    'label_field' => 'dd_name',
                ],
            ],
        ],
    ],

    // Site logs fields.
    [
        'name' => 'sl_id',
        'select' => 'sl.id',
        'title' => 'Log ID',
        'tables' => ['sl'],
        'attributes' => [
            [
                'type' => \block_dash\local\data_grid\field\attribute\identifier_attribute::class,
            ],
        ],
    ],
    [
        'name' => 'sl_eventname',
        'select' => 'sl.eventname',
        'title' => get_string('eventname'),
        'tables' => ['sl'],
    ],
]);

return $definitions;
