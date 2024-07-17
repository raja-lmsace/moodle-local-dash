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
define(["core/fragment","core/modal_factory","core/modal_events","core/notification","core/str", 'core/ajax'],
    (function(Fragment,ModalFactory,ModalEvents,notification,String, Ajax) {

    /**
     * Grade activity action.
     */
    function activitygrade() {
        document.body.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('grade-activity')) {
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
                    body: getgradebodycontent(params),
                    large: false
                })
                .then(function(modal) {
                    modal.show();

                    modal.getRoot().on(ModalEvents.save, e => {
                        e.preventDefault();
                        modal.getRoot().find('form').submit();
                    });

                    modal.getRoot().on('submit', 'form', e => {
                        e.preventDefault();
                        submitFormData(params);
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
     * Submit form data.
     *
     * @param {object} params
     */
    function submitFormData(params) {
        var modalform = document.querySelectorAll('#activity-grade-action form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        Ajax.call([{
            methodname: 'dashaddon_activity_completion_grade_activity',
            args: {userid: params.userid, formdata: formData, cmid: params.cmid, gradeitemid: params.gradeitemid},
            done: function(response) {

                if (response.message) {
                    notification.addNotification({
                        message: response.message,
                        type: "error"
                    });
                }

                if (response.status) {
                    window.location.reload();
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
    function getgradebodycontent(params) {
        return Fragment.loadFragment('dashaddon_activity_completion', 'grade_activity_form', params.contextid, params);
    }

    /**
     * Initialize the activity grade action and rebind events on paginated content changes.
     */
    function init() {
        activitygrade();
    }

    return {
        init: init
    };

}));