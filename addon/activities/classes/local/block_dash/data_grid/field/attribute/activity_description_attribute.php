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
 * Transform activity data into activity description.
 *
 * @package    dashaddon_activities
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_activities\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use cm_info;
use context_module;

/**
 * Transforms activity data to formatted activity description.
 *
 * @package dashaddon_activities
 */
class activity_description_attribute extends abstract_field_attribute {
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
        global $DB, $CFG;
        require_once($CFG->libdir . '/externallib.php');
        $module = $DB->get_record('modules', ['id' => $record->cm_module]);
        $instance = $DB->get_record($module->name, ['id' => $record->cm_instance]);
        $cm = cm_info::create(get_coursemodule_from_id($module->name, $data));
        $modulecontext = context_module::instance($data);
        if ($cm == null) {
            return '';
        }
        $cmcontent = format_module_intro($module->name, $instance, $cm->id, false);
        [$intro, $format] = external_format_text(
            $cmcontent,
            FORMAT_HTML,
            $modulecontext->id,
            $cm->modname,
            'intro',
            $cm->id,
            ['noclean' => true]
        );
        return $intro;
    }
}
