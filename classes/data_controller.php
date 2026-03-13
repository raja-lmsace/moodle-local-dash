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
 * Data controller for multi-category custom field.
 *
 * @package   customfield_multicategory
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_multicategory;

/**
 * Data controller class for multi-category custom field.
 *
 * @package   customfield_multicategory
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {
    /**
     * Return the name of the field where the information is stored.
     *
     * @return string
     */
    public function datafield(): string {
        return 'value';
    }

    /**
     * Returns the default value as it would be stored in the database.
     *
     * @return mixed
     */
    public function get_default_value() {
        return json_encode([]);
    }

    /**
     * Add fields for editing multicategory data on the course form.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $elementname = $this->get_form_element_name();

        // Get the course ID.
        $courseid = null;
        if ($this->get('instanceid')) {
            $courseid = $this->get('instanceid');
        }

        // Get available categories based on configuration and permissions.
        $categories = $field->get_available_categories($courseid);

        if (empty($categories)) {
            $mform->addElement(
                'static',
                $elementname . '_notice',
                $field->get_formatted_name(),
                get_string('nocategoriesavailable', 'customfield_multicategory'),
            );
            return;
        }

        // Autocomplete options.
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('selectcategories', 'customfield_multicategory'),
            'tags' => false,
        ];

        $mform->addElement('autocomplete', $elementname, $field->get_formatted_name(), $categories, $options);

        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Validates data for this field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function instance_form_validation(array $data, array $files): array {
        $errors = parent::instance_form_validation($data, $files);
        $elementname = $this->get_form_element_name();

        if ($this->get_field()->get_configdata_property('required')) {
            // Standard required rule doesn't work well on multi-select.
            if (empty($data[$elementname])) {
                $errors[$elementname] = get_string('err_required', 'form');
            }
        }

        return $errors;
    }

    /**
     * Prepares the form element before setting data.
     *
     * @param \stdClass $instance
     */
    public function instance_form_before_set_data(\stdClass $instance) {
        $elementname = $this->get_form_element_name();
        $value = $this->get_value();

        if (!empty($value)) {
            $categoryids = json_decode($value, true);
            if (is_array($categoryids)) {
                $instance->{$elementname} = $categoryids;
            }
        }
    }

    /**
     * Called after instance form data submission to convert array to JSON.
     *
     * @param \stdClass $datanew
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();

        if (isset($datanew->{$elementname})) {
            $value = $datanew->{$elementname};
            if (is_array($value)) {
                // Filter out empty values and convert to integers.
                $value = array_filter($value, function ($v) {
                    return $v !== '' && $v !== null;
                });
                $value = array_map('intval', $value);
                $datanew->{$elementname} = json_encode(array_values($value));
            } else if (empty($value)) {
                $datanew->{$elementname} = json_encode([]);
            }
        } else {
            $datanew->{$elementname} = json_encode([]);
        }

        parent::instance_form_save($datanew);
    }

    /**
     * Returns value in a human-readable format.
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = $this->get_value();

        if ($this->is_empty($value)) {
            return null;
        }

        $categoryids = json_decode($value, true);
        if (!is_array($categoryids) || empty($categoryids)) {
            return null;
        }

        $names = [];
        foreach ($categoryids as $catid) {
            $category = \core_course_category::get($catid, IGNORE_MISSING);
            if ($category) {
                $context = \context_coursecat::instance($category->id);
                $filtercontext = \context_helper::get_navigation_filter_context($context);
                $names[] = format_string($category->name, true, ['context' => $filtercontext]);
            }
        }

        return implode(', ', $names);
    }

    /**
     * Checks if the value is empty.
     *
     * @param mixed $value
     * @return bool
     */
    public function is_empty($value): bool {
        if (empty($value)) {
            return true;
        }

        $decoded = json_decode($value, true);
        return !is_array($decoded) || empty($decoded);
    }
}
