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
 * Modal class for dashboard report.
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\model;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/dash/lib.php');
require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");

/**
 * Dashboard class.
 */
class dashboard extends persistent {
    /**
     * Dashboard modal db tablename.
     */
    const TABLE = 'dashaddon_dashboard_dash';

    /**
     * Check user Loggedin.
     */
    const PERMISSION_LOGGEDIN = 'loggedin';

    /**
     * Check user has cohort permission.
     */
    const PERMISSION_COHORT = 'cohort';

    /**
     * Check user has cohort permission.
     */
    const PERMISSION_ROLE = 'role';

    /**
     * case modal has public access.
     */
    const PERMISSION_PUBLIC = 'public';

    /**
     * Defined properties modal contains.
     *
     * @return array
     */
    protected static function define_properties() {
        $props = [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'contextid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'contexttype' => [
                'type' => PARAM_TEXT,
            ],
            'categoryid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'permission' => [
                'type' => PARAM_TEXT,
            ],
            'cohort_id' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'roles' => [
                'type' => PARAM_RAW,
            ],
            'rolecontext' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'shortname' => [
                'type' => PARAM_ALPHAEXT,
                'message' => new \lang_string('invalidshortname', 'block_dash'),
            ],
            'secondarynav' => [
                'type' => PARAM_INT,
            ],
            'description' => [
                'type' => PARAM_CLEANHTML,
                'default' => '',
            ],
            'descriptionformat' => [
                'choices' => [FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN],
                'type' => PARAM_INT,
                'default' => FORMAT_HTML,
            ],
            'dashicon' => [
                'type' => PARAM_TEXT,
            ],
            'dashthumbnailimage' => [
                'type' => PARAM_INT,
            ],
            'dashbgimage' => [
                'type' => PARAM_INT,
            ],
            'includedblocks' => [
                'type' => PARAM_RAW,
            ],
            'displaydashboardtitle' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'displaycta' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'ctalink' => [
                'type' => PARAM_TEXT,
                'default' => 'enrolment',
            ],
            'ctacampaignid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'ctacustomurl' => [
                'type' => PARAM_URL,
                'default' => '',
            ],
            'ctacustomurltext' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'redirecttodashboard' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
        return $props;
    }

    /**
     * Clears the hook cache and optionally recreates it.
     *
     * This function clears the cache for hook callbacks and optionally recreates it
     * if the specified conditions are met. If the `$create` parameter is set to `false`,
     * it checks if the `dashaddon_dashboard_dash` table exists and if there is a record
     * with `redirecttodashboard` set to `true` and `permission` set to `public`. If these
     * conditions are not met, the function returns `false`.
     *
     * @param bool $create Optional. Whether to recreate the cache after clearing it. Default is `false`.
     * @return bool Returns `false` if the cache is not recreated, otherwise no return value.
     */
    public function clear_hook_cache($create = false) {
        $hookmanager = new \dashaddon_dashboard\local\hooks\helper();
        return $hookmanager->clear_hook_cache($create);
    }

    /**
     * Summary of get_context_instance.
     * @return \context
     */
    public function get_context_instance() {
        if ($this->get('contexttype') == 'course') {
            return \context_course::instance($this->get('courseid'));
        } else if ($this->get('contexttype') == 'category') {
            return \context_coursecat::instance($this->get('categoryid'));
        } else {
            return \context_system::instance();
        }
    }

    /**
     * Check if user can view dashboard.
     *
     * @param \stdClass $user
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function has_access(\stdClass $user) {
        global $CFG, $DB;

        $contexttype = $this->get('contexttype');
        $context = $this->get_context_instance();

        require_once("$CFG->dirroot/cohort/lib.php");

        $course = null;
        $coursecategory = null;
        if ($coursecontext = $context->get_course_context(false)) {
            $course = $DB->get_record('course', ['id' => $coursecontext->instanceid]);
        }

        if (has_capability('local/dash:managedashboards', $context)) {
            return true;
        }

        switch ($this->get('permission')) {
            case self::PERMISSION_LOGGEDIN:
                try {
                    if ($course) {
                        return is_enrolled(\context_course::instance($course->id), $user);
                    } else {
                        require_login(null, false, null, false, true);
                    }
                } catch (\require_login_exception $e) {
                    return false;
                }

                return true;
            case self::PERMISSION_COHORT:
                return cohort_is_member($this->get('cohort_id'), $user->id);
            case self::PERMISSION_ROLE:
                $roles = json_decode($this->get('roles'));
                // Roles not mentioned then stop the role check.
                if ($roles == '' || empty($roles)) {
                    return false;
                }

                // Verify the default user role is set to view the dashboard.
                $defaultuserroleid = isset($CFG->defaultuserroleid) ? $CFG->defaultuserroleid : 0;
                if ($defaultuserroleid && in_array($defaultuserroleid, $roles) && !empty($user->id) && !isguestuser($user->id)) {
                    return true;
                }

                // Verify the guest user have view the dashboard.
                if (isguestuser()) {
                    $guestroles = get_archetype_roles('guest');
                    $guestroleid = array_column($guestroles, 'id');
                    if (array_intersect($guestroleid, $roles)) {
                        return true;
                    }
                }

                [$insql, $inparam] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'role');

                $contextsql = ($this->get('rolecontext') == SYSTEMCONTEXT) ? ' AND contextid=:systemcontext ' : '';

                $sql = "SELECT u.* FROM {user} u WHERE u.id=:userid AND u.id IN
                        (SELECT userid FROM {role_assignments} WHERE roleid $insql AND userid=:rluserid $contextsql)";

                $params = [
                    'userid' => $user->id,
                    'rluserid' => $user->id,
                    'systemcontext' => \context_system::instance()->id,
                ];
                $mainparms = array_merge($params, $inparam);

                $records = $DB->get_records_sql($sql, $mainparms);

                // Records found user will have access otherwise restrict the user to view the dashboard.
                return count($records) > 0 ? true : false;
            case self::PERMISSION_PUBLIC:
                // No permission check.
                return true;
            default:
                return false;
        }
    }

    /**
     * Processes the onboard navigation for the dashboard.
     *
     * This function retrieves the included blocks, sorts them based on their positions,
     * and generates the navigation menu for the dashboard. It also handles the display
     * settings for the dashboard title and call-to-action button.
     *
     * @return string The rendered HTML for the onboard navigation.
     */
    public function process_onboard_navigation() {
        global $OUTPUT, $PAGE;
        $inculdeblocks = !empty($this->get('includedblocks')) ? json_decode($this->get('includedblocks')) : [];
        if (empty($inculdeblocks)) {
            return '';
        }
        $blocksoptions = \dashaddon_dashboard\helper::get_dashaddondash_pageblocks($this->get('shortname'));
        // Create position map from $blocksoptions.
        $positionmap = array_flip(array_keys($blocksoptions));

        // Sort $includeblocks based on positions in $blocksoptions.
        usort($inculdeblocks, function ($a, $b) use ($positionmap) {
            return ($positionmap[$a] ?? PHP_INT_MAX) - ($positionmap[$b] ?? PHP_INT_MAX);
        });
        $blocknamelist = [];
        $template = [];
        $nodes = [];
        $i = 1;
        foreach ($inculdeblocks as $blockid) {
            $blockinfo = block_instance_by_id($blockid);
            if ($blockinfo) {
                $newstrblockname = get_string('pluginname', 'block_' . $blockinfo->instance->blockname);
                $blocktitle = !empty($blockinfo->title) ? $blockinfo->title : $newstrblockname;
                $list['blockname'] = $blocktitle;
                $list['blockid'] = $blockid;
                $blocknamelist[] = $list;
                $itemdata = new \stdClass();
                $itemdata->id = $i;
                $itemdata->title = $blocktitle;
                $itemdata->sortorder = $i;
                $itemdata->url = $PAGE->url->out(false) . "#inst" . $blockid;
                $node['itemdata'] = $itemdata;
                $node['url'] = $PAGE->url->out(false) . "#inst" . $blockid;
                $node['key'] = 'block-' . $i;
                $node['text'] = $blocktitle;
                if ($i < 6) {
                    $node['forceintomoremenu'] = false;
                } else {
                    $node['forceintomoremenu'] = true;
                }
                $i++;
                $nodes[] = $node;
            }
        }
        $moremenu = new \core\navigation\output\more_menu((object) $nodes, 'navbar-nav', false);
        $template['moremenubar'] = $moremenu->export_for_template($PAGE->get_renderer('core'));
        $template['dashboardname'] = $this->get('name');
        $template['blocknamelist'] = $blocknamelist;
        $template['extraclasses'] = count($inculdeblocks) < 6 ? 'nav-menu' : '';
        $showtitleclass = '';
        if (!$this->get('displaydashboardtitle')) {
            $showtitleclass = 'hide-title';
        } else if ($this->get('displaydashboardtitle') == 2) {
            $showtitleclass = 'show-sticky-title';
        }
        $template['showtitleclass'] = $showtitleclass;
        $showbuttonclass = '';
        if (!$this->get('displaycta')) {
            $showbuttonclass = 'hide-button';
        } else if ($this->get('displaycta') == 2) {
            $showbuttonclass = 'show-sticky-button';
        }
        $template['showctaclass'] = $showbuttonclass;
        [$ctatext, $ctaurl] = $this->process_call_action();
        $template['ctatext'] = $ctatext;
        $template['ctaurl'] = $ctaurl;
        $template['hidebuttontitle'] = !$this->get('displaydashboardtitle') && !$this->get('displaycta');
        return $OUTPUT->render_from_template('dashaddon_dashboard/dashonpagenavigation', $template);
    }

    /**
     * Processes the call to action based on the 'ctalink' value and returns the corresponding text and URL.
     *
     * @return array An array containing the text and URL for the call to action.
     */
    public function process_call_action() {
        global $DB, $PAGE;
        $ctalink = $this->get('ctalink');
        if ($ctalink == 'enrolment') {
            $courseid = $this->get('courseid');
            $text = get_string('strctaenrolment', 'block_dash');
            $url = new \moodle_url('/enrol/index.php', ['id' => $courseid]);
        } else if ($ctalink == 'campaign') {
            $text = get_string('strctacampaign', 'block_dash');
            $campaignid = $this->get('ctacampaignid');
            if ($campaignid) {
                $campaign = $DB->get_record('auth_magic_campaigns', ['id' => $campaignid]);
                $url = new \moodle_url('/auth/magic/campaigns/view.php', ['code' => $campaign->code]);
            } else {
                $url = new \moodle_url('/my');
            }
        } else if ($ctalink == 'shopurl') {
            $text = get_string('strshopurl', 'block_dash');
            $shopurlfield = get_config('local_dash', 'courseshopurl');
            $url = $DB->get_field(
                'customfield_data',
                'value',
                ['instanceid' => $this->get('courseid'), 'fieldid' => $shopurlfield]
            );
            $url = empty($url) ? new \moodle_url('/course/view.php', ['id' => $this->get('courseid')]) : $url;
        } else if ($ctalink == 'custom') {
            $text = !empty($this->get('ctacustomurltext')) ? $this->
                get('ctacustomurltext') : get_string('strcustomurl', 'block_dash');
            $url = !empty($this->get('ctacustomurl')) ? $this->get('ctacustomurl') : $PAGE->url->out(false);
        }
        return [$text, $url];
    }

    /**
     * Validate the shortname.
     *
     * @param int $value The value.
     * @return true|\lang_string
     */
    protected function validate_shortname($value) {
        if (self::record_exists_select('shortname = ? AND id != ?', [$value, $this->get('id')])) {
            return new \lang_string('invalidshortnameunique', 'block_dash');
        }

        if (strlen($value) > 16) {
            return new \lang_string('invalidshortnametoolong', 'block_dash');
        }

        return true;
    }

    /**
     * Loads the prepare editor filemanager.
     */
    public function prepare_dashboard_filemanager() {
        global $DB;
        $dashboard = $DB->get_record(static::TABLE, ['id' => $this->get('id')], '*', MUST_EXIST);
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        $upd = new \stdClass();
        $upd->id = $dashboard->id;
        foreach ($filemanagers as $field) {
            file_save_draft_area_files(
                $dashboard->{$field},
                \context_system::instance()->id,
                'dashaddon_dashboard',
                $field,
                $dashboard->id,
                self::get_filemanager_options(),
            );
            $upd->{$field} = $dashboard->{$field};
        }
        $DB->update_record('dashaddon_dashboard_dash', $upd);
    }

    /**
     * After create dashboard.
     */
    public function after_create() {
        $this->prepare_dashboard_filemanager();
    }

    /**
     * After update the dashoard.
     *
     * @param object $result
     */
    public function after_update($result) {
        global $DB;
        $this->prepare_dashboard_filemanager();
        $dashboard = $DB->get_record(static::TABLE, ['id' => $this->get('id')], '*', MUST_EXIST);

        // Check if context has changed and migrate block positions.
        $oldcontextid = $this->oldcontextid;
        $newcontextid = $this->get('contextid');

        if ($oldcontextid && $newcontextid && $oldcontextid != $newcontextid) {
            $this->migrate_block_positions($oldcontextid, $newcontextid);
        }

        $update = new \stdClass();
        $update->id = $dashboard->id;
        $update->roles = $dashboard->roles;
        $update->includedblocks = $dashboard->includedblocks;
        $DB->update_record('dashaddon_dashboard_dash', $update);

        // Clear the stored old contextid.
        $this->oldcontextid = null;
    }

    /**
     * Loads the prepare editor files.
     */
    public function prepare_filemanger_files() {
        global $DB;
        $record = $DB->get_record(static::TABLE, ['id' => $this->get('id')], '*', MUST_EXIST);
        $filemanagers = ['dashthumbnailimage', 'dashbgimage'];
        foreach ($filemanagers as $field) {
            $draftideditor = file_get_submitted_draft_itemid($field . '_filemanager');
            file_prepare_draft_area(
                $draftideditor,
                \context_system::instance()->id,
                'dashaddon_dashboard',
                $field,
                $record->id,
                self::get_filemanager_options()
            );
            $this->raw_set($field, $draftideditor);
        }
    }

    /**
     * Dashboard form filemanager element options.
     *
     * @return array
     */
    public static function get_filemanager_options() {
        global $CFG;
        return [
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'context' => \context_system::instance(),
            'noclean' => true,
            'subdirs' => false,
        ];
    }

    /**
     * @var int|null Store the old context ID before update
     */
    private $oldcontextid = null;

    /**
     * Method to be executed before updating the dashboard.
     * This method sets up the context ID for the dashboard.
     *
     * @return void
     */
    public function before_update() {
        // Store the old contextid before it gets changed.
        $this->oldcontextid = $this->get('contextid');
        $this->setup_contextid();
    }

    /**
     * Method to be executed before creating a dashboard instance.
     * This method sets up the context ID for the dashboard.
     *
     * @return void
     */
    public function before_create() {
        $this->setup_contextid();
    }

    /**
     * Sets up the context ID based on the context type.
     *
     * This method determines the context type (course, category, or system) and
     * sets the context ID accordingly. It retrieves the context type from the
     * object's properties and then fetches the appropriate context instance.
     * Finally, it sets the context ID in the object's properties.
     *
     * @return void
     */
    public function setup_contextid() {
        $contexttype = $this->get('contexttype');
        if ($contexttype == 'course') {
            $context = \context_course::instance($this->get('courseid'));
        } else if ($contexttype == 'category') {
            $context = \context_coursecat::instance($this->get('categoryid'));
        } else {
            $context = \context_system::instance();
        }
        $this->raw_set('contextid', $context->id);
    }

    /**
     * Before validate the properties.
     */
    public function before_validate() {
        $value = $this->raw_get('roles');
        $this->raw_set('roles', json_encode($value));
        $inculdeblocks = !empty($this->raw_get('includedblocks')) ? json_encode($this->raw_get('includedblocks')) : '';
        $this->raw_set('includedblocks', $inculdeblocks);
    }

    /**
     * Set the roles in the dashboard form.
     */
    public function set_roles_data() {
        global $DB;
        $record = $DB->get_record(static::TABLE, ['id' => $this->get('id')], '*', MUST_EXIST);
        if (!empty($record->roles)) {
            $roles = json_decode($record->roles);
            if (!empty($roles)) {
                if (!is_array($roles)) {
                    $roles = explode(',', $roles);
                }
                $role = implode(',', $roles);
                $this->raw_set('roles', $role);
            }
        }
    }

    /**
     * Sets the included blocks data for the dashboard.
     *
     * This method retrieves the record from the database using the current dashboard ID.
     * If the 'includedblocks' field is not empty, it decodes the JSON data. If the decoded
     * data is not an array, it converts it into an array by splitting the string by commas.
     * Finally, it converts the array back into a comma-separated string and sets it to the
     * 'includedblocks' field of the dashboard.
     *
     * @return void
     */
    public function set_includedblocks_data() {
        global $DB;
        $record = $DB->get_record(static::TABLE, ['id' => $this->get('id')], '*', MUST_EXIST);
        if (!empty($record->includedblocks)) {
            $blocks = json_decode($record->includedblocks);
            if (!empty($blocks)) {
                if (!is_array($blocks)) {
                    $blocks = explode(',', $blocks);
                }
                $blocks = implode(',', $blocks);
                $this->raw_set('includedblocks', $blocks);
            }
        }
    }

    /**
     * Duplicate the current dashboard.
     *
     * @return dashboard The new dashboard instance
     */
    public function duplicate() {
        global $DB;

        $data = $this->to_record();
        unset($data->id);
        $data->name = $this->get('name') . ' ' . get_string('copy', 'block_dash');
        $context = $this->get_context_instance();
        $data->contextid = $context->id;

        // Generate unique shortname.
        do {
            $newshortname = $this->dulicate_dashboard_shortname();
        } while ($DB->record_exists('dashaddon_dashboard_dash', ['shortname' => $newshortname]));

        $data->shortname = $newshortname;

        $newdashboard = new dashboard(0, $data);
        $newdashboard->create();

        $onpagenavigationblocks = [];
        $currentonpagenavigatioblocks = !empty($this->get('includedblocks')) ? json_decode($this->get('includedblocks')) : [];

        $blocks = \dashaddon_dashboard\helper::get_dashaddondash_pageblocks($this->get('shortname'));

        foreach ($blocks as $blockid => $blockname) {
            $block = $DB->get_record('block_instances', ['id' => $blockid]);
            if (!$block) {
                continue; // Skip if block not found.
            }

            // Clone the block instance.
            $newblock = clone($block);
            unset($newblock->id);
            $newblock->pagetypepattern = 'dashaddon-dashboard-' . $newdashboard->get('shortname');
            $newblock->defaultregion = $newdashboard->get('shortname');

            // Insert the new block instance.
            $newblockid = $DB->insert_record('block_instances', $newblock);

            // Copy all block positions for this block (there might be multiple contexts).
            $blockpositions = $DB->get_records('block_positions', ['blockinstanceid' => $block->id]);
            foreach ($blockpositions as $existbp) {
                $bp = new \stdClass();
                $bp->blockinstanceid = $newblockid;
                $bp->contextid = $context->id;
                $bp->pagetype = 'dashaddon-dashboard-' . $newdashboard->get('shortname');
                $bp->region = $existbp->region ?: $newdashboard->get('shortname');
                $bp->visible = $existbp->visible;
                $bp->weight = $existbp->weight;
                $DB->insert_record('block_positions', $bp);
            }

            // Track blocks for navigation.
            if (in_array($block->id, $currentonpagenavigatioblocks)) {
                $onpagenavigationblocks[] = $newblockid;
            }
        }
        // Update the included blocks for the new dashboard.
        $DB->set_field(
            'dashaddon_dashboard_dash',
            'includedblocks',
            json_encode($onpagenavigationblocks),
            ['id' => $newdashboard->get('id')]
        );

        return $newdashboard;
    }

    /**
     * Generates a random string of a specified length using specified characters.
     *
     * This function creates a random string of 16 characters in length, consisting of
     * lowercase and uppercase letters, as well as hyphens.
     *
     * @return string A randomly generated string of 16 characters.
     */
    protected function dulicate_dashboard_shortname() {
        $length = 16;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-';
        $characterslength = strlen($characters);
        $randomstring = '';
        for ($i = 0; $i < $length; $i++) {
            $randomstring .= $characters[random_int(0, $characterslength - 1)];
        }
        return $randomstring;
    }

    /**
     * Migrate block positions when dashboard context changes.
     *
     * @param int $oldcontextid The old context ID.
     * @param int $newcontextid The new context ID.
     * @return void
     */
    protected function migrate_block_positions($oldcontextid, $newcontextid) {
        global $DB;

        // Get the page type pattern for this dashboard.
        $pagetypepattern = 'dashaddon-dashboard-' . $this->get('shortname');

        // First, update any block_positions records (manual overrides).
        $sql = "UPDATE {block_instances}
                SET parentcontextid = :newcontextid
                WHERE parentcontextid = :oldcontextid
                AND pagetypepattern = :pagetype";

        $DB->execute($sql, [
            'oldcontextid' => $oldcontextid,
            'newcontextid' => $newcontextid,
            'pagetype' => $pagetypepattern,
        ]);

        // Clean up any orphaned block_instances records for the old context.
        $DB->delete_records('block_instances', [
            'parentcontextid' => $oldcontextid,
            'pagetypepattern' => $pagetypepattern,
        ]);
    }
}
