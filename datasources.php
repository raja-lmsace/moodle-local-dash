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

require(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/adminlib.php");

global $USER;

$context = context_system::instance();

admin_externalpage_setup('localdashmanagedatasources');

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/dash/datasources.php'));
$PAGE->set_title(get_string('managedatasources', 'block_dash'));
$PAGE->set_heading(get_string('managedatasources', 'block_dash'));
$PAGE->navbar->add(get_string('managedatasources', 'block_dash'));

require_login();
require_capability('local/dash:managedatasources', $context);

echo $OUTPUT->header();

if (
    array_key_exists('developer', core_component::get_plugin_list('dashaddon')) &&
    has_capability('dashaddon/developer:managecustomdatasources', \context_system::instance())
) {
    echo $OUTPUT->single_button(
        new moodle_url('/local/dash/addon/developer/customdatasource.php', ['action' => 'create']),
        get_string('createcustomdatasource', 'block_dash'),
        'get',
        ['class' => 'pull-right']
    );

    echo $OUTPUT->heading(get_string('customdatasources', 'block_dash'));
    $table = new \dashaddon_developer\data_source\table\custom_data_source_table('datasources');
    $table->define_baseurl($PAGE->url);
    $table->out(25, false);
}

echo $OUTPUT->footer();
