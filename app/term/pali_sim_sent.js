var _sent_id = 0;
function pali_sim_dlg_init(title = gLocal.gui.similar_sentences) {
	$("body").append('<div id="pali_sim_dlg" title="' + title + '"><div id="pali_sim_dlg_content"></div></div>');

	$("#pali_sim_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: "在新窗口打开",
				click: function () {
					window.open("../reader/?view=sim&id=" + _sent_id + "&display=sent&direction=col", "_blank");
					$(this).dialog("close");
				},
			},
			{
				text: gLocal.gui.close,
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}
function pali_sim_dlg_open(id, start, length) {
	_sent_id = id;

	$.post(
		"../pali_sent/get_sim.php",
		{
			sent_id: id,
			start: start,
			length: length,
		},
		function (data) {
			let sents = JSON.parse(data);
			let html = pali_sim_dlg_render(sents);
			$("#pali_sim_dlg_content").html(html);
			note_ref_init();
			$("#pali_sim_dlg").dialog("open");
		}
	);
}

function pali_sim_dlg_render(sent_list) {
	let output = "";
	for (const iterator of sent_list) {
		output += "<div class='pali_sent_div'>";
		output += "<div class='pali_sent'>" + iterator.text + "</div>";
		output += "<div class='path'>" + iterator.path + "</div>";
		output += "</div>";
	}

	return output;
}
