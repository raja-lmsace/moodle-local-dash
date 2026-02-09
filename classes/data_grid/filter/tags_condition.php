<?php
// This file is part of The Bootstrap Moodle theme
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
 * Filter items based on tags in a certain component and itemtype.
 * @package    local_dash
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;

/**
 * Filter items based on tags in a certain component and itemtype.
 *
 * @package local_dash
 */
class tags_condition extends condition {
    /**
     * Current component.
     *
     * @var string
     */
    private $component;

    /**
     * Type of item.
     *
     * @var string
     */
    private $itemtype;

    /**
     * Condition construtor.
     *
     * @param string $name
     * @param string $select
     * @param string $component
     * @param string $itemtype
     * @param string $label
     */
    public function __construct($name, $select, $component, $itemtype, $label = '') {
        $this->component = $component;
        $this->itemtype = $itemtype;

        parent::__construct($name, $select, $label);
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['tags'])) {
            $values = $this->get_preferences()['tags'];
            $itemids = [];

            $collectionid = \core_tag_area::get_collection($this->component, $this->itemtype);
            if (is_array($values)) {
                foreach ($values as $value) {
                    if ($tag = \core_tag_tag::get_by_name($collectionid, $value)) {
                        foreach ($tag->get_tagged_items($this->component, $this->itemtype) as $item) {
                            $itemids[] = $item->id;
                        }
                    }
                }
            }
            return !empty($itemids) ? $itemids : [0];
        }
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

        global $OUTPUT;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $options = [];
        $collectionid = \core_tag_area::get_collection($this->component, $this->itemtype);
        $tags = \core_tag_collection::get_tag_cloud($collectionid);
        $data = $tags->export_for_template($OUTPUT);

        foreach ($data->tags as $tag) {
            $options[$tag->name] = $tag->name;
        }

        $mform->addElement('autocomplete', $fieldname . '[tags]', get_string('tags', 'block_dash'), $options, [
            'multiple' => true,
        ]);
        $mform->hideIf($fieldname . '[tags]', $fieldname . '[enabled]');
    }
}
