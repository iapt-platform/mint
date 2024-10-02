/**
 * 显示句子历史记录窗口
 */

function chapter_dynamic_init() {
	$("body").append('<div id="chapter_dynamic_dlg" title="Chapter Dynamic"><div id="chapter_dynamic_content"></div></div>');
	$("#chapter_dynamic_dlg").dialog({
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

function chapter_dynamic_show(book,para,channel_id) {
    let imgUrl = location.host ;
    imgUrl = "/api/sentence/progress/daily/image?";
    imgUrl += "channel="+channel_id;
    imgUrl += "&&book=" + book;
    imgUrl += "&from=" + para;
    imgUrl += "&view=palistrlen";
    $("#chapter_dynamic_dlg").html("<img src='"+imgUrl+"'>");
    $("#chapter_dynamic_dlg").dialog("open");
}
