var _article_add_dlg_div;
function article_add_dlg_init(div) {
	_article_add_dlg_div = div;
	let html = "";
	html += "<div id='article_add_dlg'>";
	html += "<fieldset class='broder-1 broder-r'>";
	html += "<legend>" + gLocal.gui.title + "</legend>";
	html += "<input type='input' id='article_add_title' placeholder='" + gLocal.gui.input + gLocal.gui.title + "' />";
	html += "</fieldset>";
	html += "<div>";
	html += "</div>";
	html += "<div style='display:flex;justify-content: space-evenly;padding-top: 1em;'>";
	html += "<button onclick='article_add_cancel()'>" + gLocal.gui.cancel + "</button>";
	html += "<button onclick='article_add_new()'>" + gLocal.gui.new + "</button>";
	html += "</div>";
	html += "</div>";

	$("#" + div).append(html);
}

function article_add_dlg_show() {
	$("#" + _article_add_dlg_div).show();
}
function article_add_dlg_hide() {
	$("#" + _article_add_dlg_div).hide();
}
function article_add_cancel() {
	article_add_dlg_hide();
	$("#article_add_title").val("");
}

function article_add_new() {
	$.post(
		"../article/my_article_put.php",
		{
			title: $("#article_add_title").val(),
		},
		function (data) {
			let error = JSON.parse(data);
			if (error.status == 0) {
				alert("ok");
				article_add_cancel();
				window.location.reload();
			} else {
				alert(error.message);
			}
		}
	);
}
