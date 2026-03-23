<?php
// This file is part of The Bootstrap Moodle theme
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
 * Filter items based on tags in a certain component and itemtype.
 *
 * @package    dashaddon_programs
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_program\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Filter items based on tags in a certain component and itemtype.
 *
 * @package dashaddon_programs
 */
class tags_program_filter extends select_filter {
    /**
     * Current component.
     *
     * @var string
     */
    private $component;

    /**
     * Type of item.
     *
     * @var string
     */
    private $itemtype;

    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {

        $tags = $this->get_used_tags();
        foreach ($tags as $tag) {
            $this->add_option($tag, $tag);
        }

        parent::init();
    }

    /**
     * Returns list of all tags of programs that user may see or is allocated to.
     *
     * NOTE: not used anywhere, this was intended for tag filtering UI
     *
     * @return array [tagid => tagname]
     */
    public function get_used_tags(): array {
        global $USER, $DB, $CFG;

        $userid = $USER->id;

        $sql = "SELECT DISTINCT t.id, t.name
                FROM {tag} t
                JOIN {tag_instance} tt ON tt.itemtype = 'program' AND tt.tagid = t.id AND tt.component = 'enrol_programs'
                JOIN {enrol_programs_programs} p ON p.id = tt.itemid
                LEFT JOIN {enrol_programs_cohorts} pc ON pc.programid = p.id
                WHERE pc.cohortid IS NULL OR EXISTS (
                    SELECT cm.id
                    FROM {cohort_members} cm
                    JOIN {enrol_programs_cohorts} pc ON pc.cohortid = cm.cohortid
                    WHERE cm.userid = :userid2 AND pc.programid = p.id
                )
                ORDER BY t.name ASC";

        $params = ['userid1' => $userid, 'userid2' => $userid];

        $menu = $DB->get_records_sql_menu($sql, $params);

        return array_map('format_string', $menu);
    }

    /**
     * Condition construtor.
     * @param string $name
     * @param string $select
     * @param string $component
     * @param string $itemtype
     * @param string $label
     */
    public function __construct($name, $select, $component, $itemtype, $label = '') {
        $this->component = $component;
        $this->itemtype = $itemtype;

        parent::__construct($name, $select, $label);
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        [$sql, $values] = parent::get_sql_and_params();

        if (!empty($values)) {
            $collectionid = \core_tag_area::get_collection($this->component, $this->itemtype);

            array_walk($values, function (&$value) {
                $value = \core_text::strtolower($value);
            });

            [$taginsql, $taginparams] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED, 'tgp');
            $sql = "epp.id IN (SELECT ti.itemid
                    FROM {tag_instance} ti
                    JOIN {tag} t ON ti.tagid = t.id
                    WHERE t.name $taginsql
                    AND tagcollid = :tagcolid)";

            $taginparams['tagcolid'] = $collectionid;

            return [$sql, $taginparams];
        }
    }
}
