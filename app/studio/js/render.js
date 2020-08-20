var gDisplayCapacity = 20
var gCurrTopParagraph = 0;
var gVisibleParBegin = 0;
var gVisibleParEnd = 10;

//显示模式
var _display_para_arrange = 0;//0:横向 排列 1:纵向排列
var _display_sbs = 0; //0:逐段  1:逐句

//翻译块显示模式
//在编辑状态下显示预览
var _tran_show_preview_on_edit = true;
//在非编辑状态下显示编辑框
var _tran_show_textarea_esc_edit = true;

var gVisibleParBeginOld = 0;
var gVisibleParEndOld = gDisplayCapacity;
//var gPalitext_length=0
var gtext_max_length = 0
var g_allparlen_array = new Array;
function palitext_calculator() {
	var allText = "";
	var allTextLen_array = new Array;
	var text_max2_length = 0;
	allBlock = gXmlBookDataBody.getElementsByTagName("block")
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		//bookId=getNodeText(xmlParInfo,"book")
		//paragraph=getNodeText(xmlParInfo,"paragraph")
		type = getNodeText(xmlParInfo, "type")
		if (type == "wbw") {
			var wbwTextNode = xmlParData.getElementsByTagName("word");
			var para_text_cal = ""
			for (var iText = 0; iText < wbwTextNode.length; iText++) {
				if (getNodeText(wbwTextNode[iText], "type") != ".ctl.") {
					para_text_cal += getNodeText(wbwTextNode[iText], "pali") + " ";
				}
			}
			allText += para_text_cal
			allTextLen_array.push(allText.length);
			//gPalitext_length+=para_text_cal.length
			if (para_text_cal.length > text_max2_length) {
				text_max2_length = para_text_cal.length
				if (text_max2_length > gtext_max_length) {
					var num_max_tem = gtext_max_length
					gtext_max_length = text_max2_length
					text_max2_length = num_max_tem
				}
			}
		}
	}
	for (i_cal in allTextLen_array) {
		g_allparlen_array.push(allTextLen_array[i_cal] / gtext_max_length);
	}
	gDisplayCapacity = 19 + text_max2_length / gtext_max_length
	if (gDisplayCapacity * gtext_max_length < 5000) {
		gDisplayCapacity = 5000 / gtext_max_length;
	}
}
//添加新的段落块
function addNewBlockToHTML(bookId, parId) {
	parHeadingLevel = 0;
	parTitle = "";
	var divBlock = document.createElement("div");
	var typId = document.createAttribute("id");
	typId.nodeValue = "par_" + bookId + "_" + (parId - 1);
	divBlock.attributes.setNamedItem(typId);
	var typClass = document.createAttribute("class");
	typClass.nodeValue = "pardiv";
	divBlock.attributes.setNamedItem(typClass);

	var output = "<a name=\"par_begin_" + bookId + "_" + (parId - 1) + "\"></a>";
	output += "	<div id=\"head_tool_" + bookId + "_" + (parId - 1) + "\" class=\"head_tool edit_tool\">"
	output += "	<button id=\"id_heading_add_new\" onclick=\"editor_heading_add_new('" + bookId + "','" + parId + "')\" >" + gui_string_editor[0] + "</button>"
	output += "<input id='page_break_'" + bookId + "_" + (parId - 1) + " type=\"checkbox\" onclick=\"editor_page_break(this,'" + bookId + "','" + (parId - 1) + "')\" />" + gui_string_editor[1]
	output += "</div>"

	//heading
	output += "	<div id=\"heading_" + bookId + "_" + (parId - 1) + "\" class=\"par_heading\"></div>"

	//word by word translate block
	//内部可以包含多个wbw块
	output += "	<div id=\"wblock_" + bookId + "_" + (parId - 1) + "\" class=\"wbwdiv\"></div>"

	//translate div
	output += "	<div id=\"tran_" + bookId + "_" + (parId - 1) + "\" class=\"trandiv\"></div>"

	//word note div
	output += "	<div id=\"wnote_" + bookId + "_" + (parId - 1) + "\" class=\"wnotediv\"></div>"

	//note div
	output += "	<div id=\"note_" + bookId + "_" + (parId - 1) + "\" class=\"pnotediv\"></div>"

	//vedio div
	output += "	<div id=\"vedioblock_" + bookId + "_" + (parId - 1) + "\" class=\"vediodiv\"></div>"

	divBlock.innerHTML = output;
	document.getElementById("sutta_text").appendChild(divBlock);
}

function renderToc(bookId, parBegin, parEnd) {
	var firstHeadingLevel = getNodeText(gXmlParIndex[parBegin - 1], "level");

	var output = "<ul>";
	for (var iPar = parBegin; iPar < parEnd; iPar++) {
		parTitle = getNodeText(gXmlParIndex[iPar - 1], "title");
		parHeadingLevel = getNodeText(gXmlParIndex[iPar - 1], "level");
		if (parHeadingLevel > 0) {
			output += "<li class=\"toc_h_" + (parHeadingLevel - firstHeadingLevel + 1) + "\">";
			output += "<a href=\"#par-" + bookId + "-" + iPar + "\">" + parTitle + "</a>";
			output += "</li>"
		}
	}
	output += "</ul>";
	return output;
}

function updataWordParContainer(bookId, parId) {
	document.getElementById("wblock-" + bookId + "-" + parId + "\"").innerHTML = renderWordParContainerInner(bookId, parId);
}

function renderWordParContainerInner(bookId, parId) {
	var strHtml = "";
	strHtml += "<div class=\"wbwparblock\">"
	strHtml += renderWordParBlockInner(packageIndex);
	strHtml += "</div>";
}

//根据block数据更新 目录 列表
function updataToc() {
	document.getElementById("content").innerHTML = "";
	//创建目录空壳
	for (let iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		if (gArrayDocParagraph[iPar].level > 0) {
			let bookId = gArrayDocParagraph[iPar].book
			let parIndex = gArrayDocParagraph[iPar].paragraph
			let tocId = "toc_" + bookId + "_" + (parIndex - 1)
			let str = "<div class=\"toc_heading\">"
			str += "<table><tr><td>"
			str += "<input type='checkbox' checked onclick=\"editor_par_show(this,'" + bookId + "','" + parIndex + "')\" />"
			str += "</td><td>"
			str += "<div class=\"toc_heading_inner\" id=\"" + tocId + "\">";
			str += "<a onclick=\"editor_goto_link('" + bookId + "'," + parIndex + ")\" >[" + parIndex + "]</a>";
			str += "</div>"
			str += "</td></tr></table>"
			str += "</div>"
			document.getElementById("content").innerHTML += str
		}
	}

	allBlock = gXmlBookDataBody.getElementsByTagName("block")
	for (let iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book")
		paragraph = getNodeText(xmlParInfo, "paragraph")
		type = getNodeText(xmlParInfo, "type")
		if (type == "heading") {
			level = getNodeText(xmlParInfo, "level")
			//if(level>0)
			{
				language = getNodeText(xmlParInfo, "language")
				bId = getNodeText(xmlParInfo, "id")
				strHeadingText = getNodeText(xmlParData, "text")
				tocId = "toc_" + bookId + "_" + (paragraph - 1);
				/*
				var htmlBlock=document.getElementById(tocId)
				if(htmlBlock==null){
					var str="<div class=\"toc_heading\">";
					str += "<table><tr><td>";
					str += "<input type='checkbox' checked onclick=\"editor_par_show(this,'"+bookId+"','"+(paragraph-1)+"')\" />";
					str += "</td><td>";
					str += "<div class=\"toc_heading_inner\" id=\""+tocId+"\"></div>";
					str += "</td></tr></table>";
					str += "</div>";
					document.getElementById("content").innerHTML+=str
				}
				*/
				let tocText = "<p class=\"toc_item_" + level + " " + language + "_text\">";
				tocText += "<a onclick=\"editor_goto_link('" + bookId + "'," + paragraph + ")\" >[" + paragraph + "]-" + strHeadingText + "</a>";
				tocText += "</p>";

				$("#toc_" + bookId + "_" + (paragraph - 1)).html(tocText);

			}
		}
	}
	palitext_calculator();
}
//向html中插入数据块
function insertBlockToHtml(element) {
	xmlParInfo = element.getElementsByTagName("info")[0];
	xmlParData = element.getElementsByTagName("data")[0];

	bookId = getNodeText(xmlParInfo, "book")
	paragraph = getNodeText(xmlParInfo, "paragraph")
	type = getNodeText(xmlParInfo, "type")
	language = getNodeText(xmlParInfo, "language")
	bId = getNodeText(xmlParInfo, "id")

	blockId = "par_" + bookId + "_" + (paragraph - 1)
	var htmlBlock = document.getElementById(blockId)
	if (htmlBlock == null) {
		addNewBlockToHTML(bookId, paragraph);
	}

	if (!isParInView(getParIndex(bookId, paragraph))) {
		document.getElementById(blockId).style.display = "none";
		return;
	}
	else {
		document.getElementById(blockId).style.display = "inline-flex";
	}



	switch (type) {
		case "wbw":
			var strHtml = renderWordParBlockInner(element);
			$("#wnote_" + bookId + "_" + (paragraph - 1)).html(renderNoteShell(element));
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml
			var typ = document.createAttribute("class");
			typ.nodeValue = "wbwparblock";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_wbw_" + bId;
			paraDiv.attributes.setNamedItem(id);

			blockId = "wblock_" + bookId + "_" + (paragraph - 1)
			document.getElementById(blockId).appendChild(paraDiv);

			refreshWordNoteDiv(element);
			refreshNoteNumber();
			break;
		case "translate":
			var strHtml = "";
			strHtml = renderTranslateParBlockInner(element);
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml
			var typ = document.createAttribute("class");
			typ.nodeValue = "tran_parblock " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_tran_" + bId;
			paraDiv.attributes.setNamedItem(id);


			blockId = "tran_" + bookId + "_" + (paragraph - 1)
			document.getElementById(blockId).appendChild(paraDiv);
			//逐句
			if (_display_sbs == 1) {
				let eAllSent = document.getElementById(blockId).getElementsByClassName("tran_sent");
				for (let iSen = 0; iSen < eAllSent.length; iSen++) {
					let senA = eAllSent[iSen].getAttributeNode("sn").value;
					let blockId = eAllSent[iSen].getAttributeNode("block").value;
					let eSBSDiv = document.getElementById("sent_" + senA);
					if (eSBSDiv) {
						let eBlockSenDiv = document.getElementById("sent_" + senA + "_" + blockId);
						if (!eBlockSenDiv) {//没有 添加
							let divSen = document.createElement("div");
							let typ = document.createAttribute("class");
							typ.nodeValue = "sbs_sent_block";
							divSen.attributes.setNamedItem(typ);

							let typId = document.createAttribute("id");
							typId.nodeValue = "sent_" + senA + "_" + blockId;
							divSen.attributes.setNamedItem(typId);

							let sn = document.createAttribute("sn");
							sn.nodeValue = senA;
							divSen.attributes.setNamedItem(sn);

							let block = document.createAttribute("block");
							block.nodeValue = blockId;
							divSen.attributes.setNamedItem(block);

							divSen.innerHTML = eAllSent[iSen].innerHTML;
							eSBSDiv.appendChild(divSen);
						}
						else {
							eBlockSenDiv.innerHTML = eAllSent[iSen].innerHTML;

						}
						eAllSent[iSen].innerHTML = "";
					}
				}
			}
			break;
		case "note":
			var strHtml = "";
			strHtml = renderNoteParBlockInner(element);
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml
			var typ = document.createAttribute("class");
			typ.nodeValue = "note_parblock " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_note_" + bId;
			paraDiv.attributes.setNamedItem(id);


			blockId = "note_" + bookId + "_" + (paragraph - 1)
			document.getElementById(blockId).appendChild(paraDiv);
			document.getElementById(blockId).style.display = "block";
			break;
		case "heading":
			headingLevel = getNodeText(xmlParInfo, "level")
			var strHtml = "";
			strHtml = renderHeadingBlockInner(element);
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml
			var typ = document.createAttribute("class");
			typ.nodeValue = "heading_parblock_" + headingLevel + "_" + language + " " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_heading_" + bId;
			paraDiv.attributes.setNamedItem(id);


			blockId = "heading_" + bookId + "_" + (paragraph - 1)
			document.getElementById(blockId).appendChild(paraDiv);
			document.getElementById(blockId).style.display = "block";
			//document.getElementById("id_heading_level_"+bookId+"_"+(paragraph-1)).value=headingLevel;
			break;
	}

}

function updataHeadingBlockInHtml(book, par) {
	document.getElementById("heading_" + book + "_" + (par - 1)).innerHTML = "";
	allBlock = gXmlBookDataBody.getElementsByTagName("block")
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book")
		paragraph = getNodeText(xmlParInfo, "paragraph")
		type = getNodeText(xmlParInfo, "type")
		if (type == "heading" && bookId == book && paragraph == par) {
			insertBlockToHtml(allBlock[iBlock])
		}
	}
}

function renderBlock() {

}
/*
重绘翻译数据块
*/
function update_tran_sent(blockId, begin, end) {
	let eBlock = document.getElementById("id_tran_" + blockId);
	if (eBlock) {
		eBlock.innerHTML = renderTranslateParBlockInnerById(blockId);
		term_updata_translation();
	}
	/*
	$("#id_tran_"+blockId).html(renderTranslateParBlockInnerById(blockId));
	term_updata_translation();
*/
}

/*
更新翻译数据块数据
*/
function update_tran_block_text(blockId) {
	let block = doc_tran("#" + blockId);
	if (block != null) {
		let book = block.info("book");
		let para = block.info("paragraph");
		let sent = block.list();
		if (sent != null) {
			for (const element of sent) {
				let eText = document.getElementById("ta_" + blockId + "_" + element.begin + "_" + element.end);
				if (eText) {
					eText.innerHTML = element.text;
				}
				let eLable = document.getElementById("tran_pre_" + blockId + "_p" + book + "-" + para + "-" + element.end);
				if (eLable) {
					eLable.innerHTML = element.text;
				}
			}
			term_updata_translation();
		}

	}
}

/*
重绘翻译数据块
*/
function update_tran_block(blockId) {
	let eBlock = document.getElementById("id_tran_" + blockId);
	if (eBlock) {
		eBlock.innerHTML = renderTranslateParBlockInnerById(blockId);
		term_updata_translation();
	}
	/*
	$("#id_tran_"+blockId).html(renderTranslateParBlockInnerById(blockId));
	term_updata_translation();
*/
}
//譯文段落渲染
function renderTranslateParBlockInnerById(blockId) {
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		bId = getNodeText(xmlParInfo, "id");
		if (bId == blockId) {
			return (renderTranslateParBlockInner(xBlock[iBlock]));
		}
	}
}
//譯文段落渲染
function renderTranslateParBlockInner(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	var book = getNodeText(xmlParInfo, "book");
	var paragraph = getNodeText(xmlParInfo, "paragraph");
	var bId = getNodeText(xmlParInfo, "id");
	var power = getNodeText(xmlParInfo, "power");
	var readonly = getNodeText(xmlParInfo, "readonly");

	par_num = paragraph - 1;

	type = getNodeText(xmlParInfo, "type")
	language = getNodeText(xmlParInfo, "language")
	var allSen = elementBlock.getElementsByTagName("sen")
	output += "<div>";
	for (iSen = 0; iSen < allSen.length; iSen++) {
		var senText = getNodeText(allSen[iSen], "text");
		var senBegin = getNodeText(allSen[iSen], "begin");
		var senEnd = getNodeText(allSen[iSen], "end");
		var senA = "p" + book + "-" + paragraph + "-" + senEnd;

		output += "<div id='tran_sent_" + bId + "_" + senA + "' block='" + bId + "' sn='" + senA + "' class='tran_sent' >";


		if (power != "read") {//可写入模式
			if (readonly != 1) {
				output += "<div class='tran_sent_inner' onmouseenter=\"tran_sent_div_mouseenter('" + bId + "','" + senA + "')\" >";
			}
		}
		else {
			output += "<div class='tran_sent_inner' >";
		}
		output += "<div id='tran_pre_" + bId + "_" + senA + "' class='tran_sent_pre'>";
		if (senText == "") {
			output += "<span style='color:gray;'>我的译文</span>";
		}
		else {
			output += term_std_str_to_tran(senText);
		}
		output += "</div>";
		output += "<div id='tran_sent_text_div_" + bId + "_" + senA + "' class='tran_sen_textarea'";
		//在非编辑状态下 不显示 编辑框
		if (_tran_show_textarea_esc_edit == false) {
			output += " style='display:none'";
		}
		output += ">";
		//只读权限不显示修改框
		if (power != "read") {//可写入模式
			if (readonly != 1) {//只读模式不显示修改框 只有power=write才考虑只读模式
				output += "<textarea id='ta_" + bId + "_" + senBegin + "_" + senEnd + "' ";
				output += " onkeyup=\"updateTranslationPreview('" + bId + "_" + senA + "',this)\" ";
				output += " onchange=\"tran_text_onchange('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" ";
				//output += " onchange=\"sen_save('"+bId+"','"+senBegin+"','"+senEnd+"',this.value)\" ";
				output += " onblur=\"tran_sent_div_blur('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" ";
				output += " onfocus=\"tran_sent_div_onfocus('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" >";
				senText = senText.replace(/\<br \/\>/g, "\n\n");
				output += term_std_str_to_edit(senText);
				output += "</textarea>";
			}
		}

		output += "</div>";
		output += "<div class=\"tran_sent_text_tool_bar\">";
		//output += "<button onclick=\"tran_sen_save_click('"+bId+"','"+senBegin+"','"+senEnd+"',this)\">Save</button>";
		output += "<span></span>";
		output += "<span onclick=\"show_tran_msg('" + bId + "','" + senBegin + "','" + senEnd + "')\"><span id='' class=\"word_msg\"></span></span>";

		output += "</div>";
		output += "</div>";
		output += "</div>";

		output += "</div>";
	}
	output += "</div>";

	return output;
}



function renderTranslateParBlockInnerPreview(strText) {

}
function updateTranslationPreview_a(blockId, text) {
	var out = "";
	var newText = text;
	newText = newText.replace(/\n\n/g, "<br />");
	newText = term_tran_edit_replace(newText);
	newText = term_edit_to_std_str(newText);
	//out+=newText+"<br>";
	newText = term_std_str_to_tran(newText);
	out += newText;
	if (out == "") {
		out = "<span style='color:#ccbfbf;'>I have some ...</span>";
	}
	$("#tran_pre_" + blockId).html(out);
	term_updata_translation();
}
function updateTranslationPreview(blockId, obj) {
	updateTranslationPreview_a(blockId, obj.value);
}

function getSuperTranslateModifyString(inString, par_num, par_guid, language) {
	var curr_super_info = getPrevNextTrans_Guid("curr", par_guid);

	var arrString = translate_split(curr_super_info.senText, language).string;
	var output = "";
	var arrString_last = "";
	var sent_ID = "";
	var arr_sentA_ID = g_arr_Para_ID[par_num]
	var y = 0;
	for (y in arr_par_sent_num) {//得到本段句子外殻數0~par_num_last
		var arr_sentID = arr_par_sent_num[y].split("_");
		if (arr_sentID[1] == par_num) {
			var par_num_last = arr_sentID[2]
		}
	}
	if (par_num_last >= arrString.length - 1) {//外殻數大於等於切分結果，執行充填
		for (var x = 0; x < arrString.length; x++) {
			//整段渲染
			sent_ID = "sent_" + arr_sentA_ID[x];
			var superStringPosition_Start = 0
			var superStringPosition_End = 0
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			var output_super = "";
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start
				superStringPosition_Start += superString[super_x].length;
				output_super += "<span id='SBS_senA_" + par_guid + "_" + superStringPosition_End + "' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super += "<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + "' class=\"tooltip\">";
				output_super += superString[super_x];
				output_super += "<span class=\"tooltiptext tooltip-bottom\">";
				output_super += "<button onclick='super_trans_modify(up_sen," + superStringPosition_Start + "," + par_guid + "," + language + ")'>▲</button>";
				output_super += "<button onclick='super_trans_modify(down_sen," + superStringPosition_End + "," + par_guid + "," + language + ")'>▼</button>";
				output_super += "</span></span>";
			}
			var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>"
			if (document.getElementById(sent_ID).innerHTML.length != 0) {
				if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
					document.getElementById(sent_ID).innerHTML = output_super_0;//把逐句譯內容根據相應ID號寫入
				}
				else {
					document.getElementById(sent_ID).innerHTML += output_super_0;//把逐句譯內容根據相應ID號寫入
				}
			}
			output += output_super.replace(/SBS_/g, "PBP_");//逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par");//逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par");//逐句譯id轉換為逐段id

		}
	}
	else if (par_num_last < arrString.length - 1) {//外殻數小於切分結果
		for (var x = 0; x < par_num_last; x++) {//除最後一句，其他充填
			//整段渲染
			sent_ID = "sent_" + arr_sentA_ID[x];
			var superStringPosition_Start = 0
			var superStringPosition_End = 0
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			var output_super = "";
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start
				superStringPosition_Start += superString[super_x].length;
				output_super += "<span id='SBS_senA_" + par_guid + "_" + superStringPosition_End + "' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super += "<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + "' class=\"tooltip\">";
				output_super += superString[super_x];
				output_super += "<span class=\"tooltiptext tooltip-bottom\">";
				output_super += "<button onclick='super_trans_modify(up_sen," + superStringPosition_Start + "," + par_guid + "," + language + ")'>▲</button>";
				output_super += "<button onclick='super_trans_modify(down_sen," + superStringPosition_End + "," + par_guid + "," + language + ")'>▼</button>";
				output_super += "</span></span>";
			}
			output += output_super.replace(/SBS_/g, "PBP_");//逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par");//逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par");//逐句譯id轉換為逐段id
			var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>"
			if (document.getElementById(sent_ID).innerHTML.length != 0) {
				if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
					document.getElementById(sent_ID).innerHTML = output_super_0;//把逐句譯內容根據相應ID號寫入
				}
				else {
					document.getElementById(sent_ID).innerHTML += output_super_0;//把逐句譯內容根據相應ID號寫入
				}
			}
		}
		var output_super = "";
		for (var x = par_num_last; x < arrString.length - 1; x++) {//最後一句，合併所有譯文
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start
				superStringPosition_Start += superString[super_x].length;
				output_super += "<span id='SBS_senA_" + par_guid + "_" + superStringPosition_End + "' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super += "<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + "' class=\"tooltip\">";
				output_super += superString[super_x];
				output_super += "<span class=\"tooltiptext tooltip-bottom\">";
				output_super += "<button onclick='super_trans_modify(up_sen," + superStringPosition_Start + "," + par_guid + "," + language + ")'>▲</button>";
				output_super += "<button onclick='super_trans_modify(down_sen," + superStringPosition_End + "," + par_guid + "," + language + ")'>▼</button>";
				output_super += "</span></span>";
			}
			output += output_super.replace(/SBS_/g, "PBP_");//逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par");//逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par");//逐句譯id轉換為逐段id
		}
		sent_ID = "sent_" + arr_sentA_ID[par_num_last];
		var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>"
		if (document.getElementById(sent_ID).innerHTML.length != 0) {
			if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
				document.getElementById(sent_ID).innerHTML = output_super_0;//把逐句譯內容根據相應ID號寫入
			}
			else {
				document.getElementById(sent_ID).innerHTML += output_super_0;//把逐句譯內容根據相應ID號寫入
			}
		}
	}
	return output;
}

function super_trans_modify(updown, superStringPosition, par_guid, language) {//未完成

	var prev_super_info = getPrevNextTrans_Guid(prev, par_guid);
	var next_super_info = getPrevNextTrans_Guid(next, par_guid);
	var curr_super_info = getPrevNextTrans_Guid(curr, par_guid);

	var new_prev_super_info = new Object();
	var new_next_super_info = new Object();
	var new_curr_super_info = new Object();

	switch (updown) {
		case "up_par":
			new_prev_super_info.GUID = prev_super_info.GUID;
			new_prev_super_info.senText = prev_super_info.senText + curr_super_info.senText.replace(/#/g, "").slice(0, superStringPosition);
			var new_senText_array = translate_super_split(new_prev_super_info.senText, language)
			new_prev_super_info.senA = "0_";
			for (new_senText_array_i in new_senText_array) {
				new_prev_super_info.senA += new_senText_array[new_senText_array_i].length;
				new_prev_super_info.senA += "#" + new_senText_array[new_senText_array_i].length + "_";
				if (new_senText_array_i == new_senText_array.length - 1) {
					new_prev_super_info.senA += new_senText_array[new_senText_array_i].length;
					break;
				}
			}
			new_prev_super_info.senA = (new_prev_super_info.senA + "#").replace(/##/g, "");

			new_curr_super_info.GUID = curr_super_info.GUID;
			new_curr_super_info.senText = curr_super_info.senText.replace(/#/g, "").slice(superStringPosition);
			var new_senText_array = translate_super_split(new_curr_super_info.senText, language)
			new_curr_super_info.senA = "";
			for (new_senText_array_i in new_senText_array) {
				new_curr_super_info.senA += new_senText_array[new_senText_array_i].length;
				new_curr_super_info.senA += "#" + new_senText_array[new_senText_array_i].length + "_";
				if (new_senText_array_i == new_senText_array.length - 1) {
					new_curr_super_info.senA += new_senText_array[new_senText_array_i].length;
					break;
				}
			}
			new_curr_super_info.senA = (new_curr_super_info.senA + "#").replace(/##/g, "");

			break;
		case "down_par":
			if (par_num > max_par_num) {
				//无效通知

			}

			break;
	}
}
function getPrevNextTrans_Guid(direction, par_guid) {
	var allBlock = gXmlBookDataBody.getElementsByTagName("block")//得到全局block
	var prev_super_info = new Object;
	var next_super_info = new Object;
	var curr_super_info = new Object;
	var super_arr_xmlParInfo = new Array();
	var super_arr_xmlParData = new Array();
	var super_arr_xmlParAllsen = new Array();
	var super_arr_xmlParAllloc = new Array();

	curr_super_info.GUID = par_guid;
	for (var super_iBlock = 0; super_iBlock < allBlock.length; super_iBlock++) {
		if (super_iBlock == "length") {
			break;
		}
		var elementBlock = allBlock[super_iBlock];//得到其中一段的數據
		if (elementBlock.getElementsByTagName("sen").length != 0) {
			super_arr_xmlParInfo.push(elementBlock.getElementsByTagName("info")[0]);
			super_arr_xmlParData.push(elementBlock.getElementsByTagName("data")[0]);
		}
	}
	if (elementBlock.getElementsByTagName("sen").length != 0) {

		for (var super_iData in super_arr_xmlParData) {
			var super_xmlParAll_Sen = "";
			var super_xmlParAll_Loc = "";
			for (var super_iSen = 0; super_iSen < (super_arr_xmlParData[super_iData].childNodes).length; super_iSen++) {
				var super_xmlParData_Detail = (super_arr_xmlParData[super_iData].childNodes)[super_iSen]
				super_xmlParAll_Sen += super_xmlParData_Detail.getElementsByTagName("text")[0].innerHTML + "#";
				super_xmlParAll_Loc += super_xmlParData_Detail.getElementsByTagName("a")[0].innerHTML + "#";
			}
			super_xmlParAll_Sen = (super_xmlParAll_Sen + "#").replace(/##/g, "");
			super_xmlParAll_Loc = (super_xmlParAll_Loc + "#").replace(/##/g, "");
			super_arr_xmlParAllsen.push(super_xmlParAll_Sen);
			super_arr_xmlParAllloc.push(super_xmlParAll_Loc);
		}

		for (var super_iBlock = 0; super_iBlock < super_arr_xmlParInfo.length; super_iBlock++) {
			if (super_iBlock == "length") {
				break;
			}
			var super_GUID = getNodeText(super_arr_xmlParInfo[super_iBlock], "id");//得到guid
			prev_super_info.senText = "";
			prev_super_info.senA = "";
			curr_super_info.senText = super_arr_xmlParAllsen[super_iBlock];
			curr_super_info.senA = super_arr_xmlParAllloc[super_iBlock];
			next_super_info.senText = "";
			next_super_info.senA = "";
			if (super_GUID == par_guid && super_iBlock == 0) {
				prev_super_info.GUID = "";//前一個為空
				next_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock + 1], "id");//得到後一個guid
				next_super_info.senText = super_arr_xmlParAllsen[super_iBlock + 1];
				next_super_info.senA = super_arr_xmlParAllloc[super_iBlock + 1];//根據譯文數組得到錨點

				break;
			}
			else if (super_GUID == par_guid && super_iBlock > 0 && super_iBlock < super_arr_xmlParAllsen.length - 1) {
				prev_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock - 1], "id");//得到前一個guid
				next_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock + 1], "id");//得到後一個guid
				prev_super_info.senText = super_arr_xmlParAllsen[super_iBlock - 1];
				prev_super_info.senA = super_arr_xmlParAllloc[super_iBlock - 1];//根據譯文數組得到錨點
				next_super_info.senText = super_arr_xmlParAllsen[super_iBlock + 1];
				next_super_info.senA = super_arr_xmlParAllloc[super_iBlock + 1];//根據譯文數組得到錨點

				break;
			}
			else if (super_GUID == par_guid && super_iBlock == super_arr_xmlParAllsen.length - 1) {
				prev_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock - 1], "id");//得到前一個guid
				next_super_info.GUID = "";
				prev_super_info.senText = super_arr_xmlParAllsen[super_iBlock - 1];
				prev_super_info.senA = super_arr_xmlParAllloc[super_iBlock - 1];//根據譯文數組得到錨點
				break;
			}

		}
	}
	else {
		prev_super_info.GUID = "";
		prev_super_info.senText = "";
		prev_super_info.senA = "";
		curr_super_info.GUID = "";
		curr_super_info.senText = "";
		curr_super_info.senA = "";
		next_super_info.GUID = "";
		next_super_info.senText = "";
		next_super_info.senA = "";

	}
	switch (direction) {
		case "next":
			return (next_super_info);
			break;
		case "curr":
			return (curr_super_info);
			break;
		case "prev":
			return (prev_super_info);
			break;
	}
}

function translate_split(inString, language) {
	var newString = ""
	var arrString = new Object;
	var language_type = "";
	if (language.toLowerCase() == "tw" || language.toLowerCase() == "zh" || language.toLowerCase() == "sc" || language.toLowerCase() == "tc") {
		language_type = "ZH"
	}
	else {
		language_type = "EN"
	}
	arrString.language_type = language_type.toLowerCase();
	switch (language_type) {
		case "ZH":
			for (var i_cntransplit in cn_transplit) {
				newString = inString
				eval("newString = newString.replace(" + cn_transplit[i_cntransplit] + ")");
			}
			/*newString = inString.replace(/。/g,"。#");
			newString = newString.replace(/;/g,"；");
			newString = newString.replace(/ /g,"");
			newString = newString.replace(/；/g,"；#");
			newString = newString.replace(/？/g,"？#");
			newString = newString.replace(/”“/g,"”#“");
			newString = newString.replace(/’‘/g,"’#‘");
			newString = newString.replace(/。#”/g,"。”#");
			newString = newString.replace(/？#”/g,"？”#");
			newString = newString.replace(/？“/g,"？#“");
			newString = newString.replace(/：“/g,"：#“");
			newString = newString.replace(/：「/g,"：#「");
			newString = newString.replace(/！#’/g,"！’#");
			newString = newString.replace(/。#’/g,"。’#");
			newString = newString.replace(/？#’/g,"？’#");
			newString = newString.replace(/？‘/g,"？#‘");
			newString = newString.replace(/：‘/g,"：#‘");
			newString = newString.replace(/##/g,"#");
			*/

			arrString.string = newString.split("#");
			break;
		case "EN":
			newString = inString
			for (var i_entransplit in en_transplit) {
				newString = inString
				eval("newString = newString.replace(" + en_transplit[i_entransplit] + ")");
			}

			/*newString = inString.replace(/\./g,"\.#");
			newString = newString.replace(/;/g,"；");
			newString = newString.replace(/；/g,"；#");
			newString = newString.replace(/\?/g,"？#");
			newString = newString.replace(/\"\"/g,"”#“");
			newString = newString.replace(/.\'\'/g,".”");
			newString = newString.replace(/.#\"/g,".”#");
			newString = newString.replace(/\?#\"/g,"？”#");
			newString = newString.replace(/\?“/g,"？#“");
			newString = newString.replace(/,\"/g,",#“");
			newString = newString.replace(/!#\'/g,"！’#");
			newString = newString.replace(/.#\'/g,".’#");
			newString = newString.replace(/？#’/g,"？’#");
			newString = newString.replace(/？‘/g,"？#‘");
			newString = newString.replace(/##/g,"#");
			*/

			arrString.string = newString.split("#");
			break;

	}
	return arrString
	// body...
}
function translate_super_split(inString) {
	var newString = inString
	var arrString = new Object;
	var language_type = "";
	if (language.toLowerCase() == "tw" || language.toLowerCase() == "zh" || language.toLowerCase() == "sc" || language.toLowerCase() == "tc") {
		language_type = "ZH"
	}
	else {
		language_type = "EN"
	}
	arrString.language_type = language_type.toLowerCase();
	switch (language_type) {
		case "ZH":
			newString = newString.replace(/，/g, "，#");
			newString = newString.replace(/！/g, "！#");
			newString = newString.replace(/！#”/g, "！”#");
			newString = newString.replace(/！#’/g, "！’#");

			arrString.string = newString.split("#");
			break;
		case "EN":
			newString = newString.replace(/,/g, ",#");
			newString = newString.replace(/!/g, "!#");

			arrString.string = newString.split("#");

			break;
	}
	return arrString;
	// body...
}

function renderNoteParBlockInner(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	book = getNodeText(xmlParInfo, "book")
	paragraph = getNodeText(xmlParInfo, "paragraph")
	bId = getNodeText(xmlParInfo, "id")

	type = getNodeText(xmlParInfo, "type")
	var allSen = elementBlock.getElementsByTagName("sen")
	output += "<button type=\"button\" class=\"edit_note_button imgbutton\" onclick=\"editor_note_edit('" + bId + "')\"><svg class='icon'><use xlink:href='svg/icon.svg#ic_mode_edit'></use></svg></button>"
	for (iSen = 0; iSen < allSen.length; iSen++) {
		senText = getNodeText(allSen[iSen], "text")
		senA = getNodeText(allSen[iSen], "a")
		output += "<span id=\"note_sen_" + bId + "_" + iSen + "\" class=\"note_sen\">"
		output += senText;
		output += "</span>"
	}

	return output;
}

function renderHeadingBlockInner(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	book = getNodeText(xmlParInfo, "book")
	paragraph = getNodeText(xmlParInfo, "paragraph")
	bId = getNodeText(xmlParInfo, "id")

	type = getNodeText(xmlParInfo, "type")
	var headingData = elementBlock.getElementsByTagName("data")[0]
	headingText = getNodeText(headingData, "text")
	output += "<span id=\"id_heading_text_" + bId + "\">" + headingText + "</span>";

	output += "<button type=\"button\" class=\"edit_tool imgbutton\" onclick=\"editor_heading_edit('" + bId + "')\"><svg class='icon'><use xlink:href='svg/icon.svg#ic_mode_edit'></use></svg></button>"

	return output;
}

function updateWordParBlockInnerAll() {
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		mId = getNodeText(xmlParInfo, "id")
		type = getNodeText(xmlParInfo, "type")
		if (type = "wbw") {
			updateWordParBlockInner(xBlock[iBlock]);
		}
	}
}

function updateWordParBlockInner(elementBlock) {
	var xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	var blockId = getNodeText(xmlParInfo, "id");
	var wbwblock = document.getElementById("id_wbw_" + blockId);
	if (wbwblock) {
		wbwblock.innerHTML = renderWordParBlockInner(elementBlock);
		word_mouse_event();
	}
}
function renderNoteShell(elementBlock) {
	let output = "";
	let allWord = elementBlock.getElementsByTagName("word");
	for (iWord = 0; iWord < allWord.length; iWord++) {
		let wID = getNodeText(allWord[iWord], "id");
		output += "<div id=\"wn_" + wID + "\">";
		output += "<div id=\"wnn_" + wID + "\"></div>";
		output += "<div id=\"wnr_" + wID + "\"></div>";
		output += "<div id=\"wnc_" + wID + "\"></div>";
		output += "</div>";
	}
	return (output);
}
function render_sent_tool_bar(elementBlock, begin) {

	let output = "";
	let axmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let abook = getNodeText(axmlParInfo, "book");
	let aparagraph = getNodeText(axmlParInfo, "paragraph");
	let xallWord = elementBlock.getElementsByTagName("word");
	let iBegin = -1;
	let iEnd = -1;
	let iWordId = 0;
	for (let i = 0; i < xallWord.length; i++) {
		let aID = getNodeText(xallWord[i], "id");
		let aEnter = getNodeText(xallWord[i], "enter");
		let arrId = aID.split("-");
		iWordId = parseInt(arrId[2]);
		if (iWordId > begin && iBegin < 0) {
			iBegin = iWordId;
		}
		if (iWordId > begin && aEnter == "1") {//句子末尾
			iEnd = iWordId;
			break;
		}
	}
	if (iEnd == -1) {
		iEnd = iWordId;
	}

	output += "<div class='sent_wbw_trans_bar'>";
	let sentIdString = abook + "-" + aparagraph + "-" + iBegin + "-" + iEnd;
	let sentIdStringLink = "{{" + sentIdString + "}}";
	output += "<span>" + sentIdString + "<a onclick=\"copy_to_clipboard('" + sentIdStringLink + "')\">[" + gLocal.gui.copy_to_clipboard + "]</a></span>";
	//	output += "<span>"+abook+"-"+aparagraph+"-"+iBegin+"-"+iEnd+"</span>";
	output += "<guide gid='sent_func' style='margin:unset;'></guide>";
	output += "</div>";
	return (output);
}
var arr_par_sent_num = new Array();
var g_arr_Para_ID = new Array();
function renderWordParBlockInner(elementBlock) {
	var output = "<div style='display:block;width:100%'>";
	let outList = "<table>";//list mode 
	var Note_Mark = 0;
	var sent_gramma_i = 0;
	var word_length_count = 0;
	var sent_num = 0;

	var arr_Para_ID = new Array();
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book");
	let paragraph = getNodeText(xmlParInfo, "paragraph");
	let par_num = paragraph - 1;
	let type = getNodeText(xmlParInfo, "type");
	let allWord = elementBlock.getElementsByTagName("word");
	output += "<div class='sent_wbw_trans'>";
	output += render_sent_tool_bar(elementBlock, 0);
	output += "<div class='sent_wbw'>";

	let sent_begin = 0;
	let wID;
	let word_id;
	for (let iWord = 0; iWord < allWord.length; iWord++) {
		wID = getNodeText(allWord[iWord], "id");
		let wPali = getNodeText(allWord[iWord], "pali");
		let wReal = getNodeText(allWord[iWord], "real");
		let wType = getNodeText(allWord[iWord], "type");
		let wGramma = getNodeText(allWord[iWord], "gramma");
		let wCase = getNodeText(allWord[iWord], "case");
		let wMean = getNodeText(allWord[iWord], "mean");
		let wParent = getNodeText(allWord[iWord], "parent");
		let wUn = getNodeText(allWord[iWord], "un");
		let wStyle = getNodeText(allWord[iWord], "style");
		let wEnter = getNodeText(allWord[iWord], "enter");

		if ((wType == "" || wType == "?") && wCase != "") {
			wType = wCase.split("#")[0];
		}

		word_id = parseInt(wID.split("-")[2]);
		if (sent_begin == 0) {
			sent_begin = word_id;
		}

		//渲染单词块
		//word div class
		let strMouseEvent = " onmouseover=\"on_word_mouse_enter()\" onmouseout=\"on_word_mouse_leave()\""
		if (wType == ".ctl." && wGramma == ".a.") {
			//output += "<div><a name='"+wPali+"'></a></div>";
			output += "<div id=\"wb" + wID + "\" class='ctrl sent_gramma_" + (sent_gramma_i % 3) + "'> <a name='" + wPali + "'></a>";
		}
		else if (Note_Mark == 1) {
			output += "<div id=\"wb" + wID + "\" class='word org_note sent_gramma_" + (sent_gramma_i % 3) + "'> <a name='" + wPali + "'></a>";
		}
		else {
			if (wReal == "") {
				output += "<div id=\"wb" + wID + "\" class=\"word_punc un_parent sent_gramma_" + (sent_gramma_i % 3) + "\" >";//符號
			}
			else {
				if (wType == ".un.") {
					output += "<div id=\"wb" + wID + "\" class=\"word un_parent sent_gramma_" + (sent_gramma_i % 3) + "\" >";//粘音詞
				}
				else if (wType == ".comp.") {
					output += "<div id=\"wb" + wID + "\" class=\"word comp_parent sent_gramma_" + (sent_gramma_i % 3) + "\" >";//複合詞
				}
				else {
					output += "<div id=\"wb" + wID + "\" class=\"word sent_gramma_" + (sent_gramma_i % 3) + "\" >";//普通
				}
			}
		}

		//head div class
		output += "	<div id='ws_" + wID + "' class='word_head_shell' >";
		if (wUn.length > 0) {
			output += "	<div class='word_head un_pali' id=\"whead_" + wID + "\">";

		}
		else {
			output += "	<div class='word_head' id=\"whead_" + wID + "\">"
		}

		output += renderWordHeadInner(allWord[iWord]);
		output += "	</div>";
		output += "	</div>";
		output += "	<div id=\"detail" + wID + "\" class=\"wbody\">";
		output += renderWordBodyInner(allWord[iWord])
		output += "	</div>";
		output += "</div>";

		outList += "<tr>";
		outList += "<td>" + wReal + ":</td> <td>" + wType + "</td><td>" + wGramma + "</td> <td>" + wParent + "</td> <td>" + wMean + "</td>";
		outList += "</tr>";
		//渲染单词块结束

		word_length_count += wPali.length
		if (iWord >= 1) {
			var pre_pali_spell = getNodeText(allWord[iWord - 1], "pali");
			var pre_pali_type = getNodeText(allWord[iWord - 1], "type")
			var pre_pali_Gramma = getNodeText(allWord[iWord - 1], "gramma")
			var pre_pali_Case = getNodeText(allWord[iWord - 1], "case")
			if (pre_pali_type == "" && pre_pali_Case != "") {
				pre_pali_type = pre_pali_Case.split("#")[0];
			}
			if (pre_pali_Case != "" && pre_pali_Case.lastIndexOf("$") != -1) {
				var pre_case_array = pre_pali_Case.split("$");
				pre_pali_Case = pre_case_array[pre_case_array.length - 1]
			}
		}
		if ((iWord + 2) <= allWord.length) {
			var next_pali_spell = getNodeText(allWord[iWord + 1], "pali");
			var next_pali_type = getNodeText(allWord[iWord + 1], "type")
			var next_pali_Gramma = getNodeText(allWord[iWord + 1], "gramma")
			var next_pali_Case = getNodeText(allWord[iWord + 1], "case")
			if (next_pali_type == "" && next_pali_Case != "") {
				next_pali_type = next_pali_Case.split("#")[0];
			}
			if (next_pali_Case != "" && next_pali_Case.lastIndexOf("$") != -1) {
				var next_case_array = next_pali_Case.split("$");
				next_pali_Case = next_case_array[next_case_array.length - 1]
			}
		}
		if (next_pali_spell == "(") {
			Note_Mark = 1;
		}
		else if (pre_pali_spell == ")" && Note_Mark == 1) {
			Note_Mark = 0;
		}
		else {

		}

		if (wEnter == 1) {//句子末尾
			var sent_ID = "sent_" + par_num + "_" + sent_num;
			if (_display_sbs == 1) {//逐句显示模式
				output += "</div>";//逐句逐词译块结束

				//逐句翻译块开始
				output += "<div id='sent_div_" + wID + "' class='translate_sent'>";
				output += "<div class='translate_sent_head'>";
				output += "<div class='translate_sent_head_toolbar'>";
				output += "<span></span>";
				output += "<span onclick=\"show_tran_net('" + book + "','" + paragraph + "','" + sent_begin + "','" + word_id + "')\"><span id='' class=\"word_msg\">issue</span></span>";
				sent_begin = word_id;
				output += "</div>";
				output += "<div class='translate_sent_head_content'>";
				output += "</div>";
				output += "</div>";
				output += "<div id='sent_" + wID + "' class='translate_sent_content'>";
				output += "</div>";//逐句翻译块内容结束
				output += "<div class='translate_sent_foot'>";
				output += "</div>";
				output += "</div>";
				//逐句翻译块结束


				output += "</div>";//逐句块结束
				//下一个逐句块开始
				output += "<div class='sent_wbw_trans'>";
				//output += output += render_sent_tool_bar(elementBlock,word_id);
				let nextBegin = word_id + 1;
				let nextEnd = 0;
				for (let j = iWord + 1; j < allWord.length; j++) {
					let aID = getNodeText(allWord[j], "id");
					let aEnter = getNodeText(allWord[j], "enter");
					let arrId = aID.split("-");
					iWordId = parseInt(arrId[2]);
					if (aEnter == 1) {
						nextEnd = iWordId;
						break;
					}
				}
				if (nextEnd == 0) {
					nextEnd = iWordId;
				}
				output += "<div class='sent_wbw_trans_bar'>";
				let sentIdString = book + "-" + paragraph + "-" + nextBegin + "-" + nextEnd;
				let sentIdStringLink = "{{" + sentIdString + "}}";
				output += "<span>" + sentIdString + "<a onclick=\"copy_to_clipboard('" + sentIdStringLink + "')\">[" + gLocal.gui.copy_to_clipboard + "]</a></span>";
				output += "<guide gid='sent_func' style='margin:unset;'></guide>";
				output += "</div>";

				output += "<div class='sent_wbw'>";
			}
			sent_num += 1
			word_length_count = 0;
			sent_gramma_i = 0;
			arr_Para_ID.push(wID);
		}

	}//循環結束

	output += "</div>";

	//逐句翻译块开始
	output += "<div id='sent_div_" + wID + "' class='translate_sent'>";
	output += "<div class='translate_sent_head'>";
	output += "<div class='translate_sent_head_toolbar'>";
	output += "<span></span>";
	output += "<span onclick=\"show_tran_net('" + book + "','" + paragraph + "','" + sent_begin + "','" + word_id + "')\"><span id='' class=\"word_msg\">issue</span></span>";
	output += "</div>";
	output += "<div class='translate_sent_head_content'>";
	output += "</div>";
	output += "</div>";
	output += "<div id='sent_" + wID + "' class='translate_sent_content'>";
	output += "</div>";//逐句翻译块内容结束
	output += "<div class='translate_sent_foot'>";
	output += "</div>";
	output += "</div>";
	//逐句翻译块结束

	output += "</div>";

	outList += "</table>";

	var sent_ID = "sent_" + par_num + "_" + sent_num;
	arr_Para_ID.push(wID);
	arr_par_sent_num.push(sent_ID);
	g_arr_Para_ID[par_num] = arr_Para_ID;
	guide_init();
	return output;//+outList;

}

function magic_sentence_cut() {
	var all_sent_array = document.getElementsByClassName("sent_wbw");
	for (i_magic = 0; i_magic < all_sent_array.length; i_magic++) {
		if (all_sent_array[i_magic].getElementsByClassName("word sent_gramma_1").length != 0) {
			all_sent_array[i_magic].getElementsByClassName("word sent_gramma_1")[0].className += " sent_cut";
		}
		if (all_sent_array[i_magic].getElementsByClassName("word sent_gramma_2").length != 0) {
			all_sent_array[i_magic].getElementsByClassName("word sent_gramma_2")[0].className += " sent_cut";
		}
	}
}

function updataWordHeadById(wordId) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	var iIndex = getWordIndex(wordId);
	if (iIndex >= 0) {
		$("#whead_" + wordId).html(renderWordHeadInner(xAllWord[iIndex]));
	}
}

function updataWordHeadByIndex(wordIndex) {
	if (wordIndex > 0) {
		let xAllWord = gXmlBookDataBody.getElementsByTagName("word");
		let wordId = getNodeText(xAllWord[wordIndex], "id");
		let obj = document.getElementById("whead_" + wordId);
		if (obj) {
			obj.innerHTML = renderWordHeadInner(xAllWord[wordIndex]);
		}
	}
}
function updateWordBodyById(wordId) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	var wordIndex = getWordIndex(wordId);
	var obj = document.getElementById("detail" + wordId);
	if (obj) {
		obj.innerHTML = renderWordBodyInner(xAllWord[wordIndex]);
	}
}
//根据xmlDocument 对象中的单词序号修改单词块的显示（不含Pali）
//返回 无	
function modifyWordDetailByWordIndex(wordIndex) {
	xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	wordId = getNodeText(xAllWord[wordIndex], "id");
	try {
		var sDetail = "detail" + wordId;
		var cDetail = document.getElementById(sDetail);
		if (cDetail != null) {
			cDetail.innerHTML = renderWordDetailByElement(xAllWord[wordIndex]);
		}
	}
	catch (error) {
		debugOutput(error);
	}
}

function renderWordHeadInner(element) {
	var output = "";
	var wid = getNodeText(element, "id");
	var wpali = getNodeText(element, "pali");
	var sParent = getNodeText(element, "parent");
	var wId = getNodeText(element, "id");
	var wNote = getNodeText(element, "note");

	wStyle = "v_" + getNodeText(element, "style")
	if (wStyle == "v_bld") {
		if (wpali.indexOf("{") >= 0) {
			wpali = wpali.replace("{", "<span class='v_bld'>")
			wpali = wpali.replace("}", "</span>")
			wStyle = "";
		}
	}
	if (wNote.indexOf("{{") >= 0 && wNote.indexOf("}}") >= 0) {
		wStyle += " note_ref";
	}

	if (wNote.substring(0, 6) == "=term(") {
		wStyle += " term_word_head";
	}
	else {
		if (sParent.length > 0) {
			if (term_lookup_my(sParent) != null) {
				wStyle += " term_my";
			}
			else {
				if (term_lookup_all(sParent) != null) {
					wStyle += " term_other";
				}
			}
		}
	}

	var sign_count = 0;
	var letter_count = 0;
	for (i_sign in local_sign_str) {
		if (wpali.lastIndexOf(local_sign_str[i_sign].id) != -1) {
			sign_count += 1;
		}//如果是标点或数字
		else if (wpali.lastIndexOf(local_letter_str[i_sign].id) != -1) {
			letter_count += 1;
		}//如果有字母
	}
	if (sign_count != 0 && letter_count == 0) {
		output += "<a name='w" + wId + "'>";
	}
	else {
		output += "<a name='w" + wId + "' onclick='on_word_click(\"" + wId + "\")'>";
	}
	if (wpali)
		output += "<span id=\"whead1_" + wid + "\" class=\"whead paliword1 " + wStyle + "\">"
	output += wpali;
	output += "</span>"
	output += "</a>"
	if (wNote.length > 0) {
		output += "<span id=\"wnote_root_" + wid + "\"><wnh wid=\"" + wid + "\">[1]</wnh></span>";
	}
	else {
		output += "<span id=\"wnote_root_" + wid + "\"></span>";
	}
	var newMsg = msg_word_msg_num(wid);
	if (newMsg > 0) {
		output += "<span class='word_msg' onclick=\"word_msg_counter_click('" + wid + "')\">" + newMsg + "</span>";
	}
	output += "<span id=\"whead2_" + wid + "\" class=\"whead paliword2 " + wStyle + "\">"
	output += "</span>"
	return output;
}

function renderWordBodyInner(element) {
	return renderWordDetailByElement(element)
}

//新的渲染单词块函数
function renderWordDetailByElement_edit_a(xmlElement) {
	if (xmlElement == null) {
		return ("");
	}
	var wordNode = xmlElement;
	var sPali = getNodeText(wordNode, "pali");
	var sId = getNodeText(wordNode, "id");
	var sReal = getNodeText(wordNode, "real");
	var sMean = getNodeText(wordNode, "mean");
	var sOrg = getNodeText(wordNode, "org");
	var sOm = getNodeText(wordNode, "om");
	var sCase = getNodeText(wordNode, "case");
	var sType = getNodeText(wordNode, "type");
	var sGramma = getNodeText(wordNode, "gramma");
	var sParent = getNodeText(wordNode, "parent");
	var sParentGrammar = getNodeText(wordNode, "pg");
	var sParent2 = getNodeText(wordNode, "parent2");
	var sNote = getNodeText(wordNode, "note");
	var sStatus = getNodeText(wordNode, "status");
	if (sStatus == "") {
		sStatus = 0;
	}
	var wordID = sId;

	if (sCase == "") {
		sCase = sType + "#" + sGramma;
	}

	var _txtOutDetail = "";
	var _bgColor = "";
	var status_bg_color = "status_bg_color_" + sStatus;
	var _caseColor = "";
	//标点符号
	if (sReal.length <= 1) {
		_txtOutDetail += "<div>";
		/*id begin*/
		_txtOutDetail += "<div class='ID'> </div>";
		/*mean begin*/
		_txtOutDetail += "<div class='mean'> </div>";
		/*org begin*/
		_txtOutDetail += "<div class='org'> </div>";
		/*org mean begin*/
		_txtOutDetail += "<div class='om'> </div>";
		/*case begin*/
		_txtOutDetail += "<div class='case'> </div>";

		_txtOutDetail += "</div>";
		return _txtOutDetail;

	}


	var strBookMarkColor = getNodeText(wordNode, "bmc");
	if (strBookMarkColor.length > 2) {
		var icolor = strBookMarkColor.substr(-1);
		_bgColor = " class='bookmarkcolor" + icolor + "' ";
	}


	// Auto Match Begin


	if (sCase == '?' && _bgColor == "") {
		_caseColor = " class='bookmarkcolorx' "
	}
	if (g_useMode == "read" || g_useMode == "translate") {
		_bgColor = "";
		_caseColor = "";
		if (sOrg == "?") { sOrg = " "; }
		if (sMean == "?") { sMean = " "; }
		if (sCase == "?" || sCase == "?#?") { sCase = " "; }
	}



	//编辑模式开始
	{
		/*gramma*/
		/*find in dict*/
		var arrGramma = new Array();
		var thisWord = sReal;
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali) {
				if ((g_DictWordList[iDict].Type != "" && g_DictWordList[iDict].Type != "?") || (g_DictWordList[iDict].Gramma != "" && g_DictWordList[iDict].Gramma != "?")) {
					var arrCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
					pushNewToList(arrGramma, arrCase);
				}
			}
		}

		if (sCase == "?" || sCase == "?#?") {
			if (arrGramma.length > 0) {
				setNodeText(xmlElement, "case", arrGramma[0]);
				sCase = arrGramma[0];
			}
			else {
				setNodeText(xmlElement, "case", "?#?");
				sCase = "?#?";
			}
		}
		else {
			if (sCase.indexOf("#") == -1) {
				sCase = "?#" + sCase;
				setNodeText(xmlElement, "case", "?#" + sCase);
			}
		}

		var currType = sCase.split("#")[0];
		var currGramma = sCase.split("#")[1];


		//end gramma

		if (getNodeText(wordNode, "lock") == "true") {
			_bgColor += "  style='box-shadow: 0 3px 0 0 #FF0000'";
		}
		else {
			_bgColor += " class='bookmarkcolor0' style='box-shadow: 0 0 0 0'";
		}

		//状态颜色
		_txtOutDetail += "<div class='" + status_bg_color + "'>";
		//书签颜色
		_txtOutDetail += "<div " + _bgColor + ">";

		/*id begin*/
		/*
		_txtOutDetail +=  "<p class='ID'>";
		_txtOutDetail +=  sId + "&nbsp;"; 
		_txtOutDetail +=  "</p>"; 	
		*/
		/*id end*/



		/*meaning*/

		//格位公式开始
		//去除格位公式
		var currMeaning = removeFormula(sMean);
		//currMeaning = getLocalFormulaStr(currGramma,currMeaning);

		var orgMeaning = sMean;
		//切割过长意思
		if (sReal.length < 4) {
			currMeaning = getLocalFormulaStr(currGramma, cutString(currMeaning, 24));
		}
		else {
			currMeaning = getLocalFormulaStr(currGramma, cutString(currMeaning, sReal.length * 6));
		}

		renderMeaning = currMeaning.replace(/{/g, "<span class='grm_add_mean'>");
		renderMeaning = renderMeaning.replace(/}/g, "</span>");
		renderMeaning = renderMeaning.replace(/\[/g, "<span class='grm_add_mean_user'>");
		renderMeaning = renderMeaning.replace(/\]/g, "</span>");
		//格位公式结束
		if (sMean.length == 0) {
			renderMeaning = "<span class='word_space_holder'>meaning</span>"
		}
		//渲染下拉菜单
		_txtOutDetail += "<div class='mean'>";
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		_txtOutDetail += renderMeaning;
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div id='mean_" + wordID + "' class=\"case_dropdown-content\">";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";

		//render formula menu

		arrFormula = getFormulaList(currGramma);
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<svg class='edit_icon';'><use xlink:href='svg/icon.svg#ic_more'></use></svg>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		newWord = removeFormula_B(orgMeaning);
		_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"[]" + newWord + "\")'>[None]</a>";
		_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"" + newWord + "\")'>[Auto]</a>";
		for (var i in arrFormula) {
			newWord = removeFormula_B(orgMeaning);
			newWord = arrFormula[i].replace("~", newWord);
			newWord = newWord.replace(/{/g, "[");
			newWord = newWord.replace(/}/g, "]");
			_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"" + newWord + "\")'>" + arrFormula[i] + "</a>";
		}

		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of meaning*/

		/*org begin 拆分*/

		_txtOutDetail += "<div class='org'  name='w_org'>";
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		let currOrg;
		if (sOrg.length == 0) {
			currOrg = "<span class='word_space_holder'>parts</span>"
		}
		else {
			currOrg = sOrg;
		}
		_txtOutDetail += currOrg;
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		_txtOutDetail += "<div id='parts_" + sId + "'>Loading</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of factors*/

		/*part meaning begin*/


		_txtOutDetail += "<div class='om'  name='w_om'>";
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		sOm = sOm.replace("_un_auto_factormean_", getLocalGrammaStr("_un_auto_factormean_"));

		if (sOm == "?" || sOm.substring(0, 3) == "[a]") {
			var currDefualtFM = "";
			var listFactorForFactorMean = sOrg.split("+");
			for (iFactor in listFactorForFactorMean) {
				currDefualtFM += findFirstMeanInDict(listFactorForFactorMean[iFactor]) + "+";//拆分元素加号分隔
			}
			currDefualtFM = currDefualtFM.replace(/"  "/g, " ");
			currDefualtFM = currDefualtFM.replace(/"+ "/g, "+");
			currDefualtFM = currDefualtFM.replace(/" +"/g, "+");
			currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1);//去掉尾部的加号 kosalla
			if (currDefualtFM.slice(-1, -2) == "+") {
				currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1);
			}
			currOM = "[a]" + currDefualtFM;
			setNodeText(wordNode, "om", currOM);
		}
		else {
			currOM = sOm;
		}
		if (currOM.length == 0) {
			currOM = "<span class='word_space_holder''>part mean</span>"
		}

		_txtOutDetail += currOM;
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		_txtOutDetail += "<div id='partmean_" + sId + "'></div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";

		/*org meaning end*/


		/*begin gramma*/

		_txtOutDetail += "<div class='case'>";
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		var sLocalCase = getLocalGrammaStr(sCase);
		var mArrGramma = sCase.split("#");
		if (mArrGramma.length >= 2) {
			mType = mArrGramma[0];
			mGramma = mArrGramma[1];
			mLocalType = sLocalCase.split("#")[0];
			mLocalGramma = sLocalCase.split("#")[1];
		}
		else {
			mType = "";
			mGramma = mArrGramma[0];
			mLocalType = "";
			mLocalGramma = sLocalCase.split("#")[0];
		}

		if (mType != "") {
			_txtOutDetail += ("<span class='cell'>" + mLocalType + "</span>");
		}

		_txtOutDetail += cutString(mLocalGramma, 30);
		if ((mLocalType.length + mLocalGramma.length) == 0) {
			_txtOutDetail += "&nbsp;";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		_txtOutDetail += "<div id='gramma_" + sId + "'></div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";


		//连读词按钮
		if (mType == ".un." || mType == ".comp.") {
			nextElement = com_get_nextsibling(xmlElement);
			if (nextElement != null) {//下一个元素存在
				if (getNodeText(nextElement, "un") == sId) {//若有孩子則显示收起按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_remove(\"" + wordID + "\")'>";
					_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_join \"></use></svg>";
					_txtOutDetail += "</button>";
					var parentElement = document.getElementById('wb' + sId);
					if (parentElement) {
						parentElement.classList.add("un_parent");
					}
				}
				else {//無kid展開按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
					_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_split \"></use></svg>";
					_txtOutDetail += "</button> ";
				}
			}
			else {//下一个元素不存在
				_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
				_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_split \"></use></svg>";
				_txtOutDetail += "</button> ";
			}
		}
		//连读词按钮 结束
		//爷爷和父亲的关系 如pp
		/*
		if(wordGranfatherGramma.length>0 && wordGranfatherGramma.length<6){
			_txtOutDetail  += "<span  class=\"tooltip\">«"+getLocalGrammaStr(wordGranfatherGramma)+"<span class=\"tooltiptext tooltip-bottom\">"+wordGranfather+"</span>"+"</span> ";
		}
		*/
		_txtOutDetail += "</div> ";
		/*end of gramma*/

		_txtOutDetail += "</div>";

		_txtOutDetail += "</div>";
	}
	return (_txtOutDetail);
}

function renderWordDetailByElement(xmlElement) {
	return renderWordDetailByElement_edit_a(xmlElement);

	if (xmlElement == null) {
		return ("");
	}
	wordNode = xmlElement;
	var sPali = getNodeText(wordNode, "pali");
	var sId = getNodeText(wordNode, "id");
	var sReal = getNodeText(wordNode, "real");
	var sMean = getNodeText(wordNode, "mean");
	var sOrg = getNodeText(wordNode, "org");
	var sOm = getNodeText(wordNode, "om");
	var sCase = getNodeText(wordNode, "case");
	var sType = getNodeText(wordNode, "type");
	var sGramma = getNodeText(wordNode, "gramma");
	var sParent = getNodeText(wordNode, "parent");
	var sNote = getNodeText(wordNode, "note");
	wordID = sId;

	if (sCase == "") {
		sCase = sType + "#" + sGramma;
	}

	var _txtOutDetail = "";
	var _bgColor = "";
	var _caseColor = "";
	//标点符号
	if (sReal.length <= 1) {
		_txtOutDetail += "<div>";
		/*id begin*/
		_txtOutDetail += "<div class='ID'> </div>";
		/*mean begin*/
		_txtOutDetail += "<div class='mean'> </div>";
		/*org begin*/
		_txtOutDetail += "<div class='org'> </div>";
		/*org mean begin*/
		_txtOutDetail += "<div class='om'> </div>";
		/*case begin*/
		_txtOutDetail += "<div class='case'> </div>";

		_txtOutDetail += "</div>";
		return _txtOutDetail;

	}

	if (sMean == '?') {
		_bgColor = " class='bookmarkcolorx' "
	}

	strBookMarkColor = getNodeText(wordNode, "bmc");
	if (strBookMarkColor.length > 2) {
		var icolor = strBookMarkColor.substr(-1);
		_bgColor = " class='bookmarkcolor" + icolor + "' ";
	}


	// Auto Match Begin


	if (sCase == '?' && _bgColor == "") {
		_caseColor = " class='bookmarkcolorx' "
	}
	if (g_useMode == "read" || g_useMode == "translate") {
		_bgColor = "";
		_caseColor = "";
		if (sOrg == "?") { sOrg = " "; }
		if (sMean == "?") { sMean = " "; }
		if (sCase == "?" || sCase == "?#?") { sCase = " "; }
	}

	if (g_useMode == "read") {
		_txtOutDetail += "<div " + _bgColor + ">";

		/*id begin*/
		_txtOutDetail += "<p class='ID'>";
		_txtOutDetail += sId + "&nbsp;";
		_txtOutDetail += "</p>";
		/*id end*/

		/*meaning begin*/
		var showMean = sMean.replace(/{/g, "<span class='grm_add_mean'>");
		showMean = showMean.replace(/}/g, "</span>");
		showMean = showMean.replace(/\[/g, "<span class='grm_add_mean_user'>");
		showMean = showMean.replace(/\]/g, "</span>");
		_txtOutDetail += "<p class='mean'>";
		_txtOutDetail += showMean + "&nbsp;";
		_txtOutDetail += "</p>";
		/*meaning end*/

		/*org begin*/
		_txtOutDetail += "<p class='org'>";
		_txtOutDetail += sOrg + "&nbsp;";
		_txtOutDetail += "</p>";
		/*org end*/

		/*org meaning begin*/
		_txtOutDetail += "<p class='om'>";
		_txtOutDetail += sOm + "&nbsp;";
		_txtOutDetail += "</p>";
		/*org meaning end*/

		/*grmma begin*/
		_txtOutDetail += "<p class='case'>";
		sCase = getLocalGrammaStr(sCase);
		var mGramma = sCase.split("#");
		if (mGramma.length >= 2) {
			mType = sCase.split("#")[0];
			mGramma = sCase.split("#")[1];
		}
		else {
			mType = "";
			mGramma = sCase.split("#")[0];
		}
		//_txtOutDetail += ("<span class='type'>"+mType+"</span> "+mGramma);
		if (mType != "") {
			_txtOutDetail += ("<span class='type'>" + mType + "</span>");
		}
		_txtOutDetail += mGramma;
		_txtOutDetail = _txtOutDetail + "&nbsp;" + "</p>";
		/*grmma end*/

		_txtOutDetail += "</div>";

	}

	//编辑模式开始
	if (g_useMode == "edit") {

		if (getNodeText(wordNode, "lock") == "true") {
			_bgColor += "  style='box-shadow: 0 3px 0 0 #FF0000'";
		}
		else {
			_bgColor += " class='bookmarkcolor0' style='box-shadow: 0 0 0 0'";
		}

		_txtOutDetail += "<div " + _bgColor + ">";

		/*id begin*/
		_txtOutDetail += "<p class='ID'>";
		_txtOutDetail += sId + "&nbsp;";
		_txtOutDetail += "</p>";
		/*id end*/

		/*gramma*/
		/*find in dict*/
		var arrGramma = new Array();
		var thisWord = getNodeText(wordNode, "real");
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali) {
				if ((g_DictWordList[iDict].Type != "" && g_DictWordList[iDict].Type != "?") || (g_DictWordList[iDict].Gramma != "" && g_DictWordList[iDict].Gramma != "?")) {
					var arrCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
					pushNewToList(arrGramma, arrCase);
				}
			}
		}

		if (sCase == "?" || sCase == "?#?") {
			if (arrGramma.length > 0) {
				setNodeText(xmlElement, "case", arrGramma[0]);
				sCase = arrGramma[0];
			}
			else {
				setNodeText(xmlElement, "case", "?#?");
				sCase = "?#?";
			}
		}
		else {
			if (sCase.indexOf("#") == -1) {
				sCase = "?#" + sCase;
				setNodeText(xmlElement, "case", "?#" + sCase);
			}
		}

		var currType = sCase.split("#")[0];
		var currGramma = sCase.split("#")[1];


		//end gramma

		/*meaning*/
		/*find in dict*/
		var arrMeaning = new Array();


		var currMeaning = "";
		var currGrammaMeaning = "";
		var wordParent = "";
		var wordGramma0 = "";
		//self meaning
		var thisWord = getNodeText(wordNode, "real");
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali && (g_DictWordList[iDict].Type != ".root." && g_DictWordList[iDict].Type != ".suf." && g_DictWordList[iDict].Type != ".prf.")) {
				if (wordParent == "" && g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
					wordParent = g_DictWordList[iDict].Parent;
					wordGramma0 = g_DictWordList[iDict].Gramma;
				}
				var tempCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
				if (sCase == tempCase && g_DictWordList[iDict].Mean.length > 0) {
					if (currGrammaMeaning == "") {
						if (dict_language_enable.indexOf(g_DictWordList[iDict].Language) >= 0) {
							currGrammaMeaning = g_DictWordList[iDict].Mean.split("$")[0];
						}
					}
				}
				var arrMean = g_DictWordList[iDict].Mean.split("$");
				for (var i = 0; i < arrMean.length; i++) {
					if (arrMean[i].length > 0 && arrMean[i] != "?") {
						//pushNewToList(arrMeaning,g_DictWordList[iDict].dictID+'$'+arrMeaning.length+'$$'+arrMean[i]);
						if (dict_language_enable.indexOf(g_DictWordList[iDict].Language) >= 0) {
							arrMeaning.push(g_DictWordList[iDict].dictID + '$' + arrMeaning.length + '$$' + arrMean[i] + "$" + g_DictWordList[iDict].Language);
						}
					}
				}
			}
		}


		var wordGramma1 = "";
		//find in father
		if (wordParent != "") {
			//add parent infomation
			if (sParent == "" || sParent == " ") {
				setNodeText(wordNode, "parent", wordParent);
				sParent = wordParent;
			}
			thisWord = wordParent;
			wordParent = "";
			wordGramma1 = "";
			for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
				if (thisWord == g_DictWordList[iDict].Pali && (g_DictWordList[iDict].Type != ".v." && g_DictWordList[iDict].Type != ".n." && g_DictWordList[iDict].Type != ".ti." && g_DictWordList[iDict].Type != ".adj." && g_DictWordList[iDict].Type != ".pron." && g_DictWordList[iDict].Type != ".num.")) {
					if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
						if (wordParent == "") {
							wordParent = g_DictWordList[iDict].Parent;
						}
						if (wordGramma1 == "") {
							wordGramma1 = g_DictWordList[iDict].Gramma;
						}
					}
					var arrMean = g_DictWordList[iDict].Mean.split("$");
					for (var i = 0; i < arrMean.length; i++) {
						if (arrMean[i].length > 0 && arrMean[i] != "?") {
							//pushNewToList(arrMeaning,g_DictWordList[iDict].dictID+'$'+"*$"+getLocalParentFormulaStr(wordGramma0,arrMean[i]));
							if (dict_language_enable.indexOf(g_DictWordList[iDict].Language) >= 0) {
								arrMeaning.push(g_DictWordList[iDict].dictID + '$' + arrMeaning.length + "$*$" + getLocalParentFormulaStr(wordGramma0, arrMean[i]) + "$" + (g_DictWordList[iDict].Language));
							}
						}
					}
				}
			}
		}

		//爷爷跟父亲的关系 比如 pp
		wordGranfatherGramma = wordGramma1;
		wordGranfather = wordParent;

		//grandfather
		var wordGramma2 = "";
		if (wordParent != "") {
			thisWord = wordParent;
			wordParent = "";
			wordGramma2 = "";
			for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
				if (thisWord == g_DictWordList[iDict].Pali && (g_DictWordList[iDict].Type != ".v." && g_DictWordList[iDict].Type != ".n." && g_DictWordList[iDict].Type != ".ti." && g_DictWordList[iDict].Type != ".adj." && g_DictWordList[iDict].Type != ".pron." && g_DictWordList[iDict].Type != ".num.")) {
					if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
						if (wordParent == "") {
							wordParent = g_DictWordList[iDict].Parent;
						}
						if (wordGramma2 == "") {
							wordGramma2 = g_DictWordList[iDict].Gramma;
						}
					}
					var arrMean = g_DictWordList[iDict].Mean.split("$");
					for (var i = 0; i < arrMean.length; i++) {
						if (arrMean[i].length > 0 && arrMean[i] != "?") {
							//pushNewToList(arrMeaning,g_DictWordList[iDict].dictID+'$'+"**"+getLocalParentFormulaStr(wordGramma1,arrMean[i]));
							if (dict_language_enable.indexOf(g_DictWordList[iDict].Language) >= 0) {
								arrMeaning.push(g_DictWordList[iDict].dictID + '$' + arrMeaning.length + "$**$" + getLocalParentFormulaStr(wordGramma1, arrMean[i]) + "$" + (g_DictWordList[iDict].Language));
							}
						}
					}
				}
			}
		}

		arrMeaning.sort(sortMeanByDictOrder);
		//arrMeaning.sort(sortMeanByLanguageOrder);
		newMeanList = removeSameWordInArray(arrMeaning);

		sMean = sMean.replace("_un_auto_mean_", getLocalGrammaStr("_un_auto_mean_"))

		if (sMean == "?") {
			//自动匹配一个意思
			//currGrammaMeaning是与语法信息最匹配的一个意思 如果使用这个 与语言排序冲突
			if (currGrammaMeaning.length > 0 && currGrammaMeaning != "?") {
				currMeaning = currGrammaMeaning;
			}
			else {
				if (newMeanList.length > 0) {
					currMeaning = newMeanList[0].word;
				}
				else {
					currMeaning = sMean;
				}
			}

		}
		else {
			currMeaning = removeFormula(sMean);
			currMeaning = getLocalFormulaStr(currGramma, currMeaning);
		}

		orgMeaning = removeFormula(currMeaning);
		if (sReal.length < 4) {
			currMeaning = getLocalFormulaStr(currGramma, cutString(orgMeaning, 24));
		}
		else {
			currMeaning = getLocalFormulaStr(currGramma, cutString(orgMeaning, sReal.length * 6));
		}
		setNodeText(wordNode, "mean", currMeaning);

		renderMeaning = currMeaning.replace(/{/g, "<span class='grm_add_mean'>");
		renderMeaning = renderMeaning.replace(/}/g, "</span>");
		renderMeaning = renderMeaning.replace(/\[/g, "<span class='grm_add_mean_user'>");
		renderMeaning = renderMeaning.replace(/\]/g, "</span>");

		//渲染下拉菜单

		_txtOutDetail += "<div class='mean'>";

		_txtOutDetail += render_word_mean_menu(wordNode);

		//render formula menu

		arrFormula = getFormulaList(currGramma);
		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<svg class='edit_icon';'><use xlink:href='svg/icon.svg#ic_more'></use></svg>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		newWord = removeFormula_B(orgMeaning);
		_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"[]" + newWord + "\")'>[None]</a>";
		_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"" + newWord + "\")'>[Auto]</a>";
		for (var i in arrFormula) {
			newWord = removeFormula_B(orgMeaning);
			newWord = arrFormula[i].replace("~", newWord);
			newWord = newWord.replace(/{/g, "[");
			newWord = newWord.replace(/}/g, "]");
			_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"mean\",\"" + newWord + "\")'>" + arrFormula[i] + "</a>";
		}

		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of meaning*/

		/*org begin 拆分*/
		/*
		_txtOutDetail +=  "<p class='org' name='w_org'>";
		_txtOutDetail +=   sOrg  + "&nbsp;";
		_txtOutDetail +=  "</p> ";
		*/
		/*find in dict*/

		var currOrg = "";
		var wordParent = "";
		var arrOrg = new Array();
		var thisWord = getNodeText(wordNode, "real");
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali) {
				if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
					wordParent = g_DictWordList[iDict].Parent;
				}
				var currOrg = g_DictWordList[iDict].Factors;
				if (currOrg.length > 0 && currOrg != "?") {
					pushNewToList(arrOrg, currOrg);
					//arrOrg.push(currOrg);
				}
			}
		}
		//father
		if (wordParent != "") {
			thisWord = wordParent;
			wordParent = "";
			wordGramma1 = "";
			for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
				if (thisWord == g_DictWordList[iDict].Pali) {
					if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
						if (wordParent == "") {
							wordParent = g_DictWordList[iDict].Parent;
						}
					}
					var currOrg = g_DictWordList[iDict].Factors;
					if (currOrg.length > 0 && currOrg != "?") {
						pushNewToList(arrOrg, currOrg);
						//arrOrg.push(currOrg);
					}
				}
			}
		}
		//grandfather
		if (wordParent != "") {
			thisWord = wordParent;
			wordParent = "";
			wordGramma2 = "";
			for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
				if (thisWord == g_DictWordList[iDict].Pali) {
					if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
						if (wordParent == "") {
							wordParent = g_DictWordList[iDict].Parent;
						}
					}
					var currOrg = g_DictWordList[iDict].Factors;
					if (currOrg.length > 0 && currOrg != "?") {
						pushNewToList(arrOrg, currOrg);
						//arrOrg.push(currOrg);
					}
				}
			}
		}

		_txtOutDetail += "<div class='org'  name='w_org'>";

		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";

		if (sOrg == "?") {
			if (arrOrg.length > 0) {
				currOrg = arrOrg[0];
			}
			else {
				currOrg = sOrg;
			}
			setNodeText(wordNode, "org", currOrg);
		}
		else {
			currOrg = sOrg;
		}

		_txtOutDetail += currOrg;
		if (arrOrg.length > 0) {

			_txtOutDetail += "<svg class='edit_icon'>";
			_txtOutDetail += "<use xlink:href='svg/icon.svg#ic_down'></use>";
			_txtOutDetail += "</svg>";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		_txtOutDetail += "<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" + wordID + "\",\"org\",\"\")'>" + gLocal.gui.empty1 + "</button>"
		_txtOutDetail += "<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='show_word_map(\"" + wordID + "\")'>" + gLocal.gui.wordmap + "</button>";
		//新加 base 信息 vn
		_txtOutDetail += "<div class=\"case_dropdown-org\">";
		_txtOutDetail += "<div class=\"case_dropdown-first\">";

		_txtOutDetail += "<a style='z-index:250; position:absolute; margin-right:2em;'>";
		_txtOutDetail += sParent + "</a>";
		_txtOutDetail += "<span style='z-index:220' class='case_dropdown-title'>";
		_txtOutDetail += gLocal.gui.more + "»</span>";
		_txtOutDetail += "</div>";

		for (i in arrOrg) {
			_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"org\",\"" + arrOrg[i] + "\")'>" + arrOrg[i] + "</a>";
		}
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of factors*/

		/*part meaning begin*/
		listFactorForFactorMean = currOrg.split("+");
		currDefualtFM = "";
		for (iFactor in listFactorForFactorMean) {
			currDefualtFM += findFirstMeanInDict(listFactorForFactorMean[iFactor]) + "+";//拆分元素加号分隔
		}
		currDefualtFM = currDefualtFM.replace(/"  "/g, " ");
		currDefualtFM = currDefualtFM.replace(/"+ "/g, "+");
		currDefualtFM = currDefualtFM.replace(/" +"/g, "+");
		currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1);//去掉尾部的加号 kosalla

		if (currDefualtFM.slice(-1, -2) == "+") {
			currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1);
		}


		/*find in dict*/

		var currOM = "";
		var wordParent = "";
		var arrOM = new Array();
		var thisWord = getNodeText(wordNode, "real");
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali) {
				if (g_DictWordList[iDict].Parent.length > 0 && g_DictWordList[iDict].Parent != thisWord) {
					wordParent = g_DictWordList[iDict].Parent;
				}
				var currOM = g_DictWordList[iDict].FactorMean;
				if (currOM.length > 0 && currOM != "?") {
					pushNewToList(arrOM, currOM);
					//arrOM.push(currOM);
				}
			}
		}

		/*	
			//father
			if(wordParent!=""){
				thisWord=wordParent;
				wordParent="";
				for(iDict=0;iDict<g_DictWordList.length;iDict++){
					if(thisWord==g_DictWordList[iDict].Pali){
						if(g_DictWordList[iDict].Parent.length>0 && g_DictWordList[iDict].Parent!=thisWord){
							if(wordParent==""){
								wordParent=g_DictWordList[iDict].Parent;
							}
						}
						var currOM=g_DictWordList[iDict].FactorMean;
						if(currOM.length>0 && currOM!="?"){
							pushNewToList(arrOM,currOM);
						}
					}
				}	
			}
			//grandfather
			if(wordParent!=""){
				thisWord=wordParent;
				wordParent="";
				wordGramma2="";
				for(iDict=0;iDict<g_DictWordList.length;iDict++){
					if(thisWord==g_DictWordList[iDict].Pali){
						if(g_DictWordList[iDict].Parent.length>0 && g_DictWordList[iDict].Parent!=thisWord){
							if(wordParent==""){
								wordParent=g_DictWordList[iDict].Parent;
							}
						}
						var currOM=g_DictWordList[iDict].FactorMean;
						if(currOM.length>0 && currOM!="?"){
							pushNewToList(arrOM,currOM);
						}
					}
				}	
			}
		*/
		_txtOutDetail += "<div class='om'  name='w_om'>";

		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		sOm = sOm.replace("_un_auto_factormean_", getLocalGrammaStr("_un_auto_factormean_"))

		if (sOm == "?" || sOm.substring(0, 3) == "[a]") {
			if (arrOM.length > 0) {
				currOM = arrOM[0];
			}
			else {
				currOM = "[a]" + currDefualtFM;
			}
			setNodeText(wordNode, "om", currOM);
		}
		else {
			currOM = sOm;
		}

		_txtOutDetail += currOM;
		if (arrOM.length > 0) {
			_txtOutDetail += " ▾";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		_txtOutDetail += "<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" + wordID + "\",\"om\",\"\")'>" + gLocal.gui.empty1 + "</button>";
		_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"om\",\"[a]" + currDefualtFM + "\")'>[" + gLocal.gui.auto + "]" + currDefualtFM + "</a>";
		for (i in arrOM) {
			_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"om\",\"" + arrOM[i] + "\")'>" + arrOM[i] + "</a>";
		}
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";

		/*org meaning end*/


		/*begin gramma*/

		_txtOutDetail += "<div class='case'>";


		_txtOutDetail += "<div class=\"case_dropdown\">";
		_txtOutDetail += "<p class='case_dropbtn' >";
		sLocalCase = getLocalGrammaStr(sCase);
		var mArrGramma = sCase.split("#");
		if (mArrGramma.length >= 2) {
			mType = mArrGramma[0];
			mGramma = mArrGramma[1];
			mLocalType = sLocalCase.split("#")[0];
			mLocalGramma = sLocalCase.split("#")[1];
		}
		else {
			mType = "";
			mGramma = mArrGramma[0];
			mLocalType = "";
			mLocalGramma = sLocalCase.split("#")[0];
		}

		if (mType != "") {
			_txtOutDetail += ("<span class='cell'>" + mLocalType + "</span>");
		}
		_txtOutDetail += cutString(mLocalGramma, 30);

		_txtOutDetail += "</p>";

		_txtOutDetail += "<div class=\"case_dropdown-content\">";
		for (i in arrGramma) {
			_txtOutDetail += "<a onclick='fieldListChanged(\"" + wordID + "\",\"case\",\"" + arrGramma[i] + "\")'>" + cutString(getLocalGrammaStr(arrGramma[i]), 30) + "</a>";
		}
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";


		//连读词按钮
		if (mType == ".un." || mType == ".comp.") {
			nextElement = com_get_nextsibling(xmlElement);
			if (nextElement != null) {//下一个元素存在
				if (getNodeText(nextElement, "un") == sId) {//若有孩子則显示收起按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_remove(\"" + wordID + "\")'>";
					_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_join \"></use></svg>";
					_txtOutDetail += "</button>";
					var parentElement = document.getElementById('wb' + sId);
					if (parentElement) {
						parentElement.classList.add("un_parent");
					}
				}
				else {//無kid展開按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
					_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_split \"></use></svg>";
					_txtOutDetail += "</button> ";
				}
			}
			else {//下一个元素不存在
				_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
				_txtOutDetail += "<svg class=\"icon\" ><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_split \"></use></svg>";
				_txtOutDetail += "</button> ";
			}
		}
		//连读词按钮 结束
		//爷爷和父亲的关系 如pp
		if (wordGranfatherGramma.length > 0 && wordGranfatherGramma.length < 6) {
			_txtOutDetail += "<span  class=\"tooltip\">«" + getLocalGrammaStr(wordGranfatherGramma) + "<span class=\"tooltiptext tooltip-bottom\">" + wordGranfather + "</span>" + "</span> ";
		}

		_txtOutDetail += "</div> ";
		/*end of gramma*/

		//Auto Match Finished	

		_txtOutDetail += "</div>";

	}

	return (_txtOutDetail);
}

function renderWordNoteDivByParaNo(book, paragraph) {

}
/*
paragraph word note
*/
function renderWordNoteDivByElement(elementBlock) {
	let output = "";
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book")
	let paragraph = getNodeText(xmlParInfo, "paragraph")
	let bId = getNodeText(xmlParInfo, "id")

	let type = getNodeText(xmlParInfo, "type")
	let allWord = elementBlock.getElementsByTagName("word")
	let iNoteCounter = 0;
	for (iWord = 0; iWord < allWord.length; iWord++) {
		let oneNote = "";
		let pali = getNodeText(allWord[iWord], "pali");
		let sNote = getNodeText(allWord[iWord], "note");
		if (sNote != "") {
			oneNote += note_init(sNote);
		}
		let sRela = getNodeText(allWord[iWord], "rela");
		if (sRela != "") {
			oneNote += "<div>" + renderWordRelation(allWord[iWord]) + "</div>";
		}
		id = getNodeText(allWord[iWord], "id");
		if (oneNote == "") {//有内容
			$("#wnote_root_" + id).html("");
		}
		else {
			$("#wnote_root_" + id).html("<wnh wid='" + id + "'></wnh>");
			output += "<div>";
			output += "<wnc wid='" + id + "'></wnc>";
			output += "<strong>" + pali + ":</strong>";
			output += oneNote;
			output += "</div>";
		}
	}
	return (output);
}
function refreshWordNoteDiv(elementBlock) {
	let html = renderWordNoteDivByElement(elementBlock);
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book")
	let paragraph = getNodeText(xmlParInfo, "paragraph")
	try {
		if (html == "") {
			document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "none";
		}
		else {
			document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "block";
		}
		document.getElementById("wnote_" + book + "_" + (paragraph - 1)).innerHTML = html;
	}
	catch (e) {
		console.error(e.message);
		console.error("wnote_" + book + "_" + (paragraph - 1));
	}

}

function refreshWordNote(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	book = getNodeText(xmlParInfo, "book")
	paragraph = getNodeText(xmlParInfo, "paragraph")
	bId = getNodeText(xmlParInfo, "id")

	type = getNodeText(xmlParInfo, "type")
	var allWord = elementBlock.getElementsByTagName("word")
	var iNoteCounter = 0;
	for (iWord = 0; iWord < allWord.length; iWord++) {
		pali = getNodeText(allWord[iWord], "pali");
		wnote = note_init(getNodeText(allWord[iWord], "note"));
		id = getNodeText(allWord[iWord], "id");
		var note = wnote;
		if (note.length > 0 && note != "?") {
			iNoteCounter++;
			if (wnote.substring(0, 6) == "=term(") {
				var termId = wnote.slice(6, -1);
				//alert(arrTerm.length);
				if (arrTerm[note]) {
					note = "<term guid='" + termId + "' pos='wbw'>" + arrTerm[termId].note + "</term>";
				}
				else {
					note = "<term guid='" + termId + "' pos='wbw'></term>";
				}
			}

			output += "<p><a href=\"#word_note_root_" + id + "\" name=\"word_note_" + id + "\">[" + iNoteCounter + "]</a>"
			if (note.match("{") && note.match("}")) {
				note = note.replace("{", "<strong>");
				note = note.replace("}", "</strong>");
				if (note.length > 100) {
					shortNote = note.substring(0, 99);
					otherNode = note.substring(100);
					output += shortNote + "<span class=\"full_note_handle\">...<span class=\"full_note\">" + otherNode + "</span></span>";
				}
				else {
					output += note;
				}
			}
			else {
				if (note.length > 100) {
					shortNote = note.substring(0, 99);
					otherNode = note.substring(100);
					output += shortNote + "<span class=\"full_note_handle\">...<span class=\"full_note\">" + otherNode + "</span></span>";
				}
				else {
					output += "<strong>" + pali + ":</strong>" + note;
				}
			}

			output += "</p>";
			if (termId) {
				document.getElementById("wnote_root_" + id).innerHTML = "<a onclick=\"term_show_win('" + termId + "')\" name=\"word_note_root_" + id + "\">[" + iNoteCounter + "]</a>"
			}
			else {
				document.getElementById("wnote_root_" + id).innerHTML = "<a href=\"#word_note_" + id + "\" name=\"word_note_root_" + id + "\">[" + iNoteCounter + "]</a>";
			}
		}
		else {
			noteRootObj = document.getElementById("wnote_root_" + id);
			if (noteRootObj) {
				document.getElementById("wnote_root_" + id).innerHTML = "";
			}
		}
	}
	if (output == "") {
		document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "none";
	}
	else {
		document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "block";
	}
	document.getElementById("wnote_" + book + "_" + (paragraph - 1)).innerHTML = output
}

function updateWordNote(element) {
	let paliword = getNodeText(element, "real");
	let wnote = getNodeText(element, "note");
	wnote = note_init(wnote);
	let id = getNodeText(element, "id");

	if (wnote == "") {
		$("#wnote_root_" + id).html("");
		$("#wnn_" + id).html("");
	}
	else {
		$("#wnote_root_" + id).html("<wnh wid='" + id + "'></wnh>");
		let output = "";
		output += "<wnc wid='" + id + "'></wnc>";
		let head = "";
		let content = "";
		if (wnote.match("{") && wnote.match("}")) {
			wnote = wnote.replace("{", "<strong>");
			wnote = wnote.replace("}", "</strong>");
		}
		else {
			output += "<strong>" + paliword + ":</strong>";
		}
		output += wnote;

		$("#wnn_" + id).html(output);
	}


}





function updateWordCommentary(element) {

}

//根据xmlDocument 对象中的单词序号修改单词块的显示（不含Pali）
//返回 无	
function updataWordBodyByElement(element) {
	try {
		var sId = getNodeText(wordNode, "id");
		var sDetail = "detail" + sId;
		var cDetail = document.getElementById(sDetail);
		if (cDetail != null) {

			cDetail.innerHTML = renderWordDetailByElement(element);
		}
	}
	catch (error) {
		var_dump(error);
	}
}

//根据xmlDocument 对象中的单词序号修改单词块的显示（不含Pali）
//返回 无	
function modifyWordDetailByWordId(wordId) {
	try {
		var sDetail = "detail" + wordId;
		var cDetail = document.getElementById(sDetail);
		if (cDetail != null) {

			cDetail.innerHTML = renderWordDetailById(wordId);
		}
	}
	catch (error) {
		var_dump(error);
	}
}

//根据xmlDocument 对象中的单词GUID返回单词块字符串（不含Pali）
//返回 字符串	

function renderWordDetailById(wordID) {
	var mXML = gXmlBookDataBody.getElementsByTagName("word");
	var wordIndex = getWordIndex(wordID);

	return (renderWordDetailByElement(mXML[wordIndex]));
}


function reloadPar(parIndex) {
	bookId = gArrayDocParagraph[parIndex].book
	parNo = gArrayDocParagraph[parIndex].paragraph

	var htmlBlock = document.getElementById("par_" + bookId + "_" + (parNo - 1))
	if (htmlBlock.style.display != "none") {
		return (false);
	}

	allBlock = gXmlBookDataBody.getElementsByTagName("block")
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		currBookId = getNodeText(xmlParInfo, "book")
		currParNo = getNodeText(xmlParInfo, "paragraph")
		if (bookId == currBookId && currParNo == parNo) {
			insertBlockToHtml(allBlock[iBlock]);
		}
	}
	return (true);
}

function removePar(parIndex) {
	bookId = gArrayDocParagraph[parIndex].book
	parNo = gArrayDocParagraph[parIndex].paragraph
	if (gCurrModifyWindowParNo == parIndex) {
		//关闭单词修改窗口
		closeModifyWindow();
	}

	var htmlBlock = document.getElementById("par_" + bookId + "_" + (parNo - 1))
	if (htmlBlock) {
		htmlBlock.style.display = "none";
		document.getElementById("heading_" + bookId + "_" + (parNo - 1)).innerHTML = "";
		document.getElementById("wblock_" + bookId + "_" + (parNo - 1)).innerHTML = "";
		document.getElementById("tran_" + bookId + "_" + (parNo - 1)).innerHTML = "";
		document.getElementById("wnote_" + bookId + "_" + (parNo - 1)).innerHTML = "";
		document.getElementById("note_" + bookId + "_" + (parNo - 1)).innerHTML = "";
	}

}

function setView(topParIndex) {
	var parInOnePage = 3;
	gCurrTopParagraph = topParIndex;
	gVisibleParBegin = topParIndex - parInOnePage;
	gVisibleParEnd = parInOnePage * 2;
	if (gVisibleParBegin < 0) {
		gVisibleParBegin = 0;
	}
	if (gVisibleParEnd >= gArrayDocParagraph.length) {
		gVisibleParEnd = gArrayDocParagraph.length - 1;
	}
}



function isParInView(parIndex) {
	if (parIndex >= gVisibleParBegin && parIndex <= gVisibleParEnd) {
		return (true);
	}
	else {
		return (false);
	}
}
function refresh_Dispaly_Cap() {
	var new_par_length = gPalitext_length / gtext_max_length;

	for (refresh_i in allTextLen_array) {
		gNewArrayDocParagraph.push(allTextLen_array[refresh_i].length / gtext_max_length);
	}
}
function prev_page() {//向上翻页
	gVisibleParBeginOld = gVisibleParBegin;
	gVisibleParEndOld = gVisibleParEnd;
	if (g_allparlen_array[gVisibleParEnd - 1] - g_allparlen_array[gVisibleParBegin - 1] <= gDisplayCapacity) {
		gVisibleParBegin -= 1;
	}
	else if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin - 1] > gDisplayCapacity) {
		gVisibleParBegin -= 1;
		gVisibleParEnd -= 1;
	}

	if (gVisibleParBegin < 0) {//如果到顶，则恢复原始值
		gVisibleParBegin = 0;
		//gVisibleParEnd=gDisplayCapacity;
		if (gVisibleParEnd >= gArrayDocParagraph.length) {//如果到底，则锁死最大值
			gVisibleParEnd = gArrayDocParagraph.length - 1;
		}
	}

	updataView();
}

function next_page() {//向下翻页
	gVisibleParBeginOld = gVisibleParBegin;
	gVisibleParEndOld = gVisibleParEnd;

	if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin + 1] <= gDisplayCapacity) {
		gVisibleParEnd += 1;
	}
	else if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin + 1] > gDisplayCapacity) {
		gVisibleParBegin += 1;
		gVisibleParEnd += 1;
	}

	if (gVisibleParEnd >= gArrayDocParagraph.length) {//如果到底
		gVisibleParEnd = gArrayDocParagraph.length - 1;
		//gVisibleParBegin=gVisibleParEnd-gDisplayCapacity;
		if (gVisibleParBegin < 0) {
			gVisibleParBegin = 0;
		}
	}
	updataView()
}

function setNewView(newBegin, newEnd) {
	gVisibleParBeginOld = gVisibleParBegin;
	gVisibleParEndOld = gVisibleParEnd;

	gVisibleParBegin = newBegin;
	gVisibleParEnd = newEnd;

	if (gVisibleParBegin < 0) {
		gVisibleParBegin = 0;
		//gVisibleParEnd=gDisplayCapacity;
		if (gVisibleParEnd >= gArrayDocParagraph.length) {
			gVisibleParEnd = gArrayDocParagraph.length - 1;
		}
	}

	if (gVisibleParEnd >= gArrayDocParagraph.length) {
		gVisibleParEnd = gArrayDocParagraph.length - 1;
		//gVisibleParBegin=gVisibleParEnd-gDisplayCapacity;
		if (gVisibleParBegin < 0) {
			gVisibleParBegin = 0;
		}
	}
	updataView()
}

function updataView() {
	var topNewDivArray = Array();//在顶端新加入的块列表
	for (var iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		if (isParInView(iPar)) {
			isLoadNew = reloadPar(iPar);
			if (isLoadNew) {
				if (iPar < gVisibleParBeginOld) {
					topNewDivArray.push(iPar);
				}
			}
		}
		else {
			removePar(iPar)
		}
	}
	word_mouse_event();
	//console.log("updataview");
	console.log(topNewDivArray.toString());
}

function render_word_mean_menu2(spell) {

	var wReal = spell;

	var output = "<div>";
	for (var iDict = 0; iDict < mDict[spell].length; iDict++) {
		output += "<div>";
		output += "<span>dict_name</span>";
		var meanGroup = partList2[i].mean.split('$');
		for (var iMean = 0; iMean < meanGroup.length; iMean++) {
			output += "<span class='wm_one_mean' onclick='fieldListChanged(\"" + wId + "\",\"mean\",\"" + meanGroup[iMean] + "\")'>" + meanGroup[iMean] + "</span>";
		}
		output += "</div>";
	}
	output += "</div>";

	return;

	var sWord = new Array(wReal);
	sWord = sWord.concat(render_get_word_parent_list(wReal));
	for (var iWord in sWord) {
		output += "<div class=\"case_dropdown-first\">";
		output += "<a style=\"z-index:250; position:absolute; margin-right:2em;\" onclick='dict_search(\"" + sWord[iWord] + "\")'>";
		output += sWord[iWord] + "</a>"
		output += "<span style=\"z-index:220\" class=\"case_dropdown-title\" onclick=\"submenu_show_detail(this)\">";
		output += "<svg class=\"icon\" style=\"fill:var(--main-color)\"><use xlink:href=\"svg/icon.svg#ic_add\"></use></svg>";
		output += "</span><div class=\"case_dropdown-detail\">";
		var partList = render_get_word_mean_list(sWord[iWord])
		var L_width_mean = 0

		var partList2 = repeat_combine(partList);
		//计算字符长度并控制
		for (var i in partList2) {
			var L_width = getLocalDictname(partList2[i].mean).replace(/[\u0391-\uFFE5]/g, "aa").length
			if (L_width_mean < L_width / 1.7) {
				L_width_mean = L_width / 1.7
			}

		}
		for (var i in partList2) {
			var meanGroup = partList2[i].mean.split('$');
			var htmlMean = "";
			output += "<a style='display:flex; flex-wrap: wrap;'>";
			output += "<div id='div_dictname_" + wId + "_" + iWord + "_" + i + "' style='margin-right: auto; display:flex;'>"
			//output  += "<a ><div id='div_dictname_"+wId+"_"+iWord+"_"+i+"' style='width: "+L_width_dictname+"em; display:flex'>"
			output += "<span id='span_dictname_" + wId + "_" + iWord + "_" + i + "'";
			output += "style='height: 1.5em;' class='wm_dictname' >";//onclick='fieldListChanged1(\""+wId+"\",\"mean\",\""+partList2[i].mean+"\")'
			output += getLocalDictname(partList2[i].dict) + "</span>"
			output += "</div>"
			output += "<div id='div_type_" + wId + "_" + iWord + "_" + i + "' style='margin-left: 0.4em; display:flex'>"
			//output  += "<div id='div_type_"+wId+"_"+iWord+"_"+i+"' style='width: "+L_width_type+"em; display:flex'>"
			output += "<span id='span_type_" + wId + "_" + iWord + "_" + i + "' style='height: 1.5em;' class='wm_wordtype'>" + getLocalGrammaStr(partList2[i].type) + "</span>"

			for (var iMean in meanGroup) {
				if (meanGroup[iMean] != "") {
					htmlMean += "<span class='wm_one_mean' onclick='fieldListChanged(\"" + wId + "\",\"mean\",\"" + meanGroup[iMean] + "\")'>" + meanGroup[iMean] + "</span>";
				}
			}
			output += "</div><div style='width:15em; display:flex; flex-wrap: wrap;'>" + htmlMean + "</div>";
			output += "</a>";
		}
		output += "</div></div>";
	}
	output += "</div></div></div>";
	return (output);
}


function render_word_mean_menu(xElement) {
	var wMean = getNodeText(xElement, "mean");
	var wReal = getNodeText(xElement, "real");
	var wId = getNodeText(xElement, "id");
	var renderMeaning = wMean.replace(/{/g, "<span class='grm_add_mean'>");
	renderMeaning = renderMeaning.replace(/}/g, "</span>");
	renderMeaning = renderMeaning.replace(/\[/g, "<span class='grm_add_mean_user'>");
	renderMeaning = renderMeaning.replace(/\]/g, "</span>");

	var output = "";
	output += "<div class=\"case_dropdown\">";
	output += "<p class='case_dropbtn' >";
	output += renderMeaning;
	output += "</p>";
	output += "<div class=\"case_dropdown-content\">";
	output += "<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" + wId + "\",\"mean\",\"\")'>" + gLocal.gui.empty1 + "</button><div class=\"case_dropdown-org\">"

	var sWord = new Array(wReal);
	sWord = sWord.concat(render_get_word_parent_list(wReal));
	for (var iWord in sWord) {
		output += "<div class=\"case_dropdown-first\">";
		output += "<a style=\"z-index:250; position:absolute; margin-right:2em;\" onclick='dict_search(\"" + sWord[iWord] + "\")'>";
		output += sWord[iWord] + "</a>"
		output += "<span style=\"z-index:220\" class=\"case_dropdown-title\" onclick=\"submenu_show_detail(this)\">";
		output += "<svg class=\"icon\" style=\"fill:var(--main-color)\"><use xlink:href=\"svg/icon.svg#ic_add\"></use></svg>";
		output += "</span>";
		output += "<div class=\"case_dropdown-detail\">";

		var partList = render_get_word_mean_list(sWord[iWord])
		var L_width_mean = 0

		var partList2 = repeat_combine(partList);

		for (var i in partList2) {
			var L_width = getLocalDictname(partList2[i].mean).replace(/[\u0391-\uFFE5]/g, "aa").length
			if (L_width_mean < L_width / 1.7) {
				L_width_mean = L_width / 1.7
			}

		}
		for (var i in partList2) {
			var meanGroup = partList2[i].mean.split('$');
			var htmlMean = "";
			output += "<a style='display:flex; flex-wrap: wrap;'>";
			output += "<div id='div_dictname_" + wId + "_" + iWord + "_" + i + "' style='margin-right: auto; display:flex;'>"
			output += "<span id='span_dictname_" + wId + "_" + iWord + "_" + i + "'";
			output += "style='height: 1.5em;' class='wm_dictname' >";//onclick='fieldListChanged1(\""+wId+"\",\"mean\",\""+partList2[i].mean+"\")'
			output += getLocalDictname(partList2[i].dict) + "</span>"
			output += "</div>"
			output += "<div id='div_type_" + wId + "_" + iWord + "_" + i + "' style='margin-left: 0.4em; display:flex'>"
			output += "<span id='span_type_" + wId + "_" + iWord + "_" + i + "' style='height: 1.5em;' class='wm_wordtype'>";
			if (partList2[i].type == ".n:base.") {
				output += getLocalGrammaStr(partList2[i].gramma);
			}
			else {
				output += getLocalGrammaStr(partList2[i].type);
			}

			output += "</span>";

			for (var iMean in meanGroup) {
				if (meanGroup[iMean] != "") {
					htmlMean += "<span class='wm_one_mean' onclick='fieldListChanged(\"" + wId + "\",\"mean\",\"" + meanGroup[iMean] + "\")'>" + meanGroup[iMean] + "</span>";
				}
			}
			output += "</div>";
			output += "<div style='width:15em; display:flex; flex-wrap: wrap;'>" + htmlMean + "</div>";
			output += "</a>";
		}
		output += "</div></div>";
	}
	output += "</div></div></div>";
	return (output);
}

function render_get_word_mean_list(sWord) {
	var wReal = sWord;

	var output = Array();

	for (var x in g_DictWordList) {
		if (g_DictWordList[x].Pali == wReal || g_DictWordList[x].Real == wReal) {
			if (dict_language_enable.indexOf(g_DictWordList[x].Language) >= 0) {
				if (g_DictWordList[x].Mean != "" && g_DictWordList[x].Type != ".part." && g_DictWordList[x].Type != ".root." && g_DictWordList[x].Type != ".suf." && g_DictWordList[x].Type != ".pfx.") {
					var newPart = Object();
					newPart.dict = g_DictWordList[x].dictname;
					newPart.mean = g_DictWordList[x].Mean;
					newPart.type = g_DictWordList[x].Type;
					var bIsExist = false;
					for (var iBuffer in output) {
						if (output[iBuffer].dict == newPart.dict && output[iBuffer].type == newPart.type && output[iBuffer].mean == newPart.mean) {
							bIsExist = true;
							break;
						}
						if (newPart.dict == "wbw") {
							if (output[iBuffer].dict == newPart.dict && output[iBuffer].type == newPart.type) {
								output[iBuffer].mean += "$" + newPart.mean;
								bIsExist = true;
								break;
							}
						}
					}
					if (!bIsExist) {
						output.push(newPart);
					}
				}
			}
		}
	}
	return (output);
}

function render_get_word_parent_list(sWord) {
	var wReal = sWord;

	var output = Array();

	for (var x in g_DictWordList) {
		if (g_DictWordList[x].Pali == wReal) {
			{
				if (g_DictWordList[x].Parent != "" && g_DictWordList[x].Parent != sWord) {
					var newPart = g_DictWordList[x].Parent;
					var bIsExist = false;
					for (var iBuffer in output) {
						if (output[iBuffer] == newPart) {
							bIsExist = true;
							break;
						}
					}
					if (!bIsExist) {
						output.push(newPart);
					}
				}
			}
		}
	}
	return (output);

}
function repeat_combine(list) {
	var list_new = new Array();

	for (var repeat_combine_i = 0; repeat_combine_i < list.length; repeat_combine_i++) {
		var repeat_string = new Object;
		var repeat_combine_j = 0;
		repeat_string.dict = list[repeat_combine_i].dict;
		repeat_string.type = list[repeat_combine_i].type;
		repeat_string.mean = list[repeat_combine_i].mean;
		for (repeat_combine_j = repeat_combine_i + 1; repeat_combine_j < list.length; repeat_combine_j++) {
			if (list[repeat_combine_j].dict == list[repeat_combine_i].dict && list[repeat_combine_j].type == list[repeat_combine_i].type) {
				repeat_string.mean = repeat_string.mean + "$" + list[repeat_combine_j].mean;
				repeat_string.mean = repeat_string.mean.replace(/\$\$/g, "$");
				repeat_string.mean = repeat_string.mean.replace(/\$ /g, "$");
				repeat_string.mean = repeat_string.mean.replace(/ \$/g, "$");
				repeat_combine_i = repeat_combine_j
			}

		}
		repeat_string.mean = "#" + repeat_string.mean + "#";
		repeat_string.mean = repeat_string.mean.replace(/ #/g, "");
		repeat_string.mean = repeat_string.mean.replace(/# /g, "");
		repeat_string.mean = repeat_string.mean.replace(/\$#/g, "");
		repeat_string.mean = repeat_string.mean.replace(/#\$/g, "");
		repeat_string.mean = repeat_string.mean.replace(/  /g, "");
		repeat_string.mean = repeat_string.mean.replace(/#/g, "");
		list_new.push(repeat_string);
	}

	return (list_new);
}

function show_pop_note(wordid) {
	$("#word_note_pop_content").html(note_init(doc_word("#" + wordid).val("note")));
	$("#word_note_pop").show("500");
}

function refreshNoteNumber() {
	$("wnh").each(function (index, element) {
		let id = $(this).attr("wid");
		//$(this).html("<a href='#word_note_"+id+"'  name=\"word_note_root_"+id+"\">"+(index+1)+"</a>");
		$(this).html("<span onclick=\"show_pop_note('" + id + "')\">" + (index + 1) + "</span>");
	});

	$("wnc").each(function (index, element) {
		let id = $(this).attr("wid");
		$(this).html("<a href='#word_note_root_" + id + "' name=\"word_note_" + id + "\">[" + (index + 1) + "]</a>");
	});

	note_refresh_new();

}
