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
 * Condition for assignment tags - display only courses assigned to user with matching tags.
 *
 * @package    dashaddon_learningpath
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_learningpath\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\condition;
use moodleform;
use MoodleQuickForm;

/**
 * Condition for assignment tags - filters courses by assignment tags assigned to the user.
 */
class assignment_tags_condition extends condition {
    /**
     * @var string Filter label.
     */
    private $label;

    /**
     * Constructor.
     *
     * @param string $name Condition name/identifier.
     * @param string $select SQL select field.
     * @param string $label Condition label to display.
     */
    public function __construct($name, $select, $label = '') {
        $this->label = $label;
        parent::__construct($name, $select, $label);
    }

    /**
     * Get filter config label.
     *
     * @return string
     */
    public function get_label() {
        if (!empty($this->label)) {
            return $this->label;
        }
        return get_string('assignmenttags', 'block_dash');
    }

    /**
     * Get values from filter based on user selection.
     *
     * @return array|null
     */
    public function get_values() {
        if (isset($this->get_preferences()['tags']) && !empty($this->get_preferences()['tags'])) {
            return $this->get_preferences()['tags'];
        }
        return null;
    }

    /**
     * Return a list of operations this condition supports.
     *
     * @return array
     */
    public function get_supported_operations() {
        return [
            self::OPERATION_IN_OR_EQUAL,
        ];
    }

    /**
     * Get course IDs that match the assignment tags for the current user.
     *
     * @return array Array of course IDs
     */
    public function get_course_ids_from_tags() {
        global $DB, $CFG;

        $values = $this->get_values();
        if (!empty($values) && !is_array($values)) {
            $values = [$values];
        }

        $userid = $this->get_user_id();
        $params = ['userid' => $userid];
        $tagconditions = [];

        if (!empty($values)) {
            $i = 0;
            foreach ($values as $tag) {
                $tag = trim($tag);
                if ($tag === '' || $tag === null) {
                    continue;
                }

                $paramname = 'tag_' . $i;
                if ($CFG->dbtype === 'pgsql') {
                    $tagconditions[] = $DB->sql_like('CAST(tags AS TEXT)', ':' . $paramname, false, false);
                } else {
                    $tagconditions[] = $DB->sql_like('tags', ':' . $paramname, false, false);
                }
                $params[$paramname] = '%"' . $DB->sql_like_escape($tag) . '"%';
                $i++;
            }
        }

        $tagsql = '';
        if (!empty($tagconditions)) {
            $tagsql = ' AND (' . implode(' OR ', $tagconditions) . ')';
        }

        $sql = "SELECT DISTINCT courseid
                FROM {tool_timetable_course_overrides}
                WHERE userid = :userid" . $tagsql;

        $courseids = $DB->get_fieldset_sql($sql, $params);

        return $courseids ? $courseids : [];
    }

    /**
     * Get SQL for where clause.
     * Filters courses that have been assigned to the user with matching assignment tags.
     *
     * @return array Array containing SQL and parameters.
     */
    public function get_sql_and_params() {
        global $DB;

        $courseids = $this->get_course_ids_from_tags();

        if (!empty($courseids)) {
            [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid_' . $this->get_name() . '_');
            $sql = $this->get_select() . ' ' . $insql;

            return [$sql, $params];
        }

        return false;
    }

    /**
     * Get the user ID for filtering.
     * Tries to get from widget context, otherwise uses current user.
     *
     * @return int User ID
     */
    protected function get_user_id() {
        global $USER, $PAGE;

        if ($PAGE->pagelayout == 'mypublic') {
            $userid = optional_param('id', 0, PARAM_INT);
            if ($userid) {
                return $userid;
            }
        }

        return $USER->id;
    }

    /**
     * Add form fields for this condition.
     *
     * @param moodleform $moodleform
     * @param MoodleQuickForm $mform
     * @param string $fieldnameformat
     */
    public function build_settings_form_fields(
        moodleform $moodleform,
        MoodleQuickForm $mform,
        $fieldnameformat = 'filters[%s]'
    ): void {
        global $DB;

        $manager = \core_plugin_manager::instance();
        $plugin = $manager->get_plugin_info('tool_timetable');
        if (!$plugin || $plugin->get_status() === \core_plugin_manager::PLUGIN_STATUS_MISSING) {
            return;
        }

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat);

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        // Get all unique tags from the assignments table.
        $alltags = $DB->get_fieldset_select(
            'tool_timetable_course_overrides',
            'tags',
            'tags IS NOT NULL AND tags <> \'\'',
            []
        );

        $taglist = [];
        foreach ($alltags as $tagjson) {
            if (empty($tagjson)) {
                continue;
            }
            $tags = json_decode($tagjson, true);
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    if (!empty($tag)) {
                        $taglist[$tag] = $tag;
                    }
                }
            }
        }

        // Sort tags alphabetically.
        asort($taglist);

        $mform->addElement('autocomplete', $fieldname . '[tags]', $this->get_label(), $taglist, [
            'multiple' => true,
        ]);
        $mform->addHelpButton($fieldname . '[tags]', 'assignmenttags', 'dashaddon_learningpath');
        $mform->hideIf($fieldname . '[tags]', $fieldname . '[enabled]');
    }
}
