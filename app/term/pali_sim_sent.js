function pali_sim_dlg_init(title = "Sim") {
	$("body").append('<div id="pali_sim_dlg" title="' + title + '"><div id="pali_sim_dlg_content"></div></div>');

	$("#pali_sim_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: "Close",
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}
function pali_sim_dlg_open(id, sim_id) {
	{
		$.post(
			"../pali_sent/get_sim.php",
			{
				id: id,
				sim: JSON.stringify(sim_id),
			},
			function (data) {
				let sents = JSON.parse(data);
				let html = pali_sim_dlg_render(sents);
				$("#pali_sim_dlg_content").html(html);
				$("#pali_sim_dlg").dialog("open");
			}
		);
	}
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
