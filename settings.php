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

    require_once($CFG->dirroot . '/local/dash/lib.php');
    require_once($CFG->dirroot . '/local/dash/classes/admin_setting_configfontawesomeicon.php');

    $name = 'local_dash/courseshopurl';
    $title = get_string('courseshopurl', 'block_dash');
    $coursefields = local_dash_get_coursefields();
    $setting = new admin_setting_configselect($name, $title, '', null, $coursefields);
    $page->add($setting);

    // Course field for shape.
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
     $setting->set_updatedcallback('local_dash_reset_fontawesome_icon_map');
    $page->add($setting);

    $fieldid = get_config('local_dash', 'customvisualfield');
    $fieldoptions = [];

    if ($fieldid) {
        $fieldoptions = local_dash_get_custom_field_options($fieldid);
    }

    // Icon mapping for visual custom field options.
    $selectedvisualfieldid = get_config('local_dash', 'customvisualfield');
    if ($selectedvisualfieldid && $selectedvisualfieldid != 0) {
        $visualfieldoptions = local_dash_get_custom_field_options($selectedvisualfieldid);

        if (!empty($visualfieldoptions)) {
            // Build icon map once for callback.
            $iconmap = local_dash_build_fa_icon_map();

            foreach ($visualfieldoptions as $optionid => $optionname) {
                if ($optionid == 0) {
                    continue;
                }

                $identifier = 'customvisualicon_' . $selectedvisualfieldid . '_' . $optionid;
                $name = 'local_dash/' . $identifier;
                $title = get_string('customvisualicon', 'local_dash') . ': ' . $optionname;
                $description = get_string('customvisualicon_desc', 'local_dash');

                // Get default value.
                $default = get_config('local_dash', $identifier);

                // Setup autocomplete options.
                $autocompleteoptions = [
                    'ajax' => 'local_dash/fontawesome_icon_selector',
                    'placeholder' => get_string('pickicon', 'block_dash'),
                    'noselectionstring' => get_string('noiconselected', 'block_dash'),
                    'showsuggestions' => true,
                    'tags' => false,
                    'casesensitive' => false,
                    // Value HTML callback for icon rendering.
                    'valuehtmlcallback' => function ($value = '') use ($iconmap) {
                        global $OUTPUT;

                        // Handle empty value.
                        if (empty($value) || !is_string($value)) {
                            return '';
                        }

                        // Check if icon exists in map.
                        if (!isset($iconmap[$value])) {
                            return htmlspecialchars($value);
                        }

                        $source = $iconmap[$value]['source'] ?? '';
                        $icon = null;

                        // Build icon object based on source.
                        switch ($source) {
                            case 'core':
                                $icon = (object)[
                                    'class' => $iconmap[$value]['class'],
                                    'name' => $value,
                                    'source' => get_string('sourcecore', 'block_dash'),
                                    'sourcecolor' => 'bg-warning text-dark',
                                ];
                                break;

                            case 'fasolid':
                                $icon = (object)[
                                    'class' => 'fas ' . $iconmap[$value]['class'],
                                    'name' => $iconmap[$value]['class'],
                                    'source' => get_string('sourcefasolid', 'block_dash'),
                                    'sourcecolor' => 'bg-success',
                                ];
                                break;

                            case 'fabrand':
                                $icon = (object)[
                                    'class' => 'fab ' . $iconmap[$value]['class'],
                                    'name' => $iconmap[$value]['class'],
                                    'source' => get_string('sourcefabrand', 'block_dash'),
                                    'sourcecolor' => 'bg-success',
                                ];
                                break;

                            case 'fablank':
                                $icon = (object)[
                                    'class' => 'fab ' . $iconmap[$value]['class'],
                                    'name' => $iconmap[$value]['class'],
                                    'source' => get_string('sourcefablank', 'block_dash'),
                                    'sourcecolor' => 'bg-success',
                                ];
                                break;

                            default:
                                return htmlspecialchars($value);
                        }

                        // Render template if icon was built.
                        if ($icon) {
                            try {
                                return $OUTPUT->render_from_template(
                                    'local_dash/form_autocomplete_fontawesome_icon',
                                    $icon
                                );
                            } catch (\Exception $e) {
                                debugging('Error rendering icon template: ' . $e->getMessage(), DEBUG_DEVELOPER);
                                return htmlspecialchars($value);
                            }
                        }

                        return htmlspecialchars($value);
                    },
                ];

                // Create AJAX autocomplete setting.
                $setting = new \local_dash\admin_setting_ajax_autocomplete(
                    $name,
                    $title,
                    $description,
                    '',
                    $autocompleteoptions
                );

                $setting->set_updatedcallback('local_dash_reset_fontawesome_icon_map');

                $page->add($setting);
            }
        }
    }

    $ADMIN->add('localdashsettings', $page);

    $ADMIN->add('localdashsettings', new admin_externalpage(
        'localdashmanagedashboards',
        get_string('managedashboards', 'block_dash'),
        new moodle_url('/local/dash/addon/dashboard/dashboard_list.php')
    ));

    $ADMIN->add('localdashsettings', new admin_externalpage(
        'managedashaddonplugins',
        get_string('managedashaddonplugins', 'block_dash'),
        new moodle_url('/local/dash/manageaddon.php', ['subtype' => 'dashaddon'])
    ));

    $ADMIN->add('appearance', new admin_externalpage(
        'localdashmanagedashboards2',
        get_string('managedashboards', 'block_dash'),
        new moodle_url('/local/dash/addon/dashboard/dashboard_list.php')
    ));


    foreach (core_plugin_manager::instance()->get_plugins_of_type('dashaddon') as $plugin) {
        // Load all the dashaddon plugins settings pages.
        $plugin->load_settings($ADMIN, 'localdashsettings', $hassiteconfig);
    }
}
