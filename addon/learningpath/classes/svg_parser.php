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
 * SVG parser for extracting zones.
 *
 * @package    dashaddon_learningpath
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath;

/**
 * SVG parser for extracting zones.
 */
class svg_parser {
    /**
     * Parse SVG content and extract zones in order.
     * @param string $svgcontent SVG content
     * @param string $type SVG type
     * @return array Array of zones in the order they appear in SVG
     */
    public static function parse_zones($svgcontent, $type) {
        $zones = [];
        $supportedelements = self::get_supported_elements();

        if (empty($svgcontent)) {
            return $zones;
        }

        // Load SVG into DOMDocument for better parsing.
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($svgcontent);
        libxml_clear_errors();

        // Get the SVG root element.
        $svgelement = $dom->documentElement;

        // Counter for each element type.
        $typecounters = [];

        // Initialize counters for all supported types.
        foreach ($supportedelements as $elementtype) {
            $typecounters[$elementtype] = 0;
        }

        // Traverse all child nodes in document order.
        $allnodes = self::get_all_nodes_in_order($svgelement);

        foreach ($allnodes as $element) {
            // Check if this element is a supported zone type.
            $elementtype = strtolower($element->nodeName);

            if (!in_array($elementtype, $supportedelements)) {
                continue;
            }

            // Get the index for this element type.
            $zoneindex = $typecounters[$elementtype];

            // Generate zone ID.
            $zoneid = 'zone_' . $type . '_' . $elementtype . '_' . $zoneindex;

            // Get existing ID or use generated one.
            $existingid = $element->getAttribute('id');
            if (!empty($existingid)) {
                $zoneid = $existingid;
            }

            $zones[] = [
                'id' => $zoneid,
                'type' => $elementtype,
                'zoneindex' => $zoneindex,
                'typename' => self::get_type_display_name($elementtype),
                'attributes' => self::get_element_attributes($element),
                'position' => self::calculate_center_position($element, $elementtype),
            ];

            // Increment counter for this type.
            $typecounters[$elementtype]++;
        }

        return $zones;
    }


    /**
     * Get all nodes in document order (depth-first traversal).
     * @param \DOMElement $element
     * @return array Array of DOMElements
     */
    private static function get_all_nodes_in_order($element) {
        $nodes = [];

        // Add current element if it's an element node.
        if ($element->nodeType === XML_ELEMENT_NODE) {
            $nodes[] = $element;
        }

        // Traverse children.
        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $childnodes = self::get_all_nodes_in_order($child);
                    $nodes = array_merge($nodes, $childnodes);
                }
            }
        }

        return $nodes;
    }

    /**
     * Get supported zone elements from config.
     * @return array
     */
    public static function get_supported_elements() {
        $config = get_config('dashaddon_learningpath', 'supported_zone_elements');
        if (empty($config)) {
            $config = 'circle,rect,polygon,ellipse,g';
        }
        return array_map('trim', explode(',', $config));
    }

    /**
     * Get display name for element type.
     * @param string $type
     * @return string
     */
    private static function get_type_display_name($type) {
        $names = [
            'circle' => get_string('zone_type_circle', 'block_dash'),
            'rect' => get_string('zone_type_rectangle', 'block_dash'),
            'polygon' => get_string('zone_type_polygon', 'block_dash'),
            'ellipse' => get_string('zone_type_ellipse', 'block_dash'),
            'g' => get_string('zone_type_group', 'block_dash'),
        ];
        return isset($names[$type]) ? $names[$type] : ucfirst($type);
    }

    /**
     * Get element attributes.
     * @param \DOMElement $element
     * @return array
     */
    private static function get_element_attributes($element) {
        $attributes = [];
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $attributes[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $attributes;
    }

    /**
     * Calculate center position of element.
     * @param \DOMElement $element
     * @param string $type
     * @return array
     */
    private static function calculate_center_position($element, $type) {
        $position = ['x' => 0, 'y' => 0];

        switch ($type) {
            case 'circle':
                $position['x'] = (float)$element->getAttribute('cx');
                $position['y'] = (float)$element->getAttribute('cy');
                break;
            case 'rect':
                $x = (float)$element->getAttribute('x');
                $y = (float)$element->getAttribute('y');
                $width = (float)$element->getAttribute('width');
                $height = (float)$element->getAttribute('height');
                $position['x'] = $x + ($width / 2);
                $position['y'] = $y + ($height / 2);
                break;
            case 'ellipse':
                $position['x'] = (float)$element->getAttribute('cx');
                $position['y'] = (float)$element->getAttribute('cy');
                break;
            case 'polygon':
                $points = $element->getAttribute('points');
                $position = self::calculate_polygon_center($points);
                break;
            case 'g':
                // For groups, try to find a representative position.
                $bbox = self::calculate_group_bbox($element);
                $position['x'] = $bbox['x'] + ($bbox['width'] / 2);
                $position['y'] = $bbox['y'] + ($bbox['height'] / 2);
                break;
        }

        return $position;
    }

    /**
     * Calculate polygon center from points.
     * @param string $points
     * @return array
     */
    private static function calculate_polygon_center($points) {
        $coords = preg_split('/[\s,]+/', trim($points));
        $x = 0;
        $y = 0;
        $count = 0;

        for ($i = 0; $i < count($coords); $i += 2) {
            if (isset($coords[$i + 1])) {
                $x += (float)$coords[$i];
                $y += (float)$coords[$i + 1];
                $count++;
            }
        }

        return [
            'x' => $count > 0 ? $x / $count : 0,
            'y' => $count > 0 ? $y / $count : 0,
        ];
    }

    /**
     * Calculate bounding box for group element.
     * @param \DOMElement $element
     * @return array
     */
    private static function calculate_group_bbox($element) {
        // Simplified bbox calculation - in real implementation.
        // You'd need to traverse all child elements.
        return [
            'x' => 0,
            'y' => 0,
            'width' => 100,
            'height' => 100,
        ];
    }

    /**
     * Add data attributes to SVG zones for highlighting.
     * @param string $svgcontent
     * @return string
     */
    public static function add_zone_attributes($svgcontent) {
        $supportedelements = self::get_supported_elements();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($svgcontent);
        libxml_clear_errors();

        foreach ($supportedelements as $elementtype) {
            $elements = $dom->getElementsByTagName($elementtype);

            foreach ($elements as $element) {
                $element->setAttribute('data-zone-type', $elementtype);
                $element->setAttribute(
                    'class',
                    trim($element->getAttribute('class') . ' learningpath-zone')
                );
            }
        }

        return $dom->saveXML();
    }
}
