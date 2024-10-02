<?php require_once '../../public/load_lang.php';?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<link href="../lib/main.css" rel="stylesheet" />
		<script src="../lib/main.js"></script>
		<script src="../../public/js/jquery.js"></script>
		<script>
			function getCookie(name) {
				var start = document.cookie.indexOf(name + "=");
				var len = start + name.length + 1;
				if (!start && name != document.cookie.substring(0, name.length)) {
					return null;
				}
				if (start == -1) return null;
				var end = document.cookie.indexOf(";", len);
				if (end == -1) end = document.cookie.length;
				return decodeURI(document.cookie.substring(len, end));
			}

			document.addEventListener("DOMContentLoaded", function () {
				var initialTimeZone = "local";
				var timeZoneSelectorEl = document.getElementById("time-zone-selector");
				var loadingEl = document.getElementById("loading");
				var calendarEl = document.getElementById("calendar");

				var calendar = new FullCalendar.Calendar(calendarEl, {
					timeZone: initialTimeZone,
					locale: getCookie("language"),
					headerToolbar: {
						left: "prev,next today",
						center: "title",
						right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
					},
					//initialDate: '2020-09-12',
					navLinks: true, // can click day/week names to navigate views
					editable: false,
					selectable: true,
					dayMaxEvents: true, // allow "more" link when too many events
					events: {
						url: "php/get-events.php",
						failure: function () {
							document.getElementById("script-warning").style.display = "inline"; // show
						},
					},
					loading: function (bool) {
						if (bool) {
							loadingEl.style.display = "inline"; // show
						} else {
							loadingEl.style.display = "none"; // hide
						}
					},
					eventClick: function (info) {
						info.jsEvent.preventDefault(); // don't let the browser navigate

						if (info.event.url) {
							window.open(info.event.url);
						}
					},

					eventTimeFormat: { hour: "numeric", minute: "2-digit" }, //timeZoneName: "short"

					dateClick: function (arg) {
						console.log("dateClick", calendar.formatIso(arg.date));
					},
					select: function (arg) {
						console.log("select", calendar.formatIso(arg.start), calendar.formatIso(arg.end));
					},
				});

				calendar.render();

				// load the list of available timezones, build the <select> options
				// it's HIGHLY recommended to use a different library for network requests, not this internal util func
				FullCalendar.requestJson(
					"GET",
					"php/get-time-zones.php",
					{},
					function (timeZones) {
						timeZones.forEach(function (timeZone) {
							var optionEl;

							if (timeZone !== "UTC") {
								// UTC is already in the list
								optionEl = document.createElement("option");
								optionEl.value = timeZone;
								optionEl.innerText = timeZone;
								timeZoneSelectorEl.appendChild(optionEl);
							}
						});
					},
					function () {
						// TODO: handle error
					}
				);

				// when the timezone selector changes, dynamically change the calendar option
				timeZoneSelectorEl.addEventListener("change", function () {
					calendar.setOption("timeZone", this.value);
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

			#top {
				background: #eee;
				border-bottom: 1px solid #ddd;
				padding: 0 10px;
				line-height: 40px;
				font-size: 12px;
			}
			.left {
				float: left;
				font-size: 150%;
			}
			.right {
				float: right;
			}
			.clear {
				clear: both;
			}

			#script-warning,
			#loading {
				display: none;
			}
			#script-warning {
				font-weight: bold;
				color: red;
			}

			#calendar {
				max-width: 1100px;
				margin: 40px auto;
				padding: 0 10px;
			}

			.tzo {
				color: #000;
			}
			#time-zone-selector{
				font-size: 100%;
			}		
.fc-direction-ltr .fc-daygrid-event.fc-event-end, .fc-direction-rtl .fc-daygrid-event.fc-event-start {
    flex-flow: wrap;
}
.fc-event-title{
	margin-left: 16px;
}
		</style>
	</head>
	<body>
		<div id="top">
			<div class="left">
			<?php echo $_local->gui->timezone;?>：
				<select id="time-zone-selector">
					<option value="local" selected>
						<?php echo $_local->gui->local;?>
					</option>
					<option value="UTC">UTC</option>
				</select>
			</div>

			<div class="right">
				<span id="loading">
					<?php echo $_local->gui->loading;?>...</span>
				<span id="script-warning"><code>php/get-events.php</code> must be running.</span>
			</div>

			<div class="clear"></div>
		</div>

		<div id="calendar"></div>
	</body>

</html>
<script>
	document.addEventListener('load',function(){
		$("button").each(function () {
				if ($(this).html() == "today") {
					$(this).html(gLocal.gui.today);
				}
				if ($(this).html() == "list") {
					$(this).html(gLocal.gui.list);
				}
				if ($(this).html() == "week") {
					$(this).html(gLocal.gui.week);
				}
				if ($(this).html() == "month") {
					$(this).html(gLocal.gui.month);
				}
				if ($(this).html() == "day") {
					$(this).html(gLocal.gui.day);
				}
			});
		$("button").click(function () {
				$("button").each(function () {
					if ($(this).html().indexOf("today") != -1) {
						$(this).html(gLocal.gui.today);
					}
					if ($(this).html().indexOf("list") != -1) {
						$(this).html(gLocal.gui.list);
					}
					if ($(this).html().indexOf("week") != -1) {
						$(this).html(gLocal.gui.week);
					}
					if ($(this).html().indexOf("month") != -1) {
						$(this).html(gLocal.gui.month);
					}
					if ($(this).html().indexOf("day") != -1 && $(this).html().indexOf("today") == -1) {
						$(this).html(gLocal.gui.day);
					}
				});
			});

		$("div").click(function () {
				$("button").each(function () {
					if ($(this).html().indexOf("today") != -1) {
						$(this).html(gLocal.gui.today);
					}
					if ($(this).html().indexOf("list") != -1) {
						$(this).html(gLocal.gui.list);
					}
					if ($(this).html().indexOf("week") != -1) {
						$(this).html(gLocal.gui.week);
					}
					if ($(this).html().indexOf("month") != -1) {
						$(this).html(gLocal.gui.month);
					}
					if ($(this).html().indexOf("day") != -1 && $(this).html().indexOf("today") == -1) {
						$(this).html(gLocal.gui.day);
					}
				});
			});
    })
for(wait_i=1;wait_i<50;wait_i++){
	if(document.readyState!="complete"){
		setTimeout("",100)
	}
	else{
		
		break;
	}
}
		


	</script>

