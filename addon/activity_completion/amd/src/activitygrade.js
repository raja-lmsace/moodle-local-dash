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
 * Override the activity grade.
 *
 * @module   dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', "core/fragment", "core/modal_factory", "core/modal_events", "core/notification", "core/str", 'core/ajax'],
    (function ($, Fragment, ModalFactory, ModalEvents, notification, String, Ajax) {

        const SELECTORS = {
            dashBlock: '.block[data-block="dash"]',
            gradebtn: '.grade-activity-btn',
        };

        /**
         * Grade the activity in the activity completion data source.
         */
        class Activitygrade {

            constructor(blockID) {
                this.blockID = blockID;
                this.SELECTORS = SELECTORS;
                this.SELECTORS.blockRoot = '#inst' + this.blockID;
                this.activitygrade();
            }

            getRoot() {
                return document.querySelector(this.SELECTORS.blockRoot);
            }

            /**
             * Grade activity action.
             */
            activitygrade() {

                var self = this;
                var blockid = this.blockID;
                this.getRoot().addEventListener('click', function (e) {
                    var gradebtn = e.target.closest(self.SELECTORS.gradebtn);
                    if (gradebtn) {

                        e.preventDefault();

                        var params = {
                            userid: e.target.getAttribute("data-userid"),
                            cmid: e.target.getAttribute("data-cmid"),
                            contextid: e.target.getAttribute("data-contextid"),
                            currentgrade: e.target.getAttribute("data-currentgrade"),
                            gradeitemid: e.target.getAttribute("data-gradeitemid"),
                        };

                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: String.get_string('activitygrade', 'dashaddon_activity_completion'),
                            body: self.getgradebodycontent(params),
                            large: false
                        })
                            .then(function (modal) {
                                modal.show();

                                modal.getRoot().on(ModalEvents.save, e => {
                                    e.preventDefault();
                                    modal.getRoot().find('form').submit();
                                });

                                modal.getRoot().on('submit', 'form', e => {
                                    e.preventDefault();
                                    self.submitFormData(params, blockid);
                                    modal.hide();
                                });

                                modal.getRoot().on(ModalEvents.hidden, function () {
                                    modal.destroy();
                                });

                                return modal;
                            }).catch(notification.exception);
                    }
                });
            }

            /**
             * Submit form data.
             *
             * @param {object} params
             * @param {int} blockid
             */
            submitFormData(params, blockid) {
                var modalform = document.querySelectorAll('#activity-grade-action form')[0];
                var formData = new URLSearchParams(new FormData(modalform)).toString();
                Ajax.call([{
                    methodname: 'dashaddon_activity_completion_grade_activity',
                    args: { userid: params.userid, formdata: formData, cmid: params.cmid, gradeitemid: params.gradeitemid },
                    done: function (response) {

                        if (response.message) {
                            notification.addNotification({
                                message: response.message,
                                type: "error"
                            });
                        }

                        if (response.status) {
                            Activitygrade.refresh(blockid);
                        }
                    }
                }]);
            }

            /**
             * Returns submit form data in load fragment.
             *
             * @param {object} params
             * @returns {Promise}
             */
            getgradebodycontent(params) {
                return Fragment.loadFragment('dashaddon_activity_completion', 'grade_activity_form', params.contextid, params);
            }

            /**
             * Trigger the filter form to submit. to refresh the course content.
             *
             * @param {int} blockid
             */
            static refresh(blockid) {
                var block = '#inst' + blockid;
                // Quick fix. TODO: Need to implement the method in Dashinstance.js to referesh the content from anywhere.
                if ($(block).find('select:eq(1)').length == 0) {
                    $(block).find('.filter-form').append('<select style="display:none;"><option>1</option></select>');
                }

                $(block).find('.filter-form').find('select').trigger('change');
            }

        }

        return {
            init: function (blockID) {
                new Activitygrade(blockID);
            }
        };

    }));
