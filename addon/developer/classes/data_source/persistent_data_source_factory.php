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
 * Create data sources based on database records.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\data_source;

use block_dash\local\data_source\data_source_factory_interface;
use block_dash\local\data_source\data_source_interface;
use dashaddon_developer\model\custom_data_source as custom_data_source_model;

/**
 * Create data sources based on database records.
 *
 * @package dashaddon_developer
 */
class persistent_data_source_factory implements data_source_factory_interface {
    /**
     * Build the data source.
     *
     * @param string $identifier
     * @param \context $context
     * @return data_source_interface
     */
    public static function build_data_source($identifier, \context $context) {

        if (!custom_data_source_model::record_exists_select('idnumber = :idnumber', ['idnumber' => $identifier])) {
            return null;
        }

        $record = custom_data_source_model::get_record(['idnumber' => $identifier]);

        return new persistent_data_source($record, $context);
    }
}
