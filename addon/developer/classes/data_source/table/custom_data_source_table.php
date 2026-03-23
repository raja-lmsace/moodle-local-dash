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
 * Table that lists data sources.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\data_source\table;

use dashaddon_developer\model\custom_data_source;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table that lists data sources.
 *
 * @package dashaddon_developer
 */
class custom_data_source_table extends \table_sql {
    /**
     * Sessions_report_table constructor.
     *
     * @param int $uniqueid Id of the data source.
     *
     * @throws \coding_exception
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('name');
        $headers[] = get_string('actions');
        $columns[] = 'name';
        $columns[] = 'actions';

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Set help icons.
        $this->define_help_for_headers([]);
    }

    /**
     * You can override this method in a child class. See the description of
     * build_table which calls this method.
     *
     * @param string $column
     * @param custom_data_source $row
     * @return string
     * @throws \coding_exception
     */
    public function other_cols($column, $row) {
        return $row->get($column);
    }

    /**
     * Actions for tags.
     *
     * @param custom_data_source $data
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($data) {
        global $OUTPUT;

        return $OUTPUT->single_button(
            new \moodle_url(
                '/local/dash/addon/developer/customdatasource.php',
                ['action' => 'edit', 'id' => $data->get('id')]
            ),
            get_string('edit', 'block_dash'),
            'get'
        ) .
            $OUTPUT->single_button(
                new \moodle_url(
                    '/local/dash/addon/developer/customdatasource.php',
                    ['action' => 'delete', 'id' => $data->get('id')]
                ),
                get_string('delete', 'block_dash'),
                'get'
            );
    }

    /**
     * Fetch the records from the DB based on the querys defined in this datasource.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        if ($pagesize != -1) {
            $this->pagesize($pagesize, custom_data_source::count_records());
        } else {
            $this->pageable(false);
        }

        $this->rawdata = custom_data_source::get_records(
            [],
            $this->get_sql_sort(),
            'ASC',
            $this->get_page_start(),
            $this->get_page_size()
        );
    }
}
