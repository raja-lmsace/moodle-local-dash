<?php

namespace dashaddon_learningpath\output;

use dashaddon_learningpath\external\zone_config;
use dashaddon_learningpath\form\zone_config_form;
use dashaddon_learningpath\zone_manager;
use dashaddon_learningpath\svg_parser;
use context_block;
use context_system;


defined('MOODLE_INTERNAL') || die();

/**
 * Fragment output for zone configuration.
 */
class fragment {

    /**
     * Render zone configuration fragment.
     * @param array $args
     * @return string
     */
    public static function zone_config($args) {
        global $PAGE, $OUTPUT;

        $blockid = $args['blockid'];
        $context = context_block::instance($blockid);

        // Check if form is being submitted.
        if (!empty($args['submitbutton'])) {
            return self::process_zone_config_submission($args);
        }

        // Get zone configuration data.
        $data = self::get_zone_config($blockid);

        // Prepare custom data for form.
        $customdata = [
            'blockid' => $blockid,
            'svgs' => $data['svgs'],
            'courses' => $data['courses']
        ];

        // Create form.
        $form = new zone_config_form(null, $customdata);

        // Return form HTML.
        return \html_writer::tag('div', $form->render(), ['class' => 'zone-config-form']);
    }


    /**
     * Process form submission.
     *
     * @param array $args Form data
     * @return string JSON response
     */
    private static function process_zone_config_submission($args) {
        global $DB;

        try {
            $blockid = $args['blockid'];
            $context = context_block::instance($blockid);

            $formdata = [];
            parse_str($args['formdata'], $formdata);
            if (!$formdata) {
                return json_encode([
                    'success' => false,
                    'error' => 'No form data received'
                ]);
            }

            // Update the block config data.
            if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid))) {
                $config = unserialize_object(base64_decode($configdata));
                $config->preferences['positioning'] = 'zones';
                $configdata = base64_encode(serialize($config));
                $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $blockid));
            }

            // Initialize zone manager.
            $zonemanager = new zone_manager($blockid);

            // Group zones by SVG type.
            $svgtypes = ['desktop', 'tablet', 'mobile'];
            $saved_count = 0;
            $total_zones = 0;

            foreach ($svgtypes as $svgtype) {
                $zones = [];

                // Extract zones for this SVG type from form data.
                foreach ($formdata as $key => $value) {
                    $pattern = "/^zone_{$svgtype}_(.+)_zoneid$/";
                    if (preg_match($pattern, $key, $matches)) {
                        $zoneid_from_key = $matches[1]; // Extract the zone ID from the key.
                        $prefix = "zone_{$svgtype}_{$zoneid_from_key}";

                        // Get the actual zone ID from the form data.
                        $actual_zoneid = $formdata[$key];

                        // Get zone data.
                        $zone_data = [
                            'zoneid' => $actual_zoneid,
                            'type' => $formdata[$prefix . '_zonetype'] ?? '',
                            'enabled' => false,
                            'courseid' => null
                        ];

                        // Check if zone is enabled.
                        $enabled_field = $prefix . '_enabled';
                        if (isset($formdata[$enabled_field])) {
                            if (is_array($formdata[$enabled_field])) {
                                $zone_data['enabled'] = in_array("1", $formdata[$enabled_field]);
                            } else {
                                $zone_data['enabled'] = !empty($formdata[$enabled_field]);
                            }
                        }

                        // Get course assignment.
                        $courseid_field = $prefix . '_courseid';
                        if (isset($formdata[$courseid_field])) {
                            $courseid = $formdata[$courseid_field];
                            if ($courseid !== "0" && !empty($courseid)) {
                                $zone_data['courseid'] = (int)$courseid;
                            }
                        }

                        $zones[] = $zone_data;
                        $total_zones++;
                    }
                }
                // Save zones for this SVG type.
                if (!empty($zones)) {
                    $result = $zonemanager->save_zones($svgtype, $zones);
                    if ($result) {
                        $saved_count++;
                    }
                }
            }


            // Return success response
            return json_encode([
                'success' => true,
                'message' => get_string('zones_saved_successfully', 'block_dash'),
                'details' => [
                    'svg_types_processed' => $saved_count,
                    'total_zones' => $total_zones,
                    'zones_by_type' => [
                        'desktop' => self::count_zones_by_type($formdata, 'desktop'),
                        'tablet' => self::count_zones_by_type($formdata, 'tablet'),
                        'mobile' => self::count_zones_by_type($formdata, 'mobile')
                    ]
                ]
            ]);

        } catch (\Exception $e) {

            // Return error response.
            return json_encode([
                'success' => false,
                'error' => 'Error saving zone configuration: ' . $e->getMessage()
            ]);
        }
    }



    /**
     * Helper function to count zones by type
     * @param array $formdata Form data
     * @param string $svgtype SVG type
     * @return int Number of zones
     */
    private static function count_zones_by_type($formdata, $svgtype) {
        $count = 0;
        foreach ($formdata as $key => $value) {
            if (preg_match("/^zone_{$svgtype}_(.+)_zoneid$/", $key)) {
                $count++;
            }
        }
        return $count;
    }


    /**
     * Get zone configuration data.
     * @param int $blockid Block instance ID
     * @return array Zone configuration data
     */
    public static function get_zone_config($blockid) {
        global $DB;

        // Get block instance and validate context.
        $blockinstance = $DB->get_record('block_instances', ['id' => $blockid], '*', MUST_EXIST);
        $context = context_block::instance($blockid);

        // Get block configuration.
        $config = unserialize_object(base64_decode($blockinstance->configdata));
        $preferences = $config->preferences ?? [];

        $result = [
            'svgs' => [],
            'courses' => self::get_available_courses()
        ];

        $zonemanager = new \dashaddon_learningpath\zone_manager($blockid);

        // Process each SVG file.
        $svgtypes = [
            'desktop' => $preferences['desktoppath'] ?? null,
            'tablet' => $preferences['tabletpath'] ?? null,
            'mobile' => $preferences['mobilepath'] ?? null
        ];

        $svgcount = 0;
        foreach ($svgtypes as $type => $filename) {
            if ($filename && $filename !== '0') {
                $svgcontent = dashaddon_learningpath_get_filename_path($type . '_learningpath', $filename);

                if (!empty($svgcontent)) {
                    $parsedzones = svg_parser::parse_zones($svgcontent->get_content());
                    $savedzones = $zonemanager->get_zones($type);
                    $zones = self::merge_zone_data($parsedzones, $savedzones, $blockid);

                    $result['svgs'][] = [
                        'filename' => $filename,
                        'displayname' => ucfirst($type),
                        'svgtype' => $type,
                        'svgcontent' => svg_parser::add_zone_attributes($svgcontent->get_content()),
                        'zones' => $zones,
                        'first' => $svgcount === 0,
                        'tablet' => $type === 'tablet',
                        'last' => false,
                    ];
                    $svgcount++;
                }
            }
        }

        // Mark the last item.
        if (!empty($result['svgs'])) {
            $result['svgs'][count($result['svgs']) - 1]['last'] = true;
        }

        return $result;
    }


    /**
     * Get SVG content from file.
     * @param string $filename
     * @param string $filearea
     * @return string
     */
    private static function get_svg_content($filename, $filearea) {
        $files = dashaddon_learningpath_get_all_learning_paths($filearea);
        if (isset($files[$filename])) {
            return $files[$filename];
        }
        return '';
    }


    /**
     * Get available courses.
     * @return array
     */
    private static function get_available_courses() {
        global $DB;

        $courses = $DB->get_records_sql(
            "SELECT id, fullname FROM {course} WHERE id > 1 AND visible = 1 ORDER BY fullname"
        );

        $result = [];
        foreach ($courses as $course) {
            $result[] = [
                'id' => $course->id,
                'fullname' => format_string($course->fullname),
                'selected' => false
            ];
        }

        return $result;
    }

    /**
     * Merge parsed zones with saved zone data.
     * @param array $parsedzones
     * @param array $savedzones
     * @param int $blockid
     * @return array
     */
    private static function merge_zone_data($parsedzones, $savedzones, $blockid) {
        global $DB;
        $savedbyid = [];
        foreach ($savedzones as $saved) {
            $savedbyid[$saved->zoneid] = $saved;
        }

        $result = [];
        foreach ($parsedzones as $parsed) {
            $saved = isset($savedbyid[$parsed['id']]) ? $savedbyid[$parsed['id']] : null;
            $courseid = $DB->get_field('dashaddon_learningpath_zones', 'courseid', ['zoneid' => $parsed['id'], 'zonetype' => $parsed['type'], 'blockid' => $blockid]);
            $status = $DB->get_field('dashaddon_learningpath_zones', 'enabled', ['zoneid' => $parsed['id'], 'zonetype' => $parsed['type'], 'blockid' => $blockid]);
            $result[] = [
                'id' => $parsed['id'],
                'type' => $parsed['type'],
                'typename' => $parsed['typename'],
                'enabled' => $status ? (bool)$status : false,
                'courseid' => $courseid ?? 0,
                'position' => $parsed['position']
            ];
        }

        return $result;
    }

}