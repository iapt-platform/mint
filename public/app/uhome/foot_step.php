<?php
if(isset($_GET["userid"])){
	$foot_set_userid = $_GET["userid"];
}
else if(isset($_COOKIE["userid"])){
	$foot_set_userid = $_COOKIE["userid"];
}
else{
	echo "no user id";
	exit;
}

include "../pcdl/html_head.php";
?>

<body>

<?php
    require_once "../config.php";
    require_once "../public/_pdo.php";
    require_once '../public/function.php';
    require_once '../ucenter/function.php';
	require_once "../pcdl/head_bar.php";
    $currChannal = "foot-step";
	require_once "../uhome/head.php";
?>

<link href='../lib/fullcalendar/main.css' rel='stylesheet' />
<script src='../lib/fullcalendar/main.js'></script>

<script src="../../node_modules/highcharts/highstock.js"></script>
<script src="../../node_modules/highcharts/modules/data.js"></script>
<script src="../../node_modules/highcharts/modules/exporting.js"></script>

<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/data.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>

<div class='section_inner'>
<div id="container" style="height: 400px; min-width: 310px"></div>
</div>
<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      locale: getCookie("language"),
      editable: false,
      navLinks: true, // can click day/week names to navigate views
      dayMaxEvents: true, // allow "more" link when too many events
      events: {
        url: 'foot_step_data.php?userid=<?php echo $foot_set_userid;?>',
        failure: function() {
          document.getElementById('script-warning').style.display = 'block'
        }
      },
      loading: function(bool) {
        document.getElementById('loading').style.display =
          bool ? 'block' : 'none';
      }
    });

    calendar.render();
  });

Highcharts.setOptions({
    lang: {
        weekdays: [
            gLocal.gui.ravi, gLocal.gui.canda, gLocal.gui.bhumma, gLocal.gui.budha,
            gLocal.gui.guru, gLocal.gui.sukka, gLocal.gui.sora
        ],
		shortMonths:[
			gLocal.gui.Jan, gLocal.gui.Feb, gLocal.gui.Mar, gLocal.gui.Apr, gLocal.gui.May, gLocal.gui.Jun, gLocal.gui.Jul, gLocal.gui.Aug, gLocal.gui.Sep, gLocal.gui.Oct, gLocal.gui.Nov, gLocal.gui.Dec
		]
    }
});

  Highcharts.getJSON('../ucenter/active_get.php', function (data) {
    var ohlc = [],
        volume = [],
        dataLength = data.length,
        // set the allowed units for data grouping
        groupingUnits = [[
            'week',                         // unit name
            [1]                             // allowed multiples
        ], [
            'month',
            [1, 2, 3, 4, 6]
        ]],

        i = 0;

    for (i; i < dataLength; i += 1) {
        ohlc.push([
            data[i][0], // the date
            data[i][1], // open
            data[i][2], // high
            data[i][3], // low
            data[i][4] // close
        ]);

        volume.push([
            data[i][0], // the date
            data[i][5] // the volume
        ]);
    }

  // create the chart
  Highcharts.stockChart('container', {


    rangeSelector: {
      selected: 2
    },

    title: {
      text: gLocal.gui.progress_curve
    },
        yAxis: [{
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: gLocal.gui.EXP
            },
            height: '60%',
            lineWidth: 2,
            resize: {
                enabled: true
            }
        }, {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: gLocal.gui.action
            },
            top: '65%',
            height: '35%',
            offset: 0,
            lineWidth: 2
        }],

	tooltip: {
			shared: false,
			useHTML: true,
			pointFormatter: function() {
				if(this.high){
					return '<b><a href="../">'+this.series.name + ' : ' + this.high + '&nbsp;' +gLocal.gui.h+ '</a><br><a href="../">' +gLocal.gui.day_EXP + ' : ' + Math.round((this.high - this.low)*100)/100 + '&nbsp;' +gLocal.gui.h+'</a></b><br/>'; 
				}
				else{
					return '<b><a href="../">'+this.series.name + ' : ' + this.y + '&nbsp;' +gLocal.gui.times+'</a><span style="display:none;">'+this.x+'</span></b>'; 
					
				}

			},
			valueDecimals: 2,//保留两位小數
            split: true

		},

    series: [{
      type: 'ohlc',
      name: gLocal.gui.EXP_in_total,
      data: ohlc,
      dataGrouping: {
        units: [[
          'week', // unit name
          [1] // allowed multiples
        ], [
          'month',
          [1, 2, 3, 4, 6]
        ]]
      }
        }, {
            type: 'column',
            name: gLocal.gui.day_action,
            data: volume,
            yAxis: 1,
            dataGrouping: {
                units: groupingUnits
            }
        }]
  });
  });
</script>
<style>

  #script-warning {
    display: none;
    background: #eee;
    border-bottom: 1px solid #ddd;
    padding: 0 10px;
    line-height: 40px;
    text-align: center;
    font-weight: bold;
    font-size: 12px;
    color: red;
  }

  #loading {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
  }

  #calendar {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 10px;
  }

</style>

<div class='section_inner'>
  <div id='script-warning'>
    <code>php/get-events.php</code> must be running.
  </div>

  <div id='loading'>loading...</div>
  <div id='calendar'></div>
</div>
<?php
include "../pcdl/html_foot.php";
?>
