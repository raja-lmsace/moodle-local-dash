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
 * Behat Course sections steps definitions.
 *
 * @package    dashaddon_course_sections
 * @category   test
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode,
Behat\Mink\Exception\DriverException,
Behat\Mink\Exception\ExpectationException;

/**
 * Course sections steps definitions.
 *
 * @package    dashaddon_course_sections
 * @category   test
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_dashaddon_course_sections extends behat_base {
    /**
     * Check that the focus mode enable.
     *
     * @Given /^I check style css"(?P<color>(?:[^"]|\\")*)" "(?P<selector>(?:[^"]|\\")*)" "(?P<type>(?:[^"]|\\")*)"$/
     * @param string $value
     * @param string $selector
     * @param string $type
     * @throws ExpectationException
     */
    public function i_check_style_css($value, $selector, $type): void {
        $stylejs = "
            return (
                Y.one('{$selector}').getComputedStyle('{$type}')
            )
        ";
        if (strpos($this->evaluate_script($stylejs), $value) === false) {
            throw new ExpectationException("Doesn't working correct style", $this->getSession());
        }
    }
}
