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
 * Dashaddon skill graph - Manage competencies apearance.
 *
 * @module   dashaddon_skill_graph/skill_progress
 * @copyright 2023 bdecent GmbH <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/modal_factory', 'core/str', 'core_form/modalform'], function ($, ModalFactory, Str, ModalForm) {

    const SELECTORS = {
        table: 'table.competency-list',
        editskill: '[data-target="competency-setup"]',
        skillsRow: 'table.competency-list a.competency-setup'
    };

    class DashAddonSkillsProgress {

        constructor(skl) {

            this.SELECTORS = SELECTORS;
            this.competencyID = skl.dataset.competencyId;
            this.SELECTORS.root = SELECTORS.table + ' [data-competency-id="' + this.competencyID + '"]';
            this.addActionListiners();
        }

        getRoot() {
            return document.querySelector(this.SELECTORS.root);
        }

        showContentForm() {

            var formClass = 'dashaddon_skill_graph\\competencyform';

            const modalForm = new ModalForm({

                formClass: formClass,
                // Add as many arguments as you need, they will be passed to the form:
                args: { competencyid: this.competencyID },
                // Modal configurations, here set modal title.
                modalConfig: { title: Str.get_string('courseskills', 'tool_skills') },
            });

            modalForm.show();

            // Listen to events if you want to execute something on form submit.
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, function () {
                window.location.reload();
            });
        }


        addActionListiners() {

            var self = this;

            this.getRoot().addEventListener('click', function (e) {

                if (e.target.closest(SELECTORS.editskill)) {
                    e.preventDefault();
                    self.showContentForm();
                }
            });
        }

        /**
         * Add event listenrs.
         */
        static createInstances() {

            let lists = document.querySelectorAll(SELECTORS.editskill);

            if (lists !== null) {
                lists.forEach((skl) => {
                    new DashAddonSkillsProgress(skl);
                });
            }
        }

    }

    return {

        init: function () {
            DashAddonSkillsProgress.createInstances();
        }
    };
});
