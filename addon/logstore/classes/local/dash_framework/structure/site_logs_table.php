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
 * Class site_logs_table.
 *
 * @package    dashaddon_logstore
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_logstore\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\field_interface;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\button_attribute;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\link_attribute;
use lang_string;
use local_dash\data_grid\field\attribute\event\event_color_attribute;
use local_dash\data_grid\field\attribute\event\event_description_attribute;
use local_dash\data_grid\field\attribute\event\event_icon_attribute;
use local_dash\data_grid\field\attribute\event\event_link_attribute;
use local_dash\data_grid\field\attribute\event\event_name_attribute;
use local_dash\data_grid\field\attribute\event\event_url_attribute;
use local_dash\data_grid\field\attribute\tags_attribute;
use local_dash\data_grid\field\attribute\timeago_attribute;
use moodle_url;


/**
 * Class site_logs_table.
 *
 * @package dashaddon_logstore
 */
class site_logs_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('logstore_standard_log', 'sl');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_sl', 'block_dash');
    }

    /**
     * Define the fields available in the reports for this table data source.
     * @return field_interface[]
     */
    public function get_fields(): array {
        return [
            new field('id', new lang_string('logs'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('eventname', new lang_string('eventname'), $this, 'sl.id', [
                new event_name_attribute(),
            ]),
            new field('eventicon', new lang_string('eventicon', 'block_dash'), $this, 'sl.id', [
                new event_icon_attribute(),
            ]),
            new field('eventcolor', new lang_string('eventcolor', 'block_dash'), $this, 'sl.id', [
                new event_color_attribute(),
            ]),
            new field('eventdescription', new lang_string('eventdescription', 'block_dash'), $this, 'sl.id', [
                new event_description_attribute(),
            ]),
            new field('eventclass', new lang_string('eventclass', 'block_dash'), $this, 'sl.eventname'),
            new field('eventurl', new lang_string('eventurl', 'block_dash'), $this, 'sl.id', [
                new event_url_attribute(),
            ]),
            new field('eventlink', new lang_string('eventlink', 'block_dash'), $this, 'sl.id', [
                new event_link_attribute(),
            ]),
            new field('eventbutton', new lang_string('eventbutton', 'block_dash'), $this, 'sl.id', [
                new event_url_attribute(),
                new button_attribute(['label' => get_string('gotoevent', 'block_dash')]),
            ]),
            new field('timecreated', new lang_string('eventtime', 'block_dash'), $this, 'sl.timecreated', [
                new date_attribute(),
            ]),
            new field('timeago', new lang_string('timesinceevent', 'block_dash'), $this, 'sl.timecreated', [
                new timeago_attribute(),
            ]),
        ];
    }
}
