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
 * Plugin administration pages are defined here.
 *
 * @package     local_dash
 * @category    admin
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $ADMIN->add('localplugins', new admin_category('localdashsettings', get_string('pluginname', 'local_dash')));

    $settings = null;

    $page = new admin_settingpage('localdashgeneralsettings', get_string('generalsettings', 'block_dash'));

    $name = 'local_dash/courseimage';
    $title = get_string('courseimage', 'block_dash');
    $description = get_string('courseimagedesc', 'block_dash');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'courseimage');
    $page->add($setting);

    // Prevent user acces of course index.php.
    $name = 'local_dash/hidecoursecategory';
    $title = get_string('hidecoursecategory', 'block_dash');
    $description = get_string('hidecoursecategorydesc', 'block_dash');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    // Content addon restrict to current section.
    $name = 'local_dash/restrictcurrentsection';
    $title = get_string('restrictcurrentsection', 'block_dash');
    $description = get_string('restrictcurrentsection_desc', 'block_dash');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);

    $name = 'local_dash/courseredirecturl';
    $title = get_string('courseredirecturl', 'block_dash');
    $description = get_string('courseredirecturldesc', 'block_dash');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    require_once($CFG->dirroot.'/local/dash/lib.php');

    $name = 'local_dash/courseshopurl';
    $title = get_string('courseshopurl', 'block_dash');
    $coursefields = local_dash_get_coursefields();
    $setting = new admin_setting_configselect($name, $title, '', null, $coursefields);
    $page->add($setting);

    // Course field for shape
    $name = 'local_dash/customselectfield';
    $title = get_string('customselectfield', 'local_dash');
    $description = get_string('customselectfield_desc', 'local_dash');
    $coursefieldoptions = local_dash_get_custom_coursefields();
    $setting = new admin_setting_configselect($name, $title, $description, 0, $coursefieldoptions);
    $page->add($setting);

    $selectedshapefieldid = get_config('local_dash', 'customselectfield');
    if ($selectedshapefieldid && $selectedshapefieldid != 0) {
        $shapefieldoptions = local_dash_get_custom_field_options($selectedshapefieldid);

        if (!empty($shapefieldoptions)) {
            foreach ($shapefieldoptions as $optionid => $optionname) {
                if ($optionid == 0) {
                    continue;
                }

                $shapes = [
                    'circle' => get_string('shape:circle', 'block_dash'),
                    'triangle' => get_string('shape:triangle', 'block_dash'),
                    'hexagon' => get_string('shape:hexagon', 'block_dash'),
                    'diamond' => get_string('shape:diamond', 'block_dash'),
                    'star' => get_string('shape:star', 'block_dash'),
                ];

                $name = 'local_dash/shapemap_' . $selectedshapefieldid . '_' . $optionid;
                $title = get_string('shapemapping', 'local_dash') . ': ' . $optionname;
                $description = get_string('shapemapping_desc', 'local_dash');

                $setting = new admin_setting_configselect($name, $title, $description, 'circle', $shapes);
                $page->add($setting);
            }
        }
    }

    // Visual icon select field.
    $name = 'local_dash/customvisualfield';
    $title = get_string('customvisualfield', 'local_dash');
    $description = get_string('customvisualfield_desc', 'local_dash');
    $coursefieldoptions = local_dash_get_custom_coursefields();
    $setting = new admin_setting_configselect($name, $title, $description, 0, $coursefieldoptions);
    $page->add($setting);

    $fieldid = get_config('local_dash', 'customvisualfield');
    $fieldoptions = [];

    if ($fieldid) {
        $fieldoptions = local_dash_get_custom_field_options($fieldid);
    }

    $selectedfieldid = get_config('local_dash', 'customvisualfield');
    $options = local_dash_get_custom_field_options($selectedfieldid);

    if (!empty($options)) {
        foreach ($options as $optionid => $optionname) {
            if ($optionid == 0) {
                continue;
            }

            $filearea = 'customvisualicon_' . $optionid;
            $name = 'local_dash/' . $filearea;
            $title = get_string('customvisualicon', 'local_dash') . ': ' . $optionname;
            $description = get_string('customvisualicon_desc', 'local_dash');
            $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
            $page->add($setting);
        }
    }

    $ADMIN->add('localdashsettings', $page);

    $ADMIN->add('localdashsettings', new admin_externalpage(
        'localdashmanagedashboards',
        get_string('managedashboards', 'block_dash'),
        new moodle_url('/local/dash/addon/dashboard/dashboard_list.php')));

    $ADMIN->add('localdashsettings', new admin_externalpage('managedashaddonplugins',
        get_string('managedashaddonplugins', 'block_dash'),
        new moodle_url('/local/dash/manageaddon.php', ['subtype' => 'dashaddon'])));

    $ADMIN->add('appearance', new admin_externalpage(
        'localdashmanagedashboards2',
        get_string('managedashboards', 'block_dash'),
        new moodle_url('/local/dash/addon/dashboard/dashboard_list.php')));

}

foreach (core_plugin_manager::instance()->get_plugins_of_type('dashaddon') as $plugin) {
    // Load all the dashaddon plugins settings pages.
    $plugin->load_settings($ADMIN, 'localdashsettings', $hassiteconfig);
}
