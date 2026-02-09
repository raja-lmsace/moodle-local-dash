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
 * dashaddon_calendar_events table.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events\local\dash_framework\structure;

use lang_string;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use block_dash\local\data_grid\field\attribute\widget_attribute;
use dashaddon_calendar_events\events;
use dashaddon_calendar_events\local\block_dash\data_grid\field\attribute\event_linked_attribute;
use dashaddon_calendar_events\local\block_dash\data_grid\field\attribute\event_icon_attribute;
use local_dash\data_grid\field\attribute\color_attribute;
use local_dash\data_grid\field\attribute\duration_attribute;
use local_dash\data_grid\field\attribute\minutes_attribute;

/**
 * Calendar events table structure definitions for calendar events datasource.
 */
class events_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('events', events::$tablealias);
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ce', 'dashaddon_calendar_events');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {

        $fields = [

            new field('id', new \lang_string('event', 'block_dash'), $this, null, [
                new identifier_attribute(),
            ]),

            // Name of the event.
            new field('name', new \lang_string('event:title', 'block_dash'), $this),

            // Type of events.
            new field('eventtype', new \lang_string('event:type', 'block_dash'), $this, null, [
                new widget_attribute(['callback' => fn($row, $data) => events::instance($data, $row)->event_type_string()]),
            ]),

            // Context of event.
            new field('contextevent', new lang_string('event:contextevent', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_activity_context()]),
            ]),

            // Context of event (linked).
            new field('contextlinked', new lang_string('event:contextlinked', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_activity_context(true)]),
                new event_linked_attribute(),
            ]),

            // Description.
            new field('description', new \lang_string('event:description', 'block_dash'), $this, 'ce.description', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_description(true)]),
            ]),

            // Go to activity.
            new field('gotoactivity', new lang_string('event:gotoactivity', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->
                    get_activity_url($data, $row)]),
                new link_attribute(['label' => new lang_string('event:gotoactivity', 'block_dash')]),
            ]),

            // Location.
            new field('location', new lang_string('event:location', 'block_dash'), $this, null, [
                new widget_attribute(['callback' => fn($row, $data)=> format_string($data ? ucfirst($data) : $data, false)]),
            ]),

            // Start date.
            new field('startdate', new lang_string('event:startdate', 'block_dash'), $this, 'ce.timestart', [
                new date_attribute([
                    'format' => get_string('strftimedaydate', 'langconfig'),
                ]),
            ]),

            // Start time.
            new field('starttime', new lang_string('event:starttime', 'block_dash'), $this, 'ce.timestart', [
                new date_attribute([
                    'format' => get_string('strftimetime', 'langconfig'),
                ]),
            ]),

            // End date (calculated based upon the duration setting).
            new field('enddate', new lang_string('event:enddate', 'block_dash'), $this, 'ce.timeduration', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_endtime($data, $row)]),
                new date_attribute([
                    'format' => get_string('strftimedaydate', 'langconfig'),
                ]),
            ]),

            // End time (calculated based upon the duration setting).
            new field('endtime', new lang_string('event:endtime', 'block_dash'), $this, 'ce.timeduration', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_endtime($data, $row)]),
                new date_attribute([
                    'format' => get_string('strftimetime', 'langconfig'),
                ]),
            ]),

            // Event duration.
            new field('duration', new lang_string('event:duration', 'block_dash'), $this, 'ce.timeduration', [
                new duration_attribute(),
            ]),

            // Event duration in minutes.
            new field('durationinminutes', new lang_string('event:durationinminutes', 'block_dash'), $this, 'ce.timeduration', [
                new minutes_attribute(),
            ]),

            // Repeated event.
            new field('repeated', new lang_string('event:repeated', 'block_dash'), $this, 'ce.repeatid', [
                new bool_attribute(),
            ]),

            // Status.
            new field('status', new lang_string('event:status', 'block_dash'), $this, 'ce.id', [
                // Todo: Conver the duration in minutes as separate attribute.
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_status($data, $row)]),
            ]),

            // Event color.
            new field('color', new lang_string('event:color', 'block_dash'), $this, 'ce.type', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->event_type()]),
                new color_attribute(['prefix' => 'calendar_event_']),
            ]),

            // Event icon.
            new field('icon', new lang_string('even:icon', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_icon($data, $row)]),
                new event_icon_attribute(),
            ]),

            // Event image URL for timeline.
            new field('imageurl', new lang_string('event:imageurl', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_image($data, $row)]),
                new image_url_attribute(),
            ]),

            // Event image.
            new field('image', new lang_string('event:image', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_image($data, $row)]),
                new image_attribute(),
            ]),

            // Event image (linked).
            new field('imagelinked', new lang_string('event:imagelinked', 'block_dash'), $this, 'ce.id', [
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_image($data, $row)]),
                new image_attribute(),
                new widget_attribute(['callback' => fn($row, $data)=> events::instance($data, $row)->get_image_link($data, $row)]),
                new event_linked_attribute(),
            ]),

        ];
        return $fields;
    }
}
