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
 * Compentancy widget administration settings page.
 *
 * @package    dashaddon_skill_graph
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/blocks/dash/lib.php');

if (get_config('dashaddon_skill_graph', 'enabled') && !(in_array('skill_graph', block_dash_disabled_addons_list()))) {
    $ADMIN->add('localdashsettings', new \admin_externalpage(
        'dashaddonskillgraph',
        get_string('managecompentency', 'block_dash'),
        new \moodle_url('/local/dash/addon/skill_graph/competencylist.php'),
        'moodle/competency:competencymanage'
    ));
}
