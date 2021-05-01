function related_para_dlg_init(title = "Related") {
	$("body").append(
		'<div id="related_para_dlg" title="' + title + '"><div id="related_para_dlg_content"></div></div>'
	);

	$("#related_para_dlg").dialog({
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
function related_para_dlg_open(book, para) {
	$.get(
		"../term/related_para.php",
		{
			book: book,
			para: para,
		},
		function (data) {
			let para = JSON.parse(data);
			let html = related_para_dlg_render(para);
			$("#related_para_dlg_content").html(html);
			$("#related_para_dlg").dialog("open");
		}
	);
}

function related_para_dlg_render(para) {
	let output = "";
	for (const iterator of para.book_list) {
		output += "<div>";
		if (para.curr_book_id == iterator.id) {
			output += "<b>" + iterator.title + "</b>";
		} else {
			//找到与这个书匹配的段落
			let paraList = new Array();
			for (const ipara of para.data) {
				if (ipara.bookid == iterator.id) {
					paraList.push(ipara);
				}
			}
			output +=
				"<a href='../reader/?view=chapter&book=" +
				paraList[0].book +
				"&para=" +
				paraList[0].para +
				"' target='_blank'>" +
				iterator.title +
				"</a>";
		}
		output += "</div>";
	}

	return output;
}
