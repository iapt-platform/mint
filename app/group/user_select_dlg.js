var _user_select_dlg_div;
function user_select_dlg_init(div) {
	_user_select_dlg_div = div;
	let html = "";
	html += "<div id='user_select_dlg'>";
	html += "<div >";
	html += "<div >" + gLocal.gui.nick_name + "/" + gLocal.gui.group + "</div>";
	html += "<input type='text' id='user_select_title' maxlength='32' placeholder='" + gLocal.gui.name + "' ";
	html += 'onkeyup="username_search_keyup(event,this)"';
	html += "/>";
	html += "</div>";
	html += "<div id='user_list'>";
	html += "</div>";
	html += "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
	html += "<div>";

	html += "</div>";
	html += "<div>";
	html += "<button onclick='user_select_cancel()'>" + gLocal.gui.cancel + "</button>";
	html += "</div>";
	html += "</div>";
	html += "</div>";
	$("#" + div).append(html);
}

function user_select_dlg_show() {
	$("#" + _user_select_dlg_div).show();
}
function user_select_dlg_hide() {
	$("#" + _user_select_dlg_div).hide();
}
function user_select_cancel() {
	user_select_dlg_hide();
	$("#user_select_title").val("");
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
		//dict_search(obj.value);
	} else {
		username_search(obj.value, 1);
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
					for (x in result) {
						html +=
							"<li><a onclick=\"user_selected('" +
							result[x].id +
							"',0)\">" +
							result[x].nickname +
							"[" +
							result[x].email +
							"]</a></li>";
					}
				}
				html += "</ul>";
				$("#user_list").html(html);
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
					for (const iterator of result) {
						html +=
							"<li><a onclick=\"coop_add('" +
							iterator.id +
							"',1)\">" +
							iterator.name +
							"[" +
							iterator.username.nickname +
							"]</a></li>";
					}
				}
				html += "</ul>";
				$("#search_result").html(html);
			}
		);
	}
}
