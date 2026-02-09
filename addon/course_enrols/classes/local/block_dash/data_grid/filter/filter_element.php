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
 * Filters results to specific course completion status
 *
 * @package    dashaddon_course_enrols
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_course_enrols\local\block_dash\data_grid\filter;

use block_dash\local\data_grid\filter\filter_collection_interface;

/**
 * Filter element.
 */
trait filter_element {
    /**
     * Override this method and call it after creating a form element.
     *
     * @param filter_collection_interface $filtercollection
     * @param string $elementnameprefix
     * @param boolean $sort
     * @throws \Exception
     * @return string
     */
    public function create_filter_element(
        filter_collection_interface $filtercollection,
        $elementnameprefix = '',
        $sort = true
    ) {
        global $OUTPUT;
        $options = $this->options;

        if ($sort) {
            asort($options);
        }

        // If All option is present, send it to top.
        if (isset($options[self::ALL_OPTION])) {
            $options = [self::ALL_OPTION => $options[self::ALL_OPTION]] + $options;
        }

        $newoptions = [];
        foreach ($options as $value => $label) {
            $newoptions[] = ['value' => $value, 'label' => $label, 'selected' => in_array($value, $this->get_selected_options())];
        }

        $name = $elementnameprefix . $this->get_name();

        return $OUTPUT->render_from_template('dashaddon_course_enrols/filter_select', [
            'name' => $name,
            'options' => $newoptions,
            'multiple' => true,
        ]);
    }
}
