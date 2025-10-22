<?php
namespace dashaddon_learningpath;

defined('MOODLE_INTERNAL') || die();

/**
 * SVG parser for extracting zones.
 */
class svg_parser {

    /**
     * Parse SVG content and extract zones.
     * @param string $svgcontent SVG content
     * @return array Array of zones
     */
    public static function parse_zones($svgcontent) {
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

        // Counter for each element type to ensure consistent IDs
        $elementcounters = [];

        foreach ($supportedelements as $elementtype) {

            // Initialize counter for this element type
            if (!isset($elementcounters[$elementtype])) {
                $elementcounters[$elementtype] = 0;
            }

            $elements = $dom->getElementsByTagName($elementtype);

            foreach ($elements as $element) {
                $id = $element->getAttribute('id');
                if (empty($id)) {
                    $elementcounters[$elementtype]++;
                    $id = 'zone_' . $elementtype . '_' . $elementcounters[$elementtype];
                    $element->setAttribute('id', $id);
                }

                $zones[] = [
                    'id' => $id,
                    'type' => $elementtype,
                    'typename' => self::get_type_display_name($elementtype),
                    'attributes' => self::get_element_attributes($element),
                    'position' => self::calculate_center_position($element, $elementtype)
                ];
            }
        }

        return $zones;
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
                // For groups, try to find a representative position
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
            'y' => $count > 0 ? $y / $count : 0
        ];
    }

    /**
     * Calculate bounding box for group element.
     * @param \DOMElement $element
     * @return array
     */
    private static function calculate_group_bbox($element) {
        // Simplified bbox calculation - in real implementation,
        // you'd need to traverse all child elements
        return [
            'x' => 0,
            'y' => 0,
            'width' => 100,
            'height' => 100
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
                $element->setAttribute('class',
                    trim($element->getAttribute('class') . ' learningpath-zone'));
            }
        }

        return $dom->saveXML();
    }
}