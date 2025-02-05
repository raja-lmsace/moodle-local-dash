<?php
namespace dashaddon_roleassignments\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_name_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_originalname_attribute;
use dashaddon_roleassignments\local\block_dash\data_grid\field\attribute\role_description_attribute;
use lang_string;


class role_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('role', 'r');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_r', 'block_dash');
    }


    public function get_fields(): array {
        return [
            new field('id', new lang_string('role'), $this, 'r.id', [
                new identifier_attribute(),
            ]),
            new field('rolename', new lang_string('rolename', 'block_dash'), $this, 'r.id',
            [new role_name_attribute()]),
            new field('roleoriginalname', new lang_string('originalrolename', 'block_dash'), $this, 'r.id', [
                new role_originalname_attribute(),
            ]),
            new field('shortname', new lang_string('shortname'), $this, 'r.shortname'),
            new field('description', new lang_string('description'), $this, 'r.id', [new role_description_attribute()]),
        ];
    }
}
