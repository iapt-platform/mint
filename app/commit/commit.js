var _commit_data;
var previewWin;
function commit_init(param) {
	previewWin = model_win_init({ container: "model_win" });
	_commit_data = param;

	previewWin.show(commit_preview_render());
}
function commit_render_head(step) {
	let html = "";
	html += "<div class='commit_head step" + step + "'>";
	html += "<div class='commit_step commit_step_1'><div class='num'>1</div><div>选择版本</div></div>";
	html += "<div class='commit_step commit_step_2'><div class='num'>2</div><div>预览</div></div>";
	html += "<div class='commit_step commit_step_3'><div class='num'>3</div><div>完成</div></div>";
	html += "</div>";
	return html;
}
function commit_render_channel_select() {
	let html = "";
	html += "<div class='commit_win_inner'>";
	html += commit_render_head(1);
	html += "<div>当前:";
	html += "<select id='src_channel' onchange='src_change(this)'>";
	if (typeof _commit_data.src == "undefined") {
		html += "<option value='' selected>请选择当前版本</option>";
	}
	for (const iterator of _my_channal) {
		html += "<option value='" + iterator.id + "' ";
		if (_commit_data.src == iterator.id) {
			html += " selected ";
		}
		html += ">" + iterator.name + "</option>";
	}
	html += "</select>";

	html += "</div>";

	html += "<div>推送到:";
	html += "<select id='dest_channel' onchange='dest_change(this)'>";
	if (typeof _commit_data.dest == "undefined") {
		html += "<option value='' selected>请选择推送到</option>";
	}
	for (const iterator of _my_channal) {
		html += "<option value='" + iterator.id + "' ";
		if (_commit_data.dest == iterator.id) {
			html += " selected ";
		}
		html += ">" + iterator.name + "</option>";
	}
	html += "</select>";
	html += "</div>";
	html += "<div id='commit_preview'></div>";

	html += "<button onclick='previewWin.show(commit_preview_render())'>预览</button>";
	html += "</div>";

	return html;
}

function commit_preview_render() {
	let html = "";
	html += "<div class='commit_win_inner'>";
	html += commit_render_head(2);

	if (
		typeof _commit_data.src != "undefined" &&
		_commit_data.src != null &&
		_commit_data.src != "" &&
		typeof _commit_data.dest != "undefined" &&
		_commit_data.dest != null &&
		_commit_data.dest != ""
	) {
		let sentList = new Array();
		for (const iterator of _arrData) {
			sentList.push(iterator.book + "-" + iterator.para + "-" + iterator.begin + "-" + iterator.end);
		}
		_commit_data.sent = sentList;
		let arrSentInfo = new Array();
		for (const iterator of _commit_data.sent) {
			let id = com_guid();
			arrSentInfo.push({ id: id, data: iterator });
		}

		if (arrSentInfo.length > 0) {
			let setting = new Object();
			setting.lang = "";
			setting.channal = _commit_data.src + "," + _commit_data.dest;
			$.post(
				"../term/note.php",
				{
					setting: JSON.stringify(setting),
					data: JSON.stringify(arrSentInfo),
				},
				function (data, status) {
					if (status == "success") {
						let sentData;
						let html = "";
						try {
							sentData = JSON.parse(data);
						} catch (e) {}
						html += "<div class='commit_win_inner'>";
						html += commit_render_head(2);
						html += "<div>";
						html += "<button onclick='previewWin.show(commit_render_channel_select())'>返回</button>";
						html += "<button onclick='commit_pull()'>推送</button>";
						html += "<button onclick='commit_close()'>放弃</button>";
						html += "</div>";
						html += "<div class='commit_compare'>";
						html += "<div class='pali'>巴利原文</div>";
						html += "<div class='src_text'>当前版本：" + channal_getById(_commit_data.src).name + "</div>";
						html += "<div class='dest_text'>推送到：" + channal_getById(_commit_data.dest).name + "</div>";
						html += "</div>";

						for (const iterator of sentData) {
							if (iterator.translation[0].id != "") {
								html += "<div class='commit_compare'>";
								html += "<div ><input type='checkbox' /></div>";
								html += "<div class='pali'>" + iterator.palitext + "</div>";
								html += "<div class='src_text'>";
								html += iterator.translation[0].text;
								html += "</div>";
								html += "<div class='dest_text'>";
								if (iterator.translation[1].id == "") {
									if (iterator.translation[0].id == "") {
										html += "无记录";
									} else {
										html += "<ins>" + iterator.translation[0].text + "</ins>";
									}
								} else {
									if (iterator.translation[0].update_time > iterator.translation[1].update_time) {
										html += "<del>" + iterator.translation[1].text + "</del><br>";
										html += "<ins>" + iterator.translation[0].text + "</ins>";
									} else {
										html += "[新]" + iterator.translation[1].text;
									}
								}
								html += "</div>";
								html += "</div>";
							}
						}
						html += "</div>";

						previewWin.show(html);
						//$("#commit_preview").html(html);
					}
				}
			);
			html += "加载中 请稍等……";
			html += "</div>";
			return html;
		} else {
			html += "没有句子被选择";
			html += "</div>";
			return html;
		}
	} else {
		return commit_render_channel_select();
	}
}
function commit_render_final(result) {
	let html = "";
	html += "<div class='commit_win_inner'>";
	html += commit_render_head(3);
	if (typeof result.update != "undefined") {
		html += "<div>修改：" + result.update + "</div>";
	}
	if (typeof result.insert != "undefined") {
		html += "<div>新增：" + result.insert + "</div>";
	}
	if (typeof result.pr != "undefined") {
		html += "<div>提交修改建议：" + result.pr + "</div>";
	}
	html +=
		"<div><a href='' onclick='window.reload()'>刷新页面</a>查看修改结果。<a onclick='previewWin.close()'>关闭</a></div>";
	html += "</div>";
	return html;
}
function commit_pull() {
	$.post(
		"../commit/commit.php",
		{
			data: JSON.stringify(_commit_data),
		},
		function (data, status) {
			if (status == "success") {
				let html = "";
				try {
					let result = JSON.parse(data);
					if (result.status == 0) {
						previewWin.show(commit_render_final(result));
					} else {
						alert(result.message);
					}
				} catch (e) {}
			}
		}
	);
}
function commit_close() {}
function dest_change(obj) {
	_commit_data.dest = $(obj).val();
}
function src_change(obj) {
	_commit_data.src = $(obj).val();
}
