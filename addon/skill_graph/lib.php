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
 * @package    dashaddon_skill_graph
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register the skill graph as widget to dash.
 *
 * @return array List of widgets.
 */
function dashaddon_skill_graph_register_widget(): array {

    return [
        [
            'name' => get_string('widget:skill_graph', 'dashaddon_skill_graph'),
            'identifier' => dashaddon_skill_graph\widget\competency_widget::class,
            'help' => 'widget:skill_graph',
        ],

        [
            'name' => get_string('widget:competency_progress', 'dashaddon_skill_graph'),
            'identifier' => dashaddon_skill_graph\widget\competency_progress_widget::class,
            'help' => 'widget:skill_graph',
        ],
    ];
}

/**
 * Dash plugin file definitions, List of fileareas used in local_dash plugin.
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
function dashaddon_skill_graph_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($context->contextlevel == CONTEXT_SYSTEM && (stripos($filearea, 'competencyimage') !== false)) {
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
        $file = $fs->get_file($context->id, 'dashaddon_skill_graph', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false; // The file does not exist.
        }

        // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
        send_stored_file($file, 86400, 0, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}
