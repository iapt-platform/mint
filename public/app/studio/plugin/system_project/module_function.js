

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
/*
Modle:
    Name:editor_project
*/
var currResObj = null
var currResIndex = -1
/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function editor_project_init() {

}

function editor_project_wbw_export() {
	var allText = "";
	var resId = document.getElementById("id_wbw_export_list").value;
	var resList = lstResWbw[resId]
	for (var iBlock = 0; iBlock < resList.element.length; iBlock++) {
		currBlock = resList.element[iBlock]
		xmlParInfo = currBlock.getElementsByTagName("info")[0];
		xmlParData = currBlock.getElementsByTagName("data")[0];
		{
			wbwTextNode = xmlParData.getElementsByTagName("word");
			for (var iText = 0; iText < wbwTextNode.length; iText++) {
				if (getNodeText(wbwTextNode[iText], "type") != ".ctl.") {
					allText += getNodeText(wbwTextNode[iText], "pali") + " ";
				}
			}
			allText += "\r\n"
		}
	}
	document.getElementById("id_project_wbw_export_text").value = allText;
}


function editor_project_translate_export() {
	var allText = "";
	var resId = document.getElementById("id_translate_export_list").value;
	var resList = lstResTranslate[resId]
	for (var iBlock = 0; iBlock < resList.element.length; iBlock++) {
		currBlock = resList.element[iBlock]
		xmlParInfo = currBlock.getElementsByTagName("info")[0];
		xmlParData = currBlock.getElementsByTagName("data")[0];
		{
			tranTextNode = xmlParData.getElementsByTagName("sen");
			for (var iText = 0; iText < tranTextNode.length; iText++) {
				allText += getNodeText(tranTextNode[iText], "text") + "\r\n";
			}
		}
	}
	document.getElementById("id_project_translate_export_text").value = allText;
}

function editor_project_note_export() {
	var allText = "";
	var resId = document.getElementById("id_note_export_list").value;
	var resList = lstResNote[resId]
	for (var iBlock = 0; iBlock < resList.element.length; iBlock++) {
		currBlock = resList.element[iBlock]
		xmlParInfo = currBlock.getElementsByTagName("info")[0];
		xmlParData = currBlock.getElementsByTagName("data")[0];
		{
			tranTextNode = xmlParData.getElementsByTagName("sen");
			for (var iText = 0; iText < tranTextNode.length; iText++) {
				allText += getNodeText(tranTextNode[iText], "text") + "\r\n";
			}
		}
	}
	document.getElementById("id_project_note_export_text").value = allText;
}

//添加新的译文
function editor_project_translate_addnew(bWithText) {
	var strXml = "";
	strXml += "<pkg>"
	if (bWithText) {
		var strSen = document.getElementById("id_project_translate_import_text").value.split("\n");
	}
	let newTranslateLanguage = document.getElementById("id_project_translate_new_language").value;
	let newTranslateAuthor = document.getElementById("id_project_translate_new_author").value;
	let newTranslateEditor = document.getElementById("id_project_translate_new_editor").value;
	let newTranslateTag = document.getElementById("id_project_translate_new_tag").value;

	var album_guid = new Array();
	for (var iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		BookId = gArrayDocParagraph[iPar].book;
		album_guid[BookId.toString()] = com_uuid();
	}
	for (let iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		let BookId = gArrayDocParagraph[iPar].book;
		let ParId = gArrayDocParagraph[iPar].paragraph;
		let iLevel = gArrayDocParagraph[iPar].level;
		let blockId = com_uuid();
		strXml += "<block>";
		strXml += "<info>";
		strXml += "<album_id>-1</album_id>";
		strXml += "<album_guid>" + album_guid[BookId.toString()] + "</album_guid>";
		strXml += "<id>" + blockId + "</id>";
		strXml += "<book>" + BookId + "</book>";
		strXml += "<paragraph>" + ParId + "</paragraph>";
		strXml += "<level>" + iLevel + "</level>";
		strXml += "<type>translate</type>";
		strXml += "<tag>" + newTranslateTag + "</tag>";
		strXml += "<author>" + newTranslateAuthor + "</author>";
		strXml += "<editor>" + newTranslateEditor + "</editor>";
		strXml += "<version>1</version>";
		strXml += "<edition>第一版</edition>";
		strXml += "<language>" + newTranslateLanguage + "</language>";
		strXml += "</info>";

		usent_block_create(blockId, BookId, ParId, newTranslateLanguage, newTranslateAuthor, newTranslateEditor, newTranslateTag);
		fileinfo_add_block(doc_head("doc_id"), 2, blockId);

		if (bWithText && iPar < strSen.length) {
			strXml += "<data><sen><begin></begin><end></end><text>" + strSen[iPar] + "</text></sen></data>"
		}
		else {
			strXml += "<data>";
			var xBlock = gXmlBookDataBody.getElementsByTagName("block");
			for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
				xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
				xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
				let mId = getNodeText(xmlParInfo, "id");
				let book = getNodeText(xmlParInfo, "book");
				let paragraph = getNodeText(xmlParInfo, "paragraph");
				let type = getNodeText(xmlParInfo, "type");
				if (BookId == book && ParId == paragraph && type == "wbw") {
					var xWord = xBlock[iBlock].getElementsByTagName("word");
					if (xWord.length > 0) {
						var ibegin = getNodeText(xWord[0], "id").split("-")[2];
						var iend = 0;
						for (var iWord = 0; iWord < xWord.length; iWord++) {
							var wEnter = getNodeText(xWord[iWord], "enter");
							if (wEnter == 1) {
								iend = getNodeText(xWord[iWord], "id").split("-")[2];
								let sentId = com_uuid();
								strXml += "<sen>";
								strXml += "<id>" + sentId + "</id>";
								strXml += "<begin>" + ibegin + "</begin>";
								strXml += "<end>" + iend + "</end>";
								strXml += "<text></text>";
								strXml += "<status>1</status>";
								strXml += "</sen>";
								usent_create(blockId, sentId, book, paragraph, ibegin, iend, "", newTranslateTag, newTranslateLanguage, newTranslateAuthor, newTranslateEditor);
								iend++;
								ibegin = iend;
							}
						}
						iend = getNodeText(xWord[xWord.length - 1], "id").split("-")[2];
						let sentId = com_uuid();
						strXml += "<sen>";
						strXml += "<id>" + sentId + "</id>";
						strXml += "<begin>" + ibegin + "</begin>";
						strXml += "<end>" + iend + "</end>";
						strXml += "<text></text>";
						strXml += "<status>1</status>";
						strXml += "</sen>";
						usent_create(blockId, sentId, book, paragraph, ibegin, iend, "", newTranslateTag, newTranslateLanguage, newTranslateAuthor, newTranslateEditor);
					}
				}
			}
			strXml += "</data>";
		}
		strXml += "</block>"
	}
	strXml += "</pkg>";

	usent_block_commit();
	usent_commit();
	fileinfo_add_block_commit(g_docid);

	if (window.DOMParser) {
		parser = new DOMParser();
		newXmlData = parser.parseFromString(strXml, "text/xml");
	}
	else // Internet Explorer
	{
		newXmlData = new ActiveXObject("Microsoft.XMLDOM");
		newXmlData.async = "false";
		newXmlData.loadXML(strXml);
	}

	if (newXmlData == null) {
		alert("error:can not load book index.");
		return;
	}

	xmlParBlocks = newXmlData.getElementsByTagName("block");
	for (iXml = 0; iXml < xmlParBlocks.length; iXml++) {
		insertBlockToHtml(xmlParBlocks[iXml])
		insertBlockToXmlBookData(xmlParBlocks[iXml])
	}

	refreshResource();
}

//添加新的注解
function editor_project_note_addnew(bWithText) {
	var strXml = "";
	strXml += "<pkg>"

	if (bWithText) {
		var strSen = document.getElementById("id_project_note_import_text").value.split("\n");
	}

	newNoteLanguage = document.getElementById("id_project_note_new_language").value;
	newNoteAuthor = document.getElementById("id_project_note_new_author").value;

	xmlBookToc = gXmlBookDataHeadToc.getElementsByTagName("paragraph");
	for (var iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		BookId = gArrayDocParagraph[iPar].book
		ParId = gArrayDocParagraph[iPar].paragraph
		strXml += "<block>"
		strXml += "<info><id>" + com_guid() + "</id><book>" + BookId + "</book><paragraph>" + ParId + "</paragraph><type>note</type><author>" + newNoteAuthor + "</author><edition>1</edition><subver>0</subver><language>" + newNoteLanguage + "</language></info>";
		if (bWithText && iPar < strSen.length) {
			strXml += "<data><sen><a></a><text>" + strSen[iPar] + "</text></sen></data>"
		}
		else {
			strXml += "<data><sen><a></a><text>" + gui_string_editor_project[1] + "</text></sen></data>"
		}
		strXml += "</block>"
	}
	strXml += "</pkg>"
	if (window.DOMParser) {
		parser = new DOMParser();
		newXmlData = parser.parseFromString(strXml, "text/xml");
	}
	else // Internet Explorer
	{
		newXmlData = new ActiveXObject("Microsoft.XMLDOM");
		newXmlData.async = "false";
		newXmlData.loadXML(strXml);
	}

	if (newXmlData == null) {
		alert("error:can not load book index.");
		return;
	}

	xmlParBlocks = newXmlData.getElementsByTagName("block");
	for (iXml = 0; iXml < xmlParBlocks.length; iXml++) {
		insertBlockToHtml(xmlParBlocks[iXml])
		insertBlockToXmlBookData(xmlParBlocks[iXml])
	}
	refreshResource();

}

/*refresh resource list
*/
function refreshResource() {
	lstResTranslate = new Array();
	lstResNote = new Array();
	lstResWbw = new Array();
	lstResHeading = new Array();
	lstResIld = new Array();

	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		var xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		var xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		var bookId = getNodeText(xmlParInfo, "book");
		var paragraph = getNodeText(xmlParInfo, "paragraph");
		var type = getNodeText(xmlParInfo, "type");
		newRes = new Object();
		newRes.type = type;
		newRes.book = getNodeText(xmlParInfo, "book");
		newRes.paragraph = getNodeText(xmlParInfo, "paragraph");
		newRes.album_id = getNodeText(xmlParInfo, "album_id");
		newRes.album_guid = getNodeText(xmlParInfo, "album_guid");
		newRes.album_author = getNodeText(xmlParInfo, "album_author");
		newRes.author = getNodeText(xmlParInfo, "author");
		newRes.editor = getNodeText(xmlParInfo, "editor");
		newRes.revision = getNodeText(xmlParInfo, "revision");
		newRes.edition = getNodeText(xmlParInfo, "edition");
		newRes.subver = getNodeText(xmlParInfo, "subver");
		newRes.language = getNodeText(xmlParInfo, "language");
		newRes.count = 1
		newRes.element = new Array(xBlock[iBlock]);
		switch (type) {
			case "wbw":
				addItemToResList(newRes, lstResWbw)
				break;
			case "translate":
				addItemToResList(newRes, lstResTranslate)
				break;
			case "note":
				addItemToResList(newRes, lstResNote)
				break;
			case "heading":
				addItemToResList(newRes, lstResHeading)
				break;
		}
	}
	var wbwListStr = "";
	var wbwListStr2 = "";
	var wbw_count = 0;
	for (i = 0; i < lstResWbw.length; i++) {
		if (i > 0 && lstResWbw[i].author != lstResWbw[i - 1].author && lstResWbw[i].language != lstResWbw[i - 1].language) {
			wbwListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('wbw'," + (i - 1) + ")\">" + lstResWbw[i - 1].author + "[" + lstResWbw[i - 1].language + "]-" + wbw_count + "</a></li>"
			tran_count = 1;
			wbwListStr2 += "<option value=\"" + (i - 1) + "\">" + info2 + "</option>"
		}
		else if (i == lstResWbw.length - 1 && i != 0) {
			wbwListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('wbw'," + (i - 1) + ")\">" + lstResWbw[i - 1].author + "[" + lstResWbw[i - 1].language + "]-" + wbw_count + "</a></li>"
			wbwListStr2 += "<option value=\"" + (i - 1) + "\">" + info2 + "</option>"

		}
		else {
			wbw_count += lstResWbw[i].count;
		}

		var info1 = lstResWbw[i].author + "," + lstResWbw[i].language
		var info2 = lstResWbw[i].author + "[" + lstResWbw[i].language + "]"
	}

	var tranListStr = "";
	var tranListStr2 = ""
	var tran_count = 0;
	for (i = 0; i < lstResTranslate.length; i++) {
		if (i > 0 && lstResTranslate[i].author != lstResTranslate[i - 1].author && lstResTranslate[i].language != lstResTranslate[i - 1].language) {
			tranListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('translate'," + (i - 1) + ")\">" + lstResTranslate[i - 1].author + "[" + lstResTranslate[i - 1].language + "]-" + tran_count + "</a></li>"
			tran_count = 1;
			tranListStr2 += "<option value=\"" + (i - 1) + "\">" + info2 + "</option>"
		}
		else if (i == lstResTranslate.length - 1 && i != 0) {
			tranListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('translate'," + (i - 1) + ")\">" + lstResTranslate[i - 1].author + "[" + lstResTranslate[i - 1].language + "]-" + tran_count + "</a></li>"
			tranListStr2 += "<option value=\"" + (i - 1) + "\">" + info2 + "</option>"

		}
		else {
			tran_count += lstResTranslate[i].count;
		}
		var info1 = lstResTranslate[i].author + "," + lstResTranslate[i].language
		var info2 = lstResTranslate[i].author + "[" + lstResTranslate[i].language + "]"
	}
	var noteListStr = "";
	var noteListStr2 = "";
	for (i = 0; i < lstResNote.length; i++) {
		noteListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('note'," + i + ")\">" + lstResNote[i].author + "[" + lstResNote[i].language + "]-" + lstResNote[i].count + "</a></li>"
		var info1 = lstResNote[i].author + "," + lstResNote[i].language
		var info2 = lstResNote[i].author + "[" + lstResNote[i].language + "]"
		noteListStr2 += "<option value=\"" + i + "\">" + info2 + "</option>"
	}

	var headingListStr = "";
	for (i = 0; i < lstResHeading.length; i++) {
		headingListStr += "<li><input type=\"checkbox\" checked /><a onclick=\"project_res_info_click('heading'," + i + ")\">" + lstResHeading[i].author + "[" + lstResHeading[i].language + "]-" + lstResHeading[i].count + "</a></li>"
	}
	var iCountIld = g_DictWordList.length;
	var iCountIldXML = gXmlBookDataInlineDict.getElementsByTagName("word").length;
	var ildListStr = "<li>Inline Dict XML(" + iCountIldXML + ")</li><li>Inline Dict Array(" + iCountIld + ")</li>";

	document.getElementById("id_editor_project_res_wbw_inner").innerHTML = wbwListStr;
	document.getElementById("id_editor_project_res_translate_inner").innerHTML = tranListStr;
	document.getElementById("id_editor_project_res_note_inner").innerHTML = noteListStr;
	document.getElementById("id_editor_project_res_heading_inner").innerHTML = headingListStr;
	document.getElementById("id_editor_project_res_ild_inner").innerHTML = ildListStr;
	document.getElementById("id_translate_export_list").innerHTML = tranListStr2;
	document.getElementById("id_note_export_list").innerHTML = noteListStr2;
	document.getElementById("id_wbw_export_list").innerHTML = wbwListStr2;

}

function project_res_ild_remove() {
	var_dump(removeAllInlinDictItem() + " recoder removed");
	refreshResource();
}

function project_res_info_click(type, index) {

	$("#project_res_album_info").show();
	$("#project_res_album_info").siblings().hide();

	currResIndex = index;
	switch (type) {
		case "wbw":
			currResObj = lstResWbw;
			project_show_album_info(currResObj[index].album_id, currResObj[index].book, type);
			break;
		case "translate":
			currResObj = lstResTranslate;
			project_show_album_info(currResObj[index].album_id, currResObj[index].book, type);
			break;
		case "note":
			currResObj = lstResNote
			break;
		case "heading":
			currResObj = lstResHeading
			break;
		default:
			currResObj = null;
			break;
	}
	document.getElementById("id_project_res_info_language").value = currResObj[index].language;
	document.getElementById("id_project_res_info_author").value = currResObj[index].author;
	document.getElementById("id_project_res_info_editor").value = currResObj[index].editor;
	$("#id_project_res_info_edition").val(currResObj[index].edition);
	$("#project_res_info_title").html(currResObj[index].author);
}

var g_new_album_guid = "";
function project_show_album_info(album_id, book, type) {
	$.get("./album.php",
		{
			op: "get_album",
			book: book,
			type: type,
			album_id: album_id
		},
		function (data, status) {
			var album_data = JSON.parse(data);
			var html = "";
			if (album_data.length > 0) {
				var bFound = false;
				for (var i = 0; i < album_data.length; i++) {
					if (album_data[i].id == currResObj[currResIndex].album_id) {
						var alink = "./album.php?op=show_info&album_id=" + album_data[i].id;
						html = "专辑名称：<a href='" + alink + "' target='_blank'>《" + album_data[i].title + "》</a><button>删除</button>";
						bFound = true;
						break;
						//$("#project_album_id").html(html);	
						//return;
					}
				}
				if (!bFound) {
					html = "专辑名称：无";
				}
				var html_album_list = "";
				//标记文档中已经被使用的
				for (var i = 0; i < album_data.length; i++) {
					album_data[i].used = false;
					for (var j = 0; j < currResObj.length; j++) {
						if (album_data[i].id == currResObj[j].album_id) {
							album_data[i].used = true;
						}
					}
				}
				for (var i = 0; i < album_data.length; i++) {
					if (album_data[i].used == false) {
						var alink = "./album.php?op=show_info&album_id=" + album_data[i].id;
						var album_id = album_data[i].id;
						var album_guid = album_data[i].guid;
						html_album_list += "<li><a href='" + alink + "' target='_blank'>《" + album_data[i].title + "》</a>" + album_data[i].author + album_data[i].language + " <button onclick=\"project_apply_album('" + album_id + "','" + album_guid + "')\">使用此专辑发布</button></li>";
					}
				}
				if (html_album_list != "") {
					html += " <br />(可以选择下列已有的专辑)";
					html += "<ul>";
					html += html_album_list;
					html += "</ul>";
				}
				g_new_album_guid = com_guid();
				html += "<button onclick='project_new_album_show()'>新建专辑</button>";
				html += "<div id='project_new_album' style='display:none;'>";
				html += "<input type='hidden' id='new_album_album_guid' value='" + g_new_album_guid + "'/>";
				html += "<input type='hidden' id='new_album_album_type' value='" + currResObj[currResIndex].type + "'/>";
				html += "<input type='hidden' id='new_album_book' value='" + currResObj[currResIndex].book + "'/>";
				html += "作者(必填):<input type='input' id='new_album_author' value='" + currResObj[currResIndex].author + "'/><br>";
				html += "专辑名称(必填):<input type='input' id='new_album_title' value=''/><br>";
				html += "语言:" + currResObj[currResIndex].language + "<input type='hidden' id='new_album_lang' value='" + currResObj[currResIndex].language + "'/><br>";
				html += "Tag:<input type='input' id='new_album_tag' value=''/><br>";
				html += "Summary:<input type='input' id='new_album_summary' value=''/><br>";
				html += "Edition:<input type='input' id='new_album_edition' placeholder=\"第一版\" value=''/><br>";
				html += "<input type=\"button\" value='完成' onclick='project_new_album_submit()' />";
				html += "</div>";


			}
			else {
				var html = "专辑名称：无";
				g_new_album_guid = com_guid();
				html += "<button onclick='project_new_album_show()'>新建专辑</button>";
				html += "<div id='project_new_album' style='display:none;'>";
				html += "<input type='hidden' id='new_album_album_guid' value='" + g_new_album_guid + "'/>";
				html += "<input type='hidden' id='new_album_album_type' value='" + currResObj[currResIndex].type + "'/>";
				html += "<input type='hidden' id='new_album_book' value='" + currResObj[currResIndex].book + "'/>";
				html += "作者(必填):<input type='input' id='new_album_author' value='" + currResObj[currResIndex].author + "'/><br>";
				html += "专辑名称(必填):<input type='input' id='new_album_title' value=''/><br>";
				html += "语言:" + currResObj[currResIndex].language + "<input type='hidden' id='new_album_lang' value='" + currResObj[currResIndex].language + "'/><br>";
				html += "Tag:<input type='input' id='new_album_tag' value=''/><br>";
				html += "Summary:<input type='input' id='new_album_summary' value=''/><br>";
				html += "Edition:<input type='input' id='new_album_edition' placeholder=\"第一版\" value=''/><br>";
				html += "<input type=\"button\" value='完成' onclick='project_new_album_submit()' />";
				html += "</div>";

			}
			html += "<div id='project_new_album_server_response'></div>"
			$("#project_album_id").html(html);
		});

}

function project_apply_album(album_id, album_guid) {
	//应用新的专辑号到文档数据块
	var count = 0;
	var xBlock = currResObj[currResIndex].element;
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		var xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		setNodeText(xmlParInfo, "album_guid", album_guid);
		setNodeText(xmlParInfo, "album_id", album_id);
		count++;
	}
	currResObj[currResIndex].album_id = album_id;
	currResObj[currResIndex].album_guid = album_guid;
	alert("应用新的专辑到" + count + "段落");
	project_res_info_click(currResObj[currResIndex].type, currResIndex);
}
function project_new_album_show() {
	$("#project_new_album").show(200);
}
function project_new_album_submit() {
	var album_guid = $("#new_album_album_guid").val();
	var album_type = $("#new_album_album_type").val();
	var book = $("#new_album_book").val();
	var author = $("#new_album_author").val();
	var title = $("#new_album_title").val();
	var lang = $("#new_album_lang").val();
	var tag = $("#new_album_tag").val();
	var summary = $("#new_album_summary").val();
	var edition = $("#new_album_edition").val();
	if (author == "") {
		alert("作者不能为空");
		return;
	}
	if (title == "") {
		alert("标题不能为空");
		return;
	}
	$.get("./album.php",
		{
			op: "new",
			album_guid: album_guid,
			album_type: album_type,
			book: book,
			lang: lang,
			tag: tag,
			summary: summary,
			author: author,
			edition: edition,
			title: title
		},
		function (data, status) {
			$("#project_new_album_server_response").html(data);
			if (data.substring(0, 5) != "error") {
				//新建专辑成功 传回新建的album id
				alert("新建专辑成功");
				//应用新的专辑号到文档数据块
				var old_album_guid = currResObj[currResIndex].album_guid;

				var count = 0;
				var xBlock = currResObj[currResIndex].element;
				for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
					var xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
					setNodeText(xmlParInfo, "album_guid", g_new_album_guid);
					setNodeText(xmlParInfo, "album_id", data);
					count++;
				}
				currResObj[currResIndex].album_id = data;
				currResObj[currResIndex].album_guid = g_new_album_guid;
				alert("应用新的专辑到" + count + "段落");
				project_res_info_click(currResObj[currResIndex].type, currResIndex);
			}
			else {
				$("#project_new_album_server_response").html(data);
			}
		});
}

function addItemToResList(obj, resList) {
	var isFind = false
	for (i = 0; i < resList.length; i++) {
		if (resList[i].album_guid == obj.album_guid) {
			isFind = true;
			resList[i].count++;
			resList[i].element.push(obj.element[0])
			return;
		}
	}
	resList.push(obj);
}

function editor_project_publish() {
	xmlHttp = null;
	if (window.XMLHttpRequest) {// code for IE7, Firefox, Opera, etc.
		xmlHttp = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {// code for IE6, IE5
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (xmlHttp != null) {
		xmlHttp.open("POST", "./pc_publish.php", false);
		xmlHttp.send(com_xmlToString(gXmlBookData));
		var_dump(xmlHttp.responseText);
	}
	else {
		var_dump("Your browser does not support XMLHTTP.");
	}
}

function editor_project_res_info_modify() {
	xBlocks = currResObj[currResIndex].element
	for (x in xBlocks) {
		xmlParInfo = xBlocks[x].getElementsByTagName("info")[0];
		setNodeText(xmlParInfo, "language", document.getElementById("id_project_res_info_language").value);
		setNodeText(xmlParInfo, "author", document.getElementById("id_project_res_info_author").value);
		setNodeText(xmlParInfo, "editor", document.getElementById("id_project_res_info_editor").value);
		setNodeText(xmlParInfo, "edition", document.getElementById("id_project_res_info_edition").value);
	}
	refreshResource()
}

function editor_project_res_remove() {
	var r = confirm("Remove a resource!");
	if (r == true) {
		xBlocks = currResObj[currResIndex].element

		for (x in xBlocks) {

			xmlParInfo = xBlocks[x].getElementsByTagName("info")[0];
			blockid = getNodeText(xmlParInfo, "id");
			type = getNodeText(xmlParInfo, "type");
			var htmlDivId = "";
			switch (type) {
				case "wbw":
					htmlDivId = "id_wbw_" + blockid;
					break;
				case "translate":
					htmlDivId = "id_tran_" + blockid;
					break;
				case "note":
					htmlDivId = "id_note_" + blockid;
					break;
				case "heading":
					htmlDivId = "id_heading_" + blockid;
					break;
			}
			xHtmlDom = document.getElementById(htmlDivId);
			if (xHtmlDom) {
				xHtmlDom.parentNode.removeChild(xHtmlDom);
			}
			else {
				//alert("错误的数据包id-"+blockid);
			}

			xBlocks[x].parentNode.removeChild(xBlocks[x]);
		}
		refreshResource()
		alert("Removed OK! \nPlease save and open project again.");

	}

}

function editor_project_res_publish() {
	var xBlocks = currResObj[currResIndex].element;
	if (xBlocks.length) {
		var xmlParInfo = xBlocks[0].getElementsByTagName("info")[0];
		var album_id = getNodeText(xmlParInfo, "album_id");
		var album_guid = getNodeText(xmlParInfo, "album_guid");
		var album_type = getNodeText(xmlParInfo, "type");
		var album_lang = getNodeText(xmlParInfo, "language");
		var album_author = getNodeText(xmlParInfo, "author");
		var album_title = getNodeText(xmlParInfo, "album_title");
		var book = getNodeText(xmlParInfo, "book");

		window.open("./publish.php?step=1&id=" + album_id + "&filename=" + g_filename + "&type=" + album_type + "&book=" + book + "&lang=" + album_lang + "&author=" + album_author + "&title=" + album_title, "_blank");

	}
}

function project_res_type_click(sType) {
	var html = "";
	switch (sType) {
		case "doc":
			$("#id_editor_project_res_docinfo").show();
			$("#id_editor_project_res_docinfo").siblings().hide();
			break;
		case "wbw":
			$("#id_editor_project_res_wbw").show();
			$("#id_editor_project_res_wbw").siblings().hide();
			break;
		case "tran":
			$("#id_editor_project_res_translate").show();
			$("#id_editor_project_res_translate").siblings().hide();
			break;
		case "note":
			$("#id_editor_project_res_note").show();
			$("#id_editor_project_res_note").siblings().hide();
			break;

	}
}
