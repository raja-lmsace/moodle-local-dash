define(['jquery', 'core/fragment', 'core/modal_events',
    'core/notification', 'core/modal_save_cancel', 'core/str'], function ($, Fragment, ModalEvents, notification, SaveCancelModal, Str) {


    const ZoneConfig = function (zoneuniqueid, blockid, contextid) {
        var self = this;
        this.zoneuniqueid = zoneuniqueid;
        this.blockid = blockid;
        this.contextId = contextid;
        $zoneconfigid = '#' + zoneuniqueid;
        $(document).on('click', '.modal-body ' + $zoneconfigid, function(e) {
            console.log("Configure zones clicked");
            e.preventDefault();
            var blockid = $(this).data('blockid');
            self.openZoneConfig(blockid);
        });
    };


    /**
     * Open the zone configuration modal using Moodle fragment.
     * @param {int} blockid The block instance ID
     */
    ZoneConfig.prototype.openZoneConfig = function(blockid) {
        var self = this;
        SaveCancelModal.create({
            title: Str.get_string('configure_zones_title', 'block_dash'),
            large: true,
            body: self.getZoneBody(blockid)
        }).then(function(modal) {

            modal.getRoot().on(ModalEvents.bodyRendered, function() {
                self.initZoneConfigEvents(modal, blockid);
            });

            modal.getRoot().on(ModalEvents.save, e => {
                e.preventDefault();
                self.saveZoneConfiguration(modal);
            });

            // Handle hidden event.
            modal.getRoot().on(ModalEvents.hidden, () => {
                // Destroy when hidden.
                modal.destroy();
            });

            modal.show();

            return modal;
        }).catch(Notification.exception);
    };


    /**
     * Save complete zone configuration.
     * @param {int} blockid Block instance ID
     * @param {Object} modal The modal object
     */
    ZoneConfig.prototype.saveZoneConfiguration = function(modal) {
            var self = this;
            const form = modal.getRoot().find('form');
            if (!form) {
                notification.addNotification({
                    message: 'Form not found',
                    type: 'error'
                });
                return;
            }
            var formData = form.serialize();

            // Submit via fragment.
            Fragment.loadFragment('dashaddon_learningpath', 'handler', self.contextId, {
                method: 'zone_config',
                blockid: self.blockid,
                submitbutton: 1,
                formdata: formData,
            }).then(function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    modal.hide();
                    modal.destroy();
                    return response;
                } else {
                    notification.addNotification({
                        message: result.error || 'Error saving zones',
                        type: 'error'
                    });
                }
            }).catch(function(error) {
                modal.setBody(self.getZoneBody(blockid, JSON.stringify(form.serialize())));
                return modal;
            });
    };


    ZoneConfig.prototype.getZoneBody = function(blockid, formdata = null) {
        var desktoppath = document.querySelectorAll("select[name='config_preferences[desktoppath]']")[0];
        var mobilepath =  document.querySelectorAll("select[name='config_preferences[mobilepath]']")[0];
        var tabletpath =  document.querySelectorAll("select[name='config_preferences[tabletpath]']")[0];
        var paths = {
            desktoppath: desktoppath ? desktoppath.value : null,
            mobilepath: mobilepath ? mobilepath.value : null,
            tabletpath: tabletpath ? tabletpath.value : null
        };

        var self = this;
        var params = {
            blockid: self.blockid,
            method: 'zone_config',
            formdata: formdata,
            paths: JSON.stringify(paths)
        };
        return Fragment.loadFragment('dashaddon_learningpath', 'handler', self.contextId, params);
    };


    /**
     * Initialize events within the zone configuration modal.
     * @param {Object} modal The modal object
     * @param {int} blockid The block instance ID
     */
    ZoneConfig.prototype.initZoneConfigEvents = function(modal, blockid) {
        var modalRoot = modal.getRoot();

            $(document).on('click', '.zone-config-container .nav-link', function(e) {
            e.preventDefault();
            // Remove active class from all tabs and panes.
            $('.nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');

            // Add active class to clicked tab.
            $(this).addClass('active');

            // Show corresponding tab pane.
            var target = $(this).attr('href');
            $(target).addClass('show active');
        });

        // Zone hover highlighting.
        modalRoot.find('.zone-list .zone-item').on('mouseenter', function() {
            var zoneId = $(this).data('zone-id');
            var svgElement = modalRoot.find('svg [id="' + zoneId + '"]');
            svgElement.addClass('zone-highlight');
        }).on('mouseleave', function() {
            modalRoot.find('svg .zone-highlight').removeClass('zone-highlight');
        });

        // SVG element hover highlighting.
        modalRoot.find('svg [data-zone-type]').on('mouseenter', function() {
            var zoneId = $(this).attr('id');
            var listItem = modalRoot.find('.zone-list [data-zone-id="' + zoneId + '"]');
            listItem.addClass('zone-list-highlight');
        }).on('mouseleave', function() {
            modalRoot.find('.zone-list .zone-list-highlight').removeClass('zone-list-highlight');
        });

        // Zone enable/disable toggle.
        modalRoot.find('.zone-enabled-toggle').on('change', function() {
            // Update immediately without saving.
            var enabled = $(this).is(':checked');
            var zoneItem = $(this).closest('.zone-item');
            if (enabled) {
                zoneItem.removeClass('zone-disabled');
            } else {
                zoneItem.addClass('zone-disabled');
            }
        });
    };


    return {
        init: function (zoneuniqueid, blockid, contextid) {
            return new ZoneConfig(zoneuniqueid, blockid, contextid);
        }
    };
});