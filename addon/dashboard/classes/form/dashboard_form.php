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
use dashaddon_dashboard\model\dashboard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot. "/local/dash/addon/dashboard/lib.php");

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
        global $DB,  $PAGE;

        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        if (!$this->get_persistent()->get('id')) {
            $mform->addElement('text', 'shortname', get_string('shortname'));
            // Shortname is actually PARAM_ALPHAEXT. Setting to PARAM_TEXT so value is not cleaned before validation. That
            // Allows dashboard::validate_shortname() to warn the user instead of silently cleaning and submitting.
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addRule('shortname', get_string('required'), 'required');
        } else {
            $mform->addElement('static', 'shortname', get_string('shortname'), $this->get_persistent()->get('shortname'));
        }

        // Description.
        $mform->addElement('editor', 'description', get_string('description'), ['rows' => 4]);
        $mform->setType('description', PARAM_CLEANHTML);

        if ($this->get_persistent()->get('shortname') != 'coredashboard') {

            $options = $DB->get_records_sql_menu('SELECT ctx.id, c.fullname FROM {course} c
                                                JOIN {context} ctx ON ctx.contextlevel = :contextlevel
                                                AND ctx.instanceid = c.id
                                                WHERE c.format != :format
                                                ORDER BY c.fullname', [
                                                    'contextlevel' => CONTEXT_COURSE,
                                                    'format' => 'site',
                                                ]);
            $options = [\context_system::instance()->id => get_string('system', 'block_dash')] + $options;

            $mform->addElement('select', 'contextid', get_string('contextid', 'block_dash'), $options);
            $mform->setType('contextid', PARAM_INT);
            $mform->addRule('contextid', get_string('required'), 'required');
            $mform->addHelpButton('contextid', 'contextid', 'block_dash');

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

            if (local_dash_secondarynav()) {
                $mform->addElement('select', 'secondarynav', get_string('secondarynav', 'block_dash'), [
                    1 => get_string('yes'),
                    0 => get_string('no'),
                ]);
                $mform->setType('secondarynav', PARAM_INT);
            } else {
                $mform->addElement('hidden', 'secondarynav', 0);
                $mform->setType('secondarynav', PARAM_INT);
            }
        } else {
            $mform->addElement('hidden', 'secondarynav', 0);
            $mform->setType('secondarynav', PARAM_INT);
        }

        // Add a font awesome icon element.
        $theme = \theme_config::load($PAGE->theme->name);
        $faiconsystem = \core\output\icon_system_fontawesome::instance($theme->get_icon_system());
        $iconlist = $faiconsystem->get_core_icon_map();
        array_unshift($iconlist, '');
        // Create element.
        $iconwidget = $mform->addElement('select', 'dashicon',
                get_string('dashicon', 'block_dash'), $iconlist);
        $mform->setType('dashicon', PARAM_TEXT);
        $iconwidget->setMultiple(false);
        $mform->addHelpButton('dashicon', 'dashicon', 'block_dash');
        // Include the fontawesome icon picker to the element.
        $systemcontextid = \context_system::instance()->id;
        $PAGE->requires->js_call_amd('local_dash/fontawesome-popover', 'init', ['#id_dashicon', $systemcontextid]);

        // Add thumbnail image as filemanager element.
        $mform->addElement('filemanager', 'dashthumbnailimage',
                get_string('dashthumbnailimg', 'block_dash'), null, [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => 'web_image',
                        'return_types' => FILE_INTERNAL,
        ]);
        $mform->addHelpButton('dashthumbnailimage', 'dashthumbnailimg', 'block_dash');

        // Add background image as filemanager element.
        $mform->addElement('filemanager', 'dashbgimage',
                get_string('dashbgimg', 'block_dash'), null, [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => 'web_image',
                        'return_types' => FILE_INTERNAL,
        ]);
        $mform->addHelpButton('dashbgimage', 'dashbgimg', 'block_dash');

        $this->add_action_buttons();
    }
}
