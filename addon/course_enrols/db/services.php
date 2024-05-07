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
 * Define external service functions.
 *
 * @package    dashaddon_course_enrols
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'dashaddon_course_enrols_submit_user_enrolment_form' => [
       'classname' => 'dashaddon_course_enrols\external',
       'methodname' => 'submit_user_enrolment_form',
       'description' => 'Generate a course backup file and return a link.',
       'type' => 'read',
       'ajax'        => true,
    ],

    'dashaddon_course_enrols_unenrol_user_enrolment' => [
        'classname' => 'dashaddon_course_enrols\external',
        'methodname' => 'unenrol_user_enrolment',
        'description' => 'External function that unenrols a given user enrolment',
        'type' => 'write',
        'ajax' => true,
    ],

    'dashaddon_course_enrols_enrol_courses' => [
        'classname' => 'dashaddon_course_enrols\external',
        'methodname' => 'enrol_courses',
        'description' => 'External function that unenrols a given user enrolment',
        'type' => 'write',
        'ajax' => true,
    ],
];
