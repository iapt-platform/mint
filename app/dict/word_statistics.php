<?PHP
include "../pcdl/html_head.php";
require_once "../public/load_lang_js.php";//语言文件
?>
	<body>
		<style type="text/css">

            .highcharts-figure, .highcharts-data-table table {
                flex:1;
                min-width: 220px; 
                max-width: 100%;
                margin: 1em auto;
            }

            .highcharts-data-table table {
                font-family: Verdana, sans-serif;
                border-collapse: collapse;
                border: 1px solid #EBEBEB;
                margin: 10px auto;
                text-align: center;
                width: 100%;
                max-width: 500px;
            }
            .highcharts-data-table caption {
                padding: 1em 0;
                font-size: 1.2em;
                color: #555;
            }
            .highcharts-data-table th {
                font-weight: 600;
                padding: 0.5em;
            }
            .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
                padding: 0.5em;
            }
            .highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
                background: #f8f8f8;
            }
            .highcharts-data-table tr:hover {
                background: #f1f7ff;
            }
            .chart_head_1 {
                text-align: center;
                font-size: x-large;
                margin-bottom: 0;
                font-weight: bold;
            }
            .highcharts-data-label {
                font-size: small;
            }
		</style>
<script src="../public/js/highcharts/highcharts.js"></script>
<script src="../public/js/highcharts/modules/sunburst.js"></script>
<script src="../public/js/highcharts/modules/exporting.js"></script>
<script src="../public/js/highcharts/modules/export-data.js"></script>
<script src="../public/js/highcharts/modules/accessibility.js"></script>
<?php
    require_once("../pcdl/head_bar.php");
?>
<h2 class="chart_head_1"><?php echo $_local->gui->statistical.$_local->gui->chart."："; ?><?php echo ucfirst($_GET["word"]) ?></h2>
<div style="display:flex;">
<figure class="highcharts-figure">
    <div id="container"></div>
</figure>
<figure class="highcharts-figure">
    <div id="container_list" style="height:50em;"></div>
</figure>
</div>

<script type="text/javascript">
  $.get("../search/word_statistics.php",
  {
    word : "<?php echo $_GET["word"] ?>"
  },
  function(data,status){
    let worddata =  JSON.parse(data);
    // Splice in transparent for the center circle
    Highcharts.getOptions().colors.splice(0, 0, 'transparent');

    Highcharts.setOptions({
            colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
        });
    Highcharts.chart('container', {
        chart: {
            height: '100%'
        },

        title: {
            text: gLocal.gui.distribution
        },
        subtitle: {
            text: ''
        },
        series: [{
            type: "sunburst",
            data: worddata,
            allowDrillToNode: true,
            cursor: 'pointer',
            dataLabels: {
                format: '{point.name}',
                filter: {
                    property: 'innerArcLength',
                    operator: '>',
                    value: 16
                },
                rotationMode: 'circular'
            },
            levels: [{
                level: 1,
                levelIsConstant: false,
                dataLabels: {
                    style: {
        				fontSize: 'x-large',
	        		},
                    filter: {
                        property: 'outerArcLength',
                        operator: '>',
                        value: 64
                    }
                }
            }, {
                level: 2,
                colorByPoint: true,
                dataLabels: {
                    style: {
        				fontSize: 'large',
	        		},
                }
            },
            {
                level: 3,
                colorVariation: {
                    key: 'brightness',
                    to: -0.5
                },
                dataLabels: {
                    style: {
        				fontSize: 'small',
	        		},
                }
            }, {
                level: 4,
                colorVariation: {
                    key: 'brightness',
                    to: 0.5
                }
            }]
        }],
        tooltip: {
            headerFormat: "",
            pointFormat: '<b><?php echo ucfirst($_GET["word"]) ?></b> <?php echo $_local->gui->in ?> <b>{point.name}</b> <?php echo $_local->gui->present ?> <b>{point.value}</b> '+gLocal.gui.times
        }
    });
  });

  $.get("../search/word_list.php",
  {
    word : "<?php echo $_GET["word"] ?>"
  },
  function(data,status){
    let worddata =  JSON.parse(data);
    Highcharts.chart('container_list', {
        chart: {
            type: 'bar'
        },
        title: {
            text: gLocal.gui.declension_list,
            style: {
				fontSize: 'large',
			    }
        },
        xAxis: {
            categories: worddata.wordlist,
            labels: {
                enabled: true,//显示
                //crosshair: false, 功能未知
                style: {
                    fontSize: 'small',
                }
    		},

        },
        yAxis: {
            min: 0,
            title: {
                text: gLocal.gui.frequency,
                style: {
				fontSize: 'large',
			    }
            },
            labels: {
                style: {
                    fontSize: 'small',
                }
    		},
        },
        legend: {
            reversed: true,
            verticalAlign: "top",
            enabled: true //图例开关
        },
        plotOptions: {
            series: {
                stacking: 'normal'
            },
            bar: {
                pointPadding: 0,
                borderWidth: 0,
                pointWidth: 20,
                allowOverlap: true,
		}

        },
        series: worddata.data
    });
});
		</script>


<?php
include "../pcdl/html_foot.php";
?>