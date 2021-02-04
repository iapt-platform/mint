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
    require_once "../path.php";
    require_once "../public/_pdo.php";
    require_once '../public/function.php';
    require_once '../ucenter/function.php';
    require_once "../pcdl/head_bar.php";
    $currChannal = "course";
    require_once "../uhome/head.php";
?>

<link href='../lib/fullcalendar/main.css' rel='stylesheet' />
<script src='../lib/fullcalendar/main.js'></script>

<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/data.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>


<div id="container" style="height: 400px; min-width: 310px"></div>

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


  Highcharts.getJSON('../ucenter/active_get.php', function (data) {

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
      name: '每日收获',
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
<style>

  body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;
  }

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

<div class='index_inner'>
  <div id='script-warning'>
    <code>php/get-events.php</code> must be running.
  </div>

  <div id='loading'>loading...</div>

  <div id='calendar'></div>
</div>
<?php
include "../pcdl/html_foot.php";
?>
