<?php
// This file is part of The Bootstrap Moodle theme
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
 * Library functions defined for skill graph widget.
 *
 * @package    dashaddon_dashboard
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Constants which are use throughout this dashaddon.
define('ANYCONTEXT', 1);
define('SYSTEMCONTEXT', 2);

/**
 * Dashboard plugin file definitions, List of fileareas used in local_dash plugin.
 *
 * @param stdclass $course
 * @param stdclass $cm
 * @param stdclass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 */
function dashaddon_dashboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    $fileareas = [
        'dashbgimage',
        'dashthumbnailimage',
    ];

    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, $fileareas) !== false) {
        // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
        $itemid = array_shift($args);
        // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
        // user really does have access to the file in question.
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args); // The last item in the $args array.
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }

        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'dashaddon_dashboard', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false; // The file does not exist.
        }

        // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
        send_stored_file($file, 86400, 0, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Get the dashboard background image.
 *
 * @param int $dashboardid Dashboard ID
 * @return string
 */
function dashaddon_dashboard_get_dashboard_background($dashboardid) {
    $fs = get_file_storage();
    $files = $fs->get_area_files(
        \context_system::instance()->id,
        'dashaddon_dashboard',
        'dashbgimage',
        $dashboardid,
        '',
        false
    );

    if (!empty($files)) {
        // Get the first file.
        $file = reset($files);

        $url = \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );
        return $url;
    }
    return '';
}

/**
 * Create the core dashboard and updated the moodle dashboard in the dash dashboard table.
 */
function dashaddon_dashboard_create_core_dashboard() {
    global $DB, $USER;

    if ($DB->record_exists('dashaddon_dashboard_dash', ['shortname' => 'coredashboard'])) {
        return;
    }

    $dashboard = new stdclass();
    $dashboard->shortname = 'coredashboard';
    $dashboard->contextid = context_system::instance()->id;
    $dashboard->timecreated = time();
    $dashboard->timemodified = time();
    $dashboard->permission = 'public';
    $dashboard->coredash = 1;
    $dashboard->usermodified = $USER->id;
    $dashboard->name = get_string('maindashboard', 'block_dash');
    $DB->insert_record('dashaddon_dashboard_dash', $dashboard);
}


/**
 * Extend the course navigation, then added the course context dashboard link in secondary menu.
 *
 * @param \navigation_node $coursenode
 * @param stdclass $course
 * @param \context_course $coursecontext
 * @return void
 */
function dashaddon_dashboard_extend_navigation_course($coursenode, $course, $coursecontext) {
    global $PAGE, $USER, $DB;
    if ($PAGE->context instanceof \context_course) {
        $context = $PAGE->context;
        if ($records = $DB->get_records('dashaddon_dashboard_dash', ['contextid' => $context->id, 'secondarynav' => 1])) {
            foreach ($records as $id => $record) {
                $dashboard = new \dashaddon_dashboard\model\dashboard($record->id);
                if ($dashboard->has_access($USER)) {
                    $url = new moodle_url('/local/dash/addon/dashboard/dashboard.php', ['id' => $record->id]);
                    $node = navigation_node::create(
                        $record->name,
                        $url,
                        navigation_node::TYPE_SETTING,
                        '',
                        $record->shortname,
                        new pix_icon('i/dashboard', '')
                    );
                    $node->add_class('dash-course-dashboard');
                    $coursenode->add_node($node);
                    $nodes[] = $record->shortname;
                }
            }

            if (isset($nodes) && !empty($nodes)) {
                $PAGE->requires->js_amd_inline("
                    require(['jquery', 'core/moremenu'], function($, moremenu) {
                        window.onload=() => {
                            var secondarynav = document.querySelector('.secondary-navigation ul.nav-tabs');
                            secondarynav.querySelector('.nav-link.active').classList.remove('active');
                            var dashDashboard = document.querySelectorAll('.dash-course-dashboard');
                            dashDashboard.forEach((e) => {
                                e.classList.remove('dropdown-item');
                                e.classList.add('nav-link');
                                parent = e.parentNode;
                                parent.setAttribute('data-forceintomoremenu', 'false');
                                secondarynav.insertBefore(parent, secondarynav.children[1]);
                            })
                            moremenu(secondarynav);
                        }
                    })
                ");
            }
        }
    }
}

/**
 * Change the block instance pagetype pattern.
 *
 * @return void
 */
function dashaddon_dashboard_change_pagetypepattern() {
    global $DB;
    $sql = "SELECT * FROM {block_instances} WHERE blockname = 'dash' AND pagetypepattern LIKE 'local-dash-dashboard%'";
    $records = $DB->get_records_sql($sql);
    foreach ($records as $record) {
        $pagetypepattern = $record->pagetypepattern;
        $prefix = 'local-dash';
        $replacement = 'dashaddon';
        $modifiypattern = str_replace($prefix, $replacement, $pagetypepattern);
        $record->pagetypepattern = $modifiypattern;
        $DB->update_record('block_instances', $record);
    }
    return true;
}
