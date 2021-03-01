var dict_pre_searching = false;
var dict_pre_search_curr_word = "";
var dict_search_xml_http = null;

function dict_search(word) {
	$("#pre_search_result").hide();
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
	word = standardize(word);

	$.get(
		"dict_lookup.php",
		{
			word: word,
		},
		function (data, status) {
			$("#dict_search_result").html(data);
			$("#dict_list").append($("#dictlist"));
			$("#search_result_shell").append($("#search_summary"));
			guide_init();
			let word_count = parseInt($("#word_count").val());
			if (word_count < 3) {
				trubo_split();
			}
		}
	);
}
function standardize(word) {
	let word_end = word.slice(-1);
	if (word_end == "n" || word_end == "m") {
		word_end = "ṃ";
		word = word.slice(0, -1) + word_end;
	}
	return word;
}

function dict_pre_search(word) {
	if (dict_pre_searching == true) {
		return;
	}
	dict_pre_searching = true;
	dict_pre_search_curr_word = word;

	$.get(
		"dict_lookup_pre.php",
		{
			word: word,
		},
		function (data, status) {
			dict_pre_searching = false;
			dict_pre_search_curr_word = "";
			try {
				let result = JSON.parse(data);
				let html = "<div>";
				for (const iterator of result) {
					html += "<div class='dict_word_list' onclick=\"dict_pre_word_click('" + iterator.word + "')\">";
					html += "<span class='spell' >" + iterator.word + "(" + iterator.count + ")</span>";
					html += "<div class='mean'>" + iterator.mean + "</div>";
					html += "</div>";
				}
				html += "</div>";
				$("#pre_search_word_content").html(html);
				$("#pre_search_result").css("display", "block");
				$(document).one("click", function () {
					$("#pre_search_result").hide();
				});
			} catch (error) {}
		}
	);
}

function dict_pre_word_click(word) {
	$("#dict_ref_search_input").val(word);
	$("#pre_search_result").hide();
	dict_search(word);
}

function search_on_load(word) {
	$("#dict_ref_search_input").val(word);
	dict_search(word);
}

function dict_input_change(obj) {
	dict_pre_search(obj.value);
}

function dict_input_onfocus() {
	if ($("#dict_ref_search_input").val() == "") {
		dict_show_history();
	}
}

function dict_input_keyup(e, obj) {
	var keynum;
	var keychar;
	var numcheck;

	if ($("#dict_ref_search_input").val() == "") {
		dict_show_history();
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
		dict_search(obj.value);
	} else {
		dict_input_split(obj.value);
		if (obj.value.indexOf("+") == -1) {
			dict_pre_search(obj.value);
		} else {
			dict_input_split(obj.value);
			$("#pre_search_result").hide();
		}
	}
}

function dict_input_split(word) {
	if (word.indexOf("+") >= 0) {
		let wordParts = word.split("+");
		let strParts = "";
		for (const iterator of wordParts) {
			strParts += "<part><a onclick='dict_search(\"" + iterator + "\")'>" + iterator + "</a></part>";
		}
		let html =
			"点击查词<div class='dropdown_ctl'><div class='content'><div class='main_view' >" +
			strParts +
			"</div></div></div>";
		$("#input_parts").html(html);
	} else {
		$("#input_parts").html("");
	}
}

function dict_show_history() {
	if (!localStorage.searchword) {
		localStorage.searchword = "";
	}
	var arrHistory = localStorage.searchword.split(",");
	var strHistory = "";
	if (arrHistory.length > 0) {
		strHistory += '<a onclick="cls_word_search_history()">清空历史记录</a>';
	}
	for (var i = 0; i < arrHistory.length; i++) {
		var word = arrHistory[i];
		strHistory += "<div class='dict_word_list'>";
		strHistory += "<a onclick='dict_pre_word_click(\"" + word + "\")'>" + word + "</a>";
		strHistory += "</div>";
	}
	$("#dict_ref_search_result").html(strHistory);
}

function cls_word_search_history() {
	localStorage.searchword = "";
	$("#dict_ref_search_result").html("");
}

function trubo_split() {
	$("#pre_search_result").hide();
	$("#input_parts").html("正在自动切分复合词……");
	$.post(
		"split.php",
		{
			word: $("#dict_ref_search_input").val(),
		},
		function (data, status) {
			try {
				let result = JSON.parse(data);
				let html = "<div>";
				if (result.length > 0) {
					for (const part of result[0]["data"]) {
						html += '自动拆分结果<div class="dropdown_ctl">';
						html += '<div class="content">';
						html +=
							'<div class="main_view">' +
							"<part>" +
							part[0].word.replace(/\+/g, "</part><part>") +
							"</part>" +
							"</div>";
						html += '<div class="more_button">' + part.length + "</div>";
						html += "</div>";
						html += '<div class="menu" >';
						for (const one_part of part) {
							html += '<div class="part_list">' + one_part.word + "</div>";
						}
						html += "</div>";
						html += "</div>";
					}
				} else {
					html += "无法拆分";
				}
				html += "</div>";
				$("#input_parts").html(html);

				$(".more_button").click(function () {
					$(this).parent().siblings(".menu").toggle();
				});

				$(".part_list").click(function () {
					let html = "<part>" + $(this).text().replace(/\+/g, "</part><part>") + "</part>";
					$(this).parent().parent().find(".main_view").html(html);
					$(this).parent().hide();
					$("part").click(function () {
						dict_search($(this).text());
					});
				});

				$("part").click(function () {
					dict_search($(this).text());
				});
			} catch (e) {}
		}
	);
}

function setNaviVisibility(strObjId = "") {
	var objNave = document.getElementById("dict_list");
	var objblack = document.getElementById("dict_list_shell");
	if (strObjId == "") {
		objblack.style.display = "none";
		objNave.className = "dict_list_off";
	} else {
		objblack.style.display = "block";
		objNave.className = "dict_list_on";
	}
}
