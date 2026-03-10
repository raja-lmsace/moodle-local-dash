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
 * Generate the course image url from the fetched record
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\field\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;
use moodle_url;

/**
 * Transform data to URL of course image.
 *
 * @package local_dash
 */
class course_image_url_attribute extends abstract_field_attribute {
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

        require_once("$CFG->dirroot/course/lib.php");
        require_once("$CFG->dirroot/blocks/dash/lib.php");
        if ($course = $DB->get_record('course', ['id' => $data])) {
            if (block_dash_is_totara()) {
                $image = course_get_image($course->id)->out();
            } else {
                $image = \core_course\external\course_summary_exporter::get_course_image($course);
            }

            if ($image == '') {
                $courseimage = get_config('local_dash', 'courseimage');
                if ($courseimage != '') {
                    $image = moodle_url::make_pluginfile_url(
                        \context_system::instance()->id,
                        'local_dash',
                        'courseimage',
                        null,
                        null,
                        $courseimage
                    );
                }
            }
            return $image;
        }

        return $data;
    }
}
