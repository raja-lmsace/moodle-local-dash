<?php
// This file is part of The Bootstrap Moodle theme
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
 * Dashaddon calendar events - Data widget helper.
 *
 * @package    dashaddon_calendar_events
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_calendar_events;

use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/calendar/lib.php');
use moodle_url;
use user_picture;
use core_calendar\local\event\proxies\cm_info_proxy;
use block_dash\local\data_grid\field\attribute\category_image_url_attribute;
use calendar_event;
use core_calendar\local\event\data_access\event_vault;
use local_dash\data_grid\field\attribute\course_image_url_attribute;
use core_calendar\external\events_related_objects_cache;
use core_calendar\external\event_exporter;
use core_calendar\local\event\container as event_container;

/**
 * Dashaddon calendar events data widget helper class.
 */
class events {
    /**
     * Event raw data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Event raw record.
     *
     * @var object
     */
    protected $row;

    /**
     * Static property for the table alias.
     *
     * @var string
     */
    public static $tablealias = 'ce';

    /**
     * Replaces the table alias from the raw event data.
     *
     * @var stdClass
     */
    public $event;

    /**
     * The row event data.
     *
     * @var stdClass
     */
    public $rowdata;

    /**
     * Static property for the events.
     *
     * @var array
     */
    public static $events;

    /**
     * Create an instance of the Event class.
     *
     * This is a static factory method to instantiate the Event class.
     *
     * @param mixed $data Data associated with the event.
     * @param object $row Row data from the database.
     * @return Event An instance of the Event class.
     */
    public static function instance($data, $row) {
        return new self($data, $row);
    }

    /**
     * Constructor to initialize the Event object.
     *
     * @param mixed $data Data associated with the event.
     * @param object $row Row data from the database.
     */
    public function __construct($data, $row) {
        global $PAGE, $DB;

        $this->data = $data;
        $this->row = $row;

        if (empty(self::$events[$row->ce_id])) {
            $eventvault = event_container::get_event_vault();

            if ($event = $eventvault->get_event_by_id($row->ce_id)) {
                $cache = new events_related_objects_cache([$event]);
                $relatedobjects = [
                    'context' => $cache->get_context($event),
                    'course' => $cache->get_course($event),
                ];

                $exporter = new event_exporter($event, $relatedobjects);
                $renderer = $PAGE->get_renderer('core_calendar');

                self::$events[$row->ce_id] = $exporter->export($renderer);

                $this->event = self::$events[$row->ce_id];
            }
        } else {
            $this->event = self::$events[$row->ce_id];
        }

        $this->rowdata = (object) array_combine(
            array_map(fn($key) => str_replace('ce_', '', $key), array_keys((array) $this->row)),
            array_values((array) $this->row)
        );
    }

    /**
     * Determine the type of event based on available data.
     *
     * Checks various event properties to identify the event type.
     * It distinguishes between site, user, group, course, course category, and other types.
     *
     * @return string The type of event (e.g., 'site', 'user', 'group', 'course', 'category', or 'other').
     */
    public function event_type() {

        $row = $this->row;

        if (isset($this->event->normalisedeventtype)) {
            return $this->event->normalisedeventtype;
        }

        if ($row->ce_eventtype == 'site') {
            $type = 'site';
        } else if ($row->ce_eventtype == 'user') {
            $type = 'user';
        } else if ($row->ce_groupid != 0 && !empty($row->ce_groupid)) {
            $type = 'group';
        } else if (
            $row->ce_courseid && ($this->rowdata->eventtype == 'course' ||
            ($this->rowdata->modulename && $this->rowdata->instance))
        ) {
            $type = 'course';
        } else if ($row->ce_categoryid) {
            $type = 'category';
        } else {
            $type = 'other';
        }

        return $type ?? 'notfound';
    }

    /**
     * Get the localized string for the event type.
     *
     * Retrieves the localized string corresponding to the event type using the Moodle `get_string` function.
     *
     * @return string The localized event type string.
     */
    public function event_type_string() {
        return $this->event->normalisedeventtypetext ?? get_string('event:type' . $this->event_type(), 'block_dash');
    }

    /**
     * Get the event description.
     *
     * Find the event type and get the event context, rewrites the description to load the images.
     *
     * @return string
     */
    public function get_description() {

        if (isset($this->event->description)) {
            return $this->event->description;
        } else {
            $type = $this->event_type();
            $context = \context_system::instance()->id;
            switch ($type) {
                case 'site':
                    $context = \context_system::instance()->id;
                    break;
                case 'user':
                    $context = \context_user::instance($this->rowdata->userid)->id;
                    break;
                case 'group':
                case 'course':
                    $context = \context_course::instance($this->rowdata->courseid)->id;
                    break;
                case 'category':
                    $context = \context_coursecat::instance($this->rowdata->categoryid)->id;
                    break;
            }
            $summary = file_rewrite_pluginfile_urls(
                $this->data,
                'pluginfile.php',
                $context,
                'calendar',
                'event_description',
                $this->rowdata->id
            );
            $summary = format_text($summary, FORMAT_HTML, ['noclean' => true]);
        }
        return $summary;
    }

    /**
     * Calculate the end time of an event based on the start date and duration.
     *
     * Returns the end time of an event by adding the duration to the start date.
     *
     * @param int $duration Duration of the event in seconds.
     * @param object $row Data object containing the start date of the event.
     * @return int|string The end time as a Unix timestamp, or an empty string if no start date is available.
     */
    public function get_endtime($duration, $row) {
        // Check if duration and start date are available.
        if ($duration && $row->{self::$tablealias . '_startdate'}) {
            $startdate = $row->{self::$tablealias . '_startdate'}; // Start date.
            // Calculate the end time by adding the duration to the start date.
            $endtime = $startdate + $duration;
            return $endtime;
        }
        return '';
    }

    /**
     * Get the status of an event based on start and duration times.
     *
     * Determines if the event is in the future, present, or past
     * based on its start date and duration, and returns the appropriate status string.
     *
     * @param object $data Not used in the current implementation.
     * @param object $row Data object containing the start date and duration of the event.
     * @return string The status of the event (future, present, or past).
     */
    public function get_status($data, $row) {
        // Check the event has a start date.
        if ($data && $row->{self::$tablealias . '_startdate'}) {
            $starttime = $row->{self::$tablealias . '_startdate'};
            // Calculate the end time of the event if a duration is provided, otherwise end time is the same as start time.
            $endtime = $row->{self::$tablealias . '_duration'} ? $starttime + $row->{self::$tablealias . '_duration'} : $starttime;

            $now = time();

            // Check if the event is in the future, present, or past.
            if ($starttime > $now) {
                $status = get_string('coursedate:future', 'block_dash');
            } else if ($starttime < $now && ($endtime && $endtime > $now)) {
                // Event is currently ongoing (start time is in the past but end time is in the future).
                $status = get_string('coursedate:present', 'block_dash');
            } else if ($endtime && $endtime < $now) {
                // Event end time is in the past.
                $status = get_string('coursedata:past', 'block_dash');
            } else {
                $status = get_string('coursedate:present', 'block_dash');
            }

            return $status;
        }

        return '';
    }

    /**
     * Get the URL of the activity related to the event.
     *
     * Returns the URL of the activity (module) related to the event.
     * It checks if the event is associated with a specific module and instance,
     * retrieves the course module information, and then returns the URL.
     *
     * @param array $data Not used in the current implementation.
     * @param stdClass $row Not used in the current implementation.
     * @return string|null The URL of the course module, or null if no module/instance is found.
     */
    public function get_activity_url($data, $row) {
        global $DB;
        // Check if the event has a module name and an instance (an instance of the module in the course).
        if ($this->rowdata->modulename && $this->rowdata->instance) {
            if (isset($this->event->url)) {
                $url = $this->event->url;
            } else {
                if ($DB->record_exists('modules', ['name' => $this->rowdata->modulename, 'visible' => 1])) {
                    $module = new cm_info_proxy($this->rowdata->modulename, $this->rowdata->instance, $this->rowdata->courseid);
                    $coursemodule = $module->get_proxied_instance();
                    $url = $coursemodule->url;
                } else {
                    $url = '';
                }
            }
            // Return the URL of the course module.
            return $url;
        }

        return null;
    }

    /**
     * Get the context of an event (course, group, user, etc.)
     *
     * Determines the type of event (group, course, user, etc.)
     * and returns the associated name and URL. If $linked is true, it returns both
     * the name and the URL as an array. Otherwise, it returns just the name.
     *
     * @param bool $linked If true, returns an array with 'url' and 'label', otherwise just the label.
     * @return string|array The context name or an array with 'url' and 'label' depending on $linked.
     */
    public function get_activity_context($linked = false) {
        global $SITE;

        // Get the type of event (group, course, user, etc.).
        $type = $this->event_type();

        switch ($type) {
            case "group":
                // Format the group name and construct the URL to the group page.
                $contextname = $this->event->groupname ?? format_string($this->rowdata->g_name);
                $linkurl = new moodle_url('/user/index.php', [
                        'id' => $this->rowdata->g_courseid,
                        'group' => $this->rowdata->groupid,
                    ]);
                break;

            case "course":
                // Format the course full name and construct the URL to the course view page.
                if (isset($this->event->course)) {
                    $contextname = $this->event->course->fullnamedisplay;
                    $linkurl = new moodle_url($this->event->course->viewurl);
                } else {
                    $contextname = format_string($this->row->c_fullname);
                    $linkurl = new moodle_url('/course/view.php', ['id' => $this->rowdata->courseid]);
                }
                break;

            case "category":
                // Get and format the course category name and construct the URL to the category page.
                $contextname = $this->event->course->coursecategory ??
                    \core_course_category::get($this->rowdata->categoryid, MUST_EXIST, true)->get_formatted_name();
                $linkurl = new moodle_url('/course/index.php', ['categoryid' => $this->rowdata->categoryid]);

                break;

            case "user":
                // Build a user object with available user fields and format the user's full name.
                $user = (object) [
                    'id' => $this->rowdata->userid,
                ];

                foreach (\core_user\fields::get_name_fields() as $field) {
                    $user->$field = $this->rowdata->{"u_" . $field} ?? '';
                }
                $contextname = fullname($user);
                $linkurl = new moodle_url('/user/view.php', ['id' => $this->rowdata->userid]);

                break;

            case "site":
                // Use the site full name and construct to homepage.
                $contextname = format_string($SITE->fullname);
                $linkurl = new moodle_url('');
                break;

            case "other":
                // Get the name of the plugin/component and construct to homepage.
                if ($this->rowdata->component !== null) {
                    if (isset($this->event->action->url)) {
                        $linkurl = new moodle_url($this->event->action->url);
                    }
                    $contextname = get_string('pluginname', $this->rowdata->component);
                    $linkurl = $linkurl ?? new moodle_url('');
                } else {
                    $contextname = $this->rowdata->eventtype;
                    $linkurl = new moodle_url('');
                }
                break;
        }

        return !$linked ? $contextname : ['url' => $linkurl ?? new moodle_url(''), 'label' => $contextname];
    }

    /**
     * Get the icon of the event type.
     *
     * Determines the appropriate icon for a given event type,
     * constructs the necessary data structure for the icon, and returns it.
     *
     * @param array $data The data related to the event.
     * @param stdClass $row The row object containing event information.
     *
     * @return array An associative array containing the icon key, component, title, and custom URL.
     */
    public function get_icon($data, $row) {
        global $PAGE, $OUTPUT, $DB;

        $type = $this->event_type();

        if ($this->rowdata->modulename && $this->rowdata->instance) {
            $type = 'module';
        }

        if ($this->rowdata->component) {
            $type = 'component';
        }

        $alttext = '';
        switch ($type) {
            case "module":
                if (isset($this->event->icon)) {
                    $key = 'monologo';
                    $component = $this->event->icon->component;
                    $iconurl = $this->event->icon->iconurl;
                    $alttext = $this->event->icon->alttext;
                } else {
                    $key = 'monologo';
                    if ($DB->record_exists('modules', ['name' => $this->rowdata->modulename, 'visible' => 1])) {
                        $module = new cm_info_proxy($this->rowdata->modulename, $this->rowdata->instance, $this->rowdata->courseid);
                        $coursemodule = $module->get_proxied_instance();
                        $component = $coursemodule->modname;
                        $iconurl = $coursemodule->get_icon_url();
                        $iconurl = $iconurl->out(false);
                    } else {
                        $iconurl = '';
                        $component = $this->rowdata->modulename;
                    }
                    $alttext = $this->event_type_string();
                }
                break;

            case "site":
                $key = 'i/siteevent';
                $component = 'core';
                $alttext = get_string('typesite', 'calendar');
                break;

            case "group":
                $key = 'i/groupevent';
                $component = 'core';
                $alttext = get_string('typegroup', 'calendar');
                break;

            case "course":
                $key = 'i/courseevent';
                $component = 'core';
                $alttext = get_string('typecourse', 'calendar');
                break;

            case "category":
                $key = 'i/categoryevent';
                $component = 'core';
                $alttext = get_string('typecategory', 'calendar');
                break;

            case "user":
                $key = 'i/userevent';
                $component = 'core';
                $alttext = get_string('typeuser', 'calendar');
                break;

            case "other":
            case "component":
                if ($PAGE->theme->resolve_image_location($this->event_type(), $this->rowdata->component)) {
                    $key = $this->event_type();
                    $component = $this->rowdata->component;
                } else {
                    $key = 'i/otherevent';
                    $component = 'core';
                }
                break;
        }

        $data = new \stdClass();
        $data->key = $key;
        $data->component = $component;
        $data->title = $alttext;
        $data->customurl = isset($iconurl) ? $iconurl : $OUTPUT->image_url($key, $component);

        return (array) $data;
    }

    /**
     * Get the image URL for the event type.
     *
     * This method determines the appropriate image URL based on the type of event
     * (component, group, course, category, user, etc.) and returns the URL of the image
     * associated with the event.
     *
     * @param array $data Additional data for the event (not used in this method).
     * @param stdClass $row Row of data corresponding to the event (not used in this method).
     *
     * @return string The image URL for the event, or a default calendar image URL if no specific image is found.
     */
    public function get_image($data, $row) {
        global $PAGE, $OUTPUT, $CFG;

        require_once($CFG->libdir . '/filestorage/file_storage.php');
        require_once($CFG->dirroot . '/course/lib.php');

        // Determine the event type using the event_type method.
        $type = $this->event_type();
        $fs = get_file_storage(); // Get the file storage instance.

        switch ($type) {
            case "group":
            case "course":
                // For the course and group events get the valid image from the determined course and make the file url.
                // Use the course icon if course doesn't have any valid image.
                $imageurl = (new course_image_url_attribute())->transform_data($this->rowdata->courseid, $row);
                if (empty($imageurl)) {
                    $imageurl = $OUTPUT->image_url('courses', 'block_myoverview');
                }
                break;

            case "category":
                // Get the category image from the dash category images.
                $imageurl = (new category_image_url_attribute())->transform_data($this->rowdata->categoryid, $row);
                break;

            case "user":
                // For the user event, get the user profile image and convert the URL.
                // Reduct the query, not used the dash "user_image_url_attribute" which runs additional query.
                $user = (object) [
                    'id' => $this->rowdata->userid,
                ];
                foreach (\core_user\fields::get_picture_fields() as $field) {
                    $user->$field = $this->rowdata->{"u_" . $field} ?? '';
                }
                $userpicture = new user_picture($user);
                $userpicture->size = 1;
                $imageurl = $userpicture->get_url($PAGE)->out(false);
                break;

            case "other":
            case "site":
            default:
                // For the site event or other events use the sites fall back image, available in the dash global configuration.
                $imageurl = $this->get_siteimage();
        }

        return $imageurl ?: $OUTPUT->image_url('i/calendar', 'core');
    }

    /**
     * Get the site fallback image.
     *
     * @return void
     */
    protected function get_siteimage() {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            \context_system::instance()->id,
            'local_dash',
            'calendareventsimage',
            0,
            '',
            false
        );
        if (!empty($files)) {
            // Get the first file.
            $file = reset($files);
            $imageurl = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                false,
            );
        }

        return $imageurl ?? '';
    }

    /**
     * Generate a link based on the event type, returning a label and URL.
     *
     * This method creates a URL for different event types (group, course, course category, user)
     * and returns an array containing the label and the URL.
     *
     * @param string $data Label text for the link.
     * @param object $row Not used in the current implementation.
     * @return array Associative array containing the 'label' and 'url'.
     */
    public function get_image_link($data, $row) {
        global $CFG;

        $result['label'] = $data;

        // Get the type of event.
        $type = $this->event_type();

        switch ($type) {
            case "group":
            case "course":
                $linkurl = new moodle_url('/course/view.php', ['id' => $this->rowdata->courseid]);
                break;

            case "category":
                $linkurl = new moodle_url('/course/index.php', ['categoryid' => $this->rowdata->categoryid]);
                break;

            case "user":
                $linkurl = new moodle_url('/user/view.php', ['id' => $this->rowdata->userid]);
                break;

            case "other":
            case "site":
                $linkurl = new moodle_url('');
                break;
            default:
                $linkurl = new moodle_url('');
        }

        $result['url'] = $linkurl;
        return $result;
    }
}
