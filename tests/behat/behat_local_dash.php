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
 * Custom behat step definitions.
 *
 * @package    local_dash
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
Behat\Mink\Exception\DriverException as DriverException,
Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Custom behat step definitions.
 */
class behat_local_dash extends behat_base {


    /**
     * Add the specified enrolment method to the specified course filling the form with the provided data.
     *
     * @Given /^I add "(?P<enrol_method>(?:[^"]|\\")*)" enrolment method in "(?P<course>(?:[^"]|\\")*)" dashwith:$/
     * @param string $enrolmethod The enrolment method being used
     * @param string $courseidentifier The courseidentifier such as short name
     * @param TableNode $table Enrolment details
     */
    public function i_add_enrolment_method_for_dashwith(string $enrolmethod, string $courseidentifier, TableNode $table): void {
        global $CFG;

        if ($CFG->branch >= "400") {
            $this->execute('behat_enrol::i_add_enrolment_method_for_with', [$enrolmethod, $courseidentifier, $table]);
        } else {
            $this->execute('behat_navigation::i_am_on_course_homepage', [$courseidentifier]);
            $this->execute('behat_enrol::i_add_enrolment_method_with', [$enrolmethod, $table]);
        }
    }

    /**
     * Go to current page setting item
     *
     * This can be used on front page, course, category or modules pages.
     *
     * @Given /^I navigate to settings in current page administration$/
     *
     * @throws ExpectationException
     * @return void
     */
    public function i_navigate_to_settings_in_current_page_administration() {
        global $CFG;

        $nodetext = ($CFG->branch >= "400") ? 'Settings' : 'Edit settings';
        $this->execute('behat_navigation::i_navigate_to_in_current_page_administration', [$nodetext]);

    }

}
