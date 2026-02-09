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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @author LMSACE Dev Team
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\output;
use html_writer;
use coding_exception;

/**
 * Custom renderer class for the local dash plugin.
 *
 * This class extends the core_renderer to provide custom rendering
 * functionality for the local dash plugin in Moodle.
 *
 * @package   local_dash
 * @category  output
 */
class renderer extends \core_renderer {
    /**
     * Renders a custom block region.
     *
     * Use this method if you want to add an additional block region to the content of the page.
     * Please note this should only be used in special situations.
     * We want to leave the theme is control where ever possible!
     *
     * This method must use the same method that the theme uses within its layout file.
     * As such it asks the theme what method it is using.
     * It can be one of two values, blocks or blocks_for_region (deprecated).
     *
     * @param string $regionname The name of the custom region to add.
     * @param string|bool $dashboard The dashboard object.
     * @return string HTML for the block region.
     */
    public function custom_block_region($regionname, $dashboard = null) {
        if ($this->page->theme->get_block_render_method() === 'blocks') {
            return $this->blocks($regionname, [], 'aside', false, $dashboard);
        } else {
            return $this->blocks_for_region($regionname);
        }
    }

    /**
     * Get the HTML for blocks in the given region.
     *
     * @since Moodle 2.5.1 2.6
     * @param string $region The region to get HTML for.
     * @param array $classes Wrapping tag classes.
     * @param string $tag Wrapping tag.
     * @param boolean $fakeblocksonly Include fake blocks only.
     * @param string|bool $dashboard The dashboard object.
     * @return string HTML.
     */
    public function blocks($region, $classes = [], $tag = 'aside', $fakeblocksonly = false, $dashboard = null) {
        $displayregion = $this->page->apply_theme_region_manipulations($region);
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = [
            'id' => 'block-region-' . preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $displayregion),
            'class' => join(' ', $classes),
            'data-blockregion' => $displayregion,
            'data-droptarget' => '1',
        ];
        if ($this->page->blocks->region_has_content($displayregion, $this)) {
            $content = html_writer::tag('h2', get_string('blocks'), ['class' => 'sr-only']) .
                $this->blocks_for_region($displayregion, $fakeblocksonly, $dashboard);
        } else {
            $content = html_writer::tag('h2', get_string('blocks'), ['class' => 'sr-only']);
        }
        return html_writer::tag($tag, $content, $attributes);
    }

    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @param boolean $fakeblocksonly Output fake block only.
     * @param string|bool $dashboard The dashboard object.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region, $fakeblocksonly = false, $dashboard = null) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $lastblock = null;
        $zones = [];
        foreach ($blockcontents as $bc) {
            if ($bc instanceof \block_contents) {
                $zones[] = $bc->title;
            }
        }
        $output = '';
        $addonpagenavigation = false;
        foreach ($blockcontents as $bc) {
            if ($bc instanceof \block_contents) {
                if ($fakeblocksonly && !$bc->is_fake()) {
                    // Skip rendering real blocks if we only want to show fake blocks.
                    continue;
                }
                $output .= $this->block($bc, $region);
                $lastblock = $bc->title;
            } else if ($bc instanceof \block_move_target) {
                if (!$fakeblocksonly) {
                    $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
                }
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
            if (!$addonpagenavigation) {
                $output .= $dashboard->process_onboard_navigation();
                $addonpagenavigation = true;
            }
        }
        return $output;
    }
}
