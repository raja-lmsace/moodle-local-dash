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
 * Responsible for creating layouts on request.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\layout;

use block_dash\local\data_source\data_source_interface;
use block_dash\local\layout\layout_factory_interface;
use block_dash\local\layout\layout_interface;
use dashaddon_developer\model\custom_layout;


/**
 * Responsible for creating layouts on request.
 *
 * @package dashaddon_developer
 */
class persistent_layout_factory implements layout_factory_interface {
    /**
     * Get layout object with datasource.
     *
     * @param string $identifier
     * @param data_source_interface $datasource
     * @return layout_interface
     */
    public static function build_layout($identifier, data_source_interface $datasource) {
        $id = str_replace('custom_', '', $identifier);

        $layout = new persistent_layout($datasource);
        $layout->set_custom_layout(new custom_layout($id));

        return $layout;
    }
}
