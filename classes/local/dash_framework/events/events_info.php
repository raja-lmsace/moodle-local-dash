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
 * Class course_category_table.
 *
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\local\dash_framework\events;

use core\event\base as base_event;
use core\event\course_completed;
use core\event\course_module_viewed;
use core\event\course_viewed;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/blocks/dash/lib.php");

/**
 * Helper class for getting extra information about events (colors, icons, descriptions, related objects, etc).
 *
 * @package local_dash
 */
class events_info {
    /**
     * Default color classname.
     */
    const DEFAULT_COLOR = 'default';

    /**
     * This events_info class instance object.
     * @var events_info
     */
    private static $instance = null;

    /**
     * Processed informations about this event.
     *
     * @var array
     */
    private $eventinfo = [];

    /**
     * Singleton pattern to get instance of this class.
     *
     * @return events_info
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new events_info();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    protected function __construct() {
        $this->eventinfo = [
            base_event::class => [],
            course_viewed::class => [
                'icon' => ['local_dash', 'viewed'],
                'color' => 'info',
            ],
            course_completed::class => [
                'icon' => ['local_dash', 'completed'],
                'color' => 'success',
            ],
            course_module_viewed::class => [
                'icon' => ['local_dash', 'viewed'],
                'color' => 'info',
            ],
        ];
    }

    /**
     * Get color for event.
     *
     * @param base_event $event
     * @return string Color identifier (primary, danger, success, info, warning, dark, etc).
     */
    public function get_event_color(base_event $event) {
        $data = [];
        if (isset($this->eventinfo[get_class($event)])) {
            $data = $this->eventinfo[get_class($event)];
        } else if (isset($this->eventinfo[get_parent_class($event)])) {
            $data = $this->eventinfo[get_parent_class($event)];
        }

        return isset($data['color']) ? $data['color'] : self::DEFAULT_COLOR;
    }

    /**
     * Get icon for given event.
     *
     * @param base_event $event
     * @return string HTML of icon.
     */
    public function get_event_icon(base_event $event) {
        global $OUTPUT;

        $data = [];
        if (isset($this->eventinfo[get_class($event)])) {
            $data = $this->eventinfo[get_class($event)];
        }

        if (isset($this->eventinfo[get_parent_class($event)])) {
            $data = $this->eventinfo[get_parent_class($event)];
        }

        if (isset($data['icon'])) {
            if (block_dash_is_totara()) {
                // Convert to flex icon output.
                return $OUTPUT->flex_icon($data['icon'][1] . ':' . $data['icon'][0]);
            } else {
                return $OUTPUT->pix_icon($data['icon'][1], '', $data['icon'][0]);
            }
        }

        return '';
    }
}
