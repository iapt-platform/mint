function term_edit_dlg_init(title = gLocal.gui.dict_terms) {
	$("body").append('<div id="term_edit_dlg" title="' + title + '"><div id="term_edit_dlg_content"></div></div>');

	$("#term_edit_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: gLocal.gui.save,
				click: function () {
					term_edit_dlg_save();
					$(this).dialog("close");
				},
			},
			{
				text: gLocal.gui.cancel,
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
	output += "<legend>" + gLocal.gui.spell + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_word' name='word' value='" +
		word.word +
		"'placeholder=" +
		gLocal.gui.required +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.first_choice_word + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_meaning' name='mean' value='" +
		word.meaning +
		"' placeholder=" +
		gLocal.gui.required +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.other_meaning + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_othermeaning name='mean2' value='" +
		word.other_meaning +
		"' placeholder=" +
		gLocal.gui.optional +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.tag + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_tag name='tag' name='tag' value='" +
		word.tag +
		"' placeholder=" +
		gLocal.gui.optional +
		" >";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.language + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_language' name='language' value='" +
		word.language +
		"' placeholder=" +
		gLocal.gui.required +
		" >";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.channel + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_channal' name='channal' value='" +
		word.channal +
		"' placeholder=" +
		gLocal.gui.optional +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.encyclopedia + "</legend>";
	output +=
		"<textarea id='term_edit_form_note' name='note' placeholder=" +
		gLocal.gui.optional +
		">" +
		word.note +
		"</textarea>";
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
