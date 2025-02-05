<?php
namespace dashaddon_roleassignments\local\dash_framework\structure;

use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use lang_string;
use block_dash\local\data_grid\field\attribute\date_attribute;
use local_dash\data_grid\field\attribute\context\context_level_attribute;
use local_dash\data_grid\field\attribute\context\context_name_attribute;
use local_dash\data_grid\field\attribute\context\context_url_attribute;
use local_dash\data_grid\field\attribute\context\context_parent_attribute;



class role_context_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('context', 'ctx');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_ctx', 'block_dash');
    }


    public function get_fields(): array {
        return [
            new field('contextname', new lang_string('contextname', 'block_dash'), $this, 'ctx.id', [new context_name_attribute()]),
            new field('contexturl', new lang_string('contexturl', 'block_dash'), $this, 'ctx.id', [new context_url_attribute()]),
            new field('contextlevel', new lang_string('contextlevel', 'block_dash'), $this, 'ctx.id', [new context_level_attribute()]),
            new field('parent', new lang_string('parent', 'block_dash'), $this, 'ctx.id', [new context_parent_attribute()]),
        ];
    }
}
