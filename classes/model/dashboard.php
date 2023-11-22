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
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\model;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/dash/lib.php');

/**
 * Dashboard class.
 */
class dashboard extends persistent {
    /**
     * Dashboard modal db tablename.
     */
    const TABLE = 'local_dash_dashboard';

    /**
     * Check user Loggedin.
     */
    const PERMISSION_LOGGEDIN = 'loggedin';

    /**
     * Check user has cohort permission
     */
    const PERMISSION_COHORT = 'cohort';

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
                'type' => PARAM_TEXT
            ],
            'contextid' => [
                'type' => PARAM_INT
            ],
            'permission' => [
                'type' => PARAM_TEXT
            ],
            'cohort_id' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null
            ],
            'shortname' => [
                'type' => PARAM_ALPHAEXT,
                'message' => new \lang_string('invalidshortname', 'block_dash')
            ],
            'secondarynav' => [
                'type' => PARAM_INT,
            ]
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
}
