function username_search_keyup(e, obj) {
	var keynum;
	var keychar;
	var numcheck;

	if ($("#wiki_search_input").val() == "") {
		$("#search_result").html("");
		return;
	}

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
		username_search(obj.value);
	}
}

function username_search(keyword) {
	let obj = document.querySelector("#cooperator_type_user");
	if (obj && obj.checked == true) {
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
					for (const iterator of result) {
						html +=
							"<li><a onclick=\"coop_add('" +
							iterator.id +
							"',0)\">" +
							iterator.username +
							"[" +
							iterator.email +
							"]</a></li>";
					}
				}
				html += "</ul>";
				$("#search_result").html(html);
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

var coop_show_div_id = "";
var coop_doc_id = "";
function coop_init(doc_id, strDivId) {
	coop_show_div_id = strDivId;
	coop_doc_id = doc_id;
}

function coop_list() {
	$.get(
		"../doc/coop.php",
		{
			do: "list",
			doc_id: coop_doc_id,
		},
		function (data, status) {
			$("#" + coop_show_div_id).html(data);
		}
	);
}

function coop_add(userid, type) {
	$.get(
		"../doc/coop.php",
		{
			do: "add",
			doc_id: coop_doc_id,
			user_id: userid,
			type: type,
		},
		function (data, status) {
			$("#" + coop_show_div_id).html(data);
		}
	);
}

function coop_del(userid) {
	$.get(
		"../doc/coop.php",
		{
			do: "del",
			doc_id: coop_doc_id,
			user_id: userid,
		},
		function (data, status) {
			$("#" + coop_show_div_id).html(data);
		}
	);
}
function coop_set(userid, value) {
	$.get(
		"../doc/coop.php",
		{
			do: "set",
			doc_id: coop_doc_id,
			user_id: userid,
			value: value,
		},
		function (data, status) {
			$("#" + coop_show_div_id).html(data);
		}
	);
}

function coop_power_change(userid, obj) {
	coop_set(userid, obj.value);
}
