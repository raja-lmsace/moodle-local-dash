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
 * Boostrap cards layout2 for course format.
 *
 * @package   local_dash
 * @copyright 2019 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_dash\layout;

use block_dash\local\paginator;
use block_dash\local\layout\abstract_layout;
use block_dash\local\data_source\form\preferences_form;
use block_dash\local\data_grid\data\data_collection_interface;
use block_dash\local\data_grid\field\field_definition_factory;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\image_url_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;

/**
 * Edit enrolment action.
*/
define('CARD_LAYOUT_SLIDER_MODE', 'slider');

/**
 * Unenrol action.
*/
define('CARD_LAYOUT_MASONRY_MODE', 'masonry');

/**
 * Unenrol action.
*/
define('CARD_LAYOUT_NORMAL_MODE', 'none');



/**
 * Boostrap cards layout2 for course format.
 */
class cards_layout extends abstract_layout {
    /**
     * Get layout template filename.
     *
     * @return string
     */
    public function get_mustache_template_name() {
        return 'local_dash/layout_cards';
    }

    /**
     * If the template should display pagination links.
     *
     * @return bool
     */
    public function supports_pagination() {
        if (
            $this->get_data_source()->get_preferences('layoutmode') == CARD_LAYOUT_SLIDER_MODE
            || $this->get_data_source()->get_preferences('layoutmode') == CARD_LAYOUT_MASONRY_MODE
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * If the template fields can be hidden or shown conditionally.
     *
     * @return bool
     */
    public function supports_field_visibility() {
        return false;
    }

    /**
     * If the template should display filters (does not affect conditions).
     *
     * @return bool
     */
    public function supports_filtering() {
        return true;
    }

    /**
     * Allows layout to modified preferences values before exporting to mustache template.
     *
     * @param  array $preferences
     * @return array
     */
    public function process_preferences(array $preferences) {
        global $OUTPUT, $PAGE;
        if (isset($preferences['layoutmode'])) {
            if ($preferences['layoutmode'] == CARD_LAYOUT_SLIDER_MODE) {
                $sliderclass = '';
                $preferences['layout_slider'] = true;
                $preferences['autoplay'] = ($preferences['autoplay']) ? 'true' : 'false';
                $preferences['arrows'] = ($preferences['arrows']) ? 'true' : 'false';
                $sliderclass .= ($preferences['centerMode']) ? 'slider-center-mode' : '';
                $preferences['centerMode'] = ($preferences['centerMode']) ? 'true' : 'false';
                $preferences['dots'] = ($preferences['dots']) ? 'true' : 'false';
                $preferences['draggable'] = ($preferences['draggable']) ? 'true' : 'false';
                $preferences['fade'] = ($preferences['fade']) ? 'true' : 'false';
                $preferences['infinite'] = ($preferences['infinite']) ? 'true' : 'false';
                $preferences['swipeToSlide'] = ($preferences['swipeToSlide']) ? 'true' : 'false';
                $sliderclass .= ($preferences['variableWidth']) ? ' slider-variable-mode' : '';
                $sliderclass .= ($preferences['rows'] == 1) ? ' slider-n-rows' : '';
                $preferences['variableWidth'] = ($preferences['variableWidth']) ? 'true' : 'false';
                $preferences['vertical'] = ($preferences['vertical']) ? 'true' : 'false';
                $preferences['verticalSwiping'] = ($preferences['verticalSwiping']) ? 'true' : 'false';
                $preferences['centerPadding'] = isset($preferences['centerPadding']) ?
                    intval($preferences['centerPadding']) : '';
                $preferences['sliderclass'] = $sliderclass;
            } else if ($preferences['layoutmode'] == CARD_LAYOUT_MASONRY_MODE) {
                $preferences['layout_masonry'] = true;
                $preferences['masonrysearch'] = isset($preferences['masonrysearch']) &&
                    ($preferences['masonrysearch']) ? true : false;
                $preferences['masonrysort'] = isset($preferences['masonrysort']) &&
                    ($preferences['masonrysort']) ? true : false;
            } else if ($preferences['layoutmode'] == CARD_LAYOUT_NORMAL_MODE) {
                $preferences['layout_default'] = true;
            }
            // Column classes.
            $preferences['columnsclass'] = isset($preferences['columns']) ?
                local_dash_get_card_column_customclass($preferences['columns']) : '';

            // Columns.
            $preferences['columns'] = $preferences['columns'] ?? 1;

            $preferences['showdetailsarea'] = isset($preferences['details_area']) &&
                ($preferences['details_area'] != 'disabled') ? true : false;
            $preferences['detailareaexpand'] = isset($preferences['details_area']) &&
                ($preferences['details_area'] == 'expanding') ? true : false;
            $preferences['detailareafloating'] = isset($preferences['details_area']) &&
                ($preferences['details_area'] == 'floating') ? true : false;
            $preferences['detailareamodal'] = isset($preferences['details_area']) &&
                ($preferences['details_area'] == 'modal') ? true : false;
            if ($preferences['showdetailsarea']) {
                if (isset($preferences['details_area_size']) && ($preferences['details_area_size'] == 'fit_content')) {
                    $preferences['fitcontentdetailsarea'] = true;
                } else {
                    $preferences['fitcarddetailsarea'] = true;
                }
            }
        }
        return parent::process_preferences($preferences);
    }

    /**
     * Add form elements to the preferences form when a user is configuring a block.
     *
     * This extends the form built by the data source. When a user chooses a layout, specific form elements may be
     * displayed after a quick refresh of the form.
     *
     * Be sure to call parent::build_preferences_form() if you override this method.
     *
     * @param  \moodleform      $form
     * @param  \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function build_preferences_form(\moodleform $form, \MoodleQuickForm $mform) {
        global $CFG;
        if ($form->get_tab() == preferences_form::TAB_FIELDS) {
            // Register the dashcolorpicker element. - LMSACE.
            require_once($CFG->dirroot . '/blocks/dash/form/element-colorpicker.php');
            \MoodleQuickForm::registerElementType(
                'dashcolorpicker',
                $CFG->dirroot . '/blocks/dash/form/element-colorpicker.php',
                'moodlequickform_dashcolorpicker'
            );

            // Layout mode - LMSACE.
            $mform->addElement(
                'select',
                'config_preferences[layoutmode]',
                get_string('layoutmode', 'block_dash'),
                [
                'none' => get_string('strgrid', 'block_dash'),
                'slider' => get_string('strslider', 'block_dash'),
                'masonry' => get_string('strmasonry', 'block_dash'),
                ]
            );
            $mform->setType('config_preferences[layoutmode]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[layoutmode]', 'layoutmode', 'block_dash');
            $mform->setDefault('config_preferences[layoutmode]', 'none');

            $mform->addElement(
                'select',
                'config_preferences[columns]',
                get_string('columns', 'block_dash'),
                [
                12 => 1, 6 => 2, 4 => 3, 3 => 4, 25 => 5, 2 => 6, 1 => 12,
                ]
            );
            $mform->setType('config_preferences[columns]', PARAM_INT);
            $mform->addHelpButton('config_preferences[columns]', 'columns', 'block_dash');
            $mform->setDefault('config_preferences[columns]', 3);

            $mform->hideIf('config_preferences[columns]', 'config_preferences[layoutmode]', 'ne', 'none');

            // Display the slider options.
            $mform->addElement('advcheckbox', 'config_preferences[autoplay]', get_string('autoplay', 'block_dash'));
            $mform->setType('config_preferences[autoplay]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[autoplay]', 'autoplay', 'block_dash');
            $mform->setDefault('config_preferences[autoplay]', false);
            $mform->hideIf('config_preferences[autoplay]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('text', 'config_preferences[autoplaySpeed]', get_string('autoplaySpeed', 'block_dash'));
            $mform->setType('config_preferences[autoplaySpeed]', PARAM_INT);
            $mform->addHelpButton('config_preferences[autoplaySpeed]', 'autoplaySpeed', 'block_dash');
            $mform->setDefault('config_preferences[autoplaySpeed]', 3000);
            $mform->hideIf('config_preferences[autoplaySpeed]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[arrows]', get_string('arrows', 'block_dash'));
            $mform->setType('config_preferences[arrows]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[arrows]', 'arrows', 'block_dash');
            $mform->setDefault('config_preferences[arrows]', true);
            $mform->hideIf('config_preferences[arrows]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[centerMode]', get_string('centerMode', 'block_dash'));
            $mform->setType('config_preferences[centerMode]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[centerMode]', 'centerMode', 'block_dash');
            $mform->setDefault('config_preferences[centerMode]', false);
            $mform->hideIf('config_preferences[centerMode]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('text', 'config_preferences[centerPadding]', get_string('centerPadding', 'block_dash'));
            $mform->setType('config_preferences[centerPadding]', PARAM_INT);
            $mform->addHelpButton('config_preferences[centerPadding]', 'centerPadding', 'block_dash');
            $mform->setDefault('config_preferences[centerPadding]', 50);
            $mform->hideIf('config_preferences[centerPadding]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[dots]', get_string('dots', 'block_dash'));
            $mform->setType('config_preferences[dots]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[dots]', 'dots', 'block_dash');
            $mform->setDefault('config_preferences[dots]', false);
            $mform->hideIf('config_preferences[dots]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[draggable]', get_string('draggable', 'block_dash'));
            $mform->setType('config_preferences[draggable]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[draggable]', 'draggable', 'block_dash');
            $mform->setDefault('config_preferences[draggable]', true);
            $mform->hideIf('config_preferences[draggable]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[fade]', get_string('fade', 'block_dash'));
            $mform->setType('config_preferences[fade]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[fade]', 'fade', 'block_dash');
            $mform->setDefault('config_preferences[fade]', false);
            $mform->hideIf('config_preferences[fade]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[infinite]', get_string('infinite', 'block_dash'));
            $mform->setType('config_preferences[infinite]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[infinite]', 'infinite', 'block_dash');
            $mform->setDefault('config_preferences[infinite]', true);
            $mform->hideIf('config_preferences[infinite]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement(
                'select',
                'config_preferences[rows]',
                get_string('rows', 'block_dash'),
                array_combine(range(1, 10), range(1, 10))
            );
            $mform->setType('config_preferences[rows]', PARAM_INT);
            $mform->addHelpButton('config_preferences[rows]', 'rows', 'block_dash');
            $mform->setDefault('config_preferences[rows]', 1);
            $mform->hideIf('config_preferences[rows]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement(
                'select',
                'config_preferences[slidesPerRow]',
                get_string('slidesPerRow', 'block_dash'),
                array_combine(range(1, 10), range(1, 10))
            );
            $mform->setType('config_preferences[slidesPerRow]', PARAM_INT);
            $mform->addHelpButton('config_preferences[slidesPerRow]', 'slidesPerRow', 'block_dash');
            $mform->setDefault('config_preferences[slidesPerRow]', 1);
            $mform->hideIf('config_preferences[slidesPerRow]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('text', 'config_preferences[slidesToShow]', get_string('slidesToShow', 'block_dash'));
            $mform->setType('config_preferences[slidesToShow]', PARAM_INT);
            $mform->addHelpButton('config_preferences[slidesToShow]', 'slidesToShow', 'block_dash');
            $mform->setDefault('config_preferences[slidesToShow]', 1);
            $mform->hideIf('config_preferences[slidesToShow]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('text', 'config_preferences[slidesToScroll]', get_string('slidesToScroll', 'block_dash'));
            $mform->setType('config_preferences[slidesToScroll]', PARAM_INT);
            $mform->addHelpButton('config_preferences[slidesToScroll]', 'slidesToScroll', 'block_dash');
            $mform->setDefault('config_preferences[slidesToScroll]', 1);
            $mform->hideIf('config_preferences[slidesToScroll]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('text', 'config_preferences[speed]', get_string('speed', 'block_dash'));
            $mform->setType('config_preferences[speed]', PARAM_INT);
            $mform->addHelpButton('config_preferences[speed]', 'speed', 'block_dash');
            $mform->setDefault('config_preferences[speed]', 300);
            $mform->hideIf('config_preferences[speed]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[swipeToSlide]', get_string('swipeToSlide', 'block_dash'));
            $mform->setType('config_preferences[swipeToSlide]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[swipeToSlide]', 'swipeToSlide', 'block_dash');
            $mform->setDefault('config_preferences[swipeToSlide]', false);
            $mform->hideIf('config_preferences[swipeToSlide]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[variableWidth]', get_string('variableWidth', 'block_dash'));
            $mform->setType('config_preferences[variableWidth]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[variableWidth]', 'variableWidth', 'block_dash');
            $mform->setDefault('config_preferences[variableWidth]', false);
            $mform->hideIf('config_preferences[variableWidth]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[vertical]', get_string('vertical', 'block_dash'));
            $mform->setType('config_preferences[vertical]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[vertical]', 'vertical', 'block_dash');
            $mform->setDefault('config_preferences[vertical]', false);
            $mform->hideIf('config_preferences[vertical]', 'config_preferences[layoutmode]', 'ne', 'slider');

            $mform->addElement('advcheckbox', 'config_preferences[verticalSwiping]', get_string('verticalSwiping', 'block_dash'));
            $mform->setType('config_preferences[verticalSwiping]', PARAM_BOOL);
            $mform->addHelpButton('config_preferences[verticalSwiping]', 'verticalSwiping', 'block_dash');
            $mform->setDefault('config_preferences[verticalSwiping]', false);
            $mform->hideIf('config_preferences[verticalSwiping]', 'config_preferences[layoutmode]', 'ne', 'slider');

            // Display masonry options.
            $mform->addElement('advcheckbox', 'config_preferences[masonrysearch]', get_string('strmasonrysearch', 'block_dash'));
            $mform->setType('config_preferences[masonrysearch]', PARAM_BOOL);
            $mform->setDefault('config_preferences[masonrysearch]', false);
            $mform->hideIf('config_preferences[masonrysearch]', 'config_preferences[layoutmode]', 'ne', 'masonry');

            $mform->addElement('advcheckbox', 'config_preferences[masonrysort]', get_string('strmasonrysort', 'block_dash'));
            $mform->setType('config_preferences[masonrysort]', PARAM_BOOL);
            $mform->setDefault('config_preferences[masonrysort]', false);
            $mform->hideIf('config_preferences[masonrysort]', 'config_preferences[layoutmode]', 'ne', 'masonry');

            $options = [];
            // Check course or user.
            if (
                in_array(
                    "dashaddon_courses\local\dash_framework\structure\course_table",
                    array_map('get_class', $this->get_data_source()->get_tables())
                )
            ) {
                $handler = \core_course\customfield\course_handler::create();
                $fields = $handler->get_fields();
                foreach ($fields as $field) {
                    $alias = 'c_f_' . strtolower($field->get('shortname'));
                    $options[$alias] = format_string($field->get_formatted_name());
                }
            } else if (
                in_array(
                    "block_dash\local\dash_framework\structure\user_table",
                    array_map('get_class', $this->get_data_source()->get_tables())
                )
            ) {
                $fields = profile_get_custom_fields();
                foreach ($fields as $field) {
                    $alias = 'u_pf_' . strtolower($field->shortname);
                    $options[$alias] = format_string($field->name);
                }
            }
            $select = $mform->addElement(
                'select',
                'config_preferences[layoutstyles]',
                get_string('styleoptions', 'block_dash'),
                $options,
                ['class' => 'select2-form']
            );
                $mform->addHelpButton('config_preferences[layoutstyles]', 'styleoptions', 'block_dash');
            $select->setMultiple(true);

            $mform->addElement('html', '<hr>');

            // Normal grid iteam.
            $imageurlfields = [];
            foreach ($this->get_data_source()->get_available_fields() as $field) {
                if ($field->has_attribute(image_url_attribute::class) && !$field->has_attribute(linked_data_attribute::class)) {
                    $imageurlfields[] = $field;
                }
            }

            $courseimageurlfields = [];
            foreach ($this->get_data_source()->get_available_fields() as $field) {
                if ($field->has_attribute(image_attribute::class)) {
                    $courseimageurlfields[] = $field;
                }
            }

            $noneoption = [null => get_string('none', 'block_dash')];

            $mform->addElement(
                'select',
                'config_preferences[backgroundimagefield]',
                get_string('backgroundimagefield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options($imageurlfields)
                )
            );
            $mform->setType('config_preferences[backgroundimagefield]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[backgroundimagefield]', 'backgroundimagefield', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[imageurlfield]',
                get_string('imageurlfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $courseimageurlfields
                    )
                )
            );
            $mform->setType('config_preferences[imageurlfield]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[imageurlfield]', 'imageurlfield', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[imageoverlayfield]',
                get_string('imageoverlayfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[imageoverlayfield]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[imageoverlayfield]', 'imageoverlayfield', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[subheadingfield]',
                get_string('subheadingfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[subheadingfield]', PARAM_TEXT);
            $mform->addHelpButton('config_preferences[subheadingfield]', 'subheadingfield', 'block_dash');

            $mform->addElement(
                'select',
                'config_preferences[headingfield]',
                get_string('headingfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[headingfield]', PARAM_TEXT);

            $mform->addElement(
                'select',
                'config_preferences[bodyfield]',
                get_string('bodyfield1', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[bodyfield]', PARAM_TEXT);

            $mform->addElement(
                'select',
                'config_preferences[body2field]',
                get_string('bodyfield2', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[body2field]', PARAM_TEXT);

            $mform->addElement(
                'select',
                'config_preferences[body3field]',
                get_string('bodyfield3', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[body3field]', PARAM_TEXT);

            $mform->addElement(
                'select',
                'config_preferences[footerfield]',
                get_string('footerfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[footerfield]', PARAM_TEXT);

            $mform->addElement(
                'select',
                'config_preferences[footerrightfield]',
                get_string('footerrightfield', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[footerrightfield]', PARAM_TEXT);

            $mform->addElement('html', '<hr>');

            // Display the details area options.
            $detailsareaoptions = [
                'disabled' => get_string('strdisabled', 'block_dash'),
                'expanding' => get_string('strexpanding', 'block_dash'),
                'floating' => get_string('strfloating', 'block_dash'),
                'modal' => get_string('strmodal', 'block_dash'),
            ];
            $mform->addElement(
                'select',
                'config_preferences[details_area]',
                get_string('details_area', 'block_dash'),
                $detailsareaoptions
            );
            $mform->setType('config_preferences[details_area]', PARAM_TEXT);
            $mform->setDefault('config_preferences[details_area]', 'disabled');
            $mform->addHelpButton('config_preferences[details_area]', 'details_area', 'block_dash');

            $detailsareasizeoptions = [
                'like_item' => get_string('like_item', 'block_dash'),
                'fit_content' => get_string('fit_content', 'block_dash'),
            ];
            $mform->addElement(
                'select',
                'config_preferences[details_area_size]',
                get_string('details_area_size', 'block_dash'),
                $detailsareasizeoptions
            );
            $mform->setType('config_preferences[details_area_size]', PARAM_TEXT);
            $mform->setDefault('config_preferences[details_area_size]', 'like_item');
            $mform->addHelpButton('config_preferences[details_area_size]', 'details_area_size', 'block_dash');
            $mform->hideIf('config_preferences[details_area_size]', 'config_preferences[details_area]', 'eq', 'disabled');
            $mform->hideIf('config_preferences[details_area_size]', 'config_preferences[details_area]', 'eq', 'modal');

            $mform->addElement(
                'select',
                'config_preferences[details_title]',
                get_string('details_title', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_title]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_title]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'select',
                'config_preferences[details_body_1]',
                get_string('details_body_1', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_body_1]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_body_1]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'select',
                'config_preferences[details_body_2]',
                get_string('details_body_2', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_body_2]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_body_2]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'select',
                'config_preferences[details_body_3]',
                get_string('details_body_3', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_body_3]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_body_3]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'select',
                'config_preferences[details_footer_left]',
                get_string('details_footer_left', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_footer_left]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_footer_left]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'select',
                'config_preferences[details_footer_right]',
                get_string('details_footer_right', 'block_dash'),
                array_merge(
                    $noneoption,
                    field_definition_factory::get_field_definition_options(
                        $this->get_data_source()->get_available_fields()
                    )
                )
            );
            $mform->setType('config_preferences[details_footer_right]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_footer_right]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[details_bg_color]',
                get_string('details_bg_color', 'block_dash')
            );
            $mform->setType('config_preferences[details_bg_color]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_bg_color]', 'config_preferences[details_area]', 'eq', 'disabled');

            $mform->addElement(
                'dashcolorpicker',
                'config_preferences[details_text_color]',
                get_string('details_text_color', 'block_dash')
            );
            $mform->setType('config_preferences[details_text_color]', PARAM_TEXT);
            $mform->hideIf('config_preferences[details_text_color]', 'config_preferences[details_area]', 'eq', 'disabled');
        }

        parent::build_preferences_form($form, $mform);
    }

    /**
     * Modify objects after data is retrieved in the data source. This allows the layout to make decisions on the
     * data source and data grid.
     *
     * @param data_collection_interface $datacollection
     */
    public function after_data(data_collection_interface $datacollection) {
        foreach ($datacollection->get_child_collections('rows') as $childcollection) {
            $this->map_data(
                [
                    'bgimageurl' => $this->get_data_source()->get_preferences('backgroundimagefield'),
                    'imageurl' => $this->get_data_source()->get_preferences('imageurlfield'),
                    'imageoverlay' => $this->get_data_source()->get_preferences('imageoverlayfield'),
                    'heading' => $this->get_data_source()->get_preferences('headingfield'),
                    'subheading' => $this->get_data_source()->get_preferences('subheadingfield'),
                    'body' => $this->get_data_source()->get_preferences('bodyfield'),
                    'body2' => $this->get_data_source()->get_preferences('body2field'),
                    'body3' => $this->get_data_source()->get_preferences('body3field'),
                    'footer' => $this->get_data_source()->get_preferences('footerfield'),
                    'footerright' => $this->get_data_source()->get_preferences('footerrightfield'),
                    'tablesort' => $this->get_data_source()->get_preferences('default_sort'),
                    'detailheading' => $this->get_data_source()->get_preferences('details_title'),
                    'detailbody' => $this->get_data_source()->get_preferences('details_body_1'),
                    'detailbody2' => $this->get_data_source()->get_preferences('details_body_2'),
                    'detailbody3' => $this->get_data_source()->get_preferences('details_body_3'),
                    'detailfooter' => $this->get_data_source()->get_preferences('details_footer_left'),
                    'detailfooterright' => $this->get_data_source()->get_preferences('details_footer_right'),
                    'stylingoptions' => $this->get_data_source()->get_preferences('layoutstyles'),
                ],
                $childcollection
            );
        }
    }
}
