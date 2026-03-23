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
 * Activities table.
 *
 * @package    dashaddon_activities
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\dash_framework\structure;

use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\query_builder\join_raw;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use local_dash\data_grid\field\attribute\tags_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use local_dash\data_grid\field\attribute\customfield_select_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_link_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_name_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_modname_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_description_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_type_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_icon_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_completion_status_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_completion_date_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_section_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_sectionlink_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_path_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_duedate_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_background_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_purpose_attribute;
use dashaddon_activities\local\block_dash\data_grid\field\attribute\activity_url_attribute;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/local/dash/addon/activities/lib.php");

use moodle_url;
use lang_string;

/**
 * Activities table structure definitions for activities datasource.
 */
class activities_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('activities', 'cm');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_cm', 'dashaddon_activities');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {
        global $DB;

        $records = $DB->get_records_menu('modules', null, '', 'id, name');

        $activitynamejoin = '';
        $activitynameselect = [];
        $activitymodifiydate = [];
        foreach ($records as $key => $record) {
            $activitynameselect[] = "{$record}.name";
            $activitymodifiydate[] = "{$record}.timemodified";
            $activitynamejoin .= " LEFT JOIN {" . $record . "} {$record} ON {$record}.id = cm.instance AND m.name = '{$record}' ";
        }

        $activitynamesql = 'mds.activityname';
        $activitymodifiydatesql = 'mds.timemodified';

        $fields = [
            new field('id', new lang_string('activity'), $this, 'cm.id', [
                new identifier_attribute(),
            ]),
            new field(
                'name',
                new lang_string('activity_name', 'dashaddon_activities'),
                $this,
                $activitynamesql,
                [],
                field_interface::VISIBILITY_VISIBLE,
                ''
            ),
            new field('link', new lang_string('activitylink', 'dashaddon_activities'), $this, 'cm.id', [
                new activity_link_attribute(),
            ]),
            new field('intro', new lang_string('description'), $this, 'cm.id', [
                new activity_description_attribute(),
            ]),
            new field('idnumber', new lang_string('idnumber'), $this, 'cm.idnumber'),
            new field('tags', new lang_string('activitytags', 'dashaddon_activities'), $this, 'cm.id', [
                new tags_attribute(['component' => 'core', 'itemtype' => 'course_modules']),
            ]),
            new field('type', new lang_string('activitytype', 'dashaddon_activities'), $this, 'cm.id', [
                new activity_type_attribute(),
            ]),
            new field('modulename', new lang_string('modname', 'dashaddon_activities'), $this, 'm.name', [
                new activity_modname_attribute(),
            ], ['supports_sorting' => false]),
            new field('modicon', new lang_string('modicon', 'dashaddon_activities'), $this, 'cm.id', [
                new activity_icon_attribute(),
            ]),
            new field('modulepurpose', new lang_string('modulepurpose', 'dashaddon_activities'), $this, 'cm.id', [
                new activity_purpose_attribute(),
            ], ['supports_sorting' => false]),
            new field(
                'modcompletionstatus',
                new lang_string('modcompletionstatus', 'dashaddon_activities'),
                $this,
                'cmc.completionstate',
                [
                new activity_completion_status_attribute(),
                ]
            ),
            new field('modcompletiondate', new lang_string('completiondate', 'dashaddon_activities'), $this, 'cmc.timemodified', [
                new activity_completion_date_attribute(),
            ]),
            new field('moduleduedate', new lang_string('duedate', 'block_dash'), $this, 'cm.completionexpected', [
                new activity_duedate_attribute(),
            ]),
            new field('modsection', new lang_string('section'), $this, 'cs.name', [
                new activity_section_attribute(),
            ]),
            new field('modsectionlink', new lang_string('sectionlink', 'dashaddon_activities'), $this, 'cs.name', [
                new activity_sectionlink_attribute(),
            ]),
            new field('modulepath', new lang_string('path'), $this, 'cm.id', [
                new activity_path_attribute(),
            ], ['supports_sorting' => false]),
            new field('createdat', new lang_string('createddate', 'block_dash'), $this, 'cm.added', [
                new date_attribute(),
            ]),
            new field('modifiedat', new lang_string('modifieddate', 'block_dash'), $this, $activitymodifiydatesql, [
                new date_attribute(),
            ]),
            new field('button', new lang_string('activitybutton', 'block_dash'), $this, 'cmc.id', [
                new activity_url_attribute(['mod' => 'cm_modulename', 'cmid' => 'cm_id']),
                new button_attribute(['label' => new lang_string('viewactivity', 'block_dash'), 'aria-label' => 'cm_name']),
            ]),
        ];

        if (dashaddon_activities_is_designer_pro_installed()) {
            $fields[] = new field('modulebgimage', new lang_string('modulebgimage', 'dashaddon_activities'), $this, 'cm.id', [
                new activity_background_attribute(),
                new image_attribute(),
            ]);

            $fields[] = new field('modulebgimageurl', new lang_string('modulebgimageurl', 'dashaddon_activities'), $this, 'cm.id', [
                new image_url_attribute(),
                new activity_background_attribute(),
            ]);

            $fields[] = new field(
                'modulebgimagelink',
                new lang_string('modulebgimagelink', 'dashaddon_activities'),
                $this,
                'cm.id',
                [
                new activity_background_attribute(), new image_attribute(), new activity_link_attribute(),
                ]
            );
        }

        if (dashaddon_activities_is_local_metadata_installed()) {
            $i = 0;
            $modulefields = $DB->get_records('local_metadata_field', ['contextlevel' => CONTEXT_MODULE]);
            foreach ($modulefields as $field) {
                $name = 'mf_' . strtolower($field->shortname);
                $select = "(SELECT metadata$i.data FROM {local_metadata} metadata$i
                   WHERE metadata$i.instanceid = cm.id AND metadata$i.fieldid = " . $field->id . ")";

                $attributes = [];

                switch ($field->datatype) {
                    case 'checkbox':
                        $attributes[] = new bool_attribute();
                        break;
                    case 'datetime':
                        $attributes[] = new date_attribute();
                        break;
                    case 'textarea':
                        break;
                    case 'menu':
                        $attributes[] = new customfield_select_attribute(['field' => $field]);
                        break;
                }

                $fields[] = new field(
                    $name,
                    new lang_string('customfield', 'block_dash', ['name' => format_string($field->name)]),
                    $this,
                    $select,
                    $attributes,
                    ['supports_sorting' => false]
                );

                $i++;
            }
        }

        // Include the additional joins for the activity names.
        $this->additionaljoins[] = new join(
            'modulenames',
            'mds',
            'cmid',
            'cm.id',
        );

        // Activity name and timemodified fields.
        $activitynamesql = 'COALESCE(' . implode(', ', $activitynameselect) . ', null) as activityname';
        $activitymodifiydatesql = 'COALESCE(' . implode(', ', $activitymodifiydate) . ', null) as timemodified';

        $this->sqlctelist['modulenames'] = "WITH {modulenames} AS (
            SELECT cm.id AS cmid, $activitynamesql, $activitymodifiydatesql
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            $activitynamejoin
        )";

        return $fields;
    }
}
