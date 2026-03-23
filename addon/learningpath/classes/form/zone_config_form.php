<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Zone configuration form.
 *
 * @package    dashaddon_learningpath
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Zone configuration form.
 *
 * @package    dashaddon_learningpath
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class zone_config_form extends \moodleform {
    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $blockid = $customdata['blockid'];
        $svgs = $customdata['svgs'];
        $courses = $customdata['courses'];

        // Hidden field for block ID.
        $mform->addElement('hidden', 'blockid', $blockid);
        $mform->setType('blockid', PARAM_INT);

        // Create tabs using HTML.
        if (!empty($svgs)) {
            $tabshtml = $this->render_tabs($svgs);
            $mform->addElement('html', $tabshtml);

            // Add form elements for each SVG type.
            foreach ($svgs as $svg) {
                $this->add_svg_section($mform, $svg, $courses);
            }

            // Close tabs HTML.
            $mform->addElement('html', '</div></div>');
        }
    }

    /**
     * Render tabs HTML.
     *
     * @param array $svgs SVG data
     * @return string HTML for tabs
     */
    private function render_tabs($svgs) {
        $html = '<div class="zone-config-container">';
        $html .= '<ul class="nav nav-tabs mb-3" role="tablist" id="zone-tabs">';

        foreach ($svgs as $svg) {
            $active = $svg['first'] ? 'active' : '';
            $selected = $svg['first'] ? 'true' : 'false';
            $html .= '<li class="nav-item">';
            $html .= sprintf(
                '<a class="nav-link %s" id="%s-tab" data-toggle="tab" href="#%s-content" ' .
                'role="tab" aria-controls="%s-content" aria-selected="%s">%s</a>',
                $active,
                $svg['svgtype'],
                $svg['svgtype'],
                $svg['svgtype'],
                $selected,
                $svg['displayname']
            );
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '<div class="tab-content">';

        return $html;
    }

    /**
     * Add SVG section with zones.
     *
     * @param object $mform Form object
     * @param array $svg SVG data
     * @param array $courses Available courses
     */
    private function add_svg_section($mform, $svg, $courses) {
        $svgtype = $svg['svgtype'];
        $active = $svg['first'] ? 'show active' : '';

        // Start tab pane.
        $html = sprintf(
            '<div class="tab-pane fade %s" id="%s-content" role="tabpanel" aria-labelledby="%s-tab">',
            $active,
            $svgtype,
            $svgtype
        );
        $html .= '<div class="row">';

        // SVG Display Column.
        $html .= '<div class="col-md-8">';
        $html .= '<div class="svg-container border p-3">';
        $html .= '<h5>' . $svg['displayname'] . ' ' . get_string('preview', 'core') . '</h5>';
        $html .= '<div class="svg-wrapper">';
        $html .= $svg['svgcontent'];
        $html .= '</div></div></div>';

        // Zone List Column.
        $html .= '<div class="col-md-4">';
        $html .= '<div class="zone-list border p-3">';
        $html .= '<h5>' . get_string('zones_found', 'block_dash') . ' (' . count($svg['zones']) . ')</h5>';

        $mform->addElement('html', $html);

        // Group zones by parent groups.
        $groupedzones = $this->group_zones_by_parent($svg['zones']);

        // Add form elements for each zone.
        if (!empty($groupedzones)) {
            foreach ($groupedzones as $item) {
                if ($item['isgroup']) {
                    // This is a group with children.
                    $this->add_group_zone($mform, $svgtype, $item, $courses);
                } else {
                    // This is a standalone zone.
                    $this->add_zone_config($mform, $svgtype, $item['zone'], $courses, false);
                }
            }
        } else {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('no_zones_found', 'block_dash') . '</div>');
        }

        // Close zone list and tab pane.
        $mform->addElement('html', '</div></div></div></div>');
    }

    /**
     * Group zones by parent groups.
     *
     * @param array $zones All zones
     * @return array Grouped zones
     */
    private function group_zones_by_parent($zones) {
        $result = [];
        $currentgroup = null;

        foreach ($zones as $zone) {
            if ($zone['zonetype'] == 'g') {
                // If there was a previous group, add it to results first.
                if ($currentgroup !== null) {
                    $result[] = $currentgroup;
                }

                // Start a new group.
                $currentgroup = [
                    'isgroup' => true,
                    'groupzone' => $zone,
                    'children' => [],
                ];
            } else {
                // Regular zone (not a group).
                if ($currentgroup !== null) {
                    // Add to current group as a child.
                    $currentgroup['children'][] = $zone;
                } else {
                    // Standalone zone (not in a group).
                    $result[] = [
                        'isgroup' => false,
                        'zone' => $zone,
                    ];
                }
            }
        }

        // Add the last group if exists.
        if ($currentgroup !== null) {
            $result[] = $currentgroup;
        }

        return $result;
    }

    /**
     * Add group zone with children.
     *
     * @param object $mform Form object
     * @param string $svgtype SVG type
     * @param array $groupitem Group item with children
     * @param array $courses Available courses
     */
    private function add_group_zone($mform, $svgtype, $groupitem, $courses) {
        $groupzone = $groupitem['groupzone'];
        $children = $groupitem['children'];

        // Group header HTML - NO FORM FIELDS FOR THE GROUP ITSELF.
        $html = '<div class="zone-group mb-4 p-3" style="background-color: #f8f9fa; border-left: 4px solid #007bff;">';
        $html .= '<div class="zone-group-header mb-2">';
        $html .= '<h6 class="mb-1"><i class="fa fa-folder-open"></i> ';
        $html .= '<strong>' . get_string('zone_type_group', 'block_dash') . '</strong>';
        $html .= '</h6>';
        $html .= '<small class="text-muted">';
        $html .= get_string('zone_id', 'block_dash') . ': <code>' . $groupzone['id'] . '</code>';
        $html .= '</small>';
        $html .= '</div>';

        $mform->addElement('html', $html);

        // Add each child zone with form fields.
        if (!empty($children)) {
            $html = '<div class="zone-group-children pl-3">';
            $mform->addElement('html', $html);

            foreach ($children as $childzone) {
                $this->add_zone_config($mform, $svgtype, $childzone, $courses, true);
            }

            $mform->addElement('html', '</div>');
        } else {
            // No children in group.
            $html = '<div class="alert alert-warning ml-3">';
            $html .= get_string('group_no_children', 'block_dash');
            $html .= '</div>';
            $mform->addElement('html', $html);
        }

        // Close group container.
        $mform->addElement('html', '</div>');
    }

    /**
     * Add zone configuration form elements.
     *
     * @param object $mform Form object
     * @param string $svgtype SVG type
     * @param array $zone Zone data
     * @param array $courses Available courses
     * @param bool $isgroupchild Whether this zone is inside a group
     */
    private function add_zone_config($mform, $svgtype, $zone, $courses, $isgroupchild = false) {
        $zoneid = $zone['id'];

        $prefix = $zoneid;
        // Start zone item container.
        $disabled = !$zone['enabled'] ? 'zone-disabled' : '';
        $childclass = $isgroupchild ? 'zone-group-child' : '';
        $html = sprintf(
            '<div class="zone-item mb-3 p-2 border-bottom %s %s" data-zone-id="%s" data-zone-type="%s">',
            $disabled,
            $childclass,
            $zoneid,
            $zone['type']
        );

        // Zone Type.
        $html .= '<div class="zone-type">';
        $html .= '<strong>' . get_string('zone_type', 'block_dash') . ':</strong> ';
        $html .= $zone['typename'];
        $html .= '</div>';

        // Zone ID.
        $html .= '<div class="zone-id">';
        $html .= '<strong>' . get_string('zone_id', 'block_dash') . ':</strong> ';
        $html .= '<code>' . $zoneid . '</code>';
        $html .= '</div>';

        $mform->addElement('html', $html);

        // Enable/Disable checkbox.
        $mform->addElement(
            'advcheckbox',
            $prefix . '_enabled',
            get_string('zone_enabled', 'block_dash'),
            '',
            ['group' => 1],
            [0, 1]
        );
        $mform->setDefault($prefix . '_enabled', $zone['enabled']);

        // Course selection dropdown.
        $courseoptions = [0 => get_string('none', 'block_dash')];
        foreach ($courses as $course) {
            $courseoptions[$course['id']] = $course['fullname'];
        }

        $mform->addElement(
            'autocomplete',
            $prefix . '_courseid',
            get_string('assign_course', 'block_dash'),
            $courseoptions
        );
        $mform->setDefault($prefix . '_courseid', $zone['courseid'] ?? 0);

        // Hidden field for zone ID.
        $mform->addElement('hidden', $prefix . '_zoneid', $zoneid);
        $mform->setType($prefix . '_zoneid', PARAM_TEXT);

        // Hidden field for zone type.
        $mform->addElement('hidden', $prefix . '_zonetype', $zone['zonetype']);
        $mform->setType($prefix . '_zonetype', PARAM_TEXT);

        // Close zone item container.
        $mform->addElement('html', '</div>');
    }

    /**
     * Form validation.
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array Validation errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
