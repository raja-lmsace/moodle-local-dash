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
 * Admin setting for AJAX-based autocomplete (e.g., FontAwesome icon selector).
 *
 * @package    local_dash
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Admin setting for AJAX-based autocomplete (e.g., FontAwesome icon selector).
 * Extends admin_setting directly to avoid choice validation issues.
 *
 * @package    local_dash
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_ajax_autocomplete extends \admin_setting {
    /** @var bool $tags Should we allow typing new entries to the field? */
    protected $tags = false;

    /** @var string $ajax Name of an AMD module to send/process ajax requests. */
    protected $ajax = '';

    /** @var string $placeholder Placeholder text for an empty list. */
    protected $placeholder = '';

    /** @var bool $casesensitive Whether the search has to be case-sensitive. */
    protected $casesensitive = false;

    /** @var bool $showsuggestions Show suggestions by default. */
    protected $showsuggestions = true;

    /** @var string $noselectionstring String that is shown when there are no selections. */
    protected $noselectionstring = '';

    /** @var callable $valuehtmlcallback Callback to render selected value HTML */
    protected $valuehtmlcallback = null;

    /**
     * Constructor.
     *
     * @param string $name unique ascii name
     * @param string $visiblename localised name
     * @param string $description localised long description
     * @param string $defaultsetting default value
     * @param array $options Options for autocomplete:
     *   - ajax: AMD module name for AJAX autocomplete
     *   - placeholder: Placeholder text
     *   - tags: Allow typing new entries
     *   - casesensitive: Case-sensitive search
     *   - showsuggestions: Show suggestions by default
     *   - noselectionstring: String shown when there are no selections
     *   - valuehtmlcallback: Callback function to render value HTML
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = '', $options = []) {
        // Call parent constructor.
        parent::__construct($name, $visiblename, $description, $defaultsetting);

        // Set AJAX and other autocomplete options.
        $this->ajax = $options['ajax'] ?? '';
        $this->placeholder = $options['placeholder'] ?? get_string('search');
        $this->tags = $options['tags'] ?? false;
        $this->casesensitive = $options['casesensitive'] ?? false;
        $this->showsuggestions = $options['showsuggestions'] ?? true;
        $this->noselectionstring = $options['noselectionstring'] ?? '';
        $this->valuehtmlcallback = $options['valuehtmlcallback'] ?? null;
    }

    /**
     * Return the setting value.
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Store the setting value.
     *
     * @param string $data
     * @return string empty string if ok, error message otherwise
     */
    public function write_setting($data) {
        // Validate the data.
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        // Write to config.
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Validate the setting value.
     *
     * @param string $data
     * @return mixed true if ok, string error message otherwise
     */
    public function validate($data) {
        // Allow empty value.
        if ($data === '' || $data === null) {
            return true;
        }
        // Allow any string value for AJAX autocomplete.
        // Since options are loaded dynamically, we can't validate against a fixed list.
        return true;
    }

    /**
     * Return XHTML for the setting.
     *
     * @param string $data Current setting value
     * @param string $query Search query
     * @return string XHTML
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $default = $this->get_defaultsetting();
        // Build the select element.
        $select = \html_writer::start_tag('select', [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'class' => 'form-control',
        ]);

        // Add current value as an option if it exists.
        if (!empty($data)) {
            $label = $this->get_value_label($data);
            $select .= \html_writer::tag('option', $label, [
                'value' => $data,
                'selected' => 'selected',
            ]);
        } else {
            // Add empty option.
            $select .= \html_writer::tag('option', $this->noselectionstring, [
                'value' => '',
            ]);
        }

        $select .= \html_writer::end_tag('select');

        // Prepare parameters for JavaScript.
        $params = [
            '#' . $this->get_id(),
            $this->tags,
            $this->ajax,
            $this->placeholder,
            $this->casesensitive,
            $this->showsuggestions,
            $this->noselectionstring,
        ];

        // Load autocomplete wrapper.
        $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', $params);

        // Return formatted admin setting.
        return format_admin_setting(
            $this,
            $this->visiblename,
            $select,
            $this->description,
            true,
            '',
            $default,
            $query
        );
    }

    /**
     * Get label for a value.
     *
     * @param string $value
     * @return string
     */
    protected function get_value_label($value) {
        global $OUTPUT;

        // If there's a value HTML callback, use it.
        if ($this->valuehtmlcallback && is_callable($this->valuehtmlcallback)) {
            try {
                $html = call_user_func($this->valuehtmlcallback, $value);
                if (!empty($html)) {
                    return $html;
                }
            } catch (\Exception $e) {
                debugging('Error in valuehtmlcallback: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        // Fallback: return the value itself.
        return htmlspecialchars($value);
    }
}
