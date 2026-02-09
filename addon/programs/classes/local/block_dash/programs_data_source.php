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
 * Programs report source defined.
 *
 * @package    dashaddon_programs
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_programs\local\block_dash;

use block_dash\local\data_source\abstract_data_source;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\dash_framework\query_builder\join;
use block_dash\local\dash_framework\query_builder\join_raw;
use block_dash\local\dash_framework\query_builder\where;
use dashaddon_program\local\block_dash\data_grid\filter\tags_program_filter;
use dashaddon_programs\local\dash_framework\structure\programs_table;

/**
 * Enrol programs data source template queries and filter defined.
 */
class programs_data_source extends abstract_data_source {
    /**
     * Constructor.
     *
     * @param \context $context
     */
    public function __construct($context) {
        $this->add_table(new programs_table());
        parent::__construct($context);
    }

    /**
     * Setup the queries to fetch the programs data.
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER, $DB, $CFG;

        $builder = new builder();

        // Cohort library.
        require_once($CFG->dirroot . '/cohort/lib.php');

        $cohorts = cohort_get_user_cohorts($USER->id);
        $cohortids = array_column($cohorts, 'id');
        $concat = $DB->sql_group_concat('pc.cohortid', ','); // Create concat sql.

        $builder->select('*', 'epp_id')
            ->select('epp.contextid', 'epp_ctx')
            ->select('pclist.cohortid', 'cohortid')

            ->join_raw(new join_raw("SELECT pc.programid, $concat as cohortid
                FROM {enrol_programs_cohorts} pc
                GROUP BY pc.programid", 'pclist', 'programid', 'epp.id', join::TYPE_LEFT_JOIN, []))
            ->from('enrol_programs_programs', 'epp');

        // Config the program is not restricted to the cohort users.
        $cohortsql = 'pclist.cohortid IS NULL';

        if ($cohortids) {
            [$insql, $inparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED, 'ch');
            $builder->join_raw(
                new join_raw("SELECT pc.programid, $concat as cohortid
                    FROM {enrol_programs_cohorts} pc
                    WHERE pc.cohortid $insql
                    GROUP BY pc.programid", 'pcuser', 'programid', 'epp.id', join::TYPE_LEFT_JOIN, $inparams)
            );
            $builder->select("pcuser.cohortid", "pcusercohort");
            // If cohort is configured then user should assigned in any of the cohorts.
            $cohortsql .= ' OR pcuser.cohortid IS NOT NULL';
        }

        $builder->where_raw("(" . $cohortsql . ")", []);

        return $builder;
    }

    /**
     * Filter conditions are added to programs preference report.
     *
     * @return filter_collection
     */
    public function build_filter_collection() {

        $filtercollection = new filter_collection(get_class($this), $this->get_context());

        $filtercollection->add_filter(new tags_program_filter(
            'epp_tags',
            'epp.id',
            'enrol_programs',
            'programs',
            get_string('tags', 'tag')
        ));

        return $filtercollection;
    }
}
