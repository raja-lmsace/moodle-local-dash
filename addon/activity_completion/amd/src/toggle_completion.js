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
 * Magic link expiration form define js.
 * @module   local_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(["core/fragment","core/modal_factory","core/modal_events","core/notification","core/str", 'core/ajax'],
    (function(Fragment,ModalFactory,ModalEvents,notification,String, Ajax) {


    /**
     * Override the activity completion in the activity completion data source.
     * @param {object} params
     */
    function togglecompletion (params) {
        const seletor = document.querySelectorAll(".activity-completion-override .custom-control-input");
        seletor.forEach((button) => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var cmid = e.currentTarget.getAttribute("data-id");
                console.log(cmid);
            //     var userid = e.currentTarget.getAttribute("data-id");
            //     params.userid = userid;
            //     ModalFactory.create({
            //         type: ModalFactory.types.SAVE_CANCEL,
            //         title: String.get_string('linkexpirytime', 'auth_magic'),
            //         body: getBodyContent(params),
            //         large: true
            //     })
            //     .then(function(modal) {

            //         modal.getRoot().on(ModalEvents.save, e => {
            //             e.preventDefault();
            //             modal.getRoot().find('form').submit();
            //         });

            //         modal.getRoot().on('submit', 'form', e => {
            //             e.preventDefault();
            //             submitFormData(userid);
            //             modal.hide();
            //         });

            //         modal.getRoot().on(ModalEvents.hidden, function() {
            //             modal.destroy();
            //         });
            //         modal.show();
            //         return modal;
            //     }).catch(notification.exception);
            });
        });
    }


    return {
        init: function(params) {
            togglecompletion(params);
        }
    };
}));