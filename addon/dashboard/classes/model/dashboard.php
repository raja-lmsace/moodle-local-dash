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
 * Modal class for dashboard report.
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\model;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/dash/lib.php');
require_once($CFG->dirroot. "/local/dash/addon/dashboard/lib.php");

/**
 * Dashboard class.
 */
class dashboard extends persistent {
    /**
     * Dashboard modal db tablename.
     */
    const TABLE = 'dashaddon_dashboard_dash';

    /**
     * Check user Loggedin.
     */
    const PERMISSION_LOGGEDIN = 'loggedin';

    /**
     * Check user has cohort permission
     */
    const PERMISSION_COHORT = 'cohort';

    /**
     * Check user has cohort permission
     */
    const PERMISSION_ROLE = 'role';

    /**
     * case modal has public access.
     */
    const PERMISSION_PUBLIC = 'public';

    /**
     * Defined properties modal contains.
     *
     * @return array
     */
    protected static function define_properties() {
        $props = [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'contextid' => [
                'type' => PARAM_INT,
            ],
            'permission' => [
                'type' => PARAM_TEXT,
            ],
            'cohort_id' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'roles' => [
                'type' => PARAM_RAW,
            ],
            'rolecontext' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'shortname' => [
                'type' => PARAM_ALPHAEXT,
                'message' => new \lang_string('invalidshortname', 'block_dash'),
            ],
            'secondarynav' => [
                'type' => PARAM_INT,
            ],
            'description' => [
                'type' => PARAM_CLEANHTML,
                'default' => ''
            ],
            'descriptionformat' => [
                'choices' => [FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN],
                'type' => PARAM_INT,
                'default' => FORMAT_HTML
            ],
            'dashicon' => [
                'type' => PARAM_TEXT,
            ],
            'dashthumbnailimage' => [
                'type' => PARAM_INT,
            ],
            'dashbgimage' => [
                'type' => PARAM_INT,
            ],
        ];
        return $props;
    }

    /**
     * Check if user can view dashboard.
     *
     * @param \stdClass $user
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function has_access(\stdClass $user) {
        global $CFG, $DB;

        $context = \context::instance_by_id($this->get('contextid'));

        require_once("$CFG->dirroot/cohort/lib.php");

        $course = null;
        if ($coursecontext = $context->get_course_context(false)) {
            $course = $DB->get_record('course', ['id' => $coursecontext->instanceid]);
        }

        if (has_capability('local/dash:managedashboards', $context)) {
            return true;
        }

        switch ($this->get('permission')) {
            case self::PERMISSION_LOGGEDIN:
                try {
                    if ($course) {
                        return is_enrolled(\context_course::instance($course->id), $user);
                    } else {
                        require_login(null, false, null, false, true);
                    }
                } catch (\require_login_exception $e) {
                    return false;
                }

                return true;
            case self::PERMISSION_COHORT:
                return cohort_is_member($this->get('cohort_id'), $user->id);
            case self::PERMISSION_ROLE:
                    $roles = json_decode($this->get('roles'));
                    // Roles not mentioned then stop the role check.
                    if ($roles == '' || empty($roles)) {
                        return false;
                    }

                    // Verify the default user role is set to view the dashboard.
                    $defaultuserroleid = isset($CFG->defaultuserroleid) ? $CFG->defaultuserroleid : 0;
                    if ($defaultuserroleid && in_array($defaultuserroleid, $roles) && !empty($user->id) && !isguestuser($user->id)) {
                        return true;
                    }

                    // Verify the guest user have view the dashboard.
                    if (isguestuser()) {
                        $guestroles = get_archetype_roles('guest');
                        $guestroleid = array_column($guestroles, 'id');
                        if (array_intersect($guestroleid, $roles)) {
                            return true;
                        }
                    }

                    list($insql, $inparam) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'role');

                    $contextsql = ($this->get('rolecontext') == SYSTEMCONTEXT) ? ' AND contextid=:systemcontext ' : '';

                    $sql = "SELECT u.* FROM {user} u WHERE u.id=:userid AND u.id IN
                            (SELECT userid FROM {role_assignments} WHERE roleid $insql AND userid=:rluserid $contextsql)";

                    $params = [
                        'userid' => $user->id,
                        'rluserid' => $user->id,
                        'systemcontext' => \context_system::instance()->id,
                    ];
                    $mainparms = array_merge($params, $inparam);

                    $records = $DB->get_records_sql($sql, $mainparms);

                    // Records found user will have access otherwise restrict the user to view the dashboard.
                    return count($records) > 0 ? true : false;
            case self::PERMISSION_PUBLIC:
                // No permission check.
                return true;
            default:
                return false;
        }
    }

    /**
     * Validate the shortname
     *
     * @param int $value The value.
     * @return true|\lang_string
     */
    protected function validate_shortname($value) {
        if (self::record_exists_select('shortname = ? AND id != ?', [$value, $this->get('id')])) {
            return new \lang_string('invalidshortnameunique', 'block_dash');
        }

        if (strlen($value) > 16) {
            return new \lang_string('invalidshortnametoolong', 'block_dash');
        }

        return true;
    }

    public function prepare_dashboard_filemanager() {
        global $DB;
        $dashboard = $DB->get_record(static::TABLE, array('id' => $this->get('id')), '*', MUST_EXIST);
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        $upd = new \stdClass();
        $upd->id = $dashboard->id;
        foreach ($filemanagers as $field) {
            file_save_draft_area_files($dashboard->{$field}, \context_system::instance()->id, 'dashaddon_dashboard', $field, $dashboard->id, self::get_filemanager_options());
            $upd->{$field} = $dashboard->{$field};
        }
        $DB->update_record('dashaddon_dashboard_dash', $upd);
    }

    public function after_create() {
        $this->prepare_dashboard_filemanager();
    }


    public function after_update($result) {
        global $DB;
        $this->prepare_dashboard_filemanager();
        $dashboard = $DB->get_record(static::TABLE, array('id' => $this->get('id')), '*', MUST_EXIST);
        $update = new \stdClass();
        $update->id = $dashboard->id;
        $update->roles = $dashboard->roles;
        $DB->update_record('dashaddon_dashboard_dash', $update);
    }

    public function prepare_filemanger_files() {
        global $DB;
        $record = $DB->get_record(static::TABLE, array('id' => $this->get('id')), '*', MUST_EXIST);
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        foreach ($filemanagers as $field) {
            $draftid_editor = file_get_submitted_draft_itemid($field.'_filemanager');
            file_prepare_draft_area($draftid_editor, \context_system::instance()->id, 'dashaddon_dashboard', $field, $record->id, self::get_filemanager_options());
            $this->raw_set($field, $draftid_editor);
        }
    }

    /**
     * Dashboard form filemanager element options.
     *
     * @return array
     */
    public static function get_filemanager_options() {
        global $CFG;
        return array(
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'context' => \context_system::instance(),
            'noclean' => true,
            'subdirs' => false,
        );
    }

    /**
     * Before validate the properties.
     */
    public function before_validate() {
        $value = $this->raw_get('roles');
        $this->raw_set('roles', json_encode($value));
    }

    /**
     * Set the roles in the dashboard form.
     */
    public function set_roles_data() {
        global $DB;
        $record = $DB->get_record(static::TABLE, array('id' => $this->get('id')), '*', MUST_EXIST);
        if (!empty($record->roles)) {
            $roles = json_decode($record->roles);
            if (!empty($roles)) {
                if (!is_array($roles)) {
                    $roles = explode(',', $roles);
                }
                $role = implode(',', $roles);
                $this->raw_set('roles', $role);
            }
        }
    }
}
