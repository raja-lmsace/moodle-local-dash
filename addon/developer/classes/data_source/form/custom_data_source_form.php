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
 * Custom data source creation persistent form.
 *
 * @package   dashaddon_developer
 * @copyright 2020 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_developer\data_source\form;


use block_dash\local\dash_framework\query_builder\where;
use core\form\persistent as persistent_form;
use dashaddon_developer\model\custom_data_source;
use html_writer;
use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once('HTML/QuickForm.php');

/**
 * Custom data source creation persistent form.
 */
class custom_data_source_form extends persistent_form {
    /**
     * Presistent form handler of submited data.
     *
     * @var core\persistent
     */
    protected static $persistentclass = custom_data_source::class;

    /**
     * List of fields data should be removed from the submitted data.
     *
     * @var array
     */
    protected static $fieldstoremove = ['timemodified'];

    /**
     * Operator condition flag for AND.
     *
     * @var int
     */
    public const OPERATORCONDITION_AND = 1;

    /**
     * Operator condition flag for OR.
     *
     * @var int
     */
    public const OPERATORCONDITION_OR = 2;

    /**
     * Count of the joins added for this datasource.
     *
     * @var int
     */
    public $joincount = 0;

    /**
     * Table alias prefix.
     *
     * @var string
     */
    public static $tablealiasprefix = 'jt';

    /**
     * List of field attributes.
     *
     * @var array
     */
    public $fieldattributes = [];

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $OUTPUT;

        $mform = $this->_form;

        // General sections start.
        $mform->addElement('header', 'general', get_string('general'));

        $mform->addElement('text', 'name', get_string('datasourcename', 'block_dash'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        $mform->addElement('text', 'idnumber', get_string('idnumber'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addRule('idnumber', get_string('required'), 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Main table selector.
        $tables = $this->get_tables_list();
        $mform->addElement('autocomplete', 'maintable', get_string('maintable', 'block_dash'), $tables);
        $mform->addHelpButton('maintable', 'maintable', 'block_dash');

        $mform->addElement('submit', 'settable', get_string('generatefields', 'block_dash'), ['class' => 'd-non']);
        $mform->registerNoSubmitButton('settable');
    }

    /**
     * Create repeats fields based on main table after the field defined.
     *
     * @return void
     */
    public function definition_after_data() {

        global $DB, $OUTPUT, $PAGE;

        $mform = $this->_form;
        $maintable = $mform->getElementValue('maintable');
        $maintable = !empty($maintable) ? reset($maintable) : 0;

        if (!$maintable) {
            // Action buttons.
            return;
        }

        // Field repeats and condition repeats.
        $persistent = $this->get_persistent();
        $fieldrepeats = $persistent->get('fieldrepeats') ?: 1;
        $conditionrepeats = $persistent->get('conditionrepeats') ?: 1;
        $joinrepeats = $persistent->get('joinrepeats') ?: 1;

        $tables = $this->get_tables_list();
        unset($tables[$maintable]);

        // Join tables sections start.
        $mform->addElement('header', 'tablejoinshdr', get_string('tablejoinshdr', 'block_dash'));

        // Enable the joins.
        $mform->addElement('advcheckbox', 'enablejoins', '', get_string('enablejoins', 'block_dash'), ['group' => 1], [0, 1]);

        // Table joins.
        $header = '<h5 class="jointable">' . get_string('tablejoinstatichdr', 'block_dash', $this->joincount) . '</h5>';
        $joins = [
            $mform->createElement('static', 'tablejoinstatichdr', $header),
            $mform->createElement('autocomplete', 'tablejoins', get_string('tablejoin', 'block_dash'), $tables),

            $mform->createElement(
                'text',
                'tablejoinsalias',
                get_string('tablejoinsalias', 'block_dash'),
                ['placeholder' => 'table AS alias (course AS c)', 'size' => 40]
            ),

            $mform->createElement(
                'text',
                'tablejoinon',
                get_string('tablejoinon', 'block_dash'),
                ['placeholder' => 'c.id=mnt.course', 'size' => 40]
            ),
            $mform->createElement('submit', 'deletejoin', get_string('deletejoin', 'block_dash'), ['class' => 'd-none'], false),
            $mform->createElement(
                'button',
                'deletejoinbtn',
                $OUTPUT->pix_icon('t/delete', get_string('deletejoin', 'block_dash')),
                ['data-type' => 'dashaddon_dev_deletebtn'],
                false
            ),
        ];

        $joinoptions = [
            'deletejoinbtn' => [
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
            ],
            'tablejoinsalias' => [
                'type' => PARAM_ALPHANUMEXT,
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
            ],
            'tablejoinon' => [
                'type' => PARAM_NOTAGS,
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
            ],
            'tablejoins' => [
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
                'helpbutton' => ['tablejoin', 'block_dash', '', true],

            ],
            'table_add_joins' => [
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
            ],
            'tablejoinstatichdr' => [
                'hideif' => ['enablejoins', 'notchecked'],
                'disabledif' => ['enablejoins', 'notchecked'],
            ],
        ];

        // Repeat elements.
        $this->repeat_elements(
            $joins,
            $joinrepeats,
            $joinoptions,
            'joinrepeats',
            'table_add_joins',
            1,
            get_string('addjointable', 'block_dash'),
            false,
            'deletejoin'
        );

        // Fields of the main table.
        $columns = $DB->get_columns($maintable);
        $tablefields = array_combine(array_keys($columns), array_keys($columns));

        // List of available attributes.
        $attributes = [0 => get_string('none')];
        $cmpts = $this->get_attributes();

        foreach ($cmpts as $key => $fullclassname) {
            if (is_subclass_of($fullclassname, abstract_field_attribute::class)) {
                $expclass = explode('\\', $fullclassname);
                $name = end($expclass);
                $name = str_replace('_', ' ', str_replace('attribute', '', $name));
                $attributes[$fullclassname] = ucfirst($name);
            }
        }

        $this->fieldattributes = $attributes;

        // Conditions list.
        $conditions = [
            where::OPERATOR_EQUAL => get_string('operatorequal', 'block_dash'),
            where::OPERATOR_NOT_EQUAL => get_string('operatornotequal', 'block_dash'),
            where::OPERATOR_GREATERTHAN => get_string('operatorgreaterthan', 'block_dash'),
            where::OPERATOR_GREATERTHAN_EQUAL => get_string('operatorgreaterthanequal', 'block_dash'),
            where::OPERATOR_LESSTHAN => get_string('operatorlessthan', 'block_dash'),
            where::OPERATOR_LESSTHAN_EQUAL => get_string('operatorlessthanequal', 'block_dash'),
            where::OPERATOR_LIKE => get_string('operatorlike', 'block_dash'),
            where::OPERATOR_NOT_LIKE => get_string('operatornotlike', 'block_dash'),
            where::OPERATOR_IN => get_string('operatorin', 'block_dash'),
            where::OPERATOR_IN_QUERY => get_string('operatorinquery', 'block_dash'),
            where::OPERATOR_NOT_IN => get_string('operatornotin', 'block_dash'),
        ];

        // Multiple conditions connection method And or OR.
        $optconditions = [
            where::CONJUNCTIVE_OPERATOR_AND => 'AND',
            where::CONJUNCTIVE_OPERATOR_OR => 'OR',
        ];

        // Field sections start.
        $mform->addElement('header', 'fieldssection', get_string('fieldssection', 'block_dash'));

        // Placeholders.
        $options = ['ajax' => 'dashaddon_developer/fields-selector', 'multiple' => true];
        $mform->addElement(
            'autocomplete',
            'placeholderfields',
            get_string('placeholderfields', 'block_dash'),
            $tablefields,
            $options
        );
        $mform->addHelpButton('placeholderfields', 'placeholderfields', 'block_dash');

        $options = ['ajax' => 'dashaddon_developer/fields-selector']; // Fields ajax request.

        $header = '<h5 class="tablefields">' . get_string('fieldstatichdr', 'block_dash') . '</h5>';

        $fieldattrs = [];
        $fieldattrs[] =& $mform->createElement(
            'select',
            'fieldattribute',
            get_string('fieldtransformdata', 'block_dash'),
            $attributes
        );
        $fieldattrs[] =& $mform->createElement('submit', 'addattributebtn', '+');

        $attrvalues = [];
        $attrvalues[] =& $mform->createElement(
            'text',
            'attributevalue',
            get_string('fieldcustomdata', 'block_dash'),
            ['size' => '50', 'placeholder' => 'course/view.php?id={PLACEHOLDER}']
        );

        $fields = [
            $mform->createElement('html', '<div class="select-fields-group">'),
            $mform->createElement('static', 'fieldstatichdr', $header),
            $mform->createElement('autocomplete', 'selectfield', get_string('field', 'block_dash'), $tablefields, $options),
            $mform->createElement(
                'group',
                'fieldattributegrp',
                get_string('fieldtransformdata', 'block_dash'),
                $fieldattrs,
                null,
                false
            ),
            $mform->createElement(
                'group',
                'attributevaluegrp',
                get_string('fieldcustomdata', 'block_dash'),
                $attrvalues,
                null,
                false
            ),

            $mform->createElement('submit', 'deletefield', get_string('deletefield', 'block_dash'), ['class' => 'd-none'], false),
            $mform->createElement(
                'button',
                'deletefieldbtn',
                $OUTPUT->pix_icon('t/delete', get_string('deletefield', 'block_dash')),
                ['data-type' => 'dashaddon_dev_deletebtn'],
                false
            ),
            $mform->createElement('html', '</div>'),
        ];

        $notcustomvaluesfields = [
            'bool_attribute', 'date_attribute', '',
        ];

        $fieldoptions = [
            'fieldattribute' => [
                'helpbutton' => ['fieldtransformdata', 'block_dash', '', true],
            ],
            'attributevalue' => [
                'hideif' => ['fieldattribute', 'in', $notcustomvaluesfields],
                'type' => PARAM_NOTAGS,
                'helpbutton' => ['fieldcustomdata', 'block_dash', '', true],
            ],

        ];

        // Field repeats.
        $this->repeat_elements(
            $fields,
            $fieldrepeats,
            $fieldoptions,
            'fieldrepeats',
            'field_add_fields',
            3,
            null,
            false,
            'deletefield'
        );

        $mform->registerNoSubmitButton('addattributebtn');

        // Condition sections start.
        $mform->addElement('header', 'conditionsection', get_string('conditionsection', 'block_dash'));

        // Custom conditions.
        $mform->addElement('textarea', 'customcondition', get_string('customcondition', 'block_dash'));
        $mform->setType('customcondition', PARAM_NOTAGS);

        // Enable the conditions.
        $mform->addElement(
            'advcheckbox',
            'enableconditions',
            '',
            get_string('enableconditions', 'block_dash'),
            ['group' => 1],
            [0, 1]
        );

        $header = '<h5 class="tableconditions">' . get_string('conditionstatichdr', 'block_dash') . '</h5>';
        $fields = [
            $mform->createElement('html', '<div class="condition-group">'),
            $mform->createElement('static', 'conditionstatichdr', $header),
            $mform->createElement('autocomplete', 'conditionfield', get_string('field', 'block_dash'), $tablefields, $options),
            $mform->createElement('select', 'operator', get_string('operator', 'block_dash'), $conditions),
            $mform->createElement('select', 'operatorcondition', get_string('operatorcondition', 'block_dash'), $optconditions),
            $mform->createElement('text', 'conditionvalue', get_string('conditionvalue', 'block_dash')),
            $mform->createElement(
                'submit',
                'deletecondition',
                get_string('deletecondition', 'block_dash'),
                ['class' => 'd-none'],
                false
            ),
            $mform->createElement(
                'button',
                'deleteconditionbtn',
                $OUTPUT->pix_icon('t/delete', get_string('deletecondition', 'block_dash')),
                ['data-type' => 'dashaddon_dev_deletebtn'],
                false
            ),
            $mform->createElement('html', '</div>'),
        ];

        $fieldoptions = [
            'operatorcondition' => [
                'helpbutton' => ['operatorcondition', 'block_dash', '', true],
                'hideif' => ['enableconditions', 'notchecked'],
                'disabledif' => ['enableconditions', 'notchecked'],
            ],
            'conditionvalue' => [
                'type' => PARAM_ALPHANUMEXT,
                'hideif' => ['enableconditions', 'notchecked'],
                'disabledif' => ['enableconditions', 'notchecked'],
                'helpbutton' => ['conditionvalue', 'block_dash', '', true],
            ],
        ];
        $fieldoptions['operator'] =
        $fieldoptions['conditionfield'] = $fieldoptions['deleteconditionbtn'] = [
            'hideif' => ['enableconditions', 'notchecked'],
            'disabledif' => ['enableconditions', 'notchecked'],
        ];

        $this->repeat_elements(
            $fields,
            $conditionrepeats,
            $fieldoptions,
            'conditionrepeats',
            'condition_add_fields',
            1,
            get_string('conditionaddfields', 'block_dash'),
            true,
            'deletecondition'
        );

        // Action buttons.
        $this->add_action_buttons();

        $PAGE->requires->js_amd_inline('
            document.querySelectorAll(\'[data-type="dashaddon_dev_deletebtn"]\').forEach((el) => {
                console.log(el);
                el.onclick = (e) => e.target.closest(".fitem").previousSibling.querySelector(\'input[type="submit"]\').click();
            })

            require(["core/str"], function(str) {
                var i = 1;
                document.querySelectorAll("h5.jointable").forEach((e) => {
                    e.innerHTML = e.innerHTML.replace("#0", "#" + i.toString());
                    i++;
                });

                var l = 1;
                document.querySelectorAll("h5.tablefields").forEach((e) => {
                    e.innerHTML = e.innerHTML.replace("#0", "#" + l.toString());
                    l++;
                });

                var k = 1;
                document.querySelectorAll("h5.tableconditions").forEach((e) => {
                    e.innerHTML = e.innerHTML.replace("#0", "#" + k.toString());
                    k++;
                });

            })
        ');
    }


    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * note: $slashed param removed
     *
     * @param stdClass|array $defaultvalues object or array of default values
     */
    function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array) $defaultvalues;
        }

        if ($attrs = optional_param_array('addattributebtn', false, PARAM_ALPHANUMEXT)) {
            $fieldattributes = $this->_form->getSubmitValue('fieldattribute');

            $key = array_key_last($attrs);

            if (isset($fieldattributes[$key]) && is_array($fieldattributes[$key])) {
                $fieldattributes[$key][] = 0;
                $this->_form->_submitValues['fieldattribute'][$key][] = 0;
            } else {
                $fieldattributes[$key] = [0];
                $this->_form->_submitValues['fieldattribute'][$key] = [0];
            }

            $defaultvalues['fieldattribute'] = $fieldattributes;
        }
        $this->_form->setDefaults($defaultvalues);
    }

    /**
     * Method to add a repeating group of elements to a form.
     *
     * Modified version of the original method in moodleform to make the field attribute and
     * field values in array format for the loop in field definition.
     *
     * @param array $elementobjs Array of elements or groups of elements that are to be repeated
     * @param int $repeats no of times to repeat elements initially
     * @param array $options a nested array. The first array key is the element name.
     *    the second array key is the type of option to set, and depend on that option,
     *    the value takes different forms.
     *         'default'    - default value to set. Can include '{no}' which is replaced by the repeat number.
     *         'type'       - PARAM_* type.
     *         'helpbutton' - array containing the helpbutton params.
     *         'disabledif' - array containing the disabledIf() arguments after the element name.
     *         'rule'       - array containing the addRule arguments after the element name.
     *         'expanded'   - whether this section of the form should be expanded by default. (Name be a header element.)
     *         'advanced'   - whether this element is hidden by 'Show more ...'.
     * @param string $repeathiddenname name for hidden element storing no of repeats in this form
     * @param string $addfieldsname name for button to add more fields
     * @param int $addfieldsno how many fields to add at a time
     * @param string $addstring name of button, {no} is replaced by no of blanks that will be added.
     * @param bool $addbuttoninside if true, don't call closeHeaderBefore($addfieldsname). Default false.
     * @param string $deletebuttonname if specified, treats the no-submit button with this name as a "delete element" button
     *         in each of the elements
     * @return int no of repeats of element in this page
     */
    public function repeat_elements(
        $elementobjs,
        $repeats,
        $options,
        $repeathiddenname,
        $addfieldsname,
        $addfieldsno = 5,
        $addstring = null,
        $addbuttoninside = false,
        $deletebuttonname = ''
        ) {
        if ($addstring === null) {
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }
        $repeats = $this->optional_param($repeathiddenname, $repeats, PARAM_INT);
        $addfields = $this->optional_param($addfieldsname, '', PARAM_TEXT);
        $oldrepeats = $repeats;
        if (!empty($addfields)) {
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
        // Value not to be overridden by submitted value.
        $mform->setConstants([$repeathiddenname => $repeats]);
        $namecloned = [];
        $no = 1;
        for ($i = 0; $i < $repeats; $i++) {
            if ($deletebuttonname) {
                $mform->registerNoSubmitButton($deletebuttonname . "[$i]");
                $isdeleted = $this->optional_param($deletebuttonname . "[$i]", false, PARAM_RAW) ||
                    $this->optional_param($deletebuttonname . "-hidden[$i]", false, PARAM_RAW);
                if ($isdeleted) {
                    $mform->addElement('hidden', $deletebuttonname . "-hidden[$i]", 1);
                    $mform->setType($deletebuttonname . "-hidden[$i]", PARAM_INT);
                    continue;
                }
            }

            foreach ($elementobjs as $elementobj) {
                $elementclone = fullclone($elementobj);
                $this->repeat_elements_fix_clone($i, $elementclone, $namecloned);

                if ($elementclone instanceof \HTML_QuickForm_group && !$elementclone->_appendName) {
                    foreach ($elementclone->getElements() as $el) {
                        $this->repeat_elements_fix_clone($i, $el, $namecloned);
                    }

                    // FIX- DASH. Create field attributes for cloned elements. -- No other changes in this function.
                    $this->create_field_attributes($elementclone, $i);

                    $elementclone->setLabel(str_replace('{no}', $no, $elementclone->getLabel()));
                } else if ($elementobj instanceof \HTML_QuickForm_submit && $elementobj->getName() == $deletebuttonname) {
                    // Mark the "Delete" button as no-submit.
                    $onclick = $elementclone->getAttribute('onclick');
                    $skip = 'skipClientValidation = true;';
                    $onclick = ($onclick !== null) ? $skip . ' ' . $onclick : $skip;
                    $elementclone->updateAttributes(['data-skip-validation' => 1, 'data-no-submit' => 1, 'onclick' => $onclick]);
                }

                // Mark newly created elements, so they know not to look for any submitted data.
                if ($i >= $oldrepeats) {
                    $mform->note_new_repeat($elementclone->getName());
                }

                $mform->addElement($elementclone);
                $no++;
            }
        }
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($options as $elementname => $elementoptions) {
                $pos = strpos($elementname, '[');
                if ($pos !== FALSE) {
                    $realelementname = substr($elementname, 0, $pos) . "[$i]";
                    $realelementname .= substr($elementname, $pos);
                } else {
                    $realelementname = $elementname . "[$i]";
                }
                foreach ($elementoptions as  $option => $params) {
                    switch ($option){
                        case 'default':
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton':
                            $params = array_merge([$realelementname], $params);
                            call_user_func_array([&$mform, 'addHelpButton'], $params);
                            break;
                        case 'disabledif':
                        case 'hideif':
                            $pos = strpos($params[0], '[');
                            $ending = '';
                            if ($pos !== false) {
                                $ending = substr($params[0], $pos);
                                $params[0] = substr($params[0], 0, $pos);
                            }
                            foreach ($namecloned as $num => $name) {
                                if ($params[0] == $name) {
                                    $params[0] = $params[0] . "[$i]" . $ending;
                                    break;
                                }
                            }
                            $params = array_merge([$realelementname], $params);
                            $function = ($option === 'disabledif') ? 'disabledIf' : 'hideIf';
                            call_user_func_array([&$mform, $function], $params);
                            break;
                        case 'rule':
                            if (is_string($params)) {
                                $params = [null, $params, null, 'client'];
                            }
                            $params = array_merge([$realelementname], $params);
                            call_user_func_array([&$mform, 'addRule'], $params);
                            break;

                        case 'type':
                            $mform->setType($realelementname, $params);
                            break;

                        case 'expanded':
                            $mform->setExpanded($realelementname, $params);
                            break;

                        case 'advanced':
                            $mform->setAdvanced($realelementname, $params);
                            break;
                    }
                }
            }
        }
        $mform->addElement('submit', $addfieldsname, $addstring, [], false);

        if (!$addbuttoninside) {
            $mform->closeHeaderBefore($addfieldsname);
        }

        return $repeats;
    }

    /**
     * Create field attribute and value elements for the cloned field based on the counts of exising attributes
     * in the submitted data.
     *
     * When user clicks the add attribute button,
     * it creates a new select element for the field attributes and a new text element for the attribute value, and it appends
     * to the field group. But when the form is submitted, we need to recreate those elements based on the
     * submitted data to make sure the form validation works and the submitted data is mapped correctly to the persistent.
     *
     * @param mixed $element
     * @param int $index
     *
     * @return void
     *
     */
    public function create_field_attributes($element, $index) {

        // Verify the element is field attribute or attribute value group. otherwise, return directly.
        if (
            !($element instanceof \HTML_QuickForm_group) ||
            (str_contains($element->getName(), 'fieldattributegrp') === false &&
            str_contains($element->getName(), 'attributevaluegrp') === false)
        ) {
            return;
        }

        // Get the submitted values for the field attributes, the structure is like fieldattribute[fieldindex][attributecount],
        // So we need to loop through the attribute count to create the elements.
        $attrslist = $this->_form->_submitValues['fieldattribute'] ?? $this->_form->_defaultValues['fieldattribute'];

        foreach ($element->getElements() as $childelement) {
            $name = $childelement->getName();

            // Element is field attribute then update the name with index.
            if (!empty($name) && str_contains($name, 'fieldattribute')) {
                $childelement->setName($name . "[0]"); // The first attribute element.

                // Multiple attribute are available.
                if (is_array($attrslist) && !empty($attrslist[$index]) && count($attrslist[$index]) > 0) {
                    // Create new select element for each attribute and append to the field group.
                    foreach ($attrslist[$index] as $i => $attrvalue) {
                        if ($i == 0) { // We already have the first element created in the form definition, so skip it.
                            continue;
                        }

                        // Create new select element for the field attribute.
                        $newattribute = $this->_form->createElement('select', $name . "[$i]", '', $this->fieldattributes);
                        // Append the new element to the field group.
                        $last = array_pop($element->_elements);
                        $element->_elements[] = $newattribute;
                        $element->_elements[] = $last;
                    }
                }
            }

            // Element is attribute value then update the name with index.

            if (!empty($name) && str_contains($name, 'attributevalue')) {
                $childelement->setName($name . "[0]"); // The first attribute value element.

                // Multiple attribute are available.
                if (is_array($attrslist) && !empty($attrslist[$index]) && count($attrslist[$index]) > 0) {
                    foreach ($attrslist[$index] as $i => $attrvalue) {
                        if ($i == 0) { // We already have the first element created in the form definition, so skip it.
                            continue;
                        }

                        $newattribute = $this->_form->createElement(
                            'text',
                            $name . "[$i]",
                            get_string('fieldcustomdata', 'block_dash'),
                            [
                                'size' => '50',
                                'placeholder' => 'course/view.php?id={PLACEHOLDER}',
                            ]
                        );
                        $element->_elements[] = $newattribute;
                    }
                }
            }
        }

        // Update the elements of the group with the new created elements.
        // Without this elements are not updated correctly. and the data will not export during the form submission.
        $element->setElements($element->_elements);
    }


    /**
     * Get the list of tables for main and join table selector.
     *
     * @return array
     */
    protected function get_tables_list() {
        global $DB;

        $dbtables = $DB->get_tables();
        $selecttables = [];

        // Tables to prevent to add. like the config and other core tables.
        $preventtables = ['config', 'config_log', 'config_plugins'];
        foreach ($dbtables as $dbtable) {
            if (!in_array($dbtable, $preventtables)) {
                $selecttables[$dbtable] = $dbtable;
            }
        }

        return $selecttables;
    }

    /**
     * Get the default data.
     *
     * This is the data that is prepopulated in the form at it loads, we automatically
     * fetch all the properties of the persistent however some needs to be converted
     * to map the form structure.
     *
     * Extend this class if you need to add more conversion.
     *
     * @return stdClass
     */
    protected function get_default_data() {
        $data = parent::get_default_data();

        $this->get_persistent()->update_properties_format($data);

        return $data;
    }

    /**
     * Get the alias for the join.
     *
     * @param bool $join
     * @return void
     */
    public static function get_alias($join = true) {

        if ($join == true) {
            $alias = 'jt{no}';
        } else {
            $alias = DASHADDON_DEVELOPER_MAIN_ALIAS;
        }

        return $alias;
    }

    /**
     * Get supported attributes to transform the data.
     *
     * @return array
     */
    public function get_attributes() {

        return [
            'block_dash\local\data_grid\field\attribute\bool_attribute',
            'block_dash\local\data_grid\field\attribute\date_attribute',
            'block_dash\local\data_grid\field\attribute\identifier_attribute',
            'block_dash\local\data_grid\field\attribute\image_attribute',
            'block_dash\local\data_grid\field\attribute\link_attribute',
            'block_dash\local\data_grid\field\attribute\linked_data_attribute',
            'block_dash\local\data_grid\field\attribute\moodle_url_attribute',
            'block_dash\local\data_grid\field\attribute\percent_attribute',
            'block_dash\local\data_grid\field\attribute\time_attribute',
            'block_dash\local\data_grid\field\attribute\user_image_url_attribute',
            'local_dash\data_grid\field\attribute\course_image_url_attribute',
            'local_dash\data_grid\field\attribute\timeago_attribute',
        ];
    }
}
