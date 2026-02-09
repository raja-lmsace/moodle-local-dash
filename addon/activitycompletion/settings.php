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
 * Plugin administration pages are defined here.
 *
 * @package    dashaddon_activitycompletion
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use dashaddon_activitycompletion\widget\activitycompletion_widget;

if ($hassiteconfig) {
    $settings = [
        'activitynotcompletedcolor' => activitycompletion_widget::COLORNOTCOMPLETED,
        'activitycompletedcolor' => activitycompletion_widget::COLORCOMPLETED,
        'activitypassedcolor' => activitycompletion_widget::COLORPASSED,
        'activityfailedcolor' => activitycompletion_widget::COLORFAILED,
    ];

    foreach ($settings as $text => $color) {
        $name = "dashaddon_activitycompletion/$text";
        $title = get_string($text, 'block_dash');
        $description = get_string($text . '_desc', 'block_dash');
        $setting = new \admin_setting_configcolourpicker($name, $title, $description, $color);
        $page->add($setting);
    }
}
