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
 * Table that lists layouts.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\layout\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table that lists layouts.
 *
 * @package dashaddon_developer
 */
class layout_table extends \table_sql {
    /**
     * Define the headers and columns of this table.
     *
     * @param string $uniqueid
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
    }

    /**
     * Name of the layout.
     *
     * @param object $data
     * @return string
     */
    public function col_name($data) {
        return \html_writer::link(
            new \moodle_url('/local/dash/addon/developer/customlayouts.php', ['id' => $data->id]),
            $data->name
        );
    }

    /**
     * Actions for layouts.
     *
     * @param \stdClass $data
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($data) {
        global $OUTPUT;

        return $OUTPUT->single_button(new \moodle_url(
            '/local/dash/addon/developer/customlayout.php',
            ['action' => 'edit', 'id' => $data->id]
        ), get_string('edit', 'block_dash'), 'get') .
            $OUTPUT->single_button(new \moodle_url(
                '/local/dash/addon/developer/customlayout.php',
                ['action' => 'delete', 'id' => $data->id]
            ), get_string('delete', 'block_dash'), 'get');
    }

    /**
     * Fetch the records from the DB based on the querys defined in this datasource.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        [$wsql, $params] = $this->get_sql_where();

        $sql = 'SELECT * FROM {dashaddon_developer_layout} l ' . $wsql;

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sql = $sql . ' ORDER BY ' . $sort;
        }

        if ($pagesize != -1) {
            $countsql = 'SELECT COUNT(DISTINCT l.id) FROM {dashaddon_developer_layout} l ' . $wsql;
            $total = $DB->count_records_sql($countsql, $params);
            $this->pagesize($pagesize, $total);
        } else {
            $this->pageable(false);
        }

        $this->rawdata = $DB->get_recordset_sql($sql, $params, $this->get_page_start(), $this->get_page_size());
    }
}
