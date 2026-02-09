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
 * Manage custom layouts.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_dash\output\renderer;
use dashaddon_developer\model\custom_layout;
use dashaddon_developer\layout\form\custom_layout_form;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/adminlib.php");

global $PAGE, $DB, $USER;

$action = required_param('action', PARAM_TEXT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dash/addon/developer/customlayout.php', ['action' => $action]));
$PAGE->navbar->add(get_string('managelayouts', 'block_dash'), new moodle_url('/local/dash/addon/developer/customlayouts.php'));

/** @var renderer $renderer */
$renderer = $PAGE->get_renderer('block_dash');

require_login();
require_capability('dashaddon/developer:managecustomlayouts', $context);

if (in_array($action, ['edit', 'create'])) {
    $PAGE->requires->js_call_amd('dashaddon_developer/layout_form');
    $PAGE->requires->css('/local/dash/addon/developer/css/codemirror.css');
    $PAGE->requires->css('/local/dash/addon/developer/css/codemirror-show-hint.css');
}

if (in_array($action, ['edit', 'create'])) {
    $PAGE->requires->js_call_amd('dashaddon_developer/layout_form');
    $PAGE->requires->css('/local/dash/addon/developer/css/codemirror.css');
    $PAGE->requires->css('/local/dash/addon/developer/css/codemirror-show-hint.css');
}

switch ($action) {
    case 'create':
        $PAGE->set_title(get_string('createcustomlayout', 'block_dash'));
        $PAGE->set_heading(get_string('createcustomlayout', 'block_dash'));
        $PAGE->navbar->add(get_string('createcustomlayout', 'block_dash'));

        $form = new custom_layout_form($PAGE->url, [
            'persistent' => null,
            'userid' => $USER->id,
        ]);

        if ($data = $form->get_data()) {
            $customlayout = new custom_layout(0, $data);
            $customlayout->create();

            \core\notification::success(get_string(
                'customlayoutcreated',
                'block_dash',
                $customlayout->to_record()
            ));
            redirect(new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'edit':
        $PAGE->set_title(get_string('editcustomlayout', 'block_dash'));
        $PAGE->set_heading(get_string('editcustomlayout', 'block_dash'));
        $PAGE->navbar->add(get_string('editcustomlayout', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $customlayout = new custom_layout($id);

        $form = new custom_layout_form($PAGE->url, [
            'persistent' => $customlayout,
        ]);

        if ($data = $form->get_data()) {
            $customlayout->from_record($data);
            $customlayout->update();

            \core\notification::success(get_string(
                'customlayoutedited',
                'block_dash',
                $customlayout->to_record()
            ));
            redirect(new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        } else if ($form->is_cancelled()) {
            redirect(new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        }

        echo $OUTPUT->header();
        $form->display();

        break;

    case 'delete':
        $PAGE->set_title(get_string('deletecustomlayout', 'block_dash'));
        $PAGE->set_heading(get_string('deletecustomlayout', 'block_dash'));
        $PAGE->navbar->add(get_string('deletecustomlayout', 'block_dash'));

        $id = required_param('id', PARAM_INT);
        $url = clone $PAGE->url;
        $url->params(['id' => $id]);
        $PAGE->set_url($url);

        $customlayout = new custom_layout($id);

        if ($confirm = optional_param('confirm', 0, PARAM_BOOL)) {
            $customlayout->delete();
            \core\notification::success(get_string(
                'customlayoutdeleted',
                'block_dash',
                $customlayout->to_record()
            ));
            redirect(new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        }

        echo $OUTPUT->header();
        $url = clone $PAGE->url;
        $url->param('confirm', 1);

        $message = get_string('deleteconfirmcustomlayout', 'block_dash', $customlayout->to_record());
        echo $OUTPUT->confirm($message, $url, new moodle_url('/local/dash/addon/developer/customlayouts.php'));
        break;
}


echo $OUTPUT->footer();
