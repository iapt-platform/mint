var _id = "";
var _word = "";
var _channel = "";
var _lang = "";
var _author = "";
var _active = "";
var _term_list;

function wiki_index_init() {}

function term_render_word_to_div(strWord,eDiv) {
	let word = [{ pali: strWord, channal: "", editor: "", lang: "" }];

	$.post(
		"../term/term_get.php",
		{
			words: JSON.stringify(word),
		},
		function (data, status) {
			let html = "";			
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					if (result.length > 0) {
						_term_list = result;
						//生成头部信息
						let type = new Array();
						let authors = new Array();
						//计算所有贡献者
						for (const iterator of result) {
							if (iterator.tag == "") {
								iterator.tag = "_null_";
							}
							if (type[iterator.tag] == null) {
								type[iterator.tag] = new Array();
							}
							type[iterator.tag].push(iterator.meaning);
							authors[iterator.owner] = iterator.user;
						}

						html += "<div class='term_word_head'>";
						html += "<div class='term_word_head_pali'>";
						html += result[0].word;
						$("#page_title").text(result[0].word + "-" + gLocal.gui.encyclopedia);
						html += "</div>";
						for (y in type) {
							html += "<div class='term_word_head_mean'>";
							if (y != "_null_") {
								html += y + "：";
							}
							for (k in type[y]) {
								html += type[y][k];
							}
							html += "</div>";
						}
						html += "<div class='term_word_head_authors'>" + gLocal.gui.contributor + "：";
						for (y in authors) {
							if (authors[y].nickname != "") {
								html += '<a onclick="">' + authors[y].nickname + "</a> ";
							} else {
								html += '<a onclick="">' + y + "</a> ";
							}
						}

						html += "</div>";
						html += "</div>";
						$("#wiki_head").html(html);
						// end of term_word_head
						html = "";

						html += "<div id='term_list_div' style='display:flex;'>";
						html += "<div id='term_list'>";

						for (const iterator of result) {
							if (iterator.tag == "_null_") {
								iterator.tag = "";
							}
							html += "<div class='term_block'>";
							html += "<div class='term_block_bar'>";
							html += "<div class='term_block_bar_left'>";
							html += "<div class='term_block_bar_left_icon'>";
							html += iterator.user.nickname.slice(0, 1);
							html += "</div>";

							html += "<div class='term_block_bar_left_info'>";
							html += "<div class='term_author'>" + iterator.user.nickname + "</div>";
							html += "<div class='term_meaning'>" + iterator.meaning;
							if (iterator.tag != "_null_") {
								html += "<span class='term_tag'>" + iterator.tag + "</span>";
							}
							html += "</div>";

							html += "</div>";

							html += "</div>";

							html += "<div class='term_block_bar_right'>";
							html += "<span>";
							if (!iterator.readonly) {
								html +=
									"<button class='icon_btn' onclick=\"wiki_term_edit('" +
									iterator.guid +
									"')\">" +
									gLocal.gui.edit +
									"</button>";
							}

							html += "<button class='icon_btn'><a href='../article/index.php?view=term&id="+iterator.guid+"&display=sent&mode=edit' target='_blank'>" + gLocal.gui.translate + "</a></button>";
							//TODO 增加点赞按钮
                            //html += "<button class='icon_btn'><a href='#'>" + gLocal.gui.like + "</a></button>";
                            html += "</span>";
							html += "</div>";

							html += "</div>";
							//term_block_bar 结束
							html +=
								"<div class='term_note' guid='" +
								iterator.guid +
								"'>" +
								note_init(iterator.note) +
								"</div>";
							html += "<div class='term_edit' id='term_edit_" + iterator.guid + "' ></div>";
							//html += "</div>";
						}
						html += "</div>";

						//end of right

						html += "</div>";
						// end of term_list_div
						
					} else {
						if (_active != "new"){
							html = "词条尚未创建 <a href='./wiki.php?word="+strWord+"&active=new'>现在创建</a>";
						}
						else{
							html = "无";
						}
					}
					$("#"+eDiv).html(html);
					note_refresh_new();
					document.title = result[0].word + "[" + result[0].meaning + "]-圣典百科";
				} catch (e) {
					console.error("term_render_word_to_div:" + e + " data:" + data);
				}
			} else {
				console.error("term error:" + data);
			}
		}
	);
}
function wiki_term_edit(id) {
	for (const iterator of _term_list) {
		if (iterator.guid == id) {
			$("#term_edit_" + id).html(render_term_form(iterator));
			$("#term_edit_" + id).show();
			$(".term_note[guid='" + id + "']").hide();
			return id;
		}
	}
	return false;
}
function term_edit_cancel() {
	$(".term_edit").hide();
	$(".term_edit").html("");
	$(".term_note").show();
}
function render_term_form(item) {
	let html = "";
	html += "<form id='form_term'>";
	html += '<input type="hidden" name="id" value="' + item.guid + '" />';
	html += "<ul>";
	html += "<li ><span class='field'>" + gLocal.gui.pali_word + "</span>";
	html +=
		'<span class="input"><input id="form_word" type="input" name="word" value="' +
		item.word +
		'" placeholder="' +
		gLocal.gui.required +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.first_choice_word + "</span>";
	html +=
		'<span class="input"><input id="form_mean" type="input" name="mean" value="' +
		item.meaning +
		'" placeholder="' +
		gLocal.gui.required +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.other_meaning + "</span>";
	html +=
		'<span class="input"><input type="input" name="mean2" value="' +
		item.other_meaning +
		'" placeholder="' +
		gLocal.gui.optional +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.tag + "</span>";
	html +=
		'<span class="input"><input type="input" name="tag" value="' +
		item.tag +
		'" placeholder="' +
		gLocal.gui.optional +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.channel + "</span>";
	html +=
		'<span class="input"><input type="input"  name="channal" value="' +
		item.channel +
		'" placeholder="' +
		gLocal.gui.optional +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.language + "</span>";
	html +=
		'<span class="input"><input id="form_lang" type="input"  name="language" value="' +
		item.language +
		'" placeholder="' +
		gLocal.gui.required +
		'"/></span></li>';

	html += "<li ><span class='field'>" + gLocal.gui.note + "</span>";
	html += "<span class='input'><textarea name='note'>" + item.note + "</textarea></span></li>";
	html += "</ul>";
	html += "</form>";
	html += "<button onclick='term_save()'>" + gLocal.gui.save + "</button>";
	if (item.guid != "") {
		html += "<button onclick='term_edit_cancel()'>" + gLocal.gui.cancel + "</button>";
	}

	return html;
}
function term_render_new_word(title, word) {
	let html = "";
	html += "<div class='term_word_head_pali'>" + title + "</div>";
	$("#wiki_head").html(html);
	html = render_term_form({
		guid: "",
		word: word,
		meaning: "",
		other_meaning: "",
		owner: "",
		channel: _channel,
		language: "",
		tag: "",
		note: "",
	});
	html += "<h2 style='border-top: 1px solid var(--border-line-color);padding-top: 0.5em;'>其他解释</h2>"
	html += "<div id='term_list'>loading……</div>"
	$("#wiki_body_left").html(html);
	term_render_word_to_div(word,"term_list");
}

function term_save() {
	if ($("#form_word").val() == "") {
		alert(gLocal.gui.pali_word + " 不能为空");
		return;
	}
	if ($("#form_mean").val() == "") {
		alert(gLocal.gui.first_choice_word + "不能为空");
		return;
	}
	if ($("#form_lang").val() == "") {
		alert(gLocal.gui.language + "不能为空");
		return;
	}
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../term/term_post.php", //url
		data: $("#form_term").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert(result.message + gLocal.gui.saved + gLocal.gui.successful);
				window.location.assign("../wiki/wiki.php?word=" + result.message);
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

function wiki_load_id(guid) {
	note_create();
	note_lookup_guid_json(guid, "wiki_body_left");
}

function wiki_load_word(word) {
	note_create();
	if (_active == "new") {
		term_render_new_word(gLocal.gui.new_technic_term, word);
	}else{
		term_render_word_to_div(word,"wiki_body_left");
	}
}
function wiki_goto_word(guid, strWord) {
	window.open("wiki.php?word=" + strWord, "_blank");
}
function wiki_word_loaded(wordlist) {
	$("#doc_title").text(wordlist[0].word + "[" + wordlist[0].meaning + "]-圣典百科");
}

function term_show_win(guid, word) {
	window.location.assign("wiki.php?word=" + word);
}

function wiki_search_keyup(e, obj) {
	var keynum;
	var keychar;
	var numcheck;

	if ($("#wiki_search_input").val() == "") {
		$("#search_result").html("");
		return;
	}

	if (window.event) {
		// IE
		keynum = e.keyCode;
	} else if (e.which) {
		// Netscape/Firefox/Opera
		keynum = e.which;
	}
	var keychar = String.fromCharCode(keynum);
	if (keynum == 13) {
		window.location.assign("wiki.php?word=" + obj.value);
	} else {
		wiki_pre_search(obj.value);
	}
}

function wiki_pre_search(keyword) {
	$.get(
		"../term/term.php",
		{
			op: "pre",
			word: keyword,
			format: "json",
		},
		function (data, status) {
			let result = JSON.parse(data);
			let html = "<ul class='wiki_search_list'>";
			if (result.length > 0) {
				for (x in result) {
					html +=
						"<li><a href='./wiki.php?op=get&word=" +
						result[x].word +
						"'>" +
						result[x].word +
						"[" +
						result[x].meaning +
						"]</a></li>";
				}
			}
			html += "</ul>";
			$("#search_result").html(html);
		}
	);
}

function set_channal(channalid) {
	location.assign("../wiki/wiki.php?word=" + _word + "&channal=" + channalid);
}
