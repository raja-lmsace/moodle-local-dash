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
 *
 * @package    dashaddon_dashboard
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use dashaddon_dashboard\model\dashboard;

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/adminlib.php");
require_once("$CFG->dirroot/cohort/lib.php");
require_once($CFG->dirroot . '/lib/csslib.php');
require_once($CFG->dirroot . '/lib/configonlylib.php');
require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");

global $PAGE, $DB, $CFG, $USER;

$USER->editing = (isset($USER->editing)) ? $USER->editing : 0;

$edit = optional_param('edit', $USER->editing, PARAM_INT);
if (!$id = optional_param('id', null, PARAM_INT)) {
    foreach (dashboard::get_records() as $dashboard) {
        if ($dashboard->has_access($USER)) {
            redirect(new moodle_url('/local/dash/addon/dashboard/dashboard.php', ['id' => $dashboard->get('id')]));
        }
    }

    redirect(new moodle_url('/my'));
}

$dashboard = new dashboard($id);

$context = $dashboard->get_context_instance();
$course = null;
$PAGE->set_context($context);
if ($coursecontext = $context->get_course_context(false)) {
    $course = $DB->get_record('course', ['id' => $coursecontext->instanceid]);
    $PAGE->set_context($coursecontext);
    $PAGE->set_course($course);
    if (get_user_preferences('drawer-open-index')) {
        set_user_preference('drawer-open-index', false);
    }
}

$PAGE->set_url(new moodle_url('/local/dash/addon/dashboard/dashboard.php', [
    'id' => $dashboard->get('id'),
]));

if (!$dashboard->has_access($USER)) {
    throw new moodle_exception('notauthorized', 'block_dash');
}

$regionname = clean_param($dashboard->get('shortname'), PARAM_ALPHAEXT);

$PAGE->set_pagetype('dashaddon-dashboard-' . $regionname);
$PAGE->blocks->add_region($regionname);
$PAGE->blocks->set_default_region($regionname);
$PAGE->set_pagelayout('mydashboard');

$permission = $dashboard->get('permission');

if ($permission !== 'public') {
    require_login();

    if (!$dashboard->has_access($USER)) {
        throw new moodle_exception('notauthorized', 'block_dash');
    }
}

$PAGE->set_title($dashboard->get('name'));
$PAGE->set_heading($dashboard->get('name'));

if (has_capability('local/dash:managedashboards', $context)) {
    $PAGE->navbar->add(
        get_string('managedashboards', 'block_dash'),
        new moodle_url('/local/dash/addon/dashboard/dashboard_list.php'),
    );
}

if ($course) {
    $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', ['id' => $course->id]));
}

$PAGE->navbar->add($dashboard->get('name'), $PAGE->url);

if ($PAGE->user_allowed_editing()) {
    $USER->editing = $edit;

    $url = clone $PAGE->url;
    if ($edit) {
        $url->param('edit', 0);
    } else {
        $url->param('edit', 1);
    }

    if (!class_exists('\core\navigation\views\secondary')) {
        $button = $OUTPUT->single_button($url, $edit ? get_string('turneditingoff') : get_string('turneditingon'));
        $PAGE->set_button($button);
    }
}

// Send out the resulting CSS code. The theme revision will be set as etag to support the browser caching.
$includestyle = new \moodle_url('/local/dash/addon/dashboard/styles.php', ['id' => $dashboard->get('id'),
    'rev' => theme_get_revision(),
]);

$PAGE->requires->js_amd_inline("
    require(['jquery'], function($) {
        var element = $('.block-region section:nth-of-type(1)');
        var elementBottom = element.offset().top + element.outerHeight();
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > elementBottom) {
                if (!$('.dash-tab-element').hasClass('fixed-top')) {
                    $('.dash-tab-element').addClass('fixed-top');
                }
            } else {
                if ($('.dash-tab-element').hasClass('fixed-top')) {
                    $('.dash-tab-element').removeClass('fixed-top');
                }
            }
        });
    })
");

$PAGE->requires->js_call_amd('dashaddon_dashboard/dashboard', 'init');

$PAGE->requires->css($includestyle);
if ($PAGE->user_is_editing()) {
    if ($context->contextlevel == CONTEXT_COURSECAT) {
        if (has_capability('local/dash:managecoursecatedashboards', $context)) {
            $PAGE->set_button($OUTPUT->single_button(
                new \moodle_url(
                    '/local/dash/addon/dashboard/dashboards.php',
                    ['action' => 'edit', 'id' => $dashboard->get('id')]
                ),
                get_string('editdashboard', 'block_dash'),
                'get'
            ));
        }
    } else {
        if (has_capability('local/dash:managedashboards', $context)) {
            $PAGE->set_button($OUTPUT->single_button(
                new \moodle_url(
                    '/local/dash/addon/dashboard/dashboards.php',
                    ['action' => 'edit', 'id' => $dashboard->get('id')]
                ),
                get_string('editdashboard', 'block_dash'),
                'get'
            ));
        }
    }
}

echo $OUTPUT->header();

echo $OUTPUT->addblockbutton($regionname);

$renderer = $PAGE->get_renderer('local_dash');

echo $renderer->custom_block_region($regionname, $dashboard);

echo $OUTPUT->footer();
