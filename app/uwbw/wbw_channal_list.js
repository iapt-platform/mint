var _wbw_channel;
function wbw_channal_list_init() {
	$("body").append(
		'<div id="wbw_channal_list_dlg" title="Open WBW"><div id="wbw_channal_list_dlg_content"></div></div>'
	);
	$("#wbw_channal_list_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: gLocal.gui.cancel,
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}

function wbw_channal_list_open(book, paralist) {
	$.post(
		"../uwbw/wbw_channel_list.php",
		{
			book: book,
			para: JSON.stringify(paralist),
		},
		function (data) {
			_wbw_channel = JSON.parse(data);
			if (_wbw_channel.status == 0) {
				let html = "";
				for (let index = 0; index < _wbw_channel.data.length; index++) {
					const element = _wbw_channel.data[index];
					html += "<div style='display:flex;line-height: 2.5em;'>";
					html += "<span style='flex:2'>";
					html += "<button onclick=\"wbw_create('" + index + "')\">";
					if (parseInt(element.wbw_para) > 0) {
						html += gLocal.gui.open;
					} else {
						html += gLocal.gui.new;
					}

					html += "</button>";
					html += "</span>";
					html += "<span  style='flex:1'>" + (index + 1) + "</span>";
					html += "<span style='flex:3'>" + element.name + "</span>";
					html += "<span style='flex:2'>" + element.lang + "</span>";
					html += "<span style='flex:2;display:none;'>" + element.wbw_para + "/" + element.count + "</span>";
					html += "</div>";
				}

				$("#wbw_channal_list_dlg_content").html(html);
				$("#wbw_channal_list_dlg").dialog("open");
			} else {
				ntf_show(_wbw_channel.error);
			}
		}
	);
}

function wbw_create(index) {
	$("#wbw_channal_list_dlg").dialog("close");
	$.post(
		"../uwbw/create_wbw.php",
		{
			book: _wbw_channel.book,
			para: _wbw_channel.para,
			lang: _wbw_channel.data[index].lang,
			channel: _wbw_channel.data[index].id,
		},
		function (data) {
			let msg = JSON.parse(data);
			if (msg.status == 0) {
				$("#wbw_channal_list_dlg_content").html("正在打开编辑窗口");
				let book = msg.book;
				let paralist = msg.para.join(",");
				let channel = msg.channel;
				let url =
					"../studio/editor.php?op=openchannal&book=" + book + "&para=" + paralist + "&channal=" + channel;
				window.open(url, "_blank");
				$("#wbw_channal_list_dlg").dialog("close");
			} else {
				ntf_show(msg.error);
			}
		}
	);
}
