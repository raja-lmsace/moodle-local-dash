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
define(["core/fragment","core/modal_factory","core/modal_events","core/notification","core/str", 'core/ajax'],
    (function(Fragment,ModalFactory,ModalEvents,notification,String, Ajax) {

    /**
     * Override the activity completion in the activity completion data source.
     */
    function overrideactivitycompletion() {
        document.body.addEventListener('click', function (e) {
            var targetElement = e.target.closest('.activity-completion-override');
            if (targetElement) {
                e.preventDefault();

                if (!targetElement) return;

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
                .then(function(modal) {
                    modal.show();
                    modal.getRoot().on(ModalEvents.save, e => {
                        e.preventDefault();
                        overridestate(data);
                        modal.hide();
                    });

                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });

                }).catch(notification.exception);
            }
        });
    }

    /**
     * Override the activity completion status.
     *
     * @param {Object} data
     */
    function overridestate(data) {
        Ajax.call([{
            methodname: 'core_completion_override_activity_completion_status',
            args: data,
            done: function() {
                window.location.reload();
            }
        }]);
    }

    return {
        init: function() {
            overrideactivitycompletion();
        }
    };

}));