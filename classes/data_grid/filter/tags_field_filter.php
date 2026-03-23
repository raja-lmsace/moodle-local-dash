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
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filter items based on tags in a certain component and itemtype.
 *
 * @package local_dash
 */
class tags_field_filter extends select_filter {
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
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $PAGE;

        $renderer = $PAGE->get_renderer('core', 'tag');
        $collectionid = \core_tag_area::get_collection($this->component, $this->itemtype);
        $tags = \core_tag_collection::get_tag_cloud($collectionid);
        $data = $tags->export_for_template($renderer);

        foreach ($data->tags as $tag) {
            $this->add_option($tag->name, $tag->name);
        }

        parent::init();
    }

    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        $values = parent::get_values();
        $itemids = [];

        $collectionid = \core_tag_area::get_collection($this->component, $this->itemtype);

        if ($values) {
            foreach ($values as $value) {
                if ($tag = \core_tag_tag::get_by_name($collectionid, $value)) {
                    foreach ($tag->get_tagged_items($this->component, $this->itemtype) as $item) {
                        $itemids[] = $item->id;
                    }
                }
            }
            return !empty($itemids) ? $itemids : [0];
        }
        return [];
    }
}
