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
 * Calendar events report source defined.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\block_dash;

use block_dash\local\data_source\abstract_data_source;
use block_dash\local\data_grid\filter\course_condition;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\my_enrolled_courses_condition;
use dashaddon_calendar_events\local\dash_framework\structure\events_table;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\query_builder\where;
use block_dash\local\data_grid\filter\my_groups_condition;
use calendar_event;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\date_filter;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\day_filter;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_activities_condition;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_context_condition;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_date_condition;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_day_condition;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_status_condition;
use dashaddon_calendar_events\local\block_dash\data_grid\filter\event_filter;
use block_dash\local\dash_framework\structure\field_interface;
use local_dash\data_grid\field\attribute\color_attribute;

/**
 * Calendar events data source template queries and filter conditions defined.
 */
class events_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {

        $this->add_table(new events_table());

        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the calendar events data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $DB;

        $builder = new builder();

        $builder
            ->set_selects([
                'ce_id' => 'ce.id',
                'ce_categoryid' => 'ce.categoryid',
                'ce_courseid' => 'ce.courseid',
                'ce_groupid' => 'ce.groupid',
                'ce_userid' => 'ce.userid',
                'ce_component' => 'ce.component',
                'ce_modulename' => 'ce.modulename',
                'ce_instance' => 'ce.instance',
                'ce_eventtype' => 'ce.eventtype',
                'ce_cmid' => 'cm.id',
            ])
            ->select('g.name', 'g_name')
            ->select('g.courseid', 'g_courseid')
            ->select('c.fullname', 'c_fullname')
            ->from('event', 'ce')
            ->join('user', 'u', 'id', 'ce.userid', join::TYPE_LEFT_JOIN)
            ->join('course', 'c', 'id', 'ce.courseid', join::TYPE_LEFT_JOIN)
            ->join('modules', 'md', 'name', 'ce.modulename', join::TYPE_LEFT_JOIN)
            ->join('course_modules', 'cm', 'instance', 'ce.instance', join::TYPE_LEFT_JOIN)
            ->join_condition('cm', 'cm.module=md.id')
            ->join('groups', 'g', 'id', 'ce.groupid', join::TYPE_LEFT_JOIN);

        // Include user fields for pictures and name.
        foreach (array_merge(\core_user\fields::get_name_fields(), \core_user\fields::get_picture_fields()) as $field) {
            $builder->select("u.$field", "u_$field");
        }

        // Hide the exposed module action events.
        $hideevents = [];
        $actionevents = $DB->get_records('event', ['type' => CALENDAR_EVENT_TYPE_ACTION]);
        foreach ($actionevents as $event) {
            if (
                $event->modulename && $event->instance > 0 && $event->courseid > 0
                && $DB->record_exists('modules', ['name' => $event->modulename,
                'visible' => 1])
            ) {
                $calendarevent = new calendar_event($event);
                $eventvisible = component_callback(
                    "mod_" . $event->modulename,
                    'core_calendar_is_event_visible',
                    [$calendarevent]
                );

                if ($eventvisible === false) {
                    $hideevents[] = $event->id;
                }

                $actionfactory = new \core_calendar\action_factory();
                $eventvisible = component_callback(
                    "mod_" . $event->modulename,
                    'core_calendar_provide_event_action',
                    [$calendarevent, $actionfactory]
                );

                if ($eventvisible === false) {
                    $hideevents[] = $event->id;
                }
            }
        }

        if (!empty($hideevents)) {
            $builder->where('ce.id', $hideevents, where::OPERATOR_NOT_IN);
        }
        return $builder;
    }


    /**
     * Filter conditions are added to calendar events preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        // ...Fitlers
        // Type of events.
        $filtercollection->add_filter(new event_filter('events_type', 'ce.id'));

        // Events dates (today, upcoming, this week, this month).
        $filtercollection->add_filter(new date_filter('event_date', 'ce.id'));

        // Days of week.
        $filtercollection->add_filter(new day_filter('event_day', 'ce.id'));

        // ...Conditions.
        // Course condition.
        $filtercollection->add_filter(new course_condition('c_course', 'ce.courseid'));

        // Course categories condition (Selected category/subcategory based).
        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'ce.categoryid'));

        // Group condition.
        $groupfilter = new my_groups_condition('group', 'ce.groupid');
        $groupfilter->set_support_currentuser();
        $filtercollection->add_filter($groupfilter);

        // Enrolled courses by role.
        $enrolledrolescondition = new my_enrolled_courses_condition('my_enrolled_courses', 'c.id');
        $enrolledrolescondition->set_support_currentuser();
        $filtercollection->add_filter($enrolledrolescondition);

        // Event type condition (Type of event).
        $filtercollection->add_filter(new event_context_condition('eventtype', ''));

        // Event status condition (Past/Present/Future).
        $filtercollection->add_filter(new event_status_condition('eventstatus', ''));

        // Event day condition (Day of week).
        $filtercollection->add_filter(new event_day_condition('eventday', ''));

        // Event date condition (today, this week, upcoming, this month).
        $filtercollection->add_filter(new event_date_condition('eventdate', ''));

        // Event activity.
        $filtercollection->add_filter(new event_activities_condition('eventactivity', ''));

        return $filtercollection;
    }

    /**
     * Get field by name. Returns null if not found.
     *
     * @param string $alias Field alias.
     * @return ?field_interface
     */
    public function get_field(string $alias): ?field_interface {
        // Fields are keyed by name.
        if ($this->has_field($alias)) {
            $field = $this->get_available_fields()[$alias];
            if ($field->get_name() == 'color') {
                $field->remove_attribute(new color_attribute());
                // Convert as css class.
                $badgemode = $this->get_preferences('layout') != 'block_dash\local\layout\timeline_layout';
                $colorattribute = new color_attribute(['prefix' => 'calendar_event_', 'badgemode' => $badgemode]);
                $field->add_attribute($colorattribute);
            }
            return $field;
        }

        return null;
    }

    /**
     * Set the default preferences of the Calendar event datasource, force the set the default settings.
     *
     * @param array $data
     * @return array
     */
    public function set_default_preferences(&$data) {
        $configpreferences = $data['config_preferences'];
        $configpreferences['available_fields']['ce_name']['visible'] = true;
        $configpreferences['available_fields']['ce_contextevent']['visible'] = true;
        $configpreferences['available_fields']['ce_startdate']['visible'] = true;
        $configpreferences['available_fields']['ce_starttime']['visible'] = true;
        $data['config_preferences'] = $configpreferences;
    }
}
