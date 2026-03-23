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
 * Unit test cases to test the accordion layout.
 *
 * @package    local_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash;

use block_dash\local\data_grid\data\strategy\grouped_strategy;
use block_dash\local\data_grid\field\field_definition_interface;
use local_dash\data_source\completions_data_source;
use dashaddon_courses\local\block_dash\courses_data_source;
use local_dash\layout\accordion_layout;

/**
 * Unit test for accordion layout.
 *
 * @group local_dash
 * @group bdecent
 * @group accordion_layout_test
 */
final class accordion_layout_test extends \advanced_testcase {
    /**
     * Test stuff() basic layout config.
     *
     * @covers ::stuff
     * @return void
     */
    public function test_stuff(): void {
        $this->resetAfterTest();

        $cat1 = $this->getDataGenerator()->create_category(['name' => 'Category 1']);
        $cat2 = $this->getDataGenerator()->create_category(['name' => 'Category 2']);

        $course1 = $this->getDataGenerator()->create_course([
            'name' => 'Course 1',
            'category' => $cat1->id,
        ]);
        $course2 = $this->getDataGenerator()->create_course([
            'name' => 'Course 2',
            'category' => $cat1->id,
        ]);
        $course3 = $this->getDataGenerator()->create_course([
            'name' => 'Course 3',
            'category' => $cat2->id,
        ]);

        $datasource = new courses_data_source(\context_system::instance());
        $layout = new accordion_layout($datasource);
        $datasource->set_layout($layout);

        $datasource->set_preferences([
            'groupby_field_definition' => 'c_id',
            'group_label_field_definition' => 'c_shortname',
        ]);
        $class = $layout->get_data_strategy();
        $this->assertEquals(grouped_strategy::class, get_class($class));
        $data = $datasource->get_data();
        $this->assertNotEmpty($data);
    }
}
