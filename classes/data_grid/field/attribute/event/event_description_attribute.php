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
 * Fetch the event description from record data.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute\event;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use core\event\base;
use logstore_standard\log\store;

/**
 * Transforms data to plugin name of course format.
 *
 * @package local_dash
 */
class event_description_attribute extends event_object_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param string $data
     * @param \stdClass $record Entire row
     * @return mixed
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;

        if ($event = parent::transform_data($data, $record)) {
            $stringidentifier = 'event_desc' . str_replace('\\', '_', $event->eventname);
            $user = $DB->get_record('user', ['id' => $event->userid]);

            $context = [
                'userid' => $user->id,
                'userfullname' => fullname($user),
                'relateduserid' => $event->relateduserid,
                'eventname' => $event::get_name(),
                'eventurl' => $event->get_url(),
                'action' => $event->action,
            ];

            if ($eventcontext = $event->get_context()) {
                $context['contextname'] = $eventcontext->get_context_name();
            }

            if ($relateduser = $DB->get_record('user', ['id' => $event->relateduserid])) {
                $context['relateduserfullname'] = fullname($relateduser);
            }

            if (get_string_manager()->string_exists($stringidentifier, 'block_dash')) {
                return new \lang_string($stringidentifier, 'block_dash', $context);
            }

            return new \lang_string('event_desc_generic', 'block_dash', $context);
        }
    }
}
