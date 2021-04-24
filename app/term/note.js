var _display = "para";
var _mode = "read";
var _direction = "row";
var _word = "";
var _channal = "";
var _lang = "";
var _author = "";

var _arrData = new Array();
var _channalData;

var MAX_NOTE_NEST = 2;

var gBuildinDictIsOpen = false;
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
	pali_sim_dlg_init();
	related_para_dlg_init();
}
function note_sent_edit_dlg_init() {
	$("body").append(
		'<div id="note_sent_edit_dlg" title="' +
			gLocal.gui.edit +
			'"><guide gid="markdown_guide"></guide><div id="edit_dialog_content"></div></div>'
	);
	guide_init();
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
	if (input) {
		let output = "<div>";
		output += marked(input);
		output += "</div>";

		let newString = output.replace(/\{\{/g, '<span class="note_shell"><note info="');
		newString = newString.replace(/\}\}/g, '" ></note></span>');

		return newString;
	} else {
		return "";
	}
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
							if (_mode && _mode == "read") {
								//阅读模式
								strHtml += render_read_mode_sent(iterator);
								$("#" + id).html(strHtml);
							} else {
								//编辑模式
								strHtml += note_json_html(iterator);
								$("#" + id).html(strHtml);
							}
						}
						//处理<code>标签作为气泡注释
						popup_init();

						//刷新句子链接递归，有加层数限制。
						//note_refresh_new();

						//将新的数据添加到数据总表
						_arrData = _arrData.concat(sentData);
						note_ref_init();
						//获取术语字典
						term_get_dict();
						//刷新channel列表
						note_channal_list();
						//显示不同的巴利语脚本
						refresh_pali_script();
						//把巴利语单词用<w>分隔用于点词查询等
						splite_pali_word();
						//处理编辑框消息
						tran_sent_textarea_event_init();
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

//渲染阅读模式句子
function render_read_mode_sent(iterator) {
	let id = iterator.id;
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

	if (
		$("#" + id)
			.parent()
			.parent()
			.children(".para_div").length == 0
	) {
		let tranDivHtml = "";
		if (_channal != "") {
			let arrChannal = _channal.split(",");
			for (let index = arrChannal.length - 1; index >= 0; index--) {
				const iChannal = arrChannal[index];
				tranDivHtml += "<div class='tran_div_channel'  channal='" + iChannal + "'></div>";
			}
		} else {
			tranDivHtml = "<div class='tran_div_channel'  channal='0'></div>";
		}
		$("#" + id)
			.parent()
			.parent()
			.prepend(
				"<div class='para_div'><div class='palitext_div'><div class='palitext palitext1'></div><div class='palitext palitext2'></div></div><div class='para_tran_div'>" +
					tranDivHtml +
					"</div></div>"
			);
	}

	$("#" + id)
		.parent()
		.parent()
		.children(".para_div")
		.find(".palitext")
		.first()
		.append(strPalitext);

	let htmlSent = "";
	htmlSent += "<div class='note_body'>";
	htmlSent += "<div class='palitext_div'>";
	htmlSent += "<div class='palitext palitext1'>" + strPalitext + "</div>";
	htmlSent += "<div class='palitext palitext2'></div>";
	htmlSent += "</div>";
	htmlSent += "<div class='sent_tran_div'>";
	for (const oneTran of iterator.translation) {
		let html = "<span class='tran_sent' lang='" + oneTran.lang + "' channal='" + oneTran.channal + "'>";
		html += marked(term_std_str_to_tran(oneTran.text, oneTran.channal, oneTran.editor, oneTran.lang));
		html += "</span>";
		htmlSent += html;
		let channelId = "0";
		if (_channal != "") {
			channelId = oneTran.channal;
		}
		$("#" + id)
			.parent()
			.parent()
			.find(".tran_div_channel[channal='" + channelId + "']")
			.append(html);
	}
	htmlSent += "</div>";
	htmlSent += "</div>"; //note_body
	htmlSent += "<div class='note_foot'>";
	htmlSent += "<span>" + iterator.ref + "</span>";
	htmlSent +=
		"<span class='sent_id'>" +
		iterator.book +
		"-" +
		iterator.para +
		"-" +
		iterator.begin +
		"-" +
		iterator.end +
		"</span>";
	htmlSent += "</div>"; //note_foot

	return htmlSent;
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
	if (parseInt(channalinfo.power) == 30) {
		output += gLocal.gui.your.slice(0, 1);
	} else {
		output += channalinfo.nickname.slice(0, 1);
	}

	output += "</span>";
	output += "</div>";

	output += "<div style='width: 100%;overflow-x: hidden;'>";

	output += "<div class='channal_list' >";

	//  output += "<a href='../wiki/wiki.php?word=" + _word;
	//  output += "&channal=" + channalinfo.id + "' >";
	switch (parseInt(channalinfo.status)) {
		case 10:
			output += "🔐";
			break;
		case 20:
			output += "🌐";
			break;
		case 30:
			output += "🌐";
			break;
		default:
			break;
	}
	if (parseInt(channalinfo.power) >= 20) {
		//if (parseInt(channalinfo.power) != 30)
		{
			output += "✏️";
		}
	}
	//✋
	output += "<a onclick=\"set_channal('" + channalinfo.id + "')\">";

	output += channalinfo["name"];

	output += "</a>";
	if (parseInt(channalinfo.power) == 30) {
		output += "@" + gLocal.gui.your;
	} else {
		output += "@" + channalinfo["nickname"];
	}
	output += "</div>";

	output += "<div class='userinfo_channal'>";
	output += channalinfo["username"];
	output += "</div>";

	//绘制句子进度
	if (channalinfo["final"]) {
		//进度
		output += "<div>";
		let article_len = channalinfo["article_len"];
		let svg_width = article_len;
		let svg_height = parseInt(article_len / 15);
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
		output += channalinfo["count"] + "/" + channalinfo["all"] + "@" + curr_x;
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
生成编辑模式句子块
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
		output += "<a onclick='junp_to(this)'>" + gLocal.gui.jump_to_this_sent + "</a>";
	}
	output +=
		"<a  onclick='related_para_dlg_open(" +
		in_json.book +
		"," +
		in_json.para +
		")'>" +
		gLocal.gui.related_para +
		"</a>";
	output +=
		"<a  onclick='goto_nissaya(" +
		in_json.book +
		"," +
		in_json.para +
		"," +
		in_json.begin +
		"," +
		in_json.end +
		")'>" +
		gLocal.gui.show_nissaya +
		"</a>";
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
	output += "<a onclick='slider_show(this)'>Slider Show</a>";
	output += "</div>";
	output += "</div>";
	output += " </div>";

	let strPalitext =
		"<pali book='" +
		in_json.book +
		"' para='" +
		in_json.para +
		"' begin='" +
		in_json.begin +
		"' end='" +
		in_json.end +
		"' >" +
		in_json.palitext +
		"</pali>";

	output += "<div class='note_body'>";
	output += "<div class='palitext_div'>";
	output += "<div class='palitext palitext_roma'>" + strPalitext + "</div>";
	output += "<div class='palitext palitext1'></div>";
	output += "<div class='palitext palitext2'></div>";
	output += "</div>";

	//译文开始
	output += "<div class='sent_tran_div'>";
	for (const iterator of in_json.translation) {
		output += render_one_sent_tran_a(iterator);
	}
	output += "</div>";
	//译文结束
	output += "</div>"; /**note_body end */

	//工具栏开始
	output += "<div class='other_tran_div' sent='";
	output += in_json.book + "-" + in_json.para + "-" + in_json.begin + "-" + in_json.end + "' >";
	output += "<div class='tool_bar' sent='";
	output += in_json.book + "-" + in_json.para + "-" + in_json.begin + "-" + in_json.end + "' >";
	output += "<span class='tool_left'>";
	//第一个按钮
	//新增译文按钮开始
	output += "<span class='' ";
	output += "book='" + in_json.book + "' ";
	output += "para='" + in_json.para + "' ";
	output += "begin='" + in_json.begin + "' ";
	output += "end='" + in_json.end + "' ";
	output += " >";
	output += "<span class='' onclick='add_new_tran_button_click(this)' title='" + gLocal.gui.add_tran + "'>➕</span>";
	output += "<div class='tran_text_tool_bar'>";
	output += "</div>";
	output += "</span>";
	//新增译文按钮结束
	output += "<span class='separate_line'></span>";
	//第二个按钮
	output += "<span class='more_tran icon_expand'></span>";
	//其他译文工具条
	output += "<span class='other_bar'  >";
	output +=
		"<span class='other_tran_span' title='🧲" +
		gLocal.gui.other +
		gLocal.gui.translation +
		"'><svg class='icon' style='fill: var(--box-bg-color1)'><use xlink:href=\"../studio/svg/icon.svg#more_tran\"></svg>" +
		gLocal.gui.translation +
		"</span>";
	output += "<span class='other_tran_num'></span>";
	output += "</span>";
	output += "<span class='separate_line'></span>";

	//手工义注
	output += "<span class='other_bar'>";
	output +=
		"<span class='other_tran_span commentray' title='📔" +
		gLocal.gui.vannana +
		"'>🪔" +
		gLocal.gui.commentary +
		"</span>";
	output += "<span class='other_tran_num'></span>";
	output += "</span>";
	output += "<span class='separate_line'></span>";

	//第三个按钮 相似句
	if (parseInt(in_json.sim) > 0) {
		output += "<span class='other_bar' >";
		output +=
			"<span class='similar_sent_span' onclick=\"note_show_pali_sim('" +
			in_json.pali_sent_id +
			"')\" title='" +
			gLocal.gui.similar_sentences +
			"'>🧬" +
			gLocal.gui.similar +
			"</span>";
		output += "<span class='similar_sent_num'>" + in_json.sim + "</span>";
		output += "</span>";
		output += "<span class='separate_line'></span>";
	}

	//第三个按钮 相似句结束
	output += "</span>";

	output += "<span class='tool_right'>";
	//出处路径开始
	output += "<span class='ref'>";
	output += "<span class='book_name tooltip'>" + in_json.booktitle;
	output += "<span class='tooltiptext tooltip-bottom'>";
	output += in_json.ref;
	output += "</span>";
	output += "<span class='sent_no'>";
	output += in_json.book + "-" + in_json.para + "-" + in_json.begin + "-" + in_json.end;
	output += "<span>";
	output += "</span>";

	output += "</span>";
	//出处路径结束
	output += "</span>";

	output += "</div>";
	//工具栏结束

	//未选择的其他译文开始
	output += "<div class='other_tran'>";
	output += "</div>";

	output += "</div>";

	return output;
}

//设置取消输入框的编辑模式
function sent_tran_set_edit_mode(obj, isEditMode) {
	$(".sent_tran").removeClass("edit_mode");
	if (isEditMode) {
		let jqObj = $(obj);
		while (!jqObj.hasClass("sent_tran")) {
			jqObj = jqObj.parent();
			if (!jqObj) {
				return;
			}
		}
		jqObj.addClass("edit_mode");
	}
}

function sent_tran_edit(obj) {
	let jqObj = $(obj);
	while (!jqObj.hasClass("sent_tran")) {
		jqObj = jqObj.parent();
		if (!jqObj) {
			return;
		}
	}
	if (jqObj.hasClass("edit_mode")) {
		jqObj.removeClass("edit_mode");
	} else {
		$(".sent_tran").removeClass("edit_mode");
		jqObj.addClass("edit_mode");
	}
}

function sent_pr_merge(id) {
	$.post(
		"../usent/sent_pr_merge.php",
		{
			id: id,
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.status > 0) {
				alert("error" + result.message);
			} else {
				ntf_show("成功采纳");
			}
		}
	);
}
function sent_commit(src, id) {
	commit_init({
		src: src,
		sent: [id],
		express: true,
	});
}
function render_one_sent_tran_a(iterator) {
	let mChannel = get_channel_by_id(iterator.channal);

	let tranText;
	let sid = iterator.book + "-" + iterator.para + "-" + iterator.begin + "-" + iterator.end;
	if (iterator.text == "") {
		if (typeof iterator.channalinfo == "undefined") {
			tranText =
				"<span style='color:var(--border-line-color);'>" +
				"空" +
				"@" +
				iterator.editor_name.nickname +
				"</span>";
		} else {
			tranText =
				"<span style='color:var(--border-line-color);'>" +
				iterator.channalinfo.name +
				"-" +
				iterator.channalinfo.lang +
				"</span>";
		}
	} else {
		//note_init处理句子链接
		tranText = note_init(term_std_str_to_tran(iterator.text, iterator.channal, iterator.editor, iterator.lang));
	}
	let html = "";
	html += "<div class='sent_tran ";
	if (typeof iterator.is_pr != "undefined" && iterator.is_pr == true) {
		html += " pr ";
	}
	html += "' dbid='" + iterator.id + "' channel='" + iterator.channal + "' sid='" + sid + "'>";
	html += "<div class='sent_tran_inner'>";
	html += '<div class="tool_bar">';
	html += '	<div class="right">';
	//句子菜单
	html += '<div class="pop_menu">';

	if (typeof iterator.is_pr != "undefined" && iterator.is_pr == true) {
		//在pr 列表中的译文
		if (typeof iterator.is_pr_editor != "undefined" && iterator.is_pr_editor == true) {
			//提交人
			//修改按钮
			html += "<button class='icon_btn tooltip' onclick='sent_tran_edit(this)'>";
			html += '<svg class="icon" >';
			html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_mode_edit"></use>';
			html += "</svg>";
			html += "<span class='tooltiptext tooltip-top'>";
			html += gLocal.gui.modify;
			html += "</span>";
			html += "</button>";

			//删除按钮
			html += "<button class='icon_btn tooltip' onclick='sent_pr_del(this)'>";
			html += '<svg class="icon" >';
			html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_delete"></use>';
			html += "</svg>";
			html += "<span class='tooltiptext tooltip-top'>";
			html += gLocal.gui.delete;
			html += "</span>";
			html += "</button>";
		} else {
			//非提交人
			if (parseInt(iterator.mypower) >= 20) {
				//有权限 采纳按钮
				html += "<button class='icon_btn tooltip' onclick=\"sent_pr_merge('" + iterator.id + "')\">";
				html += '<svg class="icon" >';
				html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#accept_copy"></use>';
				html += "</svg>";
				html += "<span class='tooltiptext tooltip-top'>";
				html += gLocal.gui.accept_copy;
				html += "</span>";
				html += "</button>";
			}
			//点赞按钮
			html += "<button class='icon_btn tooltip' onclick='sent_pr_like(this)'>";
			html += '<svg class="icon" >';
			html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#like"></use>';
			html += "</svg>";
			html += "<span class='tooltiptext tooltip-top'>";
			html += gLocal.gui.like;
			html += "</span>";
			html += "</button>";
		}
	} else {
		//非pr列表里的句子
		//编辑按钮
		html += "<button class='icon_btn tooltip' onclick='sent_tran_edit(this)'>";
		html += '<svg class="icon" >';
		if (parseInt(iterator.mypower) < 20) {
			html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#my_idea"></use>';
		} else {
			html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_mode_edit"></use>';
		}
		html += "</svg>";
		html += "<span class='tooltiptext tooltip-top'>";
		if (parseInt(iterator.mypower) < 20) {
			html += gLocal.gui.suggest;
		} else {
			html += gLocal.gui.edit;
		}
		html += "</span>";
		html += "</button>";

		//推送按钮
		let commitIcon = "";
		let commitTipText = "";
		if (parseInt(iterator.mypower) >= 30 && parseInt(iterator.status) < 30) {
			//我的私有资源 公开发布
			commitIcon = "publish";
			commitTipText = gLocal.gui.publish;
		} else {
			if (parseInt(iterator.mypower) < 20) {
				//只读资源 采纳
				commitIcon = "accept_copy";
				commitTipText = gLocal.gui.accept_copy;
			} else {
				//其他资源 复制到
				commitIcon = "copy";
				commitTipText = gLocal.gui.copy_to;
			}
		}
		html += "<button class='icon_btn tooltip' ";
		html += " onclick=\"sent_commit('" + iterator.channal + "','" + sid + "')\">";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#' + commitIcon + '"></use>';
		html += "</svg>";
		html += "<span class='tooltiptext tooltip-top'>";
		html += commitTipText;
		html += "</span>";
		html += "</button>";
		//推送按钮结束

		//更多按钮
		html += '<div class="case_dropdown">';
		html += "<button class='icon_btn'>";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_more"></use>';
		html += "</svg>";
		html += "</button>";
		html += '<div class="case_dropdown-content menu_space_between" style="right:0;">';
		//时间线
		html += "<a onclick=\"history_show('" + iterator.id + "')\">";
		html += "<span>" + gLocal.gui.timeline + "</span>";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#recent_scan"></use>';
		html += "</svg>";
		html += "</a>";
		//复制
		html += "<a onclick=\"history_show('" + iterator.id + "')\">";
		html += "<span>" + gLocal.gui.copy + "</span>";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#copy"></use>';
		html += "</svg>";
		html += "</a>";
		//点赞
		html += "<a onclick=\"history_show('" + iterator.id + "')\">";
		html += "<span>" + gLocal.gui.like + "</span>";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#like"></use>';
		html += "</svg>";
		html += "</a>";
		//分享
		html += "<a onclick=\"history_show('" + iterator.id + "')\">";
		html += "<span>" + gLocal.gui.share_to + "</span>";
		html += '<svg class="icon" >';
		html += '<use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#share_to"></use>';
		html += "</svg>";
		html += "</a>";

		html += "</div>";
		html += "</div>";
		//更多按钮结束
	}

	html += "</div>";
	//句子菜单结束
	html += "</div>";
	html += "</div>";
	//tool_bar 结束
	html += '<div class="left_bar" >';
	html += '	<div class="face">';
	if (iterator.id != "") {
		html += '<span class="head_img">' + iterator.editor_name.nickname.slice(0, 1) + "</span>";
	}
	html += "</div>";
	html +=
		'<div class="date" title="' +
		getFullDataTime(iterator.update_time) +
		'">' +
		getDataTime(iterator.update_time) +
		"</div>";
	html += "</div>";
	html += '<div class="body">';
	html += '<div class="head_bar">';
	html += '<div class="info">';
	html += '<span class="name" title="' + iterator.editor_name.nickname + gLocal.gui.recent_update + '">';
	if (typeof iterator.channalinfo == "undefined") {
		html += "unkown";
	} else {
		html += iterator.channalinfo.name;
	}

	html += "</span>";
	html += '<span class="date">' + getPassDataTime(iterator.update_time) + "</span>";
	html += "</div>";
	html += "<div class='preview'>" + tranText + "</div>";
	html += "</div>";

	html += '<div class="edit">';
	html += '<div class="input">';
	html += "<textarea class='tran_sent_textarea' dbid='" + iterator.id + "' ";
	html += "sid='" + sid + "' ";
	html += "channel='" + iterator.channal + "' ";
	if (typeof iterator.is_pr != "undefined" && iterator.is_pr == true) {
		html += ' is_pr="true" onchange1="note_pr_save(this)"';
	} else {
		html += 'is_pr="false" onchange1="note_sent_save_a(this)"';
	}

	html += ">" + iterator.text + "</textarea>";
	html += "</div>";
	html += '<div class="edit_tool">';
	//html += ""
	html +=
		'<span style="display: inline-flex;"><svg class="icon" style="width: 25px;height: 18px;"><svg id="ESC_button" viewBox="0 0 210 150" version="1.1"><filter inkscape:label="Button" inkscape:menu="Bevels" inkscape:menu-tooltip="Soft bevel, slightly depressed middle" style="color-interpolation-filters:sRGB;" id="filter1808"><feMorphology in="SourceAlpha" radius="6.6" result="result1" id="feMorphology1784" /><feGaussianBlur stdDeviation="8.9" in="result1" id="feGaussianBlur1786" /><feColorMatrix values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.3 0" result="result91" id="feColorMatrix1788" /><feComposite in="result0" operator="out" result="result2" in2="result91" id="feComposite1790" /><feGaussianBlur stdDeviation="1.7" result="result4" id="feGaussianBlur1792" /><feDiffuseLighting surfaceScale="10" id="feDiffuseLighting1796"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1794" /></feDiffuseLighting><feBlend in2="SourceGraphic" mode="multiply" id="feBlend1798" /><feComposite in2="SourceAlpha" operator="in" result="result3" id="feComposite1800" /><feSpecularLighting in="result4" surfaceScale="5" specularExponent="17.9" id="feSpecularLighting1804"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1802" /></feSpecularLighting><feComposite in2="result3" operator="atop" id="feComposite1806" /></filter></defs><sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="2.4865144" inkscape:cx="57.142857" inkscape:cy="96.638595" inkscape:document-units="mm" inkscape:current-layer="layer1" inkscape:document-rotation="0" showgrid="false" inkscape:window-width="1920" inkscape:window-height="1001" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" /><rect ry="58.781078" style="fill:#999999;fill-opacity:1;stroke-width:0;stroke-miterlimit:4;stroke-dasharray:none;filter:url(#filter1808)" id="rect1264" width="210" height="150" x="0" y="0" /><text xml:space="preserve" id="text1266" style="font-style:normal;font-weight:normal;font-size:10.5833px;line-height:1.25;font-family:sans-serif;white-space:pre;shape-inside:url(#rect1268);fill:#000000;fill-opacity:1;stroke:none;" transform="matrix(1.1320886,0,0,1.1287136,-1.7621172,25.458127)"><tspan x="30" y="70"><tspan style="font-size:70.5556px;fill:#ffffff">ESC</tspan></tspan></text></svg></svg>&nbsp;=&nbsp;';
	html +=
		"<a onclick='tran_sent_edit_cancel(this)'>" +
		gLocal.gui.cancel +
		"</a></span><span style='display: inline-flex;'>";
	html +=
		'<svg class="icon" style="width: 30px;height: 18px;"><svg id="ESC_button" viewBox="0 0 250 150" version="1.1"><filter inkscape:label="Button" inkscape:menu="Bevels" inkscape:menu-tooltip="Soft bevel, slightly depressed middle" style="color-interpolation-filters:sRGB;" id="filter1808"><feMorphology in="SourceAlpha" radius="6.6" result="result1" id="feMorphology1784" /><feGaussianBlur stdDeviation="8.9" in="result1" id="feGaussianBlur1786" /><feColorMatrix values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.3 0" result="result91" id="feColorMatrix1788" /><feComposite in="result0" operator="out" result="result2" in2="result91" id="feComposite1790" /><feGaussianBlur stdDeviation="1.7" result="result4" id="feGaussianBlur1792" /><feDiffuseLighting surfaceScale="10" id="feDiffuseLighting1796"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1794" /></feDiffuseLighting><feBlend in2="SourceGraphic" mode="multiply" id="feBlend1798" /><feComposite in2="SourceAlpha" operator="in" result="result3" id="feComposite1800" /><feSpecularLighting in="result4" surfaceScale="5" specularExponent="17.9" id="feSpecularLighting1804"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1802" /></feSpecularLighting><feComposite in2="result3" operator="atop" id="feComposite1806" /></filter></defs><sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="2.4865144" inkscape:cx="57.142857" inkscape:cy="96.638595" inkscape:document-units="mm" inkscape:current-layer="layer1" inkscape:document-rotation="0" showgrid="false" inkscape:window-width="1920" inkscape:window-height="1001" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" /><rect ry="58.781078" style="fill:#999999;fill-opacity:1;stroke-width:0;stroke-miterlimit:4;stroke-dasharray:none;filter:url(#filter1808)" id="rect1264" width="250" height="150" x="0" y="0" /><text xml:space="preserve" id="text1266" style="font-style:normal;font-weight:normal;font-size:10.5833px;line-height:1.25;font-family:sans-serif;white-space:pre;shape-inside:url(#rect1268);fill:#000000;fill-opacity:1;stroke:none;" transform="matrix(1.1320886,0,0,1.1287136,-1.7621172,25.458127)"><tspan x="30" y="70"><tspan style="font-size:70.5556px;fill:#ffffff">Shift</tspan></tspan></text></svg></svg>';
	html += "➕";
	html +=
		'<svg class="icon" style="width: 32px;height: 18px;"><svg id="ESC_button" style="width: 32px;height: 18px;" viewBox="0 0 260 150" version="1.1"><filter inkscape:label="Button" inkscape:menu="Bevels" inkscape:menu-tooltip="Soft bevel, slightly depressed middle" style="color-interpolation-filters:sRGB;" id="filter1808"><feMorphology in="SourceAlpha" radius="6.6" result="result1" id="feMorphology1784" /><feGaussianBlur stdDeviation="8.9" in="result1" id="feGaussianBlur1786" /><feColorMatrix values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.3 0" result="result91" id="feColorMatrix1788" /><feComposite in="result0" operator="out" result="result2" in2="result91" id="feComposite1790" /><feGaussianBlur stdDeviation="1.7" result="result4" id="feGaussianBlur1792" /><feDiffuseLighting surfaceScale="10" id="feDiffuseLighting1796"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1794" /></feDiffuseLighting><feBlend in2="SourceGraphic" mode="multiply" id="feBlend1798" /><feComposite in2="SourceAlpha" operator="in" result="result3" id="feComposite1800" /><feSpecularLighting in="result4" surfaceScale="5" specularExponent="17.9" id="feSpecularLighting1804"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1802" /></feSpecularLighting><feComposite in2="result3" operator="atop" id="feComposite1806" /></filter></defs><sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="2.4865144" inkscape:cx="57.142857" inkscape:cy="96.638595" inkscape:document-units="mm" inkscape:current-layer="layer1" inkscape:document-rotation="0" showgrid="false" inkscape:window-width="1920" inkscape:window-height="1001" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" /><rect ry="58.781078" style="fill:#999999;fill-opacity:1;stroke-width:0;stroke-miterlimit:4;stroke-dasharray:none;filter:url(#filter1808)" id="rect1264" width="260" height="150" x="0" y="0" /><text xml:space="preserve" id="text1266" style="font-style:normal;font-weight:normal;font-size:10.5833px;line-height:1.25;font-family:sans-serif;white-space:pre;shape-inside:url(#rect1268);fill:#000000;fill-opacity:1;stroke:none;" transform="matrix(1.1320886,0,0,1.1287136,-1.7621172,25.458127)"><tspan x="30" y="70"><tspan style="font-size:70.5556px;fill:#ffffff">Enter</tspan></tspan></text></svg></svg>&nbsp;=&nbsp;';
	if (parseInt(iterator.mypower) < 20) {
		html += "<a onclick='tran_sent_save(this)'>";
		html += gLocal.gui.submit + "<b>" + gLocal.gui.suggest + gLocal.gui.translation + "</b>";
		html += "</a></span><span style='display: inline-flex;'>";
	} else {
		html += "<a onclick='tran_sent_save(this)'>";
		html += gLocal.gui.save;
		html += "</a></span><span style='display: inline-flex;'>";
	}
	html +=
		'<svg class="icon" style="width: 32px;height: 18px;"><svg id="ESC_button" style="width: 32px;height: 18px;" viewBox="0 0 260 150" version="1.1"><filter inkscape:label="Button" inkscape:menu="Bevels" inkscape:menu-tooltip="Soft bevel, slightly depressed middle" style="color-interpolation-filters:sRGB;" id="filter1808"><feMorphology in="SourceAlpha" radius="6.6" result="result1" id="feMorphology1784" /><feGaussianBlur stdDeviation="8.9" in="result1" id="feGaussianBlur1786" /><feColorMatrix values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 0.3 0" result="result91" id="feColorMatrix1788" /><feComposite in="result0" operator="out" result="result2" in2="result91" id="feComposite1790" /><feGaussianBlur stdDeviation="1.7" result="result4" id="feGaussianBlur1792" /><feDiffuseLighting surfaceScale="10" id="feDiffuseLighting1796"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1794" /></feDiffuseLighting><feBlend in2="SourceGraphic" mode="multiply" id="feBlend1798" /><feComposite in2="SourceAlpha" operator="in" result="result3" id="feComposite1800" /><feSpecularLighting in="result4" surfaceScale="5" specularExponent="17.9" id="feSpecularLighting1804"><feDistantLight azimuth="225" elevation="45" id="feDistantLight1802" /></feSpecularLighting><feComposite in2="result3" operator="atop" id="feComposite1806" /></filter></defs><sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="2.4865144" inkscape:cx="57.142857" inkscape:cy="96.638595" inkscape:document-units="mm" inkscape:current-layer="layer1" inkscape:document-rotation="0" showgrid="false" inkscape:window-width="1920" inkscape:window-height="1001" inkscape:window-x="-9" inkscape:window-y="-9" inkscape:window-maximized="1" /><rect ry="58.781078" style="fill:#999999;fill-opacity:1;stroke-width:0;stroke-miterlimit:4;stroke-dasharray:none;filter:url(#filter1808)" id="rect1264" width="260" height="150" x="0" y="0" /><text xml:space="preserve" id="text1266" style="font-style:normal;font-weight:normal;font-size:10.5833px;line-height:1.25;font-family:sans-serif;white-space:pre;shape-inside:url(#rect1268);fill:#000000;fill-opacity:1;stroke:none;" transform="matrix(1.1320886,0,0,1.1287136,-1.7621172,25.458127)"><tspan x="30" y="70"><tspan style="font-size:70.5556px;fill:#ffffff">Enter</tspan></tspan></text></svg></svg>';
	//html += "<button>Enter</button>"
	html += "&nbsp;=&nbsp;";
	html += gLocal.gui.next_line;
	html += "</span><span style='display: inline-flex;'>MarkDown✅</span>";
	html += "</div>";
	html += "</div>";

	html += '<div class="foot_bar">';

	html += '<div class="info">';
	if (iterator.id != "") {
		html += '<span class="date"> ' + getPassDataTime(iterator.update_time) + "</span>";
	}
	if (iterator.id != "") {
		html += '<span class="name">' + iterator.editor_name.nickname + "</span>";
	}
	if (iterator.id != "") {
		html += '<span class="channel">' + gLocal.gui.updated + " ";
		if (typeof iterator.channalinfo == "undefined") {
			html += "unkown";
		} else {
			html += "<a title='" + iterator.channalinfo.owner + "'>" + iterator.channalinfo.name + "@</a>";
		}
		html += "</span>";
	} else {
		html += '<span class="channel">' + gLocal.gui.no_updated + " @";
		if (typeof iterator.channalinfo == "undefined") {
			html += "unkown";
		} else {
			html += "<a title='" + iterator.channalinfo.owner + "'>" + iterator.channalinfo.name + "@</a>";
		}
		html += "</span>";
	}

	html += '<ul class="tag_list">';
	if (iterator.pr_all && parseInt(iterator.pr_all) > 0) {
		html +=
			"<li onclick=\"note_pr_show('" +
			iterator.channal +
			"','" +
			sid +
			"')\"><span class='icon'>✋</span><span class='num'>" +
			iterator.pr_new +
			"/" +
			iterator.pr_all +
			"</span></li>";
	}
	html += "</ul>";
	html += "</div>"; //end of info

	html += "</div>"; //end of foot bar

	html += "</div>";
	html += "</div>";
	//sent_tran_inner结束
	html += '<div class="pr_content"></div>';
	html += "</div>";
	return html;
}

function tran_sent_textarea_event_init() {
	let textarea = document.querySelectorAll(".tran_sent_textarea");
	for (let iterator of textarea) {
		iterator.onkeydown = function (e) {
			let menu = document.querySelector("#menu");
			switch (e.key) {
				case "Enter":
					if (menu && menu.style.display == "block") {
						let value = textarea.value;
						let selectionStart = textarea.selectionStart;
						let str1 = value.slice(0, selectionStart);
						let str2 = value.slice(selectionStart);
						textarea.value = str1 + data[menuFocusIndex] + "]]" + str2;
						menu.style.display = "none";
						return false;
					} else {
						if (e.shiftKey) {
							//回车存盘
							tran_sent_save(e.currentTarget);
							return false;
						}
					}
					break;
				case "Escape":
					tran_sent_edit_cancel(e.currentTarget);
					break;
				default:
					break;
			}
		};
	}
}

function render_one_sent_tran(book, para, begin, end, iterator) {
	let output = "";
	output += "<div class='tran' lang='" + iterator.lang + "' style='display:flex;'>";
	//译文工具按钮开始
	output += "<div class='tran_text_tool_botton' onclick='tool_bar_show(this)'>";
	output +=
		"<div class='icon_expand' style='width: 0.8em;height: 0.8em;min-width: 0.8em;min-height: 0.8em;transition: transform 0.5s ease;'></div>";
	//译文工具栏开始
	output += "<div class='tran_text_tool_bar'>";
	output += "<div style='border-right: solid 1px;margin: 0.3em 0;'><li class = 'tip_buttom' ";
	output +=
		" onclick=\"note_edit_sentence('" +
		book +
		"' ,'" +
		para +
		"' ,'" +
		begin +
		"' ,'" +
		end +
		"' ,'" +
		iterator.channal +
		"')\"";
	output +=
		">" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_mode_edit"></use></svg>';
	output += gLocal.gui.edit + "</li>";
	output += "<li class = 'tip_buttom' ";
	output += " onclick=\"history_show('" + iterator.id + "')\" >";
	output +=
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#recent_scan"></use></svg>';
	output += gLocal.gui.timeline + "</li>";
	output +=
		"<li class = 'tip_buttom'>" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#copy"></use></svg>';
	output += gLocal.gui.copy + "</li></div>";

	output +=
		"<div style='border-right: solid 1px;margin: 0.3em 0;'><li class = 'tip_buttom'>" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#like"></use></svg>';
	output += gLocal.gui.like + "</li>";
	output +=
		"<li class = 'tip_buttom'>" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#comment"></use></svg>';
	output += gLocal.gui.comment + "</li>";
	output +=
		"<li class = 'tip_buttom'>" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#ic_shopping_cart"></use></svg>';
	output += gLocal.gui.digest + "</li></div>";
	output +=
		"<div style='margin: 0.3em 0;'><li class = 'tip_buttom'>" +
		'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="../studio/svg/icon.svg#share_to"></use></svg>';
	output += gLocal.gui.share_to + "</li>";
	output += "</div></div>";
	//译文工具栏结束
	output += "</div>";
	//译文工具按钮结束
	//译文正文开始
	output +=
		"<div class='text' id='tran_text_" +
		book +
		"_" +
		para +
		"_" +
		begin +
		"_" +
		end +
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
		//note_init处理句子链接
		output += note_init(term_std_str_to_tran(iterator.text, iterator.channal, iterator.editor, iterator.lang));
	}
	output += "</div>";
	//译文正文结束

	output += "</div>";
	//单个channal译文框结束
	return output;
}
function add_new_tran_button_click(obj) {
	let html = "<div style='display:flex; max-width: 40vw; white-space: normal;'>";
	var first_lang = "";
	for (const iterator of _my_channal) {
		if (iterator.lang) {
			first_lang = iterator.lang;
			break;
		}
	}
	html += "<ul class='channel_list lang_0' >";
	html += "<li>";
	html += first_lang;
	html += "</li>";
	for (const iterator of _my_channal) {
		if (iterator.status > 0 && first_lang.indexOf(iterator.lang) != -1 && iterator.lang != 0) {
			if (_channal.indexOf(iterator.id) < 0) {
				html += '<li class="channel_name" onclick="';
				html +=
					"new_sentence('" +
					$(obj).parent().attr("book") +
					"' ,'" +
					$(obj).parent().attr("para") +
					"' ,'" +
					$(obj).parent().attr("begin") +
					"' ,'" +
					$(obj).parent().attr("end") +
					"' ,'" +
					iterator.id +
					"',this)";
				html += '" title="' + iterator.nickname;
				html += '">' + iterator.name;
				if (parseInt(iterator.power) < 20) {
					html += "(建议)";
				}
				html += "</li>";
			}
		}
	}
	html += "</ul>";
	html += "<ul class='channel_list lang_1'>";
	html += "<li>";
	html += gLocal.gui.other;
	html += "</li>";
	for (const iterator of _my_channal) {
		if (iterator.status > 0 && first_lang.indexOf(iterator.lang) == -1 && iterator.lang != 0) {
			if (_channal.indexOf(iterator.id) < 0) {
				html += '<li class="channel_name" onclick="';
				html +=
					"new_sentence('" +
					$(obj).parent().attr("book") +
					"' ,'" +
					$(obj).parent().attr("para") +
					"' ,'" +
					$(obj).parent().attr("begin") +
					"' ,'" +
					$(obj).parent().attr("end") +
					"' ,'" +
					iterator.id +
					"',this)";
				html += '" title="' + iterator.nickname;
				html += '">' + iterator.name;
				if (parseInt(iterator.power) < 20) {
					html += "(建议)";
				}
				html += "</li>";
			}
		}
	}
	html += "</ul>";
	html += "<ul class='channel_list lang_2'>";
	html += "<li>";
	html += gLocal.gui.collaborate;
	html += "</li>";
	for (const iterator of _my_channal) {
		if (iterator.status > 0 && iterator.lang == 0) {
			if (_channal.indexOf(iterator.id) < 0) {
				html += '<li class="channel_name" onclick="';
				html +=
					"new_sentence('" +
					$(obj).parent().attr("book") +
					"' ,'" +
					$(obj).parent().attr("para") +
					"' ,'" +
					$(obj).parent().attr("begin") +
					"' ,'" +
					$(obj).parent().attr("end") +
					"' ,'" +
					iterator.id +
					"',this)";
				html += '" title="' + iterator.nickname;
				html += '">' + iterator.name;
				if (parseInt(iterator.power) < 20) {
					html += "(建议)";
				}
				html += "</li>";
			}
		}
	}
	html += "</ul>";

	html += "</div>";
	$(obj).parent().children(".tran_text_tool_bar").first().html(html);

	if ($(obj).parent().children(".tran_text_tool_bar").css("display") == "block") {
		$(obj).parent().children(".tran_text_tool_bar").first().hide();
	} else {
		$(obj).parent().children(".tran_text_tool_bar").first().show();
		$(document).one("click", function () {
			$(obj).parent().children(".tran_text_tool_bar").first().hide();
		});
		event.stopPropagation();
		$(obj).parent().show();
	}
}
function tool_bar_show(element) {
	if ($(element).find(".tran_text_tool_bar").css("display") == "none") {
		$(element).find(".tran_text_tool_bar").css("display", "flex");
		$(element).find(".icon_expand").css("transform", "rotate(-180deg)");
		$(element).css("background-color", "var(--btn-bg-color)");
		$(element).css("visibility", "visible");
		$(document).one("click", function () {
			$(element).find(".tran_text_tool_bar").hide();
			$(element).css("background-color", "var(--nocolor)");
			$(element).find(".icon_expand").css("transform", "unset");
			$(element).css("visibility", "");
		});
		event.stopPropagation();
	} else {
		$(element).find(".tran_text_tool_bar").hide();
		$(element).css("background-color", "var(--nocolor)");
		$(element).find(".icon_expand").css("transform", "unset");
		$(element).css("visibility", "");
	}
}
function new_sentence(book, para, begin, end, channel, obj) {
	let newsent = { id: "", text: "", lang: "", channal: channel };

	for (let iterator of _arrData) {
		if (iterator.book == book && iterator.para == para && iterator.begin == begin && iterator.end == end) {
			let found = false;
			for (const tran of iterator.translation) {
				if (tran.channal == channel) {
					found = true;
					break;
				}
			}
			if (!found) {
				iterator.translation.push(newsent);
			}
		}
	}
	if ($(obj).parent().parent().css("display") == "block") {
		$(obj).parent().parent().hide();
	}

	note_edit_sentence(book, para, begin, end, channel);
}

//显示更多译文按钮动作
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
				.find(".other_bar")
				.click(function () {
					const sentid = $(this).parent().parent().attr("sent").split("-");
					const book = sentid[0];
					const para = sentid[1];
					const begin = sentid[2];
					const end = sentid[3];
					let sentId = $(this).parent().parent().attr("sent");
					if ($(this).parent().parent().siblings(".other_tran").first().css("display") == "none") {
						$(".other_tran_div[sent='" + sentId + "']")
							.children(".other_tran")
							.slideDown();
						$(this).siblings(".more_tran ").css("transform", "unset");
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
								let html = "<div class='compact'>";
								for (const iterator of arrSent) {
									if (_channal.indexOf(iterator.channal) == -1) {
										html += render_one_sent_tran_a(iterator);
									}
								}
								html += "</div>";
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
						$(this).siblings(".more_tran ").css("transform", "rotate(-90deg)");
					}
				});
		} else {
			//隐藏自己
			//$(this).hide();
			$(this).find(".other_tran_span").addClass("disable"); //gLocal.gui.no + gLocal.gui.other + gLocal.gui.translation
			//$(this).find(".more_tran").hide();
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
						book +
						"' para='" +
						para +
						"' begin='" +
						begin +
						"' end='" +
						end +
						"' channal='" +
						channal +
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
function tran_sent_edit_cancel(obj) {
	sent_tran_set_edit_mode(obj, false);
}
function tran_sent_save(obj) {
	let sentDiv = find_sent_tran_div(obj);
	if (sentDiv) {
		let textarea = $(sentDiv).children().find(".tran_sent_textarea").first();
		let isPr = $(textarea).attr("is_pr");
		if (isPr == "true") {
			note_pr_save(textarea);
		} else {
			note_sent_save_a(textarea);
		}
		sent_tran_set_edit_mode(textarea, false);
	} else {
		console.error("sent div not found");
	}
}

//保存pr句子 新
function note_pr_save(obj) {
	let id = $(obj).attr("dbid");
	let sid = $(obj).attr("sid").split("-");
	let book = sid[0];
	let para = sid[1];
	let begin = sid[2];
	let end = sid[3];
	let channel = $(obj).attr("channel");
	let text = $(obj).val();
	let sent_tran_div = find_sent_tran_div(obj);
	$.post(
		"../usent/pr_post.php",
		{
			id: id,
			book: book,
			para: para,
			begin: begin,
			end: end,
			channel: channel,
			text: text,
		},
		sent_save_callback
	);

	if (sent_tran_div) {
		$(sent_tran_div).find(".preview").addClass("loading");
	}
}

//保存译文句子 新
function note_sent_save_a(obj) {
	let id = $(obj).attr("dbid");
	let sid = $(obj).attr("sid").split("-");
	let book = sid[0];
	let para = sid[1];
	let begin = sid[2];
	let end = sid[3];
	let channal = $(obj).attr("channel");
	let text = $(obj).val();
	let sent_tran_div = find_sent_tran_div(obj);
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
		sent_save_callback
	);

	if (sent_tran_div) {
		$(sent_tran_div).find(".preview").addClass("loading");
	}
}
function update_sent_tran(sentData) {}
function sent_save_callback(data) {
	let result = JSON.parse(data);
	if (result.status > 0) {
		alert("error" + result.message);
	} else {
		let sid = result.book + "-" + result.para + "-" + result.begin + "-" + result.end;

		let sent_tran_div = $(
			".sent_tran[dbid='" + result.id + "'][channel='" + result.channal + "'][sid='" + sid + "']"
		);
		if (result.commit_type == 1 || result.commit_type == 2) {
			ntf_show("成功修改");
			if (sent_tran_div) {
				let divPreview = sent_tran_div.find(".preview").first();
				if (result.text == "") {
					let channel_info = "Empty";
					let thisChannel = find_channal(result.channal);
					if (thisChannel) {
						channel_info = thisChannel.name + "-" + thisChannel.nickname;
					}
					divPreview.html("<span style='color:var(--border-line-color);'>" + channel_info + "</span>");
				} else {
					divPreview.html(
						marked(term_std_str_to_tran(result.text, result.channal, result.editor, result.lang))
					);
					term_updata_translation();
					popup_init();
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
				sent_tran_div.find(".preview").removeClass("loading");
			}
		} else if (result.commit_type == 3) {
			ntf_show("已经提交修改建议");
		} else {
			ntf_show("未提交");
		}
	}
}

//保存译文句子
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
				if (result.commit_type == 1 || result.commit_type == 2) {
					ntf_show("成功修改");
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
				} else if (result.commit_type == 3) {
					ntf_show("已经提交修改建议");
				} else {
					ntf_show("未提交");
				}
			}
		}
	);
}

function copy_ref(book, para, begin, end) {
	let strRef = "{{" + book + "-" + para + "-" + begin + "-" + end + "}}";
	copy_to_clipboard(strRef);
}

function goto_nissaya(book, para, begin = 0, end = 0) {
	window.open("../nissaya/index.php?book=" + book + "&para=" + para + "&begin=" + begin + "&end=" + end, "nissaya");
}
function edit_in_studio(book, para, begin, end) {
	wbw_channal_list_open(book, [para]);
}

//显示和隐藏某个内容 如 巴利文
function setVisibility(key, value) {
	switch (key) {
		case "palitext":
			if ($(value).is(":checked")) {
				$(".palitext").show();
			} else {
				$(".palitext").hide();
			}

			break;

		default:
			break;
	}
}

function note_show_pali_sim(SentId) {
	pali_sim_dlg_open(SentId, 0, 20);
}

function set_pali_script(pos, script) {
	if (script == "none") {
		$(".palitext" + pos).html("");
	} else {
		$(".palitext" + pos).each(function () {
			let html = $(this).siblings(".palitext_roma").first().html();
			$(this).html(html);
		});

		$(".palitext" + pos)
			.find("*")
			.contents()
			.filter(function () {
				return this.nodeType != 1;
			})
			.wrap("<pl" + pos + "></pl" + pos + ">");

		$(".palitext" + pos)
			.contents()
			.filter(function () {
				return this.nodeType != 1;
			})
			.wrap("<pl" + pos + "></pl" + pos + ">");

		$("pl" + pos).html(function (index, oldcontent) {
			return roman_to_my(oldcontent);
		});
	}
}

function splite_pali_word() {
	$("pali")
		.contents()
		.filter(function () {
			return this.nodeType != 1;
		})
		.wrap("<pl></pl>");

	$("pl").each(function () {
		let html = $(this).html();
		$(this).html("<w>" + html.replace(/\s/g, "</w> <w>") + "</w>");
	});

	$("w").click(function () {
		let word = com_getPaliReal($(this).text());
		if (gBuildinDictIsOpen) {
			window.open("../dict/index.php?builtin=true&key=" + word, "dict");
		}
	});
}

function refresh_pali_script() {
	if (_display && _display == "para") {
		//段落模式
	} else {
		//句子模式
		setting_get("lib.second_script", set_second_scrip);
	}
}
function set_second_scrip(value) {
	set_pali_script(2, value);
}
function slider_show(obj) {
	$(obj).parent().parent().parent().parent().parent().toggleClass("slider_show_shell");
}

function find_sent_tran_div(obj) {
	let jqObj = $(obj);
	while (!jqObj.hasClass("sent_tran")) {
		jqObj = jqObj.parent();
		if (!jqObj) {
			return false;
		}
	}
	return jqObj;
	/*
	let parent = obj.parentNode;
	while (parent.nodeType == 1) {
		if ($(parent).hasClass("sent_tran")) {
			return parent;
		} else if (parent.nodeName == "BODY") {
			return false;
		}
		parent = parent.parentNode;
	}

	return false;
	*/
}
//显示或隐藏pr数据
function note_pr_show(channel, id) {
	let obj = $(".sent_tran[channel='" + channel + "'][sid='" + id + "']").find(".pr_content");
	let prHtml = obj.first().html();
	if (prHtml == "") {
		note_get_pr(channel, id);
	} else {
		obj.slideUp();
		obj.html("");
	}
}

//获取pr数据并显示
function note_get_pr(channel, id) {
	let sid = id.split("-");
	let book = sid[0];
	let para = sid[1];
	let begin = sid[2];
	let end = sid[3];
	$.post(
		"../usent/get_pr.php",
		{
			book: book,
			para: para,
			begin: begin,
			end: end,
			channel: channel,
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.length > 0) {
				let html = "<div class='compact'>";
				for (const iterator of result) {
					html += render_one_sent_tran_a(iterator);
				}
				html += "</div>";
				$(".sent_tran[channel='" + channel + "'][sid='" + id + "']")
					.find(".pr_content")
					.html(html);
				$(".sent_tran[channel='" + channel + "'][sid='" + id + "']")
					.find(".pr_content")
					.slideDown();
			} else {
			}
		}
	);
	$(".sent_tran[channel='" + channel + "'][sid='" + id + "']")
		.find(".pr_content")
		.html("loading");
	$(".sent_tran[channel='" + channel + "'][sid='" + id + "']")
		.find(".pr_content")
		.show();
}

function get_channel_by_id(id) {
	if (typeof _channalData != "undefined") {
		for (const iterator of _channalData) {
			if (iterator.id == id) {
				return iterator;
			}
		}
	}
	if (typeof _my_channal != "undefined") {
		for (const iterator of _my_channal) {
			if (iterator.id == id) {
				return iterator;
			}
		}
	}
	return false;
}
//设置显示方向
function setDirection(obj) {
	if (obj.value == "row") {
		$("#contents").removeClass("vertical");
		$("#contents").addClass("horizontal");
		_direction = "row";
	} else {
		$("#contents").removeClass("horizontal");
		$("#contents").addClass("vertical");
		_direction = "col";
	}
}
//设置逐段或逐句模式
function setDisplay(obj) {
	if (obj.value == "para") {
		$("#contents").removeClass("sent_mode");
		$("#contents").addClass("para_mode");
		_display = "para";
	} else {
		$("#contents").removeClass("para_mode");
		$("#contents").addClass("sent_mode");
		_display = "sent";
	}
}
