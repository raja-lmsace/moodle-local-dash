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
 * Certificates report source defined.
 *
 * @package    dashaddon_certificates
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_certificates\local\block_dash;

use block_dash\local\data_source\abstract_data_source;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use dashaddon_certificates\local\dash_framework\structure\certificates_table;
use dashaddon_course_completions\local\dash_framework\structure\course_completion_table;
use dashaddon_courses\local\dash_framework\structure\course_table;
use block_dash\local\dash_framework\structure\user_table;
use dashaddon_categories\local\dash_framework\structure\course_category_table;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\data_grid\filter\current_course_condition;
use block_dash\local\data_grid\filter\logged_in_user_condition;
use local_dash\data_grid\filter\course_category_condition;
use local_dash\data_grid\filter\relations_role_condition;

/**
 * Certificates data source template queries and condition defined.
 */
class certificates_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {

        $this->add_table(new certificates_table());
        $this->add_table(new course_table());
        $this->add_table(new course_completion_table());
        $this->add_table(new course_category_table());
        $this->add_table(new user_table());

        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the certificates data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER, $DB, $CFG;

        $builder = new builder();

        $builder->select('*', 'tci_id')
            ->select('tct.id', 'tct_templateid')
            ->select('tci.id', 'id')
            ->select('u.id', 'userid')
            ->from('tool_certificate_issues', 'tci')
            ->join('tool_certificate_templates', 'tct', 'id', 'tci.templateid')
            ->join('user', 'u', 'id', 'tci.userid')
            ->join('course', 'c', 'id', 'tci.courseid', join::TYPE_LEFT_JOIN)
            ->join('course_categories', 'cc', 'id', 'c.category', join::TYPE_LEFT_JOIN)
            ->join('course_completions', 'ccp', 'id', 'u.id', join::TYPE_LEFT_JOIN)
            ->join_condition('ccp', 'ccp.course=tci.courseid');

        return $builder;
    }

    /**
     * Filter conditions are added to certificates preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new current_course_condition('current_course', 'c.id'));

        $filtercollection->add_filter(new course_category_condition('c_course_categories_condition', 'c.category'));

        $filtercollection->add_filter(new logged_in_user_condition('current_user', 'u.id'));

        $filtercollection->add_filter(new relations_role_condition('parentrole', 'u.id'));

        // Attach the custom course field conditions.
        local_dash_customfield_conditions($filtercollection);

        return $filtercollection;
    }
}
