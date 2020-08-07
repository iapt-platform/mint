<?PHP
include "../pcdl/html_head.php";
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
		</style>
<script src="../public/js/highcharts/highcharts.js"></script>
<script src="../public/js/highcharts/modules/sunburst.js"></script>
<script src="../public/js/highcharts/modules/exporting.js"></script>
<script src="../public/js/highcharts/modules/export-data.js"></script>
<script src="../public/js/highcharts/modules/accessibility.js"></script>
<?php
    require_once("../pcdl/head_bar.php");
?>
<h2 style="text-align:center;"><?php echo $_GET["word"] ?></h2>
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
            text: 'Distribution'
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
                    filter: {
                        property: 'outerArcLength',
                        operator: '>',
                        value: 64
                    }
                }
            }, {
                level: 2,
                colorByPoint: true
            },
            {
                level: 3,
                colorVariation: {
                    key: 'brightness',
                    to: -0.5
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
            pointFormat: '三藏译文 <b>{point.name}</b> 为 <b>{point.value}</b>'
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
            text: 'Declension List'
        },
        xAxis: {
            categories: worddata.wordlist
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Frequency'
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
                stacking: 'normal'
            }
        },
        series: worddata.data
    });
});
		</script>


<?php
include "../pcdl/html_foot.php";
?>