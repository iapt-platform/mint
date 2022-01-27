var defaultData = './pref_realtime_get.php?api=all&item=count';
var urlInput = document.getElementById('fetchURL');
var pollingCheckbox = document.getElementById('enablePolling');
var pollingInput = document.getElementById('pollingTime');

function createChart(div,title,api,item) {
    Highcharts.chart(div, {
        chart: {
            type: 'spline'
        },
        title: {
            text: title
        },
        accessibility: {
            announceNewData: {
                enabled: true,
                minAnnounceInterval: 15000,
                announcementFormatter: function (allSeries, newSeries, newPoint) {
                    if (newPoint) {
                        return 'New point added. Value: ' + newPoint.y;
                    }
                    return false;
                }
            }
        },
        data: {
            csvURL: './pref_realtime_get.php?api='+api+'&item='+item,
            enablePolling: pollingCheckbox.checked === true,
            dataRefreshRate: 60
        }
    });


}


// We recreate instead of using chart update to make sure the loaded CSV
// and such is completely gone.
pollingCheckbox.onchange =  createChart;

// Create the chart
createChart("chart-1",'总请求数/分钟','all','count');
createChart("chart-2",'总执行时间/分钟','all','delay');
createChart("chart-3",'平均执行时间/分钟','all','average');
create_live("chart-4");
function create_live(container){
    Highcharts.chart(container, {

    chart: {
        type: 'gauge',
        plotBackgroundColor: null,
        plotBackgroundImage: null,
        plotBorderWidth: 0,
        plotShadow: false
    },

    title: {
        text: '实时平均执行时间'
    },

    pane: {
        startAngle: -150,
        endAngle: 150,
        background: [{
            backgroundColor: {
                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                stops: [
                    [0, '#FFF'],
                    [1, '#333']
                ]
            },
            borderWidth: 0,
            outerRadius: '109%'
        }, {
            backgroundColor: {
                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                stops: [
                    [0, '#333'],
                    [1, '#FFF']
                ]
            },
            borderWidth: 1,
            outerRadius: '107%'
        }, {
            // default background
        }, {
            backgroundColor: '#DDD',
            borderWidth: 0,
            outerRadius: '105%',
            innerRadius: '103%'
        }]
    },

    // the value axis
    yAxis: {
        min: 0,
        max: 5000,

        minorTickInterval: 'auto',
        minorTickWidth: 1,
        minorTickLength: 10,
        minorTickPosition: 'inside',
        minorTickColor: '#666',

        tickPixelInterval: 30,
        tickWidth: 2,
        tickPosition: 'inside',
        tickLength: 10,
        tickColor: '#666',
        labels: {
            step: 2,
            rotation: 'auto'
        },
        title: {
            text: '毫秒/秒'
        },
        plotBands: [{
            from: 0,
            to: 2000,
            color: '#55BF3B' // green
        }, {
            from: 2000,
            to: 3500,
            color: '#DDDF0D' // yellow
        }, {
            from: 3500,
            to: 5000,
            color: '#DF5353' // red
        }]
    },

    series: [{
        name: 'Speed',
        data: [80],
        tooltip: {
            valueSuffix: ' ms/s'
        }
    }]

},
// Add some life
function (chart) {
    if (!chart.renderer.forExport) {
        setInterval(function () {
            $.get("./pref_live.php?api=all&item=average",function(data){
                var point = chart.series[0].points[0];
                newVal = parseInt(data);
                point.update(newVal);
            });
        }, 3000);
    }
});

}