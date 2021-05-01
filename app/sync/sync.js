var sync_db_list = [
	{ script: "sync/table_article.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_term.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_article_collect.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_sentence.php", count: -1, finished: 0, enable: true },
];
var isStop = false;
var sync_curr_do_db = 0;
function sync_index_init() {
	render_progress();
}

function sync_pull() {
	sync_curr_do_db = 0;
	isStop = false;
	$("#sync_log").html("working"); //
	sync_do_db($("#sync_server_address").val(), $("#sync_local_address").val(), 1);
}
function sync_push() {
	isStop = false;
	sync_curr_do_db = 0;
	$("#sync_log").html("working"); //
	sync_do_db($("#sync_local_address").val(), $("#sync_server_address").val(), 1);
}
function sync_stop() {
	isStop = true;
}
function sync_do_db(src, dest, time = 1) {
	let size = 500;
	while (sync_db_list[sync_curr_do_db].enable == false) {
		sync_curr_do_db++;
		if (sync_curr_do_db >= sync_db_list.length) {
			$("#sync_log").html($("#sync_log").html() + "<br>All Done"); //
			return;
		}
	}
	if (sync_db_list[sync_curr_do_db].count < 0) {
		$.get(
			"sync.php",
			{
				server: src,
				localhost: dest,
				path: sync_db_list[sync_curr_do_db].script,
				time: time,
				size: -1,
			},
			function (data) {
				let result;
				try {
					result = JSON.parse(data);
					sync_db_list[sync_curr_do_db].count = parseInt(result.data);
					sync_do_db(src, dest, time);
				} catch (error) {
					console.error(error + " data:" + data);
					return;
				}
			}
		);
	} else {
		$.get(
			"sync.php",
			{
				server: src,
				localhost: dest,
				path: sync_db_list[sync_curr_do_db].script,
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
				$("#sync_log").html(
					$("#sync_log").html() +
						"<div><h2>" +
						sync_db_list[sync_curr_do_db].script +
						"</h2>" +
						result.message +
						"</div>"
				); //
				render_progress();
				if (isStop) {
					return;
				}
				sync_db_list[sync_curr_do_db].finished += parseInt(result.src_row);
				if (result.src_row >= size) {
					//没弄完，接着弄
					sync_do_db(src, dest, result.time);
				} else {
					sync_curr_do_db++;

					if (sync_curr_do_db < sync_db_list.length) {
						while (sync_db_list[sync_curr_do_db].enable == false) {
							sync_curr_do_db++;
							if (sync_curr_do_db >= sync_db_list.length) {
								$("#sync_log").html($("#sync_log").html() + "<br>All Done"); //
								return;
							}
						}
						sync_do_db(src, dest, 1);
					} else {
						$("#sync_log").html($("#sync_log").html() + "<br>All Done"); //
					}
				}
			}
		);
	}
}
function render_progress() {
	let html = "";
	for (const iterator of sync_db_list) {
		let spanWidth = parseInt((500 * iterator.finished) / iterator.count);
		html +=
			"<div style='width:500px;background-color:white;color:black;'><span style='background-color:green;display:inline-block;width:" +
			spanWidth +
			"px;'>" +
			iterator.script +
			"|" +
			iterator.finished +
			"/" +
			iterator.count +
			"<span></div>";
	}
	$("#sync_result").html(html);
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
