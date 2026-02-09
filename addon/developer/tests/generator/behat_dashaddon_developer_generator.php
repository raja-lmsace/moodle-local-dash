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
 * Dashaddon developer behat data generator.
 *
 * @package   dashaddon_developer
 * @copyright 2025, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Dashaddon developer behat data generator.
 */
class behat_dashaddon_developer_generator extends behat_generator_base {
    /**
     * Map of short attribute names to full class names.
     */
    private const ATTRIBUTE_MAP = [
        'bool' => 'block_dash\local\data_grid\field\attribute\bool_attribute',
        'date' => 'block_dash\local\data_grid\field\attribute\date_attribute',
        'identifier' => 'block_dash\local\data_grid\field\attribute\identifier_attribute',
        'image' => 'block_dash\local\data_grid\field\attribute\image_attribute',
        'link' => 'block_dash\local\data_grid\field\attribute\link_attribute',
        'linked_data' => 'block_dash\local\data_grid\field\attribute\linked_data_attribute',
        'moodle_url' => 'block_dash\local\data_grid\field\attribute\moodle_url_attribute',
        'percent' => 'block_dash\local\data_grid\field\attribute\percent_attribute',
        'time' => 'block_dash\local\data_grid\field\attribute\time_attribute',
        'user_image_url' => 'block_dash\local\data_grid\field\attribute\user_image_url_attribute',
        'course_image_url' => 'local_dash\data_grid\field\attribute\course_image_url_attribute',
        'timeago' => 'local_dash\data_grid\field\attribute\timeago_attribute',
    ];

    /**
     * Get a list of the entities that can be created.
     *
     * @return array entity name => information about how to generate.
     */
    protected function get_creatable_entities(): array {
        return [
            'custom data sources' => [
                'singular' => 'custom data source',
                'datagenerator' => 'custom_data_source',
                'required' => ['name', 'idnumber', 'maintable'],
                'switchids' => [],
            ],
        ];
    }

    /**
     * Preprocess custom data source data before creation.
     *
     * Resolves short attribute names in fieldattribute to full class names.
     *
     * @param array $data
     * @return array
     */
    protected function preprocess_custom_data_source(array $data): array {
        if (!empty($data['fieldattribute'])) {
            $attrs = json_decode($data['fieldattribute'], true);
            if (is_array($attrs)) {
                $attrs = array_map(function ($fieldattrs) {
                    if (!is_array($fieldattrs)) {
                        return $fieldattrs;
                    }
                    return array_map(function ($attr) {
                        return $this->resolve_attribute_name($attr);
                    }, $fieldattrs);
                }, $attrs);
                $data['fieldattribute'] = json_encode($attrs);
            }
        }
        return $data;
    }

    /**
     * Resolve a short attribute name to its full class name.
     *
     * @param string|null $name Short name or full class name.
     * @return string|null The full class name, or the input unchanged.
     */
    protected function resolve_attribute_name($name) {
        if (empty($name) || $name === null) {
            return $name;
        }

        // Already a full class name.
        if (strpos($name, '\\') !== false) {
            return $name;
        }

        return self::ATTRIBUTE_MAP[$name] ?? $name;
    }
}
