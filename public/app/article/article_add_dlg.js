var _article_add_dlg_div;
var _article_create_param;
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

function article_add_dlg_show(param=null) {
	_article_create_param=param;
	if(param!=null){
		if(typeof param.title !="undefined"){
			$("#article_add_title").val(param.title);
		}
	}
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
	if(_article_create_param == null){
		_article_create_param = {
			title: $("#article_add_title").val(),
		}
	}
	$.post(
		"../article/my_article_put.php",
		_article_create_param,
		function (data) {
			let result = JSON.parse(data);
			if (result.ok === true) {
				alert("ok");
				article_add_cancel();
				window.open("../article/my_article_edit.php?id="+result.data.id);
			} else {
				alert(result.message);
			}
		}
	);
}
