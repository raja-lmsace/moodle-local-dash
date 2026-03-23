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
 * Transform activity data into activity purpose.
 *
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;

/**
 * Transforms activity data to formatted activity purpose.
 *
 * @package dashaddon_activities
 */
class activity_purpose_attribute extends abstract_field_attribute {
    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;

        $modulename = $DB->get_field("modules", 'name', ['id' => $record->cm_module]);
        $cm = cm_info::create(get_coursemodule_from_id('', $record->cm_id));
        if (
            $cm != null && $cm->get_course()->format == 'designer' &&
            dashaddon_activities_is_designer_pro_installed()
        ) {
            $modpurpose = \format_designer\options::get_option($data, 'purpose');
            if (!$modpurpose) {
                $modpurpose = get_config('local_designer', "purpose_" . $modulename);
            }
        } else {
            $modpurpose = ucfirst(plugin_supports('mod', $modulename, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER));
        }
        return $modpurpose;
    }
}
