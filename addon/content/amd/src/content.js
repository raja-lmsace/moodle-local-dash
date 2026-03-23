define(['jquery', 'core/modal_factory', 'core/str', 'core_form/modalform'], function ($, ModalFactory, Str, ModalForm) {

    const SELECTORS = {
        dashBlock: '.block[data-block="dash"]',
        addblock: '[data-target="dash-insertcontent"]',
    };

    class DashAddonContent {

        constructor(blockID) {
            this.blockID = blockID;
            this.SELECTORS = SELECTORS;
            this.SELECTORS.blockRoot = '#inst' + this.blockID;

            this.addActionListiners(); // Add actions listner in the widget.
            this.layoutID = '';
        }

        getRoot() {
            return document.querySelector(this.SELECTORS.blockRoot);
        }

        showContentForm() {

            var formClass = 'dashaddon_content\\contentform';

            const modalForm = new ModalForm({

                formClass: formClass,
                // Add as many arguments as you need, they will be passed to the form:
                args: { blockid: this.blockID, layoutid: this.layoutID },
                // Modal configurations, here set modal title.
                modalConfig: { title: Str.get_string('contentmodaltitle', 'block_dash') },
                // DOM element that should get the focus after the modal dialogue is closed:
                // returnFocus: element,
            });

            modalForm.show();

            // Listen to events if you want to execute something on form submit.
            // Event detail will contain everything the process() function returned:
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, function (e) {
                var form = e.target.querySelector('form');
                var blockID = (new FormData(form)).get('blockid');
                DashAddonContent.refresh(blockID);
            });
        }

        addActionListiners() {

            var self = this;

            var dashContent = this.getRoot().querySelector('.dashaddon-content');

            if (dashContent === null || typeof dashContent === undefined) {
                return null;
            }

            dashContent.addEventListener('click', function (e) {

                if (e.target.closest(SELECTORS.addblock)) {
                    e.preventDefault();
                    self.layoutID = e.target.closest(SELECTORS.addblock).dataset.layoutid;
                    self.showContentForm();
                }

            });

            return null;
        }

        /**
         * Trigger the filter form to submit. to refresh the course content.
         *
         * @param {Integer} blockID
         */
        static refresh(blockID) {
            // Quick fix. TODO: Need to implement the method in Dashinstance.js to referesh the content from anywhere.
            var block = '#inst' + blockID;
            if ($(block).find('select:eq(1)').length == 0) {
                $(block).find('.filter-form').append('<select style="display:none;"><option>1</option></select>');
            }

            $(block).find('.filter-form').find('select').trigger('change');
        }

    }

    return {

        init: function (blockID, contextID) {
            new DashAddonContent(blockID, contextID);
        }
    };
});
