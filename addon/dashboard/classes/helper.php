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
     * Get the list of blocks on the current page
     *
     * @param string $shortname The shortname of the page
     * @return array Array of block options
     */
    public static function get_dashaddondash_pageblocks($shortname) {
        global $DB;
        $options = [];
        if ($shortname) {
            $pagetypepattern = 'dashaddon-dashboard-' . $shortname;
            $sql = "SELECT bi.id, bi.blockname FROM {block_instances} bi
                        JOIN {block} b ON bi.blockname = b.name
                        LEFT JOIN {block_positions} bp ON bp.blockinstanceid = bi.id
                        LEFT JOIN {block_positions} bs ON bs.blockinstanceid = bi.id
                        WHERE bi.pagetypepattern = :pagetype ORDER BY
                        COALESCE(bp.region, bs.region, bi.defaultregion),
                        COALESCE(bp.weight, bs.weight, bi.defaultweight),
                        bi.id ";
            $params = ['pagetype' => $pagetypepattern];
            $blocks = $DB->get_records_sql_menu($sql, $params);

            foreach ($blocks as $blockid => $blockname) {
                $blockinfo = block_instance_by_id($blockid);
                $newstrblockname = get_string('pluginname', 'block_' . $blockinfo->instance->blockname);
                $blocktitle = !empty($blockinfo->title) ? $blockinfo->title : $newstrblockname;
                $options[$blockid] = $blocktitle;
            }
        }
        return $options;
    }

    /**
     * Loads the prepare filemanager files.
     * @param object $dashboard
     */
    public static function prepare_filemanger_files($dashboard) {
        $itemid = isset($dashboard->id) ? $dashboard->id : null;
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        foreach ($filemanagers as $filemanager) {
            $dashboard = file_prepare_standard_filemanager(
                $dashboard,
                $filemanager,
                self::get_filemanager_options(),
                \context_system::instance(),
                'local_dash',
                $filemanager,
                $itemid
            );
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
