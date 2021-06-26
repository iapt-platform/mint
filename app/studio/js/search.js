/*全文搜索引擎*/
var g_pali_word_item_hide_id = new Array();
var search_text_pre_searching = false;
var search_text_pre_search_curr_word = "";
var search_text_search_xml_http = null;
function search_text_search(word) {
	if (window.XMLHttpRequest) {// code for IE7, Firefox, Opera, etc.
		search_text_search_xml_http = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {// code for IE6, IE5
		search_text_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (search_text_search_xml_http != null) {
		search_text_search_xml_http.onreadystatechange = search_text_search_serverResponse;
		search_text_search_xml_http.open("GET", "./search_text.php?op=search&word=" + word, true);
		search_text_search_xml_http.send();
	}
	else {
		alert("Your browser does not support XMLHTTP.");
	}

}
function search_text_search_serverResponse() {
	if (search_text_search_xml_http.readyState == 4)// 4 = "loaded"
	{
		//debugOutput("server response.",0);
		if (search_text_search_xml_http.status == 200) {// 200 = "OK"
			var serverText = search_text_search_xml_http.responseText;
			search_text_result = document.getElementById("search_text_result");
			if (search_text_result) {
				search_text_result.innerHTML = serverText;
			}

		}
		else {
			//debugOutput(search_text_pre_search_xml_http.statusText,0);
		}
	}
	path_name_upgrade();

}


var search_text_pre_search_xml_http = null;
function search_text_pre_search(word) {
	if (search_text_pre_searching == true) { return; }
	search_text_pre_searching = true;
	search_text_pre_search_curr_word = word;
	if (window.XMLHttpRequest) {// code for IE7, Firefox, Opera, etc.
		search_text_pre_search_xml_http = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {// code for IE6, IE5
		search_text_pre_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (search_text_pre_search_xml_http != null) {
		search_text_pre_search_xml_http.onreadystatechange = search_text_pre_search_serverResponse;
		search_text_pre_search_xml_http.open("GET", "./search_text.php?op=pre&word=" + word, true);
		search_text_pre_search_xml_http.send();
	}
	else {
		alert("Your browser does not support XMLHTTP.");
	}

}

function search_text_pre_search_serverResponse() {
	if (search_text_pre_search_xml_http.readyState == 4)// 4 = "loaded"
	{
		//debugOutput("server response.",0);
		if (search_text_pre_search_xml_http.status == 200) {// 200 = "OK"
			var serverText = search_text_pre_search_xml_http.responseText;
			if (window.DOMParser) {
				var parser = new DOMParser();
				var wordData = parser.parseFromString(serverText, "text/xml");
			}
			else { // Internet Explorer

				var wordData = new ActiveXObject("Microsoft.XMLDOM");
				wordData.async = "false";
				wordData.loadXML(serverText);
			}
			if (wordData) {
				var wordlist = wordData.getElementsByTagName("word")
				//var obj = JSON.parse(serverText);
				var search_text_word = "";
				for (var iword = 0; iword < wordlist.length; iword++) {
					search_text_word += "<li class=\"pali_book_item\" onclick='search_text_add_key_word(\"" + getNodeText(wordlist[iword], "pali") + "\"," + getNodeText(wordlist[iword], "count") + ",this)'>" + getNodeText(wordlist[iword], "pali") + "-" + getNodeText(wordlist[iword], "count") + "</li>"
				}
				search_text_result = document.getElementById("search_word_prev");
				if (search_text_result) {
					search_text_result.innerHTML = search_text_word;
				}
			}

		}
		else {
			//debugOutput(search_text_pre_search_xml_http.statusText,0);
		}
		search_text_pre_searching = false;
		var newword = document.getElementById("search_text_input").value;
		if (newword != search_text_pre_search_curr_word) {
			search_text_pre_search(newword);
		}
	}

}
function search_text_pre_word_click(word) {
	//document.getElementById("dict_ref_search_input").value=word;
	search_text_search(word);
}

function search_text_input_change(obj) {
	search_text_pre_search(obj.value);
}

function search_text_input_keypress(e, obj) {
	var keynum
	var keychar
	var numcheck

	if (window.event) // IE
	{
		keynum = e.keyCode
	}
	else if (e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which
	}
	var keychar = String.fromCharCode(keynum)
	if (keynum == 13) {

	}
}

function search_text_input_keyup(e, obj) {
	var keynum
	var keychar
	var numcheck

	if (window.event) // IE
	{
		keynum = e.keyCode
	}
	else if (e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which
	}
	var keychar = String.fromCharCode(keynum)
	if (keynum == 13) {
		search_text_search(obj.value);
	}
	else {
		search_text_pre_search(obj.value);
	}
}

function search_text_input_split(word) {
	if (word.indexOf("+") >= 0) {
		var wordParts = word.split("+");
		var strParts = "";
		for (var i in wordParts) {
			strParts += "<a onclick='dict_search(\"" + wordParts[i] + "\")'>" + wordParts[i] + "</a>";
		}
		document.getElementById("input_parts").innerHTML = strParts;
	}
	else {
		document.getElementById("input_parts").innerHTML = "";
	}

}

var search_text_key_word = new Array();
var index_count = 0;
function search_text_add_key_word(str, count, obj) {
	var objWord = new Object();
	objWord.pali = str;
	objWord.count = count;
	objWord.index = index_count;
	index_count++;
	obj.style = "max-height: 0px; padding: 0px; opacity: 0;";
	obj.id = "pali_book_item_hide_" + com_guid();
	objWord.idstr = obj.id;
	g_pali_word_item_hide_id.push(obj.id);
	//obj.style.display="none";
	search_text_key_word.push(objWord);
	search_text_refresh_key_word();

}

function search_text_remove_key_word(word_index, obj) {
	obj.style = "max-width: 0px; padding: 0px; opacity: 0;";
	for (var iword = 0; iword < search_text_key_word.length; iword++) {
		if (search_text_key_word[iword].index == word_index) {
			if (document.getElementById(search_text_key_word[iword].idstr) != null) {
				document.getElementById(search_text_key_word[iword].idstr).style = "";
			}
			search_text_key_word.splice(iword, 1);
			break;
		}

	}
	search_text_refresh_key_word();

}

function search_text_remove_all_key_word() {
	var search_text_array = document.getElementById("search_word_key_word").getElementsByClassName("pali_book_item");
	for (var i_search = 0; i_search < search_text_array.length; i_search++) {
		search_text_array[i_search].style = "max-width: 0px; padding: 0px; opacity: 0;"
	}
	for (var iword = 0; iword < search_text_key_word.length; iword++) {
		if (document.getElementById(search_text_key_word[iword] != null)) {
			document.getElementById(search_text_key_word[iword].idstr).style = "";

		}

	}

	search_text_key_word = new Array();
	search_text_refresh_key_word();
	//var pali_book_item_hide_array=document.getElementsByClassName("pali_book_item_hide");
	//for(i_pali_book_item_hide in pali_book_item_hide_array){
	//	pali_book_item_hide_array[i_pali_book_item_hide].className="pali_book_item";
	//}
}

function search_text_refresh_key_word() {
	var html_key_list = "";
	for (var iword = 0; iword < search_text_key_word.length; iword++) {
		html_key_list += "<li class=\"pali_book_item\" onclick='search_text_remove_key_word(\"" + search_text_key_word[iword].index + "\",this)'>" + (search_text_key_word[iword].pali) + "-" + (search_text_key_word[iword].count) + "</li>"
	}
	var search_text_ctl_key_word = document.getElementById("search_word_key_word");
	if (search_text_ctl_key_word) {
		search_text_ctl_key_word.innerHTML = html_key_list;
	}
}

function search_text_advance_search() {
	var key_word = "";
	for (var iword = 0; iword < search_text_key_word.length; iword++) {
		key_word += search_text_key_word[iword].pali + ",";
	}
	search_text_search(key_word);
}
function path_name_upgrade() {
	var path_array = document.getElementsByClassName('book_path');
	for (var i_path = 0; i_path < path_array.length; i_path++) {
		var path_str = path_array[i_path].innerHTML.split('#');
		for (j_path in local_palicannon_index) {
			if (local_palicannon_index[j_path].id == path_str[0]) {
				path_str[0] = local_palicannon_index[j_path].c1 + ">";
				path_str[0] += local_palicannon_index[j_path].c2 + ">";
				if (local_palicannon_index[j_path].c3 != "") {
					path_str[0] += local_palicannon_index[j_path].c3 + ">";
					if (local_palicannon_index[j_path].c4 != "") {
						path_str[0] += local_palicannon_index[j_path].c4 + ">";
					}
				}
				path_str[0] += "《" + local_palicannon_index[j_path].title + "》";
			}
		}
		path_array[i_path].innerHTML = path_str[0] + path_str[1];
	}
}

function search_edit(bookid, par) {
	var newRes = new Object()
	newRes.res = "wbw";
	newRes.book = bookid;
	newRes.parNum = par;
	newRes.parEnd = par;
	newRes.language = "pali";
	newRes.author = "templet";
	newRes.editor = "pcds";
	newRes.revision = 1;
	newRes.edition = 1;
	newRes.subver = 1;
	var arrRes = new Array();
	arrRes.push(newRes);
	window.open("./project.php?op=create&data=" + JSON.stringify(arrRes));
}