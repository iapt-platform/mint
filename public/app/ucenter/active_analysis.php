<?php

?>

<html>
<body>
<script src="../../node_modules/highcharts/highstock.js"></script>
<script src="../../node_modules/highcharts/modules/data.js"></script>
<script src="../../node_modules/highcharts/modules/exporting.js"></script>

<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/data.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>


<div id="container" style="height: 400px; min-width: 310px"></div>

<script>
Highcharts.getJSON('active_get.php', function (data) {

// create the chart
Highcharts.stockChart('container', {


	rangeSelector: {
		selected: 2
	},

	title: {
		text: 'Pali Step'
	},

	series: [{
		type: 'ohlc',
		name: 'AAPL Stock Price',
		data: data,
		dataGrouping: {
			units: [[
				'week', // unit name
				[1] // allowed multiples
			], [
				'month',
				[1, 2, 3, 4, 6]
			]]
		}
	}]
});
});

</script>
</body>
</html>