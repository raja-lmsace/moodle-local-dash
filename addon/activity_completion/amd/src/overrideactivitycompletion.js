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
 * Override the activity completion.
 *
 * @module   dashaddon_activity_completion
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', "core/fragment", "core/modal_factory", "core/modal_events", "core/notification", "core/str", 'core/ajax'],
    (function ($, Fragment, ModalFactory, ModalEvents, notification, String, Ajax) {

        const SELECTORS = {
            dashBlock: '.block[data-block="dash"]',
            overridebtn: '.activity-completion-override',
        };

        /**
         * Override the activity completion in the activity completion data source.
         */
        class CompletionOverride {

            constructor(blockID) {
                this.blockID = blockID;
                this.SELECTORS = SELECTORS;
                this.SELECTORS.blockRoot = '#inst' + this.blockID;
                this.overrideactivitycompletion();
            }

            getRoot() {
                return document.querySelector(this.SELECTORS.blockRoot);
            }

            /**
             * Override the activity completion.
             */
            overrideactivitycompletion() {

                var self = this;
                var blockid = this.blockID;
                this.getRoot().addEventListener('click', function (e) {
                    var targetElement = e.target.closest(self.SELECTORS.overridebtn);
                    if (targetElement) {
                        e.preventDefault();

                        var data = {
                            userid: targetElement.getAttribute("data-userid"),
                            cmid: targetElement.getAttribute("data-cmid"),
                            newstate: targetElement.getAttribute("data-state"),
                        };

                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: String.get_string('confirm'),
                            body: String.get_string('completionstate', 'dashaddon_activity_completion'),
                            large: false
                        })
                            .then(function (modal) {
                                modal.show();
                                modal.getRoot().on(ModalEvents.save, e => {
                                    e.preventDefault();
                                    self.overridestate(data, blockid);
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
             * Override the activity completion status.
             *
             * @param {Object} data
             * @param {int} blockid
             */
            overridestate(data, blockid) {
                Ajax.call([{
                    methodname: 'core_completion_override_activity_completion_status',
                    args: data,
                    done: function () {
                        CompletionOverride.refresh(blockid);
                    }
                }]);
            }

            /**
             * Trigger the filter form to submit. to refresh the course content.
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
                new CompletionOverride(blockID);
            }
        };

    }));
