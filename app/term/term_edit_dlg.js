function term_edit_dlg_init(title = "Trem") {
	$("body").append('<div id="term_edit_dlg" title="' + title + '"><div id="term_edit_dlg_content"></div></div>');

	$("#term_edit_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: "Save",
				click: function () {
					term_edit_dlg_save();
					$(this).dialog("close");
				},
			},
			{
				text: "Cancel",
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}
function term_edit_dlg_open(id = "") {
	if (id == "") {
		$("#term_edit_dlg").dialog("open");
	} else {
		$.post(
			"../term/term_get_id.php",
			{
				id: id,
			},
			function (data) {
				let word = JSON.parse(data);
				let html = term_edit_dlg_render(word);
				$("#term_edit_dlg_content").html(html);
				$("#term_edit_dlg").dialog("open");
			}
		);
	}
}

function term_edit_dlg_render(word = "") {
	if (word == "") {
		word = new Object();
		word.pali = "";
	}
	let output = "";
	output += "<form action='##' id='form_term'>";
	output += "<input type='hidden' id='term_edit_form_id' name='id' value='" + word.guid + "'>";
	output += "<fieldset>";
	output += "<legend>Spell</legend>";
	output += "<input type='input' id='term_edit_form_word' name='word' value='" + word.word + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Meaning</legend>";
	output += "<input type='input' id='term_edit_form_meaning' name='mean' value='" + word.meaning + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Meaning</legend>";
	output += "<input type='input' id='term_edit_form_othermeaning name='mean2' value='" + word.other_meaning + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Tag</legend>";
	output += "<input type='input' id='term_edit_form_tag name='tag' value='" + word.tag + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Language</legend>";
	output += "<input type='input' id='term_edit_form_language' name='language' value='" + word.language + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Channal</legend>";
	output += "<input type='input' id='term_edit_form_channal' name='channal' value='" + word.channal + "'>";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>Note</legend>";
	output += "<textarea id='term_edit_form_note' name='note'>" + word.note + "</textarea>";
	output += "</fieldset>";

	output += "</form>";

	return output;
}
function term_edit_dlg_save() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../term/term_post.php", //url
		data: $("#form_term").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert(result.message + gLocal.gui.saved + gLocal.gui.successful);
			} else {
				alert("error:" + result.message);
			}
		},
		error: function (data, status) {
			alert("异常！" + data.responseText);
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
