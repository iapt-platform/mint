var _reader_view = "sent";
var _reader_sent_id = -1;
var _reader_book = -1;
var _reader_para = -1;
var _reader_begin = -1;
var _reader_end = -1;
var _channal = "";
var _lang = "";
var _author = "";
var _display = "para";
var _direction = "row";
var arrMyTerm = new Array();
var _sent_data = new Array();
var link_str = "";

palicanon_load_term();

function reader_load() {
	let param;
	if (_reader_view == "sim") {
		param = { view: "sim", id: _reader_sent_id };
	} else {
		param = {
			view: _reader_view,
			book: _reader_book,
			para: _reader_para,
			begin: _reader_begin,
			end: _reader_end,
		};
	}
	$.get("../reader/get_para.php", param, function (data) {
		_sent_data = JSON.parse(data);
		let tpl = "";
		let currPara = 0;
		$("#contents").html("");
		if (_sent_data.sentences.length > 0) {
			let firstPara = _sent_data.sentences[0].paragraph;
			for (const iterator of _sent_data.sentences) {
				if (currPara != iterator.paragraph && _reader_view != "sim") {
					currPara = parseInt(iterator.paragraph);
					if (currPara == parseInt(_reader_para) + 1 && parseInt(_reader_para) != firstPara) {
						tpl += "</div>\n\n";
					}
					tpl += "\n\n";
					tpl += "```para\n";
					tpl += currPara + "\n";
					tpl += "```\n\n";
					if (currPara == _reader_para && parseInt(_reader_para) != firstPara) {
						tpl += "<div id='para_focus' class='focus'>\n\n";
					}
				}
				tpl +=
					"{{" +
					iterator.book +
					"-" +
					iterator.paragraph +
					"-" +
					iterator.begin +
					"-" +
					iterator.end +
					"}}\n";
			}
			link_str = tpl;
			$("#contents").html(note_init(tpl));
			note_refresh_new(function () {
				document.querySelector("#para_focus").scrollIntoView({
					block: "end",
					behavior: "smooth",
				});
				//document.querySelector("#para_focus").scrollTo(0, 200);
			});
			reader_draw_para_menu();

			//右侧目录
			let tocHtml = "";
			let tocNextMenu = "";
			if (_sent_data.toc.length > 0) {
				let firstLevel = _sent_data.toc[0].level;
				for (let index = 1; index < _sent_data.toc.length; index++) {
					const element = _sent_data.toc[index];
					tocHtml +=
						"<div class='reader_right_toc level_" +
						(element.level - firstLevel) +
						"'><a href='#para_" +
						element.paragraph +
						"'>" +
						element.toc +
						"</a></div>";
					tocNextMenu +=
						"<a href='../reader/?view=chapter&book=" +
						_reader_book +
						"&par=" +
						element.paragraph +
						"'>" +
						element.toc +
						"</a>";
				}
				$("#toc_content").html(tocHtml);
				if (tocNextMenu === "") {
					$("#para_path_next_level").hide();
				} else {
					$("#toc_next_menu").html(tocNextMenu);
					$("#para_path_next_level").show();
				}
			}
		}

		if (_sent_data.head == 1 || _sent_data.sentences.length == 0) {
			//渲染目录
			tpl = "<h2>Table of Content</h2>";
			if (_sent_data.toc.length > 0) {
				let firstLevel = _sent_data.toc[0].level;
				for (let index = 1; index < _sent_data.toc.length; index++) {
					const element = _sent_data.toc[index];
					if (element.level < 8) {
						tpl +=
							"<div class='reader_main_toc level_" +
							(element.level - firstLevel) +
							"'><a href='../reader/?view=chapter&book=" +
							_reader_book +
							"&par=" +
							element.paragraph +
							"&display=" +
							_display +
							"'>" +
							element.toc +
							"</a></div>";
					}
				}
			}
			$("#contents_toc").html(tpl);
		}
	});

	reader_get_path();
}

function reader_draw_para_menu() {
	$(".language-para").each(function () {
		let strPara = $(this).text();
		$(this).addClass("case_dropdown");
		let html = "<a name='para_" + strPara + "'></a>";
		html += "<div class='case_dropdown-content para_menu'>";
		if (typeof _reader_view != "undefined" && _reader_view != "para") {
			html += "<a onclick=\"junp_to_para('" + _reader_book + "','" + strPara + "')\">仅显示此段</a>";
		}
		html += "<a onclick=\"edit_wbw('" + _reader_book + "','" + strPara + "')\">" + gLocal.gui.edit_now + "</a>";
		html += "<a  onclick='goto_nissaya(" + _reader_book + "," + strPara + ")'>" + gLocal.gui.show_nissaya + "</a>";
		html +=
			"<a onclick=\"copy_para_ref('" + _reader_book + "','" + strPara + "')\">" + gLocal.gui.copy_to_clipboard + "</a>";
		html +=
			"<a onclick=\"copy_text('" +
			_reader_book +
			"','" +
			strPara +
			"')\">" +
			gLocal.gui.copy +
			"“" +
			gLocal.gui.pāli +
			"”</a>";
		html +=
			"<a onclick=\"add_to_list('" +
			_reader_book +
			"','" +
			strPara +
			"')\">" +
			gLocal.gui.add_to_edit_list +
			"</a>";
		html += "</div>";
		$(this).append(html);
	});
}

function junp_to_para(book, para) {
	let url = "../reader/?view=para&book=" + book + "&par=" + para + "&display=sent";
	location.assign(url);
}

function copy_para_ref(book, para) {
	let output = "";
	let currPara = para;
	for (const iterator of _sent_data.sentences) {
		if (iterator.book == book && iterator.paragraph == para) {
			output += "{{" + book + "-" + para + "-" + iterator.begin + "-" + iterator.end + "}}\n";
		}
	}
	output += "\n";
	copy_to_clipboard(output);
}
function reader_get_path() {
	$.get(
		"../reader/get_config.php",
		{
			book: _reader_book,
			para: _reader_para,
		},
		function (data) {
			$("#para_path").html(data);
			$("chapter").each(function () {
				//替换为本地语言字符串
				let title = $(this).text();
				let strLocalTitle = "";
				if (
					(strLocalTitle = term_lookup_my(
						title,
						_channal.split()[0],
						getCookie("userid"),
						getCookie("language")
					))
				) {
					$(this).html(strLocalTitle.word);
				} else if (
					(strLocalTitle = term_lookup_my_a(
						title,
						_channal.split()[0],
						getCookie("userid"),
						getCookie("language")
					))
				) {
					let newLocal = title.replace(strLocalTitle.word, strLocalTitle.meaning);
					$(this).html(newLocal);
				}
			});
			var bookTitle = $("chapter").first().html();
			let suttaTitle = $("chapter").last().html();

			$("#pali_pedia").html(bookTitle);
			$("#article_title").html(suttaTitle);
			$("#page_title").text(suttaTitle);
			if (_reader_view == "chapter") {
				//$("chapter").last().hide();
			}
		}
	);
}

function palicanon_load_term() {
	$.get(
		"../term/term.php",
		{
			op: "my",
		},
		function (data) {
			arrMyTerm = JSON.parse(data);
		}
	);
}

function set_channal(channalid) {
	let url = "../reader/?view=" + _reader_view;
	if (_reader_book != -1) {
		url += "&book=" + _reader_book;
	}
	if (_reader_para != -1) {
		url += "&par=" + _reader_para;
	}
	if (_reader_begin != -1) {
		url += "&begin=" + _reader_begin;
	}
	if (_reader_end != -1) {
		url += "&end=" + _reader_end;
	}
	if (channalid != "") {
		url += "&channal=" + channalid;
	}
	if (_display != "") {
		url += "&display=" + _display;
	}
	if (_mode != "") {
		url += "&mode=" + _mode;
	}
	if (_direction != "") {
		url += "&direction=" + _direction;
	}
	location.assign(url);
}

function edit_wbw(book, para) {
	wbw_channal_list_open(book, [para]);
}

function setMode(mode = "read") {
	let url = "../reader/?view=" + _reader_view;

	if (_reader_sent_id != -1) {
		url += "&id=" + _reader_sent_id;
	}
	if (_reader_book != -1) {
		url += "&book=" + _reader_book;
	}
	if (_reader_para != -1) {
		url += "&par=" + _reader_para;
	}
	if (_reader_begin != -1) {
		url += "&begin=" + _reader_begin;
	}
	if (_reader_end != -1) {
		url += "&end=" + _reader_end;
	}
	if (_channal != "") {
		url += "&channal=" + _channal;
	}
	if (_display != "") {
		if (mode == "read") {
			url += "&display=" + _display;
		} else {
			url += "&display=sent";
		}
	}
	if (mode != "") {
		url += "&mode=" + mode;
	}
	location.assign(url);
}
