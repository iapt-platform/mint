/**
 * 显示句子历史记录窗口
 */

function historay_init() {
	$("body").append('<div id="sent_history_dlg" title="History"><div id="sent_history_content"></div></div>');
	$("#sent_history_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: "Save",
				click: function () {
					$(this).dialog("close");
				},
			},
			{
				text: "Cancel",
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}

function history_show(id) {
	$.get(
		"../usent/historay_get.php",
		{
			id: id,
		},
		function (data) {
			let result = JSON.parse(data);
			let html = "";
			if (result.status == 0) {
				let currDate = new Date();

				for (const iterator of result.data) {
					let pass = currDate.getTime() - iterator.date;
					let strPassTime = "";
					if (pass < 60 * 1000) {
						//一分钟内
						strPassTime = Math.floor(pass / 1000) + "秒前";
					} else if (pass < 3600 * 1000) {
						//一小时内
						strPassTime = Math.floor(pass / 1000 / 60) + "分钟前";
					} else if (pass < 3600 * 24 * 1000) {
						//一天内
						strPassTime = Math.floor(pass / 1000 / 3600) + "小时前";
					} else if (pass < 3600 * 24 * 7 * 1000) {
						//一周内
						strPassTime = Math.floor(pass / 1000 / 3600 / 24) + "天前";
					} else if (pass < 3600 * 24 * 30 * 1000) {
						//一个月内
						strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 7) + "周前";
					} else {
						//超过一个月
						strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 30) + "月前";
					}
					if (iterator.userinfo.username == getCookie("username")) {
						html += "<div class=''>You</div>";
					} else {
						html += "<div class=''>" + iterator.userinfo.nickname + "</div>";
					}

					html += "<div class=''>" + strPassTime + "</div>";
					html += "<div class=''>" + iterator.text + "</div>";
				}
				$("#sent_history_content").html(html);
				$("#sent_history_dlg").dialog("open");
			} else {
				ntf_show(result.error);
			}
		}
	);
}
