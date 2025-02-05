<?php
namespace dashaddon_roleassignments\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use lang_string;
use block_dash\local\data_grid\field\attribute\date_attribute;


class role_assignments_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('role_assignments', 'ra');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ra', 'block_dash');
    }


    public function get_fields(): array {
        return [
            new field('timemodified', new lang_string('timemodified', 'block_dash'), $this, 'ra.timemodified', [
                new date_attribute(),
            ]),
        ];
    }
}
