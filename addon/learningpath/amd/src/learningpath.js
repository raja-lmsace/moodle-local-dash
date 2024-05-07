
define(['jquery',  'core/fragment', 'core/modal_factory', 'core/modal_events',
'core/notification'], function($, Fragment, ModalFactory, ModalEvents, notification) {

    // Todo: Add padding on SVG;

    // var Path;

    const increasedView = 100;

    var Data;

    const Selectors = {
        dashBlock: "#dash-",
        svgParent: '#learningpath-svg-',
        classes: {}
    }

    const learningPath = function(uniqueid, data, contextid, grid) {
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

        Selectors.classes.dashBlock =  Selectors.dashBlock + data.blockid;
        Selectors.classes.svgParent = Selectors.svgParent + uniqueid;

        var svgList = document.querySelectorAll(Selectors.classes.dashBlock + ' svg');

        Array.from(svgList).forEach((svg, i) => {
            new BuildSVGPath(svg, i, uniqueid, contextid, self);
        });
    }

    learningPath.prototype.processGridModel = function() {
        var self = this;
        var girds = document.querySelectorAll("#learningpath-gird-" + self.uniqueId + " li.grid-block");
        if (girds) {
            girds.forEach((element) => {
                $(element).click(function(e) {
                    self.showCircleDetails(e);
                })
            });
        }
    }

    learningPath.prototype.getCourseDetails = function(target) {
        var self = this;
        var courseid = target.getAttribute("data-courseid");
        var prevCourse = target.getAttribute("data-prevcourse");
        var nextCourse = target.getAttribute("data-nextcourse");
        var checkgrid = target.getAttribute("data-grid");
        var params = {
            courseid : courseid,
            prevcourse: prevCourse,
            nextcourse: nextCourse,
            isgrid: (checkgrid != null) ? true : false,

        };
        return Fragment.loadFragment('dashaddon_learningpath', 'course_details_area', this.contextId, params);
    }

    learningPath.prototype.showCircleDetails = function(event) {
        console.log(event.currentTarget);
        var learingPathID = event.target.closest(".learning-path-block").getAttribute("id");
        ModalFactory.create({
            title: "",
            type: "",
            body: this.getCourseDetails(event.currentTarget),
            large: false
        }).then(function(modal) {
            modal.show();
            modal.getRoot().on(ModalEvents.bodyRendered, function() {
                var courseNavigation = document.querySelectorAll(".modal-body .pagination li");
                if (courseNavigation) {
                    courseNavigation.forEach((element) => {
                        element.addEventListener("click", (e) => {
                            var circleid = e.currentTarget.getAttribute("data-circle");
                            var navigateHandler = document.querySelectorAll("#" + learingPathID + " ." + circleid)[0];
                            modal.destroy();
                            if (navigateHandler) {
                                $("#" + learingPathID + " ." + circleid).trigger('click');
                            }
                        });
                    });
                }

            });
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });
            return modal;
        }).catch(notification.exception);
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
            this.createCoursePaths();
            this.processCircleModel();
        }

        processCircleModel() {
            var self = this;
            var circles = this.svg.querySelectorAll("circle.course-circle");
            if (circles) {
                circles.forEach((element) => {
                    $(element).click(function(e) {
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
                    })
                });
            }
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
            var pathPoints = this.calculatePointsAlongPath(path, Data.courses.length); // Adjust the number of points as needed
            var imageSize = { width: Data.courseimgwidth, height: Data.courseimgheight }; // Size of your course image
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

            var isCircle = (Math.abs(this.startPoint.x - this.endPoint.x) <= Data.courseimgwidth )
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

        // Create and append image elements along the path
        createImagesAlongPath(svg, points, courses, imageSize) {
            var imageWidth = imageSize.width;
            var imageHeight = imageSize.height;
            var radious = imageWidth / 2;

            var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            svg.appendChild(defs);

            var self = this;
            points.forEach(function(point, i) {
                var course = courses[i];
                var imagePath = courses[i].img;

                // Create course image.
                // console.log(Data.dotimg);
                if (!Data.dotimg) {
                    var image = self.createImage(imagePath, imageWidth, imageHeight);

                    // Make the pattern. use the image element in pattern.
                    var patternID = 'dash-' + Data.blockid + '-course-'+ course.info.id + '-pattern-' + self.index;
                    var pattern = self.createPattern(image, patternID);

                    // Append the pattern into definitions.
                    defs.appendChild(pattern);
                } else {
                    patternID = false;
                }

                // Create a circle and append into svg. link the pattern to the circle for fill.
                var circle = self.createCircle(point, course, patternID, radious, courses, i);
                svg.appendChild(circle);

            });

            // Create start element and end elements.
            // svg.insertBefore(defs, svg.firstElementChild);
        }

        getStartEndPoints(path, pathLength) {

            this.startPoint = path.getPointAtLength(0); // Start point is at distance 0
            this.endPoint = path.getPointAtLength(pathLength); // End point is at the total length of the path

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

        createCircle(point, course, patternID, radious, courses, i) {
            //console.log("i the value" + i);
            var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.classList.add('course-circle');

            //console.log(course.info.id);
            var courseInd = "circle-course-" + course.info.id;
            circle.classList.add(courseInd);
            circle.setAttribute("data-courseid", course.info.id);

            // Get the prev and next item.
            var prevCourseItem = "";
            var prevCourseId = 0;
            if (i > 0) {
                prevCourseItem = "circle-course-" + courses[i - 1].info.id;
                prevCourseId = courses[i - 1].info.id;
            }
            circle.setAttribute("data-prevcourse", prevCourseId);
            circle.setAttribute("data-prevcourse-item", prevCourseItem);

            var nextCourseItem = "";
            var nextCourseId = 0;
            if (i < courses.length - 1) {
                nextCourseItem = "circle-course-" + courses[i + 1].info.id;
                nextCourseId = courses[i + 1].info.id;
            }
            circle.setAttribute("data-nextcourse", nextCourseId);
            circle.setAttribute("data-nextcourse-item", nextCourseItem);


            var completionStatus = course.report.inprogress ? 'inprogress' : 'notstarted';
            var completionStatus = course.report.completed ? 'completed' : completionStatus;

            if (course.report.unavailable) {
                completionStatus = 'unavailable';
            } else if (course.report.completed) {
                completionStatus = 'completed';
            } else if (course.report.inprogress) {
                completionStatus = 'inprogress';
            }  else {
                completionStatus = 'notstarted';
            }

            circle.classList.add(completionStatus);

            if (!patternID) {
                circle.classList.add('dot-image');
            }

            var attrs = {
                'cx': point.x,
                'cy': point.y ,
                'r': radious,
                'fill': patternID ? 'url(#' + patternID + ')' : 'none',
                'stroke-width': '4',
                'stroke': "var(--gray)",
                'data-course-status': completionStatus,
                'data-title': course.info.fullname,
                'data-toggle': 'tooltip',
                'data-current-length': point.length
            }

            for (var i in attrs) {
                circle.setAttribute(i, attrs[i]);
            }
            return circle;
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
            mask.id = 'dash-'+ Data.blockid +'-learning-mask-' + index;

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
        init: function(uniqueid, data, contextid, grid) {
            return new learningPath(uniqueid, data, contextid, grid);
        }
    };

});
