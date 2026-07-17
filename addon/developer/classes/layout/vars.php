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
 * Template placeholder definitions for custom layouts.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\layout;

/**
 * Template placeholder definitions for custom layouts.
 *
 * @package dashaddon_developer
 */
class vars {
    /**
     * Get available placeholder groups.
     *
     * @return array
     */
    public static function get_vars() {
        return [
            'User' => self::user_profile_fields(),
            'Course' => self::course_fields(),
        ];
    }

    /**
     * User information placeholders.
     *
     * @return array
     */
    public static function user_profile_fields() {
        global $CFG;

        require_once($CFG->dirroot . '/lib/authlib.php');

        static $fields;

        if ($fields === null) {
            $fields = [
                'User_Id', 'User_Idnumber', 'User_Firstname', 'User_Lastname', 'User_Fullname', 'User_Middlename',
                'User_Alternatename', 'User_Email', 'User_Username', 'User_Institution', 'User_Department',
                'User_Address', 'User_City', 'User_Country', 'User_Mobile', 'User_Phone',
            ];

            $profilefields = (new \auth_plugin_base())->get_custom_user_profile_fields();

            foreach ($profilefields as $profilefield) {
                $shortname = str_replace('profile_field', '', $profilefield);
                $shortname = ltrim($shortname, '_');

                $fields[] = 'User_Profilefield_' . $shortname;
            }
        }

        return $fields;
    }

    /**
     * Course information placeholders.
     *
     * @return array
     */
    public static function course_fields() {
        return [
            'Course_Fullname', 'Course_Shortname', 'Course_Summary', 'Course_Summaryplain', 'Course_Courseurl',
            'Course_Startdate', 'Course_Enddate', 'Course_Id', 'Course_Category', 'Course_Idnumber',
            'Course_Format', 'Course_Visible', 'Course_Groupmode', 'Course_Groupmodeforce',
            'Course_Defaultgroupingid', 'Course_Lang', 'Course_Calendartype', 'Course_Theme',
            'Course_Timecreated', 'Course_Timemodified', 'Course_Enablecompletion',
            'Course_categories_Id', 'Course_categories_Name',
        ];
    }

    /**
     * Get current course placeholder context.
     *
     * @param mixed $row
     * @return array
     */
    public static function current_course_context($row = null) {
        global $DB;

        $course = null;
        $category = null;

        $courseid = !empty($row) && !empty($row['c_id']) ? $row['c_id'] : 0;
        $categoryid = !empty($row) && !empty($row['cc_id']) ? $row['cc_id'] : 0;

        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
        }
        if ($categoryid) {
            $category = $DB->get_record('course_categories', ['id' => $categoryid]);
        }

        $context = [
                    'Course_Fullname' => '', 'Course_Shortname' => '', 'Course_Summary' => '',
                    'Course_Summaryplain' => '', 'Course_Courseurl' => '', 'Course_Startdate' => '',
                    'Course_Enddate' => '', 'Course_Id' => '', 'Course_Category' => '',
                    'Course_Idnumber' => '', 'Course_Format' => '', 'Course_Visible' => '',
                    'Course_Groupmode' => '', 'Course_Groupmodeforce' => '', 'Course_Defaultgroupingid' => '',
                    'Course_Lang' => '', 'Course_Calendartype' => '', 'Course_Theme' => '',
                    'Course_Timecreated' => '', 'Course_Timemodified' => '', 'Course_Enablecompletion' => '',
                    'Course_categories_Id' => '', 'Course_categories_Name' => '',
        ];

        if ($course) {
            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $summary = format_text($course->summary, $course->summaryformat);

            $context['Course_Fullname'] = $course->fullname ?? '';
            $context['Course_Shortname'] = $course->shortname ?? '';
            $context['Course_Summary'] = $summary;
            $context['Course_Summaryplain'] = strip_tags($summary);
            $context['Course_Courseurl'] = $courseurl->out(false);
            $context['Course_Startdate'] = !empty($course->startdate) ? userdate($course->startdate) : '';
            $context['Course_Enddate'] = !empty($course->enddate) ? userdate($course->enddate) : '';
            $context['Course_Id'] = $course->id ?? '';
            $context['Course_Category'] = $course->category ?? '';
            $context['Course_Idnumber'] = $course->idnumber ?? '';
            $context['Course_Format'] = $course->format ?? '';
            $context['Course_Visible'] = isset($course->visible) ? $course->visible : '';
            $context['Course_Groupmode'] = $course->groupmode ?? '';
            $context['Course_Groupmodeforce'] = $course->groupmodeforce ?? '';
            $context['Course_Defaultgroupingid'] = $course->defaultgroupingid ?? '';
            $context['Course_Lang'] = $course->lang ?? '';
            $context['Course_Calendartype'] = $course->calendartype ?? '';
            $context['Course_Theme'] = $course->theme ?? '';
            $context['Course_Timecreated'] = !empty($course->timecreated) ? userdate($course->timecreated) : '';
            $context['Course_Timemodified'] = !empty($course->timemodified) ? userdate($course->timemodified) : '';
            $context['Course_Enablecompletion'] = isset($course->enablecompletion) ? $course->enablecompletion : '';
            $context['Course_categories_Id'] = $category->id ?? '';
            $context['Course_categories_Name'] = $category->name ?? '';
        }
        if ($category) {
            $context['Course_categories_Id'] = $category->id ?? '';
            $context['Course_categories_Name'] = $category->name ?? '';
        }
        $lowercontext = [];
        foreach ($context as $key => $value) {
            $lowercontext[strtolower($key)] = $value;
        }

        $context = array_merge($context, $lowercontext);

        return $context;
    }
    /**
     * Get current user placeholder context.
     * @param \stdClass|null $user
     * @return array
     */
    public static function current_user_context($user = null) {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot . '/lib/authlib.php');
        require_once($CFG->dirroot . '/user/profile/lib.php');
        if (!empty($user) && !empty($user->id)) {
            $currentuser = \core_user::get_user($user->id, '*', MUST_EXIST);
        } else {
            return [];
        }

        $newuser = (object) ['id' => $currentuser->id];
        profile_load_data($newuser);

        $currentuserkeys = array_map(function ($value) {
            return strtolower(str_replace('profile_field_', '', $value));
        }, array_keys((array) $newuser));

        $currentuser->profilefield = (object) array_combine($currentuserkeys, (array) $newuser);

        $context = [
            'User_Id' => $currentuser->id ?? '',
            'User_Idnumber' => $currentuser->idnumber ?? '',
            'User_Firstname' => $currentuser->firstname ?? '',
            'User_Lastname' => $currentuser->lastname ?? '',
            'User_Fullname' => fullname($currentuser),
            'User_Middlename' => $currentuser->middlename ?? '',
            'User_Alternatename' => $currentuser->alternatename ?? '',
            'User_Email' => $currentuser->email ?? '',
            'User_Username' => $currentuser->username ?? '',
            'User_Institution' => $currentuser->institution ?? '',
            'User_Department' => $currentuser->department ?? '',
            'User_Address' => $currentuser->address ?? '',
            'User_City' => $currentuser->city ?? '',
            'User_Country' => $currentuser->country ?? '',
            'User_Mobile' => $currentuser->phone2 ?? '',
            'User_Phone' => $currentuser->phone1 ?? '',
        ];

        $lowercontext = [];

        foreach ($context as $key => $value) {
            $lowercontext[strtolower($key)] = $value;
        }
        $context = array_merge($context, $lowercontext);

        foreach ((array) $currentuser->profilefield as $shortname => $value) {
            if (is_array($value)) {
                if (isset($value['text'])) {
                    $value = $value['text'];
                } else {
                    $value = implode(', ', array_filter($value, function ($item) {
                        return is_scalar($item) || $item === null;
                    }));
                }
            } else if (is_object($value)) {
                if (isset($value->text)) {
                    $value = $value->text;
                } else {
                    $value = '';
                }
            }

            $context['user_profilefield_' . strtolower($shortname)] = $value ?? '';
            $context['User_Profilefield_' . $shortname] = $value ?? '';
        }

        return $context;
    }
}
