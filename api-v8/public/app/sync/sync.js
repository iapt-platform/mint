var sync_db_list = [
	{ script: "sync/table_channel.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_article.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_article_collect.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_term_channel.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_term_editor.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_sentence.php", count: -1, finished: 0, enable: true },
	{ script: "sync/table_wbw_block.php", count: -1, finished: 0, enable: false },
	{ script: "sync/table_wbw.php", count: -1, finished: 0, enable: false },
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
	sync_do_db($("#sync_server_address").val(), $("#sync_local_address").val(), 0);
}
function sync_push() {
	isStop = false;
	sync_curr_do_db = 0;
	$("#sync_log").html("working"); //
	sync_do_db($("#sync_local_address").val(), $("#sync_server_address").val(), 0);
}
function sync_stop() {
	isStop = true;
}
var retryCount = 0;
function sync_do_db(src, dest, time = 1) {
	let size = 500;
	//找到下一个有效的数据库
	while (sync_db_list[sync_curr_do_db].enable == false) {
		sync_curr_do_db++;
		if (sync_curr_do_db >= sync_db_list.length) {
			$("#sync_log").html($("#sync_log").html() + "<br>All Done"); //
			return;
		}
	}
	if (time == 0) {
		time = localStorage.getItem(sync_db_list[sync_curr_do_db].script + src);
		if (time) {
			time = parseInt(time);
		} else {
			time = 1;
		}
	}
	if (sync_db_list[sync_curr_do_db].count < 0) {
		//获取全部数据条数，用来绘制进度条
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
				let myDate = new Date();
				myDate.setTime(time);

				$("#sync_log").html(
					$("#sync_log").html() +
						"<div><h2>" +
						sync_db_list[sync_curr_do_db].script +
						myDate +
						"</h2>" +
						result.message +
						"</div>"
				); //
				render_progress();
				if (isStop) {
					return;
				}
				if (result.error > 0 && retryCount < 2) {
					//失败重试
					retryCount++;
					sync_do_db(src, dest, time);
					return;
				}
				retryCount = 0;
				sync_db_list[sync_curr_do_db].finished += parseInt(result.src_row);
				localStorage.setItem(sync_db_list[sync_curr_do_db].script + src, result.time);
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

function db_selected(obj) {
	let index = $(obj).attr("index");
	sync_db_list[index].enable = obj.checked;
}

function render_progress() {
	let html = "";
	for (let index = 0; index < sync_db_list.length; index++) {
		const element = sync_db_list[index];
		let spanWidth = parseInt((500 * element.finished) / element.count);
		html += "<div style='width:500px;background-color:white;color:black;'>";
		html += "<input type='checkbox' index='" + index + "' ";
		if (element.enable) {
			html += "checked";
		}
		html += " onclick='db_selected(this)' />";
		html +=
			"<span style='background-color:green;display:inline-block;width:" +
			spanWidth +
			"px;'>" +
			element.script +
			"|" +
			element.finished +
			"/" +
			element.count +
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
