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
 * Custom behat step definitions - Dashaddon Programs.
 *
 * @package    dashaddon_programs
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode,
Behat\Mink\Exception\DriverException,
Behat\Mink\Exception\ExpectationException;

/**
 * Custom behat step definitions.
 */
class behat_dashaddon_programs extends behat_base {
    /**
     * Add bootstrap 5 dropdown attribute to ellipsis menu to make it work in behat tests.
     *
     * @When /^I add dropdown menu in program action$/
     */
    public function i_add_dropdown_menu_in_program_action() {
        global $CFG;

        if ($CFG->branch >= 500) {
            $this->evaluate_script(
                "document.querySelector('.program-category-selector')
                .parentElement.querySelector('[data-toggle=dropdown]')?.setAttribute('data-bs-toggle', 'dropdown');"
            );
        }
    }
}
