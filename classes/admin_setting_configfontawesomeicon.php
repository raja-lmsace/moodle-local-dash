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
 * Admin setting for FontAwesome icon picker
 *
 * @package    local_dash
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/adminlib.php');

/**
 * Admin setting for FontAwesome icon picker using iconlib
 */
class admin_setting_configfontawesomeicon extends \admin_setting {
    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Store new setting
     *
     * @param mixed $data string or array, must not be NULL
     * @return bool true if ok, false if error occurred
     */
    public function write_setting($data) {
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Return XHTML for the setting
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $PAGE;

        $default = $this->get_defaultsetting();
        $elementid = $this->get_id();
        $elementname = $this->get_full_name();

        // Get all icons with names from iconlib.
        $allicons = iconlib::get_all_icons();
        $solidicons = iconlib::get_solid_icons();
        $brandicons = iconlib::get_brand_icons();

        // Build the HTML with icon preview.
        $html = '<div class="form-group row">';
        $html .= '<div class="col-md-3"></div>';
        $html .= '<div class="col-md-9">';

        // Create select element with optgroups.
        $html .= '<select id="' . $elementid . '" name="' . $elementname . '" class="form-control custom-select">';

        // Add empty option.
        $html .= '<option value="">' . htmlspecialchars(get_string('noicon', 'local_dash')) . '</option>';

        // Add Solid Icons group.
        $html .= '<optgroup label="' . htmlspecialchars(get_string('iconpickerfasolid', 'local_dash')) . '">';
        foreach ($solidicons as $iconclass => $iconname) {
            $selected = ($data == $iconclass) ? ' selected="selected"' : '';
            $html .= '<option value="' . htmlspecialchars($iconclass) . '"' . $selected . '>';
            $html .= htmlspecialchars($iconname);
            $html .= '</option>';
        }
        $html .= '</optgroup>';

        // Add Brand Icons group.
        $html .= '<optgroup label="' . htmlspecialchars(get_string('iconpickerfabrand', 'local_dash')) . '">';
        foreach ($brandicons as $iconclass => $iconname) {
            $selected = ($data == $iconclass) ? ' selected="selected"' : '';
            $html .= '<option value="' . htmlspecialchars($iconclass) . '"' . $selected . '>';
            $html .= htmlspecialchars($iconname);
            $html .= '</option>';
        }
        $html .= '</optgroup>';

        $html .= '</select>';

        // Add icon preview below the select with icon symbol.
        $html .= '<div class="form-control-feedback invalid-feedback" id="' . $elementid . '_error"></div>';

        if (!empty($data)) {
            $iconname = isset($allicons[$data]) ? $allicons[$data] : $data;
            $html .= '<div class="mt-3 p-3 border rounded" id="' . $elementid . '_preview" style="background-color: #f8f9fa;">';
            $html .= '<div style="font-size: 32px; color: #333;">';
            $html .= '<i class="' . htmlspecialchars($data) . '" aria-hidden="true"></i>';
            $html .= '</div>';
            $html .= '<div class="mt-2" style="font-size: 14px; color: #666;">';
            $html .= '<strong>' . htmlspecialchars($iconname) . '</strong><br>';
            $html .= '<code>' . htmlspecialchars($data) . '</code>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="mt-3 p-3 border rounded" id="' . $elementid .
                '_preview" style="display:none; background-color: #f8f9fa;"></div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        // Add JavaScript to update preview on change with icon display.
        $PAGE->requires->js_amd_inline("
            require(['jquery'], function($) {
                var iconMap = " . json_encode($allicons) . ";

                $('#{$elementid}').on('change', function() {
                    var iconClass = $(this).val();
                    var preview = $('#{$elementid}_preview');

                    if (iconClass && iconClass !== '') {
                        var iconName = iconMap[iconClass] || iconClass;
                        var html = '<div style=\"font-size: 32px; color: #333;\">';
                        html += '<i class=\"' + iconClass + '\" aria-hidden=\"true\"></i>';
                        html += '</div>';
                        html += '<div class=\"mt-2\" style=\"font-size: 14px; color: #666;\">';
                        html += '<strong>' + iconName + '</strong><br>';
                        html += '<code>' + iconClass + '</code>';
                        html += '</div>';
                        preview.html(html);
                        preview.show();
                    } else {
                        preview.hide();
                    }
                });
            });
        ");

        return format_admin_setting(
            $this,
            $this->visiblename,
            $html,
            $this->description,
            true,
            '',
            $default,
            $query
        );
    }
}
