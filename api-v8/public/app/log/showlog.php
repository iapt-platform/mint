<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="../../node_modules/jquery/dist/jquery.js"></script>

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

	<script src="../../node_modules/highcharts/highstock.js"></script>
	<script src="../../node_modules/highcharts/modules/data.js"></script>
	<script src="../../node_modules/highcharts/modules/exporting.js"></script>

	<script src="./showlog.js"></script>

</head>
<body>
<h2><?php echo $_GET['file'];?></h2>
<div style="display:flex;">
<div id='chart-1'></div>
<div id='chart-2'></div>
</div>
<div id='chart-3'></div>
<div id='chart-4'></div>
<div id='chart-3a'></div>
<div id='chart-5'></div>

<div id="chart-6"></div>




<script>

</script>

<script type="text/javascript">
getData('<?php echo $_GET['file'];?>');
</script>
</body>
</html>