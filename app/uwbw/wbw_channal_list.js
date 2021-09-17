var _wbw_channel;
function wbw_channal_list_init() {
	let channelListHtml = "";
	let style = "<style>";
	style += "#wbw_channal_list_dlg_content .ui-widget-content{border:unset;} ";
	style += "#wbw_channal_list_dlg_content .ui-widget-header{background:unset;border:unset;border-bottom: 1px solid #dddddd;} ";
	style += "</style>";
	channelListHtml += '<div id="wbw_channal_list_dlg" title=' + gLocal.gui.open + gLocal.gui.wbw + '>';
	channelListHtml += style;
	channelListHtml += '<div id="wbw_channal_list_dlg_content">';
	channelListHtml += '<div id="tabs">';
	channelListHtml += '<ul>';
    channelListHtml += '<li><a href="#tabs-1">我的<span id="my-num" class="circle_num">3</span></a></li>';
    channelListHtml += '<li><a href="#tabs-2">协作<span id="co-num"  class="circle_num">2</span></a></li>';
    channelListHtml += '<li><a href="#tabs-3">网络公开<span id="pu-num"  class="circle_num">1</span></a></li>';
	channelListHtml += '</ul>';
	channelListHtml += '<div id="tabs-1">';
	channelListHtml += '</div>';
	channelListHtml += '<div id="tabs-2">';
	channelListHtml += '</div>';
	channelListHtml += '<div id="tabs-3">';
	channelListHtml += '</div>';
	channelListHtml += '</div>';
	channelListHtml += '</div>';
	channelListHtml += '</div>';

	$("body").append(channelListHtml);
	$("#wbw_channal_list_dlg").dialog({
		autoOpen: false,
		width: 700,
		height:650,
		maxHeight: $(window).height()*0.7,
		buttons: [
			{
				text: gLocal.gui.cancel,
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
	$( "#tabs" ).tabs();
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
				let htmlMy = "";
				let htmlCollaborate = "";
				let htmlPublic = "";
				let myNum = 0;
				let coNum = 0;
				let puNum = 0;

				for (let index = 0; index < _wbw_channel.data.length; index++) {
					const element = _wbw_channel.data[index];
					let html = "";
					html += "<div style='display:flex;line-height: 2.5em;border-bottom: 1px solid gray;'>";
					html += "<span style='flex:3'>";
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
						html += gLocal.gui.copy_to;
						html += "</button>";
					}

					html += "</span>";
					html += "<span  style='flex:1'>" + (index + 1) + "</span>";
					html += "<span style='flex:5'>" + element.name + "</span>";
					html += "<span style='flex:2'>";
					let power = [
						{ id: 10, note: gLocal.gui.viewer },
						{ id: 20, note: gLocal.gui.editor },
						{ id: 30, note: gLocal.gui.owner },
					];
					for (const iterator of power) {
						if (parseInt(element.power) == iterator.id) {
							html += iterator.note;
						}
					}
					html += "</span>";
					html += "<span style='flex:2'>";
					html += element.user.nickname;
					html += "</span>";
					html += "<span style='flex:1;text-align: right;'>" + element.lang + "</span>";
					html += "<span style='flex:2;display:none;'>" + element.wbw_para + "/" + element.count + "</span>";
					html += "</div>";
					switch (element.type) {
						case "my":
							htmlMy +=html;
							myNum++;
							break;
						case "collaborate":
							htmlCollaborate +=html;
							coNum++
							break;	
						case "public":
							htmlPublic +=html;
							puNum++
							break;		
					}
				}

				$("#tabs-1").html(htmlMy);
				$("#tabs-2").html(htmlCollaborate);
				$("#tabs-3").html(htmlPublic);
				$("#my-num").text(myNum);
				$("#co-num").text(coNum);
				$("#pu-num").text(puNum);
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
