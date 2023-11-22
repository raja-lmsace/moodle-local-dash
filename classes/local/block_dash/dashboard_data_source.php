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
 * Dashboard data source.
 * @package   local_dash
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\local\block_dash;

use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\data_grid\field\field_definition_factory;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_source\abstract_data_source;
use local_dash\data_grid\filter\my_dashboards_condition;
use local_dash\data_grid\filter\nonpublic_dashboards_condition;
use local_dash\local\dash_framework\structure\dashboard_table;

/**
 * Dashboard data source.
 * @package   local_dash
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_data_source extends abstract_data_source {

    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct(\context $context) {
        $this->add_table(new dashboard_table());
        parent::__construct($context);
    }

    /**
     * Return query template for retrieving user info.
     * @return builder
     */
    public function get_query_template(): builder {
        $builder = new builder();
        $builder
            ->select('dd.id', 'dd_id')
            ->from('local_dash_dashboard', 'dd');

        return $builder;
    }

    /**
     * Build and return filter collection.
     * @return filter_collection_interface
     */
    public function build_filter_collection() {
        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new my_dashboards_condition('dd_id', 'dd.id'));

        $filtercollection->add_filter(new nonpublic_dashboards_condition('dd_nonpublic', 'dd.id'));

        return $filtercollection;
    }
}
