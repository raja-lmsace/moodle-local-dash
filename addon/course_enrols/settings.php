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
 * Course enrols widget administration settings.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $options = get_default_enrol_roles(\context_system::instance());
    $student = get_archetype_roles('student');
    $student = reset($student);
    $name = 'local_dash/course_enrol_role';
    $title = get_string('course_enrol_role', 'block_dash');
    $setting = new admin_setting_configselect($name, $title, "", isset($student->id) ? $student->id : null, $options);
    $page->add($setting);
}
