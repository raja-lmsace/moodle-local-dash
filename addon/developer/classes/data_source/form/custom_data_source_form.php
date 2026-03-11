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

/**
 * Custom data source creation persistent form.
 */
class custom_data_source_form extends persistent_form
{
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
        $fields = [
            $mform->createElement('html', '<div class="select-fields-group">'),
            $mform->createElement('static', 'fieldstatichdr', $header),
            $mform->createElement('autocomplete', 'selectfield', get_string('field', 'block_dash'), $tablefields, $options),
            $mform->createElement('select', 'fieldattribute', get_string('fieldtransformdata', 'block_dash'), $attributes),
            $mform->createElement(
                'text',
                'attributevalue',
                get_string('fieldcustomdata', 'block_dash'),
                ['size' => '50', 'placeholder' => 'course/view.php?id={PLACEHOLDER}']
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

        $PAGE->requires->js_amd_inline(
            '
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
        '
        );
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
     * @param  bool $join
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
            'block_dash\local\data_grid\field\attribute\bool_attribute',
            'block_dash\local\data_grid\field\attribute\date_attribute',
            'block_dash\local\data_grid\field\attribute\identifier_attribute',
            'block_dash\local\data_grid\field\attribute\image_attribute',
            'block_dash\local\data_grid\field\attribute\link_attribute',
            'block_dash\local\data_grid\field\attribute\moodle_url_attribute',
            'block_dash\local\data_grid\field\attribute\percent_attribute',
            'block_dash\local\data_grid\field\attribute\time_attribute',
            'local_dash\data_grid\field\attribute\timeago_attribute',
        ];
    }
}
