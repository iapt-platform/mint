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
	$.getJSON(
		"/api/v2/sentence",
		{
			view: "fulltext",
			key: keyword,
			page: _page,
		},
		function (data) {
			let result = data;
			console.log(result.time);
			let html = "";
			html += "<div>查询到 "+result.data.rows.length+" 条结果 "+"</div>";
			for (const iterator of result.data.rows) {
				html += render_word_result(iterator);
			}
			$("#contents").html(html);

		}
	);
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

	html += "<div class='title'>";
	html +=
		"<a href='../article/index.php?view=sent&book=" +
		worddata.book_id +
		"&par=" +
		worddata.paragraph +
        "&start=" +
		worddata.word_start +
        "&end=" +
		worddata.word_end +
		"' target='_blank'>";
	html += "Open" + "</a></div>";

	let highlightStr;

	highlightStr= highlightWords(worddata.content, _key_word);


	html += "<div class='wizard_par_div'>" + highlightStr + "</div>";
	html += "<div class='path'>" + worddata.book_id + "-" + worddata.paragraph + "-" + worddata.word_start + "-" + worddata.word_end + "</div>";
	html += "</div>";
	return html;
}


function gotoPage(index) {
	_page = index;
	paliword_search(_key_word, _filter_word, _bookId);
}


function dict_input_change(obj) {
	search_pre_search(obj.value);
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
		window.location.assign("../search/sentence.php?key=" + obj.value);
	}
}

