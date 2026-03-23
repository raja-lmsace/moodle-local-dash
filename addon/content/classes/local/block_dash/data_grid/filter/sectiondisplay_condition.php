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
 * Filters results to specific sections.
 *
 * @package    dashaddon_content
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_content\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;
use core_competency\api;
use dashaddon_content\local\block_dash\content_customtype;

/**
 * Filters results to specific sections.
 *
 * @package dashaddon_content
 */
class sectiondisplay_condition extends condition {
    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_EQUAL;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('contentsectiondisplay', 'block_dash');
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {

        if (isset($this->get_preferences()['sections']) && is_array($this->get_preferences()['sections'])) {
            $sections = $this->get_preferences()['sections'];
            return $sections;
        }
        return [];
    }

    /**
     * Find the block is available or not for the current page.
     */
    public function layout_is_available() {
        global $PAGE;

        // Section based display enabled, sections should be configured and this current page in the sections list.
        if (isset($this->get_preferences()['enabled']) && $this->get_preferences()['enabled']) {
            $sections = $this->get_values();

            $params = $PAGE->url->params();
            $section = $params['section'] ?? 0;

            // Is section page.
            $issectionpage = in_array($section, $sections);
            if (!empty($sections) && $issectionpage) {
                return true;
            }

            return false;
        }
        // No sections are configured.
        return true;
    }

    /**
     * Add form fields for this filter (and any settings related to this filter.)
     *
     * @param moodleform $moodleform
     * @param MoodleQuickForm $mform
     * @param string $fieldnameformat
     */
    public function build_settings_form_fields(
        moodleform $moodleform,
        MoodleQuickForm $mform,
        $fieldnameformat = 'filters[%s]'
    ): void {
        global $PAGE;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $courseid = $PAGE->course->id;
        if ($courseid != SITEID) {
            $modinfo = \course_modinfo::instance($courseid);
            $sectionsinfo = $modinfo->get_section_info_all();

            $format = course_get_format($courseid);
            $options = [];
            foreach ($sectionsinfo as $sectionid => $info) {
                $options[$sectionid] = $format->get_section_name($info);
            }
            $sections = $mform->addElement(
                'autocomplete',
                $fieldname . '[sections]',
                get_string('contentsections', 'block_dash'),
                $options
            );
            $sections->setMultiple(true);

            $mform->hideIf($fieldname . '[sections]', $fieldname . '[enabled]');
        }
    }
}
