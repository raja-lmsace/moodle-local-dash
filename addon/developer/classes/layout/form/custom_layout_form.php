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
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\layout\form;

use core\form\persistent as persisten_form;
use dashaddon_developer\model\custom_layout;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Form for editing block preferences.
 *
 * @package    dashaddon_developer
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_layout_form extends persisten_form {
    /**
     * Class defined to handle the submitted data from the form.
     *
     * @var \custom_layout
     */
    protected static $persistentclass = custom_layout::class;

    /**
     * Remove the form fields values from the submitted value.
     *
     * @var array
     */
    protected static $fieldstoremove = ['submitbutton', 'timemodified'];

    /**
     * Form fields defined.
     *
     * @return void
     */
    protected function definition() {

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        $mform->addElement(
            'advcheckbox',
            'supports_field_visibility',
            get_string('supportsfieldvisibility', 'block_dash')
        );
        $mform->setType('supports_field_visibility', PARAM_INT);
        $mform->addHelpButton('supports_field_visibility', 'supportsfieldvisibility', 'block_dash');

        $mform->addElement(
            'advcheckbox',
            'supports_filtering',
            get_string('supportsfiltering', 'block_dash')
        );
        $mform->setType('supports_filtering', PARAM_INT);
        $mform->addHelpButton('supports_filtering', 'supportsfiltering', 'block_dash');

        $mform->addElement(
            'advcheckbox',
            'supports_pagination',
            get_string('supportspagination', 'block_dash')
        );
        $mform->setType('supports_pagination', PARAM_INT);
        $mform->addHelpButton('supports_pagination', 'supportspagination', 'block_dash');

        $mform->addElement(
            'advcheckbox',
            'supports_sorting',
            get_string('supportssorting', 'block_dash')
        );
        $mform->setType('supports_sorting', PARAM_INT);
        $mform->addHelpButton('supports_sorting', 'supportssorting', 'block_dash');

        $mform->addElement('textarea', 'mustache_template', get_string('mustachetemplate', 'block_dash'));
        $mform->setType('mustache_template', PARAM_RAW);
        $mform->addRule('mustache_template', get_string('required'), 'required');

        $this->add_action_buttons();
    }
}
