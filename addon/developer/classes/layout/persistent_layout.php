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
 * This layout displays data in a grid of cards.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\layout;

use block_dash\local\layout\abstract_layout;
use dashaddon_developer\model\custom_layout;

/**
 * A layout contains information on how to display data.
 * @see abstract_layout for creating new layouts.
 *
 * This is a container for custom layouts.
 *
 * @package dashaddon_developer
 */
class persistent_layout extends abstract_layout {
    /**
     * Layout.
     *
     * @var custom_layout
     */
    private $customlayout;

    /**
     * Set the layout.
     *
     * @param custom_layout $customlayout
     * @return void
     */
    public function set_custom_layout(custom_layout $customlayout) {
        $this->customlayout = $customlayout;
    }

    /**
     * Returns the mustache path of this layout.
     *
     * @return string
     */
    public function get_mustache_template_name() {
        global $CFG;

        if ($mustachetemplate = $this->customlayout->get('mustache_template')) {
            make_localcache_directory('block_dash/templates');

            $path = "$CFG->localcachedir/block_dash/templates/" . $this->customlayout->get('id');

            if (!file_exists($path) || md5(file_get_contents($path)) != md5($mustachetemplate)) {
                file_put_contents($path, $mustachetemplate);
            }

            return '_custom/' . $this->customlayout->get('id');
        }

        return 'block_dash/layout_missing';
    }

    /**
     * If the template should display pagination links.
     *
     * @return bool
     */
    public function supports_pagination() {
        return $this->customlayout->get('supports_pagination');
    }

    /**
     * If the template fields can be hidden or shown conditionally.
     *
     * @return bool
     */
    public function supports_field_visibility() {
        return $this->customlayout->get('supports_field_visibility');
    }

    /**
     * If the template should display filters (does not affect conditions).
     *
     * @return bool
     */
    public function supports_filtering() {
        return $this->customlayout->get('supports_filtering');
    }

    /**
     * If the layout supports field sorting.
     *
     * @return mixed
     */
    public function supports_sorting() {
        return $this->customlayout->get('supports_sorting');
    }
}
