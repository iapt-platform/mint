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
						"<li><a onclick=\"coop_add('" +
						result[x].id +
						"')\">" +
						result[x].username +
						"[" +
						result[x].email +
						"]</a></li>";
				}
			}
			html += "</ul>";
			html += "<ul id='group_search_list'>";
			html += "</ul>";
			$("#search_result").html(html);

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
					let html1 = "";
					if (result.length > 0) {
						for (const iterator of result) {
							html1 +=
								"<li><a onclick=\"coop_add_group('" +
								iterator.id +
								"')\">" +
								iterator.name +
								"[" +
								iterator.username.nickname +
								"]</a></li>";
						}
					}
					$("#group_search_list").html(html1);
				}
			);
		}
	);
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

function coop_add(userid) {
	$.get(
		"../doc/coop.php",
		{
			do: "add",
			doc_id: coop_doc_id,
			user_id: userid,
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
