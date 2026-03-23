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
 * My profile - dashaddon widget, display the user key performance indicators and users basic information.
 *
 * @package    dashaddon_myprofile
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_myprofile\widget;

use block_dash\local\widget\abstract_widget;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\query_builder\join_raw;
use dashaddon_myprofile\widget\myprofile_layout;
use block_dash\local\dash_framework\structure\user_table;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\filter\filter_collection;
use DateTime;
use html_writer;
use moodle_url;
use tool_skills\helper;

defined('MOODLE_INTERNAL') || die();

// Includtion of calendar lib.
require_once("$CFG->dirroot/calendar/externallib.php");

/**
 * My profile widget class helps to generate the Key performance indicators of the user and their basic informations.
 */
class myprofile_widget extends abstract_widget {
    /**
     * Key of the cache to store user login streak count.
     *
     * @var string
     */
    protected const CACHELOGINSTREAK = 'loginstreak';

    /**
     * Maximum number of the kpi to display.
     *
     * @var int
     */
    public const KPIFIELDCOUNT = 6;

    /**
     * Points user earned.
     *
     * @var array
     */
    protected $userpoints = [];

    /**
     * Construct method.
     *
     * @param \context $context
     */
    public function __construct($context) {
        // Skills table.
        $this->add_table(new user_table());
        parent::__construct($context);
    }

    /**
     * Get the name of widget.
     *
     * @return void
     */
    public function get_name() {
        return get_string('widget:myprofile', 'block_dash');
    }

    /**
     * Check the widget support uses the query method to build the widget.
     *
     * @return bool
     */
    public function supports_query() {
        return false;
    }

    /**
     * Layout class widget will use to render the widget content.
     *
     * @return \abstract_layout
     */
    public function layout() {
        return new myprofile_layout($this);
    }

    /**
     * Pre defined preferences that widget uses.
     *
     * @return array
     */
    public function widget_preferences() {
        $preferences = [
            'datasource' => 'myprofile',
            'layout' => 'myprofile',
        ];
        return $preferences;
    }

    /**
     * Widget data count.
     *
     * @return void
     */
    public function widget_data_count() {
        $kpi = !empty($this->data['kpi']) ? count($this->data['kpi']) : 0;
        return $kpi;
    }

    /**
     * Get current page user. if block added in the profile page then the current profile user is releate user
     * Otherwise logged in user is current user.
     *
     * @return int $userid
     */
    public function get_current_userid() {
        global $PAGE, $USER;

        if ($PAGE->pagelayout == 'mypublic') {
            $userid = optional_param('id', 0, PARAM_INT);
        }

        return isset($userid) && $userid ? $userid : $USER->id;       // Owner of the page.
    }

    /**
     * Calculate the points current user earned from the skills. Grouped by the skill.
     *
     * @return array|null
     */
    protected function get_user_points() {
        static $points;

        if ($points == null) {
            // Get current userid.
            $userid = $this->get_current_userid();
            // Get user points.
            $userpoints = \tool_skills\user::get($userid)->get_user_points(false);
            foreach ($userpoints as $skillpoint) {
                $points[$skillpoint->skill] = $skillpoint->points;
            }
        }

        return $points ?: [];
    }

    /**
     * Add the kpi result as array.
     *
     * @param string $name
     * @param mixed $result
     * @param array $attrs
     * @param string $label
     *
     * @return array
     */
    protected function add_kpi(string $name, $result, $attrs = [], $label = null) {

        return [
            'name' => $name,
            'value' => $result,
            'label' => $label ?: get_string("label:$name", 'block_dash'),
        ] + $attrs;
    }

    /**
     * Build widget data and send to layout, the layout will render the widget.
     *
     * @return array
     */
    public function build_widget() {
        global $USER, $DB, $CFG;

        $kpifields = array_map(function ($i) {
            return $this->get_preferences("kpi$i");
        }, range(1, self::KPIFIELDCOUNT));

        // Current userid.
        $userid = $this->get_current_userid();

        // Build the user query.
        $query = $this->get_query();
        $query->from('user', 'u')->where('u.id', [$userid]);
        $query->where('u.deleted', [0]);

        // Cache handler for the kpi.
        $cache = \cache::make('dashaddon_myprofile', 'kpidata');

        // Init empty elements to store join queries and params.
        $join = $params = $select = $transforms = $result = [];

        // Build the selected KPI queries.
        foreach ($kpifields as $id => $field) {
            switch ($field) {
                case 'enrolledprogress':
                case 'coursesinprogress':
                case 'completedcourses':
                    // Already included the join, then no need to add again. Otherwise it makes params mismatch issue.
                    if (isset($join['completedcourses'])) {
                        break;
                    }
                    $join['completedcourses'] = " LEFT JOIN (
                                SELECT cc.course AS courseid, cc.timestarted AS completionstart, cc.timecompleted AS completedtime
                                FROM {course_completions} cc
                                WHERE cc.userid = :ccuserid AND cc.timecompleted IS NOT NULL
                            ) cc ON cc.courseid = c.id";
                    $params['ccuserid'] = $userid;
                    $select['completedcourses'] = 'cc.completionstart, cc.completedtime';

                    // Find the completed coures count.
                    $transforms['completedcourses'] = fn($courses) => count(
                        array_filter($courses, fn($course) => $course->completedtime != '')
                    );

                    // Transform the completed courses progress.
                    $transforms['enrolledprogress'] = function ($courses) use ($transforms) {
                        return $transforms['completedcourses']($courses)
                            . \html_writer::tag('span', '/' . count($courses), ['class' => 'progress-divide']);
                    };

                    // Courses in progress - tranform data to string.
                    $transforms['coursesinprogress'] = function ($courses) use ($transforms) {
                        return count($courses) - $transforms['completedcourses']($courses);
                    };

                    break;

                case 'currentcoursescount':
                    // Number of current courses - tranform data to string.
                    $transforms['currentcoursescount'] = function ($courses) {

                        $activecourses = array_filter($courses, function ($course) {
                            $startdate = $course->startdate;
                            $enddate = $course->enddate;
                            $now = time();

                            return (!$startdate || $startdate < $now) && (!$enddate || $enddate > $now);
                        });
                        return count($activecourses);
                    };

                    break;

                case 'futurecoursescount':
                    // Number of future courses - tranform data to string.
                    $transforms['futurecoursescount'] = function ($courses) {
                        $futurecourses = array_filter($courses, function ($course) {
                            $startdate = $course->startdate;
                            $now = time();
                            return $startdate && $startdate > $now;
                        });
                        return count($futurecourses);
                    };
                    break;

                case 'pastcoursescount':
                    // Number of past courses - tranform data to string.
                    $transforms['pastcoursescount'] = function ($courses) {
                        $pastcourses = array_filter($courses, function ($course) {
                            $enddate = $course->enddate;
                            $now = time();
                            return $enddate && $enddate < $now;
                        });
                        return count($pastcourses);
                    };
                    break;

                case "loginstreak":
                    if ($cache->has(self::CACHELOGINSTREAK)) {
                        // Fetch the loginstreak.
                        $streak = $cache->get(self::CACHELOGINSTREAK);
                        // Number of days students need login to maintain their streak.
                        $streakdays = get_config('dashaddon_myprofile', 'loginstreakdays');
                        $streakreached = $streak >= $streakdays ? 'dash-streak-highlight' : '';

                        // Attach the login streak count to the kpi results.
                        $result[$field] = $this->add_kpi($field, $streak, ['customclass' => "$streakreached streak-$streak"]);
                        break;
                    }

                    // Cache not stored the login streak, build it again.
                    $firstlogin = null;
                    $currentstreak = 0;
                    $i = 0;
                    $streaks = [];
                    // Number of days students need login to maintain their streak.
                    $streakdays = get_config('dashaddon_myprofile', 'loginstreakdays');
                    // Fetch the list of logins from the log.
                    $loginparams = ['userid' => $userid, 'eventname' => '\core\event\user_loggedin'];
                    $rs = $DB->get_recordset('logstore_standard_log', $loginparams, 'id desc', 'timecreated');
                    foreach ($rs as $record) {
                        $time = $record->timecreated;
                        $thisday = new DateTime(date('y-m-d', $time)); // Day of this login.

                        if (!$firstlogin) { // This is the first day. count as start of the streak.
                            $currentstreak = 1;
                        } else if ($firstlogin && $firstlogin->diff($thisday)->days <= 1) {
                            // Verify the streak is continues, is this login is next day or the same day.
                            $currentstreak = $firstlogin->diff($thisday)->days < 1 ? $currentstreak : $currentstreak + 1;
                        } else {
                            // Streak started before, difference more than 1 day with previous login.
                            break;
                        }

                        // Make this login as previous login to verify next login.
                        $firstlogin = $thisday;
                    }
                    $rs->close();

                    $streakreached = $currentstreak >= $streakdays ? 'dash-streak-highlight' : '';
                    // Attach the login streak count to the kpi results.
                    $result[$field] = $this->add_kpi(
                        $field,
                        $currentstreak,
                        ['customclass' => "$streakreached streak-$currentstreak"]
                    );

                    // Store the streaks count to cache, load from cache on next page loads.
                    $cache->set(self::CACHELOGINSTREAK, $currentstreak);
                    break;

                // Number of logins created by the user in this week.
                case 'loginsthisweek':
                    $lastweek = strtotime('this week'); // Timestamp of the last week.
                    $joparams = [
                        'jolsluserid' => $userid,
                        'jolsleventname' => '\core\event\user_loggedin',
                        'jolastweek' => $lastweek,
                    ];
                    // Join the log store and get the login events created after the last week.
                    $rawjoin = new join_raw("SELECT DISTINCT userid, count(*) AS loginsthisweek FROM {logstore_standard_log}
                                        WHERE timecreated >= :jolastweek AND userid = :jolsluserid AND eventname = :jolsleventname
                                        GROUP BY userid", 'lsl', 'userid', 'u.id', join_raw::TYPE_LEFT_JOIN, $joparams);

                    $query->join_raw($rawjoin);
                    $query->select('lsl.loginsthisweek', 'loginsthisweek');
                    // Fetch the count of logins and transform to user readable.
                    $transforms['loginsthisweek'] = fn($courses, $userdata) => $userdata->loginsthisweek ?: 0;
                    break;

                // Find the days since the last login of the user.
                case 'sincelogindays':
                    // User last login data will fetched from the user table, simply find the difference in days.
                    $transforms['sincelogindays'] = function ($courses, $userdata) {
                        $time = new DateTime(date('y-m-d', $userdata->u_lastlogin));
                        $today = new DateTime();
                        return $userdata->u_lastlogin ? $time->diff($today)->days : 0;
                    };
                    break;

                case 'onlineuserscount':
                    $result['onlineuserscount'] = $this->add_kpi($field, $this->get_current_online_users());
                    break;

                case 'numberofunreadmsg':
                    $val = \core_message\api::count_unread_conversations((object)['id' => $userid]);
                    $url = new moodle_url('/message/index.php', ['id' => $userid]);
                    $label = html_writer::link($url, get_string('label:numberofunreadmsg', 'block_dash'));
                    $result['numberofunreadmsg'] = $this->add_kpi($field, $val, [], $label);
                    break;

                case 'numberofcontactreq':
                    $val = \core_message\api::get_received_contact_requests_count($userid);
                    $url = new moodle_url('/message/index.php', ['id' => $userid]);
                    $label = html_writer::link($url, get_string('label:numberofcontactreq', 'block_dash'));
                    $result['numberofcontactreq'] = $this->add_kpi($field, $val, [], $label);
                    break;

                case 'completedcoursesinweek':
                    $lastweek = strtotime('this week');
                    $ccparams = ['ccuserid' => $userid, 'cclastweek' => $lastweek];

                    $sql = new join_raw("
                        SELECT DISTINCT userid, count(*) AS completions FROM {course_completions}
                        WHERE timecompleted >= :cclastweek AND userid = :ccuserid
                        GROUP BY userid", 'cc', 'userid', 'u.id', join::TYPE_LEFT_JOIN, $ccparams);

                    $query->join_raw($sql);
                    $query->select('cc.completions', 'completedcoursesinweek');

                    $transforms['completedcoursesinweek'] = fn($courses, $userdata) => $userdata->completedcoursesinweek ?: 0;
                    break;

                case 'completedactivitiesinweek':
                    $lastweek = strtotime('this week');
                    $cmcparams = ['cmcuserid' => $userid, 'cmclastweek' => $lastweek];

                    $sql = new join_raw(
                        "SELECT DISTINCT userid, count(*) AS completedactivitiesinweek FROM {course_modules_completion}
                            WHERE timemodified >= :cmclastweek AND userid = :cmcuserid AND completionstate >= 1
                            GROUP BY userid",
                        'cmc',
                        'userid',
                        'u.id',
                        join::TYPE_LEFT_JOIN,
                        $cmcparams
                    );

                    $query->join_raw($sql);
                    $query->select('cmc.completedactivitiesinweek', 'completedactivitiesinweek');

                    $transforms['completedactivitiesinweek'] = fn($courses, $userdata) => $userdata->completedactivitiesinweek ?: 0;
                    break;

                case 'teammemberscount':
                    $sql = new join_raw(
                        "SELECT ra.userid, count(*) as members
                            FROM {role_assignments} ra, {context} c, {user} u
                            WHERE (ra.userid = :rauserid) AND ra.contextid = c.id AND c.instanceid = u.id
                            AND c.contextlevel = :context_user GROUP BY ra.userid",
                        'ram',
                        'userid',
                        'u.id',
                        join::TYPE_LEFT_JOIN,
                        ['rauserid' => $userid, 'context_user' => CONTEXT_USER]
                    );

                    $query->select('ram.members', 'teammembers');
                    $query->join_raw($sql);
                    $transforms['teammemberscount'] = fn($courses, $userdata) => $userdata->teammembers ?: 0;
                    break;

                case 'earnedandtotalpoints':
                    if (!$this->is_plugin_installed('tool', 'skills')) {
                        break;
                    }

                    // Transforms the earned and total points for the skill to user readable format.
                    $transforms['earnedandtotalpoints'] = function ($courses, $userdata) use (&$transforms) {
                        global $DB;

                        $skillpoints = 0;
                        $courseids = array_column($courses, 'id');
                        if (!empty($courseids)) {
                            $skillpoints = helper::get_courses_skill_points($courseids);
                        }

                        return $transforms['earnedskillpoints']($courses, $userdata)
                            . \html_writer::tag('span', '/' . $skillpoints, ['class' => 'progress-divide']);
                    };
                    // Intentional fall-through to also set up earnedskillpoints transform.

                case 'earnedskillpoints':
                    if (!$this->is_plugin_installed('tool', 'skills')) {
                        break;
                    }
                    if (!isset($transforms['earnedskillpoints'])) {
                        $query->select('tsup.points', 'earnedskillpoints');
                        $query->join_raw(new join_raw(
                            'SELECT DISTINCT userid, SUM(points) AS points FROM {tool_skills_userpoints} GROUP BY userid',
                            'tsup',
                            "userid",
                            'u.id',
                            join::TYPE_LEFT_JOIN
                        ));

                        $transforms['earnedskillpoints'] = fn($courses, $userdata) => $userdata->earnedskillpoints ?: 0;
                    }
                    break;

                case "numberofoverdueactivities":
                case 'numberofdueactivities':
                    require_once($CFG->dirroot . '/local/dash/addon/myprofile/timemanagementlib.php');
                    $overdues = $dues = 0;

                    $transforms[$field] = function ($courses, $userdata) use (&$result, $field) {
                        $finaldues = 0;
                        $finaloverdues = 0;
                        foreach ($courses as $course) {
                            [$dues, $overdues] = dashaddon_myprofile_get_user_dueactivities($course->id, $userdata->u_id);
                            $finaldues += $dues;
                            $finaloverdues += $overdues;
                        }

                        if ($field == 'numberofoverdueactivities') {
                            // Number of overdue activities.
                            $label = get_string('label:numberofoverdueactivities', 'block_dash');
                            $result['numberofoverdueactivities'] = $this->add_kpi($field, $finaloverdues, [], $label);
                        }

                        if ($field == 'numberofdueactivities') {
                            // Number of due activities.
                            $result['numberofdueactivities'] = $this->add_kpi(
                                $field,
                                $finaldues,
                                [],
                                get_string('label:numberofdueactivities', 'block_dash')
                            );
                        }
                        return false;
                    };

                    // Number of due activities.
                    if ($field == 'numberofdueactivities') {
                        $result['numberofdueactivities'] = $this->add_kpi($field, $dues);
                    }

                    // Number of overdue activities.
                    if ($field == 'numberofoverdueactivities') {
                        $result['numberofoverdueactivities'] = $this->add_kpi($field, $overdues);
                    }

                    break;
            }
        }

        if (!$strategy = $this->get_layout()->get_data_strategy()) {
            throw new \coding_exception('Not fully configured.');
        }

        // Fetch user data based on the build query.
        $userdata = $query->query();

        if (empty($userdata)) {
            return false;
        }

        // Build the joins.
        $joins = implode(' ', $join);

        // Get all the courses of this user.
        $courses = $this->enrol_get_all_users_courses($userid, $select, $joins, $params);

        $user = current($userdata);
        // Transforms the fetched the values to user readable data.
        foreach ($transforms as $key => $transform) {
            $field = $key; // In some scenarios used the field value in transforms which uses the same query for data fetch.
            // TRansform the daata to user reatable format.
            $transformed = $transform($courses, $user);
            if (in_array($key, array_values($kpifields)) && $transformed !== false) {
                $result[$key] = $this->add_kpi($key, $transformed);
            }
        }

        // Reorder the results of kpi based on the selected preference order.
        if (!empty($result) && !empty($kpifields)) {
            // Flip the kpi fields values to keys, and replace the values of keys with result.
            $result = array_replace(array_flip(array_filter($kpifields)), $result);
            // Remove unselected kpi from list. Due activities are added if overdue is selected.
            $result = array_filter($result, function ($key) use ($kpifields) {
                return in_array($key, array_values($kpifields));
            }, ARRAY_FILTER_USE_KEY);
        }

        // Transform the records to user readable format based the fields attribute.
        $this->data = $strategy->convert_records_to_data_collection($userdata, $this->get_sorted_fields());
        $this->after_data($this->data); // Assign the transformaed user data to the selected userinfo fields.
        $this->data = $this->data->get_child_collections('rows'); // Data of the user fields.

        // Recreate the user data to array from data collection format.
        $userinfo = [];
        foreach ($this->data as $datacollection) {
            $fields = $datacollection->get_data();
            foreach ($fields as $field) {
                $userinfo[$field->get_name()] = $field->get_value();
            }
        }

        // Get user game avatar image.
        $userinfo['gameavatar'] = $this->get_user_gamepic($userid);
        $list = $this->get_all_preferences();
        $list['datasource'] = '';
        $list['layout'] = '';
        $enabled = array_filter($list);
        $userfields = ['profileimage', 'fullname', 'userinfo1', 'userinfo2', 'userinfo3'];

        $this->data = [
            'kpi' => array_values($result),
            'kpistatus' => !empty($result),
            'userinfo' => $userinfo,
            'userinfostatus' => !empty(array_intersect($userfields, array_keys($enabled))),
            'noresult' => empty($enabled),
        ];
        return $this->data;
    }

    /**
     * Check the myprofile contains any data to render.
     *
     * @return bool
     */
    public function is_empty() {
        $this->build_widget();
        return isset($this->data['noresult']) && $this->data['noresult'] ? true : false;
    }

    /**
     * Returns list of courses user is enrolled into without performing any capability checks.
     *
     * The $fields param is a list of field names to ADD so name just the fields you really need,
     * which will be added and uniq'd.
     *
     * COPY OF the enrollib method enrol_get_all_users_courses.
     *
     * @copyright 2010 Petr Skoda {@link http://skodak.org}
     *
     * @param int $userid User whose courses are returned, defaults to the current user.
     * @param string|array $fields Extra fields to be returned (array or comma-separated list).
     * @param [type] $joins Additional tables to be joined.
     * @param [type] $params Extra parameters helps to defined the joins conditions.
     * @return array
     */
    public function enrol_get_all_users_courses($userid, $fields = null, $joins = null, $params = null) {
        global $DB;

        // Guest account does not have any courses.
        if (isguestuser($userid) || empty($userid)) {
            return([]);
        }

        $basefields = ['id', 'category', 'sortorder', 'shortname', 'fullname',
            'idnumber', 'startdate', 'enddate', 'visible', 'defaultgroupingid', 'groupmode', 'groupmodeforce',
        ];

        $subwhere = "WHERE ue.status = :active AND e.status = :enabled
            AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
        $params['now1']    = round(time(), -2); // Improves db caching.
        $params['now2']    = $params['now1'];
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;

        $coursefields = 'c.' . join(',c.', $basefields);
        $coursefields .= ($fields) ? ', ' . implode(',', $fields) : '';
        $ccselect = ', ' . \context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;

        // Note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there.
        $sql = "SELECT $coursefields $ccselect
                FROM {course} c
                JOIN (
                    SELECT DISTINCT e.courseid
                        FROM {enrol} e
                        JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                        $subwhere
                    ) en ON (en.courseid = c.id)
                $ccjoin $joins
                WHERE c.id <> " . SITEID . "
            ";
        $params['userid']  = $userid;

        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    /**
     * Verify the plugin is installed or not.
     *
     * @param string $type
     * @param string $pluginname
     *
     * @return bool
     */
    protected function is_plugin_installed($type, $pluginname) {

        $installedplugins = \core_plugin_manager::instance()->get_installed_plugins($type);

        if (isset($installedplugins[$pluginname])) {
            return true;
        }

        return false;
    }

    /**
     * Get the user picuture from the game block.
     *
     * @param int $userid
     * @return string
     */
    protected function get_user_gamepic(int $userid) {
        global $CFG;

        if (!$this->is_plugin_installed('block', 'game')) {
            return false;
        }

        // Include game block lib file.
        require_once($CFG->dirroot . '/blocks/game/lib.php');

        // Fetch the user avatar image.
        $avatar = block_game_get_avatar_user($userid);

        // Get the config of game block, to verify the avatar is set to display to the user.
        $cfggame = get_config('block_game');
        $showavatar = !isset($cfggame->use_avatar) || $cfggame->use_avatar == 1;
        if ($showavatar && $avatar) {
            $img = $CFG->wwwroot . '/blocks/game/pix/a' . $avatar . '.svg"';
            $fs = get_file_storage();
            if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $avatar . '.svg')) {
                $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $avatar);
            }
        }
        return $img ?? false;
    }

    /**
     * Get the current online users count, based on online_users block.
     *
     * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
     *
     * @return void
     */
    protected function get_current_online_users() {
        global $CFG, $PAGE;

        $timetoshowusers = 300; // Seconds default.
        if (isset($CFG->block_online_users_timetosee)) {
            $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        }
        $now = time();

        // Calculate if we are in separate groups.
        $isseparategroups = ($PAGE->course->groupmode == SEPARATEGROUPS
                             && $PAGE->course->groupmodeforce
                             && !has_capability('moodle/site:accessallgroups', $PAGE->context));

        // Get the user current group.
        $currentgroup = $isseparategroups ? groups_get_course_group($PAGE->course) : null;

        $sitelevel = $PAGE->course->id == SITEID || $PAGE->context->contextlevel < CONTEXT_COURSE;

        $onlineusers = new \block_online_users\fetcher(
            $currentgroup,
            $now,
            $timetoshowusers,
            $PAGE->context,
            $sitelevel,
            $PAGE->course->id
        );

        // Calculate minutes.
        $minutes = floor($timetoshowusers / 60);
        $periodminutes = get_string('periodnminutes', 'block_online_users', $minutes);

        // Count users.
        $usercount = $onlineusers->count_users();

        return $usercount;
    }

    /**
     * Prefence form for widget. We make the fields disable other than the general.
     *
     * @param \moodleform $form
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {

        if ($form->get_tab() == preferences_form::TAB_GENERAL) {
            $mform->addElement('static', 'data_source_name', get_string('datasource', 'block_dash'), $this->get_name());
        }

        if ($layout = $this->get_layout()) {
            $layout->build_preferences_form($form, $mform);
        }
    }

    /**
     * Build and return filter collection.
     *
     * @return filter_collection_interface
     * @throws coding_exception
     */
    public function build_filter_collection() {
        global $PAGE;

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        return $filtercollection;
    }
}
