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

namespace dashaddon_programs\output\catelogue;

use enrol_programs\output\catalogue\renderer as programrenderer;

/**
 * Program catalogue renderer.
 *
 * @package   dashaddon_programs
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renderer of the program catelogue.
 *
 * @package   dashaddon_programs
 */
class renderer extends programrenderer {
    /**
     * Fetch the content of the program.
     *
     * @param \stdClass $program
     * @return string
     */
    public function get_program_content(\stdClass $program): string {
        $result = $this->render_program_content($program);
        return $result;
    }
}
