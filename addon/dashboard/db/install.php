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
 * DB authentication plugin install code
 *
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for database installation.
 */
function xmldb_dashaddon_dashboard_install() {
    global $CFG, $DB;
    require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");
    set_config('enabled', 1, 'dashaddon_dashboard');

    // Create the dashaddon_dashboard_dash table.
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('dashaddon_dashboard_dash')) {
        return true;
    }
    // Define table dash_data_source to be created.
    $table = new xmldb_table('dashaddon_dashboard_dash');
    // Adding fields to table dash_data_source.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('permission', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
    $table->add_field('cohort_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('roles', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('rolecontext', XMLDB_TYPE_TEXT, '10', null, null, null, null);
    $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
    $table->add_field('dashicon', XMLDB_TYPE_CHAR, '50', null, null, null, null);
    $table->add_field('dashthumbnailimage', XMLDB_TYPE_INTEGER, '15', null, null, null, null);
    $table->add_field('dashbgimage', XMLDB_TYPE_TEXT, '4', null, null, null, null);
    $table->add_field('secondarynav', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
    $table->add_field('coredash', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
    // Adding new fields for on page navigation settings.
    $table->add_field('includedblocks', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('displaydashboardtitle', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('displaycta', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('ctalink', XMLDB_TYPE_CHAR, '255', null, null, null, 'enrolment');
    $table->add_field('ctacampaignid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('ctacustomurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('ctacustomurltext', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    // Adding new fields for context.
    $table->add_field('contexttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'system');
    $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('redirecttodashboard', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
    // Adding keys to table dash_data_source.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    dashaddon_dashboard_create_core_dashboard();
}
