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
 * @package    dashaddon_course_completions
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register the skill graph as widget to dash.
 *
 * @return array List of widgets.
 */
function dashaddon_course_completions_register_widget(): array {
    return [
        [
            'name' => get_string('widget:course_completions', 'block_dash'),
            'identifier' => dashaddon_course_completions\widget\completion_widget::class,
            'help' => 'widget:course_completions',
        ],
    ];
}

/**
 * The require plugin dependencies added for the soft dependencies in the course completions dash addon.
 *
 * @return string
 */
function dashaddon_course_completions_extend_added_dependencies() {
    global $OUTPUT;
    $manager = \core_plugin_manager::instance();
    $dependencies = [
        'dashaddon_courses',
        'dashaddon_categories',
    ];
    foreach ($dependencies as $dependency) {
        $plugin = $manager->get_plugin_info($dependency);
        if (!$plugin) {
            return $OUTPUT->render_from_template('dashaddon_course_completions/upgrade', ['plugin' => $dependency]);
        } else if ($plugin->get_status() === core_plugin_manager::PLUGIN_STATUS_MISSING) {
            return $OUTPUT->render_from_template('dashaddon_course_completions/upgrade', ['plugin' => $dependency]);
        }
    }
    return '';
}
