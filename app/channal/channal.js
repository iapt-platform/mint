var _my_channal = null;
var gChannelId;
var get_channel_list_callback = null;
channal_list();
var share_win;
function channal_list_init() {
	my_channal_list();
	share_win = iframe_win_init({ container: "share_win", name: "share", width: "500px" });
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
						html += "<div style='flex:2;'>";
						html += "<guide url='../channal/card.php' gid='" + iterator.id + "'>";
						html += iterator.name;
						html += "</guide>";
						html += "</div>";
						html += "<div style='flex:2;'>";
						if (parseInt(iterator.power) == 30) {
							html += gLocal.gui.your;
						} else {
							html += "<guide url='../ucenter/card.php' gid='" + iterator.owner + "'>";
							html += iterator.nickname;
							html += "</guide>";
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

						switch (parseInt(iterator.power)) {
							case 10:
								html += "<div style='flex:1;'>";
								html += gLocal.gui.read_only;
								html += "</div>";
								html += "<div style='flex:1;'>";
								html += "</div>";
								break;
							case 20:
								html += "<div style='flex:1;'>";
								html += gLocal.gui.write;
								html += "</div>";
								html += "<div style='flex:1;'>";
								html +=
									"<a href='../channal/my_channal_edit.php?id=" +
									iterator.id +
									"'>" +
									gLocal.gui.edit +
									"</a>";
								html += "</div>";
								break;
							case 30:
								html += "<div style='flex:1;'>";
								html += gLocal.gui.owner;
								html += "</div>";
								html += "<div style='flex:1;'>";
								html +=
									"<a href='../channal/my_channal_edit.php?id=" +
									iterator.id +
									"'>" +
									gLocal.gui.edit +
									"</a>";
								html += " <a onclick=\"channel_share('" + iterator.id + "')\">Share</a>";
								html += "</div>";

								break;
							default:
								break;
						}

						html += "</div>";
					}
					$("#my_channal_list").html(html);
					guide_init();
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function channel_share(id) {
	share_win.show("../share/share.php?id=" + id + "&type=2");
}
/*
编辑channel信息
*/
function my_channal_edit(id) {
	gChannelId = id;
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
						"]</a></li>";
					html += "</div>";
					html += "</div>";
					html += "</div>";
					/*
					旧的channel分享方式 删除
					html += "<div id='coop_div' style='padding:5px;position: relative;'>";
					html += "<h2>" + gLocal.gui.cooperators + "</h2>";

					html +=
						"<button onclick='add_coop_user()'>" + gLocal.gui.add + gLocal.gui.cooperators + "</button>";
					html += "<div id='add_coop_user_dlg' class='float_dlg' style='left: 0;'></div>";

					html +=
						"<button onclick='add_coop_group()' >" +
						gLocal.gui.add +
						gLocal.gui.cooperate_group +
						"</button>";
					html += "<div id='add_coop_group_dlg' class='float_dlg' style='left: 0;'></div>";
					html += "<div id='coop_inner' >";
					if (typeof result.coop == "undefined" || result.coop.length == 0) {
						html += gLocal.gui.empty_null_mark;
					} else {
						for (const coop of result.coop) {
							html += '<div class="file_list_row" style="padding:5px;">';
							if (coop.type == 0) {
								html += '<div style="flex:1;">' + gLocal.gui.personal + "</div>";
								html += "<div style='flex:3;'>" + coop.user_name.nickname + "</div>";
							} else {
								html += '<div style="flex:1;">' + gLocal.gui.group + "</div>";
								html += "<div style='flex:3;'>" + coop.user_name.name + "</div>";
							}

							html += "<div style='flex:3;'>" + coop.power + "</div>";
							html += "<div class='hover_button' style='flex:3;'>";
							html += "<button>" + gLocal.gui.remove + "</button>";
							html += "</div>";
							html += "</div>";
						}
					}
					html += "</div>";
					html += "</div>";
*/
					$("#channal_info").html(html);
					user_select_dlg_init("add_coop_user_dlg");
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

function add_coop_user() {
	user_select_dlg_show();
}

function user_selected(id) {
	$.post(
		"../channal/coop_new_user.php",
		{
			userid: id,
			channel_id: gChannelId,
		},
		function (data) {
			let error = JSON.parse(data);
			if (error.status == 0) {
				user_select_cancel();
				alert("ok");
				location.reload();
			} else {
				alert(error.message);
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
