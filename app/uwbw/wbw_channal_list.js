var _wbw_channel;
function wbw_channal_list_init() {
	$("body").append(
		'<div id="wbw_channal_list_dlg" title=' +
			gLocal.gui.open +
			gLocal.gui.wbw +
			'><div id="wbw_channal_list_dlg_content"></div></div>'
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
					html += "<div style='display:flex;line-height: 2.5em;border-bottom: 1px solid gray;'>";
					html += "<span style='flex:4'>";
					let style = "";
					let text = "";
					if (parseInt(element.wbw_para) > 0) {
						if (parseInt(element.power) < 20) {
							text = "查看";
							style = "background-color: yellow;";
						} else {
							text = gLocal.gui.edit;
							style = "background-color: greenyellow;";
						}
					} else {
						text = gLocal.gui.new;
					}
					html +=
						"<button style='" + style + "' onclick=\"wbw_create('" + index + "')\">" + text + "</button>";

					if (parseInt(element.power) < 30) {
						html += "<button onclick=\"wbw_fork('" + index + "')\">";
						html += "复制到";
						html += "</button>";
					}

					html += "</span>";
					html += "<span  style='flex:1'>" + (index + 1) + "</span>";
					html += "<span style='flex:5'>" + element.name + "</span>";
					html += "<span style='flex:1'>";
					let power = [
						{ id: 10, note: "查看者" },
						{ id: 20, note: "编辑者" },
						{ id: 30, note: "拥有者" },
					];
					for (const iterator of power) {
						if (parseInt(element.power) == iterator.id) {
							html += iterator.note;
						}
					}
					html += "</span>";
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
					"../studio/editor.php?op=openchannel&book=" + book + "&par=" + paralist + "&channel=" + channel;
				window.open(url, "_blank");
				$("#wbw_channal_list_dlg").dialog("close");
			} else {
				ntf_show(msg.error);
			}
		}
	);
}

function wbw_fork(index) {
	$("#wbw_channal_list_dlg").dialog("close");
	let url =
		"../doc/fork_channel.php?book=" +
		_wbw_channel.book +
		"&para=" +
		_wbw_channel.para +
		"&src_channel=" +
		_wbw_channel.data[index].id;
	window.open(url, "_blank");
}
