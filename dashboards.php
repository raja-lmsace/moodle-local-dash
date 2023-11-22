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
 * Displays preconfigured dashboards.
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_dash\output\renderer;
use local_dash\form\dashboard_form;
use local_dash\model\dashboard;

require_once(__DIR__.'/../../config.php');
require_once("$CFG->libdir/adminlib.php");

global $PAGE, $DB;

$action = required_param('action', PARAM_TEXT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dash/dashboards.php', ['action' => $action]));
$PAGE->navbar->add(get_string('managedashboards', 'block_dash'), new moodle_url('/local/dash/dashboard_list.php'));

/** @var renderer $renderer */
$renderer = $PAGE->get_renderer('block_dash');

require_login();
require_capability('local/dash:managedashboards', $context);

switch ($action) {
    case 'create':
        $PAGE->set_title(get_string('createdashboard', 'block_dash'));
        $PAGE->set_heading(get_string('createdashboard', 'block_dash'));
        $PAGE->navbar->add(get_string('createdashboard', 'block_dash'));

        $form = new dashboard_form($PAGE->url, [
            'persistent' => null,
        ]);

        if ($data = $form->get_data()) {
            $dashboard = new dashboard(0, $data);
            $dashboard->create();

            \core\notification::success(get_string('dashboardcreated', 'block_dash', $dashboard->to_record()));
            redirect(new moodle_url('/local/dash/dashboard_list.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/dashboard_list.php'));
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'edit':
        $PAGE->set_title(get_string('editdashboard', 'block_dash'));
        $PAGE->set_heading(get_string('editdashboard', 'block_dash'));
        $PAGE->navbar->add(get_string('editdashboard', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $dashboard = new dashboard($id);

        $form = new dashboard_form($PAGE->url, [
            'persistent' => $dashboard,
        ]);

        if ($data = $form->get_data()) {
            $dashboard->from_record($data);
            $dashboard->update();

            \core\notification::success(get_string('dashboardedited', 'block_dash', $dashboard->to_record()));
            redirect(new moodle_url('/local/dash/dashboard_list.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/dashboard_list.php'));
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'delete':
        $PAGE->set_title(get_string('deletedashboard', 'block_dash'));
        $PAGE->set_heading(get_string('deletedashboard', 'block_dash'));
        $PAGE->navbar->add(get_string('deletedashboard', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $dashboard = new dashboard($id);

        if ($confirm = optional_param('confirm', 0, PARAM_BOOL)) {
            $dashboard->delete();
            \core\notification::success(get_string('dashboarddeleted', 'block_dash', $dashboard->to_record()));
            redirect(new moodle_url('/local/dash/dashboard_list.php'));
        }

        echo $OUTPUT->header();
        $url = clone $PAGE->url;
        $url->param('confirm', 1);

        $message = get_string('deleteconfirm', 'block_dash', $dashboard->to_record());
        echo $OUTPUT->confirm($message, $url, new moodle_url('/local/dash/dashboard_list.php'));
        break;
}

echo $OUTPUT->footer();
