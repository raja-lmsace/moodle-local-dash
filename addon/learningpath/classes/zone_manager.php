<?php
namespace dashaddon_learningpath;

defined('MOODLE_INTERNAL') || die();

/**
 * Zone manager for handling zone configurations.
 */
class zone_manager {

    /** @var int Block instance ID */
    private $blockid;

    /**
     * Constructor.
     * @param int $blockid Block instance ID
     */
    public function __construct($blockid) {
        $this->blockid = $blockid;
    }

    /**
     * Get zone configuration for a specific SVG type.
     * @param string $svgtype SVG type (desktop, tablet, mobile)
     * @return array
     */
    public function get_zones($svgtype) {
        global $DB;
        return $DB->get_records('dashaddon_learningpath_zones', [
            'blockid' => $this->blockid,
            'svgtype' => $svgtype
        ], 'timecreated ASC');
    }


    /**
     * Save zones for a specific SVG type.
     * @param string $svgtype SVG type (desktop/tablet/mobile)
     * @param array $zones Array of zone data
     * @return bool Success
     */
    public function save_zones($svgtype, $zones) {
        global $DB;

            // Delete existing zones for this block and SVG type.
            $DB->delete_records('dashaddon_learningpath_zones', [
                'blockid' => $this->blockid,
                'svgtype' => $svgtype
            ]);
            // Insert new zone configurations.
            foreach ($zones as $zone) {
                $record = new \stdClass();
                $record->blockid = $this->blockid;
                $record->svgtype = $svgtype;
                $record->zoneid = $zone['zoneid'];
                $record->zonetype = $zone['type'];
                $record->enabled = $zone['enabled'] ? 1 : 0;
                $record->courseid = $zone['courseid'];
                $record->timecreated = time();
                $record->timemodified = time();
                $DB->insert_record('dashaddon_learningpath_zones', $record);
            }
    }

    /**
     * Update zone status.
     * @param string $svgtype SVG type
     * @param string $zoneid Zone ID
     * @param bool $enabled Enabled status
     */
    public function update_zone_status($svgtype, $zoneid, $enabled) {
        global $DB;

        $DB->set_field('dashaddon_learningpath_zones', 'enabled', $enabled ? 1 : 0, [
            'blockid' => $this->blockid,
            'svgtype' => $svgtype,
            'zoneid' => $zoneid
        ]);
    }

    /**
     * Assign course to zone.
     * @param string $svgtype SVG type
     * @param string $zoneid Zone ID
     * @param int $courseid Course ID (null to unassign)
     */
    public function assign_course_to_zone($svgtype, $zoneid, $courseid) {
        global $DB;

        $DB->set_field('dashaddon_learningpath_zones', 'courseid', $courseid, [
            'blockid' => $this->blockid,
            'svgtype' => $svgtype,
            'zoneid' => $zoneid
        ]);
    }

    /**
     * Get courses assigned to zones.
     * @param string $svgtype SVG type
     * @return array
     */
    public function get_zone_course_assignments($svgtype) {
        global $DB;

        $sql = "SELECT z.zoneid, z.courseid, c.fullname
                FROM {dashaddon_learningpath_zones} z
                LEFT JOIN {course} c ON c.id = z.courseid
                WHERE z.blockid = ? AND z.svgtype = ? AND z.courseid IS NOT NULL";

        return $DB->get_records_sql($sql, [$this->blockid, $svgtype]);
    }

    /**
     * Auto-assign courses to enabled zones.
     * @param string $svgtype SVG type
     * @param array $courseids Course IDs to assign
     */
    public function auto_assign_courses($svgtype, $courseids) {
        global $DB;

        // Get enabled zones without manual course assignments.
        $zones = $DB->get_records_select('dashaddon_learningpath_zones',
            'blockid = ? AND svgtype = ? AND enabled = 1 AND courseid IS NULL',
            [$this->blockid, $svgtype],
            'sortorder ASC'
        );

        $zonearray = array_values($zones);
        foreach ($courseids as $index => $courseid) {
            if (isset($zonearray[$index])) {
                $this->assign_course_to_zone($svgtype, $zonearray[$index]->zoneid, $courseid);
            }
        }
    }

    /**
     * Get zone positioning data for rendering.
     * @param string $svgtype SVG type
     * @return array
     */
    public function get_zone_positioning_data($svgtype) {
        global $DB;

        $sql = "SELECT z.*, c.fullname as coursename
                FROM {dashaddon_learningpath_zones} z
                LEFT JOIN {course} c ON c.id = z.courseid
                WHERE z.blockid = ? AND z.svgtype = ? AND z.enabled = 1
                ORDER BY z.sortorder ASC";

        return $DB->get_records_sql($sql, [$this->blockid, $svgtype]);
    }
}