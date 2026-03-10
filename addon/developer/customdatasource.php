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
 * Manage custom datasources.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_dash\output\renderer;
use dashaddon_developer\model\custom_data_source;
use dashaddon_developer\data_source\form\custom_data_source_form;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/adminlib.php");

global $PAGE, $DB, $USER;

$action = required_param('action', PARAM_TEXT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dash/addon/developer/customdatasource.php', ['action' => $action]));
$PAGE->navbar->add(get_string('managedatasources', 'block_dash'), new moodle_url('/local/dash/datasources.php'));

/** @var renderer $renderer */
$renderer = $PAGE->get_renderer('block_dash');

require_login();
require_capability('dashaddon/developer:managecustomdatasources', $context);

switch ($action) {
    case 'create':
        $PAGE->set_title(get_string('createcustomdatasource', 'block_dash'));
        $PAGE->set_heading(get_string('createcustomdatasource', 'block_dash'));
        $PAGE->navbar->add(get_string('createcustomdatasource', 'block_dash'));

        $form = new custom_data_source_form($PAGE->url, [
            'persistent' => null,
            'userid' => $USER->id,
        ]);


        if (($data = $form->get_data()) && !$form->no_submit_button_pressed()) {
            $customdatasource = new custom_data_source(0, $data);
            $customdatasource->create();

            \core\notification::success(get_string(
                'customdatasourcecreated',
                'block_dash',
                $customdatasource->to_record()
            ));
            redirect(new moodle_url('/local/dash/datasources.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/datasources.php'));
        }

        echo $OUTPUT->header();

        $form->display();

        break;

    case 'edit':
        $PAGE->set_title(get_string('editcustomdatasource', 'block_dash'));
        $PAGE->set_heading(get_string('editcustomdatasource', 'block_dash'));
        $PAGE->navbar->add(get_string('editcustomdatasource', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $customdatasource = new custom_data_source($id);

        $form = new custom_data_source_form($PAGE->url, [
            'persistent' => $customdatasource,
        ]);

        if ($data = $form->get_data()) {
            // Update the repeat counts.
            $repeats = ['selectfield' => 'fieldrepeats', 'tablejoins' => 'joinrepeats', 'conditionfield' => 'conditionrepeats'];
            foreach ($repeats as $key => $value) {
                $data->$value = isset($data->$key) ? count($data->$key) : 0;
            }

            $customdatasource->from_record($data);
            $customdatasource->update();

            \core\notification::success(get_string(
                'customdatasourceedited',
                'block_dash',
                $customdatasource->to_record()
            ));
            redirect(new moodle_url('/local/dash/datasources.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/datasources.php'));
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'delete':
        $PAGE->set_title(get_string('deletecustomdatasource', 'block_dash'));
        $PAGE->set_heading(get_string('deletecustomdatasource', 'block_dash'));
        $PAGE->navbar->add(get_string('deletecustomdatasource', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $customdatasource = new custom_data_source($id);

        if ($confirm = optional_param('confirm', 0, PARAM_BOOL)) {
            $customdatasource->delete();
            \core\notification::success(get_string(
                'customdatasourcedeleted',
                'block_dash',
                $customdatasource->to_record()
            ));
            redirect(new moodle_url('/local/dash/datasources.php'));
        }

        echo $OUTPUT->header();
        $url = clone $PAGE->url;
        $url->param('confirm', 1);

        $message = get_string('deleteconfirmcustomdatasource', 'block_dash', $customdatasource->to_record());
        echo $OUTPUT->confirm($message, $url, new moodle_url('/local/dash/datasources.php'));
        break;
}

echo $OUTPUT->footer();
