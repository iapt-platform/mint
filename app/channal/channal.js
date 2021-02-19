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
						html += "<div style='flex:2;'>";
						if (iterator.username == getCookie("username")) {
							html += gLocal.gui.your;
						} else {
							html += iterator.nickname;
						}

						html += "</div>";
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
						html += "<div style='flex:1;'></div>";
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

/*
编辑channel信息
*/
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

					html += "<div style='width: 60%;padding: 1em;min-width: 25em;'>";
					html += '<div style="display:flex;line-height:32px;">';
					html += "<input type='hidden' name='id' value='" + result.id + "'/>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += "<div style='flex:2;'>" + gLocal.gui.title + "</div>";
					html += "<div style='flex:8;'>";
					html +=
						"<input type='input' name='name' value='" +
						result.name +
						"' maxlength='32' placeholder='channel title'/>";
					html += "</div>";
					html += "</div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.introduction + "</div>";
					html += "<div style='flex:8;'>";
					html += "<textarea name='summary'>" + result.summary + "</textarea>";
					html += "</div>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">' + gLocal.gui.language_select + "</div>";
					html += '<div style="flex:8;">';
					html +=
						'<input id="channal_lang_select" type="input"  onchange="channal_lang_change()"' +
						' placeholder = "try type chinese or en " ' +
						'  title="type language name/code" code="' +
						result.lang +
						'" value="' +
						result.lang +
						'" > <input id="channal_lang" type="hidden" name="lang" value="' +
						result.lang +
						'">';
					html += "</div>";
					html += "</div>";

					html += '<div style="display:flex;line-height:32px;">';
					html += '<div style="flex:2;">' + gLocal.gui.privacy + "</div>";
					html += '<div style="flex:8;">';
					let arrStatus = [
						{ id: 0, string: gLocal.gui.disable, note: gLocal.gui.disable_note },
						{ id: 10, string: gLocal.gui.private, note: gLocal.gui.private_note },
						{ id: 30, string: gLocal.gui.public, note: gLocal.gui.public_note },
					];
					html += "<select id = 'status'  name = 'status' onchange='status_change(this)'>";
					let status_note = "";
					for (const iterator of arrStatus) {
						html += "<option ";
						if (parseInt(result.status) == iterator.id) {
							html += " selected ";
							status_note = iterator.note;
						}
						html += " value='" + iterator.id + "'>" + iterator.string + "</option>";
					}

					html += "</select>";
					html +=
						"<span id = 'status_help' style='margin: 0 1em;'>" +
						status_note +
						"</span><a href='#' target='_blank'>[" +
						gLocal.gui.infomation +
						"]</li>";
					html += "</div>";
					html += "</div>";
					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' ></div>";
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

function status_change(obj) {
	let arrStatus = [
		{ id: 0, string: gLocal.gui.disable, note: gLocal.gui.disable_note },
		{ id: 10, string: gLocal.gui.private, note: gLocal.gui.private_note },
		{ id: 30, string: gLocal.gui.public, note: gLocal.gui.public_note },
	];
	let newStatus = $(obj).val();
	for (const iterator of arrStatus) {
		if (parseInt(newStatus) == iterator.id) {
			$("#status_help").html(iterator.note);
		}
	}
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
