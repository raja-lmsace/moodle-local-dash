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
 * Define dashboard helper.
 *
 * @package    dashaddon_dashboard
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard;
use stdClass;
use context_system;

/**
 * Class dashboard helper.
 */
class helper {

    /**
     * Postupdate the filemanager files.
     * @param object $dashboard
     */
    public static function postupdate_filemanager_files($dashboard) {
    }

    /**
     * Loads the prepare filemanager files.
     * @param object $dashboard
     */
    public static function prepare_filemanger_files($dashboard) {
        $itemid = isset($dashboard->id) ? $dashboard->id : null;
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        foreach ($filemanagers as $filemanager) {
            $dashboard = file_prepare_standard_filemanager($dashboard, $filemanager, self::get_filemanager_options(),
                \context_system::instance(), 'local_dash', $filemanager, $itemid);
        }
        return $dashboard;
    }

    /**
     * Dashboard form filemanager element options.
     *
     * @return array
     */
    public static function get_filemanager_options() {
        global $CFG;
        return [
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'context' => \context_system::instance(),
            'noclean' => true,
        ];
    }
}
