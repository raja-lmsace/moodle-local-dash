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
 * Subplugin type for local dash - defined.
 *
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\plugininfo;
use moodle_url;

/**
 * Dashaddon is subplugin of local_dash.
 */
class dashaddon extends \core\plugininfo\base {
    /**
     * Return URL used for management of plugins of this type.
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new moodle_url('/local/dash/manageaddon.php', ['subtype' => 'dashaddon']);
    }

    /**
     * Get name to identify section
     *
     * @return string
     */
    public function get_settings_section_name() {

        if ($this->is_dashaddon_disabled($this->name)) {
            return null;
        }

        return $this->type . '_' . $this->name;
    }

    /**
     * Allow uninstall
     *
     * @return bool
     */
    public function is_uninstall_allowed() {

        if ($this->is_dashaddon_disabled($this->name)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the information about plugin availability
     *
     * True means that the plugin is enabled. False means that the plugin is
     * disabled. Null means that the information is not available, or the
     * plugin does not support configurable availability or the availability
     * can not be changed.
     *
     * @return null|bool
     */
    public function is_enabled() {

        if ($this->is_dashaddon_disabled($this->name)) {
            return false;
        }

        // Return true only if the addon is enabled in config.
        return !empty(get_config($this->type . '_' . $this->name, 'enabled'));
    }

    /**
     * Loads plugin settings to the settings tree
     *
     * This function usually includes settings.php file in plugins folder.
     * Alternatively it can create a link to some settings page (instance of admin_externalpage)
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {

        $ADMIN = $adminroot; // May be used in settings.php.
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig || !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();
        if ($section != null) {
            $page = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
            include($this->full_path('settings.php')); // This may also set $settings to null.

            if ($page) {
                $ADMIN->add($parentnodename, $page);
            }
        }
    }

    /**
     * Check the addon is disabled from the config file parameters.
     *
     * @param string $name
     * @return bool
     */
    public function is_dashaddon_disabled($name) {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/dash/lib.php');
        $disabledaddons = block_dash_disabled_addons_list();

        // Check if the addon is in the disabled list.
        if (in_array($name, $disabledaddons, true)) {
            return true;
        }
        return false;
    }
}
