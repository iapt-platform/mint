var _my_channal = null;
var get_channel_list_callback = null;
channal_list();

function channal_list_init() {
	my_channal_list();
	channal_add_dlg_init("channal_add_div");
}
function channal_list() {
	$.post("../channal/get.php", {}, function (data) {
		try {
			_my_channal = JSON.parse(data);
			if (get_channel_list_callback) {
				get_channel_list_callback();
			}
		} catch (e) {
			console.error(e);
		}
	});
}

function channal_getById(id) {
	for (const iterator of _my_channal) {
		if (iterator.id == id) {
			return iterator;
		}
	}
	return false;
}

function my_channal_list() {
	$.get(
		"../channal/get.php",
		{
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let html = "";
					let result = JSON.parse(data);
					let key = 1;
					for (const iterator of result) {
						html += '<div class="file_list_row" style="padding:5px;">';
						html += '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
						html += "<div style='flex:1;'>" + key++ + "</div>";
						html += "<div style='flex:2;'>" + iterator.name + "</div>";
						html += "<div style='flex:2;'>" + iterator.nickname + "</div>";
						html += "<div style='flex:2;'>";
						let arrStatus = [
							{ id: 0, string: gLocal.gui.disable },
							{ id: 10, string: gLocal.gui.private },
							{ id: 30, string: gLocal.gui.public },
						];
						for (const status of arrStatus) {
							if (parseInt(iterator.status) == status.id) {
								html += status.string;
							}
						}
						//render_status(iterator.status) +
						html += "</div>";
						html +=
							"<div style='flex:1;'><a href='../channal/my_channal_edit.php?id=" +
							iterator.id +
							"'>" +
							gLocal.gui.edit +
							"</a></div>";
						html += "<div style='flex:1;'>15</div>";
						html += "</div>";
					}
					$("#my_channal_list").html(html);
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function my_channal_edit(id) {
	$.get(
		"../channal/my_channal_get.php",
		{
			id: id,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let html = "";
					let result = JSON.parse(data);
					$("#article_collect").attr("a_id", result.id);
					html += '<div class="" style="padding:5px;">';
					html += '<div style="max-width:2em;flex:1;"></div>';
					html += "</div>";
					html += "<div style='display:flex;height: 20em;'>";
					html += "<div style='flex:4;padding: 1em;'>";
					html += "<input type='hidden' name='id' value='" + result.id + "'/>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">'+gLocal.gui.name+'</div>';
					html += '<div style="flex:8;">';
					html +=
						"<input type='input' name='name' value='" +
						result.name +
						"' maxlength='32' placeholder='📝≤32'/>";
					html += "</div>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">'+gLocal.gui.introduction+'</div>';
					html += '<div style="flex:8;">';
					html += "<textarea name='summary'>" + result.summary + "</textarea>";
					html += "</div>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">'+gLocal.gui.language_select+'</div>';
					html += '<div style="flex:8;">';
					html +=
						'<input id="channal_lang_select" type="input"  onchange="channal_lang_change()"  title="type language name/code" code="' +
						result.lang +
						'" value="' +
						result.lang +
						'" > <input id="channal_lang" type="hidden" name="lang" value="' +
						result.lang +
						'">';
					html += "</div>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">'+gLocal.gui.privacy+'</div>';
					html += '<div style="flex:8;">';
					let arrStatus = [
						{ id: 0, string: gLocal.gui.disable },
						{ id: 10, string: gLocal.gui.private },
						{ id: 30, string: gLocal.gui.public },
					];
					html += "<select id = 'status'  name = 'status'>";
					for (const iterator of arrStatus) {
						html += "<option ";
						if (parseInt(result.status) == iterator.id) {
							html += " selected ";
						}
						html += " value='" + iterator.id + "'>" + iterator.string + "</option>";
					}

					html += "</select>";
					html += "</div>";
					html += "</div>";
					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' ></div>";
					html += "</div>";

					html += "</div>";

					$("#channal_info").html(html);
					tran_lang_select_init("channal_lang_select");
					//$("#aritcle_status").html(render_status(result.status));
					$("#channal_title").html(result.name);
					$("#preview_inner").html();
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function channal_lang_change() {
	let lang = $("#channal_lang_select").val();
	if (lang.split("_").length == 3) {
		$("#channal_lang").val(lang.split("_")[2]);
	} else {
		$("#channal_lang").val(lang);
	}
}

function my_channal_save() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../channal/my_channal_post.php", //url
		data: $("#channal_edit").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert("保存成功");
			} else {
				alert("error:" + result.message);
			}
		},
		error: function (data, status) {
			alert("异常！" + status + data.responseText);
			switch (status) {
				case "timeout":
					break;
				case "error":
					break;
				case "notmodified":
					break;
				case "parsererror":
					break;
				default:
					break;
			}
		},
	});
}
