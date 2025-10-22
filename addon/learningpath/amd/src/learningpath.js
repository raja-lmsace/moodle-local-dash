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
        if (this.isgrid && parseInt(data.detailsarea)) {
            this.processGridModel();
        }

        if (!data.courses) {
            return;
        }

        Data = data;

        Selectors.classes.dashBlock = Selectors.dashBlock + data.blockid;
        Selectors.classes.svgParent = Selectors.svgParent + uniqueid;

       /*  var svgList = document.querySelectorAll(Selectors.classes.dashBlock + ' svg');

        Array.from(svgList).forEach((svg, i) => {
            new BuildSVGPath(svg, i, uniqueid, contextid, self);
        }); */

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
                new BuildSVGPath(svg, svgIndex, uniqueid, contextid, self);
            }
        });
    }

    learningPath.prototype.processGridModel = function () {
        var self = this;
        var girds = document.querySelectorAll("#learningpath-gird-" + self.uniqueId + " li.grid-block");
        if (girds) {
            girds.forEach((element) => {
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

    learningPath.prototype.showDetailsInSidebar = function (event, learingPathID) {
        var self = this;
        var detailsContainer = document.getElementById("learningpath-course-details-" + this.uniqueId);
        var detailsContent = document.getElementById("learningpath-details-content-" + this.uniqueId);
        var infoArea = document.getElementById("learningpath-info-area-" + this.uniqueId);
        var sidebarCollapse = document.getElementById("learningpath-sidebar-" + this.uniqueId);
        var toggleButton = document.querySelector('.learning-path-widget.sidebar .sidebar-toggle[data-target="#learningpath-sidebar-' + this.uniqueId + '"]');

        if (!detailsContainer || !detailsContent || !infoArea) {
            return;
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
            };
        }
    }

    class BuildSVGPath {


        constructor(svg, index, uniqueid, contextid, learningPath) {
            this.svg = svg;
            this.index = index;
            this.uniqueid = uniqueid;
            this.contextId = contextid;
            this.learningPath = learningPath;

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
         * Create zone-based positioning for courses
         * Simply sets course IDs on configured SVG zone elements
         */
        createZonePositioning() {
            var self = this;

            // Get zone configurations for this block
            if (!Data.zoneconfigs || Data.zoneconfigs.length === 0) {
                console.log('No zone configurations found');
                return;
            }

            console.log('Processing zone configurations:', Data.zoneconfigs);

            // Process each configured zone
            Data.zoneconfigs.forEach(function(zoneConfig) {
                // Only process enabled zones with courses
                if (parseInt(zoneConfig.enabled) && zoneConfig.courseid) {

                    // e.g., zonetype='rect', zoneindex=0 means first <rect> element.
                    var elements = self.svg.querySelectorAll(zoneConfig.zonetype);

                    if (!elements || elements.length === 0) {
                        console.log('No elements found for zone type:', zoneConfig.zonetype);
                        return;
                    }


                    var zoneElement = elements[zoneConfig.zoneindex];

                    if (!zoneElement) {
                        console.log('Zone element not found:', zoneConfig.zonetype, 'at index', zoneConfig.zoneindex);
                        return;
                    }

                    // Find the course data.
                    var course = Data.courses.find(c => c.info.id == zoneConfig.courseid);
                    if (!course) {
                        console.log('Course not found:', zoneConfig.courseid);
                        return;
                    }

                    console.log('Setting course', course.info.id, 'to zone', zoneConfig.zoneid);

                    // Add classes for styling and interaction
                    zoneElement.classList.add('course-zone', 'clickable-zone');
                    var courseInd = "circle-course-" + course.info.id;
                    zoneElement.classList.add(courseInd);

                    // Set course ID on the zone element.
                    zoneElement.setAttribute('data-courseid', course.info.id);
                    zoneElement.setAttribute('data-zone', 'true');
                    zoneElement.setAttribute('data-zonetype', zoneConfig.zonetype);
                    zoneElement.setAttribute('data-zoneindex', zoneConfig.zoneindex);

                    // Set completion status.
                    /* var completionStatus = 'notstarted';
                    if (course.report.unavailable) {
                        completionStatus = 'unavailable';
                    } else if (course.report.failed) {
                        completionStatus = 'failed';
                    } else if (course.report.completed) {
                        completionStatus = 'completed';
                    } else if (course.report.inprogress) {
                        completionStatus = 'inprogress';
                    } else if (course.report.available) {
                        completionStatus = 'available';
                    } */
                }
            });
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
            var zoneElements = this.svg.querySelectorAll(".course-zone");
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
            }
        }


        /**
         * Handle click on course element (image or zone)
         */
        handleCourseClick(e) {
            var element = e.target;
            // Details are enabled, display the course details in the modal.
            if (parseInt(Data.detailsarea)) {
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
                        if (parseInt(Data.detailsarea)) {
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
            this.createCompletionPath(this.svg, this.path, this.index);
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
            var radious = imageWidth / 2;

            var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            svg.appendChild(defs);

            var self = this;
            points.forEach(function (point, i) {
                var course = courses[i];
                var imagePath = courses[i].img;
                var patternID = false;

                var isVisualNumber = Data.coursevisual === 'number' || Data.isvisualnumber;

                if (!Data.dotimg && !isVisualNumber) {
                    var image = self.createImage(imagePath, imageWidth, imageHeight);
                    patternID = 'dash-' + Data.blockid + '-course-' + course.info.id + '-pattern-' + self.index;
                    var pattern = self.createPattern(image, patternID);
                    defs.appendChild(pattern);
                }

                var shape = self.createShape(point, course, patternID, radious, courses, i);
                svg.appendChild(shape);

                if (isVisualNumber) {
                    var numberText = self.createCourseNumber(point, course, radious);
                    svg.appendChild(numberText);
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

        createImage(imagePath, imageWidth, imageHeight) {

            var image = document.createElementNS('http://www.w3.org/2000/svg', 'image');

            image.setAttribute('width', imageWidth);
            image.setAttribute('height', imageHeight);
            image.setAttribute('x', "0");
            image.setAttribute('y', "0");
            image.setAttribute('href', imagePath);
            image.setAttribute('preserveAspectRatio', "xMidYMid slice");

            return image;
        }

        createPattern(image, patternID) {

            var pattern = document.createElementNS('http://www.w3.org/2000/svg', 'pattern');
            pattern.id = patternID;
            // pattern.setAttribute('patternUnits', 'userSpaceOnUse');
            pattern.setAttribute('width', '1');
            pattern.setAttribute('height', '1');
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
            // Determine shape to use
            let shape = Data.defaultshape || 'circle';

            // Check if course has a custom shape based on custom field
            if (course.shape) {
                shape = course.shape;
            }

            const shapeData = this.getShapeData(shape, point.x, point.y, radius);
            const element = document.createElementNS('http://www.w3.org/2000/svg', shapeData.element);

            element.classList.add('course-circle');
            element.classList.add('course-shape');
            element.classList.add('course-shape-' + shape);

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

            if (!patternID) {
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
                'stroke': "var(--gray)",
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
    }

    return {
        init: function (uniqueid, data, contextid, grid) {
            return new learningPath(uniqueid, data, contextid, grid);
        }
    };

});
