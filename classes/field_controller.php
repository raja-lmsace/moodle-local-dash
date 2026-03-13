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

namespace customfield_multicategory;
/**
 * Field controller for multi-category custom field.
 *
 * @package   customfield_multicategory
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type.
     */
    const TYPE = 'multicategory';

    /**
     * Add fields for editing a multicategory field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_multicategory'));
        $mform->setExpanded('header_specificsettings', true);

        // Parent category selection - if selected, only child categories will be available.
        $categories = $this->get_all_categories_for_config();

        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('allcategories', 'customfield_multicategory'),
            'tags' => false,
        ];

        $mform->addElement(
            'autocomplete',
            'configdata[parentcategories]',
            get_string('parentcategories', 'customfield_multicategory'),
            $categories,
            $options
        );
        $mform->addHelpButton('configdata[parentcategories]', 'parentcategories', 'customfield_multicategory');
    }

    /**
     * Get all categories available for field configuration.
     *
     * @return array
     */
    protected function get_all_categories_for_config(): array {
        return \core_course_category::make_categories_list();
    }

    /**
     * Get categories available for selection on the course form.
     *
     * This respects the parent category configuration and user permissions.
     *
     * @param int|null $courseid The course ID if editing, null if creating.
     * @return array
     */
    public function get_available_categories(?int $courseid = null): array {
        $parentcategories = $this->get_configdata_property('parentcategories');
        $allcategories = [];

        if (!empty($parentcategories)) {
            // Get only children of selected parent categories.
            foreach ($parentcategories as $parentid) {
                if ($parentid <= 0) {
                    continue;
                }
                $parent = \core_course_category::get($parentid, IGNORE_MISSING);
                if ($parent) {
                    $children = $this->get_category_children_recursive($parent);
                    $allcategories = $allcategories + $children;
                }
            }
        } else {
            // No parent restriction, get all categories.
            $allcategories = \core_course_category::make_categories_list();
        }

        // Filter by user permissions.
        $filteredcategories = [];
        foreach ($allcategories as $catid => $catname) {
            if ($this->user_can_select_category($catid, $courseid)) {
                $filteredcategories[$catid] = $catname;
            }
        }

        return $filteredcategories;
    }

    /**
     * Recursively get all children of a category.
     *
     * @param \core_course_category $category
     * @return array
     */
    protected function get_category_children_recursive(\core_course_category $category): array {
        $result = [];

        if ($category->id <= 0) {
            return $result;
        }

        $context = $category->get_context();
        $filtercontext = \context_helper::get_navigation_filter_context($context);

        // Add the parent category itself.
        $result[$category->id] = format_string($category->name, true, ['context' => $filtercontext]);
        $children = $category->get_children();
        foreach ($children as $child) {
            $result = $result + $this->get_category_children_recursive($child);
        }

        return $result;
    }

    /**
     * Check if the current user can select a category.
     *
     * @param int $categoryid The category ID.
     * @param int|null $courseid The course ID if editing, null if creating.
     * @return bool
     */
    public function user_can_select_category(int $categoryid, ?int $courseid = null): bool {
        if ($categoryid <= 0) {
            return false;
        }

        $category = \core_course_category::get($categoryid, IGNORE_MISSING);
        if (!$category) {
            return false;
        }

        // Check if user can view the category.
        if (!$category->is_uservisible()) {
            return false;
        }

        $context = $category->get_context();

        // Check if user can create courses in this category.
        if (has_capability('moodle/course:create', $context)) {
            return true;
        }

        // Check if user has the manage capability in the category context.
        if (has_capability('customfield/multicategory:manage', $context)) {
            return true;
        }

        // Check if user has the manage capability at system level.
        if (has_capability('customfield/multicategory:manage', \context_system::instance())) {
            return true;
        }

        return false;
    }

    /**
     * Validate the data from the config form.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function config_form_validation(array $data, $files = []): array {
        $errors = [];
        if (!empty($data['configdata']['uniquevalues'])) {
            $errors['configdata[uniquevalues]'] = get_string('erroruniqueincompatible', 'customfield_multicategory');
        }

        return $errors;
    }

    /**
     * Does this custom field type support being used as part of the block_myoverview
     * custom field grouping?
     *
     * @return bool
     */
    public function supports_course_grouping(): bool {
        return true;
    }

    /**
     * If this field supports course grouping, then this function needs overriding to
     * return the formatted values for this.
     *
     * @param array $values the used values that need formatting
     * @return array
     */
    public function course_grouping_format_values($values): array {
        $ret = [];
        foreach ($values as $value) {
            // Value might be JSON encoded array or single value.
            $categoryids = json_decode($value, true);
            if (!is_array($categoryids)) {
                $categoryids = [$value];
            }

            foreach ($categoryids as $catid) {
                if (!isset($ret[$catid])) {
                    $category = \core_course_category::get($catid, IGNORE_MISSING);
                    if ($category) {
                        $ret[$catid] = format_string($category->name);
                    }
                }
            }
        }

        $ret[BLOCK_MYOVERVIEW_CUSTOMFIELD_EMPTY] = get_string('nocustomvalue', 'block_myoverview', $this->get_formatted_name());
        return $ret;
    }

    /**
     * Parse value from import or webservice.
     *
     * @param string $value Comma-separated category names or IDs.
     * @return string JSON encoded array of category IDs.
     */
    public function parse_value(string $value) {
        global $DB;

        if (empty($value)) {
            return json_encode([]);
        }

        $names = array_map('trim', explode(',', $value));
        $categoryids = [];

        foreach ($names as $name) {
            if (is_numeric($name)) {
                $category = \core_course_category::get((int)$name, IGNORE_MISSING);
                if ($category) {
                    $categoryids[] = (int)$name;
                    continue;
                }
            }

            // Try to find by name.
            $records = $DB->get_records('course_categories', ['name' => $name]);
            if ($records) {
                $record = reset($records);
                $categoryids[] = (int)$record->id;
            }
        }

        return json_encode(array_unique($categoryids));
    }
}
