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
 * Transforms data to programs image url.
 *
 * @package    dashaddon_programs
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\block_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use stdClass;
use moodle_url;

/**
 * Transforms data to programs image url.
 */
class program_image_url_attribute extends abstract_field_attribute {
    /**
     * Generate the programs image url based on the program id.
     *
     * @param int $data
     * @param stdClass $record
     * @return string
     */
    public function transform_data($data, stdClass $record) {
        global $CFG;

        $presentation = (array) json_decode($record->presentationjson);
        $context = \context::instance_by_id($record->epp_ctx);
        $imageurl = '';
        if (isset($presentation['image'])) {
            $imageurl = moodle_url::make_file_url(
                "$CFG->wwwroot/pluginfile.php",
                '/' . $context->id . '/enrol_programs/image/' . $record->epp_id . '/' . $presentation['image'],
                false
            );
        } else {
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                \context_system::instance()->id,
                'local_dash',
                'programbg',
                0,
                '',
                false
            );
            if (!empty($files)) {
                // Get the first file.
                $file = reset($files);
                $imageurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false,
                );
            }
        }
        return $imageurl;
    }
}
