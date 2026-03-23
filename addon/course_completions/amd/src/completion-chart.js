define(['jquery', 'core/chartjs', 'dashaddon_course_completions/chart-labels'], function ($, Chart, Labels) {

    /* global dashCourseCompletionData */
    var completionData = dashCourseCompletionData;

    var fontFamily = window.getComputedStyle(document.body, null).getPropertyValue('font-family');

    var labelsConfig = {
        render: 'value',
        fontSize: 16,
        fontStyle: 'bold',
        fontColor: completionData.colors.white,
        fontFamily: fontFamily
    };

    const courseCompletions = function (uniqueid, courses) {
        // Courses empty check.
        if (!courses) {
            return;
        }

        courses.forEach(function (course) {
            if (course.dataset.length > 0) {
                completionChart(course, uniqueid);
            }
        });
    };

    const completionChart = function (course, uniqueid) {

        var chartID = 'completion-widget-course-' + course.info.id + '-' + uniqueid;
        var ctx = document.getElementById(chartID).getContext('2d');
        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: completionData.datalabels,
                datasets: [{
                    label: course.info.fullname,
                    data: course.dataset,
                    backgroundColor: [
                        completionData.colors.completed,
                        completionData.colors.inprogress,
                        completionData.colors.notstarted
                    ]
                }]
            },
            options: {
                responsive: true,
                color: '#000000',
                font: { size: 14 },
                title: {
                    display: false,
                },
                tooltips: {
                    enabled: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    },
                    labels: labelsConfig,
                    title: {
                        display: false,
                    }
                }
            },
            plugins: [completionStat, Labels.registerPlugin(labelsConfig)]
        });

    };

    var completionStat = {
        id: 'completionStat',
        beforeDatasetsDraw: function (chart) {
            var ctx = chart.ctx;
            var top = chart.chartArea.top;

            var parentNode = chart.canvas.parentNode || false;
            if (!parentNode) {
                return;
            }
            var completion = parentNode.querySelector('.completion-percentage');
            var fontSize = 18;
            if (completion === null) {
                return;
            }
            var percent = completion.dataset.percentage;
            var ls = 10 * percent.length / 2;

            ctx.save();
            ctx.font = Chart.helpers.fontString(fontSize, 'bold', fontFamily);
            ctx.fillStyle = "{{data.colors.fontcolor}}";
            ctx.fillText(percent, chart.width / 2 - ls, top + (chart.height / 2));
            ctx.restore();
        }
    };

    return {
        init: courseCompletions,
    };
});
