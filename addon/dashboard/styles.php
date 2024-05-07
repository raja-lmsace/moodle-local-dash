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
 * Dashboard background styles serving
 *
 * @package   dashaddon_dashboard
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use dashaddon_dashboard\model\dashboard;

// Do not show any debug messages and any errors which might break the shipped CSS.
define('NO_DEBUG_DISPLAY', true);

// Do not do any upgrade checks here.
define('NO_UPGRADE_CHECK', true);

// Require config.
// @codingStandardsIgnoreStart
// Let codechecker ignore the next line because otherwise it would complain about a missing login check
// after requiring config.php which is really not needed.require('../config.php');
require(__DIR__.'/../../../../config.php');


// Require css sending libraries.
require_once($CFG->dirroot.'/lib/csslib.php');
require_once($CFG->dirroot.'/lib/configonlylib.php');
require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");

// Get parameters.
$dashboardid = required_param('id', PARAM_INT);
$themerev = required_param('rev', PARAM_INT);

$dashboard = new dashboard($dashboardid);

$dashbgimage = dashaddon_dashboard_get_dashboard_background($dashboard->get('id'));

if ($dashbgimage) {
    // Course background image style css content.
    $style = "body.path-dashaddon-dashboard {
                background-image: url('" . $dashbgimage . "');
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
            }";
}

// Send out the resulting CSS code. The theme revision will be set as etag to support the browser caching.
css_send_cached_css_content($style, $themerev);
