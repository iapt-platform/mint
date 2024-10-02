var _group_add_dlg_div;
function group_add_dlg_init(div) {
	_group_add_dlg_div = div;
	let html = "";
	html += "<div id='group_add_dlg'>";
	html += "<div >";
	html += "<div >" + gLocal.gui.name + "</div>";
	html += "<input type='text' id='group_add_title' maxlength='32' placeholder='" + gLocal.gui.name + "'/>";
	html += "</div>";
	html += "<div>";
	html += "</div>";
	html += "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
	html += "<div>";

	html += "</div>";
	html += "<div>";
	html += "<button onclick='group_add_cancel()'>" + gLocal.gui.cancel + "</button>";
	html += "<button onclick='group_add_new()'>" + gLocal.gui.new + "</button>";
	html += "</div>";
	html += "</div>";
	html += "</div>";
	$("#" + div).append(html);
}
function team_add_dlg_init(div) {
	_group_add_dlg_div = div;
	let html = "";
	html += "<div id='group_add_dlg'>";
	html += "<div >";
	html += "<div >" + gLocal.gui.name + "</div>";
	html += "<input type='text' id='group_add_title' maxlength='32' placeholder='" + gLocal.gui.name + "'/>";
	html += "</div>";
	html += "<div>";
	html += "</div>";
	html += "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
	html += "<div>";

	html += "</div>";
	html += "<div>";
	html += "<button onclick='group_add_cancel()'>" + gLocal.gui.cancel + "</button>";
	html += "<button onclick='group_add_new()'>" + gLocal.gui.new + "</button>";
	html += "</div>";
	html += "</div>";
	html += "</div>";
	$("#" + div).append(html);
}
function group_add_dlg_show() {
	$("#" + _group_add_dlg_div).show();
}
function group_add_dlg_hide() {
	$("#" + _group_add_dlg_div).hide();
}
function group_add_cancel() {
	group_add_dlg_hide();
	$("#group_add_title").val("");
}

function group_add_new() {
	if ($("#group_add_title").val() == "") {
		alert("group name is empty!");
		return;
	}
	let parentid = 0;
	if (typeof gGroupId != "undefined") {
		parentid = gGroupId;
	}
	$.post(
		"../group/my_group_put.php",
		{
			name: $("#group_add_title").val(),
			parent: parentid,
		},
		function (data) {
			let error = JSON.parse(data);
			if (error.status == 0) {
				alert("ok");
				group_add_cancel();
				location.reload();
			} else {
				alert(error.message);
			}
		}
	);
}
