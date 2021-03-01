var _channal_add_dlg_div;
function channal_add_dlg_init(div) {
	_channal_add_dlg_div = div;
	let html = "";
	html += "<div id='channal_add_dlg'>";
	html += "<div >";
	html += "<div >" + gLocal.gui.name + "</div>";
	html += "<guide gid='channel_guide'></guide>"
	html += "<input type='input' id='channal_add_title' maxlength='32' placeholder='" + gLocal.gui.name + "'/>";
	html += "</div>";
	html += "<div>";
	html += "</div>";
	html += "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
	html += "<div>";
	html += "<select id='channal_add_dlg_lang' name='lang'>";
	html += "<option value='zh'>Chinese-中文</option>";
	html += "<option value='en'>English-English</option>";
	html += "<option value='my'>Mymarnese-မြန်မာ</option>";
	html += "<option value='si'>Sinhalese-සිංහල</option>";
	html += "</select>";
	html += "<select id='channal_add_dlg_status' name='status'>";
	html += "<option value='10'>" + gLocal.gui.private + "</option>";
	html += "<option value='30'>" + gLocal.gui.public + "</option>";
	html += "</select>";

	html += "</div>";
	html += "<div>";
	html += "<button onclick='channal_add_cancel()'>" + gLocal.gui.cancel + "</button>";
	html += "<button onclick='channal_add_new()'>" + gLocal.gui.new + "</button>";
	html += "</div>";
	html += "</div>";
	html += "</div>";

	$("#" + div).append(html);
	guide_init();
}

function channal_add_dlg_show() {
	$("#" + _channal_add_dlg_div).show();
}
function channal_add_dlg_hide() {
	$("#" + _channal_add_dlg_div).hide();
}
function channal_add_cancel() {
	channal_add_dlg_hide();
	$("#channal_add_title").val("");
}

function channal_add_new() {
	if ($("#channal_add_title").val() == "") {
		alert("channal name is empty!");
		return;
	}
	$.post(
		"../channal/my_channal_put.php",
		{
			name: $("#channal_add_title").val(),
			lang: $("#channal_add_dlg_lang").val(),
			status: $("#channal_add_dlg_status").val(),
		},
		function (data) {
			let error = JSON.parse(data);
			if (error.status == 0) {
				alert("ok");
				channal_add_cancel();
				location.reload();
			} else {
				alert(error.message);
			}
		}
	);

}
