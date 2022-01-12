var dict_pre_searching = false;
var dict_pre_search_curr_word = "";
var dict_search_xml_http = null;
var _key_word = "";
var _page = 0;
var _filter_word = new Array();
var _bookId = new Array();

$(document).ready(function () {
	paliword_search(_key_word);
});

function paliword_search(keyword, words = new Array(), book = new Array()) {
	$.get(
		"../search/paliword_sc.php",
		{
			op: "search",
			key: keyword,
			words: JSON.stringify(words),
			book: JSON.stringify(book),
			page: _page,
		},
		function (data) {
			let result = JSON.parse(data);
			console.log(result.time);
			let html = "";
			let iTotleTime = result.time[result.time.length-1].time;
			html += "<div>查询到 "+result.data.length+" 条结果 "+iTotleTime+"秒</div>";
			for (const iterator of result.data) {
				html += render_word_result(iterator);
			}
			$("#contents").html(html);


			if(result.case){
				//所查单词格位变化表
				html = "";

				html += "<div class='case_item'>";
				html += "<div class='spell'><a onclick='case_filter_all()'>all</a> " + result.case_num + " Words</div>";
				html += "<div class='tag'>" + result.case_count + "</div>";
				html += "</div>";
				for (const iterator of result.case) {
					html += render_case(iterator);
				}
				$("#case_content").html(html);			
			}


			html = "";
			html += "<div class='book_tag_div filter'>";
			if (result.book_tag) {
				for (const iterator of result.book_tag) {
					html += render_book_tag(iterator);
				}
			}
			html += "</div>";
			if (result.book_list) {
				let allcount = 0;
				for (const iterator of result.book_list) {
					allcount += parseInt(iterator.count);
				}
				html += render_book_list({ book: 0, title: "All", count: allcount });
				for (const iterator of result.book_list) {
					html += render_book_list(iterator);
				}
			}
			$("#book_list").html(html);
			$("#contents_nav").html(render_nav(result));
			//章节路径链接点击，弹出阅读章节窗口
			chapter_onclick_init();
		}
	);
}

/*
  |---------------------------------------
  |章节路径链接点击，弹出阅读章节窗口
  |---------------------------------------
*/
function chapter_onclick_init() {
	$("chapter").click(function () {
		let bookid = $(this).attr("book");
		let para = $(this).attr("para");
		window.open("../article/?view=chapter&book=" + bookid + "&par=" + para, "_blank");
	});

	$("para").click(function () {
		let bookid = $(this).attr("book");
		let para = $(this).attr("para");
		window.open("../article/?view=para&book=" + bookid + "&par=" + para, "_blank");
	});
}
function highlightWords(line, word) {
	if (line && line.length > 0) {
		let output = line;
		for (const iterator of word) {
			let regex = new RegExp("(" + iterator + ")", "gi");
			output = output.replace(regex, "<highlight>$1</highlight>");
		}
		return output;
	} else {
		return "";
	}
}
function render_word_result(worddata) {
	let html = "";
	html += "<div class='search_result'>";
	let keyword = worddata.keyword;

	html += "<div class='title'>";
	html +=
		"<a href='../reader/?view=chapter&book=" +
		worddata.book +
		"&par=" +
		worddata.para +
		"&direction=col' target='_blank'>";
	html += worddata.title + "</a></div>";

	let highlightStr;

	if(typeof worddata.highlight=="undefined"){
		highlightStr= highlightWords(worddata.palitext, keyword);
	}else{
		highlightStr= worddata.highlight;
	}
	

	html += "<div class='wizard_par_div'>" + highlightStr + "</div>";
	html += "<div class='path'>" + worddata.path + "</div>";
	html += "<div class='path'>" + worddata.book + "-" + worddata.para + "-" + worddata.wt + "</div>";
	html += "</div>";
	return html;
}

//渲染单词变格表
function render_case(data) {
	let html = "";
	let wordid = data.id;
	html += "<div class='fileter_item case_item'>";
	html += "<div class='spell ";
	if (data.selected === true) {
		html += "selected";
	} else {
		html += "invalid";
	}
	html += "'>";
	html += "<input id='bold_word_" + wordid + "' class='wordcase filter' type='checkbox' ";
	if (data.selected === true) {
		html += " checked ";
	}
	html += "wordid='" + wordid + "' />";
	html += '<a onclick="word_select(' + wordid + ')">';

	html += data.spell;
	html += "</a>";
	html += "</div>";
	html += "<div class='tag'>" + data.count + "</div>";
	html += "</div>";
	return html;
}

//选择需要过滤的单词
function word_search_filter() {
	let wordlist = new Array();
	$(".wordcase").each(function () {
		if ($(this).prop("checked")) {
			wordlist.push($(this).attr("wordid"));
		}
	});
	filter_cancel();
	_filter_word = wordlist;
	_page = 0;
	paliword_search(_key_word, wordlist);
}

function word_select(wordid) {
	_filter_word = [wordid];
	_page = 0;
	paliword_search(_key_word, [wordid]);
}

function case_filter_all() {
	_page = 0;
	paliword_search(_key_word);
}

//渲染书标签过滤
function render_book_tag(data) {
	let html = "";
	html += "<div class='fileter_item book_tag'>";
	html += "<div class='spell'>";
	html += "<input booktag='" + data.tag + "' class='book_tag' type='checkbox' checked  />";
	html += data.title;
	html += "</div>";
	html += "<div class='tag'>" + data.count + "</div>";
	html += "</div>";
	return html;
}
//渲染书列表
function render_book_list(data) {
	let html = "";
	let bookid = data.book;
	let classSelected = "selected";
	html += "<div class='fileter_item book_item'>";
	html += "<div class='spell ";
	if (data.selected) {
		html += classSelected;
	}
	html += "'>";
	html += "<input book='" + bookid + "' class='book_list filter' type='checkbox' checked  />";
	html += '<a onclick="book_select(' + bookid + ')">';
	html += data.title;
	html += "</a>";
	html += "</div>";
	html += "<div class='tag'>" + data.count + "</div>";
	html += "</div>";
	return html;
}

function render_nav(result) {
	let html = "<ul class='page_nav'>";
	if (result["record_count"] > 20) {
		let pages = parseInt(result["record_count"] / 20);
		for (let index = 0; index < pages; index++) {
			html += "<li ";
			if (index == _page) {
				html += "class='curr'";
			}
			html += " onclick=\"gotoPage('" + index + "')\"";
			html += ">" + (index + 1) + "</li> ";
		}
	}
	//html += "<li >上一页</li> ";
	//html += "<li >下一页</li> ";
	html += "</ul>";
	return html;
}
function gotoPage(index) {
	_page = index;
	paliword_search(_key_word, _filter_word, _bookId);
}
function onWordFilterStart() {
	$(".case_item").children().find(".filter").show();
	$("#case_tools").children(".filter").show();
	$("#case_tools").children().find(".filter").show();
	$("#case_tools").children(".select_button").hide();
}

function filter_cancel() {
	$(".filter").hide();
	$("#case_tools").children(".select_button").show();
}
function search_filter() {}

function search_book_filter(objid, type) {
	if (document.getElementById(objid).checked == true) {
		$("." + type).show();
	} else {
		$("." + type).hide();
	}
}

//选择需要过滤的书
function book_select(bookid) {
	_page = 0;

	if (bookid == 0) {
		_bookId = new Array();
		paliword_search(_key_word, _filter_word);
	} else {
		_bookId = [bookid];
		paliword_search(_key_word, _filter_word, [bookid]);
	}
}
function book_search_filter() {
	let booklist = new Array();
	$(".booklist").each(function () {
		if ($(this).prop("checked")) {
			booklist.push($(this).attr("book"));
		}
	});
	book_filter_cancel();
	_page = 0;
	paliword_search(_key_word, _filter_word, booklist);
}
function onBookFilterStart() {
	$(".book_item").children().find(".filter").show();
	$("#book_tools").children(".filter").show();
	$("#book_tools").children().find(".filter").show();
	$("#book_tools").children(".select_button").hide();
	$(".book_tag_div").show();
}
function book_filter_cancel() {
	$(".filter").hide();
	$(".book_tag_div").hide();
	$("#book_tools").children(".select_button").show();
}

function search_pre_search(word) {
	$.get(
		"../search/paliword_sc_pre.php",
		{
			op: "pre",
			key: word,
		},
		function (data) {
			let words = JSON.parse(data);
			let html = "";
			for (const iterator of words) {
				html += "<a href='../search/paliword.php?key=" + iterator.word + "'>";
				if (parseInt(iterator.bold) > 0) {
					html += "<span style='font-weight:700;'>";
				} else {
					html += "<span>";
				}
				html += iterator.word + "-" + iterator.count;
				html += "</span>";
				html += "</a>";
			}
			$("#pre_search_word_content").html(html);
			$("#pre_search_word_content").css("display", "block");
			$(document).one("click", function () {
				$("#pre_search_word_content").hide();
			});
			event.stopPropagation();
		}
	);
}

//以下为旧的函数
function dict_bold_word_all_select() {
	var wordcount = $("#bold_word_count").val();
	for (var i = 0; i < wordcount; i++) {
		document.getElementById("bold_word_" + i).checked = document.getElementById("bold_all_word").checked;
	}

	dict_update_bold(0);
}

function dict_bold_word_select(id) {
	var wordcount = $("#bold_word_count").val();
	for (var i = 0; i < wordcount; i++) {
		document.getElementById("bold_word_" + i).checked = false;
	}
	document.getElementById("bold_word_" + id).checked = true;

	dict_update_bold(0);
}
function dict_bold_book_select(id) {
	var bookcount = $("#bold_book_count").val();
	for (var i = 0; i < bookcount; i++) {
		document.getElementById("bold_book_" + i).checked = false;
	}
	document.getElementById("bold_book_" + id).checked = true;

	dict_update_bold(0);
}
function dict_update_bold(currpage) {
	var wordlist = "(";
	var wordcount = $("#bold_word_count").val();
	for (var i = 0; i < wordcount; i++) {
		if (document.getElementById("bold_word_" + i).checked) {
			wordlist += "'" + $("#bold_word_" + i).val() + "',";
		}
	}
	wordlist = wordlist.slice(0, -1);
	wordlist += ")";

	var booklist = "(";
	var bookcount = $("#bold_book_count").val();
	for (var i = 0; i < bookcount; i++) {
		if (document.getElementById("bold_book_" + i).checked) {
			booklist += "'" + $("#bold_book_" + i).val() + "',";
		}
	}
	if (booklist.slice(-1) == ",") {
		booklist = booklist.slice(0, -1);
	}

	booklist += ")";

	$.get(
		"./paliword_search.php",
		{
			op: "update",
			target: "bold",
			word: "",
			wordlist: wordlist,
			booklist: booklist,
			currpage: currpage,
		},
		function (data, status) {
			//alert("Data: " + data + "\nStatus: " + status);
			$("#dict_bold_right").html(data);
			$("#bold_book_list").html($("#bold_book_list_new").html());
			$("#bold_book_list_new").html("");
		}
	);
}
function search_search(word) {
	$("#pre_search_result").hide();
	$("#pre_search_result_1").hide();
	if (!localStorage.searchword) {
		localStorage.searchword = "";
	}
	let oldHistory = localStorage.searchword;
	let arrOldHistory = oldHistory.split(",");
	let isExist = false;
	for (let i = 0; i < arrOldHistory.length; i++) {
		if (arrOldHistory[i] == word) {
			isExist = true;
		}
	}
	if (!isExist) {
		localStorage.searchword = word + "," + oldHistory;
	}

	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		dict_search_xml_http = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		dict_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (dict_search_xml_http != null) {
		dict_search_xml_http.onreadystatechange = dict_search_serverResponse;
		word = word.replace(/\+/g, "%2b");
		dict_search_xml_http.open("GET", "./paliword_search.php?op=search&word=" + word, true);
		dict_search_xml_http.send();
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}
function dict_search_serverResponse() {
	if (dict_search_xml_http.readyState == 4) {
		// 4 = "loaded"
		if (dict_search_xml_http.status == 200) {
			// 200 = "OK"
			var serverText = dict_search_xml_http.responseText;
			dict_result = document.getElementById("dict_ref_search_result");
			if (dict_result) {
				dict_result.innerHTML = serverText;
				$("#index_list").hide();
				$("#dict_ref_dict_link").html($("#dictlist").html());
				$("#dictlist").html("");
			}
			//$("#dict_type").html($("#real_dict_tab").html());
		} else {
			alert(dict_pre_search_xml_http.statusText, 0);
		}
	}
}

/*
var dict_pre_search_xml_http = null;
function search_pre_search(word) {
	if (dict_pre_searching == true) {
		return;
	}
	dict_pre_searching = true;
	dict_pre_search_curr_word = word;
	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		dict_pre_search_xml_http = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		dict_pre_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (dict_pre_search_xml_http != null) {
		dict_pre_search_xml_http.onreadystatechange = dict_pre_search_serverResponse;
		dict_pre_search_xml_http.open("GET", "paliword_search.php?op=pre&word=" + word, true);
		dict_pre_search_xml_http.send();
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}
*/
function dict_pre_search_serverResponse() {
	if (dict_pre_search_xml_http.readyState == 4) {
		// 4 = "loaded"
		if (dict_pre_search_xml_http.status == 200) {
			// 200 = "OK"
			var serverText = dict_pre_search_xml_http.responseText;
			$("#pre_search_word_content").html(serverText);
		} else {
			alert(dict_pre_search_xml_http.statusText, 0);
		}
		dict_pre_searching = false;
		var newword = document.getElementById("dict_ref_search_input").value;
		if (newword != dict_pre_search_curr_word) {
			search_pre_search(newword);
		}
	}
}
function dict_pre_word_click(word) {
	$("#pre_search_result").hide();
	$("#pre_search_result_1").hide();
	let inputSearch = $("#dict_ref_search_input").val();
	let arrSearch = inputSearch.split(" ");
	arrSearch[arrSearch.length - 1] = word;
	let strSearchWord = arrSearch.join(" ");
	$("#dict_ref_search_input").val(strSearchWord);
	$("#dict_ref_search_input_1").val(strSearchWord);
	search_search(word);
}

function dict_input_change(obj) {
	search_pre_search(obj.value);
}

function search_show_history() {
	if (!localStorage.searchword) {
		localStorage.searchword = "";
	}
	var arrHistory = localStorage.searchword.split(",");
	var strHistory = "";
	if (arrHistory.length > 0) {
		strHistory += '<a onclick="cls_word_search_history()">清空历史记录</a>';
	}
	const max_history_len = 20;
	let history_len = 0;
	if (arrHistory.length > max_history_len) {
		history_len = max_history_len;
	} else {
		history_len = arrHistory.length;
	}
	for (var i = 0; i < history_len; i++) {
		var word = arrHistory[i];
		strHistory += "<div class='dict_word_list'>";
		strHistory += "<a onclick='dict_pre_word_click(\"" + word + "\")'>" + word + "</a>";
		strHistory += "</div>";
	}
	$("#search_histray").html(strHistory);
}

function search_input_onfocus() {
	if ($("#dict_ref_search_input").val() == "") {
		//search_show_history();
	}
}
function search_input_keyup(e, obj) {
	var keynum;
	var keychar;
	var numcheck;

	if ($("#dict_ref_search_input").val() == "") {
		//search_show_history();
		$("#pre_search_result").hide();
		$("#pre_search_result_1").hide();
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
		//search_search(obj.value);
		window.location.assign("../search/paliword.php?key=" + obj.value);
	} else {
		if (obj.value.indexOf(" ") >= 0) {
			//search_pre_sent(obj.value);
		} else {
			$("#pre_search_sent").hide();
		}
		$("#pre_search_result").show();
		$("#pre_search_result_1").show();
		search_pre_search(obj.value);
	}
}

function search_pre_sent(word) {
	pali_sent_get_word(word, function (result) {
		let html = "";
		try {
			let arrResult = JSON.parse(result);
			for (x in arrResult) {
				html += arrResult[x].text + "<br>";
			}
			$("#pre_search_sent_title_right").html("总共" + arrResult.lenght);
			$("#pre_search_sent_content").html(html);
			$("#pre_search_sent").show();
		} catch (e) {
			console.error(e.message);
		}
	});
}
function cls_word_search_history() {
	localStorage.searchword = "";
	$("#dict_ref_search_result").html("");
}

function search_edit_now(book, para, title) {
	var res_list = new Array();
	res_list.push({
		type: "1",
		album_id: "-1",
		book: book,
		parNum: para,
		parlist: para,
		title: title + "-" + para,
	});
	res_list.push({
		type: "6",
		album_id: "-1",
		book: book,
		parNum: para,
		parlist: para,
		title: title + "-" + para,
	});
	var res_data = JSON.stringify(res_list);
	window.open("../studio/project.php?op=create&data=" + res_data, "_blank");
}
