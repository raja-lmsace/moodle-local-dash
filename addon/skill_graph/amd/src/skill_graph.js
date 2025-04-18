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
 * Dashaddon skill graph - Manage competencies apearance.
 *
 * @module   dashaddon_skill_graph/skill_graph
 * @copyright 2023 bdecent GmbH <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'dashaddon_skill_graph/chartjs'], function ($, Chart) {

    /* global skillGraphData */
    var colors = skillGraphData;

    Chart.defaults.font.size = 15;

    const chartOptions = {
        fontSize: Chart.defaults.font.size,
        textMargin: 2,
        fillStyle: colors.fillStyle
    };

    var CTX, ChartInstance;

    const skillGraph = function (uniqueid, datalabels, dataset) {

        var chartID = 'skill-graph-' + uniqueid;
        var ctx = document.getElementById(chartID).getContext('2d');
        new Chart(ctx, {
            type: "polarArea",
            data: {
                labels: datalabels,
                datasets: dataset
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'r', // Purposefully make the interaction disable due to fix the redraw of pointlabels.
                },
                layout: {
                    padding: {
                        left: 50,
                        right: 50,
                        top: 50,
                        bottom: 50
                    }
                },
                scales: {
                    r: {
                        ticks: {
                            display: false,
                            callback: function (val) {
                                return '${' + this.getLabelForValue(Number(val)) + '}%';
                            }
                        },
                        grid: {
                            display: false
                        },
                        pointLabels: {
                            display: false,
                            centerPointLabels: true,
                        },
                    }
                },
                scale: {
                    display: false
                },
                angleLines: {
                    display: true
                },
                plugins: {
                    tooltip: {
                        enabled: false
                    },
                    legend: {
                        display: false
                    },
                }
            },
            plugins: [{
                id: 'customPlugin',
                afterRender: function (chart) {
                    initPointLabels(chart);
                },
            }]
        });
    };

    const initPointLabels = (chart) => {
        ChartInstance = chart;
        CTX = chart.ctx;

        if (chart.chartArea.width < 200) {
            Chart.defaults.font.size = 12;
            chartOptions.fontSize = 12;
        }

        CTX.font = Chart.helpers.fontString(Chart.defaults.font.size, 'normal', Chart.defaults.font.family);

        var datasets = chart.data.datasets;
        var index = datasets.length > 0 ? Object.keys(datasets)[datasets.length - 1] : 0;

        var meta = ChartInstance.getDatasetMeta(index);
        var heighestRadius = 0;
        meta.data.forEach(function (element) {
            heighestRadius = heighestRadius < element.outerRadius ? element.outerRadius : heighestRadius;
        });
        meta.data.forEach(function (element, index) {
            element.outerRadius = heighestRadius;
            var label = chart.config.data.labels[index].toString();
            var info = getArcRenderInfo(element, label);
            CTX.beginPath();
            renderArcLabel(info.label, info);
            CTX.restore();
        });
    };

    const getArcRenderInfo = function (element, label) {
        var radius;
        var view = element;
        // Only outside.
        radius = view.outerRadius + chartOptions.fontSize + chartOptions.textMargin;
        var startAngle = view.startAngle;
        var endAngle = view.endAngle;
        var totalAngle = endAngle - startAngle;

        var angle = parseInt(view.outerRadius) - 10;
        var mertrics = measureLabel(label);
        var width = mertrics.width;

        if (width > angle) {
            do {
                label = label.substring(0, label.length - 1);
                width = parseInt(CTX.measureText(label + '...').width);
            } while (width > angle);
            label = label + '...';
        }

        startAngle += Math.PI / 2;
        endAngle += Math.PI / 2;
        mertrics = measureLabel(label);
        startAngle += (endAngle - (mertrics.width / radius + startAngle)) / 2;
        return {
            radius: radius,
            startAngle: startAngle,
            endAngle: endAngle,
            totalAngle: totalAngle,
            view: view,
            label: label
        };
    };

    const measureLabel = function (label) {
        if (typeof label === 'object') {
            return { width: label.width, height: label.height };
        } else {
            var width = 0;
            var lines = label.split('\n');
            for (var i = 0; i < lines.length; ++i) {
                var result = CTX.measureText(lines[i]);
                if (result.width > width) {
                    width = result.width;
                }
            }
            return { width: width, height: chartOptions.fontSize * lines.length };
        }
    };

    var renderArcLabel = function (label, renderInfo) {
        var ctx = CTX;
        var radius = renderInfo.radius;
        var view = renderInfo.view;
        ctx.save();
        ctx.translate(view.x, view.y);
        if (typeof label === 'string') {
            ctx.rotate(renderInfo.startAngle);
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'left';
            ctx.fillStyle = chartOptions.fillStyle || Chart.defaults.fillStyle;
            var lines = label.split('\n');
            var max = 0;
            var widths = [];
            var offset = 0;
            var mertrics;
            for (var j = 0; j < lines.length; ++j) {
                mertrics = ctx.measureText(lines[j]);
                if (mertrics.width > max) {
                    max = mertrics.width;
                }
                widths.push(mertrics.width);
            }
            for (var k = 0; k < lines.length; ++k) {
                var line = lines[k];
                var y = (lines.length - 1 - k) * -chartOptions.fontSize + offset;
                ctx.save();

                var padding = (max - widths[k]) / 2;
                ctx.rotate(padding / radius);
                for (var i = 0; i < line.length; i++) {
                    var char = line.charAt(i);
                    mertrics = ctx.measureText(char);
                    ctx.save();
                    ctx.translate(0, -1 * radius);
                    ctx.fillText(char, 0, y);
                    ctx.restore();
                    ctx.rotate(mertrics.width / radius);
                }
                ctx.restore();
            }
        }
        ctx.restore();
    };

    return {
        init: skillGraph,
    };
});
