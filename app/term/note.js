var _display = "";
var _word = "";
var _channal = "";
var _lang = "";
var _author = "";

var _arrData = new Array();
var _channalData;

var MAX_NOTE_NEST = 2;

/*
{{203-1654-23-45@11@en@*}}
<note>203-1654-23-45@11@en@*</note>
<note id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

<note  id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*>
	<div class=text>
	pali text
	</div>
	<tran>
	</tran>
	<ref>
	</ref>
</note>
*/

/*
解析百科字符串
{{203-1654-23-45@11@en@*}}
<note id=12345 info="203-1654-23-45@11@en@*"><note>
<note id="guid" book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

*/
function note_create() {
	wbw_channal_list_init();
	note_sent_edit_dlg_init();
	term_edit_dlg_init();
}
function note_sent_edit_dlg_init() {
	$("body").append(
		'<div id="note_sent_edit_dlg" title="' + gLocal.gui.edit + '"><div id="edit_dialog_content"></div></div>'
	);
	$("#note_sent_edit_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: gLocal.gui.save,
				click: function () {
					note_sent_save();
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
function note_init(input) {
	let newString = input.replace(/\{\{/g, '<note info="');
	newString = newString.replace(/\}\}/g, '" style="width=70vw;"></note>');

	let output = "<div>";
	output += marked(newString);
	output += "</div>";
	return output;
}

function note_update_background_style() {
	var mSentsBook = new Array();
	var mBgIndex = 1;
	$("note[info]").each(function () {
		let info = $(this).attr("info").split("-");
		if (info.length >= 2) {
			let book = info[0];
			$(this).attr("book", book);
			if (!mSentsBook[book]) {
				mSentsBook[book] = mBgIndex;
				mBgIndex++;
			}
			$(this).addClass("bg_color_" + mSentsBook[book]);
		}
	});
}
//
function note_refresh_new() {
	note_update_background_style();
	let objNotes = document.querySelectorAll("note");
	let arrSentInfo = new Array();
	for (const iterator of objNotes) {
		let id = iterator.id;
		if (id == null || id == "") {
			//查看这个节点是第几层note嵌套。大于预定层数退出。
			let layout = 1;
			let parent = iterator.parentNode;
			while (parent.nodeType == 1) {
				if (parent.nodeName == "NOTE") {
					layout++;
					if (layout > MAX_NOTE_NEST) {
						return false;
					}
				} else if (parent.nodeName == "BODY") {
					break;
				}
				parent = parent.parentNode;
			}
			id = com_guid();
			iterator.id = id;
			if (iterator.hasAttribute("info")) {
				let info = iterator.getAttribute("info");
				if (info != null || info != "") {
					/*
					let arrInfo = info.split("-");
					
					if (arrInfo.length >= 2) {
						let book = arrInfo[0];
						let para = arrInfo[1];
					}
					*/
					arrSentInfo.push({ id: id, data: info });
				}
			}
		}
	}
	if (arrSentInfo.length > 0) {
		let setting = new Object();
		setting.lang = "";
		setting.channal = _channal;
		$.post(
			"../term/note.php",
			{
				setting: JSON.stringify(setting),
				data: JSON.stringify(arrSentInfo),
			},
			function (data, status) {
				if (status == "success") {
					try {
						let sentData = JSON.parse(data);
						for (const iterator of sentData) {
							let id = iterator.id;
							let strHtml = "<a name='" + id + "'></a>";
							if (_display && _display == "para") {
								//段落模式
								let strPalitext =
									"<pali book='" +
									iterator.book +
									"' para='" +
									iterator.para +
									"' begin='" +
									iterator.begin +
									"' end='" +
									iterator.end +
									"' >" +
									iterator.palitext +
									"</pali>";
								let divPali = $("#" + id)
									.parent()
									.children(".palitext");
								if (divPali.length == 0) {
									if (_channal != "") {
										let arrChannal = _channal.split(",");
										for (let index = arrChannal.length - 1; index >= 0; index--) {
											const iChannal = arrChannal[index];
											$("#" + id)
												.parent()
												.prepend("<div class='tran_div'  channal='" + iChannal + "'></div>");
										}
									}

									$("#" + id)
										.parent()
										.prepend("<div class='palitext'></div>");
								}
								$("#" + id)
									.parent()
									.children(".palitext")
									.first()
									.append(strPalitext);
								let htmlTran = "";
								for (const oneTran of iterator.translation) {
									let html =
										"<span class='tran' lang='" +
										oneTran.lang +
										"' channal='" +
										oneTran.channal +
										"'>";
									html += marked(
										term_std_str_to_tran(
											oneTran.text,
											oneTran.channal,
											oneTran.editor,
											oneTran.lang
										)
									);
									html += "</span>";
									if (_channal == "") {
										htmlTran += html;
									} else {
										$("#" + id)
											.siblings(".tran_div[channal='" + oneTran.channal + "']")
											.append(html);
									}
								}
								$("#" + id).html(htmlTran);
							} else {
								//句子模式
								strHtml += note_json_html(iterator);
								$("#" + id).html(strHtml);
							}
						}
						//刷新句子链接递归，有加层数限制。
						note_refresh_new();

						_arrData = _arrData.concat(sentData);
						note_ref_init();
						term_get_dict();
						note_channal_list();
					} catch (e) {
						console.error(e);
					}
				}
			}
		);
	} else {
		//term_get_dict();
	}
}

//生成channel列表
function note_channal_list() {
	console.log("note_channal_list start");
	let arrSentInfo = new Array();
	$("note").each(function () {
		let info = $(this).attr("info");
		if (info && info != "") {
			arrSentInfo.push({ id: "", data: info });
		}
	});

	if (arrSentInfo.length > 0) {
		$.post(
			"../term/channal_list.php",
			{
				setting: "",
				data: JSON.stringify(arrSentInfo),
			},
			function (data, status) {
				if (status == "success") {
					try {
						let active = JSON.parse(data);
						_channalData = active;
						for (const iterator of _my_channal) {
							let found = false;
							for (const one of active) {
								if (iterator.id == one.id) {
									found = true;
									break;
								}
							}
							if (found == false) {
								_channalData.push(iterator);
							}
						}
						let strHtml = "";
						strHtml += "<div class='channel_select'>";
						strHtml += "<button onclick='onChannelChange()'>确定</button>";
						strHtml += "<button onclick='onChannelMultiSelectCancel()'>取消</button>";
						strHtml += "</div>";
						for (const iterator of _channalData) {
							if (_channal.indexOf(iterator.id) >= 0) {
								strHtml += render_channal_list(iterator);
							}
						}
						for (const iterator of _channalData) {
							if (_channal.indexOf(iterator.id) == -1) {
								strHtml += render_channal_list(iterator);
							}
						}

						$("#channal_list").html(strHtml);
						set_more_button_display();
					} catch (e) {
						console.error(e);
					}
				}
			}
		);
	}
}

function find_channal(id) {
	for (const iterator of _channalData) {
		if (id == iterator.id) {
			return iterator;
		}
	}
	return false;
}
function render_channal_list(channalinfo) {
	let output = "";
	let checked = "";
	let selected = "noselect";
	if (_channal.indexOf(channalinfo.id) >= 0) {
		checked = "checked";
		selected = "selected";
	}
	output += "<div class='list_with_head " + selected + "'>";

	output +=
		'<div class="channel_select"><input type="checkbox" ' + checked + " channal_id='" + channalinfo.id + "'></div>";
	output += "<div class='head'>";
	output += "<span class='head_img'>";
	output += channalinfo.nickname.slice(0, 2);
	output += "</span>";
	output += "</div>";

	output += "<div style='width: 100%;overflow-x: hidden;'>";

	output += "<div class='channal_list' >";

	//  output += "<a href='../wiki/wiki.php?word=" + _word;
	//  output += "&channal=" + channalinfo.id + "' >";
	output += "<a onclick=\"set_channal('" + channalinfo.id + "')\">";

	output += channalinfo["name"];

	output += "</a>";
	output += "@" + channalinfo["nickname"];
	output += "</div>";

	output += "<div class='userinfo_channal'>";
	output += channalinfo["username"];
	output += "</div>";

	if (channalinfo["final"]) {
		//进度
		output += "<div>";
		let article_len = channalinfo["article_len"];
		let svg_width = article_len;
		let svg_height = parseInt(article_len / 10);
		output += '<svg viewBox="0 0 ' + svg_width + " " + svg_height + '" width="100%" >';

		let curr_x = 0;
		let allFinal = 0;
		for (const iterator of channalinfo["final"]) {
			let stroke_width = parseInt(iterator.len);
			output += "<rect ";
			output += ' x="' + curr_x + '"';
			output += ' y="0"';
			output += ' height="' + svg_height + '"';
			output += ' width="' + stroke_width + '"';

			if (iterator.final == true) {
				allFinal += stroke_width;
				output += ' class="progress_bar_done" ';
			} else {
				output += ' class="progress_bar_undone" ';
			}
			output += "/>";

			curr_x += stroke_width;
		}
		output +=
			"<rect  x='0' y='0'  width='" + svg_width + "' height='" + svg_height / 5 + "' class='progress_bar_bg' />";
		output +=
			"<rect  x='0' y='0'  width='" +
			allFinal +
			"' height='" +
			svg_height / 5 +
			"' class='progress_bar_percent' style='stroke-width: 0; fill: rgb(100, 228, 100);'/>";
		output += '<text x="0" y="' + svg_height + '" font-size="' + svg_height * 0.8 + '">';
		output += channalinfo["count"] + "/" + channalinfo["all"];
		output += "</text>";
		output += "<svg>";
		output += "</div>";
		//进度结束
	}

	output += "</div>";
	output += "</div>";
	return output;
}

function onChannelMultiSelectStart() {
	$(".channel_select").show();
}
function onChannelMultiSelectCancel() {
	$(".channel_select").hide();
}
function onChannelChange() {
	let channal_list = new Array();
	$("[channal_id]").each(function () {
		if (this.checked) {
			channal_list.push($(this).attr("channal_id"));
		}
	});
	set_channal(channal_list.join());
}
//点击引用 需要响应的事件
function note_ref_init() {
	$("chapter").click(function () {
		let bookid = $(this).attr("book");
		let para = $(this).attr("para");
		window.open("../reader/?view=chapter&book=" + bookid + "&para=" + para, "_blank");
	});

	$("para").click(function () {
		let bookid = $(this).attr("book");
		let para = $(this).attr("para");
		window.open("../reader/?view=para&book=" + bookid + "&para=" + para, "_blank");
	});
}
/*
id
palitext
tran
ref
*/
function note_json_html(in_json) {
	let output = "";
	output += '<div class="note_tool_bar" style=" position: relative;">';
	output += '<div class="case_dropdown note_tool_context" >';
	output += "<svg class='icon' >";
	output += "<use xlink:href='../studio/svg/icon.svg#ic_more'></use>";
	output += "</svg>";
	output += "<div class='case_dropdown-content sent_menu'>";
	if (typeof _reader_view != "undefined" && _reader_view != "sent") {
		output += "<a onclick='junp_to(this)'>跳转至此句</a>";
	}
	output +=
		"<a onclick=\"copy_ref('" +
		in_json.book +
		"','" +
		in_json.para +
		"','" +
		in_json.begin +
		"','" +
		in_json.end +
		"')\">" +
		gLocal.gui.copy_link +
		"</a>";
	output += "<a onclick='copy_text(this)'>" + gLocal.gui.copy + "“" + gLocal.gui.pāli + "”</a>";
	output +=
		"<a onclick=\"edit_in_studio('" +
		in_json.book +
		"','" +
		in_json.para +
		"','" +
		in_json.begin +
		"','" +
		in_json.end +
		"')\">" +
		gLocal.gui.edit_now +
		"</a>";
	output += "<a onclick='add_to_list()'>" + gLocal.gui.add_to_edit_list + "</a>";
	output += "</div>";
	output += "</div>";
	output += " </div>";

	output += "<div class='palitext'>" + in_json.palitext + "</div>";

	//output += "<div class='translation_div'>";
	for (const iterator of in_json.translation) {
		output += "<div class='tran' lang='" + iterator.lang + "'>";

		output +=
			"<div class='text' id='tran_text_" +
			in_json.book +
			"_" +
			in_json.para +
			"_" +
			in_json.begin +
			"_" +
			in_json.end +
			"_" +
			iterator.channal +
			"'>";
		if (iterator.text == "") {
			output +=
				"<span style='color:var(--border-line-color);'>" +
				iterator.channalinfo.name +
				"-" +
				iterator.channalinfo.lang +
				"</span>";
		} else {
			//note_init处理句子链接 marked不处理
			//output += marked(term_std_str_to_tran(iterator.text, iterator.channal, iterator.editor, iterator.lang));
			output += note_init(term_std_str_to_tran(iterator.text, iterator.channal, iterator.editor, iterator.lang));
		}
		output += "</div>";

		//句子工具栏
		output += "<div class='tran_text_tool_bar'>";
		output += "<span class = 'tip_buttom' ";
		output +=
			" onclick=\"note_edit_sentence('" +
			in_json.book +
			"' ,'" +
			in_json.para +
			"' ,'" +
			in_json.begin +
			"' ,'" +
			in_json.end +
			"' ,'" +
			iterator.channal +
			"')\"";
		output += ">" + gLocal.gui.edit + "</span>";
		output += "<span class = 'tip_buttom' ";
		output += " onclick=\"history_show('" + iterator.id + "')\"";
		output += ">" + gLocal.gui.timeline + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.extension + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.like + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.comment + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.copy + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.digest + "</span>";
		output += "<span class = 'tip_buttom'>" + gLocal.gui.share_to + "</span>";
		output += "</div>";
		//句子工具栏结束

		output += "</div>";
	}
	//output += "</div>";

	output += "<div class='other_tran_div' sent='";
	output += in_json.book + "-" + in_json.para + "-" + in_json.begin + "-" + in_json.end;
	output += "'>";
	output += "<div class='tool_bar'>";
	output += "<span class='more_tran icon_expand'></span>";
	output += "<span class='other_tran_span'>" + gLocal.gui.other + gLocal.gui.translation + "</span>";
	output += "<span class='other_tran_num'></span>";
	output += "</div>";
	output += "<div class='other_tran'>";

	output += "</div>";
	output += "</div>";

	output += "<div class='add_new icon_add' ";
	output += "book='" + in_json.book + "' ";
	output += "para='" + in_json.para + "' ";
	output += "begin='" + in_json.begin + "' ";
	output += "end='" + in_json.end + "' ";
	output += " style='left:0;'>";

	//output += "<div class='more_tran icon_expand'></div>";
	output += "</div>";

	output += "<div class='ref'>" + in_json.ref;
	output +=
		"<span class='sent_no'>" +
		in_json.book +
		"-" +
		in_json.para +
		"-" +
		in_json.begin +
		"-" +
		in_json.end +
		"<span>" +
		"</div>";

	return output;
}

function set_more_button_display() {
	$(".other_tran_div").each(function () {
		const sentid = $(this).attr("sent").split("-");

		const book = sentid[0];
		const para = sentid[1];
		const begin = sentid[2];
		const end = sentid[3];
		let count = 0;
		for (const iterator of _channalData) {
			if (iterator.final) {
				for (const onesent of iterator.final) {
					let id = onesent.id.split("-");
					if (book == id[0] && para == id[1] && begin == id[2] && end == id[3] && onesent.final) {
						if (_channal.indexOf(iterator.id) == -1) {
							count++;
						}
					}
				}
			}
		}
		if (count > 0) {
			$(this).find(".other_tran_num").html(count);
			$(this).find(".other_tran_num").attr("style", "display:inline-flex;");
			$(this)
				.find(".tool_bar")
				.click(function () {
					const sentid = $(this).parent().attr("sent").split("-");
					const book = sentid[0];
					const para = sentid[1];
					const begin = sentid[2];
					const end = sentid[3];
					let sentId = book + "-" + para + "-" + begin + "-" + end;
					if ($(this).siblings(".other_tran").first().css("display") == "none") {
						$(".other_tran_div[sent='" + sentId + "']")
							.children(".other_tran")
							.slideDown();
						$(this).children(".more_tran ").css("transform", "unset");
						$.get(
							"../usent/get.php",
							{
								book: book,
								para: para,
								begin: begin,
								end: end,
							},
							function (data, status) {
								let arrSent = JSON.parse(data);
								let html = "";
								for (const iterator of arrSent) {
									if (_channal.indexOf(iterator.channal) == -1) {
										html += "<div>" + marked(iterator.text) + "</div>";
									}
								}
								let sentId =
									arrSent[0].book +
									"-" +
									arrSent[0].paragraph +
									"-" +
									arrSent[0].begin +
									"-" +
									arrSent[0].end;
								$(".other_tran_div[sent='" + sentId + "']")
									.children(".other_tran")
									.html(html);
							}
						);
					} else {
						$(".other_tran_div[sent='" + sentId + "']")
							.children(".other_tran")
							.slideUp();
						$(this).children(".more_tran ").css("transform", "rotate(-90deg)");
					}
				});
		} else {
			//隐藏自己
			//$(this).hide();
			$(this)
				.find(".other_tran_span")
				.html(gLocal.gui.no + gLocal.gui.other + gLocal.gui.translation);
			$(this).find(".more_tran").hide();
		}
	});
}

function note_edit_sentence(book, para, begin, end, channal) {
	let channalInfo;
	for (const iterator of _channalData) {
		if (iterator.id == channal) {
			channalInfo = iterator;
			break;
		}
	}
	for (const iterator of _arrData) {
		if (iterator.book == book && iterator.para == para && iterator.begin == begin && iterator.end == end) {
			for (const tran of iterator.translation) {
				if (tran.channal == channal) {
					let html = "";
					html += "<div style='color:blue;'>" + channalInfo.name + "@" + channalInfo.nickname + "</div>";
					html +=
						"<textarea id='edit_dialog_text' sent_id='" +
						tran.id +
						"' book='" +
						iterator.book +
						"' para='" +
						iterator.para +
						"' begin='" +
						iterator.begin +
						"' end='" +
						iterator.end +
						"' channal='" +
						tran.channal +
						"' style='width:100%;min-height:260px;'>" +
						tran.text +
						"</textarea>";
					$("#edit_dialog_content").html(html);
					$("#note_sent_edit_dlg").dialog("open");
					return;
				}
			}
		}
	}
	alert("未找到句子");
}

function note_sent_save() {
	let id = $("#edit_dialog_text").attr("sent_id");
	let book = $("#edit_dialog_text").attr("book");
	let para = $("#edit_dialog_text").attr("para");
	let begin = $("#edit_dialog_text").attr("begin");
	let end = $("#edit_dialog_text").attr("end");
	let channal = $("#edit_dialog_text").attr("channal");
	let text = $("#edit_dialog_text").val();

	$.post(
		"../usent/sent_post.php",
		{
			id: id,
			book: book,
			para: para,
			begin: begin,
			end: end,
			channal: channal,
			text: text,
			lang: "zh",
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.status > 0) {
				alert("error" + result.message);
			} else {
				ntf_show("success");
				if (result.text == "") {
					let channel_info = "Empty";
					let thisChannel = find_channal(result.channal);
					if (thisChannel) {
						channel_info = thisChannel.name + "-" + thisChannel.nickname;
					}
					$(
						"#tran_text_" +
							result.book +
							"_" +
							result.para +
							"_" +
							result.begin +
							"_" +
							result.end +
							"_" +
							result.channal
					).html("<span style='color:var(--border-line-color);'>" + channel_info + "</span>");
				} else {
					$(
						"#tran_text_" +
							result.book +
							"_" +
							result.para +
							"_" +
							result.begin +
							"_" +
							result.end +
							"_" +
							result.channal
					).html(marked(term_std_str_to_tran(result.text, result.channal, result.editor, result.lang)));
					term_updata_translation();
					for (const iterator of _arrData) {
						if (
							iterator.book == result.book &&
							iterator.para == result.para &&
							iterator.begin == result.begin &&
							iterator.end == result.end
						) {
							for (const tran of iterator.translation) {
								if (tran.channal == result.channal) {
									tran.text = result.text;
									break;
								}
							}
						}
					}
				}
			}
		}
	);
}

function copy_ref(book, para, begin, end) {
	let strRef = "{{" + book + "-" + para + "-" + begin + "-" + end + "}}";
	copy_to_clipboard(strRef);
}

function edit_in_studio(book, para, begin, end) {
	wbw_channal_list_open(book, [para]);
}
