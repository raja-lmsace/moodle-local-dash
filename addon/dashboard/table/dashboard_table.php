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
 *  Table that lists dashboards.
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/dash/lib.php');

/**
 * Table that lists dashboards.
 *
 * @package dashaddon_dashboard
 */
class dashboard_table extends \table_sql {
    /**
     * Summary of contextid
     * @var int
     */
    public $contextid;

    /**
     * sessions_report_table constructor.
     * @param string $uniqueid
     * @param int $contextid
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $contextid = 0) {
        parent::__construct($uniqueid);
        $this->contextid = $contextid;
        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('name');
        $headers[] = get_string('contextid', 'block_dash');
        $headers[] = get_string('permissions', 'block_dash');
        if (\local_dash_secondarynav()) {
            $headers[] = get_string('secondarynav', 'block_dash');
        }
        $headers[] = get_string('backgroundimage', 'block_dash');

        $headers[] = get_string('actions');

        $columns[] = 'name';
        $columns[] = 'contextid';
        $columns[] = 'permission';
        if (\local_dash_secondarynav()) {
            $columns[] = 'secondarynav';
        }
        $columns[] = 'backgroundimage';
        $columns[] = 'actions';

        $this->define_columns($columns);
        $this->define_headers($headers);

        $this->no_sorting('actions');

        // Set help icons.
        $this->define_help_for_headers([

        ]);
    }

    /**
     * Table column name data process.
     *
     * @param \stdclass $data
     * @return string
     */
    public function col_name($data) {
        if (!$data->coredash) {
            return \html_writer::link(
                new \moodle_url('/local/dash/addon/dashboard/dashboard.php', ['id' => $data->id]),
                $data->name
            );
        } else {
            return \html_writer::link(new \moodle_url('/my', ['id' => $data->id]), $data->name);
        }
    }

    /**
     * Table column name data process.
     *
     * @param \stdclass $data
     * @return string
     */
    public function col_backgroundimage($data) {
        global $CFG;
        require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");
        if ($url = dashaddon_dashboard_get_dashboard_background($data->id)) {
            return \html_writer::empty_tag('img', ['src' => $url, 'class' => 'img-responsive', 'alt' => $data->name]);
        } else {
            return "";
        }
    }

    /**
     * Cotextid column data process.
     *
     * @param \stdclass $data
     * @return string
     */
    public function col_contextid($data) {
        try {
            $context = \context::instance_by_id($data->contextid);
        } catch (\moodle_exception $e) {
            return get_string('contextmissing', 'block_dash');
        }

        return $context->get_context_name();
    }

    /**
     * Permissions column data process.
     *
     * @param \stdclass $data
     * @return string
     */
    public function col_permission($data) {
        return get_string('permissions' . $data->permission, 'block_dash');
    }

    /**
     * Status of the display the course context menus in course secondary menu.
     *
     * @param \stdclass $data
     * @return string
     */
    public function col_secondarynav($data) {
        return ($data->secondarynav) ? get_string('yes') : get_string('no');
    }

    /**
     * Actions for tags.
     *
     * @param \stdClass $data
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($data) {
        global $OUTPUT;

        $actions = [];

        $actions += [
            [
                'url' => new \moodle_url(
                    '/local/dash/addon/dashboard/dashboards.php',
                    ['action' => 'duplicate', 'id' => $data->id]
                ),
                'icon' => new \pix_icon('t/copy', \get_string('duplicate')),
                'attributes' => ['class' => 'action-copy'],
                'name' => 'copy',
            ],
            [
                'url' => new \moodle_url(
                    '/local/dash/addon/dashboard/dashboards.php',
                    ['action' => 'edit', 'id' => $data->id]
                ),
                'icon' => new \pix_icon('t/edit', \get_string('edit')),
                'attributes' => ['class' => 'action-edit'],
                'name' => 'edit',
            ],
            [
                'url' => new \moodle_url(
                    '/local/dash/addon/dashboard/dashboards.php',
                    ['action' => 'delete', 'id' => $data->id]
                ),
                'icon' => new \pix_icon('t/delete', \get_string('delete')),
                'attributes' => ['class' => 'action-delete'],
                'name' => 'delete',
            ],
        ];
        $actionshtml = [];
        foreach ($actions as $action) {
            $action['attributes']['role'] = 'button';
            if ($data->coredash && $action['name'] == 'copy') {
                $actionshtml[] = '&nbsp;';
            } else {
                if ($this->contextid) {
                    $action['url']->param('contextid', $this->contextid);
                }
                $actionshtml[] = $OUTPUT->action_icon($action['url'], $action['icon'], null, $action['attributes']);
            }
        }
        return \html_writer::span(join('', $actionshtml), 'course-item-actions item-actions me-0');
    }

    /**
     * Defined table database queries and data process methods.
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        [$wsql, $params] = $this->get_sql_where();

        if ($this->contextid) {
            if (!empty($wsql)) {
                $wsql .= ' AND ';
            } else {
                $wsql = ' WHERE ';
            }
            $wsql .= ' d.contextid = :contextid';
            $params['contextid'] = $this->contextid;
        }
        $sql = 'SELECT * FROM {dashaddon_dashboard_dash} d' . $wsql;
        $sort = $this->get_sql_sort();
        if ($sort) {
            $sql = $sql . ' ORDER BY ' . $sort;
        }

        if ($pagesize != -1) {
            $countsql = 'SELECT COUNT(DISTINCT d.id) FROM {dashaddon_dashboard_dash} d' . $wsql;
            $total = $DB->count_records_sql($countsql, $params);
            $this->pagesize($pagesize, $total);
        } else {
            $this->pageable(false);
        }

        $this->rawdata = $DB->get_recordset_sql($sql, $params, $this->get_page_start(), $this->get_page_size());
    }
}
