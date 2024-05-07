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
 * A widget layout contains information on how to display data.
 *
 * @package    dashaddon_myprofile
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_myprofile\widget;

use block_dash\local\widget\abstract_layout;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\field\field_definition_factory;
use dashaddon_myprofile\data_grid\field\attribute\indicator_attribute;
use block_dash\local\data_grid\data\data_collection_interface;

/**
 * Layout section for the contacts widget.
 */
class myprofile_layout extends abstract_layout {

    /**
     * Tempalte mustache file name, the layout uses.
     *
     * @return string
     */
    public function get_mustache_template_name() {
        return 'dashaddon_myprofile/widget_myprofile';
    }

    /**
     * Is the layout supports the fields method.
     *
     * @return bool
     */
    public function supports_field_visibility() {
        return true;
    }

    /**
     * Is the layout supports the filter method.
     *
     * @return bool
     */
    public function supports_filtering() {
        return false;
    }

    /**
     * Is the layout supports the pagination.
     *
     * @return bool
     */
    public function supports_pagination() {
        return true;
    }

    /**
     * Is the layout supports the sorting.
     *
     * @return bool
     */
    public function supports_sorting() {
        return false;
    }

    /**
     * List of key performance indicators of users.
     *
     * @return void
     */
    public function get_kpi_fields() {

        return [
            'completedcourses' => get_string('field:completedcourses', 'block_dash'),
            'enrolledprogress' => get_string('field:enrolledprogress', 'block_dash'),
            'coursesinprogress' => get_string('field:coursesinprogress', 'block_dash'),
            'currentcoursescount' => get_string('field:currentcoursescount', 'block_dash'),
            'futurecoursescount' => get_string('field:futurecoursescount', 'block_dash'),
            'pastcoursescount' => get_string('field:pastcoursescount', 'block_dash'),
            'earnedskillpoints' => get_string('field:earnedskillpoints', 'block_dash'),
            'earnedandtotalpoints' => get_string('field:earnedandtotalpoints', 'block_dash'),
            'loginstreak' => get_string('field:loginstreak', 'block_dash'),
            'loginsthisweek' => get_string('field:loginsthisweek', 'block_dash'),
            'completedcoursesinweek' => get_string('field:completedcoursesinweek', 'block_dash'),
            'completedactivitiesinweek' => get_string('field:completedactivitiesinweek', 'block_dash'),
            'numberofdueactivities' => get_string('field:numberofdueactivities', 'block_dash'),
            'numberofoverdueactivities' => get_string('field:numberofoverdueactivities', 'block_dash'),
            'numberofunreadmsg' => get_string('field:numberofunreadmsg', 'block_dash'),
            'numberofcontactreq' => get_string('field:numberofcontactreq', 'block_dash'),
            'sincelogindays' => get_string('field:sincelogindays', 'block_dash'),
            'teammemberscount' => get_string('field:teammemberscount', 'block_dash'),
            'onlineuserscount' => get_string('field:onlineuserscount', 'block_dash'),
        ];
    }

    /**
     * Add form elements to the preferences form when a user is configuring a block.
     *
     * This extends the form built by the data source. When a user chooses a layout, specific form elements may be
     * displayed after a quick refresh of the form.
     *
     * Be sure to call parent::build_preferences_form() if you override this method.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        global $CFG;

        if ($form->get_tab() == preferences_form::TAB_FIELDS) {

            // None option.
            $noneoption = [null => get_string('none', 'block_dash')];

            $mform->addElement('advcheckbox', 'config_preferences[profileimage]',
                get_string('field:profileimage', 'block_dash'), '', [0, 1]);

            $mform->addElement('advcheckbox', 'config_preferences[fullname]',
                get_string('field:fullname', 'block_dash'), '', [0, 1]);

            // Normal grid iteam.
            $userprofilefields = [];
            foreach ($this->get_data_source()->get_available_fields() as $field) {
                if (get_class($field->get_table()) == 'block_dash\local\dash_framework\structure\user_table') {
                    $userprofilefields[] = $field;
                }
            }

            // Kpi fields list.
            $kpiattributes = $this->get_kpi_fields();

            // Additional user information 1.
            $mform->addElement('select', 'config_preferences[userinfo1]', get_string('field:profileuserinfo', 'block_dash', 1),
                array_merge($noneoption, field_definition_factory::get_field_definition_options($userprofilefields)));
            $mform->setType('config_preferences[userinfo1]', PARAM_TEXT);

            // Additional user information 2.
            $mform->addElement('select', 'config_preferences[userinfo2]', get_string('field:profileuserinfo', 'block_dash', 2),
                array_merge($noneoption, field_definition_factory::get_field_definition_options($userprofilefields)));
            $mform->setType('config_preferences[userinfo2]', PARAM_TEXT);

            // Additional user information 3.
            $mform->addElement('select', 'config_preferences[userinfo3]', get_string('field:profileuserinfo', 'block_dash', 3),
                array_merge($noneoption, field_definition_factory::get_field_definition_options($userprofilefields)));
            $mform->setType('config_preferences[userinfo3]', PARAM_TEXT);

            foreach (range(1, myprofile_widget::KPIFIELDCOUNT) as $n) {
                // Key progress indicator.
                $mform->addElement('select', "config_preferences[kpi$n]", get_string('field:kpi', 'block_dash', $n),
                    array_merge($noneoption, $kpiattributes));
                $mform->setType("config_preferences[kpi$n]", PARAM_TEXT);
            }

        }
    }

    /**
     * Modify objects after data is retrieved in the data source. This allows the layout to make decisions on the
     * data source and data grid.
     *
     * @param data_collection_interface $datacollection
     */
    public function after_data(data_collection_interface $datacollection) {
        foreach ($datacollection->get_child_collections('rows') as $childcollection) {
            $this->map_data([
                'bgimageurl' => $this->get_data_source()->get_preferences('backgroundimagefield'),
                'userinfo1' => $this->get_data_source()->get_preferences('userinfo1'),
                'userinfo2' => $this->get_data_source()->get_preferences('userinfo2'),
                'userinfo3' => $this->get_data_source()->get_preferences('userinfo3'),
                'fullname' => 'u_fullname_linked',
            ], $childcollection);
        }
    }
}
