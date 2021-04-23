var sync_db_list = new Array("term/sync_index.php");
//var sync_db_list = ["doc/sync_index.php", "term/sync_index.php", "usent/sync.php"];
var sync_curr_do_db = 0;
function sync_index_init() { }

function sync_pull() {
	sync_curr_do_db = 0;
	$("#sync_result").html("working"); //
	sync_do_db($("#sync_server_address").val(), $("#sync_local_address").val(), 1);
}
function sync_push() {
	sync_curr_do_db = 0;
	$("#sync_result").html("working"); //
	sync_do_db($("#sync_local_address").val(), $("#sync_server_address").val(), 1);
}
function sync_do_db(src, dest, time = 1) {
	let size = 500;
	$.get(
		"sync.php",
		{
			server: src,
			localhost: dest,
			path: sync_db_list[sync_curr_do_db],
			time: time,
			size: size,
		},
		function (data) {
			let result;
			try {
				result = JSON.parse(data);
			} catch (error) {
				console.error(error + " data:" + data);
				return;
			}
			$("#sync_result").html($("#sync_result").html() + "<br>" + result.message + "<br>" + result.src_row); //
			if (result.src_row >= size) {
				sync_do_db(src, dest, result.time);
			} else {
				sync_curr_do_db++;
				if (sync_curr_do_db < sync_db_list.length) {
					sync_do_db(src, dest, 1);
				} else {
					$("#sync_result").html($("#sync_result").html() + "<br>All Done"); //
				}
			}
		}
	);
}

function login() {
	$("#server_msg").html("正在登录<br>");
	$.post(
		"login.php",
		{
			userid: $("#userid").val(),
			password: $("#password").val(),
			server: $("#sync_server_address").val(),
		},
		function (data) {
			let result = JSON.parse(data);
			$("#server_msg").html(result.message);
		}
	);
}
