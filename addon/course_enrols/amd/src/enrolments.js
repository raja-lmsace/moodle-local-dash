define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/templates', 'core/str', 'core/ajax', 'core/notification', 'block_dash/dash_instance'],
    function ($, Modal, ModalEvents, Fragment, Templates, Str, Ajax, Notification) {

        var contextid = null;

        var getRoot = null;

        var DashEnrolments = function (instanceID, contextID, uniqueID) {
            this.contextID = contextID;
            this.instanceID = instanceID;
            contextid = contextID
            this.currentPage = 0;
            getRoot = () => document.querySelector(this.instanceID);
            this.init(instanceID + " " + this.selectors.menu);
        }

        DashEnrolments.prototype.contextID = null;

        DashEnrolments.prototype.instanceID = null;

        DashEnrolments.prototype.selectors = {
            'menu': '.course-enrol-actionmenu .menu-item',
            'course': '.widget-course_enrols .course-item',
            statusElement: '[data-status]',
            enrolFormElement: '.course_enrol-enrolmentform',
            courseContainerElement: '.course-enrols-courses-list',
            datasetElement: '_enrolmentsdataset',
            reloadElements: '[data-action="reloadenrolments"]',
            pageElement: '.page-link'
        };

        DashEnrolments.prototype.getStatusContainer = function (link) {
            return link.closest(this.selectors.statusElement);
        };

        const getCourseContext = function (event) {
            return event.closest('.course-item').dataset.contextid;
        };

        DashEnrolments.prototype.getRoot = function () {
            return document.querySelector(this.instanceID);
        };

        DashEnrolments.prototype.getDataSet = function () {
            return document.querySelector(this.instanceID + this.selectors.datasetElement);
        }

        DashEnrolments.prototype.getPageLink = function () {
            return document.querySelector(this.instanceID + this.selectors.pageElement);
        }

        DashEnrolments.prototype.init = function (selector) {

            document.addEventListener('click', e => {
                const blockRoot = e.target.closest(this.instanceID);
                if (!blockRoot) {
                    return;
                }

                const link = e.target.closest('.menu-item');
                if (!link) {
                    return;
                }
                const action = link.getAttribute('data-action');
                const courseid = e.target.closest('.course-item').getAttribute('data-course');
                const ueid = link.getAttribute('rel');
                const courseContext = getCourseContext(e.target);
                if (action == 'editenrolment') {
                    e.preventDefault();
                    this.loadEditEnrolmentForm(link, ueid, parseInt(courseContext));
                } else if (action == 'showdetails') {
                    e.preventDefault();
                    this.showEnrolDetails(link, this);
                } else if (action == 'unenrol') {
                    e.preventDefault();
                    this.showUnenrolConfirmation(link);
                }
            });

            document.addEventListener('submit', (e) => {
                const form = e.target.closest(this.selectors.enrolFormElement);
                if (form !== null) {
                    e.preventDefault();
                    this.enrolUserCourse(e.target);
                }
            });

        };



        DashEnrolments.prototype.enrolUserCourse = function (form) {
            var formdata = new FormData(form);
            formdata = new URLSearchParams(formdata).toString();
            submitCourseEnrolForm(formdata).then((data) => {
                refresh();
                if (!data.result) {
                    throw data.result;
                }
                return data;
            });
        };

        DashEnrolments.prototype.initSelect2 = function () {
            $(this.instanceID).find('.select2').each(function (index, element) {
                let placeholder = null;
                if ($(element).find("option[value='-1']")) {
                    placeholder = {
                        id: '-1', // The value of the option.
                        text: $(element).find("option[value='-1']").text()
                    };
                }
                $(element).select2({
                    dropdownParent: this.getRoot(),
                    allowClear: true,
                    theme: 'bootstrap4',
                    placeholder: placeholder
                }).on('select2:unselecting', function () {
                    $(this).data('unselecting', true);
                }).on('select2:opening', function (e) {
                    if ($(this).data('unselecting')) {
                        $(this).removeData('unselecting');
                        e.preventDefault();
                    }
                });
            }.bind(this));
        };

        /**
         * Load edit enrolment form in the modal. Prevent the form submission and send the ajax request to
         * Enrol the user to course.
         *
         * @param  {} link
         * @param  {} userEnrolmentId
         * @param  {} courseContext
         */
        DashEnrolments.prototype.loadEditEnrolmentForm = function (link, userEnrolmentId, courseContext) {
            var params = { ueid: userEnrolmentId };

            const container = this.getStatusContainer(link);
            // var params = {widget: 'course_enrols', method: template, args: args};
            Modal.create({
                title: Str.get_string('edituserenrolment', 'block_dash', container.dataset.fullname),
                type: Modal.types.SAVE_CANCEL,
                body: '',
                large: true,
            }).then(modal => {
                this.modal = modal;

                // Handle save event.
                modal.getRoot().on(ModalEvents.save, e => {
                    // Don't close the modal yet.
                    e.preventDefault();

                    // Submit form data.
                    submitEditFormAjax(link, getBody, modal, userEnrolmentId, container.dataset).then(function () {
                        refresh();
                    });
                });

                // Handle hidden event.
                modal.getRoot().on(ModalEvents.hidden, () => {
                    // Destroy when hidden.
                    modal.destroy();
                });

                // Show the modal.
                modal.show();

                Fragment.loadFragment('dashaddon_course_enrols', 'user_enrolment_form', contextid, params).then((html, js) => {
                    modal.setBody(html);
                    Templates.runTemplateJS(js);
                    return html;
                }).catch(Notification.exception);

                return modal;
            });

            return true;
        };

        /**
         * Display the course enrolment details in the modal.
         *
         * @param  {HTMLElement} link
         * @param  {Object} prop
         */
        DashEnrolments.prototype.showEnrolDetails = (link, prop) => {

            const container = prop.getStatusContainer(link);

            const context = {
                editenrollink: '',
                statusclass: container.querySelector('span.badge').getAttribute('class'),
                ...container.dataset,
            };

            Modal.create({
                large: true,
                type: Modal.types.CANCEL,
                title: Str.get_string('enroldetails', 'block_dash'),
                body: Templates.render('dashaddon_course_enrols/status_details', context),
            })
                .then(modal => {


                    modal.show();

                    // Handle hidden event.
                    modal.getRoot().on(ModalEvents.hidden, () => modal.destroy());

                    return modal;
                })
                .catch(Notification.exception);
        };

        /**
         * Show and handle the unenrolment confirmation dialogue.
         *
         * @param {HTMLElement} link
         */
        DashEnrolments.prototype.showUnenrolConfirmation = function (link) {

            const container = this.getStatusContainer(link);
            const userEnrolmentId = link.getAttribute('rel');

            Modal.create({
                type: Modal.types.SAVE_CANCEL,
            })
                .then(modal => {
                    // Handle confirm event.
                    modal.getRoot().on(ModalEvents.save, e => {
                        // Don't close the modal yet.
                        e.preventDefault();

                        // Submit data.
                        submitUnenrolFormAjax(
                            link,
                            modal,
                            {
                                ueid: userEnrolmentId,
                            },
                            container.dataset
                        ).then(data => {
                            this.refresh();
                        });
                    });

                    // Handle hidden event.
                    modal.getRoot().on(ModalEvents.hidden, () => {
                        // Destroy when hidden.
                        modal.destroy();
                    });

                    // Display the delete confirmation modal.
                    modal.show();

                    const stringData = [
                        {
                            key: 'unenrol',
                            component: 'enrol',
                        },
                        {
                            key: 'unenrolconfirm',
                            component: 'enrol',
                            param: {
                                user: container.dataset.fullname,
                                course: container.dataset.coursename,
                                enrolinstancename: container.dataset.enrolinstancename,
                            }
                        }
                    ];

                    return Promise.all([Str.get_strings(stringData), modal]);
                })
                .then(([strings, modal]) => {
                    modal.setTitle(strings[0]);
                    modal.setBody(strings[1]);

                    return modal;
                })
                .catch(Notification.exception);
        };

        /**
         * Trigger the filter form to submit. to refresh the course content.
         */
        const refresh = function () {
            // Quick fix. TODO: Need to implement the method in Dashinstance.js to referesh the content from anywhere.
            if ($(getRoot()).find('select:eq(1)').length == 0) {
                $(getRoot()).find('.filter-form').append('<select style="display:none;"><option>1</option></select>');
            }
            console.log($(getRoot()).find('.filter-form').find('select'));

            $(getRoot()).find('.filter-form').find('select').trigger('change');
        }


        /**
         * Submit the edit dialogue. Cloned form core_user/status_field.js
         *
         * @param {HTMLElement} clickedLink
         * @param {Function} getBody
         * @param {Object} modal
         * @param {Number} userEnrolmentId
         * @param {Object} userData
         */
        const submitEditFormAjax = (clickedLink, getBody, modal, userEnrolmentId, userData) => {
            const form = modal.getRoot().find('form');

            return submitUserEnrolmentForm(form.serialize())
                .then(data => {
                    if (!data.result) {
                        throw data.result;
                    }
                    // Dismiss the modal.
                    modal.hide();
                    modal.destroy();

                    return data;
                })
                .then(() => {
                    return Str.get_string('enrolmentupdatedforuser', 'core_enrol', userData);
                })
                .catch(() => {
                    modal.setBody(getBody(contextid, userEnrolmentId, JSON.stringify(form.serialize())));

                    return modal;
                });
        };

        /**
         * Get the body fragment.
         *
         * @param {Number} contextId
         * @param {Number} ueid The user enrolment id
         * @param {Object} formdata
         * @returns {Promise}
         */
        const getBody = (contextId, ueid, formdata = null) => Fragment.loadFragment(
            'dashaddon_course_enrols',
            'user_enrolment_form',
            contextId,
            {
                ueid,
                formdata,
            }
        );

        /**
         * Submit the user enrolment form with the specified form data.
         *
         * @param {String} formdata
         * @return {Promise}
         */
        const submitUserEnrolmentForm = formdata => {
            return Ajax.call([{
                methodname: 'dashaddon_course_enrols_submit_user_enrolment_form',
                args: {
                    formdata,
                },
            }])[0];
        };

        /**
         * Submit the user enrolment form with the specified form data.
         *
         * @param {String} formdata
         * @return {Promise}
         */
        const unenrolUser = userEnrolmentId => {
            return Ajax.call([{
                methodname: 'dashaddon_course_enrols_unenrol_user_enrolment',
                args: {
                    ueid: userEnrolmentId,
                },
            }])[0];
        };

        /**
        * Submit the user enrolment form with the specified form data.
        *
        * @param {String} formdata
        * @return {Promise}
        */
        const submitCourseEnrolForm = formdata => {
            return Ajax.call([{
                methodname: 'dashaddon_course_enrols_enrol_courses',
                args: {
                    formdata
                },
            }])[0];
        };

        /**
         * Submit the unenrolment form.
         *
         * @param {HTMLElement} clickedLink
         * @param {Object} modal
         * @param {Object} args
         * @param {Object} userData
         */
        const submitUnenrolFormAjax = (clickedLink, modal, args, userData) => {
            return unenrolUser(args.ueid)
                .then(data => {
                    if (!data.result) {
                        // Display an alert containing the error message
                        Notification.alert(data.errors[0].key, data.errors[0].message);
                        return data;
                    }
                    refresh();
                    // Dismiss the modal.
                    modal.hide();
                    modal.destroy();
                    return data;
                })
                .then(() => {
                    return Str.get_string('unenrolleduser', 'core_enrol', userData);
                })
                .then(notificationString => {
                    return;
                })
                .catch(Notification.exception);
        };

        return DashEnrolments
    })
