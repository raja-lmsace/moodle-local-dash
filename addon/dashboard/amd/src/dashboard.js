define(['jquery'], function ($) {

    var Dashaddondash = function () {
        this.managedashboardalign();
        this.initBlockVisibilityObserver();
    };

    Dashaddondash.prototype.initBlockVisibilityObserver = function () {
        // Create intersection observer to detect visible blocks
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Get block ID from the visible section
                    const blockId = entry.target.getAttribute('id');
                    // Update nav menu active state
                    this.updateNavActiveState(blockId);
                }
            });
        }, {
            // Observer options
            root: null, // viewport
            threshold: 0.5 // At least 50% visible
        });

        // Observe all block sections
        document.querySelectorAll('section[id^="inst"]').forEach((block) => {
            observer.observe(block);
        });
    };

    Dashaddondash.prototype.updateNavActiveState = function (blockId) {
        // Remove active class from all nav items
        $('.dash-nav-block .nav-link').removeClass('active');
        // Find matching nav item and add active class
        $(`.dash-nav-block .nav-link[href$="#${blockId}"]`).addClass('active');
    };

    Dashaddondash.prototype.managedashboardalign = function () {
        $("select[name=contexttype]").change(function (e) {
            var contexttype = $("select[name=contexttype]").val();
            this.Handleronpagenavigationcta(contexttype);
        }.bind(this));
        this.Handleronpagenavigationcta($("select[name=contexttype]").val());
    };

    Dashaddondash.prototype.Handleronpagenavigationcta = function (contexttype) {
        var $enrolmentctalink = $("select[name=ctalink] option[value='enrolment']");
        var $shopurlctalink = $("select[name=ctalink] option[value='shopurl']");
        if (contexttype == 'course') {
            $enrolmentctalink.show();
            $shopurlctalink.show();
        } else {
            $enrolmentctalink.hide();
            $shopurlctalink.hide();
        }
    };

    return {
        init: function () {
            new Dashaddondash();
        }
    };
});