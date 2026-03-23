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
 * Form for editing block preferences.
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_dashboard\form;

use core\form\persistent as persisten_form;
use core_course_category;
use dashaddon_dashboard\model\dashboard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . "/local/dash/addon/dashboard/lib.php");

/**
 * Form for editing block preferences.
 *
 * @package    dashaddon_dashboard
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_form extends persisten_form {
    /**
     * Dashboard class object.
     *
     * @var dashboard::class;
     */
    protected static $persistentclass = dashboard::class;

    /**
     * List of fields to move from record.
     *
     * @var array
     */
    protected static $fieldstoremove = ['submitbutton', 'timemodified'];

    /**
     * Form field Definition
     *
     * @return void
     */
    protected function definition() {
        global $DB, $PAGE, $OUTPUT;

        $mform = $this->_form;

        // General header.
        $mform->addElement('header', 'generalheader', get_string('general'));

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        if (!$this->get_persistent()->get('id')) {
            $mform->addElement('text', 'shortname', get_string('shortname'));
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addRule('shortname', get_string('required'), 'required');
        } else {
            $mform->addElement('static', 'shortname', get_string('shortname'), $this->get_persistent()->get('shortname'));
        }

        $mform->addElement('editor', 'description', get_string('description'), ['rows' => 4]);
        $mform->setType('description', PARAM_CLEANHTML);

        // Context settings section.
        $mform->addElement('header', 'contextsettings', get_string('contextsettings', 'block_dash'));
        if ($this->get_persistent()->get('shortname') != 'coredashboard') {
            $options = $DB->get_records_sql_menu('SELECT c.id, c.fullname FROM {course} c
                                                    JOIN {context} ctx ON ctx.contextlevel = :contextlevel
                                                    AND ctx.instanceid = c.id
                                                    WHERE c.format != :format
                                                    ORDER BY c.fullname', [
                                                        'contextlevel' => CONTEXT_COURSE,
                                                        'format' => 'site',
                                                    ]);
            // Context type selection.
            $contexttypes = [
                'system' => get_string('contextsystem', 'block_dash'),
                'category' => get_string('contextcategory', 'block_dash'),
                'course' => get_string('contextcourse', 'block_dash'),
            ];
            if (isset($this->_customdata['categoryid'])) {
                $contexttypes = ['category' => get_string('contextcategory', 'block_dash')];
            }
            $mform->addElement('select', 'contexttype', get_string('contexttype', 'block_dash'), $contexttypes);
            $mform->setDefault('contexttype', 'system');

            // Course category.
            $categoryoptions = [];
            if (isset($this->_customdata['categoryid'])) {
                $categoryoptions[$this->_customdata['categoryid']] =
                    core_course_category::get($this->_customdata['categoryid'])->get_formatted_name();
            } else {
                foreach (\core_course_category::make_categories_list('moodle/category:manage') as $id => $category) {
                    $categoryoptions[$id] = $category;
                }
            }
            $mform->addElement('autocomplete', 'categoryid', get_string('selectcategory', 'block_dash'), $categoryoptions);
            $mform->addHelpButton('categoryid', 'coursecategory');
            $mform->hideIf('categoryid', 'contexttype', 'neq', 'category');

            // Course selector.
            $mform->addElement(
                'autocomplete',
                'courseid',
                get_string('selectcourse', 'block_dash'),
                $options,
                ['multiple' => false, 'includefrontpage' => false]
            );
            $mform->hideIf('courseid', 'contexttype', 'neq', 'course');

            if (local_dash_secondarynav()) {
                $mform->addElement('select', 'secondarynav', get_string('secondarynav', 'block_dash'), [
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ]);
                $mform->setType('secondarynav', PARAM_INT);
                $mform->hideIf('secondarynav', 'contexttype', 'neq', 'course');
            } else {
                $mform->addElement('hidden', 'secondarynav', 0);
                $mform->setType('secondarynav', PARAM_INT);
            }

            // Redirct to course dashboard.
            $mform->addElement('advcheckbox', 'redirecttodashboard', get_string('redirecttodashboard', 'block_dash'));
            $mform->hideIf('redirecttodashboard', 'contexttype', 'neq', 'course');
            $mform->setDefault('redirecttodashboard', 0);
        } else {
            $mform->addElement('hidden', 'secondarynav', 0);
            $mform->setType('secondarynav', PARAM_INT);
        }

        // Restrict access header.
        $mform->addElement('header', 'restrictaccessheader', get_string('restrictaccess', 'block_dash'));

        if ($this->get_persistent()->get('shortname') != 'coredashboard') {
            // Permission selector.
            $mform->addElement('select', 'permission', get_string('permissions', 'block_dash'), [
                'loggedin' => get_string('permissionsloggedin', 'block_dash'),
                'public' => get_string('permissionspublic', 'block_dash'),
                'cohort' => get_string('permissionscohort', 'block_dash'),
                'role' => get_string('permissionsrole', 'block_dash'),
            ]);
            $mform->setType('permission', PARAM_TEXT);

            // Cohort selector.
            $cohortdata = cohort_get_all_cohorts(0, 0);
            $options = [];
            foreach ($cohortdata['cohorts'] as $cohort) {
                $options[$cohort->id] = $cohort->name;
            }

            $mform->addElement('select', 'cohort_id', get_string('cohort', 'cohort'), $options);
            $mform->setType('cohort_id', PARAM_INT);
            $mform->hideIf('cohort_id', 'permission', 'noeq', 'cohort');

            // Role selection.
            $rolelist = role_get_names(\context_system::instance());
            $roleoptions = [];
            foreach ($rolelist as $role) {
                $roleoptions[$role->id] = $role->localname;
            }

            $mform->addElement('autocomplete', 'roles', get_string('role'), $roleoptions, ['multiple' => 'multiple']);
            $mform->hideIf('roles', 'permission', 'noeq', 'role');

            // Role context.
            $rolecontext = [
                ANYCONTEXT => get_string('any'),
                SYSTEMCONTEXT => get_string('coresystem'),
            ];
            $mform->addElement('select', 'rolecontext', get_string('permissionsrolecontext', 'block_dash'), $rolecontext);
            $mform->setType('rolecontext', PARAM_INT);
            $mform->addHelpButton('rolecontext', 'permissionsrolecontext', 'block_dash');
            $mform->hideIf('rolecontext', 'permission', 'noeq', 'role');
        }

        // Appearance header.
        $mform->addElement('header', 'appearanceheader', get_string('appearance'));

        // Add a font awesome icon element.
        $theme = \theme_config::load($PAGE->theme->name);
        $faiconsystem = \core\output\icon_system_fontawesome::instance($theme->get_icon_system());
        $iconlist = $faiconsystem->get_core_icon_map();
        array_unshift($iconlist, '');
        // Create element.
        $iconwidget = $mform->addElement(
            'select',
            'dashicon',
            get_string('dashicon', 'block_dash'),
            $iconlist
        );
        $mform->setType('dashicon', PARAM_TEXT);
        $iconwidget->setMultiple(false);
        $mform->addHelpButton('dashicon', 'dashicon', 'block_dash');
        // Include the fontawesome icon picker to the element.
        $systemcontextid = \context_system::instance()->id;
        $PAGE->requires->js_call_amd('local_dash/fontawesome-popover', 'init', ['#id_dashicon', $systemcontextid]);

        // Add thumbnail image as filemanager element.
        $mform->addElement(
            'filemanager',
            'dashthumbnailimage',
            get_string('dashthumbnailimg', 'block_dash'),
            null,
            [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => 'web_image',
                        'return_types' => FILE_INTERNAL,
            ]
        );
        $mform->addHelpButton('dashthumbnailimage', 'dashthumbnailimg', 'block_dash');

        // Add background image as filemanager element.
        $mform->addElement(
            'filemanager',
            'dashbgimage',
            get_string('dashbgimg', 'block_dash'),
            null,
            [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => 'web_image',
                        'return_types' => FILE_INTERNAL,
            ]
        );

        // On page navigation settings.
        $mform->addElement('header', 'onpagenavigationsettings', get_string('onpagenavigation', 'block_dash'));

        // Included blocks.
        $blocksoptions = \dashaddon_dashboard\helper::get_dashaddondash_pageblocks($this->get_persistent()->get('shortname'));
        if (!empty($blocksoptions)) {
            $mform->addElement(
                'autocomplete',
                'includedblocks',
                get_string('includedblocks', 'block_dash'),
                $blocksoptions,
                ['multiple' => 'multiple']
            );
            $mform->setType('includedblocks', PARAM_TEXT);

            // Display dashboard title.
            $displaytitleoptions = [
                0 => get_string('disabled', 'block_dash'),
                1 => get_string('always', 'block_dash'),
                2 => get_string('onlywhensticky', 'block_dash'),
            ];
            $mform->addElement(
                'select',
                'displaydashboardtitle',
                get_string('displaydashboardtitle', 'block_dash'),
                $displaytitleoptions
            );
            $mform->setDefault('displaydashboardtitle', 0);

            // Display call to action.
            $mform->addElement('select', 'displaycta', get_string('displaycta', 'block_dash'), $displaytitleoptions);
            $mform->setDefault('displaycta', 0);

            // Call to action link type.
            $ctaoptionsarray = [
                'enrolment' => get_string('enrolmentoptions', 'block_dash'),
                'campaign' => get_string('campaign', 'block_dash'),
                'shopurl' => get_string('shopurl', 'block_dash'),
                'custom' => get_string('customurl', 'block_dash'),
            ];

            $mform->addElement('select', 'ctalink', get_string('ctalink', 'block_dash'), $ctaoptionsarray);
            if (array_key_exists('magic', \core_component::get_plugin_list('auth'))) {
                // Campaign selector - visible when campaign is selected.
                $campaignoptions = $this->get_campaign_options(); // You'll need to implement this method.
                if ($campaignoptions) {
                    $mform->addElement('select', 'ctacampaignid', get_string('selectcampaign', 'block_dash'), $campaignoptions);
                    $mform->hideIf('ctacampaignid', 'ctalink', 'neq', 'campaign');
                } else {
                    $mform->addElement(
                        'static',
                        'ctacampaignidinfo',
                        get_string('selectcampaign', 'block_dash'),
                        get_string('nocampaignsareavailable', 'block_dash')
                    );
                    $mform->hideIf('ctacampaignidinfo', 'ctalink', 'neq', 'campaign');
                }
            } else {
                $mform->addElement('static', 'magicauthinfo', '', get_string('magicnotavailable', 'block_dash'));
                $mform->hideIf('magicauthinfo', 'ctalink', 'neq', 'campaign');
            }

            // Custom URL input - visible when custom is selected.
            $mform->addElement('text', 'ctacustomurl', get_string('customurl', 'block_dash'));
            $mform->setType('ctacustomurl', PARAM_URL);
            $mform->hideIf('ctacustomurl', 'ctalink', 'neq', 'custom');

            $mform->addElement('text', 'ctacustomurltext', get_string('customurltext', 'block_dash'));
            $mform->setType('ctacustomurltext', PARAM_TEXT);
            $mform->hideIf('ctacustomurltext', 'ctalink', 'neq', 'custom');
        } else {
            $mform->addElement('static', 'onpagenavigationnoblocks', '', get_string('blocksnotfound', 'block_dash'));
        }

        $this->add_action_buttons();
    }

    /**
     * Retrieves the list of campaign options.
     *
     * This function checks if the 'magic' authentication plugin is available.
     * If it is, it fetches the campaign records from the 'auth_magic_campaigns' table
     * and returns them as an associative array with the campaign ID as the key and the title as the value.
     *
     * @return array An associative array of campaign options with campaign ID as the key and title as the value.
     */
    public function get_campaign_options() {
        global $DB;
        $campaigns = [];
        if (array_key_exists('magic', \core_component::get_plugin_list('auth'))) {
            $campaigns = $DB->get_records_menu('auth_magic_campaigns', null, '', 'id, title');
        }
        return $campaigns;
    }
}
