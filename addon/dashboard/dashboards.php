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
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_dash\output\renderer;
use dashaddon_dashboard\form\dashboard_form;
use dashaddon_dashboard\model\dashboard;
use dashaddon_dashboard\helper;

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once("$CFG->libdir/adminlib.php");

global $PAGE, $DB;

$action = optional_param('action', 'create', PARAM_TEXT);
$contextid = optional_param('contextid', 0, PARAM_INT);

$context = context_system::instance();
$pageparams = ['action' => $action];
$redirecturl = new moodle_url('/local/dash/addon/dashboard/dashboard_list.php');
if ($contextid) {
    $context = context::instance_by_id($contextid);
    $pageparams['contextid'] = $contextid;
    $redirecturl->param('contextid', $contextid);
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dash/addon/dashboard/dashboards.php', $pageparams));
$PAGE->navbar->add(
    get_string('managedashboards', 'block_dash'),
    new moodle_url('/local/dash/addon/dashboard/dashboard_list.php', $pageparams)
);

/** @var renderer $renderer */
$renderer = $PAGE->get_renderer('block_dash');

require_login();
if ($context->contextlevel == CONTEXT_COURSECAT) {
    require_capability('local/dash:managecoursecatedashboards', $context);
} else {
    require_capability('local/dash:managedashboards', $context);
}

$PAGE->requires->js_call_amd('dashaddon_dashboard/dashboard', 'init');

switch ($action) {
    case 'create':
        $PAGE->set_title(get_string('createdashboard', 'block_dash'));
        $PAGE->set_heading(get_string('createdashboard', 'block_dash'));
        $PAGE->navbar->add(get_string('createdashboard', 'block_dash'));
        $customdata = ['persistent' => null];
        if ($context->contextlevel == CONTEXT_COURSECAT) {
            $customdata['categoryid'] = $context->instanceid;
        }
        $form = new dashboard_form($PAGE->url, $customdata);
        if ($data = $form->get_data()) {
            $dashboard = new dashboard(0, $data);
            $dashboard->create();
            $dashboard->clear_hook_cache(true);
            \core\notification::success(get_string('dashboardcreated', 'block_dash', $dashboard->to_record()));
            redirect($redirecturl);
        } else if ($form->is_cancelled()) {
            redirect($redirecturl);
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'duplicate':
        $PAGE->set_title(get_string('duplicatedashboard', 'block_dash'));
        $PAGE->set_heading(get_string('duplicatedashboard', 'block_dash'));
        $PAGE->navbar->add(get_string('duplicatedashboard', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $originaldashboard = new dashboard($id);
        $newdashboard = $originaldashboard->duplicate();

        \core\notification::success(get_string('dashboardduplicated', 'block_dash', $newdashboard->to_record()));
        redirect($redirecturl);
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
        $dashboard->prepare_filemanger_files();
        $dashboard->set_roles_data();
        $dashboard->set_includedblocks_data();
        $customdata = ['persistent' => $dashboard];
        if ($context->contextlevel == CONTEXT_COURSECAT) {
            $customdata['categoryid'] = $context->instanceid;
        }
        $form = new dashboard_form($PAGE->url, $customdata);

        if ($data = $form->get_data()) {
            $dashboard->from_record($data);
            $dashboard->update();
            $dashboard->clear_hook_cache();
            theme_reset_all_caches();
            \core\notification::success(get_string('dashboardedited', 'block_dash', $dashboard->to_record()));
            redirect($redirecturl);
        } else if ($form->is_cancelled()) {
            redirect($redirecturl);
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
            $dashboard->clear_hook_cache();
            \core\notification::success(get_string('dashboarddeleted', 'block_dash', $dashboard->to_record()));
            redirect($redirecturl);
        }

        echo $OUTPUT->header();
        $url = clone $PAGE->url;
        $url->param('confirm', 1);

        $message = get_string('deleteconfirm', 'block_dash', $dashboard->to_record());
        echo $OUTPUT->confirm($message, $url, new moodle_url('/local/dash/addon/dashboard/dashboard_list.php'));
        break;
}

echo $OUTPUT->footer();
