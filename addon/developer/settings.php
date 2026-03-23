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
 * Developer addon administratin settings defined here.
 *
 * @package   dashaddon_developer
 * @copyright 2025 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/dash/lib.php');

if (get_config('dashaddon_developer', 'enabled') && !(in_array('developer', block_dash_disabled_addons_list()))) {
    $ADMIN->add('localdashsettings', new admin_externalpage(
        'localdashmanagedatasources',
        get_string('managedatasources', 'block_dash'),
        new moodle_url('/local/dash/datasources.php'),
        'dashaddon/developer:managecustomdatasources'
    ));

    $ADMIN->add('localdashsettings', new admin_externalpage(
        'localdashmanagelayouts',
        get_string('managelayouts', 'block_dash'),
        new moodle_url('/local/dash/addon/developer/customlayouts.php'),
        'dashaddon/developer:managecustomlayouts'
    ));
}
