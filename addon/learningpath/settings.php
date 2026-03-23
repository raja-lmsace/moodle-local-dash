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
 * Learning path widget administration settings.
 *
 * @package    dashaddon_learningpath
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ports = [
        'desktop_learningpath',
        'tablet_learningpath',
        'mobile_learningpath',
    ];
    foreach ($ports as $port) {
        $name = "dashaddon_learningpath/$port";
        $title = get_string($port, 'block_dash');
        $description = get_string($port . '_desc', 'block_dash');
        $setting = new \admin_setting_configstoredfile(
            $name,
            $title,
            $description,
            $port,
            0,
            ['maxfiles' => -1, 'accepted_types' => ['.svg']]
        );
        $page->add($setting);
    }


    // Supported zone elements setting.
    $page->add(new admin_setting_configtext(
        'dashaddon_learningpath/supported_zone_elements',
        get_string('supported_zone_elements', 'block_dash'),
        get_string('supported_zone_elements_desc', 'block_dash'),
        'circle,rect,polygon,ellipse,g',
        PARAM_TEXT
    ));

    // Info area.
    $name = 'dashaddon_learningpath/infoarea';
    $title = get_string('field:infoarea', 'block_dash');
    $description = get_string('field:infoarea_help', 'block_dash');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    // Info area position setting.
    $name = 'dashaddon_learningpath/infoareaposition';
    $title = get_string('field:infoareaposition', 'block_dash');
    $description = get_string('field:infoareaposition_help', 'block_dash');
    $default = 'top';
    $choices = [
        'top' => get_string('infoarea:top', 'block_dash'),
        'sidebar' => get_string('infoarea:sidebar', 'block_dash'),
    ];
    $page->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // KPI options.
    $kpioptions = [
        'none' => get_string('none', 'block_dash'),
        'courses' => get_string('kpi:courses', 'block_dash'),
        'coursespercent' => get_string('kpi:coursespercent', 'block_dash'),
        'badges' => get_string('kpi:badges', 'block_dash'),
        'period' => get_string('kpi:period', 'block_dash'),
        'status' => get_string('kpi:status', 'block_dash'),
    ];

    // KPI 1 setting.
    $name = 'dashaddon_learningpath/kpi1';
    $title = get_string('field:kpi1', 'block_dash');
    $description = '';
    $default = 'courses';
    $page->add(new admin_setting_configselect($name, $title, $description, $default, $kpioptions));

    // KPI 2 setting.
    $name = 'dashaddon_learningpath/kpi2';
    $title = get_string('field:kpi2', 'block_dash');
    $description = '';
    $default = 'badges';
    $page->add(new admin_setting_configselect($name, $title, $description, $default, $kpioptions));

    // KPI 3 setting.
    $name = 'dashaddon_learningpath/kpi3';
    $title = get_string('field:kpi3', 'block_dash');
    $description = '';
    $default = 'period';
    $page->add(new admin_setting_configselect($name, $title, $description, $default, $kpioptions));

    // KPI 4 setting.
    $name = 'dashaddon_learningpath/kpi4';
    $title = get_string('field:kpi4', 'block_dash');
    $description = '';
    $default = 'status';
    $page->add(new admin_setting_configselect($name, $title, $description, $default, $kpioptions));

    // Display path index setting.
    $name = 'dashaddon_learningpath/displaypathindex';
    $title = get_string('field:displaypathindex', 'block_dash');
    $description = get_string('field:displaypathindex_help', 'block_dash');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    // Display faculty setting.
    global $DB;
    $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
    [$insql, $inparams] = $DB->get_in_or_equal(array_values($courseroles));
    $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
    $rolesoptions = role_fix_names($roles, null, ROLENAME_ALIAS, true);

    $name = 'dashaddon_learningpath/displayfaculty';
    $title = get_string('field:displayfaculty', 'block_dash');
    $description = get_string('field:displayfaculty_help', 'block_dash');
    $default = [];
    $page->add(new admin_setting_configmultiselect($name, $title, $description, $default, $rolesoptions));

    // Display badges setting.
    $name = 'dashaddon_learningpath/displaybadges';
    $title = get_string('field:displaybadges', 'block_dash');
    $description = get_string('field:displaybadges_help', 'block_dash');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    // Default colour status.
    $unavailablecolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/unavailablecirclecolor',
        get_string('unavailablecirclecolor', 'block_dash'),
        get_string('unavailablecirclecolor_desc', 'block_dash'),
        '#CBCBCB'
    );
    $page->add($unavailablecolor);

    $availablecolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/availablecirclecolor',
        get_string('availablecirclecolor', 'block_dash'),
        get_string('availablecirclecolor_desc', 'block_dash'),
        '#808080'
    );
    $page->add($availablecolor);

    $notstartedcolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/notstartedcirclecolor',
        get_string('notstartedcirclecolor', 'block_dash'),
        get_string('notstartedcirclecolor_desc', 'block_dash'),
        '#00008b' // Default dark blue.
    );
    $page->add($notstartedcolor);

    $inprogresscolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/inprogresscirclecolor',
        get_string('inprogresscirclecolor', 'block_dash'),
        get_string('inprogresscirclecolor_desc', 'block_dash'),
        '#ffd700'
    );
    $page->add($inprogresscolor);

    $completedcolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/completedcirclecolor',
        get_string('completedcirclecolor', 'block_dash'),
        get_string('completedcirclecolor_desc', 'block_dash'),
        '#11b56a'
    );
    $page->add($completedcolor);

    $failedcolor = new \admin_setting_configcolourpicker(
        'dashaddon_learningpath/failedcirclecolor',
        get_string('failedcirclecolor', 'block_dash'),
        get_string('failedcirclecolor_desc', 'block_dash'),
        '#ff0000'
    );
    $page->add($failedcolor);

    $courseimgsizes = [
        'dot' => get_string('dot', 'block_dash'),
        'tiny' => get_string('tinyimage', 'block_dash'),
        'small' => get_string('smallimage', 'block_dash'),
        'medium' => get_string('mediumimage', 'block_dash'),
        'large' => get_string('largeimage', 'block_dash'),
        'extralarge' => get_string('extralargeimage', 'block_dash'),
    ];

    $defaultcourseimgsize = new \admin_setting_configselect(
        'block_dash/defaultcourseimgsize',
        get_string('courseimgsize', 'block_dash'),
        get_string('courseimgsize_desc', 'block_dash'),
        'dot',
        $courseimgsizes
    );

    $page->add($defaultcourseimgsize);

    $shapes = [
        'circle'   => get_string('shape:circle', 'block_dash'),
        'triangle' => get_string('shape:triangle', 'block_dash'),
        'hexagon'  => get_string('shape:hexagon', 'block_dash'),
        'diamond'  => get_string('shape:diamond', 'block_dash'),
        'star'     => get_string('shape:star', 'block_dash'),
        'custom' => get_string('customfields', 'block_dash'),
    ];

    $defaultcourseshape = new \admin_setting_configselect(
        'dashaddon_learningpath/defaultcourseshape',
        get_string('courseshape', 'block_dash'),
        get_string('courseshape_desc', 'block_dash'),
        'circle',
        $shapes
    );

    $page->add($defaultcourseshape);
}
