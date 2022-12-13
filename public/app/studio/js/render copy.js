var gRenderPageLimit = 1; //限制 1 不限制 设为1000

var gDisplayCapacity = 20 * gRenderPageLimit;
var gCurrTopParagraph = 0;
var gVisibleParBegin = 0;
var gVisibleParEnd = 10 * gRenderPageLimit;

//显示模式
var _display_para_arrange = 0; //0:横向 排列 1:纵向排列
var _display_sbs = 0; //0:逐段  1:逐句

//翻译块显示模式
//在编辑状态下显示预览
var _tran_show_preview_on_edit = true;
//在非编辑状态下显示编辑框
var _tran_show_textarea_esc_edit = true;

var gVisibleParBeginOld = 0;
var gVisibleParEndOld = gDisplayCapacity;
//var gPalitext_length=0
var gtext_max_length = 0;
var g_allparlen_array = new Array();
function palitext_calculator() {
	var allText = "";
	var allTextLen_array = new Array();
	var text_max2_length = 0;
	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		//bookId=getNodeText(xmlParInfo,"book")
		//paragraph=getNodeText(xmlParInfo,"paragraph")
		type = getNodeText(xmlParInfo, "type");
		if (type == "wbw") {
			var wbwTextNode = xmlParData.getElementsByTagName("word");
			var para_text_cal = "";
			for (var iText = 0; iText < wbwTextNode.length; iText++) {
				if (getNodeText(wbwTextNode[iText], "type") != ".ctl.") {
					para_text_cal += getNodeText(wbwTextNode[iText], "pali") + " ";
				}
			}
			allText += para_text_cal;
			allTextLen_array.push(allText.length);
			//gPalitext_length+=para_text_cal.length
			if (para_text_cal.length > text_max2_length) {
				text_max2_length = para_text_cal.length;
				if (text_max2_length > gtext_max_length) {
					var num_max_tem = gtext_max_length;
					gtext_max_length = text_max2_length;
					text_max2_length = num_max_tem;
				}
			}
		}
	}
	for (i_cal in allTextLen_array) {
		g_allparlen_array.push(allTextLen_array[i_cal] / gtext_max_length);
	}
	gDisplayCapacity = 19 * gRenderPageLimit + text_max2_length / gtext_max_length;
	if (gDisplayCapacity * gtext_max_length < 5000 * gRenderPageLimit) {
		gDisplayCapacity = (5000 * gRenderPageLimit) / gtext_max_length;
	}
}
//添加新的段落块
function addNewBlockToHTML(bookId, parId, begin = -1, end = -1) {
	parHeadingLevel = 0;
	parTitle = "";
	var divBlock = document.createElement("div");
	var typId = document.createAttribute("id");
	if (begin == -1 && end == -1) {
		//基于段落的块
		typId.nodeValue = "par_" + bookId + "_" + (parId - 1);
	} else {
		//基于句子的块
		typId.nodeValue = "par_" + bookId + "_" + (parId - 1) + "_" + begin + "_" + end;
	}

	divBlock.attributes.setNamedItem(typId);
	var typClass = document.createAttribute("class");
	typClass.nodeValue = "pardiv";
	divBlock.attributes.setNamedItem(typClass);

	var output = '<a name="par_begin_' + bookId + "_" + (parId - 1) + '"></a>';
	output += '	<div id="head_tool_' + bookId + "_" + (parId - 1) + '" class="head_tool edit_tool">';
	output +=
		'	<button id="id_heading_add_new" onclick="editor_heading_add_new(\'' +
		bookId +
		"','" +
		parId +
		"')\" >" +
		gui_string_editor[0] +
		"</button>";
	output +=
		"<input id='page_break_'" +
		bookId +
		"_" +
		(parId - 1) +
		' type="checkbox" onclick="editor_page_break(this,\'' +
		bookId +
		"','" +
		(parId - 1) +
		"')\" />" +
		gui_string_editor[1];
	output += "</div>";

	//heading
	output += '	<div id="heading_' + bookId + "_" + (parId - 1) + '" class="par_heading"></div>';

	//word by word translate block
	//内部可以包含多个wbw块
	output += '	<div id="wblock_' + bookId + "_" + (parId - 1) + '" class="wbwdiv"></div>';

	//translate div
	output += '	<div id="tran_' + bookId + "_" + (parId - 1) + '" class="trandiv"></div>';

	//word note div
	output += '	<div id="wnote_' + bookId + "_" + (parId - 1) + '" class="wnotediv"></div>';

	//note div
	output += '	<div id="note_' + bookId + "_" + (parId - 1) + '" class="pnotediv"></div>';

	//vedio div
	output += '	<div id="vedioblock_' + bookId + "_" + (parId - 1) + '" class="vediodiv"></div>';

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
			output += '<li class="toc_h_' + (parHeadingLevel - firstHeadingLevel + 1) + '">';
			output += '<a href="#par-' + bookId + "-" + iPar + '">' + parTitle + "</a>";
			output += "</li>";
		}
	}
	output += "</ul>";
	return output;
}

function updataWordParContainer(bookId, parId) {
	document.getElementById("wblock-" + bookId + "-" + parId + '"').innerHTML = renderWordParContainerInner(
		bookId,
		parId
	);
}

function renderWordParContainerInner(bookId, parId) {
	var strHtml = "";
	strHtml += '<div class="wbwparblock">';
	strHtml += renderWordParBlockInner(packageIndex);
	strHtml += "</div>";
}

//根据block数据更新 目录 列表
function updataToc() {
	document.getElementById("content").innerHTML = "";
	//创建目录空壳
	for (let iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		if (gArrayDocParagraph[iPar].level > 0) {
			let bookId = gArrayDocParagraph[iPar].book;
			let parIndex = gArrayDocParagraph[iPar].paragraph;
			let tocId = "toc_" + bookId + "_" + (parIndex - 1);
			let str = '<div class="toc_heading">';
			str += "<table><tr><td>";
			str +=
				"<input type='checkbox' checked onclick=\"editor_par_show(this,'" +
				bookId +
				"','" +
				parIndex +
				"')\" />";
			str += "</td><td>";
			str += '<div class="toc_heading_inner" id="' + tocId + '">';
			str += "<a onclick=\"editor_goto_link('" + bookId + "'," + parIndex + ')" >[' + parIndex + "]</a>";
			str += "</div>";
			str += "</td></tr></table>";
			str += "</div>";
			document.getElementById("content").innerHTML += str;
		}
	}

	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (let iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		type = getNodeText(xmlParInfo, "type");
		if (type == "heading") {
			level = getNodeText(xmlParInfo, "level");
			//if(level>0)
			{
				language = getNodeText(xmlParInfo, "language");
				bId = getNodeText(xmlParInfo, "id");
				strHeadingText = getNodeText(xmlParData, "text");
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
				let tocText = '<p class="toc_item_' + level + " " + language + '_text">';
				tocText +=
					"<a onclick=\"editor_goto_link('" +
					bookId +
					"'," +
					paragraph +
					')" >[' +
					paragraph +
					"]-" +
					strHeadingText +
					"</a>";
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

	let bookId = getNodeText(xmlParInfo, "book");
	let paragraph = getNodeText(xmlParInfo, "paragraph");
	let base_on = getNodeText(xmlParInfo, "base");
	let begin = getNodeText(xmlParInfo, "begin");
	let end = getNodeText(xmlParInfo, "end");

	type = getNodeText(xmlParInfo, "type");
	language = getNodeText(xmlParInfo, "language");
	bId = getNodeText(xmlParInfo, "id");

	let htmlBlock;
	if (base_on == "sentence") {
		blockId = "par_" + bookId + "_" + (paragraph - 1) + "_" + begin + "_" + end;
		htmlBlock = document.getElementById(blockId);
		if (htmlBlock == null) {
			addNewBlockToHTML(bookId, paragraph, begin, end);
		}
	} else {
		blockId = "par_" + bookId + "_" + (paragraph - 1);
		htmlBlock = document.getElementById(blockId);
		if (htmlBlock == null) {
			addNewBlockToHTML(bookId, paragraph);
		}
	}

	if (!isParInView(getParIndex(bookId, paragraph))) {
		document.getElementById(blockId).style.display = "none";
		return;
	} else {
		document.getElementById(blockId).style.display = "inline-flex";
	}

	switch (type) {
		case "wbw":
			var strHtml = renderWordParBlockInner(element);
			$("#wnote_" + bookId + "_" + (paragraph - 1)).html(renderNoteShell(element));
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml;
			note_refresh_new();
			var typ = document.createAttribute("class");
			typ.nodeValue = "wbwparblock";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_wbw_" + bId;
			paraDiv.attributes.setNamedItem(id);

			blockId = "wblock_" + bookId + "_" + (paragraph - 1);
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
			paraDiv.innerHTML = strHtml;
			var typ = document.createAttribute("class");
			typ.nodeValue = "tran_parblock " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_tran_" + bId;
			paraDiv.attributes.setNamedItem(id);

			blockId = "tran_" + bookId + "_" + (paragraph - 1);
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
						if (!eBlockSenDiv) {
							//没有 添加
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
						} else {
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
			paraDiv.innerHTML = strHtml;
			var typ = document.createAttribute("class");
			typ.nodeValue = "note_parblock " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_note_" + bId;
			paraDiv.attributes.setNamedItem(id);

			blockId = "note_" + bookId + "_" + (paragraph - 1);
			document.getElementById(blockId).appendChild(paraDiv);
			document.getElementById(blockId).style.display = "block";
			break;
		case "heading":
			headingLevel = getNodeText(xmlParInfo, "level");
			var strHtml = "";
			strHtml = renderHeadingBlockInner(element);
			var paraDiv = document.createElement("div");
			var node = document.createTextNode("");
			paraDiv.appendChild(node);
			paraDiv.innerHTML = strHtml;
			var typ = document.createAttribute("class");
			typ.nodeValue = "heading_parblock_" + headingLevel + "_" + language + " " + language + "_text";
			paraDiv.attributes.setNamedItem(typ);

			var id = document.createAttribute("id");
			id.nodeValue = "id_heading_" + bId;
			paraDiv.attributes.setNamedItem(id);

			blockId = "heading_" + bookId + "_" + (paragraph - 1);
			document.getElementById(blockId).appendChild(paraDiv);
			document.getElementById(blockId).style.display = "block";
			//document.getElementById("id_heading_level_"+bookId+"_"+(paragraph-1)).value=headingLevel;
			break;
	}
	guide_init();
}

function updataHeadingBlockInHtml(book, par) {
	document.getElementById("heading_" + book + "_" + (par - 1)).innerHTML = "";
	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		type = getNodeText(xmlParInfo, "type");
		if (type == "heading" && bookId == book && paragraph == par) {
			insertBlockToHtml(allBlock[iBlock]);
		}
	}
}

function renderBlock() {}
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
				let eLable = document.getElementById(
					"tran_pre_" + blockId + "_p" + book + "-" + para + "-" + element.end
				);
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
			return renderTranslateParBlockInner(xBlock[iBlock]);
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

	type = getNodeText(xmlParInfo, "type");
	language = getNodeText(xmlParInfo, "language");
	var allSen = elementBlock.getElementsByTagName("sen");
	output += "<div>";
	for (iSen = 0; iSen < allSen.length; iSen++) {
		var senText = getNodeText(allSen[iSen], "text");
		var senBegin = getNodeText(allSen[iSen], "begin");
		var senEnd = getNodeText(allSen[iSen], "end");
		var senA = "p" + book + "-" + paragraph + "-" + senEnd;

		output +=
			"<div id='tran_sent_" + bId + "_" + senA + "' block='" + bId + "' sn='" + senA + "' class='tran_sent' >";

		if (power != "read") {
			//可写入模式
			if (readonly != 1) {
				output +=
					"<div class='tran_sent_inner' onmouseenter=\"tran_sent_div_mouseenter('" +
					bId +
					"','" +
					senA +
					"')\" >";
			}
		} else {
			output += "<div class='tran_sent_inner' >";
		}
		output += "<div id='tran_pre_" + bId + "_" + senA + "' class='tran_sent_pre'>";
		if (senText == "") {
			output += "<span style='color:gray;'>" + gLocal.gui.translation + "</span>";
		} else {
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
		if (power != "read") {
			//可写入模式
			if (readonly != 1) {
				//只读模式不显示修改框 只有power=write才考虑只读模式
				output += "<textarea id='ta_" + bId + "_" + senBegin + "_" + senEnd + "' ";
				output += " onkeyup=\"updateTranslationPreview('" + bId + "_" + senA + "',this)\" ";
				output += " onchange=\"tran_text_onchange('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" ";
				//output += " onchange=\"sen_save('"+bId+"','"+senBegin+"','"+senEnd+"',this.value)\" ";
				output += " onblur=\"tran_sent_div_blur('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" ";
				output +=
					" onfocus=\"tran_sent_div_onfocus('" + bId + "','" + senBegin + "','" + senEnd + "',this)\" >";
				senText = senText.replace(/\<br \/\>/g, "\n\n");
				output += term_std_str_to_edit(senText);
				output += "</textarea>";
			}
		}

		output += "</div>";
		output += '<div class="tran_sent_text_tool_bar">';
		//output += "<button onclick=\"tran_sen_save_click('"+bId+"','"+senBegin+"','"+senEnd+"',this)\">Save</button>";
		output += "<span></span>";
		output +=
			"<span onclick=\"show_tran_msg('" +
			bId +
			"','" +
			senBegin +
			"','" +
			senEnd +
			"')\"><span id='' class=\"word_msg\"></span></span>";

		output += "</div>";
		output += "</div>";
		output += "</div>";

		output += "</div>";
	}
	output += "</div>";

	return output;
}

function renderTranslateParBlockInnerPreview(strText) {}
function updateTranslationPreview_a(blockId, text) {
	let out = "";
	let newText = text;
	newText = marked(newText);
	//newText = term_tran_edit_replace(newText);
	//newText = term_edit_to_std_str(newText);
	newText = term_std_str_to_tran(newText);
	out += newText;
	if (out == "") {
		out = "<span style='color:#ccbfbf;'>" + gLocal.gui.with_idea + "</span>";
	}
	$("#tran_pre_" + blockId).html(out);
	term_updata_translation();
}
function updateTranslationPreview(blockId, obj) {
	let out = "";
	let newText = obj.value;
	newText = marked(newText);
	let channal = $(obj).attr("channal");
	let lang = $(obj).attr("lang");

	newText = term_std_str_to_tran(newText, channal, getCookie("userid"), lang);
	out += newText;
	if (out == "") {
		out = "<span style='color:#ccbfbf;'>" + gLocal.gui.with_idea + "</span>";
	}
	$("#tran_pre_" + blockId).html(out);
	term_updata_translation();
	$(obj).css({'height': 'auto'}).height($(obj)[0].scrollHeight+"px");
}

function getSuperTranslateModifyString(inString, par_num, par_guid, language) {
	var curr_super_info = getPrevNextTrans_Guid("curr", par_guid);

	var arrString = translate_split(curr_super_info.senText, language).string;
	var output = "";
	var arrString_last = "";
	var sent_ID = "";
	var arr_sentA_ID = g_arr_Para_ID[par_num];
	var y = 0;
	for (y in arr_par_sent_num) {
		//得到本段句子外殻數0~par_num_last
		var arr_sentID = arr_par_sent_num[y].split("_");
		if (arr_sentID[1] == par_num) {
			var par_num_last = arr_sentID[2];
		}
	}
	if (par_num_last >= arrString.length - 1) {
		//外殻數大於等於切分結果，執行充填
		for (var x = 0; x < arrString.length; x++) {
			//整段渲染
			sent_ID = "sent_" + arr_sentA_ID[x];
			var superStringPosition_Start = 0;
			var superStringPosition_End = 0;
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			var output_super = "";
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start;
				superStringPosition_Start += superString[super_x].length;
				output_super +=
					"<span id='SBS_senA_" +
					par_guid +
					"_" +
					superStringPosition_End +
					"' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super +=
					"<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + '\' class="tooltip">';
				output_super += superString[super_x];
				output_super += '<span class="tooltiptext tooltip-bottom">';
				output_super +=
					"<button onclick='super_trans_modify(up_sen," +
					superStringPosition_Start +
					"," +
					par_guid +
					"," +
					language +
					")'>▲</button>";
				output_super +=
					"<button onclick='super_trans_modify(down_sen," +
					superStringPosition_End +
					"," +
					par_guid +
					"," +
					language +
					")'>▼</button>";
				output_super += "</span></span>";
			}
			var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>";
			if (document.getElementById(sent_ID).innerHTML.length != 0) {
				if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
					document.getElementById(sent_ID).innerHTML = output_super_0; //把逐句譯內容根據相應ID號寫入
				} else {
					document.getElementById(sent_ID).innerHTML += output_super_0; //把逐句譯內容根據相應ID號寫入
				}
			}
			output += output_super.replace(/SBS_/g, "PBP_"); //逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par"); //逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par"); //逐句譯id轉換為逐段id
		}
	} else if (par_num_last < arrString.length - 1) {
		//外殻數小於切分結果
		for (var x = 0; x < par_num_last; x++) {
			//除最後一句，其他充填
			//整段渲染
			sent_ID = "sent_" + arr_sentA_ID[x];
			var superStringPosition_Start = 0;
			var superStringPosition_End = 0;
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			var output_super = "";
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start;
				superStringPosition_Start += superString[super_x].length;
				output_super +=
					"<span id='SBS_senA_" +
					par_guid +
					"_" +
					superStringPosition_End +
					"' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super +=
					"<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + '\' class="tooltip">';
				output_super += superString[super_x];
				output_super += '<span class="tooltiptext tooltip-bottom">';
				output_super +=
					"<button onclick='super_trans_modify(up_sen," +
					superStringPosition_Start +
					"," +
					par_guid +
					"," +
					language +
					")'>▲</button>";
				output_super +=
					"<button onclick='super_trans_modify(down_sen," +
					superStringPosition_End +
					"," +
					par_guid +
					"," +
					language +
					")'>▼</button>";
				output_super += "</span></span>";
			}
			output += output_super.replace(/SBS_/g, "PBP_"); //逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par"); //逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par"); //逐句譯id轉換為逐段id
			var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>";
			if (document.getElementById(sent_ID).innerHTML.length != 0) {
				if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
					document.getElementById(sent_ID).innerHTML = output_super_0; //把逐句譯內容根據相應ID號寫入
				} else {
					document.getElementById(sent_ID).innerHTML += output_super_0; //把逐句譯內容根據相應ID號寫入
				}
			}
		}
		var output_super = "";
		for (var x = par_num_last; x < arrString.length - 1; x++) {
			//最後一句，合併所有譯文
			var superString = translate_super_split(arrString[x], language).string;
			var language_type = translate_super_split(arrString[x], language).language_type;
			for (var super_x = 0; super_x < superString.length; super_x++) {
				superStringPosition_End = superStringPosition_Start;
				superStringPosition_Start += superString[super_x].length;
				output_super +=
					"<span id='SBS_senA_" +
					par_guid +
					"_" +
					superStringPosition_End +
					"' class=\"tran_sen\" style='display:none'>";
				output_super += superStringPosition_End + "_" + superStringPosition_Start;
				output_super += "</span>";
				output_super +=
					"<span id='SBS_senText_" + par_guid + "_" + superStringPosition_End + '\' class="tooltip">';
				output_super += superString[super_x];
				output_super += '<span class="tooltiptext tooltip-bottom">';
				output_super +=
					"<button onclick='super_trans_modify(up_sen," +
					superStringPosition_Start +
					"," +
					par_guid +
					"," +
					language +
					")'>▲</button>";
				output_super +=
					"<button onclick='super_trans_modify(down_sen," +
					superStringPosition_End +
					"," +
					par_guid +
					"," +
					language +
					")'>▼</button>";
				output_super += "</span></span>";
			}
			output += output_super.replace(/SBS_/g, "PBP_"); //逐句譯id轉換為逐段id
			output = output.replace(/up_sen/g, "up_par"); //逐句譯id轉換為逐段id
			output = output.replace(/down_sen/g, "down_par"); //逐句譯id轉換為逐段id
		}
		sent_ID = "sent_" + arr_sentA_ID[par_num_last];
		var output_super_0 = "<div class=" + language_type + "_text>" + output_super + "</div>";
		if (document.getElementById(sent_ID).innerHTML.length != 0) {
			if (document.getElementById(sent_ID).innerHTML == gLocal.gui.sent_trans) {
				document.getElementById(sent_ID).innerHTML = output_super_0; //把逐句譯內容根據相應ID號寫入
			} else {
				document.getElementById(sent_ID).innerHTML += output_super_0; //把逐句譯內容根據相應ID號寫入
			}
		}
	}
	return output;
}

function super_trans_modify(updown, superStringPosition, par_guid, language) {
	//未完成

	var prev_super_info = getPrevNextTrans_Guid(prev, par_guid);
	var next_super_info = getPrevNextTrans_Guid(next, par_guid);
	var curr_super_info = getPrevNextTrans_Guid(curr, par_guid);

	var new_prev_super_info = new Object();
	var new_next_super_info = new Object();
	var new_curr_super_info = new Object();

	switch (updown) {
		case "up_par":
			new_prev_super_info.GUID = prev_super_info.GUID;
			new_prev_super_info.senText =
				prev_super_info.senText + curr_super_info.senText.replace(/#/g, "").slice(0, superStringPosition);
			var new_senText_array = translate_super_split(new_prev_super_info.senText, language);
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
			var new_senText_array = translate_super_split(new_curr_super_info.senText, language);
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
	var allBlock = gXmlBookDataBody.getElementsByTagName("block"); //得到全局block
	var prev_super_info = new Object();
	var next_super_info = new Object();
	var curr_super_info = new Object();
	var super_arr_xmlParInfo = new Array();
	var super_arr_xmlParData = new Array();
	var super_arr_xmlParAllsen = new Array();
	var super_arr_xmlParAllloc = new Array();

	curr_super_info.GUID = par_guid;
	for (var super_iBlock = 0; super_iBlock < allBlock.length; super_iBlock++) {
		if (super_iBlock == "length") {
			break;
		}
		var elementBlock = allBlock[super_iBlock]; //得到其中一段的數據
		if (elementBlock.getElementsByTagName("sen").length != 0) {
			super_arr_xmlParInfo.push(elementBlock.getElementsByTagName("info")[0]);
			super_arr_xmlParData.push(elementBlock.getElementsByTagName("data")[0]);
		}
	}
	if (elementBlock.getElementsByTagName("sen").length != 0) {
		for (var super_iData in super_arr_xmlParData) {
			var super_xmlParAll_Sen = "";
			var super_xmlParAll_Loc = "";
			for (var super_iSen = 0; super_iSen < super_arr_xmlParData[super_iData].childNodes.length; super_iSen++) {
				var super_xmlParData_Detail = super_arr_xmlParData[super_iData].childNodes[super_iSen];
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
			var super_GUID = getNodeText(super_arr_xmlParInfo[super_iBlock], "id"); //得到guid
			prev_super_info.senText = "";
			prev_super_info.senA = "";
			curr_super_info.senText = super_arr_xmlParAllsen[super_iBlock];
			curr_super_info.senA = super_arr_xmlParAllloc[super_iBlock];
			next_super_info.senText = "";
			next_super_info.senA = "";
			if (super_GUID == par_guid && super_iBlock == 0) {
				prev_super_info.GUID = ""; //前一個為空
				next_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock + 1], "id"); //得到後一個guid
				next_super_info.senText = super_arr_xmlParAllsen[super_iBlock + 1];
				next_super_info.senA = super_arr_xmlParAllloc[super_iBlock + 1]; //根據譯文數組得到錨點

				break;
			} else if (super_GUID == par_guid && super_iBlock > 0 && super_iBlock < super_arr_xmlParAllsen.length - 1) {
				prev_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock - 1], "id"); //得到前一個guid
				next_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock + 1], "id"); //得到後一個guid
				prev_super_info.senText = super_arr_xmlParAllsen[super_iBlock - 1];
				prev_super_info.senA = super_arr_xmlParAllloc[super_iBlock - 1]; //根據譯文數組得到錨點
				next_super_info.senText = super_arr_xmlParAllsen[super_iBlock + 1];
				next_super_info.senA = super_arr_xmlParAllloc[super_iBlock + 1]; //根據譯文數組得到錨點

				break;
			} else if (super_GUID == par_guid && super_iBlock == super_arr_xmlParAllsen.length - 1) {
				prev_super_info.GUID = getNodeText(super_arr_xmlParInfo[super_iBlock - 1], "id"); //得到前一個guid
				next_super_info.GUID = "";
				prev_super_info.senText = super_arr_xmlParAllsen[super_iBlock - 1];
				prev_super_info.senA = super_arr_xmlParAllloc[super_iBlock - 1]; //根據譯文數組得到錨點
				break;
			}
		}
	} else {
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
			return next_super_info;
			break;
		case "curr":
			return curr_super_info;
			break;
		case "prev":
			return prev_super_info;
			break;
	}
}

function translate_split(inString, language) {
	var newString = "";
	var arrString = new Object();
	var language_type = "";
	if (
		language.toLowerCase() == "tw" ||
		language.toLowerCase() == "zh" ||
		language.toLowerCase() == "sc" ||
		language.toLowerCase() == "tc"
	) {
		language_type = "ZH";
	} else {
		language_type = "EN";
	}
	arrString.language_type = language_type.toLowerCase();
	switch (language_type) {
		case "ZH":
			for (var i_cntransplit in cn_transplit) {
				newString = inString;
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
			newString = inString;
			for (var i_entransplit in en_transplit) {
				newString = inString;
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
	return arrString;
	// body...
}
function translate_super_split(inString) {
	var newString = inString;
	var arrString = new Object();
	var language_type = "";
	if (
		language.toLowerCase() == "tw" ||
		language.toLowerCase() == "zh" ||
		language.toLowerCase() == "sc" ||
		language.toLowerCase() == "tc"
	) {
		language_type = "ZH";
	} else {
		language_type = "EN";
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
	book = getNodeText(xmlParInfo, "book");
	paragraph = getNodeText(xmlParInfo, "paragraph");
	bId = getNodeText(xmlParInfo, "id");

	type = getNodeText(xmlParInfo, "type");
	var allSen = elementBlock.getElementsByTagName("sen");
	output +=
		'<button type="button" class="edit_note_button imgbutton" onclick="editor_note_edit(\'' +
		bId +
		"')\"><svg class='icon' style='fill: var(--detail-color);'><use xlink:href='svg/icon.svg#ic_mode_edit'></use></svg></button>";
	for (iSen = 0; iSen < allSen.length; iSen++) {
		senText = getNodeText(allSen[iSen], "text");
		senA = getNodeText(allSen[iSen], "a");
		output += '<span id="note_sen_' + bId + "_" + iSen + '" class="note_sen">';
		output += senText;
		output += "</span>";
	}

	return output;
}

function renderHeadingBlockInner(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	book = getNodeText(xmlParInfo, "book");
	paragraph = getNodeText(xmlParInfo, "paragraph");
	bId = getNodeText(xmlParInfo, "id");

	type = getNodeText(xmlParInfo, "type");
	var headingData = elementBlock.getElementsByTagName("data")[0];
	headingText = getNodeText(headingData, "text");
	output += '<span id="id_heading_text_' + bId + '">' + headingText + "</span>";

	output +=
		'<button type="button" class="edit_tool imgbutton" onclick="editor_heading_edit(\'' +
		bId +
		"')\"><svg class='icon' style='fill: var(--detail-color);'><use xlink:href='svg/icon.svg#ic_mode_edit'></use></svg></button>";

	return output;
}

function updateWordParBlockInnerAll() {
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		mId = getNodeText(xmlParInfo, "id");
		type = getNodeText(xmlParInfo, "type");
		if ((type = "wbw")) {
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
		note_refresh_new();
	}
}

function renderNoteShell(elementBlock) {
	let output = "";
	let allWord = elementBlock.getElementsByTagName("word");
	for (iWord = 0; iWord < allWord.length; iWord++) {
		let wID = getNodeText(allWord[iWord], "id");
		output += '<div id="wn_' + wID + '">';
		output += '<div id="wnn_' + wID + '"></div>';
		output += '<div id="wnr_' + wID + '"></div>';
		output += '<div id="wnc_' + wID + '"></div>';
		output += "</div>";
	}
	return output;
}

//句子上面的工具条
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
		if (iWordId > begin && aEnter == "1") {
			//句子末尾
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

	output += "<span style='flex: 7;display: flex;'>";
	output += "<div style='background-color: silver;'>";
	//句子编号
	output += "<span style='font-size: large; font-weight: bolder;' title=" + gLocal.gui.text_num + ">";
	output += sentIdString;
	output += "</span>";
	//功能按钮
	//拷贝到剪贴板
	output +=
		"<button class='icon_btn' onclick=\"copy_to_clipboard('" +
		sentIdStringLink +
		"')\" title=" +
		gLocal.gui.copy_to_clipboard +
		">";
	output +=
		'<svg style="fill: var(--link-color);" t="1601480724259" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4644"><path d="M791.272727 93.090909H139.636364v837.818182a93.090909 93.090909 0 0 1-93.090909-93.090909V93.090909a93.090909 93.090909 0 0 1 93.090909-93.090909h558.545454a93.090909 93.090909 0 0 1 93.090909 93.090909zM232.727273 186.181818h744.727272v837.818182H232.727273V186.181818z" p-id="4645"></path></svg>';
	output += "</button>";

	//在阅读器中打开
	let reader_open_link = "";
	if (_display_sbs == 0) {
		//逐段模式
		reader_open_link = "../reader/?view=para&book=" + abook + "&par=" + aparagraph;
	} else {
		//逐句模式
		reader_open_link =
			"../reader/?view=sent&book=" + abook + "&par=" + aparagraph + "&begin=" + iBegin + "&end=" + iEnd;
	}
	output +=
		"<button class='icon_btn'  onclick=\"window.open('" +
		reader_open_link +
		"')\" target='_blank' title='" +
		gLocal.gui.scan_in_reader +
		"'>";
	output +=
		'<svg style="fill: var(--link-color);" t="1601482753387" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="22291"><path d="M703.730499 544.578527a191.730499 191.730499 0 0 1 156.260356 302.806368l122.004508 122.004507a31.955083 31.955083 0 0 1-45.248398 45.184488l-121.940597-121.940598A191.730499 191.730499 0 1 1 703.730499 544.642437z m-6.391017-511.28133c38.857381 0 70.301183 30.67688 70.301183 68.511698v386.912146a255.640665 255.640665 0 1 0-69.022979 503.16474l-563.687667 0.06391c-38.857381 0-70.301183-30.67688-70.301183-68.447788V101.808895C64.628836 63.910166 96.072638 33.233286 134.930019 33.233286h562.409463z m6.391017 575.191496a127.820333 127.820333 0 1 0 0 255.640665 127.820333 127.820333 0 0 0 0-255.640665z m-351.505915 0h-127.820332a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511281h127.820332a31.955083 31.955083 0 0 0 0-63.910166z m0-191.730499h-127.820332a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511282h127.820332a31.955083 31.955083 0 0 0 0-63.910167z m191.730499-191.730499h-319.550831a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511282h319.550831a31.955083 31.955083 0 0 0 0-63.910167z" p-id="22292"></path></svg>';
	output += "</button>";

	//关系图
	output +=
		"<button class='icon_btn' title='" +
		gLocal.gui.relational_map +
		"' class='rel_map' onclick=\"sent_show_rel_map('" +
		abook +
		"','" +
		aparagraph +
		"','" +
		iBegin +
		"','" +
		iEnd +
		"')\">" +
		'<svg style="transform: rotate(-90deg); fill: var(--link-color);" t="1601482033694" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="18290"><path d="M903.3 650.8H791.9V511.3H540.5V399.9h167.7c30.9 0 55.9-25.5 55.9-56.4V120.3c0-31.3-25.1-56.4-55.9-56.4H316.4c-30.9 0-55.5 25.1-55.5 56.4 0 0 0 222.8-0.4 223.2 0 31.3 25.1 56.4 55.9 56.4h168.2v111.4H232.8v139.6H120.9c-30.9-0.1-55.9 25-55.9 55.9v196.4c0 30.4 25.1 55.5 55.9 55.9h279.6c30.9 0 55.9-25.1 55.9-55.9V706.8c0-30.9-25.1-55.9-55.9-55.9H288.7v-83.7H736v83.7H624.2c-30.9 0-55.9 25.1-55.9 55.9v196.4c0 30.9 25.1 55.9 55.9 55.9h279.1c30.9 0 55.9-25.1 55.9-55.9V706.8c0-30.9-25-56-55.9-56z" p-id="18291"></path></svg>' +
		"</button>";

	//拷贝词意到剪贴板
	output +=
		"<button class='icon_btn' title='" +
		gLocal.gui.form_sent +
		"' class='rel_map' onclick=\"sent_copy_meaning('" +
		abook +
		"','" +
		aparagraph +
		"','" +
		iBegin +
		"','" +
		iEnd +
		"')\">" +
		'<svg style="fill: var(--link-color);" t="1611985739555" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6173" width="200" height="200"><path d="M423.198 640a83.84 83.84 0 0 1-64-28.8 259.84 259.84 0 0 1-26.88-308.48L441.118 128a261.12 261.12 0 1 1 448 272l-35.2 57.6a83.84 83.84 0 1 1-145.92-90.24l35.2-57.6a92.8 92.8 0 0 0-158.72-96.64l-107.52 176.64a92.8 92.8 0 0 0 9.6 109.44 83.84 83.84 0 0 1-64 139.52z" p-id="6174"></path><path d="M357.918 1024a261.12 261.12 0 0 1-222.72-397.44l31.36-50.56a83.84 83.84 0 1 1 144 87.68l-31.36 51.2a92.8 92.8 0 0 0 30.72 128 91.52 91.52 0 0 0 70.4 10.88 92.16 92.16 0 0 0 57.6-41.6l107.52-177.92a93.44 93.44 0 0 0-6.4-105.6 83.84 83.84 0 1 1 134.4-103.68 262.4 262.4 0 0 1 17.28 296.96L581.278 896a259.84 259.84 0 0 1-163.84 120.32 263.68 263.68 0 0 1-59.52 7.68z" p-id="6175"></path></svg>' +
		"</button>";

	//功能按钮结束
	output += "</div>";
	output += "<guide gid='sent_func' style='margin:unset;'></guide>";
	output += "</span>";
	output += "<span style='flex: 3;'><guide gid='sent_trans' style='margin-left:100%;'></guide></span>";
	output += "</div>";
	return output;
}
function renderWordBlock(element) {
	let output = "";
	let Note_Mark = 0;
	let wID = getNodeText(element, "id");
	let wPali = getNodeText(element, "pali");
	let wReal = getNodeText(element, "real");
	let wType = getNodeText(element, "type");
	let wGramma = getNodeText(element, "gramma");
	let wCase = getNodeText(element, "case");
	let wUn = getNodeText(element, "un");
	if ((wType == "" || wType == "?") && wCase != "") {
		wType = wCase.split("#")[0];
	}

	//渲染单词块
	//word div class
	let wordclass;
	let strMouseEvent = ' onmouseover="on_word_mouse_enter()" onmouseout="on_word_mouse_leave()"';
	if (wType == ".ctl." && wGramma == ".a.") {
		wordclass = "ctrl";
	} else if (Note_Mark == 1) {
		wordclass = "word org_note";
	} else {
		if (wReal == "") {
			wordclass = "word word_punc un_parent "; //符號
		} else {
			if (wType == ".un.") {
				wordclass = "word un_parent"; //粘音詞
			} else if (wType == ".comp.") {
				wordclass = "word comp_parent"; //複合詞
			} else {
				wordclass = "word"; //普通
			}
		}
	}
	output += '<div id="wb' + wID + "\" class='" + wordclass + "'> <a name='" + wPali + "'></a>";
	//head div class
	output += "	<div id='ws_" + wID + "' class='word_head_shell' >";
	let wordheadclass;
	if (wUn.length > 0) {
		wordheadclass = "word_head un_pali";
	} else {
		wordheadclass = "word_head";
	}
	output += "	<div class='" + wordheadclass + "' id=\"whead_" + wID + '">';
	output += renderWordHeadInner(element);
	output += "	</div>";
	output += "	</div>"; //word_head_shell
	output += '	<div id="detail' + wID + '" class="wbody">';
	output += renderWordBodyInner(element);
	output += "	</div>";

	output += "</div>";
	return output;
}
var arr_par_sent_num = new Array();
var g_arr_Para_ID = new Array();
function renderWordParBlockInner(elementBlock) {
	var output = "<div style='display:block;width:100%'>";
	var Note_Mark = 0;
	var sent_num = 0;

	var arr_Para_ID = new Array();
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book");
	let paragraph = getNodeText(xmlParInfo, "paragraph");
	let par_num = paragraph - 1;
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
		let wUn = getNodeText(allWord[iWord], "un");
		let wEnter = getNodeText(allWord[iWord], "enter");
		if ((wType == "" || wType == "?") && wCase != "") {
			wType = wCase.split("#")[0];
		}

		//渲染单词块
		output += renderWordBlock(allWord[iWord]);
		/*
		//word div class
		let wordclass;
		let strMouseEvent = ' onmouseover="on_word_mouse_enter()" onmouseout="on_word_mouse_leave()"';
		if (wType == ".ctl." && wGramma == ".a.") {
			wordclass = "ctrl";
		} else if (Note_Mark == 1) {
			wordclass = "word org_note";
		} else {
			if (wReal == "") {
				wordclass = "word word_punc un_parent "; //符號
			} else {
				if (wType == ".un.") {
					wordclass = "word un_parent"; //粘音詞
				} else if (wType == ".comp.") {
					wordclass = "word comp_parent"; //複合詞
				} else {
					wordclass = "word"; //普通
				}
			}
		}
		output += '<div id="wb' + wID + "\" class='" + wordclass + "'> <a name='" + wPali + "'></a>";
		//head div class
		output += "	<div id='ws_" + wID + "' class='word_head_shell' >";
		let wordheadclass;
		if (wUn.length > 0) {
			wordheadclass = "word_head un_pali";
		} else {
			wordheadclass = "word_head";
		}
		output += "	<div class='" + wordheadclass + "' id=\"whead_" + wID + '">';
		output += renderWordHeadInner(allWord[iWord]);
		output += "	</div>";
		output += "	</div>"; //word_head_shell
		output += '	<div id="detail' + wID + '" class="wbody">';
		output += renderWordBodyInner(allWord[iWord]);
		output += "	</div>";

		output += "</div>";
*/
		//渲染单词块结束

		word_id = parseInt(wID.split("-")[2]);
		if (sent_begin == 0) {
			sent_begin = word_id;
		}

		if (iWord >= 1) {
			var pre_pali_spell = getNodeText(allWord[iWord - 1], "pali");
			var pre_pali_type = getNodeText(allWord[iWord - 1], "type");
			var pre_pali_Gramma = getNodeText(allWord[iWord - 1], "gramma");
			var pre_pali_Case = getNodeText(allWord[iWord - 1], "case");
			if (pre_pali_type == "" && pre_pali_Case != "") {
				pre_pali_type = pre_pali_Case.split("#")[0];
			}
			if (pre_pali_Case != "" && pre_pali_Case.lastIndexOf("$") != -1) {
				var pre_case_array = pre_pali_Case.split("$");
				pre_pali_Case = pre_case_array[pre_case_array.length - 1];
			}
		}
		if (iWord + 2 <= allWord.length) {
			var next_pali_spell = getNodeText(allWord[iWord + 1], "pali");
			var next_pali_type = getNodeText(allWord[iWord + 1], "type");
			var next_pali_Gramma = getNodeText(allWord[iWord + 1], "gramma");
			var next_pali_Case = getNodeText(allWord[iWord + 1], "case");
			if (next_pali_type == "" && next_pali_Case != "") {
				next_pali_type = next_pali_Case.split("#")[0];
			}
			if (next_pali_Case != "" && next_pali_Case.lastIndexOf("$") != -1) {
				var next_case_array = next_pali_Case.split("$");
				next_pali_Case = next_case_array[next_case_array.length - 1];
			}
		}
		if (next_pali_spell == "(" || wPali == "(") {
			Note_Mark = 1;
		} else if ((pre_pali_spell == ")" || wPali == ")") && Note_Mark == 1) {
			Note_Mark = 0;
		} else {
		}
		/*
		if (next_pali_spell == "[" || curr_pali_spell == "[") {
			Note_Mark2 = 1;
		} else if ((pre_pali_spell == "]" || curr_pali_spell == "]") && Note_Mark2 == 1) {
			Note_Mark2 = 0;
		} else {
		}*/

		if (wEnter == 1) {
			//句子末尾
			var sent_ID = "sent_" + par_num + "_" + sent_num;
			if (_display_sbs == 1) {
				//逐句显示模式
				output += "</div>"; //逐句逐词译块结束

				//逐句翻译块开始
				output += "<div id='sent_div_" + wID + "' class='translate_sent'>";
				output += "<div class='translate_sent_head'>";
				output += "<div class='translate_sent_head_toolbar' style='display:none;'>";
				output += "<span></span>";
				output +=
					"<span onclick=\"show_tran_net('" +
					book +
					"','" +
					paragraph +
					"','" +
					sent_begin +
					"','" +
					word_id +
					"')\"><span id='' class=\"word_msg\">issue</span></span>";
				//
				output += "</div>";

				output += "<div class='translate_sent_head_content'>";
				//句子预览
				output += render_tran_sent_block(book, paragraph, sent_begin, word_id, 0, true);
				if (_my_channal != null) {
					for (const iterator of _my_channal) {
						if (iterator.status > 0) {
							let readonly;
							if (iterator.power > 0 && iterator.power < 20) {
								readonly = true;
							} else {
								readonly = false;
							}
							output += render_tran_sent_block(
								book,
								paragraph,
								sent_begin,
								word_id,
								iterator.uid,
								readonly
							);
						}
					}
				}
				//句子预览结束
				output += "</div>";

				output +=
					"<div class='trans_text_content'  pcds='sent-net' book='" +
					book +
					"' para='" +
					paragraph +
					"' begin='" +
					sent_begin +
					"' end=''>";
				output += "</div>";

				output += "<div id='sent_" + wID + "' class='translate_sent_content'>";
				output += "</div>";
				output += "</div>";
				output += "</div>";

				//逐句翻译块内容结束
				output += "<div class='translate_sent_foot'>";
				output += "</div>";
				//output += "</div>";
				//逐句翻译块结束

				output += "</div>"; //逐句块结束

				sent_begin = word_id + 1;
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
				output +=
					"<span style='flex: 7;display: flex;'><div style='background-color: silver;'><span style='font-size: large; font-weight: bolder;' title=" +
					gLocal.gui.text_num +
					">" +
					sentIdString +
					"</span><button class='icon_btn' onclick=\"copy_to_clipboard('" +
					sentIdStringLink +
					"')\" title=" +
					gLocal.gui.copy_to_clipboard +
					">";
				output +=
					'<svg style="fill: var(--link-color);" t="1601480724259" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4644"><path d="M791.272727 93.090909H139.636364v837.818182a93.090909 93.090909 0 0 1-93.090909-93.090909V93.090909a93.090909 93.090909 0 0 1 93.090909-93.090909h558.545454a93.090909 93.090909 0 0 1 93.090909 93.090909zM232.727273 186.181818h744.727272v837.818182H232.727273V186.181818z" p-id="4645"></path></svg>';
				output += "</button>";
				output +=
					"<button class='icon_btn'  onclick=\"window.open('../reader/?view=sent&book=" +
					book +
					"&par=" +
					paragraph +
					"&begin=" +
					nextBegin +
					"&end=" +
					nextEnd +
					"')\" target='_blank' title='" +
					gLocal.gui.scan_in_reader +
					"'>";

				output +=
					'<svg style="fill: var(--link-color);" t="1601482753387" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="22291"><path d="M703.730499 544.578527a191.730499 191.730499 0 0 1 156.260356 302.806368l122.004508 122.004507a31.955083 31.955083 0 0 1-45.248398 45.184488l-121.940597-121.940598A191.730499 191.730499 0 1 1 703.730499 544.642437z m-6.391017-511.28133c38.857381 0 70.301183 30.67688 70.301183 68.511698v386.912146a255.640665 255.640665 0 1 0-69.022979 503.16474l-563.687667 0.06391c-38.857381 0-70.301183-30.67688-70.301183-68.447788V101.808895C64.628836 63.910166 96.072638 33.233286 134.930019 33.233286h562.409463z m6.391017 575.191496a127.820333 127.820333 0 1 0 0 255.640665 127.820333 127.820333 0 0 0 0-255.640665z m-351.505915 0h-127.820332a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511281h127.820332a31.955083 31.955083 0 0 0 0-63.910166z m0-191.730499h-127.820332a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511282h127.820332a31.955083 31.955083 0 0 0 0-63.910167z m191.730499-191.730499h-319.550831a31.955083 31.955083 0 0 0-5.751915 63.398885l5.751915 0.511282h319.550831a31.955083 31.955083 0 0 0 0-63.910167z" p-id="22292"></path></svg>';
				output += "</button>";
				output +=
					"<button class='icon_btn' title='" +
					gLocal.gui.relational_map +
					"' class='rel_map' onclick=\"sent_show_rel_map('" +
					book +
					"','" +
					paragraph +
					"','" +
					nextBegin +
					"','" +
					nextEnd +
					"')\">" +
					'<svg style="transform: rotate(-90deg); fill: var(--link-color);" t="1601482033694" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="18290"><path d="M903.3 650.8H791.9V511.3H540.5V399.9h167.7c30.9 0 55.9-25.5 55.9-56.4V120.3c0-31.3-25.1-56.4-55.9-56.4H316.4c-30.9 0-55.5 25.1-55.5 56.4 0 0 0 222.8-0.4 223.2 0 31.3 25.1 56.4 55.9 56.4h168.2v111.4H232.8v139.6H120.9c-30.9-0.1-55.9 25-55.9 55.9v196.4c0 30.4 25.1 55.5 55.9 55.9h279.6c30.9 0 55.9-25.1 55.9-55.9V706.8c0-30.9-25.1-55.9-55.9-55.9H288.7v-83.7H736v83.7H624.2c-30.9 0-55.9 25.1-55.9 55.9v196.4c0 30.9 25.1 55.9 55.9 55.9h279.1c30.9 0 55.9-25.1 55.9-55.9V706.8c0-30.9-25-56-55.9-56z" p-id="18291"></path></svg>' +
					"</button>";
				//拷贝词意到剪贴板
				output +=
					"<button class='icon_btn' title='" +
					gLocal.gui.form_sent +
					"' class='rel_map' onclick=\"sent_copy_meaning('" +
					book +
					"','" +
					paragraph +
					"','" +
					nextBegin +
					"','" +
					nextEnd +
					"')\">" +
					'<svg style="fill: var(--link-color);" t="1611985739555" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6173" width="200" height="200"><path d="M423.198 640a83.84 83.84 0 0 1-64-28.8 259.84 259.84 0 0 1-26.88-308.48L441.118 128a261.12 261.12 0 1 1 448 272l-35.2 57.6a83.84 83.84 0 1 1-145.92-90.24l35.2-57.6a92.8 92.8 0 0 0-158.72-96.64l-107.52 176.64a92.8 92.8 0 0 0 9.6 109.44 83.84 83.84 0 0 1-64 139.52z" p-id="6174"></path><path d="M357.918 1024a261.12 261.12 0 0 1-222.72-397.44l31.36-50.56a83.84 83.84 0 1 1 144 87.68l-31.36 51.2a92.8 92.8 0 0 0 30.72 128 91.52 91.52 0 0 0 70.4 10.88 92.16 92.16 0 0 0 57.6-41.6l107.52-177.92a93.44 93.44 0 0 0-6.4-105.6 83.84 83.84 0 1 1 134.4-103.68 262.4 262.4 0 0 1 17.28 296.96L581.278 896a259.84 259.84 0 0 1-163.84 120.32 263.68 263.68 0 0 1-59.52 7.68z" p-id="6175"></path></svg>' +
					"</button>";

				output += "</div>";
				output += "<guide gid='sent_func' style='margin:unset;'></guide>";
				output += "</span>";
				output += "<span style='flex: 3;'><guide gid='sent_trans' style='margin-left:100%;'></guide></span>";
				output += "</div>";

				output += "<div class='sent_wbw'>";
			}
			sent_num += 1;
			arr_Para_ID.push(wID);
		}
	} //循環結束

	output += "</div>";

	//逐句翻译块开始
	output += "<div id='sent_div_" + wID + "' class='translate_sent'>";
	output += "<div class='translate_sent_head'>";
	output += "<div class='translate_sent_head_toolbar'>";
	output += "<span></span>";
	output +=
		"<span onclick=\"show_tran_net('" +
		book +
		"','" +
		paragraph +
		"','" +
		sent_begin +
		"','" +
		word_id +
		"')\"><span id='' class=\"word_msg\">issue</span></span>";
	output += "</div>";
	output += "<div class='translate_sent_head_content'>";

	//逐句译文开始
	output += render_tran_sent_block(book, paragraph, sent_begin, word_id, 0, true);
	if (_my_channal != null) {
		for (const iterator of _my_channal) {
			if (iterator.status > 0) {
				let readonly;
				if (iterator.power > 0 && iterator.power < 20) {
					readonly = true;
				} else {
					readonly = false;
				}
				output += render_tran_sent_block(book, paragraph, sent_begin, word_id, iterator.uid, readonly);
			}
		}
	}

	output += "</div>";
	output += "<div id='sent_" + wID + "' class='translate_sent_content'>";
	output += "</div>";
	//逐句翻译块内容结束

	output += "<div class='translate_sent_foot'>";
	output += "</div>";
	output += "</div>";
	//逐句翻译块结束

	output += "</div>";

	var sent_ID = "sent_" + par_num + "_" + sent_num;
	arr_Para_ID.push(wID);
	arr_par_sent_num.push(sent_ID);
	g_arr_Para_ID[par_num] = arr_Para_ID;
	return output;
}
//根据relation 绘制关系图
function sent_copy_meaning(book, para, begin, end) {
	let wordId = 0;

	let output = "";
	for (wordId = parseInt(begin); wordId <= parseInt(end); wordId++) {
		output += doc_word("#p" + book + "-" + para + "-" + wordId).val("mean");
	}
	copy_to_clipboard(output);
}

var relaSearchDeep=0;
function relaMoveSubgraph(seed,from,to){
	let iFound = 0;
	//查找与种子相连接的节点
	from.forEach(function(item,index,arr){
		if(relaSearchDeep==0){
			if( item.bid==seed){
				to.push(item);
				arr.splice(index,1);
				iFound++;
			}
			//删除连接到iti的线
			/*
			if( item.aid==seed && item.b=='iti'){
				arr.splice(index,1);
			}
			*/
		}
		else{
			if(item.aid==seed || item.bid==seed){
				to.push(item);
				arr.splice(index,1);
				iFound++;
			}
		}
	})
	if(iFound>0){
		//找到了继续查找
		to.forEach(function(item,index,arr){
			relaSearchDeep++;
			relaMoveSubgraph(item.aid,from,to);
		})		
	}
	relaSearchDeep--;

}
//根据relation 绘制关系图
function sent_show_rel_map(book, para, begin, end) {
	let memind = "graph LR\n";
	let pali_text = "";
	let rListA = new Array();
	
	let rListPool = new Array();
	let arrIti = new Array();

	let idList = new Array();
	$("#wbp" + book + "-" + para + "-" + begin)
		.parent()
		.children(".word")
		.each(function (index, element) {
			idList.push(this.id.slice(3));
		});

	for (const iterator_wid of idList) {
		let rel = doc_word("#p" + iterator_wid).val("rela");
		let pali = doc_word("#p" + iterator_wid).val("pali");
		let real = doc_word("#p" + iterator_wid).val("real");
		let type = doc_word("#p" + iterator_wid).val("type");

		let meaning = doc_word("#p" + iterator_wid).val("mean");

		if (type != ".ctl.") {
			pali_text += pali + " ";
		}
		if (rel != "") {
			let relaData = JSON.parse(rel);
			let language = getCookie("language");
			for (const iterator of relaData) {
				let strRel = iterator.relation;
				let relation_locstr = "";
				for (let x in list_relation) {
					if (list_relation[x].id == strRel && language == list_relation[x].language) {
						relation_locstr = list_relation[x].note;
						break;
					}
				}

				let dest = iterator.dest_spell;
				let type = doc_word("#p" + iterator.dest_id).val("case");
				let meanDest = doc_word("#p" + iterator.dest_id).val("mean");

				if (type.indexOf(".v.") >= 0) {
					dest = iterator.dest_id + '(("' + dest + "<br>" + meanDest + '"))';
				} else {
					dest = iterator.dest_id + '["' + dest + "<br>" + meanDest + '"]';
				}

				if(iterator.dest_spell=="iti"){
					arrIti.push({id:"p"+iterator_wid,dest_id:iterator.dest_id});
				}
				memind =
					"p" +
					iterator_wid +
					'("' +
					real +
					"<br>" +
					meaning +
					'")--"' +
					strRel +
					"<br>" +
					relation_locstr +
					'" --> ' +
					dest +
					"\n";

				rListA.push({a:real,aid:"p"+iterator_wid,b:iterator.dest_spell,bid:iterator.dest_id,str:memind});
			}
		}
	}

	let subgraphTitle = 1;
	rListPool.push({id:0,value:rListA,parent:-1});

	//倒序处理，能够处理iti嵌套
	for (let index = arrIti.length-1; index >=0; index--) {
		const element = arrIti[index];
		let rListB = new Array();
		for (let iPool = 0; iPool < rListPool.length; iPool++) {
			;
			relaSearchDeep = 0;
			relaMoveSubgraph(element.id,rListPool[iPool].value,rListB);
			if(rListB.length>0){
				rListPool.push({id:subgraphTitle++,value:rListB,parent:rListPool[iPool].id});
				console.log("找到：",element.id);
				break;
			}
		}
	}


	memind = "flowchart LR\n";
	for (const iterator of rListA) {
		memind +=iterator.str;
	}
	//渲染subgraph
	rListPool.forEach(function(item,index,arr){
		if(item.parent==0){
			memind += renderRelationSubgraph(rListPool,index);
		}
	});

	let graph = mermaid.render("graphDiv", memind);
	document.querySelector("#term_body_parent").innerHTML = '<div class="win_body_inner" id="term_body"></div>'; //清空之前的记录
	document.querySelector("#term_body").outerHTML =
		"<h3 style='padding: 0 1em;'>" + pali_text + "</h3>" + document.querySelector("#term_body").outerHTML;
	document.querySelector("#term_body").innerHTML = graph;
	document.querySelector("#term_win").style.display = "flex";
	document.querySelector(".win_body").style.display = "block";
}

//用递归渲染，subgraph嵌套
function renderRelationSubgraph(arrData,index){
	let output = "";
	output += "\nsubgraph "+arrData[index].id+"\n";
	for (const rb of arrData[index].value) {
		output +=rb.str;
	}	

	arrData.forEach(function(item,indexSub,arr){
		if(item.parent==arrData[index].id){
			output += renderRelationSubgraph(arrData,indexSub);
		}
	});
	output += "end\n";
	//output += subgraphTitle + " --> " + element.dest_id+ "\n";
	return output;
}

//句子编辑块
function render_tran_sent_block(book, para, begin, end, channal = 0, readonly = true) {
	let usent_count = _user_sent_buffer.getSentNum(book, para, begin, end);
	let netSent = doc_msg_get_trans(book, para, begin, end);
	let sender = "";
	let sChannalName = "";
	let sent_text = "";
	let sent_lang = "en";
	let objSent = {
                text:"",
                language:"en",
                id:"",
                tag:"[]",
                author:"{}"
            };
	let thischannal;
	let shell_class = "";
	if (channal == 0) {
		//百家言
		shell_class += " channel_0";
		if (netSent.length > 0) {
			sender = netSent[netSent.length - 1].sender;
			sent_text += netSent[netSent.length - 1].data.text;
		} else if (usent_count > 0) {
			sender = _user_sent_buffer.getSentText(book, para, begin, end)[0].nickname;
			sChannalName = "channal name";
			sent_text += _user_sent_buffer.getSentText(book, para, begin, end)[0].text;
		}
	} else {
		sender = "通道的名字";
		shell_class += " mychannel";
		objSent = _user_sent_buffer.getSentText(book, para, begin, end, channal);
		thischannal = channal_getById(channal);
		if (objSent == false) {
			objSent = {
                text:"",
                language:thischannal.lang,
                id:"",
                tag:"[]",
                author:"{}"
            };
			sent_text = "";
		} else {
			sent_text = objSent.text;
		}
		sent_lang = objSent.language;
	}

	let output = "";

	if (readonly == true) {
		shell_class += " readonly";
	}

	output += "<div class='trans_text_block " + shell_class + "' channel_id='" + channal + "'>";
	output += "<div class='trans_text_info' >";

	if (channal == 0) {
		output += "<span class='author'>" + sender + "</span>";
	} else {
		output += "<span style='width: 100%;display: contents;'>";
		output += "<span>";

		if (thischannal) {
			output += "<b>" + thischannal.name + "</b>@";
			if (parseInt(thischannal.power) >= 30) {
				output += gLocal.gui.your;
			} else {
				output += thischannal.nickname;
			}
		} else {
			output += "未知的频道名";
		}
		output += "-[" + thischannal.lang + "]";
		output += "</span>";
		output +=
			"<span style='margin-left: auto;' class='send_status' id='send_" +
			book +
			"_" +
			para +
			"_" +
			begin +
			"_" +
			end +
			"_" +
			channal +
			"'></span>";
	}
	output += "</span><span></span>";
	output += "</div>";
	let id = "tran_pre_" + book + "_" + para + "_" + begin + "_" + end + "_" + channal;
	output += "<div class='trans_text_content' tid = '" + id;
	if (channal == 0) {
		output += "'  pcds='sent-net-all' ";
	} else {
		output += "'  pcds='sent-net' ";
	}

	output +=
		" book='" + book + "' para='" + para + "' begin='" + begin + "' end='" + end + "' channal='" + channal + "'>";
	if (readonly) {
		output += note_init(term_std_str_to_tran(sent_text, channal, getCookie("userid"), sent_lang));
	} else {
		output += "<span id='" + id + "' ";
		output += "onclick=\"sent_edit_click('";
		output += book + "','" + para + "','" + begin + "','" + end + "','" + channal + "',)\">";
        if(typeof objSent !== 'undefined'){
            if (objSent.text == null || objSent.text == "") {
                output += "<span style='color:gray;'>";
                output += "<svg class='icon' style='fill: var(--detail-color);'>";
                output += "<use xlink='http://www.w3.org/1999/xlink' href='svg/icon.svg#ic_mode_edit'>";
                output += "</span>";
            } else {
                output += note_init(term_std_str_to_tran(objSent.text, channal, getCookie("userid"), sent_lang));
            }            
        }


		output += "</span>";
	}

	output += "</div>";
	//句子预览结束
	//编辑框开始
	if (readonly == false) {
		output += "<div style='display:none;'>";
		let id = book + "_" + para + "_" + begin + "_" + end + "_" + channal;
		output += "<textarea id='trans_sent_edit_" + id + "' ";
		output += " onkeyup = \"updateTranslationPreview('" + id + "',this)\" ";
		output +=
			" onchange=\"trans_text_save('" +
			book +
			"','" +
			para +
			"','" +
			begin +
			"','" +
			end +
			"','" +
			channal +
			"')\"";
		output += "class='trans_sent_edit' style='background-color: #f8f8fa;color: black;border-color: silver;' ";
		output += "sent_id='" + objSent.id + "' ";
		output += "book='" + book + "'  para='" + para + "'  begin='" + begin + "'  end='" + end + "' ";
		output +=
			" channal='" +
			channal +
			"'  author='" +
			objSent.author +
			"'  lang='" +
			objSent.language +
			"'  tag='" +
			objSent.tag +
			"' >";
		output += objSent.text;
		output += "</textarea>";
		output += "<div class='trans_text_info' style='justify-content: flex-end;'>";
		output += "<span></span>";
		output += "<button class='icon_btn' ";
		output +=
			"onclick=\"trans_text_save('" +
			book +
			"','" +
			para +
			"','" +
			begin +
			"','" +
			end +
			"','" +
			channal +
			"')\" ";
		output +=
			"title=" +
			gLocal.gui.draft +
			">" +
			'<svg class="icon" style="fill: var(--link-color); height: 2em; width: 2em;"><use xlink:href="svg/icon.svg#ic_save"></use></svg>';
		output += "</button>";
		output += "<button class='icon_btn' ";
		output +=
			"onclick=\"trans_text_send('" +
			book +
			"','" +
			para +
			"','" +
			begin +
			"','" +
			end +
			"','" +
			channal +
			"')\"";
		output +=
			" title=" +
			gLocal.gui.send +
			" disabled>" +
			'<svg class="icon" style="fill: var(--link-color); height: 2em; width: 2em;"><use xlink:href="svg/icon.svg#send_by_paper_plane"></use></svg>';
		output += "</button>";

		output += "</div>";
		output += "</div>";
	}

	if (readonly) {
		output += "<div class='trans_text_info'>" + "<span></span>" + "<span><span class='tools'>";

		output +=
			"<button class='icon_btn' title=" +
			gLocal.gui.accept_copy +
			">" +
			"<svg class='icon' style='fill: var(--link-color); height: 2em; width: 2em; '><use xlink='http://www.w3.org/1999/xlink' href='svg/icon.svg#ic_move_to_inbox'></use></svg>" +
			"</button>";

		if (channal == 0) {
			//百家言 显示更多按钮
			output +=
				"<button class='icon_btn' onclick=\"show_tran_net('" +
				book +
				"','" +
				para +
				"','" +
				begin +
				"','" +
				end +
				"')\" title=" +
				gLocal.gui.message +
				">" +
				'<svg class="icon" style="fill: var(--link-color); height: 2em; width: 2em;"><use xlink:href="plugin/system_message/icon.svg#icon_message"></use></svg>' +
				"</button>";
		}

		output += "</span><span>" + usent_count + "</span></span>";
		output += "</div>";
	}
	output += "</div>";
	return output;
}

function trans_text_save(book, para, begin, end, channal) {
	let textarea = $("#trans_sent_edit_" + book + "_" + para + "_" + begin + "_" + end + "_" + channal);
	if (textarea) {
		let objsent = new Object();
		objsent.id = textarea.attr("sent_id");
		objsent.book = book;
		objsent.paragraph = para;
		objsent.begin = begin;
		objsent.end = end;
		objsent.channal = channal;
		objsent.author = textarea.attr("author");
		objsent.lang = textarea.attr("lang");
		objsent.text = textarea.val();
		_user_sent_buffer.setSent(objsent);
	}
}

function sent_edit_click(book, para, begin, end, channal) {
	$(".trans_sent_edit").parent().hide(200);
	$(
		".trans_sent_edit[book='" +
			book +
			"'][para='" +
			para +
			"'][begin='" +
			begin +
			"'][end='" +
			end +
			"'][channal='" +
			channal +
			"']"
	)
		.parent()
		.show();
		$(
			".trans_sent_edit[book='" +
				book +
				"'][para='" +
				para +
				"'][begin='" +
				begin +
				"'][end='" +
				end +
				"'][channal='" +
				channal +
				"']"
		).css({'height': 'auto'}).height($(
			".trans_sent_edit[book='" +
				book +
				"'][para='" +
				para +
				"'][begin='" +
				begin +
				"'][end='" +
				end +
				"'][channal='" +
				channal +
				"']"
		)[0].scrollHeight+"px");

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
	} catch (error) {
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

	wStyle = "v_" + getNodeText(element, "style");
	if (wStyle == "v_bld") {
		if (wpali.indexOf("{") >= 0) {
			wpali = wpali.replace("{", "<span class='v_bld'>");
			wpali = wpali.replace("}", "</span>");
			wStyle = "";
		}
	}
	if (wNote.indexOf("{{") >= 0 && wNote.indexOf("}}") >= 0) {
		wStyle += " note_ref";
	}

	if (wNote.substring(0, 6) == "=term(") {
		wStyle += " term_word_head";
	} else {
		if (sParent.length > 0) {
			if (term_lookup_my(sParent) != false) {
				wStyle += " term_my";
			} else {
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
		} //如果是标点或数字
		else if (wpali.lastIndexOf(local_letter_str[i_sign].id) != -1) {
			letter_count += 1;
		} //如果有字母
	}
	if (sign_count != 0 && letter_count == 0) {
		output += "<a name='w" + wId + "'>";
	} else {
		output += "<a name='w" + wId + "' onclick='on_word_click(\"" + wId + "\")'>";
	}
	if (wpali) output += '<span id="whead1_' + wid + '" class="whead paliword1 ' + wStyle + '">';
	output += wpali;
	output += "</span>";
	output += "</a>";
	if (wNote.length > 0) {
		output += '<span id="wnote_root_' + wid + '" onclick="show_pop_note(\'' + wid + '\')"><wnh wid="' + wid + '">[1]</wnh></span>';
	} else {
		output += '<span id="wnote_root_' + wid + '" onclick="show_pop_note(\'' + wid + '\')"></span>';
	}
	var newMsg = msg_word_msg_num(wid);
	if (newMsg > 0) {
		output += "<span class='word_msg' onclick=\"word_msg_counter_click('" + wid + "')\">" + newMsg + "</span>";
	}
	output += '<span id="whead2_' + wid + '" class="whead paliword2 ' + wStyle + '">';
	output += "</span>";
	return output;
}

function renderWordBodyInner(element) {
	return renderWordDetailByElement(element);
}

//新的渲染单词块函数
function renderWordDetailByElement_edit_a(xmlElement) {
	if (xmlElement == null) {
		return "";
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

	if (sCase == "?" && _bgColor == "") {
		_caseColor = " class='bookmarkcolorx' ";
	}
	if (g_useMode == "read" || g_useMode == "translate") {
		_bgColor = "";
		_caseColor = "";
		if (sOrg == "?") {
			sOrg = " ";
		}
		if (sMean == "?") {
			sMean = " ";
		}
		if (sCase == "?" || sCase == "?#?") {
			sCase = " ";
		}
	}

	//编辑模式开始
	{
		/*gramma*/
		/*find in dict*/
		var arrGramma = new Array();
		var thisWord = sReal;
		for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
			if (thisWord == g_DictWordList[iDict].Pali) {
				if (
					(g_DictWordList[iDict].Type != "" && g_DictWordList[iDict].Type != "?") ||
					(g_DictWordList[iDict].Gramma != "" && g_DictWordList[iDict].Gramma != "?")
				) {
					var arrCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
					pushNewToList(arrGramma, arrCase);
				}
			}
		}

		if (sCase == "?" || sCase == "?#?") {
			if (arrGramma.length > 0) {
				setNodeText(xmlElement, "case", arrGramma[0]);
				sCase = arrGramma[0];
			} else {
				setNodeText(xmlElement, "case", "?#?");
				sCase = "?#?";
			}
		} else {
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
		} else {
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
		} else {
			currMeaning = getLocalFormulaStr(currGramma, cutString(currMeaning, sReal.length * 6));
		}

		currMeaning = currMeaning.replace(/ /g, "&nbsp;");
		renderMeaning = currMeaning.replace(/{/g, "<span class='grm_add_mean'>");
		renderMeaning = renderMeaning.replace(/}/g, "</span>");
		renderMeaning = renderMeaning.replace(/\[/g, "<span class='grm_add_mean_user'>");
		renderMeaning = renderMeaning.replace(/\]/g, "</span>");
		//格位公式结束
		if (sMean.length == 0) {
			renderMeaning = "<span class='word_space_holder'>"+gLocal.gui.meaning+"</span>";
		}
		//渲染下拉菜单
		_txtOutDetail += "<div class='mean'>";
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		_txtOutDetail += renderMeaning;
		_txtOutDetail += "</p>";
		_txtOutDetail += "<div id='mean_" + wordID + '\' class="case_dropdown-content">';
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";

		//render formula menu

		arrFormula = getFormulaList(currGramma);
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<svg class='edit_icon'><use xlink:href='svg/icon.svg#ic_more'></use></svg>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		newWord = removeFormula_B(orgMeaning);
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" +
			wordID +
			'","mean","[]' +
			newWord +
			"\")'>[" +
			gLocal.gui.none +
			"]</a>";
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>[" + gLocal.gui.auto + "]</a>";
		for (var i in arrFormula) {
			newWord = removeFormula_B(orgMeaning);
			newWord = arrFormula[i].replace("~", newWord);
			newWord = newWord.replace(/{/g, "[");
			newWord = newWord.replace(/}/g, "]");
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>" + arrFormula[i] + "</a>";
		}

		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of meaning*/

		/*org begin 拆分*/

		_txtOutDetail += "<div class='org'  name='w_org'>";
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		let currOrg;
		if (sOrg.length == 0) {
			currOrg = "<span class='word_space_holder'>parts</span>";
		} else {
			currOrg = sOrg;
		}
		_txtOutDetail += currOrg;
		_txtOutDetail += "</p>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		_txtOutDetail += "<div id='parts_" + sId + "'>Loading</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of factors*/

		/*part meaning begin*/

		_txtOutDetail += "<div class='om'  name='w_om'>";
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		sOm = sOm.replace("_un_auto_factormean_", getLocalGrammaStr("_un_auto_factormean_"));

		if (sOm == "?" || sOm.substring(0, 3) == "[a]") {
			var currDefualtFM = "";
			var listFactorForFactorMean = sOrg.split("+");
			for (iFactor in listFactorForFactorMean) {
				currDefualtFM += findFirstMeanInDict(listFactorForFactorMean[iFactor]) + "+"; //拆分元素加号分隔
			}
			currDefualtFM = currDefualtFM.replace(/"  "/g, " ");
			currDefualtFM = currDefualtFM.replace(/"+ "/g, "+");
			currDefualtFM = currDefualtFM.replace(/" +"/g, "+");
			currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1); //去掉尾部的加号 kosalla
			if (currDefualtFM.slice(-1, -2) == "+") {
				currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1);
			}
			currOM = "[a]" + currDefualtFM;
			setNodeText(wordNode, "om", currOM);
		} else {
			currOM = sOm;
		}
		if (currOM.length == 0) {
			currOM = "<span class='word_space_holder''>"+gLocal.gui.partmeaning+"</span>";
		}

		_txtOutDetail += currOM;
		_txtOutDetail += "</p>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		_txtOutDetail += "<div id='partmean_" + sId + "'></div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";

		/*org meaning end*/

		/*begin gramma*/

		_txtOutDetail += "<div class='case'>";
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		var sLocalCase = getLocalGrammaStr(sCase);
		var mArrGramma = sCase.split("#");
		if (mArrGramma.length >= 2) {
			mType = mArrGramma[0];
			mGramma = mArrGramma[1];
			mLocalType = sLocalCase.split("#")[0];
			mLocalGramma = sLocalCase.split("#")[1];
		} else {
			mType = "";
			mGramma = mArrGramma[0];
			mLocalType = "";
			mLocalGramma = sLocalCase.split("#")[0];
		}

		if (mType != "") {
			_txtOutDetail += "<span class='cell'>" + mLocalType + "</span>";
		}

		_txtOutDetail += cutString(mLocalGramma, 30);
		if (mLocalType.length + mLocalGramma.length == 0) {
			_txtOutDetail += "&nbsp;";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		_txtOutDetail += "<div id='gramma_" + sId + "'></div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";

		if(sParentGrammar && sParentGrammar!="" && sParentGrammar!=" "){
			_txtOutDetail += "<span class='cell' style='outline: unset;background-color: wheat;' title='"+sParent2+"'>" + getLocalGrammaStr(sParentGrammar) + "</span>";
		}
		

		//连读词按钮
		if (mType == ".un." || mType == ".comp.") {
			nextElement = com_get_nextsibling(xmlElement);
			if (nextElement != null) {
				//下一个元素存在
				if (getNodeText(nextElement, "un") == sId) {
					//若有孩子則显示收起按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_remove(\"" + wordID + "\")'>";
					_txtOutDetail +=
						'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_join "></use></svg>';
					_txtOutDetail += "</button>";
					var parentElement = document.getElementById("wb" + sId);
					if (parentElement) {
						parentElement.classList.add("un_parent");
					}
				} else {
					//無kid展開按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
					_txtOutDetail +=
						'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_split "></use></svg>';
					_txtOutDetail += "</button> ";
				}
			} else {
				//下一个元素不存在
				_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
				_txtOutDetail +=
					'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_split "></use></svg>';
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
	return _txtOutDetail;
}

function renderWordDetailByElement(xmlElement) {
	return renderWordDetailByElement_edit_a(xmlElement);

	if (xmlElement == null) {
		return "";
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

	if (sMean == "?") {
		_bgColor = " class='bookmarkcolorx' ";
	}

	strBookMarkColor = getNodeText(wordNode, "bmc");
	if (strBookMarkColor.length > 2) {
		var icolor = strBookMarkColor.substr(-1);
		_bgColor = " class='bookmarkcolor" + icolor + "' ";
	}

	// Auto Match Begin

	if (sCase == "?" && _bgColor == "") {
		_caseColor = " class='bookmarkcolorx' ";
	}
	if (g_useMode == "read" || g_useMode == "translate") {
		_bgColor = "";
		_caseColor = "";
		if (sOrg == "?") {
			sOrg = " ";
		}
		if (sMean == "?") {
			sMean = " ";
		}
		if (sCase == "?" || sCase == "?#?") {
			sCase = " ";
		}
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
		} else {
			mType = "";
			mGramma = sCase.split("#")[0];
		}
		//_txtOutDetail += ("<span class='type'>"+mType+"</span> "+mGramma);
		if (mType != "") {
			_txtOutDetail += "<span class='type'>" + mType + "</span>";
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
		} else {
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
				if (
					(g_DictWordList[iDict].Type != "" && g_DictWordList[iDict].Type != "?") ||
					(g_DictWordList[iDict].Gramma != "" && g_DictWordList[iDict].Gramma != "?")
				) {
					var arrCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
					pushNewToList(arrGramma, arrCase);
				}
			}
		}

		if (sCase == "?" || sCase == "?#?") {
			if (arrGramma.length > 0) {
				setNodeText(xmlElement, "case", arrGramma[0]);
				sCase = arrGramma[0];
			} else {
				setNodeText(xmlElement, "case", "?#?");
				sCase = "?#?";
			}
		} else {
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
			if (
				thisWord == g_DictWordList[iDict].Pali &&
				g_DictWordList[iDict].Type != ".root." &&
				g_DictWordList[iDict].Type != ".suf." &&
				g_DictWordList[iDict].Type != ".prf."
			) {
				if (
					wordParent == "" &&
					g_DictWordList[iDict].Parent.length > 0 &&
					g_DictWordList[iDict].Parent != thisWord
				) {
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
							arrMeaning.push(
								g_DictWordList[iDict].dictID +
									"$" +
									arrMeaning.length +
									"$$" +
									arrMean[i] +
									"$" +
									g_DictWordList[iDict].Language
							);
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
				if (
					thisWord == g_DictWordList[iDict].Pali &&
					g_DictWordList[iDict].Type != ".v." &&
					g_DictWordList[iDict].Type != ".n." &&
					g_DictWordList[iDict].Type != ".ti." &&
					g_DictWordList[iDict].Type != ".adj." &&
					g_DictWordList[iDict].Type != ".pron." &&
					g_DictWordList[iDict].Type != ".num."
				) {
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
								arrMeaning.push(
									g_DictWordList[iDict].dictID +
										"$" +
										arrMeaning.length +
										"$*$" +
										getLocalParentFormulaStr(wordGramma0, arrMean[i]) +
										"$" +
										g_DictWordList[iDict].Language
								);
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
				if (
					thisWord == g_DictWordList[iDict].Pali &&
					g_DictWordList[iDict].Type != ".v." &&
					g_DictWordList[iDict].Type != ".n." &&
					g_DictWordList[iDict].Type != ".ti." &&
					g_DictWordList[iDict].Type != ".adj." &&
					g_DictWordList[iDict].Type != ".pron." &&
					g_DictWordList[iDict].Type != ".num."
				) {
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
								arrMeaning.push(
									g_DictWordList[iDict].dictID +
										"$" +
										arrMeaning.length +
										"$**$" +
										getLocalParentFormulaStr(wordGramma1, arrMean[i]) +
										"$" +
										g_DictWordList[iDict].Language
								);
							}
						}
					}
				}
			}
		}

		arrMeaning.sort(sortMeanByDictOrder);
		//arrMeaning.sort(sortMeanByLanguageOrder);
		newMeanList = removeSameWordInArray(arrMeaning);

		sMean = sMean.replace("_un_auto_mean_", getLocalGrammaStr("_un_auto_mean_"));

		if (sMean == "?") {
			//自动匹配一个意思
			//currGrammaMeaning是与语法信息最匹配的一个意思 如果使用这个 与语言排序冲突
			if (currGrammaMeaning.length > 0 && currGrammaMeaning != "?") {
				currMeaning = currGrammaMeaning;
			} else {
				if (newMeanList.length > 0) {
					currMeaning = newMeanList[0].word;
				} else {
					currMeaning = sMean;
				}
			}
		} else {
			currMeaning = removeFormula(sMean);
			currMeaning = getLocalFormulaStr(currGramma, currMeaning);
		}

		orgMeaning = removeFormula(currMeaning);
		if (sReal.length < 4) {
			currMeaning = getLocalFormulaStr(currGramma, cutString(orgMeaning, 24));
		} else {
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
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<svg class='edit_icon'><use xlink:href='svg/icon.svg#ic_more'></use></svg>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		newWord = removeFormula_B(orgMeaning);
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" +
			wordID +
			'","mean","[]' +
			newWord +
			"\")'>[" +
			gLocal.gui.none +
			"]</a>";
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>[" + gLocal.gui.auto + "]</a>";
		for (var i in arrFormula) {
			newWord = removeFormula_B(orgMeaning);
			newWord = arrFormula[i].replace("~", newWord);
			newWord = newWord.replace(/{/g, "[");
			newWord = newWord.replace(/}/g, "]");
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>" + arrFormula[i] + "</a>";
		}

		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of meaning*/

		/*org begin 拆分*/
		/*
	=======
		return renderWordDetailByElement_edit_a(xmlElement);
	
		if (xmlElement == null) {
			return "";
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

	if (sMean == "?") {
		_bgColor = " class='bookmarkcolorx' ";
	}

	strBookMarkColor = getNodeText(wordNode, "bmc");
	if (strBookMarkColor.length > 2) {
		var icolor = strBookMarkColor.substr(-1);
		_bgColor = " class='bookmarkcolor" + icolor + "' ";
	}

	// Auto Match Begin

	if (sCase == "?" && _bgColor == "") {
		_caseColor = " class='bookmarkcolorx' ";
	}
	if (g_useMode == "read" || g_useMode == "translate") {
		_bgColor = "";
		_caseColor = "";
		if (sOrg == "?") {
			sOrg = " ";
		}
		if (sMean == "?") {
			sMean = " ";
		}
		if (sCase == "?" || sCase == "?#?") {
			sCase = " ";
		}
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
		} else {
			mType = "";
			mGramma = sCase.split("#")[0];
		}
		//_txtOutDetail += ("<span class='type'>"+mType+"</span> "+mGramma);
		if (mType != "") {
			_txtOutDetail += "<span class='type'>" + mType + "</span>";
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
		} else {
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
				if (
					(g_DictWordList[iDict].Type != "" && g_DictWordList[iDict].Type != "?") ||
					(g_DictWordList[iDict].Gramma != "" && g_DictWordList[iDict].Gramma != "?")
				) {
					var arrCase = g_DictWordList[iDict].Type + "#" + g_DictWordList[iDict].Gramma;
					pushNewToList(arrGramma, arrCase);
				}
			}
		}

		if (sCase == "?" || sCase == "?#?") {
			if (arrGramma.length > 0) {
				setNodeText(xmlElement, "case", arrGramma[0]);
				sCase = arrGramma[0];
			} else {
				setNodeText(xmlElement, "case", "?#?");
				sCase = "?#?";
			}
		} else {
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
			if (
				thisWord == g_DictWordList[iDict].Pali &&
				g_DictWordList[iDict].Type != ".root." &&
				g_DictWordList[iDict].Type != ".suf." &&
				g_DictWordList[iDict].Type != ".prf."
			) {
				if (
					wordParent == "" &&
					g_DictWordList[iDict].Parent.length > 0 &&
					g_DictWordList[iDict].Parent != thisWord
				) {
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
							arrMeaning.push(
								g_DictWordList[iDict].dictID +
									"$" +
									arrMeaning.length +
									"$$" +
									arrMean[i] +
									"$" +
									g_DictWordList[iDict].Language
							);
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
				if (
					thisWord == g_DictWordList[iDict].Pali &&
					g_DictWordList[iDict].Type != ".v." &&
					g_DictWordList[iDict].Type != ".n." &&
					g_DictWordList[iDict].Type != ".ti." &&
					g_DictWordList[iDict].Type != ".adj." &&
					g_DictWordList[iDict].Type != ".pron." &&
					g_DictWordList[iDict].Type != ".num."
				) {
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
								arrMeaning.push(
									g_DictWordList[iDict].dictID +
										"$" +
										arrMeaning.length +
										"$*$" +
										getLocalParentFormulaStr(wordGramma0, arrMean[i]) +
										"$" +
										g_DictWordList[iDict].Language
								);
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
				if (
					thisWord == g_DictWordList[iDict].Pali &&
					g_DictWordList[iDict].Type != ".v." &&
					g_DictWordList[iDict].Type != ".n." &&
					g_DictWordList[iDict].Type != ".ti." &&
					g_DictWordList[iDict].Type != ".adj." &&
					g_DictWordList[iDict].Type != ".pron." &&
					g_DictWordList[iDict].Type != ".num."
				) {
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
								arrMeaning.push(
									g_DictWordList[iDict].dictID +
										"$" +
										arrMeaning.length +
										"$**$" +
										getLocalParentFormulaStr(wordGramma1, arrMean[i]) +
										"$" +
										g_DictWordList[iDict].Language
								);
							}
						}
					}
				}
			}
		}

		arrMeaning.sort(sortMeanByDictOrder);
		//arrMeaning.sort(sortMeanByLanguageOrder);
		newMeanList = removeSameWordInArray(arrMeaning);

		sMean = sMean.replace("_un_auto_mean_", getLocalGrammaStr("_un_auto_mean_"));

		if (sMean == "?") {
			//自动匹配一个意思
			//currGrammaMeaning是与语法信息最匹配的一个意思 如果使用这个 与语言排序冲突
			if (currGrammaMeaning.length > 0 && currGrammaMeaning != "?") {
				currMeaning = currGrammaMeaning;
			} else {
				if (newMeanList.length > 0) {
					currMeaning = newMeanList[0].word;
				} else {
					currMeaning = sMean;
				}
			}
		} else {
			currMeaning = removeFormula(sMean);
			currMeaning = getLocalFormulaStr(currGramma, currMeaning);
		}

		orgMeaning = removeFormula(currMeaning);
		if (sReal.length < 4) {
			currMeaning = getLocalFormulaStr(currGramma, cutString(orgMeaning, 24));
		} else {
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
		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<svg class='edit_icon'><use xlink:href='svg/icon.svg#ic_more'></use></svg>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		newWord = removeFormula_B(orgMeaning);
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" +
			wordID +
			'","mean","[]' +
			newWord +
			"\")'>[" +
			gLocal.gui.none +
			"]</a>";
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>[" + gLocal.gui.auto + "]</a>";
		for (var i in arrFormula) {
			newWord = removeFormula_B(orgMeaning);
			newWord = arrFormula[i].replace("~", newWord);
			newWord = newWord.replace(/{/g, "[");
			newWord = newWord.replace(/}/g, "]");
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" + wordID + '","mean","' + newWord + "\")'>" + arrFormula[i] + "</a>";
		}

		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";
		/*end of meaning*/

		/*org begin 拆分*/
		/*
>>>>>>> bc16896ae743d21de32c24d5ffe63b6be00a315c
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

		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";

		if (sOrg == "?") {
			if (arrOrg.length > 0) {
				currOrg = arrOrg[0];
			} else {
				currOrg = sOrg;
			}
			setNodeText(wordNode, "org", currOrg);
		} else {
			currOrg = sOrg;
		}

		_txtOutDetail += currOrg;
		if (arrOrg.length > 0) {
			_txtOutDetail += "<svg class='edit_icon'>";
			_txtOutDetail += "<use xlink:href='svg/icon.svg#ic_down'></use>";
			_txtOutDetail += "</svg>";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		_txtOutDetail +=
			"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" +
			wordID +
			'","org","")\'>' +
			gLocal.gui.empty1 +
			"</button>";
		_txtOutDetail +=
			"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='show_word_map(\"" +
			wordID +
			"\")'>" +
			gLocal.gui.wordmap +
			"</button>";
		//新加 base 信息 vn
		_txtOutDetail += '<div class="case_dropdown-org">';
		_txtOutDetail += '<div class="case_dropdown-first">';

		_txtOutDetail += "<a style='z-index:250; position:absolute; margin-right:2em;'>";
		_txtOutDetail += sParent + "</a>";
		_txtOutDetail += "<span style='z-index:220' class='case_dropdown-title'>";
		_txtOutDetail += gLocal.gui.more + "»</span>";
		_txtOutDetail += "</div>";

		for (i in arrOrg) {
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" + wordID + '","org","' + arrOrg[i] + "\")'>" + arrOrg[i] + "</a>";
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
			currDefualtFM += findFirstMeanInDict(listFactorForFactorMean[iFactor]) + "+"; //拆分元素加号分隔
		}
		currDefualtFM = currDefualtFM.replace(/"  "/g, " ");
		currDefualtFM = currDefualtFM.replace(/"+ "/g, "+");
		currDefualtFM = currDefualtFM.replace(/" +"/g, "+");
		currDefualtFM = currDefualtFM.substring(0, currDefualtFM.length - 1); //去掉尾部的加号 kosalla

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

		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		sOm = sOm.replace("_un_auto_factormean_", getLocalGrammaStr("_un_auto_factormean_"));

		if (sOm == "?" || sOm.substring(0, 3) == "[a]") {
			if (arrOM.length > 0) {
				currOM = arrOM[0];
			} else {
				currOM = "[a]" + currDefualtFM;
			}
			setNodeText(wordNode, "om", currOM);
		} else {
			currOM = sOm;
		}

		_txtOutDetail += currOM;
		if (arrOM.length > 0) {
			_txtOutDetail += " ▾";
		}
		_txtOutDetail += "</p>";
		_txtOutDetail += '<div class="case_dropdown-content">';
		_txtOutDetail +=
			"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" +
			wordID +
			'","om","")\'>' +
			gLocal.gui.empty1 +
			"</button>";
		_txtOutDetail +=
			"<a onclick='fieldListChanged(\"" +
			wordID +
			'","om","[a]' +
			currDefualtFM +
			"\")'>[" +
			gLocal.gui.auto +
			"]" +
			currDefualtFM +
			"</a>";
		for (i in arrOM) {
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" + wordID + '","om","' + arrOM[i] + "\")'>" + arrOM[i] + "</a>";
		}
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div> ";

		/*org meaning end*/

		/*begin gramma*/

		_txtOutDetail += "<div class='case'>";

		_txtOutDetail += '<div class="case_dropdown">';
		_txtOutDetail += "<p class='case_dropbtn' >";
		sLocalCase = getLocalGrammaStr(sCase);
		var mArrGramma = sCase.split("#");
		if (mArrGramma.length >= 2) {
			mType = mArrGramma[0];
			mGramma = mArrGramma[1];
			mLocalType = sLocalCase.split("#")[0];
			mLocalGramma = sLocalCase.split("#")[1];
		} else {
			mType = "";
			mGramma = mArrGramma[0];
			mLocalType = "";
			mLocalGramma = sLocalCase.split("#")[0];
		}

		if (mType != "") {
			_txtOutDetail += "<span class='cell'>" + mLocalType + "</span>";
		}
		_txtOutDetail += cutString(mLocalGramma, 30);

		_txtOutDetail += "</p>";

		_txtOutDetail += '<div class="case_dropdown-content">';
		for (i in arrGramma) {
			_txtOutDetail +=
				"<a onclick='fieldListChanged(\"" +
				wordID +
				'","case","' +
				arrGramma[i] +
				"\")'>" +
				cutString(getLocalGrammaStr(arrGramma[i]), 30) +
				"</a>";
		}
		_txtOutDetail += "</div>";
		_txtOutDetail += "</div>";

		//连读词按钮
		if (mType == ".un." || mType == ".comp.") {
			nextElement = com_get_nextsibling(xmlElement);
			if (nextElement != null) {
				//下一个元素存在
				if (getNodeText(nextElement, "un") == sId) {
					//若有孩子則显示收起按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_remove(\"" + wordID + "\")'>";
					_txtOutDetail +=
						'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_join "></use></svg>';
					_txtOutDetail += "</button>";
					var parentElement = document.getElementById("wb" + sId);
					if (parentElement) {
						parentElement.classList.add("un_parent");
					}
				} else {
					//無kid展開按鈕
					_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
					_txtOutDetail +=
						'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_split "></use></svg>';
					_txtOutDetail += "</button> ";
				}
			} else {
				//下一个元素不存在
				_txtOutDetail += "<button class='in_word_button' onclick='edit_un_split(\"" + wordID + "\")'>";
				_txtOutDetail +=
					'<svg class="icon" ><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_split "></use></svg>';
				_txtOutDetail += "</button> ";
			}
		}
		//连读词按钮 结束
		//爷爷和父亲的关系 如pp
		if (wordGranfatherGramma.length > 0 && wordGranfatherGramma.length < 6) {
			_txtOutDetail +=
				'<span  class="tooltip">«' +
				getLocalGrammaStr(wordGranfatherGramma) +
				'<span class="tooltiptext tooltip-bottom">' +
				wordGranfather +
				"</span>" +
				"</span> ";
		}

		_txtOutDetail += "</div> ";
		/*end of gramma*/

		//Auto Match Finished

		_txtOutDetail += "</div>";
	}

	return _txtOutDetail;
}

function renderWordNoteDivByParaNo(book, paragraph) {}
/*
paragraph word note
*/
function renderWordNoteDivByElement(elementBlock) {
	let output = "";
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book");
	let paragraph = getNodeText(xmlParInfo, "paragraph");
	let bId = getNodeText(xmlParInfo, "id");

	let type = getNodeText(xmlParInfo, "type");
	let allWord = elementBlock.getElementsByTagName("word");
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
		if (oneNote == "") {
			//有内容
			$("#wnote_root_" + id).html("");
		} else {
			$("#wnote_root_" + id).html("<wnh wid='" + id + "'></wnh>");
			output += "<div>";
			output += "<wnc wid='" + id + "'></wnc>";
			output += "<strong>" + pali + ":</strong>";
			output += oneNote;
			output += "</div>";
		}
	}
	return output;
}
function refreshWordNoteDiv(elementBlock) {
	let html = renderWordNoteDivByElement(elementBlock);
	let xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	let book = getNodeText(xmlParInfo, "book");
	let paragraph = parseInt(getNodeText(xmlParInfo, "paragraph"));
	let noteid = "wnote_" + book + "_" + (paragraph - 1);
	try {
		if (html == "") {
			$("#" + noteid).hide();
		} else {
			$("#" + noteid).html(html);
			$("#" + noteid).show();
		}
	} catch (e) {
		console.error(e.message + noteid);
	}
}

function refreshWordNote(elementBlock) {
	var output = "";
	xmlParInfo = elementBlock.getElementsByTagName("info")[0];
	book = getNodeText(xmlParInfo, "book");
	paragraph = getNodeText(xmlParInfo, "paragraph");
	bId = getNodeText(xmlParInfo, "id");

	type = getNodeText(xmlParInfo, "type");
	var allWord = elementBlock.getElementsByTagName("word");
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
				} else {
					note = "<term guid='" + termId + "' pos='wbw'></term>";
				}
			}

			output += '<p><a href="#word_note_root_' + id + '" name="word_note_' + id + '">[' + iNoteCounter + "]</a>";
			if (note.match("{") && note.match("}")) {
				note = note.replace("{", "<strong>");
				note = note.replace("}", "</strong>");
				if (note.length > 100) {
					shortNote = note.substring(0, 99);
					otherNode = note.substring(100);
					output +=
						shortNote +
						'<span class="full_note_handle">...<span class="full_note">' +
						otherNode +
						"</span></span>";
				} else {
					output += note;
				}
			} else {
				if (note.length > 100) {
					shortNote = note.substring(0, 99);
					otherNode = note.substring(100);
					output +=
						shortNote +
						'<span class="full_note_handle">...<span class="full_note">' +
						otherNode +
						"</span></span>";
				} else {
					output += "<strong>" + pali + ":</strong>" + note;
				}
			}

			output += "</p>";
			if (termId) {
				document.getElementById("wnote_root_" + id).innerHTML =
					"<a onclick=\"term_show_win('" +
					termId +
					'\')" name="word_note_root_' +
					id +
					'">[' +
					iNoteCounter +
					"]</a>";
			} else {
				document.getElementById("wnote_root_" + id).innerHTML =
					'<a href="#word_note_' + id + '" name="word_note_root_' + id + '">[' + iNoteCounter + "]</a>";
			}
		} else {
			noteRootObj = document.getElementById("wnote_root_" + id);
			if (noteRootObj) {
				document.getElementById("wnote_root_" + id).innerHTML = "";
			}
		}
	}
	if (output == "") {
		document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "none";
	} else {
		document.getElementById("wnote_" + book + "_" + (paragraph - 1)).style.display = "block";
	}
	document.getElementById("wnote_" + book + "_" + (paragraph - 1)).innerHTML = output;
}

function updateWordNote(element) {
	let paliword = getNodeText(element, "real");
	let wnote = getNodeText(element, "note");
	wnote = note_init(wnote);
	let id = getNodeText(element, "id");

	if (wnote == "") {
		$("#wnote_root_" + id).html("");
		$("#wnn_" + id).html("");
	} else {
		$("#wnote_root_" + id).html("<wnh wid='" + id + "'></wnh>");
		let output = "";
		output += "<wnc wid='" + id + "'></wnc>";
		let head = "";
		let content = "";
		if (wnote.match("{") && wnote.match("}")) {
			wnote = wnote.replace("{", "<strong>");
			wnote = wnote.replace("}", "</strong>");
		} else {
			output += "<strong>" + paliword + ":</strong>";
		}
		output += wnote;
		$("#wnn_" + id).html(output);
	}
}

function updateWordCommentary(element) {}

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
	} catch (error) {
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
	} catch (error) {
		var_dump(error);
	}
}

//根据xmlDocument 对象中的单词GUID返回单词块字符串（不含Pali）
//返回 字符串

function renderWordDetailById(wordID) {
	var mXML = gXmlBookDataBody.getElementsByTagName("word");
	var wordIndex = getWordIndex(wordID);

	return renderWordDetailByElement(mXML[wordIndex]);
}

function reloadPar(parIndex) {
	bookId = gArrayDocParagraph[parIndex].book;
	parNo = gArrayDocParagraph[parIndex].paragraph;

	var htmlBlock = document.getElementById("par_" + bookId + "_" + (parNo - 1));
	if (htmlBlock.style.display != "none") {
		return false;
	}

	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		currBookId = getNodeText(xmlParInfo, "book");
		currParNo = getNodeText(xmlParInfo, "paragraph");
		if (bookId == currBookId && currParNo == parNo) {
			insertBlockToHtml(allBlock[iBlock]);
		}
	}
	return true;
}

function removePar(parIndex) {
	bookId = gArrayDocParagraph[parIndex].book;
	parNo = gArrayDocParagraph[parIndex].paragraph;
	if (gCurrModifyWindowParNo == parIndex) {
		//关闭单词修改窗口
		closeModifyWindow();
	}

	var htmlBlock = document.getElementById("par_" + bookId + "_" + (parNo - 1));
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
		return true;
	} else {
		return false;
	}
}
function refresh_Dispaly_Cap() {
	var new_par_length = gPalitext_length / gtext_max_length;

	for (refresh_i in allTextLen_array) {
		gNewArrayDocParagraph.push(allTextLen_array[refresh_i].length / gtext_max_length);
	}
}
function prev_page() {
	//向上翻页
	gVisibleParBeginOld = gVisibleParBegin;
	gVisibleParEndOld = gVisibleParEnd;
	if (g_allparlen_array[gVisibleParEnd - 1] - g_allparlen_array[gVisibleParBegin - 1] <= gDisplayCapacity) {
		gVisibleParBegin -= 1;
	} else if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin - 1] > gDisplayCapacity) {
		gVisibleParBegin -= 1;
		gVisibleParEnd -= 1;
	}

	if (gVisibleParBegin < 0) {
		//如果到顶，则恢复原始值
		gVisibleParBegin = 0;
		//gVisibleParEnd=gDisplayCapacity;
		if (gVisibleParEnd >= gArrayDocParagraph.length) {
			//如果到底，则锁死最大值
			gVisibleParEnd = gArrayDocParagraph.length - 1;
		}
	}

	updataView();
}

function next_page() {
	//向下翻页
	gVisibleParBeginOld = gVisibleParBegin;
	gVisibleParEndOld = gVisibleParEnd;

	if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin + 1] <= gDisplayCapacity) {
		gVisibleParEnd += 1;
	} else if (g_allparlen_array[gVisibleParEnd + 1] - g_allparlen_array[gVisibleParBegin + 1] > gDisplayCapacity) {
		gVisibleParBegin += 1;
		gVisibleParEnd += 1;
	}

	if (gVisibleParEnd >= gArrayDocParagraph.length) {
		//如果到底
		gVisibleParEnd = gArrayDocParagraph.length - 1;
		//gVisibleParBegin=gVisibleParEnd-gDisplayCapacity;
		if (gVisibleParBegin < 0) {
			gVisibleParBegin = 0;
		}
	}
	updataView();
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
	updataView();
}

function updataView() {
	var topNewDivArray = Array(); //在顶端新加入的块列表
	for (var iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
		if (isParInView(iPar)) {
			isLoadNew = reloadPar(iPar);
			if (isLoadNew) {
				if (iPar < gVisibleParBeginOld) {
					topNewDivArray.push(iPar);
				}
			}
		} else {
			removePar(iPar);
		}
	}
	word_mouse_event();
	//console.log("updataview");
	//console.log(topNewDivArray.toString());
}

function render_word_mean_menu2(spell) {
	var wReal = spell;

	var output = "<div>";
	for (var iDict = 0; iDict < mDict[spell].length; iDict++) {
		output += "<div>";
		output += "<span>dict_name</span>";
		var meanGroup = partList2[i].mean.split("$");
		for (var iMean = 0; iMean < meanGroup.length; iMean++) {
			output +=
				"<span class='wm_one_mean' onclick='fieldListChanged(\"" +
				wId +
				'","mean","' +
				meanGroup[iMean] +
				"\")'>" +
				meanGroup[iMean] +
				"</span>";
		}
		output += "</div>";
	}
	output += "</div>";

	return;

	var sWord = new Array(wReal);
	sWord = sWord.concat(render_get_word_parent_list(wReal));
	for (var iWord in sWord) {
		output += '<div class="case_dropdown-first">';
		output +=
			'<a style="z-index:250; position:absolute; margin-right:2em;" onclick=\'dict_search("' +
			sWord[iWord] +
			"\")'>";
		output += sWord[iWord] + "</a>";
		output += '<span style="z-index:220" class="case_dropdown-title" onclick="submenu_show_detail(this)">';
		output += '<svg class="icon" style="fill:var(--main-color)"><use xlink:href="svg/icon.svg#ic_add"></use></svg>';
		output += '</span><div class="case_dropdown-detail">';
		var partList = render_get_word_mean_list(sWord[iWord]);
		var L_width_mean = 0;

		var partList2 = repeat_combine(partList);
		//计算字符长度并控制
		for (var i in partList2) {
			var L_width = getLocalDictname(partList2[i].mean).replace(/[\u0391-\uFFE5]/g, "aa").length;
			if (L_width_mean < L_width / 1.7) {
				L_width_mean = L_width / 1.7;
			}
		}
		for (var i in partList2) {
			var meanGroup = partList2[i].mean.split("$");
			var htmlMean = "";
			output += "<a style='display:flex; flex-wrap: wrap;'>";
			output +=
				"<div id='div_dictname_" + wId + "_" + iWord + "_" + i + "' style='margin-right: auto; display:flex;'>";
			//output  += "<a ><div id='div_dictname_"+wId+"_"+iWord+"_"+i+"' style='width: "+L_width_dictname+"em; display:flex'>"
			output += "<span id='span_dictname_" + wId + "_" + iWord + "_" + i + "'";
			output += "style='height: 1.5em;' class='wm_dictname' >"; //onclick='fieldListChanged1(\""+wId+"\",\"mean\",\""+partList2[i].mean+"\")'
			output += getLocalDictname(partList2[i].dict) + "</span>";
			output += "</div>";
			output +=
				"<div id='div_type_" + wId + "_" + iWord + "_" + i + "' style='margin-left: 0.4em; display:flex'>";
			//output  += "<div id='div_type_"+wId+"_"+iWord+"_"+i+"' style='width: "+L_width_type+"em; display:flex'>"
			output +=
				"<span id='span_type_" +
				wId +
				"_" +
				iWord +
				"_" +
				i +
				"' style='height: 1.5em;' class='wm_wordtype'>" +
				getLocalGrammaStr(partList2[i].type) +
				"</span>";

			for (var iMean in meanGroup) {
				if (meanGroup[iMean] != "") {
					htmlMean +=
						"<span class='wm_one_mean' onclick='fieldListChanged(\"" +
						wId +
						'","mean","' +
						meanGroup[iMean] +
						"\")'>" +
						meanGroup[iMean] +
						"</span>";
				}
			}
			output += "</div><div style='width:15em; display:flex; flex-wrap: wrap;'>" + htmlMean + "</div>";
			output += "</a>";
		}
		output += "</div></div>";
	}
	output += "</div></div></div>";
	return output;
}

/**
 * 渲染单词意思下拉菜单
 * @param {xmlnode} xElement 单词xml节点
 * @returns 
 */
function render_word_mean_menu(xElement) {
	var wMean = getNodeText(xElement, "mean");
	var wReal = getNodeText(xElement, "real");
	var wId = getNodeText(xElement, "id");
	var renderMeaning = wMean.replace(/{/g, "<span class='grm_add_mean'>");
	renderMeaning = renderMeaning.replace(/}/g, "</span>");
	renderMeaning = renderMeaning.replace(/\[/g, "<span class='grm_add_mean_user'>");
	renderMeaning = renderMeaning.replace(/\]/g, "</span>");

	var output = "";
	output += '<div class="case_dropdown">';
	output += "<p class='case_dropbtn' >";
	output += renderMeaning;
	output += "</p>";
	output += '<div class="case_dropdown-content">';
	output += "<button ";
	output += "style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' ";
	output += "onclick='fieldListChanged(\"" + wId + '","mean","")\'>' ;
	output +=  gLocal.gui.empty1 ;
	output += '</button>';
	
	output += '<div class="case_dropdown-org">';

	var sWord = new Array(wReal);
	sWord = sWord.concat(render_get_word_parent_list(wReal));
	for (var iWord in sWord) {
		output += '<div class="case_dropdown-first">';
		output +=
			'<a style="z-index:250; position:absolute; margin-right:2em;" onclick=\'dict_search("' +
			sWord[iWord] +
			"\")'>";
		output += sWord[iWord] + "</a>";
		output += '<span style="z-index:220" class="case_dropdown-title" onclick="submenu_show_detail(this)">';
		output += '<svg class="icon" style="fill:var(--main-color)"><use xlink:href="svg/icon.svg#ic_add"></use></svg>';
		output += "</span>";
		output += '<div class="case_dropdown-detail">';

		var partList = render_get_word_mean_list(sWord[iWord]);
		var L_width_mean = 0;

		var partList2 = repeat_combine(partList);

		for (var i in partList2) {
			var L_width = getLocalDictname(partList2[i].mean).replace(/[\u0391-\uFFE5]/g, "aa").length;
			if (L_width_mean < L_width / 1.7) {
				L_width_mean = L_width / 1.7;
			}
		}
		for (var i in partList2) {
			var meanGroup = partList2[i].mean.split("$");
			var htmlMean = "";
			output += "<a style='display:flex; flex-wrap: wrap;'>";
			output +=
				"<div id='div_dictname_" + wId + "_" + iWord + "_" + i + "' style='margin-right: auto; display:flex;'>";
			output += "<span id='span_dictname_" + wId + "_" + iWord + "_" + i + "'";
			output += "style='height: 1.5em;' class='wm_dictname' >"; //onclick='fieldListChanged1(\""+wId+"\",\"mean\",\""+partList2[i].mean+"\")'
			output += getLocalDictname(partList2[i].dict) + "</span>";
			output += "</div>";
			output +=
				"<div id='div_type_" + wId + "_" + iWord + "_" + i + "' style='margin-left: 0.4em; display:flex'>";
			output +=
				"<span id='span_type_" + wId + "_" + iWord + "_" + i + "' style='height: 1.5em;' class='wm_wordtype'>";
			if (partList2[i].type == ".n:base.") {
				output += getLocalGrammaStr(partList2[i].gramma);
			} else {
				output += getLocalGrammaStr(partList2[i].type);
			}

			output += "</span>";

			for (var iMean in meanGroup) {
				if (meanGroup[iMean] != "") {
					htmlMean +=
						"<span class='wm_one_mean' onclick='fieldListChanged(\"" +
						wId +
						'","mean","' +
						meanGroup[iMean] +
						"\")'>" +
						meanGroup[iMean] +
						"</span>";
				}
			}
			output += "</div>";
			output += "<div style='width:15em; display:flex; flex-wrap: wrap;'>" + htmlMean + "</div>";
			output += "</a>";
		}
		output += "</div></div>";
	}
	output += "</div></div></div>";
	return output;
}

/**
 * 获取单词意思列表
 * @param {string} sWord 
 * @returns 
 */
function render_get_word_mean_list(sWord) {
	var wReal = sWord;

	var output = Array();

	for (var x in g_DictWordList) {
		if (g_DictWordList[x].Pali == wReal || g_DictWordList[x].Real == wReal) {
			//用用户设置的语言过滤结果
			if (dict_language_enable.indexOf(g_DictWordList[x].Language) >= 0) {
				if (
					g_DictWordList[x].Mean != "" &&
					g_DictWordList[x].Type != ".part." &&
					g_DictWordList[x].Type != ".root." &&
					g_DictWordList[x].Type != ".suf." &&
					g_DictWordList[x].Type != ".pfx."
				) {
					var newPart = Object();
					newPart.dict = g_DictWordList[x].dictname;
					newPart.mean = g_DictWordList[x].Mean;
					newPart.type = g_DictWordList[x].Type;
					var bIsExist = false;
					for (var iBuffer in output) {
						if (
							output[iBuffer].dict == newPart.dict &&
							output[iBuffer].type == newPart.type &&
							output[iBuffer].mean == newPart.mean
						) {
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
	return output;
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
	return output;
}
function repeat_combine(list) {
	var list_new = new Array();

	for (var repeat_combine_i = 0; repeat_combine_i < list.length; repeat_combine_i++) {
		var repeat_string = new Object();
		var repeat_combine_j = 0;
		repeat_string.dict = list[repeat_combine_i].dict;
		repeat_string.type = list[repeat_combine_i].type;
		repeat_string.mean = list[repeat_combine_i].mean;
		for (repeat_combine_j = repeat_combine_i + 1; repeat_combine_j < list.length; repeat_combine_j++) {
			if (
				list[repeat_combine_j].dict == list[repeat_combine_i].dict &&
				list[repeat_combine_j].type == list[repeat_combine_i].type
			) {
				repeat_string.mean = repeat_string.mean + "$" + list[repeat_combine_j].mean;
				repeat_string.mean = repeat_string.mean.replace(/\$\$/g, "$");
				repeat_string.mean = repeat_string.mean.replace(/\$ /g, "$");
				repeat_string.mean = repeat_string.mean.replace(/ \$/g, "$");
				repeat_combine_i = repeat_combine_j;
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

	return list_new;
}

function show_pop_note(wordid) {
	let html = $("wnc[wid='" + wordid + "']")
		.parent()
		.html();
	$("#word_note_pop_content").html(
		html
		/*note_init(doc_word("#" + wordid).val("note"))*/
	);
	$("#word_note_pop").show("500");
}

function refreshNoteNumber() {
	$("wnh").each(function (index, element) {
		let id = $(this).attr("wid");
		//$(this).html("<a href='#word_note_"+id+"'  name=\"word_note_root_"+id+"\">"+(index+1)+"</a>");
		$(this).html("<span>" + (index + 1) + "</span>");
	});

	$("wnc").each(function (index, element) {
		let id = $(this).attr("wid");
		$(this).html("<a href='#word_note_root_" + id + "' name=\"word_note_" + id + '">[' + (index + 1) + "]</a>");
	});

	note_refresh_new();
}
