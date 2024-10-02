var _res_id;
var _res_type;
var gUserList = new Array();

function share_load(id, type) {
	refresh_coop_list(id, type);
}

function refresh_coop_list(id, type) {
	$.get(
		"../share/coop_get.php",
		{
			res_id: id,
			res_type: type,
		},
		function (data, status) {
			if (status == "success") {
				let result = JSON.parse(data);
				$("#coop_list").html(render_coop_list(result));
			}
		}
	);
}

function render_coop_list(cooplist) {
	let html = "";
	if (typeof cooplist == "undefined" || cooplist.length == 0) {
		html += gLocal.gui.empty_null_mark;
	} else {
		for (const coop of cooplist) {
			html += '<div class="file_list_row" style="padding:5px;">';
			let username;
			if (coop.cooperator_type == 0) {
				username = coop.user.nickname;
				html += '<div style="flex:1;" title="' + gLocal.gui.personal + '">';
				html += "<svg class='icon'>";
				html += "	<use xlink:href='../studio/svg/icon.svg#ic_person'></use>";
				html += "</svg>";
				html += "</div>";
				html += "<div style='flex:3;'>" + username + "</div>";
			} else {
				username = coop.user;
				html += '<div style="flex:1;" title="' + gLocal.gui.group + '">';
				html += "<svg class='icon'>";
				html += "	<use xlink:href='../studio/svg/icon.svg#ic_two_person'></use>";
				html += "</svg>";
				html += "</div>";
				html += "<div style='flex:3;'>";
				if (coop.parent_name != "") {
					html += coop.parent_name + "/";
				}
				html += username + "</div>";
			}

			html += "<div style='flex:3;'>";
			let power = [
				{ id: 10, string: gLocal.gui.viewer },
				{ id: 20, string: gLocal.gui.editor },
			];
			html += "<select onchange=\"coop_set_power('" + coop.cooperator_id + "',this)\">";
			for (const iterator of power) {
				html += "<option value='" + iterator.id + "' ";
				if (iterator.id == coop.power) {
					html += " selected ";
				}
				html += ">" + iterator.string + "</option>";
			}
			html += "</select>";

			html += "</div>";
			html += "<div class='hover_button' style='flex:3;'>";
			html +=
				"<button onclick=\"coop_remove('" +
				coop.cooperator_id +
				"','" +
				username +
				"')\">" +
				gLocal.gui.remove +
				"</button>";
			html += "</div>";
			html += "</div>";
		}
	}
	return html;
}

function username_search_keyup(e, obj) {
	var keynum;
	if (window.event) {
		// IE
		keynum = e.keyCode;
	} else if (e.which) {
		// Netscape/Firefox/Opera
		keynum = e.which;
	}
	var keychar = String.fromCharCode(keynum);
	if (keynum == 13) {
	} else {
		if (obj.value.length > 0) {
			let type = $("#user_type").val();
			username_search(obj.value, type);
		} else {
			$("#user_search").html("");
		}
	}
}

function user_selected(id, name, type) {
	if (parseInt(type) == 0) {
		gUserList.push({ id: id, name: name, type: type });
		$("#user_list").html(render_user_list());
		$("#user_search").html("");
	} else {
		$.get("../group/get.php", { id: id }, function (data, status) {
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					gUserList.push({ id: id, name: name, type: type, project: result.children });
					$("#user_list").html(render_user_list());
					$("#user_search").html("");
				} catch (e) {}
			}
		});
	}
}
function render_user_list() {
	let html = "<ul>";
	let arrIndex = 0;
	for (const iterator of gUserList) {
		html += "<li>";
		html += "<span>";
		if (iterator.type == 1) {
			//小组
			html += "👥";
		} else {
			html += "👤";
		}
		html += iterator.name;
		html += "</span>";
		html += "<a class='btn_del' onclick=\"userlist_del(' + arrIndex + ')\">"+gLocal.gui.delete+"</a>";
		html += "</li>";
		arrIndex++;
	}
	html += "</ul>";
	return html;
}

//从候选列表中删除一个元素
function userlist_del(index) {
	let deleted = gUserList.splice(index, 1);
	$("#user_list").html(render_user_list());
	if (gUserList.length == 0) {
		$("#coop_new_tools").hide();
	}
}
function username_search(keyword, type) {
	//let obj = document.querySelector("#cooperator_type_user");
	if (type == 1) {
		$.get(
			"../ucenter/get.php",
			{
				username: keyword,
			},
			function (data, status) {
				let result;
				try {
					result = JSON.parse(data);
				} catch (error) {
					console(error);
				}
				let html = "<ul id='user_search_list'>";
				if (result.length > 0) {
					$("#coop_new_tools").show();
					for (const iterator of result) {
						html +=
							"<li onclick=\"user_selected('" +
							iterator.id +
							"','" +
							iterator.username +
							"',0)\">" +
							iterator.nickname +
							"@" +
							iterator.username +
							"</li>";
					}
				} else {
					$("#coop_new_tools").hide();
				}
				html += "</ul>";
				$("#user_search").html(html);
			}
		);
	} else {
		$.get(
			"../group/get_name.php",
			{
				name: keyword,
			},
			function (data, status) {
				let result;
				try {
					result = JSON.parse(data);
				} catch (error) {
					console(error);
				}
				let html = "<ul id='user_search_list'>";
				if (result.length > 0) {
					$("#coop_new_tools").show();
					for (const iterator of result) {
						html +=
							"<li onclick=\"user_selected('" +
							iterator.uid +
							"','" +
							iterator.name +
							"',1)\">" +
							iterator.name +
							"</li>";
					}
				} else {
					$("#coop_new_tools").hide();
				}
				html += "</ul>";
				$("#user_search").html(html);
			}
		);
	}
}

function add_coop() {
	let coopList = new Array();
	for (const itUser of gUserList) {
		coopList.push({ id: itUser.id, type: itUser.type });
		/*
		if (itUser.type == 0) {
			coopList.push({ id: itUser.id, type: itUser.type });
		} else if (itUser.type == 1) {
			let obj = document.querySelector("#prj_" + itUser.id);
			if (obj.checked) {
				coopList.push({ id: itUser.id, type: itUser.type });
			}
			if (typeof itUser.project != "undefined") {
				for (const project of itUser.project) {
					obj = document.querySelector("#prj_" + project.id);
					if (obj.checked) {
						coopList.push({ id: project.id, type: itUser.type });
					}
				}
			}
		}
		*/
	}
	$.post(
		"../share/coop_put.php",
		{
			res_id: _res_id,
			res_type: _res_type,
			user_info: JSON.stringify(coopList),
			power: $("#coop_new_power").val(),
		},
		function (data, status) {
			cancel_coop();
			let result = JSON.parse(data);
			if (parseInt(result.status) == 0) {
				refresh_coop_list(_res_id, _res_type);
			} else {
				alert(result.message);
			}
		}
	);
}

function cancel_coop() {
	$("#user_list").html("");
	$("#user_search").html("");
	$("#coop_new_tools").hide();
	$("#search_user").val("");
	gUserList = new Array();
}

function coop_remove(userid, username) {
	let strMsg = "要删除%name%吗？";
	if (confirm(strMsg.replace("%name%", username))) {
		$.post(
			"../share/coop_del.php",
			{
				res_id: _res_id,
				res_type: _res_type,
				user_id: userid,
			},
			function (data, status) {
				cancel_coop();
				let result = JSON.parse(data);
				if (parseInt(result.status) == 0) {
					refresh_coop_list(_res_id, _res_type);
				} else {
					alert(result.message);
				}
			}
		);
	}
}

function coop_set_power(userid, power) {
	{
		$.post(
			"../share/coop_post.php",
			{
				res_id: _res_id,
				res_type: _res_type,
				user_id: userid,
				power: $(power).val(),
			},
			function (data, status) {
				cancel_coop();
				let result = JSON.parse(data);
				if (parseInt(result.status) == 0) {
					refresh_coop_list(_res_id, _res_type);
				} else {
					alert(result.message);
				}
			}
		);
	}
}
