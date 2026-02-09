define(['jquery', 'core/fragment', 'core/modal_factory', 'core/modal_events',
    'core/notification', 'core/templates', 'core/modal_save_cancel'], function ($, Fragment, Modal, ModalEvents, notification, Templates, SaveCancelModal) {

    // Todo: Add padding on SVG;

    // var Path;

    const increasedView = 100;

    var Data;

    const Selectors = {
        dashBlock: "#dash-",
        svgParent: '#learningpath-svg-',
        classes: {}
    }

    const learningPath = function (uniqueid, data, contextid, grid) {
        var self = this;
        this.contextId = contextid;
        this.uniqueId = uniqueid;
        this.isgrid = grid;
        if (this.isgrid && parseInt(data.infoarea)) {
            this.processGridModel();
        }


        if (!data.courses) {
            return;
        }

        Data = data;

        Selectors.classes.dashBlock = Selectors.dashBlock + data.blockid;
        Selectors.classes.svgParent = Selectors.svgParent + uniqueid;

        // Find all SVG containers (they have data-svg-index from template)
        var svgContainers = document.querySelectorAll(Selectors.classes.dashBlock + ' .svg-block[data-svg-index]');

        Array.from(svgContainers).forEach((container) => {
            // Get SVG index and viewport from template data attributes
            var svgIndex = parseInt(container.getAttribute('data-svg-index'));
            var viewport = container.getAttribute('data-viewport');

            // Find the actual SVG element inside the container
            var svg = container.querySelector('svg');

            if (svg) {
                // Pass the svg element and index from template.
                new BuildSVGPath(svg, svgIndex, uniqueid, contextid, self, viewport);
            }
        });
    }

    learningPath.prototype.processGridModel = function () {
        var self = this;
        var grids = document.querySelectorAll("#learningpath-grid-" + self.uniqueId + " li.grid-block");
        if (grids) {
            grids.forEach((element) => {
                $(element).click(function (e) {
                    self.showCircleDetails(e);
                })
            });
        }
    }

    learningPath.prototype.getCourseDetails = function (target) {
        var courseid = target.getAttribute("data-courseid");
        var prevCourse = target.getAttribute("data-prevcourse");
        var nextCourse = target.getAttribute("data-nextcourse");
        var checkgrid = target.getAttribute("data-grid");

        // Detect if widget is in sidebar mode
        var widgetcontainer = target.closest('.learning-path-widget');
        var sidebar = widgetcontainer && widgetcontainer.classList.contains('sidebar');

        var params = {
            courseid: courseid,
            prevcourse: prevCourse,
            nextcourse: nextCourse,
            isgrid: (checkgrid != null) ? true : false,
            sidebar: sidebar,
        };
        return Fragment.loadFragment('dashaddon_learningpath', 'course_details_area', this.contextId, params);
    }

    learningPath.prototype.showCircleDetails = function (event) {
        var self = this;
        var learingPathID = event.target.closest(".learning-path-block").getAttribute("id");
        var widgetContainer = event.target.closest(".learning-path-widget");

        // Remove active class from all course elements.
        self.closeIconElementClass();

        // Add active class to current clicked element
        var clickedElement = event.currentTarget;
        if (clickedElement) {
            clickedElement.classList.add('element-active');
        }


        // Check if sidebar mode is active
        var isSidebarMode = widgetContainer && widgetContainer.classList.contains('sidebar');

        if (isSidebarMode) {
            // Display details in sidebar instead of modal
            this.showDetailsInSidebar(event, learingPathID);
        } else {
            // Display details in modal (original behavior for top position)
            Modal.create({
                title: "",
                type: "",
                body: this.getCourseDetails(event.currentTarget),
                large: false
            }).then(function (modal) {
                modal.show();
                modal.getRoot().on(ModalEvents.bodyRendered, function () {
                    var courseNavigation = document.querySelectorAll(".modal-body .pagination li");
                    if (courseNavigation) {
                        courseNavigation.forEach((element) => {
                            element.addEventListener("click", (e) => {
                                var circleid = e.currentTarget.getAttribute("data-circle");
                                var navigateHandler = document.querySelector("#" + learingPathID + " ." + circleid);
                                modal.destroy();
                                if (navigateHandler) {
                                    navigateHandler.click();
                                }
                            });
                        });
                    }

                });
                modal.getRoot().on(ModalEvents.hidden, function () {
                    modal.destroy();
                });
                return modal;
            }).catch(notification.exception);
        }
    }

    learningPath.prototype.closeIconElementClass = function () {
        var self = this;
        var allCourseElements = document.querySelectorAll(
            "#learningpath-svg-" + self.uniqueId + " .course-circle, " +
            "#learningpath-svg-" + self.uniqueId + " .course-zone, " +
            "#learningpath-svg-tab-" + self.uniqueId + " .course-circle, " +
            "#learningpath-svg-tab-" + self.uniqueId + " .course-zone, " +
            "#learningpath-svg-mob-" + self.uniqueId + " .course-circle, " +
            "#learningpath-svg-mob-" + self.uniqueId + " .course-zone, " +
            "#learningpath-grid-" + self.uniqueId + " .grid-block"
        );
        if (allCourseElements) {
            console.log(allCourseElements);
            allCourseElements.forEach(function(el) {
                el.classList.remove('element-active');
            });
        }
    }

    learningPath.prototype.showDetailsInSidebar = function (event, learingPathID) {
        var self = this;
        var detailsContainer = document.getElementById("learningpath-course-details-" + this.uniqueId);
        var detailsContent = document.getElementById("learningpath-details-content-" + this.uniqueId);
        var infoArea = document.getElementById("learningpath-info-area-" + this.uniqueId);
        var sidebarCollapse = document.getElementById("learningpath-sidebar-" + this.uniqueId);
        var toggleButton = document.querySelector('.learning-path-widget.sidebar .learningpath-sidebar-' + this.uniqueId);

        if (!detailsContainer || !detailsContent || !infoArea) {
            return;
        }


        if (toggleButton) {
            toggleButton.onclick = function() {
                // Remove active class from all course elements.
                self.closeIconElementClass();
            };
        }

        // Function to load and show details
        var loadAndShowDetails = function() {
            // Load course details
            self.getCourseDetails(event.currentTarget).then(function(html) {
                detailsContent.innerHTML = html;

                // Hide info area and toggle button, show details area
                infoArea.style.display = 'none';
                detailsContainer.style.display = 'block';
                if (toggleButton) {
                    toggleButton.style.display = 'none';
                }

                // Scroll to top of sidebar
                if (sidebarCollapse.scrollTop) {
                    sidebarCollapse.scrollTop = 0;
                }

                // Set up navigation handlers
                var courseNavigation = detailsContent.querySelectorAll(".pagination li");
                if (courseNavigation) {
                    courseNavigation.forEach((element) => {
                        element.addEventListener("click", (e) => {
                            var circleid = e.currentTarget.getAttribute("data-circle");
                            var navigateHandler = document.querySelector("#" + learingPathID + " ." + circleid);
                            if (navigateHandler) {
                                navigateHandler.click();
                            }
                        });
                    });
                }
            }).catch(notification.exception);
        };

        // Ensure sidebar is expanded first, then load details
        if (!$(sidebarCollapse).hasClass('show')) {
            // Sidebar is collapsed, open it and wait for it to finish
            $(sidebarCollapse).one('shown.bs.collapse', function() {
                // Sidebar is now fully opened, load details
                loadAndShowDetails();
            });
            $(sidebarCollapse).collapse('show');
        } else {
            // Sidebar is already open, load details immediately
            loadAndShowDetails();
        }

        // Set up back button handler
        var backBtn = detailsContainer.querySelector('.details-back');
        if (backBtn) {
            backBtn.onclick = function() {
                detailsContainer.style.display = 'none';
                infoArea.style.display = 'block';
                if (toggleButton) {
                    toggleButton.style.display = 'block';
                }
            };
        }

        // Set up close button handler
        var closeBtn = detailsContainer.querySelector('.details-close');
        if (closeBtn) {
            closeBtn.onclick = function() {
                detailsContainer.style.display = 'none';
                infoArea.style.display = 'block';
                if (toggleButton) {
                    toggleButton.style.display = 'block';
                }
                $(sidebarCollapse).collapse('hide');

                // Remove active class from all course elements.
               self.closeIconElementClass();
            };
        }
    }

    class BuildSVGPath {


        constructor(svg, index, uniqueid, contextid, learningPath, viewport) {
            this.svg = svg;
            this.index = index;
            this.uniqueid = uniqueid;
            this.contextId = contextid;
            this.learningPath = learningPath;
            this.viewport = viewport;

            this.path = null;
            this.pathLength = null;
            this.startPoint = null;
            this.endPoint = null;
            this.setupSVGSize();

            // Check positioning mode from Data object.
            if (Data.positioning === 'zones') {
               this.createZonePositioning();
            } else {
                // Default: path-based positioning
                this.createCoursePaths();
            }
            this.processCircleModel();
        }


        /**
         * Create zone-based positioning for courses with shape transformation.
         */
        createZonePositioning() {
            var self = this;
            // Get zone configurations for this block.
            if (!Data.zoneconfigs || Data.zoneconfigs.length === 0) {
                return;
            }

            // Create defs element for patterns (reusing existing pattern system).
            var defs = this.svg.querySelector('defs');
            if (!defs) {
                defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                this.svg.appendChild(defs);
            }

            var imageWidth = Data.courseimgwidth;
            var imageHeight = Data.courseimgheight;

            // Check if we should use original SVG shapes.
            var useSvgShape = Data.defaultshape === 'svgshape';

            // This prevents issues with DOM changes affecting our element selection
            var elementMap = {};

            // Get all unique zone types from configs
            var zoneTypes = {};
            Data.zoneconfigs[this.viewport].forEach(function(config) {
                zoneTypes[config.zonetype] = true;
            });


            // For each zone type, get all elements and store them with a marker
            Object.keys(zoneTypes).forEach(function(zoneType) {
                var elements = Array.from(self.svg.querySelectorAll(zoneType));
                elements.forEach(function(element, index) {
                    var key = zoneType + '_' + index;
                    // Mark the element with a unique data attribute so we can identify it later
                    element.setAttribute('data-original-zone-key', key);
                    elementMap[key] = element;
                });
            });


            //Process each zone config
            Data.zoneconfigs[this.viewport].forEach(function(zoneConfig) {
                if (!parseInt(zoneConfig.enabled) || !zoneConfig.courseid) {
                    return;
                }

                var zoneType = zoneConfig.zonetype;
                var zoneIndex = zoneConfig.zoneindex;
                var configKey = zoneType + '_' + zoneIndex;

                // Get the original element from our pre-built map
                var originalElement = elementMap[configKey];
                if (!originalElement) {
                    console.log('Element not found in map:', configKey);
                    return;
                }

                // Check if this element was already transformed (it will have data-zone="transformed" attribute)
                if (originalElement.getAttribute('data-zone') === 'transformed') {
                    console.log('Element already transformed, skipping:', configKey);
                    return;
                }

                // Check if element is still in the DOM (has a parent)
                if (!originalElement.parentNode) {
                    console.log('Element no longer in DOM:', configKey);
                    return;
                }

                // Find the course data.
                var course = Data.courses.find(c => c.info.id == zoneConfig.courseid);

                if (!course) {
                    console.log('Course not found:', zoneConfig.courseid);
                    return;
                }

                // Calculate center position of the original zone element.
                var centerPos = self.calculateZoneCenter(originalElement);

                // Calculate radius based on original element size.
                //var bbox = originalElement.getBBox();
                var bbox = self.getSafeBBox(originalElement);
                var radius = Math.min(bbox.width, bbox.height) / 2;

                // Create pattern for course image if not using dot or number visual.
                var isVisualNumber = Data.coursevisual === 'number' || Data.isvisualnumber;
                var isVisualimg = Data.coursevisual === 'courseimg';
                var isVisualcustom = Data.coursevisual === 'custom';

                var courseIndex = Data.courses.findIndex(c => c.info.id == zoneConfig.courseid);

                var prevCourseId = zoneConfig.prevcourse || 0;
                var prevCourseItem = prevCourseId > 0 ? "circle-course-" + prevCourseId : "";

                var nextCourseId = zoneConfig.nextcourse || 0;
                var nextCourseItem = nextCourseId > 0 ? "circle-course-" + nextCourseId : "";

                var parentElement = originalElement.parentNode;
                var targetElement = null;

                if (useSvgShape) {
                    // Use original SVG shape - just add attributes and styling
                    originalElement.classList.add('course-circle', 'course-zone', 'clickable-zone');
                    originalElement.classList.add('circle-course-' + course.info.id);

                    var tag = originalElement.tagName.toLowerCase();
                    
                    if (tag === 'polygon') {
                        originalElement.classList.add('polygon-zone');
                    } else if (tag === 'ellipse') {
                        originalElement.classList.add('ellipse-zone');
                    } else if (tag === 'rect') {
                        originalElement.classList.add('rect-zone');
                    }

                    originalElement.setAttribute('data-courseid', course.info.id);
                    originalElement.setAttribute('data-zone', 'transformed'); // Mark as transformed
                    originalElement.setAttribute('data-zonetype', zoneConfig.zonetype);
                    originalElement.setAttribute('data-zoneindex', zoneConfig.zoneindex);
                    originalElement.setAttribute('data-prevcourse', prevCourseId);
                    originalElement.setAttribute('data-prevcourse-item', prevCourseItem);
                    originalElement.setAttribute('data-nextcourse', nextCourseId);
                    originalElement.setAttribute('data-nextcourse-item', nextCourseItem);

                    // Apply completion status styling
                    var completionStatus = course.report.inprogress ? 'inprogress' : 'notstarted';
                    completionStatus = course.report.completed ? 'completed' : completionStatus;

                    if (course.report.unavailable) {
                        completionStatus = 'unavailable';
                    } else if (course.report.available) {
                        completionStatus = 'available';
                    } else if (course.report.failed) {
                        completionStatus = 'failed';
                    } else if (course.report.completed) {
                        completionStatus = 'completed';
                    } else if (course.report.inprogress) {
                        completionStatus = 'inprogress';
                    } else {
                        completionStatus = 'notstarted';
                    }

                    originalElement.classList.add(completionStatus);
                    originalElement.setAttribute('data-course-status', completionStatus);

                    // Apply stroke color based on status
                    if (course.statuscolor) {
                        originalElement.setAttribute('stroke', course.statuscolor);
                        originalElement.setAttribute('stroke-width', '6');
                    }
                    // Keep original fill or apply pattern if needed
                    if (!Data.dotimg && isVisualimg) {
                        var imagePath = course.img;
                        var image = self.createImage(imagePath);
                        var patternID = 'dash-' + Data.blockid + '-course-' + course.info.id + '-pattern-zone-' + self.index;
                        var pattern = self.createPattern(image, patternID);
                        defs.appendChild(pattern);
                        originalElement.setAttribute('fill', 'url(#' + patternID + ')');
                    } else if (isVisualcustom && course.statuscolor) {
                        originalElement.setAttribute('fill', course.statuscolor);
                    }

                    targetElement = originalElement;

                } else {
                    // Transform to custom shape - existing behavior

                    // Create pattern for course image if not using dot or number visual.
                    var patternID = false;

                    if (!Data.dotimg && isVisualimg) {
                        var imagePath = course.img;
                        var image = self.createImage(imagePath);
                        patternID = 'dash-' + Data.blockid + '-course-' + course.info.id + '-pattern-zone-' + self.index;
                        var pattern = self.createPattern(image, patternID);
                        defs.appendChild(pattern);
                    } else if (!Data.dotimg && isVisualcustom) {
                        patternID = false;
                    }

                    // Create point object similar to path-based positioning.
                    var point = {
                        x: centerPos.x,
                        y: centerPos.y,
                        length: 0 // Not used in zone mode.
                    };

                    // Reuse existing createShape() method to create the shape element.
                    var shapeElement = self.createShape(point, course, patternID, radius, Data.courses, courseIndex);

                    if (!shapeElement) {
                        console.log('Could not create shape for course:', course.info.id);
                        return;
                    }

                    shapeElement.setAttribute('data-courseid', course.info.id);
                    shapeElement.setAttribute('data-zone', 'transformed'); // Mark as transformed
                    shapeElement.setAttribute('data-zonetype', zoneConfig.zonetype);
                    shapeElement.setAttribute('data-zoneindex', zoneConfig.zoneindex);
                    shapeElement.setAttribute('stroke-width', '2');
                    shapeElement.classList.add('course-zone', 'clickable-zone');

                    shapeElement.setAttribute('data-prevcourse', prevCourseId);
                    shapeElement.setAttribute('data-prevcourse-item', prevCourseItem);
                    shapeElement.setAttribute('data-nextcourse', nextCourseId);
                    shapeElement.setAttribute('data-nextcourse-item', nextCourseItem);

                    // Replace original zone element with new shape.
                    parentElement.replaceChild(shapeElement, originalElement);

                    targetElement = shapeElement;
                }

                // If using number visual, create text overlay.
                if (isVisualNumber && course.coursenumber) {
                    var numberText = self.createCourseNumber(centerPos, course, radius);
                    // Insert text right after the shape element.
                    if (targetElement && parentElement) {
                        parentElement.insertBefore(numberText, targetElement.nextSibling);
                    }
                }

                // If course has an icon, create icon overlay.
                if (course.icon) {
                    var iconElement = self.createIconOverlay(centerPos, course, radius);
                    if (iconElement && targetElement && parentElement) {
                        parentElement.insertBefore(iconElement, targetElement.nextSibling);
                    }
                }

                var infoicon = self.createInfoAreaIcon(centerPos, course, radius);
                if (infoicon) {
                    parentElement.insertBefore(infoicon, targetElement.nextSibling);
                }

            });
        };


        createInfoAreaIcon(centerPos, course, radius) {
             // Create foreignObject to embed HTML icon
            var foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');

            var iconSize = radius * 0.55; // Icon container is 20% of shape radius.

            foreignObject.setAttribute('x', (centerPos.x - iconSize / 2));
            foreignObject.setAttribute('y', (centerPos.y - radius - iconSize));
            foreignObject.setAttribute('width', iconSize);
            foreignObject.setAttribute('height', iconSize);
            foreignObject.setAttribute('class', 'course-icon-overlay');
            foreignObject.setAttribute('pointer-events', 'all'); // Allow clicks to pass through to shape

            // Create div container for icon
            var div = document.createElement('div');
            div.style.width = '100%';
            div.style.height = '100%';
            div.style.alignItems = 'top';
            div.style.borderRadius = '50%';
            div.style.justifyContent = 'center';
            div.style.fontSize = (iconSize * 0.8) + 'px'; // Icon size is 60% of container
            div.innerHTML = '<i class="fa fa-caret-down"></i>'; // Icon HTML from PHP (e.g., <i class="fa fa-home"></i>)

            foreignObject.appendChild(div);
            return foreignObject;
        }


        /**
         * Create icon overlay for course (for zone positioning)
         *
         * @param {object} centerPos Center position {x, y}
         * @param {object} course Course data
         * @param {number} radius Radius of the shape
         * @returns {SVGElement|null} Foreign object with icon or null
         */
        createIconOverlay(centerPos, course, radius) {
            if (!course.icon) {
                return null;
            }

            // Create foreignObject to embed HTML icon
            var foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');

            var iconSize = radius * 1.1; // Icon container is 120% of shape radius

            foreignObject.setAttribute('x', (centerPos.x - iconSize / 2) + 3);
            foreignObject.setAttribute('y', (centerPos.y - iconSize / 2));
            foreignObject.setAttribute('width', iconSize);
            foreignObject.setAttribute('height', iconSize);
            foreignObject.setAttribute('class', 'course-icon-overlay');
            foreignObject.setAttribute('pointer-events', 'none'); // Allow clicks to pass through to shape

            // Create div container for icon
            var div = document.createElement('div');
            div.style.width = '100%';
            div.style.height = '100%';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.fontSize = (iconSize * 0.6) + 'px'; // Icon size is 60% of container
            div.style.color = '#ffffff';
            div.innerHTML = course.icon; // Icon HTML from PHP (e.g., <i class="fa fa-home"></i>)

            foreignObject.appendChild(div);

            return foreignObject;
        }


        /**
         * Calculate center position of a zone element.
         */
        calculateZoneCenter(element) {
            var self = this;
            var tagName = element.tagName.toLowerCase();
            var position = { x: 0, y: 0 };

            try {
                // Get bounding box for local (untransformed) coordinates
                //var bbox = element.getBBox();
                var bbox = self.getSafeBBox(element);

                // Calculate center in local coordinates
                var localCenterX = bbox.x + (bbox.width / 2);
                var localCenterY = bbox.y + (bbox.height / 2);

                // Check if element has transform attribute
                var transformAttr = element.getAttribute('transform');

                if (transformAttr) {
                    // Try MATRIX transform first: matrix(a b c d e f)
                    var matrixMatch = transformAttr.match(/matrix\(([^)]+)\)/);
                    if (matrixMatch) {
                        var values = matrixMatch[1].trim().split(/[\s,]+/).map(parseFloat);
                        if (values.length === 6) {
                            // Matrix format: matrix(a, b, c, d, e, f)
                            var a = values[0], b = values[1], c = values[2], d = values[3];
                            var e = values[4], f = values[5]; // Translation values

                            position.x = (a * localCenterX) + (c * localCenterY) + e;
                            position.y = (b * localCenterX) + (d * localCenterY) + f;
                        } else if (values.length < 6) {
                            var len = values.length;
                            position.x = values[len - 2];
                            position.y = values[len - 1];
                        } else {
                            position.x = localCenterX;
                            position.y = localCenterY;
                        }
                    } else {
                        // Try TRANSLATE transform: translate(x y)
                        var translateMatch = transformAttr.match(/translate\(([^\s,]+)[\s,]+([^)]+)\)/);

                        if (translateMatch) {
                            var translateX = parseFloat(translateMatch[1]) || 0;
                            var translateY = parseFloat(translateMatch[2]) || 0;

                            // Simple translation: add to local center
                            position.x = localCenterX + translateX;
                            position.y = localCenterY + translateY;

                        } else {
                            // No recognizable transform, use local center
                            position.x = localCenterX;
                            position.y = localCenterY;
                        }
                    }
                } else {
                    // No transform attribute, use local center directly
                    position.x = localCenterX;
                    position.y = localCenterY;
                }

            } catch (e) {
                // Fallback
                try {
                    //var bbox = element.getBBox();
                    var bbox = self.getSafeBBox(element);
                    position.x = bbox.x + (bbox.width / 2);
                    position.y = bbox.y + (bbox.height / 2);
                } catch (e2) {
                    console.error('getBBox also failed:', e2);
                }
            }

            return position;
        }


        /**
         * Calculate center of polygon from points string
         */
        calculatePolygonCenter(pointsStr) {
            if (!pointsStr) {
                return {x: 0, y: 0};
            }

            var coords = pointsStr.trim().split(/[\s,]+/).filter(c => c);
            var sumX = 0, sumY = 0, count = 0;

            for (var i = 0; i < coords.length; i += 2) {
                if (coords[i] && coords[i + 1]) {
                    sumX += parseFloat(coords[i]);
                    sumY += parseFloat(coords[i + 1]);
                    count++;
                }
            }

            return {
                x: count > 0 ? sumX / count : 0,
                y: count > 0 ? sumY / count : 0
            };
        }

        /**
         * Create course number text overlay for zones
         */
        createCourseNumberForZone(centerPos, course, zoneElement) {
            var self = this;
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');

            // Get approximate size for text scaling
            //var bbox = zoneElement.getBBox();
            var bbox = self.getSafeBBox(zoneElement);
            var size = Math.min(bbox.width, bbox.height);
            var fontSize = size * 0.3;

            // Position text at center of zone
            text.setAttribute('x', centerPos.x);
            text.setAttribute('y', centerPos.y);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('dominant-baseline', 'central');
            text.setAttribute('class', 'course-number-text zone-number-text');
            text.setAttribute('pointer-events', 'none');

            // Style the text
            text.style.fontSize = fontSize + 'px';
            text.style.fontWeight = 'bold';
            text.style.fill = '#ffffff';
            text.style.stroke = '#000000';
            text.style.strokeWidth = '2px';
            text.style.paintOrder = 'stroke';

            // Add the course number
            text.textContent = course.coursenumber || '';

            return text;
        }


        /**
         * Process clickable elements (both zones and course images)
         */
        processClickableElements() {
            var self = this;
            // Handle course image clicks
            var courseImages = this.svg.querySelectorAll(".course-circle");
            if (courseImages) {
                courseImages.forEach((element) => {
                    $(element).click(function (e) {
                        e.preventDefault();
                        self.handleCourseClick(e);
                    });
                });
            }

            // Handle zone element clicks (for zone-based positioning).
           /*  var zoneElements = this.svg.querySelectorAll(".course-zone");
            if (zoneElements) {
                zoneElements.forEach((element) => {
                    $(element).click(function (e) {
                        e.preventDefault();
                        self.handleCourseClick(e);
                    });

                    // Add hover effect
                    $(element).hover(
                        function() {
                            $(this).css('opacity', '0.7');
                            $(this).css('cursor', 'pointer');
                        },
                        function() {
                            $(this).css('opacity', '1');
                        }
                    );
                });
            } */
        }


        /**
         * Handle click on course element (image or zone)
         */
        handleCourseClick(e) {
            var element = e.target;
            // Details are enabled, display the course details in the modal.
            if (parseInt(Data.infoarea)) {
                this.learningPath.showCircleDetails(e);
            } else {
                // Goto the course view page.
                var courseId = element.dataset.courseid || element.getAttribute('data-courseid');
                if (courseId) {
                    var courseLink = M.cfg.wwwroot + '/course/view.php?id=' + courseId;
                    window.location.href = courseLink;
                }
            }
        }

        processCircleModel() {
            var self = this;
            // Query for all course shapes (circles, polygons, etc.)
            var shapes = this.svg.querySelectorAll(".course-circle");
            if (shapes) {
                shapes.forEach((element) => {
                    $(element).click(function (e) {
                        e.preventDefault();
                        // Details are enabled, display the course details in the modal.
                        var infoarea = e.target.closest(".learning-path-widget");
                        if (infoarea && parseInt(infoarea.getAttribute("data-infoarea"))) {
                            self.learningPath.showCircleDetails(e);
                        } else {
                            // Goto the course view page.
                            var element = e.target;
                            var courseLink = M.cfg.wwwroot + '/course/view.php?id=' + element.dataset.courseid;
                            window.location.href = courseLink;
                        }
                    });
                });
            }

            this.processClickableElements();
        }

        setupSVGSize() {
            // Increase the width and height of svg, helps to put some padding to view the start and end elements.
            var clientReact = this.svg.getBoundingClientRect();

            // var width = clientReact.width + increasedView;
            // var height = clientReact.height + increasedView;

            var width = this.svg.getAttribute('width');
            var height = this.svg.getAttribute('height');

            this.svg.setAttribute('width', "100%");
            this.svg.setAttribute('height', "100%");


            var viewWidth, viewHeight, x, y;
            if (this.svg.hasAttribute('viewBox') && width && height) {
                [x, y, viewWidth, viewHeight] = this.svg.getAttribute('viewBox').split(" ");

                viewHeight = parseInt(viewHeight);
                viewWidth = parseInt(viewWidth);

                if (viewWidth == width) {
                    viewWidth += increasedView;
                }

                if (viewHeight == height) {
                    viewHeight += increasedView;
                }

                this.svg.setAttribute('viewBox', '-30, -30, ' + viewWidth + ", " + viewHeight);
            }
        }

        createCoursePaths() {

            var path = this.svg.querySelector('path');

            if (path === undefined || path === null || path == '') {
                return;
            }

            this.path = path;

            var pathID = path.id;

            if (pathID === '' && path !== undefined) {
                path.setAttribute('id', "learning-path-" + this.index + "-" + this.uniqueid);
                pathID = path.id;
            }

            var length = path.getTotalLength();
            this.pathLength = length;

            // Find the start and end points of the path.
            this.getStartEndPoints(this.path, this.pathLength);

            // Usage
            var pathPoints = this.calculatePointsAlongPath(path, Data.courses.length); // Adjust the number of points as needed.
            var imageSize = { width: Data.courseimgwidth, height: Data.courseimgheight }; // Size of your course image.
            this.createImagesAlongPath(this.svg, pathPoints, Data.courses, imageSize);

            // Create a start and end elements based on the preferences.

            if (parseInt(Data.startelement)) {
                this.createStartElement(this.svg, this.startPoint);
            }

            if (parseInt(Data.finishelement)) {
                this.createFinishElement(this.svg, this.endPoint);
            }

            // Create a completion path.
            //this.createCompletionPath(this.svg, this.path, this.index);
        }


        // Calculate the points along the path.
        calculatePointsAlongPath(path, numPoints) {
            // Get the total length of the path.
            var length = path.getTotalLength();

            var points = [];

            // Increase start and end element.
            var startPos = 0;
            if (parseInt(Data.startelement)) {
                numPoints = numPoints + 1;
                startPos = 1;
            }

            var isCircle = (Math.abs(this.startPoint.x - this.endPoint.x) <= Data.courseimgwidth)
                && (Math.abs(this.startPoint.y - this.endPoint.y) <= Data.courseimgwidth);

            for (var i = startPos; i < numPoints; i++) {
                var currentLength = (i / numPoints) * length;
                var point = path.getPointAtLength(currentLength);
                points.push({ x: point.x, y: point.y, length: currentLength });
            }

            // Finish element is not enabled, then make the last element to the end.

            if (!parseInt(Data.finishelement) && !isCircle) {
                var point = path.getPointAtLength(this.pathLength);
                points.lastItem.x = point.x;
                points.lastItem.y = point.y;
                points.lastItem.length = this.pathLength;
            }

            return points;
        }

        // Create and append image elements along the path.
        createImagesAlongPath(svg, points, courses, imageSize) {
            var imageWidth = imageSize.width;
            var imageHeight = imageSize.height;
            var radius = imageWidth / 2;

            var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            svg.appendChild(defs);

            var self = this;
            points.forEach(function (point, i) {
                var course = courses[i];
                var imagePath = courses[i].img;
                var patternID = false;

                var isVisualNumber = Data.coursevisual === 'number' || Data.isvisualnumber;
                var isVisualimg = Data.coursevisual === 'courseimg';
                var isVisualcustom = Data.coursevisual === 'custom';

                if (!Data.dotimg && isVisualimg) {
                    var image = self.createImage(imagePath, imageWidth, imageHeight);
                    patternID = 'dash-' + Data.blockid + '-course-' + course.info.id + '-pattern-' + self.index;
                    var pattern = self.createPattern(image, patternID);
                    defs.appendChild(pattern);
                } else if (!Data.dotimg && isVisualcustom) {
                    patternID = false;
                }
                var shape = self.createShape(point, course, patternID, radius, courses, i);
                svg.appendChild(shape);

                if (course.icon) {
                    var iconElement = self.createIconOverlay(point, course, radius);
                    if (iconElement) {
                        svg.appendChild(iconElement);
                    }
                }

                if (isVisualNumber) {
                    var numberText = self.createCourseNumber(point, course, radius);
                    svg.appendChild(numberText);
                }

                var infoicon = self.createInfoAreaIcon(point, course, radius);
                if (infoicon) {
                    svg.appendChild(infoicon);
                }

            });

            // Create start element and end elements.
            // svg.insertBefore(defs, svg.firstElementChild);
        }

        getStartEndPoints(path, pathLength) {

            this.startPoint = path.getPointAtLength(0); // Start point is at distance 0.
            this.endPoint = path.getPointAtLength(pathLength); // End point is at the total length of the path.

            return [this.startPoint, this.endPoint];
        };

        createImage(imagePath, imageWidth = 0, imageHeight = 0) {

            var image = document.createElementNS('http://www.w3.org/2000/svg', 'image');

            image.setAttribute('width', '1');
            image.setAttribute('height', '1');
            image.setAttribute('x', "0");
            image.setAttribute('y', "0");
            image.setAttribute('href', imagePath);
            image.setAttribute('preserveAspectRatio', "xMidYMid slice");

            return image;
        }

        createPattern(image, patternID) {

            var pattern = document.createElementNS('http://www.w3.org/2000/svg', 'pattern');
            pattern.id = patternID;
            pattern.setAttribute('width', '1');
            pattern.setAttribute('height', '1');
            pattern.setAttribute('x', '0');
            pattern.setAttribute('y', '0');
            pattern.setAttribute('patternContentUnits', 'objectBoundingBox');

            pattern.appendChild(image);

            return pattern;
        }

        /**
         * Get SVG path data for different shapes
         * @param {string} shape Shape type (circle, triangle, hexagon, diamond, star)
         * @param {number} cx Center X coordinate
         * @param {number} cy Center Y coordinate
         * @param {number} size Size/radius of the shape
         * @returns {object} Object with element type and attributes
         */
        getShapeData(shape, cx, cy, size) {
            const radius = size;
            let shapeData = {};

            switch (shape) {
                case 'triangle':
                    // Equilateral triangle pointing up
                    const height = radius * Math.sqrt(3);
                    const halfBase = radius;
                    shapeData = {
                        element: 'polygon',
                        points: [
                            [cx, cy - (2 * height) / 3],
                            [cx - halfBase, cy + height / 3],
                            [cx + halfBase, cy + height / 3]
                        ].map(p => p.join(',')).join(' ')
                    };
                    break;

                case 'hexagon':
                    // Regular hexagon
                    const hexPoints = [];
                    for (let i = 0; i < 6; i++) {
                        const angle = (Math.PI / 3) * i;
                        hexPoints.push([
                            cx + radius * Math.cos(angle),
                            cy + radius * Math.sin(angle)
                        ]);
                    }
                    shapeData = {
                        element: 'polygon',
                        points: hexPoints.map(p => p.join(',')).join(' ')
                    };
                    break;

                case 'diamond':
                    // Diamond (rotated square)
                    shapeData = {
                        element: 'polygon',
                        points: [
                            [cx, cy - radius],
                            [cx + radius, cy],
                            [cx, cy + radius],
                            [cx - radius, cy]
                        ].map(p => p.join(',')).join(' ')
                    };
                    break;

                case 'star':
                    // 5-pointed star
                    const starPoints = [];
                    const outerRadius = radius;
                    const innerRadius = radius * 0.4;
                    for (let i = 0; i < 10; i++) {
                        const angle = (Math.PI / 5) * i - Math.PI / 2;
                        const r = i % 2 === 0 ? outerRadius : innerRadius;
                        starPoints.push([
                            cx + r * Math.cos(angle),
                            cy + r * Math.sin(angle)
                        ]);
                    }
                    shapeData = {
                        element: 'polygon',
                        points: starPoints.map(p => p.join(',')).join(' ')
                    };
                    break;

                case 'circle':
                default:
                    shapeData = {
                        element: 'circle',
                        cx: cx,
                        cy: cy,
                        r: radius
                    };
                    break;
            }

            return shapeData;
        }

        /**
         * Get bounding box dimensions for clipping
         * @param {string} shape Shape type
         * @param {number} size Size/radius of the shape
         * @returns {object} Object with width and height
         */
        getBoundingBox(shape, size) {
            const radius = size;
            let box = {width: radius * 2, height: radius * 2};

            switch (shape) {
                case 'triangle':
                    box.height = radius * Math.sqrt(3) * 1.2;
                    break;
                case 'star':
                    box.width = radius * 2.2;
                    box.height = radius * 2.2;
                    break;
            }

            return box;
        }

        createShape(point, course, patternID, radius, courses, i) {
            var self = this;
            // Determine shape to use
            let shape = Data.defaultshape || 'circle';
            var isVisualcustom = Data.coursevisual === 'custom';
            // Check if course has a custom shape based on custom field
            if (course.shape) {
                shape = course.shape;
            }

            const shapeData = this.getShapeData(shape, point.x, point.y, radius);

            const element = document.createElementNS('http://www.w3.org/2000/svg', shapeData.element);

            element.classList.add('course-circle');
            element.classList.add('course-shape');
            element.classList.add('course-shape-' + shape);
            element.classList.add('imgsize-' + Data.imgsize);

            var courseInd = "circle-course-" + course.info.id;
            element.classList.add(courseInd);
            element.setAttribute("data-courseid", course.info.id);

            // Get the prev and next item.
            var prevCourseItem = "";
            var prevCourseId = 0;
            if (i > 0) {
                prevCourseItem = "circle-course-" + courses[i - 1].info.id;
                prevCourseId = courses[i - 1].info.id;
            }
            element.setAttribute("data-prevcourse", prevCourseId);
            element.setAttribute("data-prevcourse-item", prevCourseItem);

            var nextCourseItem = "";
            var nextCourseId = 0;
            if (i < courses.length - 1) {
                nextCourseItem = "circle-course-" + courses[i + 1].info.id;
                nextCourseId = courses[i + 1].info.id;
            }
            element.setAttribute("data-nextcourse", nextCourseId);
            element.setAttribute("data-nextcourse-item", nextCourseItem);

            var completionStatus = course.report.inprogress ? 'inprogress' : 'notstarted';
            completionStatus = course.report.completed ? 'completed' : completionStatus;

            // Colorstatus.
            if (course.report.unavailable) {
                completionStatus = 'unavailable';
            } else if (course.report.available) {
                completionStatus = 'available';
            } else if (course.report.failed) {
                completionStatus = 'failed';
            } else if (course.report.completed) {
                completionStatus = 'completed';
            } else if (course.report.inprogress) {
                completionStatus = 'inprogress';
            } else {
                completionStatus = 'notstarted';
            }

            element.classList.add(completionStatus);

            if (!patternID && Data.dotimg) {
                element.classList.add('dot-image');
            }

            const decode = (text) => {
                // Decode HTML entities before setting data-title
                const textarea = document.createElement('textarea');
                textarea.innerHTML = text;
                return textarea.value;
            };

            // Set common attributes
            var attrs = {
                'fill': patternID ? 'url(#' + patternID + ')' : 'none',
                'stroke-width': '4',
                'stroke': course.statuscolor ? course.statuscolor : "var(--gray)",
                'data-course-status': completionStatus,
                'data-title': decode(course.info.fullname),
                'data-toggle': 'tooltip',
                'data-current-length': point.length,
                'data-shape': shape
            };

            // Add shape-specific attributes
            if (shapeData.element === 'circle') {
                attrs.cx = shapeData.cx;
                attrs.cy = shapeData.cy;
                attrs.r = shapeData.r;
            } else if (shapeData.element === 'polygon') {
                attrs.points = shapeData.points;
            }

            for (var attr in attrs) {
                element.setAttribute(attr, attrs[attr]);
            }

            if (isVisualcustom) {
                var statusColor = course.statuscolor;
                element.setAttribute('fill', statusColor);
            }

            return element;
        }

        /**
         * Create course number text overlay
         * @param {object} point Point coordinates {x, y, length}
         * @param {object} course Course data
         * @param {number} radius Radius/size of the shape
         * @returns {SVGElement} Text element
         */
        createCourseNumber(point, course, radius) {
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');

            // Position text at center of shape
            text.setAttribute('x', point.x);
            text.setAttribute('y', point.y);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('dominant-baseline', 'central');
            text.setAttribute('class', 'course-number-text');
            text.setAttribute('pointer-events', 'none');

            // Style the text
            text.style.fontSize = (radius * 0.8) + 'px';
            text.style.fontWeight = 'bold';
            text.style.fill = '#ffffff';
            text.style.stroke = '#000000';
            text.style.strokeWidth = '1px';
            text.style.paintOrder = 'stroke';

            // Add the course number
            text.textContent = course.coursenumber || '';

            return text;
        }

        createCompletionPath(svg, originalPath, index) {
            var endLength = 0;

            var courses = svg.querySelectorAll('circle');

            var completed = 0;
            var nextCourse;

            Array.from(courses).some((course, i) => {

                // Verify the course status is completed.
                if (course.dataset.courseStatus === 'completed') {
                    completed++;
                    nextCourse = course;
                } else if (course.dataset.courseStatus === 'inprogress') {
                    nextCourse = course;
                    course.classList.add('inprogress-flag');
                }
            })

            // Extend the length of the completed course.
            endLength = nextCourse ? nextCourse.dataset.currentLength : 0;

            if (completed != 0 && completed === courses.length) {
                endLength = this.pathLength;
                svg.parentNode.classList.add('learning-path-completed');
            }

            var maskPath = originalPath.cloneNode(true);
            maskPath.id += "-mask";
            maskPath.setAttribute("stroke", "white");
            maskPath.setAttribute("fill", "none");
            maskPath.setAttribute("stroke-dashoffset", "0px");

            // Set the end of mask.
            var dashArray = endLength + "px, " + this.pathLength + "px";
            maskPath.setAttribute("stroke-dasharray", dashArray);


            var mask = document.createElementNS("http://www.w3.org/2000/svg", "mask");
            mask.appendChild(maskPath);
            mask.id = 'dash-' + Data.blockid + '-learning-mask-' + index;

            svg.querySelector('defs').appendChild(mask);

            var newPath = originalPath.cloneNode(true);
            newPath.setAttribute("mask", "url(#" + mask.id + ")");
            newPath.setAttribute("stroke", "#11B56A");
            newPath.id = '';
            // newPath.setAttribute("fill", "none");
            originalPath.parentNode.insertBefore(newPath, originalPath.nextElementSibling);

        }

        createStartElement(svg, point) {

            var text = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");

            var width = 100;
            var height = 30;

            text.setAttribute("x", point.x - width / 2);
            text.setAttribute("y", point.y - height / 2);
            text.setAttribute('width', width);
            text.setAttribute('height', height);

            text.innerHTML = "<p class='start-element'> " + Data.strings.start + " </p>";

            svg.appendChild(text);
        }


        createFinishElement(svg, point) {

            var text = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");

            var width = 100;
            var height = 30;

            text.setAttribute("x", point.x - width / 2);
            text.setAttribute("y", point.y - height / 2);
            text.setAttribute('width', width);
            text.setAttribute('height', height);

            text.innerHTML = "<p class='finish-element'> " + Data.strings.finish + " </p>";

            svg.appendChild(text);
        }


        /**
         * Safe getBBox method that handles hidden elements
         * @param {SVGElement} element - The SVG element to get bounding box for
         * @returns {DOMRect} Bounding box rectangle
         */
        getSafeBBox(element) {
            var bbox = { x: 0, y: 0, width: 0, height: 0 };
            var attempts = 0;
            var maxAttempts = 2;

            while (attempts < maxAttempts) {
                try {
                    bbox = element.getBBox();

                    // Check if we got valid dimensions
                    if (bbox.width > 0 && bbox.height > 0) {
                        return bbox;
                    }

                    // First attempt failed, try making element visible
                    if (attempts === 0) {

                        // Find the SVG container and make it visible
                        var svg = element.closest('svg');
                        var svgContainer = svg ? svg.closest('.svg-block') : null;

                        if (svgContainer) {
                            var computedStyle = window.getComputedStyle(svgContainer);
                            if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
                                // Store original styles on the element itself for cleanup
                                element._originalDisplay = svgContainer.style.display;
                                element._originalVisibility = svgContainer.style.visibility;
                                element._originalPosition = svgContainer.style.position;
                                element._originalLeft = svgContainer.style.left;
                                element._originalTop = svgContainer.style.top;
                                element._originalZIndex = svgContainer.style.zIndex;
                                element._svgContainer = svgContainer;

                                // Make temporarily visible off-screen
                                svgContainer.style.display = 'block';
                                svgContainer.style.visibility = 'visible';
                                svgContainer.style.position = 'absolute';
                                svgContainer.style.left = '-9999px';
                                svgContainer.style.top = '-9999px';
                                svgContainer.style.zIndex = '-1';

                                // Continue to next attempt
                                attempts++;
                                continue;
                            }
                        }
                    }

                    // If we reach here, both attempts failed
                    break;

                } catch (e) {
                    console.error('Error getting bbox on attempt', attempts + 1, ':', e);
                    break;
                }
            }

            // Restore original styles if they were changed
            this.restoreElementStyles(element);

            // If still no valid bbox, fallback to attribute calculation
            if (bbox.width === 0 && bbox.height === 0) {
                bbox = this.calculateBBoxFromAttributes(element);
            }

            return bbox;
        }


        /**
         * Restore original styles for an element
         * @param {SVGElement} element - The element to restore styles for
         */
        restoreElementStyles(element) {
            if (element._svgContainer) {
                element._svgContainer.style.display = element._originalDisplay || '';
                element._svgContainer.style.visibility = element._originalVisibility || '';
                element._svgContainer.style.position = element._originalPosition || '';
                element._svgContainer.style.left = element._originalLeft || '';
                element._svgContainer.style.top = element._originalTop || '';
                element._svgContainer.style.zIndex = element._originalZIndex || '';
                
                // Clean up temporary properties
                delete element._originalDisplay;
                delete element._originalVisibility;
                delete element._originalPosition;
                delete element._originalLeft;
                delete element._originalTop;
                delete element._originalZIndex;
                delete element._svgContainer;
            }
        }

        /**
         * Get bbox from hidden SVG element by temporarily making it visible
         * @param {SVGElement} element - The SVG element
         * @returns {DOMRect} Bounding box rectangle
         */
        getBBoxFromHiddenElement(element) {
            var self = this;
            var bbox = { x: 0, y: 0, width: 0, height: 0 };

            try {
                // Find the SVG container
                var svg = element.closest('svg');
                var svgContainer = svg ? svg.closest('.svg-block') : null;
                var wasHidden = false;
                var originalStyles = {};

                if (svgContainer) {
                    var computedStyle = window.getComputedStyle(svgContainer);
                    if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
                        // Store original styles
                        originalStyles.display = svgContainer.style.display;
                        originalStyles.visibility = svgContainer.style.visibility;
                        originalStyles.position = svgContainer.style.position;
                        originalStyles.left = svgContainer.style.left;
                        originalStyles.top = svgContainer.style.top;
                        originalStyles.zIndex = svgContainer.style.zIndex;

                        // Make temporarily visible off-screen
                        svgContainer.style.display = 'block';
                        svgContainer.style.visibility = 'visible';
                        svgContainer.style.position = 'absolute';
                        svgContainer.style.left = '-9999px';
                        svgContainer.style.top = '-9999px';
                        svgContainer.style.zIndex = '-1';
                        wasHidden = true;
                    }
                }

                // Try to get bbox again
                //bbox = element.getBBox();
                bbox = this.getSafeBBox(element);

                // Restore original styles
                if (wasHidden && svgContainer) {
                    svgContainer.style.display = originalStyles.display || '';
                    svgContainer.style.visibility = originalStyles.visibility || '';
                    svgContainer.style.position = originalStyles.position || '';
                    svgContainer.style.left = originalStyles.left || '';
                    svgContainer.style.top = originalStyles.top || '';
                    svgContainer.style.zIndex = originalStyles.zIndex || '';
                }

                // If still no valid bbox, fallback to attribute calculation
                if (bbox.width === 0 && bbox.height === 0) {
                    bbox = this.calculateBBoxFromAttributes(element);
                }

            } catch (e) {
                console.error('Error in getBBoxFromHiddenElement:', e);
                bbox = this.calculateBBoxFromAttributes(element);
            }

            return bbox;
        }


        /**
         * Calculate bounding box from element attributes as fallback
         * @param {SVGElement} element - The SVG element
         * @returns {Object} Bounding box object {x, y, width, height}
         */
        calculateBBoxFromAttributes(element) {
            var bbox = { x: 0, y: 0, width: 0, height: 0 };
            var tagName = element.tagName.toLowerCase();

            try {
                switch (tagName) {
                    case 'ellipse':
                        var cx = parseFloat(element.getAttribute('cx')) || 0;
                        var cy = parseFloat(element.getAttribute('cy')) || 0;
                        var rx = parseFloat(element.getAttribute('rx')) || 30;
                        var ry = parseFloat(element.getAttribute('ry')) || 30;

                        bbox = {
                            x: cx - rx,
                            y: cy - ry,
                            width: rx * 2,
                            height: ry * 2
                        };
                        break;

                    case 'circle':
                        var cx = parseFloat(element.getAttribute('cx')) || 0;
                        var cy = parseFloat(element.getAttribute('cy')) || 0;
                        var r = parseFloat(element.getAttribute('r')) || 30;

                        bbox = {
                            x: cx - r,
                            y: cy - r,
                            width: r * 2,
                            height: r * 2
                        };
                        break;

                    case 'rect':
                        bbox = {
                            x: parseFloat(element.getAttribute('x')) || 0,
                            y: parseFloat(element.getAttribute('y')) || 0,
                            width: parseFloat(element.getAttribute('width')) || 60,
                            height: parseFloat(element.getAttribute('height')) || 60
                        };
                        break;

                    case 'polygon':
                    case 'polyline':
                        var points = element.getAttribute('points');
                        if (points) {
                            bbox = this.calculatePolygonBBox(points);
                        } else {
                            bbox = { x: -30, y: -30, width: 60, height: 60 };
                        }
                        break;

                    default:
                        bbox = { x: -30, y: -30, width: 60, height: 60 };
                        console.warn('Unknown element type for bbox calculation:', tagName);
                        break;
                }

            } catch (e) {
                console.error('Error calculating bbox from attributes:', e);
                bbox = { x: -30, y: -30, width: 60, height: 60 };
            }

            return bbox;
        }

        /**
         * Calculate bounding box for polygon/polyline from points
         * @param {string} pointsStr - Points string attribute
         * @returns {Object} Bounding box object
         */
        calculatePolygonBBox(pointsStr) {
            var coords = pointsStr.trim().split(/[\s,]+/).filter(c => c && !isNaN(c));

            if (coords.length < 4) {
                return { x: -30, y: -30, width: 60, height: 60 };
            }

            var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

            for (var i = 0; i < coords.length; i += 2) {
                if (coords[i] !== undefined && coords[i + 1] !== undefined) {
                    var x = parseFloat(coords[i]);
                    var y = parseFloat(coords[i + 1]);

                    if (!isNaN(x) && !isNaN(y)) {
                        minX = Math.min(minX, x);
                        minY = Math.min(minY, y);
                        maxX = Math.max(maxX, x);
                        maxY = Math.max(maxY, y);
                    }
                }
            }

            return {
                x: minX,
                y: minY,
                width: maxX - minX,
                height: maxY - minY
            };
        }
    }

    return {
        init: function (uniqueid, data, contextid, grid) {
            return new learningPath(uniqueid, data, contextid, grid);
        }
    };

});
