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
 * List of dashaddon skill graph instance in course.
 *
 * @package   dashaddon_skill_graph
 * @copyright 2023, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../config.php');

require_login();

$pageurl = new moodle_url('/local/dash/addon/skill_graph/competencylist.php');

$systemcontext = context_system::instance();

if (!has_capability('moodle/competency:competencymanage', $systemcontext)) {
    throw new moodle_exception('managecapabilitymissing', 'block_dash');
}

$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('listcompetencyframeworkscaption', 'tool_lp'));
$PAGE->set_heading(get_string('managecompentency', 'block_dash'));
$PAGE->set_url($pageurl);

echo $OUTPUT->header();

// List of main competencies.
$list = \core_competency\api::list_competencies(['parentid' => 0]);
if (empty($list)) {
    notice(get_string('compentenciesnotfound', 'block_dash'));
}

// Create a simple html table to list the main competencies with link to setup.
$table = new html_table();
$table->attributes['class'] = 'generaltable competency-list';
$table->head = [get_string('name'), ''];

$modules = [];
foreach ($list as $key => $value) {
    $data = [];
    $editurl = new moodle_url('/local/dash/addon/skill_graph/competencylist.php', ['id' => $value->get('id'), 'action' => 'setup']);
    $reports = new moodle_url('/admin/tool/lp/editcompetency.php', [
        'competencyframeworkid' => $value->get('competencyframeworkid'),
        'id' => $value->get('id'),
        'parentid' => $value->get('parentid'),
        'pagecontextid' => \context_system::instance()->id,
    ]);

    $data[] = html_writer::link($reports, format_string($value->get('shortname')), [
        'class' => 'competency-instance-name',
    ]);

    $data[] = html_writer::link($editurl, get_string('setup', 'block_dash'), [
        'data-competency-id' => $value->get('id'), 'data-target' => 'competency-setup',
        'class' => 'competency-setup',
    ]);

    $table->data[] = $data;
}


echo html_writer::div(html_writer::table($table), 'mt-5');

$PAGE->requires->js_call_amd('dashaddon_skill_graph/skill_progress', 'init', []);

echo $OUTPUT->footer();
