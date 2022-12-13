var g_DictWordList = new Array();
var g_DocWordMean = new Array();
var g_dictList = new Array();
var g_DictWordNew = new Object();
var g_DictWordUpdataIndex = 0; //正在更新的记录在内存字典表中的索引号
var g_InlineDictWordList = new Array();
var g_CurrDictBuffer = null;
var g_key_match_str = "";

var myFormula = Array(); //用户词典里的格位公式

var g_DictCount = 0;
var g_currEditWord = -1; //当前正在编辑词的id
var g_eCurrWord = null; //当前正在编辑词的element对象
var g_currBookMarkColor = "0";
var g_dictFindParentLevel = 0;
var g_dictFindAllDone = false;

var g_currAutoMatchDictType = "user";

var g_caseSelect = new Array("", "", "", "");

var gEditorTranslateEditBlockId = -1;
var gEditorNoteEditBlockId = -1;
var gEditorHeadingEditBlockId = -1;
var gEditorNewHeadingBookId = "";
var gEditorNewHeadingPar = "";
var g_fileid = 0;
var g_docid = "";

var g_op = "";
var g_book = "";
var g_para = "";
var g_channal = "";

var gCurrModifyWindowParNo = -1;

var gUserSetup;

var mDictQueue = Array();

var gNaviCurrPanalId = "";
function setNaviVisibility(strObjId = "") {
	var objNave = document.getElementById("leftmenuinner");
	var objblack = document.getElementById("BV");
	if (strObjId == "") {
		objblack.style.display = "none";
		objNave.className = "viewswitch_off";
	} else {
		$("#" + strObjId).show();
		$("#" + strObjId)
			.siblings()
			.hide();
		if (strObjId == gNaviCurrPanalId) {
			if (objNave.className == "viewswitch_off") {
				objblack.style.display = "block";
				objNave.className = "viewswitch_on";
			} else {
				objblack.style.display = "none";
				objNave.className = "viewswitch_off";
			}
		} else {
			objblack.style.display = "block";
			objNave.className = "viewswitch_on";
		}
	}
	gNaviCurrPanalId = strObjId;
}
//选项卡函数
function select_modyfy_type(itemname, idname) {
	document.getElementById("modify_detaile").style.display = "none";
	document.getElementById("modify_bookmark").style.display = "none";
	document.getElementById("modify_note").style.display = "none";
	document.getElementById("modify_spell").style.display = "none";
	document.getElementById("modify_apply").style.display = "block";
	document.getElementById("detail_li").className = "common-tab_li";
	document.getElementById("mark_li").className = "common-tab_li";
	document.getElementById("note_li").className = "common-tab_li";
	document.getElementById("spell_li").className = "common-tab_li";

	document.getElementById(itemname).style.display = "block";
	document.getElementById(idname).className = " common-tab_li_act";
}

function menuSelected(obj) {
	var objMenuItems = document.getElementsByClassName("menu");
	for (var i = 0; i < objMenuItems.length; i++) {
		objMenuItems[i].style.display = "none";
	}
	var objThisItem = document.getElementById(obj.value);
	objThisItem.style.display = "block";
}
function menuSelected_2(obj, id_name, class_Name) {
	var objMenuItems = document.getElementsByClassName(class_Name);
	var id_array = new Array();
	for (var i = 0; i < objMenuItems.length; i++) {
		if (objMenuItems[i].id.split("_")[0] == obj.id.split("_")[0]) {
			objMenuItems[i].style.display = "none";
			id_array.push(objMenuItems[i].id);
		}
	}
	var objThisItem = document.getElementById(obj.id);
	objThisItem.style.display = "block";
	for (menu_selected_i in id_array) {
		document.getElementById(id_array[menu_selected_i] + "_li").className = "common-tab_li";
	}
	//document.getElementById('content_menu_li').className = " common-tab_li";
	//document.getElementById('bookmark_menu_li').className = " common-tab_li";
	refreshBookMark();
	//document.getElementById('project_menu_li').className = " common-tab_li";
	//document.getElementById('dictionary_menu_li').className = " common-tab_li";
	//document.getElementById('layout_menu_li').className = " common-tab_li";
	//document.getElementById('plugin_menu_li').className = " common-tab_li";

	document.getElementById(id_name).className = " common-tab_li_act";
}

var editor_xmlhttp;
var currMatchingDictNum = 0; //当前正在查询的字典索引
function editor_getDictFileList() {
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		editor_xmlhttp = new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		editor_xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d = new Date();
	editor_xmlhttp.onreadystatechange = editor_serverResponse;
	editor_xmlhttp.open("GET", "./dict_get_list.php?t=" + d.getTime(), true);
	editor_xmlhttp.send();
}

function editor_serverResponse() {
	if (editor_xmlhttp.readyState == 4) {
		// 4 = "loaded"
		if (editor_xmlhttp.status == 200) {
			// 200 = "OK"
			var DictFileList = new Array();
			eval(editor_xmlhttp.responseText);
			for (x in local_dict_list) {
				g_dictList.push(local_dict_list[x]);
			}
			var fileList = "";
			for (x in local_dict_list) {
				if (local_dict_list[x].used) {
					fileList =
						fileList +
						"<p><input id='id_dict_file_list_" +
						x +
						"'  type='checkbox' style='width: 20px; height: 20px' checked onclick='dict_active(this," +
						x +
						")'/>" +
						local_dict_list[x].filename +
						"<span id='dict_result_" +
						x +
						"'></span></p>";
				} else {
					fileList =
						fileList +
						"<p><input id='id_dict_file_list_" +
						x +
						"'  type='checkbox' style='width: 20px; height: 20px' onclick='dict_active(this," +
						x +
						")'/>" +
						local_dict_list[x].filename +
						"<span id='dict_result_" +
						x +
						"'></span></p>";
				}
			}
			document.getElementById("basic_dict_list").innerHTML = fileList;
		} else {
			document.getElementById("basic_dict_list") = "Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}

function dict_active(obj, dictIndex) {
	if (this.checked) {
		g_dictList[dictIndex].used = true;
	} else {
		g_dictList[dictIndex].used = false;
	}
}

function editor_windowsInit() {
	renderChannelList();
	$("see").on("click",function () {
		var to = $(this).attr("to");
		var link;
		if (to) {
			link = to;
		} else {
			link = $(this).text();
		}
		alert(link);
		dict_search(link);
	});

	var strSertch = location.search;
	if (strSertch.length > 0) {
		strSertch = strSertch.substr(1);
		let sertchList = strSertch.split("&");
		for (const param of sertchList) {
			let item = param.split("=");
			switch (item[0]) {
				case "filename":
					g_filename = item[1];
					break;
				case "fileid":
					g_docid = item[1];
					break;
				case "doc_id":
					g_docid = item[1];
					break;
				case "op":
					g_op = item[1];
					break;
				case "book":
					g_book = item[1];
					break;
				case "para":
				case "par":
					g_para = item[1];
					break;
				case "channal":
				case "channel":
					g_channal = item[1];
					break;
			}
		}
	}
	checkCookie();
	setUseMode("Edit");

	editor_getDictFileList();
	document.getElementById("id_info_window_select").value = "view_dict_curr";
	windowsSelected(document.getElementById("id_info_window_select"));
	document.getElementById("id_info_panal").style.height = "0px";

	palicannon_init();

	//载入我的术语词典
	term_get_my();
	//载入全部术语词头
	term_get_all_pali();
	//载入格位公式
	load_my_formula();

	switch (g_op) {
		case "new":
			document.getElementById("wizard_div").style.display = "flex";
			document.getElementById("id_editor_menu_select").value = "menu_pali_cannon";
			menuSelected(document.getElementById("id_editor_menu_select"));
			createXmlDoc();
			var_dump(gLocal.gui.newproject);
			break;
		case "open":
			if (g_docid.length > 0) {
				editor_openProject(g_docid, "pcs");
			} else {
				alert(gLocal.gui.nofilename);
			}
			break;
		case "opendb":
			if (g_docid.length > 0) {
				editor_openProject(g_docid, "db");
			} else {
				alert("no doc id");
			}
			break;
		case "openchannal":
		case "openchannel":
			editor_openChannal(g_book, g_para, g_channal);
			render_channel_info(g_channal);
			break;
		case "import":
			if (g_filename.length > 0) {
				editor_importOldVer(g_filename);
				g_filename = g_filename.substring(0, g_filename.length - 4) + ".pcs";
			} else {
				alert(gLocal.gui.nofilename);
			}
			break;
		case "loadlist":
			editor_show_right_tool_bar(true);
			//get_pc_res_download_list_from_cookie();
			get_pc_res_download_list_from_string(gDownloadListString);
			createXmlDoc();
			pc_loadStream(0);
			break;
		default:
			break;
	}

	ntf_init();
}

var g_dict_search_one_pass_done = null;
var g_dict_search_one_dict_done = null;
var g_dict_search_all_done = null;
function editor_dict_all_done() {
	document.getElementById("editor_doc_notify").innerHTML = gLocal.gui.all_done;
	var t = setTimeout("document.getElementById('editor_doc_notify').innerHTML=''", 5000);
}
function editor_dict_one_dict_done(dictIndex) {
	document.getElementById("editor_doc_notify").innerHTML =
		gLocal.gui.round_1 +
		(g_dictFindParentLevel + 1) +
		gLocal.gui.round_2 +
		"【" +
		g_dictList[dictIndex].name +
		"】" +
		gLocal.gui.done;
	if (dictIndex + 1 < g_dictList.length - 1) {
		document.getElementById("editor_doc_notify").innerHTML +=
			"【" + g_dictList[dictIndex + 1].name + "】" + gLocal.gui.checking;
	}
}

function menu_dict_match() {
	g_dict_search_one_pass_done = null;
	g_dict_search_one_dict_done = null;
	g_dict_search_all_done = null;

	currMatchingDictNum = 0;
	g_dictFindParentLevel = 0;
	g_dictFindAllDone = false;
	g_dict_search_one_dict_done = editor_dict_one_dict_done;
	g_dict_search_all_done = editor_dict_all_done;
	dict_refresh_word_download_list();
	var arrBuffer = dict_get_search_list();
	g_CurrDictBuffer = JSON.stringify(arrBuffer);
	dict_mark_word_list_done();
	document.getElementById("id_dict_match_inner").innerHTML +=
		"finding parent level " + g_dictFindParentLevel + " buffer:" + arrBuffer.length + "<br>";
	editor_dict_match();
}

function editor_dict_match() {
	if (currMatchingDictNum < g_dictList.length) {
		if (g_dictList[currMatchingDictNum].used) {
			editor_loadDictFromDB(g_filename, g_dictList[currMatchingDictNum]);
		} else {
			currMatchingDictNum++;
			editor_dict_match();
		}
		if (g_dictFindAllDone) {
			dictMatchXMLDoc();
		}
	} else {
		if (g_dictFindParentLevel < 3) {
			if (g_dict_search_one_pass_done) {
				g_dict_search_one_pass_done(g_dictFindParentLevel);
			}
			currMatchingDictNum = 0;
			g_dictFindParentLevel++;

			var arrBuffer = dict_get_search_list();
			g_CurrDictBuffer = JSON.stringify(arrBuffer);
			dict_mark_word_list_done();
			document.getElementById("id_dict_match_inner").innerHTML +=
				"finding parent level " + g_dictFindParentLevel + " buffer:" + arrBuffer.length + "<br>";
			editor_dict_match();
		} else {
			document.getElementById("id_dict_match_inner").innerHTML +=
				"Max Parent Level " + g_dictFindParentLevel + " Stop!<br>";
			if (g_dict_search_all_done) {
				g_dict_search_all_done();
			}
			dict_mark_word_list_done();
			dictMatchXMLDoc();
		}
	}
}

function dict_push_word_to_download_list(word, level) {
	for (var i in g_InlineDictWordList) {
		if (g_InlineDictWordList[i].word == word) {
			return;
		}
	}
	var newWord = new Object();
	newWord.word = word;
	newWord.done = false;
	newWord.level = level;
	g_InlineDictWordList.push(newWord);
}

function dict_get_search_list() {
	var output = new Array();
	for (var i in g_InlineDictWordList) {
		if (g_InlineDictWordList[i].done == false) {
			output.push(g_InlineDictWordList[i]);
		}
	}

	return output;
}

function dict_mark_word_list_done() {
	for (var i in g_InlineDictWordList) {
		g_InlineDictWordList[i].done = true;
	}
}

function dict_refresh_word_download_list() {
	var xDict = gXmlBookDataBody.getElementsByTagName("word");
	for (var iword = 0; iword < xDict.length; iword++) {
		var pali = com_getPaliReal(getNodeText(xDict[iword], "real"));
		var part = getNodeText(xDict[iword], "org");
		var type = getNodeText(xDict[iword], "case").split("#");
		if (pali != "") {
			dict_push_word_to_download_list(pali, 0);
		}
		if (part != "") {
			var level = 1;
			if (type == ".un.") {
				level = 0;
			}
			var arrPart = part.split("+");
			for (var ipart in arrPart) {
				var onePart = com_getPaliReal(arrPart[ipart]);
				if (onePart != "") {
					dict_push_word_to_download_list(onePart, level);
				}
			}
		}
	}

	for (var i in g_DictWordList) {
		var pali = com_getPaliReal(g_DictWordList[i].Pali);
		var wparent = com_getPaliReal(g_DictWordList[i].Parent);
		var part = g_DictWordList[i].Factors;
		var type = g_DictWordList[i].Type;
		var level = 1;
		if (type == ".un.") {
			level = 0;
		}
		if (wparent != "") {
			dict_push_word_to_download_list(wparent, level);
		}
		if (part != "") {
			var arrPart = part.split("+");
			for (var ipart in arrPart) {
				var onePart = com_getPaliReal(arrPart[ipart]);
				if (onePart != "") {
					dict_push_word_to_download_list(onePart, level);
				}
			}
		}
	}
}

function dict_inid_ild_word_list() {
	g_InlineDictWordList = new Array();
	for (var i in g_DictWordList) {
		var pali = g_DictWordList[i].Pali;
		var wparent = g_DictWordList[i].Parent;
		var part = g_DictWordList[i].Factors;
		var type = g_DictWordList[i].Type;
		var level = 1;
		dict_push_word_to_download_list(g_DictWordList[i].Pali, 0);
	}
	dict_mark_word_list_done();
}

function getAllWordList() {
	var output = new Array();
	if (g_dictFindParentLevel == 0) {
		var xDict = gXmlBookDataBody.getElementsByTagName("word");
		for (iword = 0; iword < xDict.length; iword++) {
			pali = getNodeText(xDict[iword], "real");
			if (isPaliWord(pali)) {
				output.push(pali);
			}
		}
	} else {
		var currLevel = g_dictFindParentLevel - 1;
		for (i = 0; i < g_DictWordList.length; i++) {
			if (g_DictWordList[i].ParentLevel == currLevel) {
				if (g_DictWordList[i].Parent.length > 0 && g_DictWordList[i].Parent != g_DictWordList[i].Pali) {
					var arrList = g_DictWordList[i].Parent.split("$");
					var paliInParent = false;
					for (x = 0; x < arrList.length; x++) {
						if (arrList[x] == g_DictWordList[i].Pali) {
							paliInParent = true;
						}
					}
					if (paliInParent == false) {
						output.push(g_DictWordList[i].Parent);
					}
				}
				if (g_DictWordList[i].Factors.length > 0) {
					arrList = g_DictWordList[i].Factors.split("+");
					for (x = 0; x < arrList.length; x++) {
						if (arrList[x] != g_DictWordList[i].Pali) {
							output.push(arrList[x]);
						}
					}
				}
			}
		}
	}
	if (output.length > 0) {
		return output.join("$");
	} else {
		return null;
	}
}

var editor_DictXmlHttp = null;
function editor_loadDictFromDB(strFileName, dictName) {
	var xmlText = "";

	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		editor_DictXmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		editor_DictXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (editor_DictXmlHttp != null) {
		var d = new Date();
		var strLink =
			"./dict_find.php?mode=xml&filename=" + strFileName + "&type=" + dictName.type + "&dict=" + dictName.filename;
		editor_DictXmlHttp.onreadystatechange = editor_dict_serverResponse;
		//var wordList=getAllWordList();

		var wordList = g_CurrDictBuffer;

		if (wordList != null) {
			document.getElementById("id_dict_msg").innerHTML = "开始匹配字典" + dictName.name;
			editor_DictXmlHttp.open("POST", "./dict_find2.php", true);
			//editor_DictXmlHttp.send(dictName.type+"$"+dictName.filename+"$"+g_dictFindParentLevel+"$"+wordList);
			editor_DictXmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			editor_DictXmlHttp.send(
				"type=" +
					dictName.type +
					"&filename=" +
					dictName.filename +
					"&level=" +
					g_dictFindParentLevel +
					"&data=" +
					wordList
			);
		} else {
			g_dictFindAllDone = true;
			document.getElementById("id_dict_match_inner").innerHTML += "all done!";
			if (g_dict_search_all_done) {
				g_dict_search_all_done();
			}
		}
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function editor_dict_serverResponse() {
	if (editor_DictXmlHttp.readyState == 4) {
		// 4 = "loaded"
		document.getElementById("id_dict_msg").innerHTML = "已经获取字典数据";
		if (editor_DictXmlHttp.status == 200) {
			// 200 = "OK"
			var xmlText = editor_DictXmlHttp.responseText;

			if (window.DOMParser) {
				parser = new DOMParser();
				xmlDict = parser.parseFromString(xmlText, "text/xml");
			} // Internet Explorer
			else {
				xmlDict = new ActiveXObject("Microsoft.XMLDOM");
				xmlDict.async = "false";
				xmlDict.loadXML(xmlText);
			}

			if (xmlDict == null) {
				alert("error:can not load dict.");
				return;
			}

			document.getElementById("dict_result_" + currMatchingDictNum).innerHTML =
				" : " + g_dictFindParentLevel + "-" + xmlDict.getElementsByTagName("word").length;
			dictDataParse(xmlDict, currMatchingDictNum);
			editor_addDictDataToXmlDoc(xmlDict);
		} else {
			document.getElementById("id_dict_match_inner").innerHTML =
				"Problem retrieving data:" + editor_DictXmlHttp.statusText;
		}
		if (g_dict_search_one_dict_done) {
			g_dict_search_one_dict_done(currMatchingDictNum);
		}
		currMatchingDictNum++;
		editor_dict_match();
	}
}
//添加字典数据到内联字典
function editor_addDictDataToXmlDoc(xmlDictData) {
	var xDict = xmlDictData.getElementsByTagName("word");
	for (iword = 0; iword < xDict.length; iword++) {
		gXmlBookDataInlineDict.appendChild(xDict[iword].cloneNode(true));
	}
}
/*解析字典数据*/
function dictDataParse(xmlDictData, dictID) {
	document.getElementById("id_dict_msg").innerHTML = "正在解析字典数据";
	var xDict = xmlDictData.getElementsByTagName("word");
	var tOut = "";
	var sDictPali = "";
	var sDictId = "";
	var sDictOrg = "";
	var sDictMean = "";
	var sDictCase = "";
	for (iword = 0; iword < xDict.length; iword++) {
		var objDictItem = new Object(); /*一个字典元素*/
		objDictItem.Id = getNodeText(xDict[iword], "id");
		objDictItem.Guid = getNodeText(xDict[iword], "guid");
		objDictItem.Pali = getNodeText(xDict[iword], "pali");
		objDictItem.Mean = getNodeText(xDict[iword], "mean");
		objDictItem.Type = getNodeText(xDict[iword], "type");
		objDictItem.Gramma = getNodeText(xDict[iword], "gramma");
		objDictItem.Parent = getNodeText(xDict[iword], "parent");
		objDictItem.Factors = getNodeText(xDict[iword], "factors");
		objDictItem.PartId = getNodeText(xDict[iword], "part_id");
		objDictItem.FactorMean = getNodeText(xDict[iword], "factormean");
		objDictItem.Note = getNodeText(xDict[iword], "note");
		objDictItem.Confer = getNodeText(xDict[iword], "confer");
		objDictItem.Status = getNodeText(xDict[iword], "status");
		objDictItem.Enable = getNodeText(xDict[iword], "enable");
		objDictItem.Language = getNodeText(xDict[iword], "language");
		objDictItem.dictname = getNodeText(xDict[iword], "dict_name");
		//objDictItem.dictname=g_dictList[dictID].name;
		objDictItem.dictType = g_dictList[dictID].type;
		objDictItem.fileName = g_dictList[dictID].filename;
		objDictItem.dictID = dictID;
		objDictItem.ParentLevel = g_dictFindParentLevel;

		//插入数据到内联字典索引表
		var level = 1;
		if (objDictItem.Type == ".un.") {
			level = 0;
		}
		if (objDictItem.Parent != "") {
			dict_push_word_to_download_list(objDictItem.Parent, level);
		}
		if (objDictItem.Factors != "") {
			var arrPart = objDictItem.Factors.split("+");
			for (var ipart in arrPart) {
				dict_push_word_to_download_list(arrPart[ipart], level);
			}
		}

		if (objDictItem.Case != "?" || objDictItem.Org != "?" || objDictItem.Mean != "?") {
			pushNewDictItem(g_DictWordList, objDictItem);
		}
	}
	//dict end
}

function pushNewDictItem(inArray, objNew) {
	for (const iterator of inArray) {
		if (iterator.Id == objNew.Id && iterator.dictID == objNew.dictID) {
			return;
		}
	}
	inArray.push(objNew);
}

function dictShowAsTable() {
	var outData = "<table>";
	for (var i = 0; i < g_DictWordList.length; i++) {
		outData += "<tr class='dict_row" + g_DictWordList[i].ParentLevel + "'>";
		outData = outData + "<td>" + g_DictWordList[i].dictname + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Pali + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Type + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Gramma + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Parent + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Mean + "</td>";
		outData = outData + "<td>" + g_DictWordList[i].Factors + "</td>";
		outData = outData + "</tr>";
	}
	outData += "</table>";
	return outData;
}

var g_CurrActiveRecorder = "new";
function setCurrActiveRecorder(recorderName) {
	g_CurrActiveRecorder = recorderName;
}

function updataCurrActiveRecorder(filder, value) {
	if (filder == "all") {
	} else {
		document.getElementById(filder + "_" + g_CurrActiveRecorder).value = value;
		mean_change(g_CurrActiveRecorder);
	}
}
function addToCurrActiveRecorder(filder, value) {
	if (filder == "all") {
	} else {
		meanString = document.getElementById(filder + "_" + g_CurrActiveRecorder).value;
		meanList = meanString.split("$");
		for (i in meanList) {
			if (meanList[i] == value) {
				return;
			}
		}
		document.getElementById(filder + "_" + g_CurrActiveRecorder).value += "$" + value;
		mean_change(g_CurrActiveRecorder);
	}
}

function updataFactorMeanPrev(id, strNew) {
	//if(strNew!=null){
	//document.getElementById("id_factormean_prev_"+id).value=strNew;
	//}
}

function factorMeanItemChange(id, iPos, count, obj) {
	//alert(id+":"+iPos+":"+newMean);
	newMean = obj.value;
	var factorMeanPrevString = document.getElementById("id_factormean_prev_" + id).value;
	currFactorMeanPrevList = factorMeanPrevString.split("+");
	currFactorMeanPrevList[iPos] = newMean;
	document.getElementById("id_factormean_prev_" + id).value = currFactorMeanPrevList.join("+");
}

function makeFactorBlock(factorStr, id) {
	var output = "";
	var factorList = factorStr.split("+");
	var defualtFactorMeanList = new Array();

	for (iFactor in factorList) {
		arrFM = findAllMeanInDict(factorList[iFactor], 10);
		if (arrFM.length == 0) {
			arrFM[0] = "unkow";
		}
		output +=
			"<select onclick=\"factorMeanItemChange('" +
			id +
			"','" +
			iFactor +
			"','" +
			factorList.length +
			"',this)\">";
		defualtFactorMeanList.push(arrFM[0]);
		for (iFM in arrFM) {
			output += "<option value='" + arrFM[iFM] + "' >" + arrFM[iFM] + "</option>";
		}
		output += "</select>";
		if (iFactor < factorList.length - 1) {
			output += "+";
		}
	}
	//updataFactorMeanPrev(id,defualtFactorMeanList.join("+"));
	g_FactorMean = defualtFactorMeanList.join("+");
	return output;
}
function factor_change(id) {
	var factorString = document.getElementById("id_dict_user_factors_" + id).value;
	document.getElementById("id_factor_block_" + id).innerHTML = makeFactorBlock(factorString, id);
}

function makeMeanBlock(meanStr, id) {
	var output = "";
	var meanList = meanStr.split("$");
	for (i in meanList) {
		output += '<div class="mean_cell">';
		output += '<div class="button_shell">';
		output += '<p class="mean_button" onclick="meanBlockMove(\'' + id + "'," + i + "," + (i - 1) + ')">«</p>';
		output += "</div>";
		output +=
			'<p class="mean_inner" onclick="meanBlockMove(\'' + id + "'," + i + "," + 0 + ')">' + meanList[i] + "</p>";
		output += '<div class="button_shell">';
		output += '<p class="mean_button" onclick="meanBlockDelete(\'' + id + "'," + i + ')">x</p>';
		output += "</div>";
		output += "</div>";
	}
	return output;
}

function mean_change(id) {
	var meanString = document.getElementById("id_dict_user_mean_" + id).value;
	document.getElementById("id_mean_block_" + id).innerHTML = makeMeanBlock(meanString, id);
}

function meanBlockDelete(id, indexDelete) {
	var meanString = document.getElementById("id_dict_user_mean_" + id).value;
	var meanBlock = "";
	var meanList = meanString.split("$");
	meanList.splice(indexDelete, 1);
	var newString = meanList.join("$");
	document.getElementById("id_dict_user_mean_" + id).value = newString;
	mean_change(id);
}

function meanBlockMove(id, moveFrom, moveTo) {
	var meanString = document.getElementById("id_dict_user_mean_" + id).value;
	var meanBlock = "";
	var meanList = meanString.split("$");
	if (moveTo < 0) {
		moveTo = 0;
	}
	if (moveFrom == moveTo) {
		return;
	}
	var temp = meanList[moveTo];
	meanList[moveTo] = meanList[moveFrom];
	for (i = moveFrom - 1; i > moveTo; i--) {
		meanList[i + 1] = meanList[i];
	}
	meanList[moveTo + 1] = temp;
	var newString = meanList.join("$");
	/*
	for(x in meanList){
		newString+=meanList[x]+"$";
	}
	*/
	document.getElementById("id_dict_user_mean_" + id).value = newString;
	mean_change(id);
}

function addAutoMeanToFactorMean(id) {
	document.getElementById("id_dict_user_fm_" + id).value = document.getElementById("id_factormean_prev_" + id).value;
}

//show current selected word in the word window to modify
var g_WordTableCurrWord = "";
function dictCurrWordShowAsTable(inCurrWord) {
	g_WordTableCurrWord = inCurrWord;
	g_CurrActiveRecorder = "new";
	var outData = "";
	var listParent = new Array();
	var listFactors = new Array();
	var listChildren = new Array();
	outData += "<p class='word_parent'>" + gLocal.gui.parent + ":";
	for (var i = 0; i < g_DictWordList.length; i++) {
		if (g_DictWordList[i].Pali == inCurrWord) {
			if (g_DictWordList[i].Parent.length > 0) {
				var find = false;
				for (x in listParent) {
					if (listParent[x] == g_DictWordList[i].Parent) {
						find = true;
						break;
					}
				}
				if (!find) {
					listParent.push(g_DictWordList[i].Parent);
				}
			}
			if (g_DictWordList[i].Factors.length > 0) {
				arrFactors = g_DictWordList[i].Factors.split("+");
				for (iFactors in arrFactors) {
					var find = false;
					for (x in listFactors) {
						if (listFactors[x] == arrFactors[iFactors]) {
							find = true;
							break;
						}
					}
					if (!find) {
						listFactors.push(arrFactors[iFactors]);
					}
				}
			}
		}
	}
	for (x in listParent) {
		outData += "<a onclick=\"showCurrWordTable('" + listParent[x] + "')\">" + listParent[x] + "</a> ";
	}
	for (x in listFactors) {
		outData += "[<a onclick=\"showCurrWordTable('" + listFactors[x] + "')\">" + listFactors[x] + "</a>] ";
	}
	outData += "</p>";

	outData = outData + '<p class="word_current">└' + inCurrWord + "</p>";

	outData += "<p class='word_child'>└" + gLocal.gui.children + ": ";
	for (var i = 0; i < g_DictWordList.length; i++) {
		if (g_DictWordList[i].Parent == inCurrWord) {
			if (g_DictWordList[i].Pali.length > 0) {
				var find = false;
				for (x in listChildren) {
					if (listChildren[x] == g_DictWordList[i].Pali) {
						find = true;
						break;
					}
				}
				if (!find) {
					listChildren.push(g_DictWordList[i].Pali);
				}
			}
		}
	}
	for (x in listChildren) {
		outData += "<a onclick=\"showCurrWordTable('" + listChildren[x] + "')\">" + listChildren[x] + "</a> ";
	}
	outData += "</p>";

	//get new recorder filder
	var newRecorder = new Object();
	newRecorder.Type = "";
	newRecorder.Gramma = "";
	newRecorder.Parent = "";
	newRecorder.Mean = "";
	newRecorder.Note = "";
	newRecorder.Factors = "";
	newRecorder.FactorMean = "";
	newRecorder.Confer = "";
	newRecorder.Status = "";
	newRecorder.Lock = "";
	newRecorder.Tag = "";
	var newMeanList = new Array();
	for (var i = 0; i < g_DictWordList.length; i++) {
		if (g_DictWordList[i].Pali == inCurrWord) {
			if (newRecorder.Type == "" && g_DictWordList[i].Type.length > 0) {
				newRecorder.Type = g_DictWordList[i].Type;
			}
			if (newRecorder.Gramma == "" && g_DictWordList[i].Gramma.length > 0) {
				newRecorder.Gramma = g_DictWordList[i].Gramma;
			}
			if (newRecorder.Parent == "" && g_DictWordList[i].Parent.length > 0) {
				newRecorder.Parent = g_DictWordList[i].Parent;
			}
			if (g_DictWordList[i].Mean.length > 0) {
				otherMean = g_DictWordList[i].Mean.split("$");
				for (iMean in otherMean) {
					pushNewToList(newMeanList, otherMean[iMean]);
				}
				newRecorder.Mean = newMeanList.join("$");
			}
			if (newRecorder.Factors == "" && g_DictWordList[i].Factors.length > 0) {
				newRecorder.Factors = g_DictWordList[i].Factors;
			}
			if (newRecorder.FactorMean == "" && g_DictWordList[i].FactorMean.length > 0) {
				newRecorder.FactorMean = g_DictWordList[i].FactorMean;
			}
			if (newRecorder.Note == "" && g_DictWordList[i].Note) {
				if (g_DictWordList[i].Note.length > 0) {
					newRecorder.Note = g_DictWordList[i].Note;
				}
			}
		}
	}
	newMeanBlock = makeMeanBlock(newRecorder.Mean, "new");
	newFactorBlock = makeFactorBlock(newRecorder.Factors, "new");
	newFactorMeanPrevString = g_FactorMean;

	outData += '<div class="word_edit">';
	outData += '	<div class="word_edit_head">';
	outData += '<input type="input" id="id_dict_user_id_new" hidden value="0" >';
	outData += '<input type="input" id="id_dict_user_pali_new" hidden value="' + inCurrWord + '" >';
	outData +=
		'		<button type="button" onclick="editor_UserDictUpdata(\'new\',this)">' + gLocal.gui.newword + "</button>";
	outData += "		" + gLocal.gui.wordtype + ":";
	outData += '	<select name="type" id="id_dict_user_type_new" onchange="typeChange(this)">';
	for (x in gLocal.type_str) {
		if (gLocal.type_str[x].id == newRecorder.Type) {
			outData =
				outData +
				'<option value="' +
				gLocal.type_str[x].id +
				'" selected>' +
				gLocal.type_str[x].value +
				"</option>";
		} else {
			outData =
				outData + '<option value="' + gLocal.type_str[x].id + '">' + gLocal.type_str[x].value + "</option>";
		}
	}
	outData = outData + "	</select>";
	outData +=
		"		" +
		gLocal.gui.gramma +
		':<input type="input" id="id_dict_user_gramma_new" size="12" value="' +
		newRecorder.Gramma +
		'" />';
	outData +=
		"		" +
		gLocal.gui.parent +
		':<input type="input" id="id_dict_user_parent_new" size="12" value="' +
		newRecorder.Parent +
		'" />';
	outData +=
		"		" +
		gLocal.gui.part +
		':<input type="input" id="id_dict_user_factors_new" size="' +
		inCurrWord.length * 1.2 +
		'" value="' +
		newRecorder.Factors +
		'" onkeyup="factor_change(\'new\')" />';
	outData += "		" + gLocal.gui.partmeaning + ":" + newFactorBlock;
	outData += '<button type="button" onclick="addAutoMeanToFactorMean(\'new\')" >▶</button>';
	outData +=
		'		<input type="input" id="id_dict_user_fm_new" size="' +
		inCurrWord.length +
		'" value="' +
		newRecorder.FactorMean +
		'" />';
	outData += "	</div>";
	outData += '	<div class="word_edit_mean">';
	outData +=
		"	" +
		gLocal.gui.meaning +
		':<input type="input" size=\'50\' id="id_dict_user_mean_new" value="' +
		newRecorder.Mean +
		"\" onkeyup=\"mean_change('new')\"/><div class='mean_block' id='id_mean_block_new'>" +
		newMeanBlock +
		"</div>";
	outData += "	</div>";
	outData += "<input type='text' id='id_factormean_prev_new' value='" + newFactorMeanPrevString + "' hidden />";
	outData += '	<div class="word_edit_note">';
	outData +=
		gLocal.gui.note +
		":<br /><textarea id=\"id_dict_user_note_new\" rows='3' cols='100'>" +
		newRecorder.Note +
		"</textarea>";
	outData += "	</div>";
	outData += "</div>";

	//draw new

	/*
		outData=outData+"<h3>New:</h3>";
		outData+="<table>";
		
		outData+="<tr class='dict_row_new'><td></td><td>Type</td><td>Parent</td><td>Meaning</td></tr>";
		outData+="<tr class='dict_row_new'><td><input type='radio' name='dictupdata' checked onclick=\"setCurrActiveRecorder('new')\" /></td>"+
		"<td><input type=\"input\" id=\"id_dict_user_id_new\" hidden value=\"0\" >"+
		"<input type=\"input\" id=\"id_dict_user_pali_new\" hidden value=\""+inCurrWord+"\" >"+
		"	<select name=\"type\" id=\"id_dict_user_type_new\" onchange=\"typeChange(this)\">";
			for (x in gLocal.type_str){
			if(gLocal.type_str[x].id==newRecorder.Type){
				outData=outData+"<option value=\""+gLocal.type_str[x].id+"\" selected>"+gLocal.type_str[x].value+"</option>";
			}
			else{
				outData=outData+"<option value=\""+gLocal.type_str[x].id+"\">"+gLocal.type_str[x].value+"</option>";
			}
		}
		outData=outData+"	</select>";
		outData=outData+"</td>";
		outData=outData+"<td><input type=\"input\" id=\"id_dict_user_parent_new\" size=\"12\" value=\""+newRecorder.Parent+"\" /></td>";
		outData=outData+"<td><input type=\"input\" size='50' id=\"id_dict_user_mean_new\" value=\""+newRecorder.Mean+"\" onkeyup=\"mean_change('new')\"/><div class='mean_block' id='id_mean_block_new'>"+newMeanBlock+"</div></td>";
		outData+="</tr>";
		outData+="<tr class='dict_row_new'><td></td><td>Gramma</td><td>Parts</td><td>Parts Meaning</td></tr>";
		outData+="<tr class='dict_row_new'><td><button type=\"button\" onclick=\"editor_UserDictUpdata('new',this)\">Submit</button></td>";
		outData+="<td><input type=\"input\" id=\"id_dict_user_gramma_new\" size=\"12\" value=\""+newRecorder.Gramma+"\" /></td>";
		outData+="<td><input type=\"input\" id=\"id_dict_user_factors_new\" size=\""+inCurrWord.length*1.2+"\" value=\""+newRecorder.Factors+"\" onkeyup=\"factor_change('new')\" />";
		outData+="<br /><input type='text' id='id_factormean_prev_new' value='"+newFactorMeanPrevString+"' hidden />";
		outData+="<div class='factor' id='id_factor_block_new'>"+newFactorBlock+"</div>";
		outData+="<button type=\"button\" onclick=\"addAutoMeanToFactorMean('new')\" >▶</button></td>";
		outData+="<td><input type=\"input\" id=\"id_dict_user_fm_new\" size=\""+inCurrWord.length*1.5+"\" value=\""+newRecorder.FactorMean+"\" /></td></tr>";
		outData+="<tr class='dict_row_new'><td>Note</td>";
		outData+="<td colspan=3><textarea id=\"id_dict_user_note_new\" rows='3' cols='100'>"+newRecorder.Note+"</textarea></td></tr>"
		
		outData+="</table>";
		
	*/
	// end of New

	outData += "<h3>" + gLocal.gui.userdict + "</h3>";
	outData += "<table>";

	outData =
		outData +
		"<tr><th></th><th>" +
		gLocal.gui.dictsouce +
		"</th> <th>" +
		gLocal.gui.wordtype +
		"</th> <th>" +
		gLocal.gui.gramma +
		"</th> <th>" +
		gLocal.gui.parent +
		"</th> <th>" +
		gLocal.gui.meaning +
		"</th> <th>" +
		gLocal.gui.part +
		"</th> <th>" +
		gLocal.gui.partmeaning +
		"</th> <th></th> </tr>";
	for (var i = 0; i < g_DictWordList.length; i++) {
		if (g_DictWordList[i].Pali == inCurrWord) {
			if (g_DictWordList[i].dictname == "用户字典") {
				outData += "<tr class='dict_row" + g_DictWordList[i].ParentLevel + "'>";
				outData +=
					"<td><input type=radio name='dictupdata' onclick=\"setCurrActiveRecorder('" + i + "')\" /></td>";
				outData = outData + "<td>" + g_DictWordList[i].dictname + "</td>";
				outData =
					outData +
					'<td><input type="input" id="id_dict_user_id_' +
					i +
					'" hidden value="' +
					g_DictWordList[i].Id +
					'" >';
				outData =
					outData +
					'<input type="input" id="id_dict_user_pali_' +
					i +
					'" hidden value="' +
					g_DictWordList[i].Pali +
					'" >';
				outData = outData + '	<select name="type" id="id_dict_user_type_' + i + '" onchange="typeChange(this)">';
				for (x in gLocal.type_str) {
					if (gLocal.type_str[x].id == g_DictWordList[i].Type) {
						outData =
							outData +
							'<option value="' +
							gLocal.type_str[x].id +
							'" selected>' +
							gLocal.type_str[x].value +
							"</option>";
					} else {
						outData =
							outData +
							'<option value="' +
							gLocal.type_str[x].id +
							'">' +
							gLocal.type_str[x].value +
							"</option>";
					}
				}
				outData = outData + "	</select>";
				outData = outData + "</td>";
				outData =
					outData +
					'<td><input type="input" id="id_dict_user_gramma_' +
					i +
					'" size="12" value="' +
					g_DictWordList[i].Gramma +
					'" /></td>';
				outData =
					outData +
					'<td><input type="input" id="id_dict_user_parent_' +
					i +
					'" size="12" value="' +
					g_DictWordList[i].Parent +
					'" />';
				outData =
					outData +
					"<button type='button' onclick=\"showCurrWordTable('" +
					g_DictWordList[i].Parent +
					"')\">»</button></td>";
				outData =
					outData +
					'<td><input type="input" size=\'50\' id="id_dict_user_mean_' +
					i +
					'" value="' +
					g_DictWordList[i].Mean +
					"\" onkeyup='mean_change(" +
					i +
					")' /><div class='mean_block' id='id_mean_block_" +
					i +
					"'></div></td>";
				outData =
					outData +
					'<td><input type="input" id="id_dict_user_factors_' +
					i +
					'" size="15" value="' +
					g_DictWordList[i].Factors +
					'" /></td>';
				outData =
					outData +
					'<td><input type="input" id="id_dict_user_fm_' +
					i +
					'" size="15" value="' +
					g_DictWordList[i].FactorMean +
					'" /></td>';
				outData =
					outData +
					'<td><button type="button" onclick="editor_UserDictUpdata(\'' +
					i +
					"',this)\">Updata</button></td>";
				outData = outData + "</tr>";
				outData += "<tr ><td>Note</td>";
				outData +=
					'<td colspan=3><textarea id="id_dict_user_note_' +
					i +
					"\" rows='3' cols='100'>" +
					g_DictWordList[i].Note +
					"</textarea></td></tr>";
			}
		}
	}

	/*
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(g_DictWordList[i].dictname=="用户字典"){
				outData+="<tr ><td></td><td>Type</td><td>Parent</td><td>Meaning</td></tr>";
				outData+="<tr ><td><input type='radio' name='dictupdata' checked onclick=\"setCurrActiveRecorder('new')\" /></td>"+
				"<td><input type=\"input\" id=\"id_dict_user_id_new"+i+"\" hidden value=\"0\" >"+
				"<input type=\"input\" id=\"id_dict_user_pali_"+i+"\" hidden value=\""+inCurrWord+"\" >"+
				"	<select name=\"type\" id=\"id_dict_user_type_"+i+"\" onchange=\"typeChange(this)\">";
					for (x in gLocal.type_str){
					if(gLocal.type_str[x].id==g_DictWordList[i].Type){
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\" selected>"+gLocal.type_str[x].value+"</option>";
					}
					else{
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\">"+gLocal.type_str[x].value+"</option>";
					}
				}
				outData=outData+"	</select>";
				outData=outData+"</td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_parent_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Parent+"\" /></td>";
				outData=outData+"<td><input type=\"input\" size='50' id=\"id_dict_user_mean_"+i+"\" value=\""+g_DictWordList[i].Mean+"\" onkeyup=\"mean_change('new')\"/><div class='mean_block' id='id_mean_block_new'>"+newMeanBlock+"</div></td>";
				outData+="</tr>";
				outData+="<tr ><td></td><td>Gramma</td><td>Parts</td><td>Parts Meaning</td></tr>";
				outData+="<tr ><td><button type=\"button\" onclick=\"editor_UserDictUpdata('"+i+"',this)\">Submit</button></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_gramma_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Gramma+"\" /></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_factors_"+i+"\" size=\""+inCurrWord.length*1.2+"\" value=\""+g_DictWordList[i].Factors+"\" onkeyup=\"factor_change('new')\" />";
				outData+="<br /><input type='text' id='id_factormean_prev_"+i+"' value='"+newFactorMeanPrevString+"' hidden />";
				outData+="<div class='factor' id='id_factor_block_"+i+"'>"+newFactorBlock+"</div>";
				outData+="<button type=\"button\" onclick=\"addAutoMeanToFactorMean('"+i+"')\" >▲</button></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_fm_"+i+"\" size=\""+inCurrWord.length*1.5+"\" value=\""+g_DictWordList[i].FactorMean+"\" /></td></tr>";
				outData+="<tr ><td>Note</td>";
				outData+="<td colspan=3><textarea id=\"id_dict_user_note_"+i+"\" rows='3' cols='100'>"+g_DictWordList[i].Note+"</textarea></td></tr>"
			
			}
		}
	}
	*/
	outData = outData + "</table>";

	outData += "<h3>" + gLocal.gui.otherdict + "</h3>";
	outData += "<table>";
	outData =
		outData +
		"<tr><th></th><th>" +
		gLocal.gui.dictsouce +
		"</th> <th>" +
		gLocal.gui.wordtype +
		"</th> <th>" +
		gLocal.gui.gramma +
		"</th> <th>" +
		gLocal.gui.parent +
		"</th> <th>" +
		gLocal.gui.meaning +
		"</th> <th>" +
		gLocal.gui.part +
		"</th> <th>" +
		gLocal.gui.partmeaning +
		"</th> <th></th> </tr>";

	for (var i = 0; i < g_DictWordList.length; i++) {
		if (g_DictWordList[i].Pali == inCurrWord) {
			if (g_DictWordList[i].dictname == "用户字典") {
			} else {
				outData += "<tr class='dict_row" + g_DictWordList[i].ParentLevel + "'>";
				outData += '<td><button type="button" >▲</button></td>';
				outData = outData + "<td>" + g_DictWordList[i].dictname + "</td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_type','" +
					g_DictWordList[i].Type +
					'\')" >▲</button><span id="id_dict_user_gramma_' +
					i +
					'">' +
					g_DictWordList[i].Type +
					"</span></td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_gramma','" +
					g_DictWordList[i].Gramma +
					"')\">▲</button>" +
					g_DictWordList[i].Gramma +
					"</td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_parent','" +
					g_DictWordList[i].Parent +
					"')\">▲</button>" +
					g_DictWordList[i].Parent;
				outData =
					outData +
					"<button type='button' onclick=\"showCurrWordTable('" +
					g_DictWordList[i].Parent +
					"')\">»</button></td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_mean','" +
					g_DictWordList[i].Mean +
					"')\">▲</button>" +
					g_DictWordList[i].Mean +
					"<br />" +
					makeMeanLink(g_DictWordList[i].Mean) +
					"</td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_factors','" +
					g_DictWordList[i].Factors +
					"')\">▲</button>" +
					g_DictWordList[i].Factors +
					"</td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_fm','" +
					g_DictWordList[i].FactorMean +
					"')\">▲</button>" +
					g_DictWordList[i].FactorMean +
					"</td>";
				outData =
					outData +
					"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('all','" +
					g_DictWordList[i].Type +
					"')\">▲</button></td>";
				outData = outData + "</tr>";
			}
		}
	}
	outData += "</table>";

	//children
	for (x in listChildren) {
		wordChildren = listChildren[x];
		outData += "<h4>" + wordChildren + "</h4> ";
		outData += "<table>";
		outData =
			outData +
			"<tr><th></th><th>" +
			gLocal.gui.dictsouce +
			"</th> <th>" +
			gLocal.gui.wordtype +
			"</th> <th>" +
			gLocal.gui.gramma +
			"</th> <th>" +
			gLocal.gui.parent +
			"</th> <th>" +
			gLocal.gui.meaning +
			"</th> <th>" +
			gLocal.gui.part +
			"</th> <th>" +
			gLocal.gui.partmeaning +
			"</th> <th></th> </tr>";
		for (var i = 0; i < g_DictWordList.length; i++) {
			if (g_DictWordList[i].Pali == wordChildren) {
				{
					outData += "<tr class='dict_row" + g_DictWordList[i].ParentLevel + "'>";
					outData += '<td><button type="button" >▲</button></td>';
					outData = outData + "<td>" + g_DictWordList[i].dictname + "</td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_type','" +
						g_DictWordList[i].Type +
						'\')" >▲</button><span id="id_dict_user_gramma_' +
						i +
						'">' +
						g_DictWordList[i].Type +
						"</span></td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_gramma','" +
						g_DictWordList[i].Gramma +
						"')\">▲</button>" +
						g_DictWordList[i].Gramma +
						"</td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_parent','" +
						g_DictWordList[i].Parent +
						"')\">▲</button>" +
						g_DictWordList[i].Parent;
					outData =
						outData +
						"<button type='button' onclick=\"showCurrWordTable('" +
						g_DictWordList[i].Parent +
						"')\">»</button></td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_mean','" +
						g_DictWordList[i].Mean +
						"')\">▲</button>" +
						g_DictWordList[i].Mean +
						"<br />" +
						makeMeanLink(g_DictWordList[i].Mean) +
						"</td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_factors','" +
						g_DictWordList[i].Factors +
						"')\">▲</button>" +
						g_DictWordList[i].Factors +
						"</td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_fm','" +
						g_DictWordList[i].FactorMean +
						"')\">▲</button>" +
						g_DictWordList[i].FactorMean +
						"</td>";
					outData =
						outData +
						"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('all','" +
						g_DictWordList[i].Type +
						"')\">▲</button></td>";
					outData = outData + "</tr>";
				}
			}
		}
		outData += "</table>";
	}
	return outData;
}

function makeMeanLink(inStr) {
	var arrList = inStr.split("$");
	var output = "";
	for (i in arrList) {
		output +=
			"<a onclick=\"addToCurrActiveRecorder('id_dict_user_mean','" + arrList[i] + "')\">" + arrList[i] + "</a> ";
	}
	return output;
}

function showCurrWordTable(currWord) {
	document.getElementById("id_dict_curr_word_inner").innerHTML = dictCurrWordShowAsTable(currWord);
}

//匹配字典数据到文档
function dictMatchXMLDoc() {
	document.getElementById("id_dict_msg").innerHTML = gLocal.gui.dict_match;
	var docWordCounter = 0;
	var matchedCounter = 0;

	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	for (var iword = 0; iword < xDocWords.length; iword++) {
		var sPaliWord = getNodeText(xDocWords[iword], "real");
		var sFactorsWord = getNodeText(xDocWords[iword], "org");
		var sMeanWord = getNodeText(xDocWords[iword], "mean");
		var sTypeWord = getNodeText(xDocWords[iword], "case");

		if (isPaliWord(sPaliWord)) {
			docWordCounter++;

			/*将这个词与字典匹配，*/
			var iDict = 0;
			//if(sMeanWord=="?"){
			var thisWord = sPaliWord;
			for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
				if (thisWord == g_DictWordList[iDict].Pali && g_DictWordList[iDict].ParentLevel == 0) {
					if (sMeanWord == "?") {
						setNodeText(xDocWords[iword], "bmc", "bmca");
					}
					modifyWordDetailByWordIndex(iword);
					matchedCounter++;
					break;
				}
			}
			//}
			/*
			else{
				if(isPaliWord(sPaliWord)){
					matchedCounter++;
				}
			}*/
		}
	}

	var progress = (matchedCounter * 100) / docWordCounter;

	document.getElementById("id_dict_msg").innerHTML = gLocal.gui.match_end + Math.round(progress) + "%";
}

function dictGetFirstMean(strMean) {
	var arrMean = strMean.split("$");
	if (arrMean.length > 0) {
		for (var i = 0; i < arrMean.length; i++) {
			if (arrMean[i].length > 0) {
				return arrMean[i];
			} else {
				return "";
			}
		}
		return "";
	} else {
		return "";
	}
}
//test word is pali word or not
function isPaliWord(inWord) {
	if (inWord.length < 2) {
		return false;
	}
	if (inWord.match(/[x]/)) {
		return false;
	}
	if (inWord.match(/[q]/)) {
		return false;
	}
	if (inWord.match(/[w]/)) {
		return false;
	}
	if (inWord.match(/[a-y]/)) {
		return true;
	} else {
		return false;
	}
}

function submenu_show_detail(obj) {
	eParent = obj.parentNode;
	//var y = obj.getElementsByTagName("svg");
	var x = eParent.getElementsByTagName("div");
	var o = obj.getElementsByTagName("svg");
	if (x[0].style.maxHeight == "200em") {
		x[0].style.maxHeight = "0px";
		x[0].style.padding = "0px";
		x[0].style.opacity = "0";
		o[0].style.transform = "rotate(0deg)";
	} else {
		x[0].style.maxHeight = "200em";
		x[0].style.padding = "10px";
		x[0].style.opacity = "1";
		o[0].style.transform = "rotate(45deg)";
	}
}
function getAutoMaxWidth() {
	var Width = $("#").width;
}

//在导航窗口中显示与此词匹配的字典中的词
function showMatchedWordsInNavi(wordId) {
	//var matchedCounter=0;
	/*
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	
	for(var iWord=0;iWord<xDocWords.length;iWord++){
		if(getNodeText(xDocWords[iWord],"id")==wordId){
			wIndex=iWord;
		}
	}
	
	var sPaliWord = getNodeText(xDocWords[wIndex],"real");
	showWordInNavi(sPaliWord);
	*/
}

//在导航窗口中显示与此词匹配的字典中的词
function showWordInNavi(inWord) {
	var matchedCounter = 0;
	var outText = "";
	var sLastDict = "";

	var sPaliWord = inWord;
	outText = outText + "<h3>" + sPaliWord + "</h3>";
	/*将这个词与字典匹配，*/
	var iDict = 0;
	var thisWord = sPaliWord;
	for (iDict = 0; iDict < g_DictWordList.length; iDict++) {
		if (thisWord == g_DictWordList[iDict].Pali) {
			if (g_DictWordList[iDict].dictname != sLastDict) {
				outText = outText + "<dict><span>" + g_DictWordList[iDict].dictname + "<span></dict>";
				sLastDict = g_DictWordList[iDict].dictname;
			}
			outText =
				outText +
				"<input type='input' id=\"id_dict_word_list_" +
				iDict +
				"\" size='5' value='" +
				g_DictWordList[iDict].Type +
				"' />";
			outText = outText + "<input type='input' size='15' value='" + g_DictWordList[iDict].Gramma + "' /><br />";
			outText =
				outText +
				"<input type='input' size='20' value='" +
				g_DictWordList[iDict].Parent +
				"' /> <button type='button' onclick=\"showWordInNavi('" +
				g_DictWordList[iDict].Parent +
				"')\">»</button><br />";
			outText =
				outText +
				'<textarea name="dict_mean" rows="3" col="25" style="width:20em;">' +
				g_DictWordList[iDict].Mean +
				"</textarea>";
			outText = outText + "<input type='input' size='20' value='" + g_DictWordList[iDict].Factors + "' /><br />";
			outText =
				outText + "<input type='input' size='20' value='" + g_DictWordList[iDict].FactorMean + "' /><br />";
			outText =
				outText +
				"<button type='button' onclick=\"updataDict('" +
				iDict +
				"','userdict')\">Modify</button><br />";
			/*
			outText=outText+"<mean onclick=\"updataWordFromDict(this,'mean')\">"+g_DictWordList[iDict].Mean+"</mean>";				
			outText=outText+"<org onclick=\"updataWordFromDict(this,'org')\">"+g_DictWordList[iDict].Factors+"</org>";
			outText=outText+"<om onclick=\"updataWordFromDict(this,'om')\">"+g_DictWordList[iDict].FactorMean+"</om>";					
			outText=outText+"<case onclick=\"updataWordFromDict(this,'case')\">"+g_DictWordList[iDict].Type+"#"+g_DictWordList[iDict].Gramma+"</case>";
			*/
			matchedCounter++;
		}
	}

	document.getElementById("id_dict_matched").innerHTML = outText;
	document.getElementById("id_dict_curr_word_inner").innerHTML = dictCurrWordShowAsTable(inWord);
}

function updataWordFromDict(obj, field) {
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	var strValue = obj.innerHTML;
	var applayTo = document.getElementById("id_dict_applay_to").value;
	var strCurrPali = getNodeText(xDocWords[g_currEditWord], "pali");
	switch (applayTo) {
		case "current":
			setNodeText(xDocWords[g_currEditWord], field, strValue);
			modifyWordDetailByWordIndex(g_currEditWord);
			break;
		case "sys":
			for (i = 0; i < xDocWords.length; i++) {
				var strPali = getNodeText(xDocWords[i], "pali");
				if (strCurrPali == strPali) {
					var isAuto = getNodeText(xDocWords[i], "bmc");
					if (isAuto == "bmca") {
						setNodeText(xDocWords[g_currEditWord], field, strValue);
						modifyWordDetailByWordIndex(i);
					}
				}
			}
			break;
		case "all":
			for (i = 0; i < xDocWords.length; i++) {
				var strPali = getNodeText(xDocWords[i], "pali");
				if (strCurrPali == strPali) {
					setNodeText(xDocWords[g_currEditWord], field, strValue);
					modifyWordDetailByWordIndex(i);
				}
			}
			break;
	}
}

function setBookMarkColor(obj, strColor) {
	var items = obj.parentNode.getElementsByTagName("li");
	for (var i = 0; i < items.length; i++) {
		items[i].style.outline = "0px solid";
	}
	if (g_currBookMarkColor == strColor || strColor == "bmc0") {
		g_currBookMarkColor = "bmc0";
	} else {
		obj.style.outline = "0.2em solid";
		g_currBookMarkColor = strColor;
	}

	//apply_button_lock();
}

function getBookMarkColor(idColor) {
	var items = document.getElementById("id_book_mark_color_select").getElementsByTagName("li");
	for (var i = 0; i < items.length; i++) {
		items[i].style.outline = "0px solid";
	}
	if (document.getElementById("id_" + idColor)) {
		document.getElementById("id_" + idColor).style.outline = "0.2em solid";
	}
}

function match_key(obj) {
	g_key_match_str = obj.value;
	for (unicode_key_i in local_code_str) {
		g_key_match_str = g_key_match_str.replace(/\+/g, "");
		g_key_match_str = g_key_match_str.replace(/\[/g, "");
		g_key_match_str = g_key_match_str.replace(/\]/g, "");
	}
}

function unicode_key(obj) {
	var strNew = obj.value;
	var key_match_str = strNew;
	var replace_judge = 0;
	key_match_str = key_match_str.replace(/\+/g, "");
	key_match_str = key_match_str.replace(/\[/g, "");
	key_match_str = key_match_str.replace(/\]/g, "");
	for (unicode_key_i in local_code_str) {
		if (strNew.lastIndexOf(local_code_str[unicode_key_i].id) != -1) {
			replace_judge = 1;
			break;
		}
	}

	if (
		key_match_str != g_key_match_str &&
		replace_judge == 1 &&
		document.getElementById("input_smart_switch").checked
	) {
		for (unicode_key_i in local_code_str) {
			strNew = strNew.replace(local_code_str[unicode_key_i].id, local_code_str[unicode_key_i].value);
		}
		obj.value = strNew;
	}
}
function input_key(obj) {
	var strNew = obj.value;
	for (input_key_i in local_codestr_sinhala) {
		strNew = strNew.replace(local_codestr_sinhala[input_key_i].id, local_codestr_sinhala[input_key_i].value);
	}
	for (input_key_i in local_codestr_sinhala) {
		strNew = strNew.replace(local_codestr_sinhala[input_key_i].id, local_codestr_sinhala[input_key_i].value);
	}

	obj.value = strNew;
}

function getPaliReal(inStr) {
	var paliletter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
	var output = "";
	inStr = inStr.toLowerCase();
	inStr = inStr.replace(/ṁ/g, "ṃ");
	inStr = inStr.replace(/ŋ/g, "ṃ");
	for (x in inStr) {
		if (paliletter.indexOf(inStr[x]) != -1) {
			output += inStr[x];
		}
	}
	return output;
}

function menu_file_convert() {
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	var outText = "";
	var sLastDict = "";
	for (var iword = 0; iword < xDocWords.length; iword++) {
		var sPaliWord = getNodeText(xDocWords[iword], "pali");
		var sPaliMean = getNodeText(xDocWords[iword], "mean");
		/*var thisWord = sPaliWord.toLowerCase();
		thisWord = thisWord.replace(/-/g,"");
		thisWord = thisWord.replace(/'/g,"");
		thisWord = thisWord.replace(/’/g,"");*/
		setNodeText(xDocWords[iword], "real", getPaliReal(sPaliWord));
		setNodeText(xDocWords[iword], "om", sPaliMean);
	}
	alert("convert " + xDocWords.length + "words.");
}

function editor_save() {
	$.post(
		"./dom_http.php",
		{
			fileid: g_docid,
			xmldata: com_xmlToString(gXmlBookData),
		},
		function (data, status) {
			ntf_show("Data: " + data + "\nStatus: " + status);
		}
	);
}

/*Parse csv data and fill this document*/
function csvDataParse(xmlCSVData) {
	document.getElementById("id_csv_msg_inner").innerHTML = "Parseing CSV Data";
	var xCSV = xmlCSVData.getElementsByTagName("word");
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");

	for (iword = 0; iword < xCSV.length; iword++) {
		setNodeText(xDocWords[iword], "pali", getNodeText(xCSV[iword], "pali"));
		setNodeText(xDocWords[iword], "real", getNodeText(xCSV[iword], "real"));
		setNodeText(xDocWords[iword], "id", getNodeText(xCSV[iword], "id"));
		setNodeText(xDocWords[iword], "mean", getNodeText(xCSV[iword], "mean"));
		setNodeText(xDocWords[iword], "org", getNodeText(xCSV[iword], "org"));
		setNodeText(xDocWords[iword], "om", getNodeText(xCSV[iword], "om"));
		setNodeText(xDocWords[iword], "case", getNodeText(xCSV[iword], "case"));
		setNodeText(xDocWords[iword], "bmc", getNodeText(xCSV[iword], "bmc"));
		setNodeText(xDocWords[iword], "bmt", getNodeText(xCSV[iword], "bmt"));
		setNodeText(xDocWords[iword], "note", getNodeText(xCSV[iword], "note"));
		setNodeText(xDocWords[iword], "lock", getNodeText(xCSV[iword], "lock"));
		modifyWordDetailByWordIndex(iword);
	}
	document.getElementById("id_csv_msg_inner").innerHTML = "Updata Document Data OK!";
}

//import csv end

//export cav begin
function menu_file_export_ild() {
	xmlHttp = null;
	var_dump(gLocal.gui.loading);
	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		xmlHttp = new XMLHttpRequest();
		var_dump("test XMLHttpRequest<br/>");
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		var_dump("testing Microsoft.XMLHTTP<br/>");
	}

	if (xmlHttp != null) {
		var_dump(gLocal.gui.ok);
		xmlHttp.open("POST", "./export_ild.php", false);
		var sendHead = "filename=" + g_filename + "#";
		var dictDataString = "<dict>";
		for (iDict in g_DictWordList) {
			dictDataString += "<word>";
			dictDataString += "<recorderId>" + g_DictWordList[iDict].Id + "</recorderId>";
			dictDataString += "<pali>" + g_DictWordList[iDict].Pali + "</pali>";
			dictDataString += "<mean>" + g_DictWordList[iDict].Mean + "</mean>";
			dictDataString += "<type>" + g_DictWordList[iDict].Type + "</type>";
			dictDataString += "<gramma>" + g_DictWordList[iDict].Gramma + "</gramma>";
			dictDataString += "<parent>" + g_DictWordList[iDict].Parent + "</parent>";
			dictDataString += "<factors>" + g_DictWordList[iDict].Factors + "</factors>";
			dictDataString += "<factorMean>" + g_DictWordList[iDict].FactorMean + "</factorMean>";
			dictDataString += "<note>" + g_DictWordList[iDict].Note + "</note>";
			dictDataString += "<confer>" + g_DictWordList[iDict].Confer + "</confer>";
			dictDataString += "<status>" + g_DictWordList[iDict].Status + "</status>";
			dictDataString += "<delete>" + g_DictWordList[iDict].Delete + "</delete>";
			dictDataString += "<dictname>" + g_DictWordList[iDict].dictname + "</dictname>";
			dictDataString += "<dictType>" + g_DictWordList[iDict].dictType + "</dictType>";
			dictDataString += "<fileName>" + g_DictWordList[iDict].fileName + "</fileName>";
			dictDataString += "<parentLevel>" + g_DictWordList[iDict].ParentLevel + "</parentLevel>";
			dictDataString += "</word>";
		}
		dictDataString += "</dict>";
		xmlHttp.send(sendHead + dictDataString);
		var_dump(xmlHttp.responseText);
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function menu_file_tools_empty(opt) {
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	if (opt == "all") {
		for (var iword = 0; iword < xDocWords.length; iword++) {
			setNodeText(xDocWords[iword], "mean", "?");
			setNodeText(xDocWords[iword], "org", "?");
			setNodeText(xDocWords[iword], "om", "?");
			setNodeText(xDocWords[iword], "case", "?");
			setNodeText(xDocWords[iword], "parent","?");
			setNodeText(xDocWords[iword], "bmc", "");
			setNodeText(xDocWords[iword], "bmt", "");
			setNodeText(xDocWords[iword], "note", "");
			setNodeText(xDocWords[iword], "lock", "FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	} else if (opt == "mean") {
		for (var iword = 0; iword < xDocWords.length; iword++) {
			setNodeText(xDocWords[iword], "mean", "[]");
			//setNodeText(xDocWords[iword],"org","?");
			setNodeText(xDocWords[iword], "om", "");
			//setNodeText(xDocWords[iword],"case","?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	} else if (opt == "case") {
		for (var iword = 0; iword < xDocWords.length; iword++) {
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			setNodeText(xDocWords[iword], "case", "?#?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	} else if (opt == "bookmark") {
		for (var iword = 0; iword < xDocWords.length; iword++) {
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			//setNodeText(xDocWords[iword],"case","?#?");
			setNodeText(xDocWords[iword], "bmc", "");
			setNodeText(xDocWords[iword], "bmt", "");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	} else if (opt == "note") {
		for (var iword = 0; iword < xDocWords.length; iword++) {
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			//setNodeText(xDocWords[iword],"case","?#?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			setNodeText(xDocWords[iword], "note", "");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	}
}

function menu_file_tools_GUID() {
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");

	for (var iword = 0; iword < xDocWords.length; iword++) {
		setNodeText(xDocWords[iword], "id", com_guid());
		modifyWordDetailByWordIndex(iword);
	}
	var_dump("reset id finished!");
}

function showDebugPanal() {
	var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

	var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
}

function show_popup(strMsg) {
	var p = window.createPopup();
	var pbody = p.document.body;
	pbody.style.backgroundColor = "red";
	pbody.style.border = "solid black 1px";
	pbody.innerHTML = strMsg + "<br />外面点击，即可关闭它！";
	p.show(150, 150, 200, 50, document.body);
}
//Info_Panal顯示 begin

function setInfoPanalVisibility() {
	if (document.getElementById("id_info_panal").style.height == "0px") {
		setInfoPanalSize("half");
	} else {
		setInfoPanalSize("hidden");
	}
}

//Info_Panal顯示尺寸
function setInfoPanalSize(inSize) {
	var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

	var objInfoPanal = document.getElementById("id_info_panal");
	//show_popup(w);
	//alert(objInfoPanal.style.right);

	switch (inSize) {
		case "hidden": //min
			objInfoPanal.style.height = 0 + "px";
			//setTimeout("hiddenPanal()",400);
			break;

		case "min": //min
			objInfoPanal.style.height = 30 + "px";
			break;
		case "half": //half
			objInfoPanal.style.height = h / 2 + "px";
			break;
		case "0.6": //2/3
			objInfoPanal.style.height = h * 0.6 + "px";
			break;
		case "max": //max
			objInfoPanal.style.height = h + "px";
			break;
	}
}
function hiddenPanal() {
	document.getElementById("id_info_panal").style.display = "none";
}

//Info_Panal顯示 end

function windowsSelected(obj) {
	document.getElementById("word_table").style.display = "none";
	document.getElementById("id_dict_match_result").style.display = "none";
	document.getElementById("id_dict_curr_word").style.display = "none";
	document.getElementById("id_debug").style.display = "none";
	switch (obj.value) {
		case "view_vocabulary":
			document.getElementById("word_table").style.display = "block";
			break;
		case "view_dict_all":
			document.getElementById("id_dict_match_result").style.display = "block";
			break;
		case "view_dict_curr":
			document.getElementById("id_dict_curr_word").style.display = "block";
			break;
		case "view_debug":
			document.getElementById("id_debug").style.display = "block";
			break;
	}
}

function userDictUpdata() {}

var editor_DictUpdataXmlHttp = null;
function editor_UserDictUpdata(recorderName, thisObj) {
	thisObj.disabled = true;
	var xmlText = "";

	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		editor_DictUpdataXmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		editor_DictUpdataXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (editor_DictUpdataXmlHttp != null) {
		var queryString = "<wordlist>";
		queryString += "<word>";
		var d_id = document.getElementById("id_dict_user_id_" + recorderName).value;
		var d_pali = document.getElementById("id_dict_user_pali_" + recorderName).value;
		var d_type = document.getElementById("id_dict_user_type_" + recorderName).value;
		var d_gramma = document.getElementById("id_dict_user_gramma_" + recorderName).value;
		var d_parent = document.getElementById("id_dict_user_parent_" + recorderName).value;
		var d_mean = document.getElementById("id_dict_user_mean_" + recorderName).value;
		var d_note = document.getElementById("id_dict_user_note_" + recorderName).value;
		var d_factors = document.getElementById("id_dict_user_factors_" + recorderName).value;
		var d_fm = document.getElementById("id_dict_user_fm_" + recorderName).value;
		var d_confer = "";
		var d_status = "";
		var d_delete = "";
		var d_tag = "";
		var d_language = language_translation;
		queryString += "<id>" + d_id + "</id>";
		queryString += "<pali>" + d_pali + "</pali>";
		queryString += "<type>" + d_type + "</type>";
		queryString += "<gramma>" + d_gramma + "</gramma>";
		queryString += "<parent>" + d_parent + "</parent>";
		queryString += "<mean>" + d_mean + "</mean>";
		queryString += "<note>" + d_note + "</note>";
		queryString += "<factors>" + d_factors + "</factors>";
		queryString += "<fm>" + d_fm + "</fm>";
		queryString += "<confer>" + d_confer + "</confer>";
		queryString += "<status>" + d_status + "</status>";
		queryString += "<enable>TRUE</enable>";
		queryString += "<language>" + d_language + "</language>";
		queryString += "</word>";
		queryString += "</wordlist>";
		editor_DictUpdataXmlHttp.onreadystatechange = editor_UserDictUpdata_serverResponse;
		debugOutput("updata user dict start.", 0);
		editor_DictUpdataXmlHttp.open("POST", "./dict_updata_user.php", true);
		editor_DictUpdataXmlHttp.send(queryString);

		var i = recorderName;
		g_DictWordUpdataIndex = i;
		g_DictWordNew.Id = d_id;
		g_DictWordNew.Pali = d_pali;
		g_DictWordNew.Mean = d_mean;
		g_DictWordNew.Type = d_type;
		g_DictWordNew.Gramma = d_gramma;
		g_DictWordNew.Parent = d_parent;
		g_DictWordNew.Factors = d_factors;
		g_DictWordNew.FactorMean = d_fm;
		g_DictWordNew.Note = d_note;
		g_DictWordNew.Confer = d_confer;
		g_DictWordNew.Status = d_status;
		g_DictWordNew.Delete = d_delete;
		g_DictWordNew.dictname = "用户字典";
		g_DictWordNew.dictType = "user";
		g_DictWordNew.fileName = "user_default";
		g_DictWordNew.ParentLevel = 0;
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function editor_UserDictUpdata_serverResponse() {
	if (editor_DictUpdataXmlHttp.readyState == 4) {
		// 4 = "loaded"
		debugOutput("server response.", 0);
		if (editor_DictUpdataXmlHttp.status == 200) {
			// 200 = "OK"
			var serverText = editor_DictUpdataXmlHttp.responseText;
			debugOutput(serverText, 0);
			obj = JSON.parse(serverText);
			if (obj.msg[0].server_return == -1) {
				alert(obj.msg[0].server_error);
			} else {
				var_dump(gLocal.gui.userdict + obj.msg[0].server_op + " " + gLocal.gui.ok);
				switch (obj.msg[0].server_op) {
					case "insert":
						g_DictWordNew.Id = obj.msg[0].server_return;
						g_DictWordNew.dictID = 1; /*temp code*/
						var inDict = false;
						for (iFindNew in g_DictWordList) {
							if (
								g_DictWordList[iFindNew].Id == g_DictWordNew.Id &&
								g_DictWordList[iFindNew].fileName == "user_default"
							) {
								inDict = true;
								break;
							}
						}
						//if recorder in list don't add to list
						if (!inDict) {
							g_DictWordList.unshift(g_DictWordNew);
							editor_insertNewWordToInlineDict(g_DictWordNew);
						}
						break;
					case "update":
						g_DictWordList[g_DictWordUpdataIndex] = g_DictWordNew;
						editor_updataInlineDict(g_DictWordUpdataIndex, g_DictWordNew);
						break;
				}
			}
			showCurrWordTable(g_WordTableCurrWord);
			modifyWordDetailByWordId(g_currEditWord);
		} else {
			debugOutput(xmlhttp.statusText, 0);
		}
	}
}

function editor_insertNewWordToInlineDict(newWord) {
	var xAllWord = gXmlBookDataInlineDict.getElementsByTagName("word");
	var newElement = gXmlBookData.createElement("word");
	setNodeText(newElement, "id", newWord.Id.toString());
	setNodeText(newElement, "guid", newWord.Guid);
	setNodeText(newElement, "pali", newWord.Pali);
	setNodeText(newElement, "mean", newWord.Mean);
	setNodeText(newElement, "type", newWord.Type);
	setNodeText(newElement, "gramma", newWord.Gramma);
	setNodeText(newElement, "parent", newWord.Parent);
	setNodeText(newElement, "parentid", newWord.ParentId);
	setNodeText(newElement, "factors", newWord.Factors);
	setNodeText(newElement, "factorMean", newWord.FactorMean);
	setNodeText(newElement, "partid", newWord.PartId);
	setNodeText(newElement, "note", newWord.Note);
	setNodeText(newElement, "confer", newWord.Confer);
	setNodeText(newElement, "status", newWord.Status);
	setNodeText(newElement, "enable", newWord.Enable);
	setNodeText(newElement, "dictname", newWord.dictname);
	setNodeText(newElement, "dictType", newWord.dictType);
	setNodeText(newElement, "fileName", newWord.fileName);
	setNodeText(newElement, "parentLevel", newWord.ParentLevel.toString());
	if (xAllWord.length > 0) {
		gXmlBookDataInlineDict.insertBefore(newElement, xAllWord[0]);
	} else {
		gXmlBookDataInlineDict.insertBefore(newElement, null);
	}
}

function editor_updataInlineDict(iword, newWord) {
	var xILD = gXmlBookDataInlineDict.getElementsByTagName("word");
	if (xILD == null) {
		return;
	}

	setNodeText(xILD[iword], "id", newWord.Id);
	setNodeText(xILD[iword], "pali", newWord.Pali);
	setNodeText(xILD[iword], "mean", newWord.Mean);
	setNodeText(xILD[iword], "type", newWord.Type);
	setNodeText(xILD[iword], "gramma", newWord.Gramma);
	setNodeText(xILD[iword], "parent", newWord.Parent);
	setNodeText(xILD[iword], "factors", newWord.Factors);
	setNodeText(xILD[iword], "factorMean", newWord.FactorMean);
	setNodeText(xILD[iword], "note", newWord.Note);
	setNodeText(xILD[iword], "confer", newWord.Confer);
	setNodeText(xILD[iword], "status", newWord.Status);
	setNodeText(xILD[iword], "delete", newWord.Delete);
	setNodeText(xILD[iword], "dictname", newWord.dictname);
	setNodeText(xILD[iword], "dictType", newWord.dictType);
	setNodeText(xILD[iword], "fileName", newWord.fileName);
	setNodeText(xILD[iword], "parentLevel", newWord.ParentLevel);
}
/*
上传到我的字典
*/
function upload_to_my_dict(wordIdFrom = -1, wordIdTo = -1) {
	let words = new Array();

	let queryString = "<wordlist>";
	let x = gXmlBookDataBody.getElementsByTagName("word");
	let iCount = 0;

	let wordNode;
	let d_pali;
	let d_guid;
	let d_mean;
	let d_parent;
	let d_parent_id;
	let d_parentmean;
	let d_factors;
	let d_fm;
	let d_part_id;
	let d_case;
	let d_note;
	let formula;

	for (let wordID = wordIdFrom; wordID <= wordIdTo; wordID++) {
		if (wordIdFrom == -1) {
			d_pali = doc_word("#" + g_currEditWord).val("real");
			d_guid = "";
			d_mean = $("#input_meaning").val();

			d_parent = $("#id_text_parent").val();
			d_parent_id = "";
			d_parent = com_getPaliReal(d_parent);
			d_parentmean = removeFormulaB(d_mean, "[", "]");
			d_parentmean = removeFormulaB(d_parentmean, "{", "}");
			//if(d_parentmean.substr())
			d_factors = $("#input_org").val();
			d_fm = $("#input_om").val(); //g_arrPartMean.join("+");
			d_part_id = "";
			d_case = $("#input_case").val();
			d_note = $("#id_text_note").val();
		} else {
			wordNode = x[wordID];
			d_pali = getNodeText(wordNode, "real");
			d_guid = getNodeText(wordNode, "guid");
			d_mean = getNodeText(wordNode, "mean");
			d_parent = getNodeText(wordNode, "parent");
			d_parent_id = getNodeText(wordNode, "parent_id");
			d_parent = com_getPaliReal(d_parent);
			d_parentmean = removeFormulaB(d_mean, "[", "]");
			d_parentmean = removeFormulaB(d_parentmean, "{", "}");
			d_factors = getNodeText(wordNode, "org");
			d_fm = getNodeText(wordNode, "om");
			d_part_id = getNodeText(wordNode, "part_id");
			d_case = getNodeText(wordNode, "case");
			d_note = getNodeText(wordNode, "note");
		}
		var iPos = d_case.indexOf("#");
		if (iPos >= 0) {
			var d_type = d_case.substring(0, iPos);
			if (iPos < d_case.length - 1) {
				var d_gramma = d_case.substring(iPos + 1);
			} else {
				var d_gramma = "";
			}
		} else {
			var d_type = "";
			var d_gramma = d_case;
		}
		formula = getFormulaFromMeaning(d_mean, "[", "]");

		let d_language = get_string_lang(d_mean);

		if (d_mean == "?") {
			d_mean = "";
		}
		if (d_factors == "?") {
			d_factors = "";
		}
		if (d_fm == "?" || d_fm == "[a]?") {
			d_fm = "";
		}

		if ((d_type == ".un." || d_type == ".comp.") && d_mean == "" && d_factors == "" && d_fm == "") {
		} else if (d_pali.length > 0 && !(d_mean == "" && d_factors == "" && d_fm == "" && d_case == "")) {
			//parent gramma infomation
			switch (d_type) {
				case ".n.":
					d_parentType = ".n:base.";
					d_parentGramma = d_gramma.split("$")[0];
					if (d_parentGramma == ".m." || d_parentGramma == ".f." || d_parentGramma == ".nt.") {
						d_parentGramma = d_parentGramma;
					} else {
						d_parentGramma = "";
					}
					break;
				case ".adj.":
					d_parentType = ".ti:base.";
					d_parentGramma = ".adj.";
					break;
				case ".ti.":
					d_parentType = ".ti:base.";
					d_parentGramma = "";
					break;
				case ".pron.":
					d_parentType = ".pron:base.";
					d_parentGramma = "";
					break;
				case ".num.":
					d_parentType = ".num:base.";
					d_parentGramma = "";
					break;
				case ".v.":
					d_parentType = ".v:base.";
					d_parentGramma = "";
					break;
				case ".v:ind.":
					d_parentType = ".v:base.";
					d_parentGramma = "";
					break;
				case ".ind.":
					d_parentType = ".ind.";
					d_parentGramma = "";
					break;
				default:
					d_parentType = "";
					d_parentGramma = "";
					break;
			}

			let d_confer = "";
			let d_status = "512";
			let d_enable = "TRUE";

			queryString += "<word>";
			queryString += "<pali>" + d_pali + "</pali>";
			queryString += "<guid>" + d_guid + "</guid>";
			queryString += "<type>" + d_type + "</type>";
			queryString += "<gramma>" + d_gramma + "</gramma>";
			queryString += "<parent>" + d_parent + "</parent>";
			queryString += "<parent_id>" + d_parent_id + "</parent_id>";
			queryString += "<mean>" + d_mean + "</mean>";
			queryString += "<note>" + d_note + "</note>";
			queryString += "<factors>" + d_factors + "</factors>";
			queryString += "<fm>" + d_fm + "</fm>";
			queryString += "<part_id>" + d_part_id + "</part_id>";
			queryString += "<confer>" + d_confer + "</confer>";
			queryString += "<status>" + d_status + "</status>";
			queryString += "<enable>" + d_enable + "</enable>";
			queryString += "<language>" + d_language + "</language>";
			queryString += "</word>";
			words.push(
				{
					word:d_pali,
					type:d_type,
					grammar:d_gramma,
					parent:d_parent,
					mean:d_mean,
					factors:d_factors,
					factormean:d_fm,
					language:d_language,
				}
			)
			iCount++;

			//formula
			if (formula != "~") {
				queryString += "<word>";
				queryString += "<pali>_formula_</pali>";
				queryString += "<guid></guid>";
				queryString += "<type>" + d_type + "</type>";
				queryString += "<gramma>" + d_gramma + "</gramma>";
				queryString += "<parent></parent>";
				queryString += "<parent_id></parent_id>";
				queryString += "<mean>" + formula + "</mean>";
				queryString += "<note></note>";
				queryString += "<factors></factors>";
				queryString += "<fm></fm>";
				queryString += "<part_id></part_id>";
				queryString += "<confer></confer>";
				queryString += "<status>" + d_status + "</status>";
				queryString += "<enable>" + d_enable + "</enable>";
				queryString += "<language>" + d_language + "</language>";
				queryString += "</word>";
				iCount++;
				words.push(
					{
						word:"_formula_",
						type:d_type,
						grammar:d_gramma,
						parent:"",
						mean:formula,
						factors:"",
						factormean:"",
						language:d_language,
					}
				);
			}

			//parent recorder
			if (d_parent.length > 0) {
				queryString += "<word>";
				queryString += "<pali>" + d_parent + "</pali>";
				queryString += "<guid></guid>";
				queryString += "<type>" + d_parentType + "</type>";
				queryString += "<gramma>" + d_parentGramma + "</gramma>";
				queryString += "<parent></parent>";
				queryString += "<parent_id></parent_id>";
				queryString += "<mean>" + d_parentmean + "</mean>";
				queryString += "<note></note>";
				let fc = d_factors.split("+");
				if (fc.length > 0 && fc[fc.length - 1].slice(0, 1) == "[" && fc[fc.length - 1].slice(-1) == "]") {
					fc.pop();
				}
				queryString += "<factors>" + fc.join("+") + "</factors>";
				let fm = d_fm.split("+");
				fm.length = fc.length;
				queryString += "<fm>" + fm.join("+") + "</fm>";
				queryString += "<part_id></part_id>";
				queryString += "<confer></confer>";
				queryString += "<status>512</status>";
				queryString += "<enable>TRUE</enable>";
				queryString += "<language>" + d_language + "</language>";
				queryString += "</word>";
				iCount++;
				words.push(
					{
						word:d_parent,
						type:d_parentType,
						grammar:d_parentGramma,
						parent:"",
						mean:d_parentmean,
						factors:fc.join("+"),
						factormean:fm.join("+"),
						language:d_language,
					}
				);
			}

			//part recorder
			if (d_fm.slice(0, 3) != "[a]") {
				let arrPart = d_factors.split("+");
				let arrPartMean = d_fm.split("+");
				if (arrPart.length > 0 && arrPart.length == arrPartMean.length) {
					for (iPart in arrPart) {
						if (arrPartMean[iPart] != "" && arrPartMean[iPart] != "?")
							arrPart[iPart] = arrPart[iPart].replace("(", "");
						arrPart[iPart] = arrPart[iPart].replace(")", "");
						queryString += "<word>";
						queryString += "<guid></guid>";
						queryString += "<pali>" + arrPart[iPart] + "</pali>";
						queryString += "<type>.part.</type>";
						queryString += "<gramma></gramma>";
						queryString += "<parent></parent>";
						queryString += "<parent_id></parent_id>";
						queryString += "<mean>" + arrPartMean[iPart] + "</mean>";
						queryString += "<note></note>";
						queryString += "<factors></factors>";
						queryString += "<fm></fm>";
						queryString += "<part_id></part_id>";
						queryString += "<confer></confer>";
						queryString += "<status>512</status>";
						queryString += "<enable>TRUE</enable>";
						queryString += "<language>" + d_language + "</language>";
						queryString += "</word>";
						iCount++;
						words.push(
							{
								word:arrPart[iPart],
								type:".part.",
								grammar:"",
								parent:"",
								mean:arrPartMean[iPart],
								factors:"",
								factormean:"",
								language:d_language,
							}
						);
					}
				}
			}
		}
	}
	queryString += "</wordlist>";

	if (iCount == 0) {
		ntf_show("no word update");
	} else {
		/*
		$.post(
			"./dict_updata_wbw.php", 
			queryString, 
			function (data, status) {
				ntf_show("Data: " + data + "\nStatus: " + status);
			}
		);
		*/
		$.post(
			"../api/user_dicts.php", 
			{
				op:'create',
				view:'wbw',
				data: JSON.stringify(words),
			}, 
			function (data, status) {
				ntf_show("Data: " + data + "\nStatus: " + status);
			}
		);
	}
}

// word by word dict updata
var editor_wbwUpdataXmlHttp = null;
function editor_WbwUpdata(wordIdFrom, wordIdTo) {
	var xmlText = "";

	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		editor_wbwUpdataXmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		editor_wbwUpdataXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (editor_wbwUpdataXmlHttp != null) {
		let queryString = "<wordlist>";
		let x = gXmlBookDataBody.getElementsByTagName("word");
		let iCount = 0;
		let d_language = language_translation;
		for (let wordID = wordIdFrom; wordID <= wordIdTo; wordID++) {
			let wordNode = x[wordID];
			let d_pali = getNodeText(wordNode, "real");
			let d_guid = getNodeText(wordNode, "guid");
			let d_mean = getNodeText(wordNode, "mean");
			let d_parent = getNodeText(wordNode, "parent");
			let d_parent_id = getNodeText(wordNode, "parent_id");
			d_parent = com_getPaliReal(d_parent);
			let d_parentmean = removeFormulaB(d_mean, "[", "]");
			d_parentmean = removeFormulaB(d_parentmean, "{", "}");
			let formula = getFormulaFromMeaning(d_mean);
			let d_factors = getNodeText(wordNode, "org");
			let d_fm = getNodeText(wordNode, "om");
			let d_part_id = getNodeText(wordNode, "part_id");
			let d_case = getNodeText(wordNode, "case");
			let d_note = getNodeText(wordNode, "note");

			if (d_pali.length > 0 && !(d_mean == "?" && d_factors == "?" && d_fm == "?" && d_case == "?")) {
				let iPos = d_case.indexOf("#");
				if (iPos >= 0) {
					let d_type = d_case.substring(0, iPos);
					if (iPos < d_case.length - 1) {
						let d_gramma = d_case.substring(iPos + 1);
					} else {
						let d_gramma = "";
					}
				} else {
					let d_type = "";
					let d_gramma = d_case;
				}

				//parent gramma infomation
				switch (d_type) {
					case ".n.":
						d_parentType = ".n:base.";
						d_parentGramma = d_gramma.split("$")[0];
						if (d_parentGramma == ".m." || d_parentGramma == ".f." || d_parentGramma == ".nt.") {
							d_parentGramma = d_parentGramma;
						} else {
							d_parentGramma = "";
						}
						break;
					case ".adj.":
						d_parentType = ".adj:base.";
						d_parentGramma = "";
						break;
					case ".ti.":
						d_parentType = ".ti:base.";
						d_parentGramma = "";
						break;
					case ".pron.":
						d_parentType = ".pron:base.";
						d_parentGramma = "";
						break;
					case ".num.":
						d_parentType = ".num:base.";
						d_parentGramma = "";
						break;
					case ".v.":
						d_parentType = ".v:base.";
						d_parentGramma = "";
						break;
					case ".ind.":
						d_parentType = ".ind.";
						d_parentGramma = "";
						break;
					default:
						d_parentType = "";
						d_parentGramma = "";
						break;
				}

				let d_confer = "";
				let d_status = "512";
				let d_enable = "TRUE";

				queryString += "<word>";
				queryString += "<pali>" + d_pali + "</pali>";
				queryString += "<guid>" + d_guid + "</guid>";
				queryString += "<type>" + d_type + "</type>";
				queryString += "<gramma>" + d_gramma + "</gramma>";
				queryString += "<parent>" + d_parent + "</parent>";
				queryString += "<parent_id>" + d_parent_id + "</parent_id>";
				queryString += "<mean>" + d_mean + "</mean>";
				queryString += "<note>" + d_note + "</note>";
				queryString += "<factors>" + d_factors + "</factors>";
				queryString += "<fm>" + d_fm + "</fm>";
				queryString += "<part_id>" + d_part_id + "</part_id>";
				queryString += "<confer>" + d_confer + "</confer>";
				queryString += "<status>" + d_status + "</status>";
				queryString += "<enable>" + d_enable + "</enable>";
				queryString += "<language>" + d_language + "</language>";
				queryString += "</word>";
				iCount++;

				//formula
				queryString += "<word>";
				queryString += "<pali>_formula_</pali>";
				queryString += "<guid></guid>";
				queryString += "<type>" + d_type + "</type>";
				queryString += "<gramma>" + d_gramma + "</gramma>";
				queryString += "<parent></parent>";
				queryString += "<parent_id></parent_id>";
				queryString += "<mean>" + formula + "</mean>";
				queryString += "<note></note>";
				queryString += "<factors></factors>";
				queryString += "<fm></fm>";
				queryString += "<part_id></part_id>";
				queryString += "<confer></confer>";
				queryString += "<status>" + d_status + "</status>";
				queryString += "<enable>" + d_enable + "</enable>";
				queryString += "<language>" + d_language + "</language>";
				queryString += "</word>";
				iCount++;

				//parent recorder
				if (d_parent.length > 0) {
					queryString += "<word>";
					queryString += "<pali>" + d_parent + "</pali>";
					queryString += "<guid></guid>";
					queryString += "<type>" + d_parentType + "</type>";
					queryString += "<gramma>" + d_parentGramma + "</gramma>";
					queryString += "<parent></parent>";
					queryString += "<parent_id></parent_id>";
					queryString += "<mean>" + d_parentmean + "</mean>";
					queryString += "<note></note>";
					queryString += "<factors></factors>";
					queryString += "<fm></fm>";
					queryString += "<part_id></part_id>";
					queryString += "<confer></confer>";
					queryString += "<status>512</status>";
					queryString += "<enable>TRUE</enable>";
					queryString += "<language>" + d_language + "</language>";
					queryString += "</word>";
					iCount++;
				}

				//part recorder
				arrPart = d_factors.split("+");
				arrPartMean = d_fm.split("+");
				if (arrPart.length > 0 && arrPart.length == arrPartMean.length) {
					for (iPart in arrPart) {
						if (arrPartMean[iPart] != "?" && arrPartMean[iPart] != "" && arrPartMean[iPart] != "") {
							arrPart[iPart] = arrPart[iPart].replace("(", "");
							arrPart[iPart] = arrPart[iPart].replace(")", "");
							queryString += "<word>";
							queryString += "<guid></guid>";
							queryString += "<pali>" + arrPart[iPart] + "</pali>";
							queryString += "<type>.part.</type>";
							queryString += "<gramma></gramma>";
							queryString += "<parent></parent>";
							queryString += "<parent_id></parent_id>";
							queryString += "<mean>" + arrPartMean[iPart] + "</mean>";
							queryString += "<note></note>";
							queryString += "<factors></factors>";
							queryString += "<fm></fm>";
							queryString += "<part_id></part_id>";
							queryString += "<confer></confer>";
							queryString += "<status>512</status>";
							queryString += "<enable>TRUE</enable>";
							queryString += "<language>" + d_language + "</language>";
							queryString += "</word>";
							iCount++;
						}
					}
				}
			}
		}
		queryString += "</wordlist>";
		if (iCount > 0) {
			editor_wbwUpdataXmlHttp.onreadystatechange = editor_wbwDictUpdata_serverResponse;
			console.log("updata user dict start.", 0);
			editor_wbwUpdataXmlHttp.open("POST", "./dict_updata_wbw.php", true);
			editor_wbwUpdataXmlHttp.send(queryString);
		} else {
			console.log("no user dicttionary data need updata.", 0);
		}
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function editor_wbwDictUpdata_serverResponse() {
	if (editor_wbwUpdataXmlHttp.readyState == 4) {
		// 4 = "loaded"
		debugOutput("server response.", 0);
		if (editor_wbwUpdataXmlHttp.status == 200) {
			// 200 = "OK"
			let serverText = editor_wbwUpdataXmlHttp.responseText;
			var_dump(serverText);
			debugOutput(serverText, 0);
		} else {
			debugOutput(xmlhttp.statusText, 0);
		}
	}
}

function uploadAllWordData() {
	let x = gXmlBookDataBody.getElementsByTagName("word");
	if (x.length > 0) {
		editor_WbwUpdata(0, x.length - 1);
	} else {
	}
}
function renderCaseSelect(type, case1, case2, case3, unique_id, padding_width) {
	eCaseTable = document.getElementById("input_select_case");
	eCaseItems = eCaseTable.getElementsByTagName("span");

	if (type) {
		strTypeSelect =
			'<div id="id_case_dropdown_0_' +
			unique_id +
			'" class="case_dropdown gramma_selector" style=\'min-width: unset;padding-right: ' +
			padding_width +
			"em;'>";
		strTypeSelect +=
			"<p class=\"case_dropbtn cell\" style='line-height: 1.2em;'>" + getLocalGrammaStr(type) + "</p>";
	} else {
		strTypeSelect =
			'<div id="id_case_dropdown_0_' +
			unique_id +
			'" class="case_dropdown gramma_selector" style=\'min-width: unset;padding-right: ' +
			padding_width +
			"em;'>";
		strTypeSelect += "<p class=\"case_dropbtn  cell\" style='line-height: 1.2em;'>?</p>";
	}
	strTypeSelect += '<div class="case_dropdown-content">';

	for (iType = 0; iType < gLocal.type_str.length; iType++) {
		strTypeSelect +=
			"<a onclick=\"caseChanged(0,'" +
			gLocal.type_str[iType].id +
			"')\">" +
			gLocal.type_str[iType].value +
			"</a>";
	}
	strTypeSelect += "</div>";
	strTypeSelect += "</div>";
	eCaseItems[0].innerHTML = strTypeSelect;

	for (iType = 0; iType < gramma_str.length; iType++) {
		if (gramma_str[iType].id == type) {
			var strTypeSelect = "";
			gramma = gramma_str[iType].a;
			if (gramma.length > 0) {
				items = gramma.split("$");
				if (case1 == "") {
					case1 = items[0];
					g_caseSelect[1] = case1;
				}
				strTypeSelect =
					'<div id="id_case_dropdown_1_' +
					unique_id +
					'" class="case_dropdown gramma_selector" style=\'min-width: unset;padding-right: ' +
					padding_width +
					"em;'><p class=\"case_dropbtn\" style='line-height: 1.2em;'>" +
					getLocalGrammaStr(case1) +
					"</p>";
				strTypeSelect += '<div class="case_dropdown-content">';
				for (iItem = 0; iItem < items.length; iItem++) {
					strTypeSelect +=
						"<a onclick=\"caseChanged(1,'" +
						items[iItem] +
						"')\">" +
						getLocalGrammaStr(items[iItem]) +
						"</a>";
				}
				strTypeSelect += "</div>";
				strTypeSelect += "</div>";
			} else {
				g_caseSelect[1] = "";
			}
			eCaseItems[1].innerHTML = strTypeSelect;

			strTypeSelect = "";
			gramma = gramma_str[iType].b;
			if (gramma.length > 0) {
				items = gramma.split("$");
				if (case2 == "") {
					case2 = items[0];
					g_caseSelect[2] = case2;
				}
				strTypeSelect =
					'<div id="id_case_dropdown_2_' +
					unique_id +
					'" class="case_dropdown gramma_selector" style=\'min-width: unset;padding-right: ' +
					padding_width +
					"em;'><p class=\"case_dropbtn\" style='line-height: 1.2em;'>" +
					getLocalGrammaStr(case2) +
					"</p>";
				strTypeSelect += '<div class="case_dropdown-content">';
				for (iItem = 0; iItem < items.length; iItem++) {
					strTypeSelect +=
						"<a onclick=\"caseChanged(2,'" +
						items[iItem] +
						"')\">" +
						getLocalGrammaStr(items[iItem]) +
						"</a>";
				}
				strTypeSelect += "</div>";
				strTypeSelect += "</div>";
			} else {
				g_caseSelect[2] = "";
			}
			eCaseItems[2].innerHTML = strTypeSelect;

			strTypeSelect = "";
			gramma = gramma_str[iType].c;
			if (gramma.length > 0) {
				items = gramma.split("$");
				if (case3 == "") {
					case3 = items[0];
					g_caseSelect[3] = case3;
				}
				strTypeSelect =
					'<div id="id_case_dropdown_3_' +
					unique_id +
					'" class="case_dropdown gramma_selector" style=\'min-width: unset;padding-right: ' +
					padding_width +
					"em;'><p class=\"case_dropbtn\" style='line-height: 1.2em;'>" +
					getLocalGrammaStr(case3) +
					"</p>";
				strTypeSelect += '<div class="case_dropdown-content">';
				for (iItem = 0; iItem < items.length; iItem++) {
					strTypeSelect +=
						"<a onclick=\"caseChanged(3,'" +
						items[iItem] +
						"')\">" +
						getLocalGrammaStr(items[iItem]) +
						"</a>";
				}
				strTypeSelect += "</div>";
				strTypeSelect += "</div>";
			} else {
				g_caseSelect[3] = "";
			}
			eCaseItems[3].innerHTML = strTypeSelect;
		}
	}
}

function refreshCaseSelect() {
	renderCaseSelect(g_caseSelect[0], g_caseSelect[1], g_caseSelect[2], g_caseSelect[3], "wbw", 1);
	var newCaseString = g_caseSelect[0] + "#";
	if (g_caseSelect[1].length > 0) {
		newCaseString += g_caseSelect[1];
	}
	if (g_caseSelect[2].length > 0) {
		newCaseString += "$" + g_caseSelect[2];
	}
	if (g_caseSelect[3].length > 0) {
		newCaseString += "$" + g_caseSelect[3];
	}
	document.getElementById("input_case").value = newCaseString;
	rela_refresh(g_currEditWord);
}

function caseChanged(index, newValue) {
	g_caseSelect[index] = newValue;
	refreshCaseSelect();
	refreshPartMeaningSelect();
}

function removeFormula_B(inStr) {
	pos = 0;
	copy = true;
	var output = "";
	for (i = 0; i < inStr.length; i++) {
		if (inStr[i] == "{" || inStr[i] == "[") {
			copy = false;
		}
		if (copy) {
			output += inStr[i];
		}
		if (inStr[i] == "}" || inStr[i] == "]") {
			copy = true;
		}
	}
	return output;
}
function removeFormula(inStr) {
	if (inStr.indexOf("[") >= 0) {
		return inStr;
	}
	pos = 0;
	copy = true;
	var output = "";
	for (i = 0; i < inStr.length; i++) {
		if (inStr[i] == "{") {
			copy = false;
		}
		if (copy) {
			output += inStr[i];
		}
		if (inStr[i] == "}") {
			copy = true;
		}
	}
	return output;
}

//移除字符串中的格位公式
//input:[zzz]xxx[yyy]
//output:xxx
function removeFormulaB(inStr, sBegin, sEnd) {
	pos = 0;
	copy = true;
	var output = "";
	for (i = 0; i < inStr.length; i++) {
		if (inStr[i] == sBegin) {
			copy = false;
		}
		if (copy) {
			output += inStr[i];
		}
		if (inStr[i] == sEnd) {
			copy = true;
		}
	}
	return output;
}

function getFormulaFromMeaning(inStr, sBegin, sEnd) {
	let pos = 0;
	let fromulaBegin = false;
	let meaningBegin = false;
	let output = "";
	let meaningExisted = false;
	for (i = 0; i < inStr.length; i++) {
		if (inStr[i] == sBegin) {
			fromulaBegin = true;
			meaningBegin = false;
		} else if (inStr[i] == sEnd) {
			fromulaBegin = false;
			meaningBegin = true;
			output += inStr[i];
		} else {
			if (!fromulaBegin) {
				meaningBegin = true;
			}
		}

		if (meaningBegin && !meaningExisted) {
			output += "~";
			meaningExisted = true;
		}
		if (fromulaBegin) {
			output += inStr[i];
		}
	}
	return output;
}

//关闭单词修改窗口
function closeModifyWindow() {
	var eWin = document.getElementById("modifywin");
	if (eWin) {
		eWin.style.display = "none";
		document.getElementById("modifyDiv").appendChild(eWin);
		gCurrModifyWindowParNo = -1;
	} else {
	}
	if (_display_sbs == 1) {
		$("#ws_" + g_currEditWord).removeClass("wbw_selected");
	}
}

//取消对单个词的修改
function modifyCancel() {
	//关闭单词修改窗口
	closeModifyWindow();

	//??????
	getStyleClass("debug_info").style.display = "none";
	document.getElementById("debug").style.display = "-webkit-flex";
	document.getElementById("debug").style.display = "-moz-flex";
	document.getElementById("debug").style.display = "-webkit-flex";
	refreshNoteNumber();
}

//获取某词的段落索引
function getParIndexByWordId(wordId) {
	//遍历所有块，找到这个单词
	var bookId = "";
	var parNo = "";

	var allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		type = getNodeText(xmlParInfo, "type");
		if (type == "wbw") {
			words = xmlParData.getElementsByTagName("word");
			for (var iWord = 0; iWord < words.length; iWord++) {
				wId = getNodeText(words[iWord], "id");
				if (wId == wordId) {
					bookId = getNodeText(xmlParInfo, "book");
					parNo = getNodeText(xmlParInfo, "paragraph");
					break;
				}
			}
		}
	}
	if (bookId == "" || parNo == "") {
		return false;
	} else {
		for (var iPar = 0; iPar < gArrayDocParagraph.length; iPar++) {
			currBookId = gArrayDocParagraph[iPar].book;
			currParNo = gArrayDocParagraph[iPar].paragraph;

			if (currBookId == bookId && currParNo == parNo) {
				return iPar;
			}
		}
	}

	return false;
}
var mouse_action_edit = true;
var mouse_action_lookup = true;
var mouse_action_translate = false;
function lock_key(obj, key, check_id, svg_id) {
	var lock_key_str = "";
	switch (key) {
		case "off":
			lock_key_str += "<input id='" + check_id + '\' type="checkbox" style="display:none; " />';
			lock_key_str +=
				'<svg class="icon" onclick=lock_key(\'' + obj + "','on','" + check_id + "','" + svg_id + "')>";
			lock_key_str += '<use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_' + svg_id + '_off">';
			lock_key_str += "</use></svg>";
			document.getElementById(obj).innerHTML = lock_key_str;
			break;
		case "on":
			lock_key_str += "<input id='" + check_id + '\' type="checkbox" style="display:none; " checked/>';
			lock_key_str +=
				'<svg class="icon" onclick=lock_key(\'' + obj + "','off','" + check_id + "','" + svg_id + "')>";
			lock_key_str += '<use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_' + svg_id + '_on ">';
			lock_key_str += "</use></svg>";
			document.getElementById(obj).innerHTML = lock_key_str;
			break;
	}
}

function set_word_click_action(obj, item) {
	switch (item) {
		case "normal":
			if (document.getElementById(obj).checked == true) {
				document.getElementById(obj).checked = false;
				document.getElementById("icon_" + obj + "_on").style.display = "none";
				document.getElementById("icon_" + obj + "_off").style.display = "block";
			} else {
				document.getElementById(obj).checked = true;
				document.getElementById("icon_" + obj + "_on").style.display = "block";
				document.getElementById("icon_" + obj + "_off").style.display = "none";
			}

			break;
		case "edit":
			if (document.getElementById(obj).checked == true) {
				document.getElementById(obj).checked = false;
				document.getElementById("icon_" + obj + "_on").style.display = "none";
				document.getElementById("icon_" + obj + "_off").style.display = "block";
				mouse_action_edit = document.getElementById(obj).checked;
			} else {
				document.getElementById(obj).checked = true;
				document.getElementById("icon_" + obj + "_on").style.display = "block";
				document.getElementById("icon_" + obj + "_off").style.display = "none";
				document.getElementById("icon_Trans_as_on").style.display = "none";
				document.getElementById("icon_Trans_as_off").style.display = "block";
				document.getElementById("Trans_as").checked = false;
				mouse_action_translate = false;
				mouse_action_edit = document.getElementById(obj).checked;
			}
			break;
		case "lookup":
			if (document.getElementById(obj).checked == true) {
				document.getElementById(obj).checked = false;
				document.getElementById("icon_" + obj + "_on").style.display = "none";
				document.getElementById("icon_" + obj + "_off").style.display = "block";
				mouse_action_lookup = document.getElementById(obj).checked;
			} else {
				document.getElementById(obj).checked = true;
				document.getElementById("icon_" + obj + "_on").style.display = "block";
				document.getElementById("icon_" + obj + "_off").style.display = "none";
				document.getElementById("icon_Trans_as_on").style.display = "none";
				document.getElementById("icon_Trans_as_off").style.display = "block";
				document.getElementById("Trans_as").checked = false;
				mouse_action_translate = false;
				mouse_action_lookup = document.getElementById(obj).checked;
			}
			break;
		case "translate":
			if (document.getElementById(obj).checked == true) {
				document.getElementById(obj).checked = false;
				document.getElementById("icon_" + obj + "_on").style.display = "none";
				document.getElementById("icon_" + obj + "_off").style.display = "block";
				mouse_action_translate = document.getElementById(obj).checked;
			} else {
				document.getElementById(obj).checked = true;
				document.getElementById("icon_" + obj + "_on").style.display = "block";
				document.getElementById("icon_" + obj + "_off").style.display = "none";
				document.getElementById("icon_Look_Up_on").style.display = "none";
				document.getElementById("icon_Look_Up_off").style.display = "block";
				document.getElementById("icon_Edit_Dialog_on").style.display = "none";
				document.getElementById("icon_Edit_Dialog_off").style.display = "block";
				document.getElementById("Edit_Dialog").checked = false;
				document.getElementById("Look_Up").checked = false;
				mouse_action_edit = false;
				mouse_action_lookup = false;
				mouse_action_translate = document.getElementById(obj).checked;
			}
			break;
	}
}

//鼠标点击词头
function on_word_click(sWordId) {
	closeModifyWindow();
	g_currEditWord = sWordId;
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	var wid = getWordIndex(sWordId);
	g_eCurrWord = xAllWord[wid];
	var sReal = getNodeText(xAllWord[wid], "real");
	var sParent = getNodeText(xAllWord[wid], "parent");
	var sMeaning = getNodeText(xAllWord[wid], "mean");

	//显示修改单个词的窗口
	if (mouse_action_edit) {
		showModifyWin(sWordId);
	}

	//word Message
	msg_show_content(1, sWordId);
	msg_show_content_panal();

	//术语
	note_lookup(sParent, "term_dict");

	//参考字典
	if (mouse_action_lookup) {
		//dict_search(sReal);
		window.open("../dict/index.php?builtin=true&theme=dark&key="+sReal,target="dict");

	}

	//添加词到翻译框
	//if(mouse_action_translate){
	//	add_word_to_tran_win(sMeaning);
	//}
}

function note_apply(guid) {}
/*
function apply_button_lock(){

	if($("#input_lock")[0].checked){// || g_currBookMarkColor!="bmc0"
		$("#apply_to_down")[0].disabled=true;
		$("#apply_to_up")[0].disabled=true;
		$("#apply_to_all")[0].disabled=true;

	}
	else{
		$("#apply_to_down")[0].disabled=false;
		$("#apply_to_up")[0].disabled=false;
		$("#apply_to_all")[0].disabled=false;
	}

}
 */

function mdf_win_data_change(key, value) {
	$("#" + key).val(value);
}
function mdf_win_part_change(strPart) {
	$("#input_org").val(strPart);
	input_org_change();
}
function mdf_win_case_change(strCase) {
	let aCase = strCase.split("#");
	let type = "";
	let gramma = "";
	if (aCase[0]) {
		type = aCase[0];
	}
	if (aCase[0]) {
		type = aCase[0];
	}
	if (aCase[1]) {
		gramma = aCase[1];
	}
	$("#input_case").val(strCase);
	let arrGramma = gramma.split("$");
	g_caseSelect[0] = type;
	if (arrGramma[0]) {
		g_caseSelect[1] = arrGramma[0];
	} else {
		g_caseSelect[1] = "";
	}
	if (arrGramma[1]) {
		g_caseSelect[2] = arrGramma[1];
	} else {
		g_caseSelect[2] = "";
	}
	if (arrGramma[2]) {
		g_caseSelect[3] = arrGramma[2];
	} else {
		g_caseSelect[3] = "";
	}
	refreshCaseSelect();
}
//显示修改单个词的窗口
function showModifyWin(sWordId) {
	//获取当前编辑的单词所在的段的索引号
	gCurrModifyWindowParNo = getParIndexByWordId(sWordId);

	let xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	let wid = getWordIndex(sWordId);

	let tWin = "";
	let tApply = "";
	let eWord = document.getElementById("wb" + sWordId);
	let eWin = document.getElementById("modifywin");
	let eWordInfo = document.getElementById("modify_detaile");
	let eApply = document.getElementById("modify_apply");

	let sReal = getNodeText(xAllWord[wid], "real");
	let sParent = getNodeText(xAllWord[wid], "parent");
	let sMeaning = getNodeText(xAllWord[wid], "mean");
	let sOrg = getNodeText(xAllWord[wid], "org");
	let sOm = getNodeText(xAllWord[wid], "om");
	let sCase = getNodeText(xAllWord[wid], "case");
	let sParentGrammar = getNodeText(xAllWord[wid], "pg");
	let sParent2 = getNodeText(xAllWord[wid], "parent2");

	//showCurrWordTable(sReal);

	if (g_useMode == "edit") {
		//初始值
		$("#input_meaning").val(sMeaning);
		$("#input_org").val(sOrg);
		$("#input_om").val(sOm);
		$("#input_case").val(sCase);
		$("#input_parent_grammar").val(sParentGrammar);
		$("#id_text_prt_prt").val(sParent2);
		
		if (sParentGrammar != "" || sParent2 != "" || sParent2 != " ") {
			document.getElementById("edit_detail_prt_prt").style.display = "block";
			document.getElementById("svg_parent2").style.transform = "rotate(90deg)";
		} else {
			document.getElementById("edit_detail_prt_prt").style.display = "none";
			document.getElementById("svg_parent2").style.transform = "rotate(0deg)";
		}
		document.getElementById("parent_grammar").innerHTML = getLocalGrammaStr(sParentGrammar);


		//右侧修改菜单
		$("#word_mdf_mean_dropdown").html(render_word_menu_mean(g_currEditWord, 1));
		$("#word_mdf_parts_dropdown").html(render_word_menu_parts(sWordId, 1));
		$("#word_mdf_case_dropdown").html(render_word_menu_gramma(sWordId, 1));
		$("#word_mdf_parent_dropdown").html(render_word_menu_parent(sWordId));
		$("#word_mdf_prt_prt_dropdown").html(render_word_menu_parent_parent(sWordId));

		let typeAndGramma = sCase.split("#");
		if (typeAndGramma.length > 1) {
			sType = typeAndGramma[0];
			sGramma = typeAndGramma[1];
		} else {
			sType = "";
			sGramma = typeAndGramma[0];
		}
		g_caseSelect[0] = sType;
		aGramma = sGramma.split("$");
		lenGramma = aGramma.length;
		if (lenGramma > 3) {
			lenGramma = 3;
		}
		for (iGramma = 0; iGramma < lenGramma; iGramma++) {
			g_caseSelect[iGramma + 1] = aGramma[iGramma];
		}
		//刷新type and gramma 下拉菜单选项
		refreshCaseSelect();

		//刷新part meaning 下拉菜单选项
		g_currPartMeaning = "";
		g_initPartMeaning = true;
		refreshPartMeaningSelect();
		if (sOrg != "?" && sOrg != "") {
			input_org_change(document.getElementById("input_org"));
		}

		tApply += '<div class="modifybutton">';
		tApply += "<p style='display: flex' >"; //onclick=apply_button_lock()

		if (getNodeText(xAllWord[wid], "lock") == "true") {
			tApply += "<span class='apply_to' id='edit_lock' align=\"left\">";
			tApply += '<input type="checkbox" style=" display:none;" align="left" id=\'input_lock\' checked />';
			tApply +=
				"<svg class=\"icon\" onclick=lock_key('edit_lock','off','input_lock','lock')><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_lock_on \"></use></svg>";
			tApply += "</span>";
		} else {
			tApply += "<span class='apply_to' id='edit_lock' align=\"left\">";
			tApply += '<input type="checkbox" style=" display:none;" align="left" id=\'input_lock\' />';
			tApply +=
				"<svg class=\"icon\" onclick=lock_key('edit_lock','on','input_lock','lock')><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_lock_off\"></use></svg>";
			tApply += "</span>";
		}
		//tApply += "<button onclick=\"upload_to_my_dict()\">上传到我的字典</button>";

		let allword = doc_word();
		let sameCount = 0;
		for (let i = 0; i < allword.length; i++) {
			if (
				sReal == getNodeText(allword[i], "real") ||
				(sParent != "" && sParent == getNodeText(allword[i], "parent"))
			) {
				sameCount++;
			}
		}

		if (sameCount > 1) {
			tApply += "<span>";
		} else {
			tApply += "<span style='display:none'>";
		}
		tApply +=
			'<input id=\'checkbox_apply_same\' type="checkbox" style=" width:14px; height:14px; margin-left:10px;" align="left"/>' +
			gLocal.gui.applyto +
			"&nbsp;<span id='same_word_count' >" +
			(sameCount + 1) +
			"&nbsp;" +
			gLocal.gui.same_word +
			"</span>";
		tApply += "</span>";

		tApply += "<span class='apply_to' id='upload_lock' align=\"left\">";
		tApply +=
			'<input type="checkbox" style="display:none; width:14px; height:14px; margin-left:10px;" align="left" id=\'input_to_db\' />';
		//tApply += "<svg class=\"icon\" onclick=lock_key('upload_lock','on','input_to_db','cloud')><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_cloud_off\"></span>";
		tApply += "</span>";

		tApply += "</p>";

		tApply += "</div>";
		tApply += '<div class="modifycancel" align="right">';
		/*		
		tApply += "<span class='apply_to'>" 
		tApply+= "<svg class=\"icon\"><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_format_paint\">";
		tApply += "</span>";
		

		tApply+= "<button class='apply_to' id='apply_to_down' onclick=\"modifyApply('" + sWordId + "','down')\" title='向下填充'>";
		tApply+= "<svg style='transform: rotate(180deg)' class=\"icon\"><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_file_upload\">";
		tApply+= "</button>";
		
		tApply+= "<button class=' apply_to' id='apply_to_up' onclick=\"modifyApply('" + sWordId + "','up')\"  title='向上填充'>";
		tApply+= "<svg class=\"icon\"><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_file_upload\">";
		tApply+= "</button>";
		
		tApply+= "<button class=' apply_to' id='apply_to_all' onclick=\"modifyApply('" + sWordId + "','all')\"  title='更新全部'>";
		tApply+= "<svg class=\"icon\"><use xlink=\"http://www.w3.org/1999/xlink\" href=\"svg/icon.svg#ic_format_line_spacing\">";
		tApply+= "</button>";
		*/
		tApply +=
			"<button class=' apply_to' id='apply_to_this' onclick=\"modifyApply('" +
			sWordId +
			"',true)\"  title='Save and Favorite'>";
		tApply += "💾&🌐"+gLocal.gui.to_user_dictionary;
		tApply += "</button>";

		tApply +=
			"<button class=' apply_to' id='apply_to_this' onclick=\"modifyApply('" +
			sWordId +
			"',false)\"  title='Save Draft'>";
		tApply += "💾";//gLocal.gui.save
		//tApply += '<svg class="icon" style="fill: var(--box-bg-color1)"><use xlink:href="../../node_modules/bootstrap-icons/bootstrap-icons.svg#translate"></use></svg>'
		tApply += "</button>";

		tApply += "<button class=' apply_to' onclick=\"modifyCancel()\">";
		tApply += "❌";//gLocal.gui.cancel+
		tApply += "</button>";
		tApply += "</div>";
		eApply.innerHTML = tApply;

		document.getElementById("id_text_bookmark").value = getNodeText(xAllWord[wid], "bmt");
		document.getElementById("id_text_note").value = getNodeText(xAllWord[wid], "note");
		document.getElementById("id_text_id").innerHTML = get_book_name_by_id(getNodeText(xAllWord[wid], "id"));
		document.getElementById("id_text_pali").value = getNodeText(xAllWord[wid], "pali");
		document.getElementById("id_text_real").value = getNodeText(xAllWord[wid], "real");
		document.getElementById("id_text_parent").value = getNodeText(xAllWord[wid], "parent");

		$("#id_relation_text").val(getNodeText(xAllWord[wid], "rela"));
		rela_refresh(sWordId);

		if (getNodeText(xAllWord[wid], "bmc") == "") {
			g_currBookMarkColor = "bmc0";
		} else {
			g_currBookMarkColor = getNodeText(xAllWord[wid], "bmc");
		}
		getBookMarkColor(g_currBookMarkColor);

		//显示编辑窗口
		eWin.style.display = "block";
		if (_display_sbs == 1) {
			$("#modifywin").addClass("left_edit_frame");
			//根据偏移量设置窗口位置
			if (
				$(".sent_wbw").outerWidth() +
					$("#left_tool_bar").outerWidth() -
					$("#wb" + sWordId).offset().left -
					$("#modifywin").outerWidth() >
				0
			) {
				//$("#modifywin").removeClass("right_edit_frame")
				$("#modifywin").css("margin-left", "0");

				//$("#modifywin").style();
				//$("#modifywin").css("margin-left", "8px");
				//$("#modifywin::after").css("left", "0");
				//$("#modifywin::after").style.left = "0";, "": "" }
			} else {
				let margin_change =
					$(".sent_wbw").outerWidth() +
					$("#left_tool_bar").outerWidth() -
					$("#wb" + sWordId).offset().left -
					$("#modifywin").outerWidth();
				//$("#modifywin").removeClass("left_edit_frame")
				//$("#modifywin").addClass("right_edit_frame")
				$("#modifywin").css("margin-left", margin_change + "px");
				//$("#modifywin::after").css("right", "0");
				//$("#modifywin::after").style.right = "0";
			}
			$("#ws_" + sWordId).addClass("wbw_selected");
		}

		var sDetail = "detail" + sWordId;
		var eDetail = document.getElementById(sDetail);
		eWord.insertBefore(eWin, eDetail);

		//document.getElementById("dict_ref_search_input").value = sReal;

		//editor_refresh_inline_dict(sReal);
	}
}

function get_book_name_by_id(bookid) {
	var book_id = bookid.split("-")[0];
	var book_id2 = bookid.slice(book_id.length);

	for (i_bookname in local_palicannon_index) {
		if (book_id == local_palicannon_index[i_bookname].id) {
			book_id2 = local_palicannon_index[i_bookname].title + book_id2;
		}
	}
	return book_id2;
}

function add_word_to_tran_win(sMeaning) {
	var tranObj = document.getElementById("id_text_edit_form");
	if (tranObj && tranObj.style.display != "none") {
		var textObj = document.getElementById("id_text_edit_area");
		if (textObj) {
			textObj.value += sMeaning;
		}
	}
}

//编辑窗口拆分改变
var g_arrPartMean = null;
var g_initPartMeaning = true;
var mDict = new Array();
function input_org_change() {
	g_arrPartMean = null;
	g_initPartMeaning = true;

	var arrPart = $("#input_org").val().split("+");
	var arrNewPart = new Array();
	var i;
	for (i in arrPart) {
		if (!mDict[arrPart[i]]) {
			arrNewPart.push(arrPart[i]);
		}
	}
	if (arrNewPart.length > 0) {
		//如果有内存字典里面没有的单词，查询
		$.get(
			"./dict_find_one.php",
			{
				word: arrNewPart.join(),
				type: "part",
			},
			function (data, status) {
				try {
					var worddata = JSON.parse(data);
				} catch (e) {
					console.error(e.error);
				}
				if (worddata.length > 0) {
					var spell = new Array();
					for (var i in worddata) {
						if (mDict[worddata[i].pali]) {
							spell[worddata[i].pali] = 1;
						} else {
							spell[worddata[i].pali] = 0;
						}
					}
					for (var word in spell) {
						if (spell[word] == 0) {
							mDict[word] = new Array();
						}
					}
					for (var i in worddata) {
						if (spell[worddata[i].pali] == 0) {
							mDict[worddata[i].pali].push(worddata[i]);
						}
					}
				} else {
				}
				refreshPartMeaningSelect();
			}
		);
	} else {
		refreshPartMeaningSelect();
	}
}

const db_name = "../../tmp/user/wbw.db3";
//载入我的字典中的各位公式
function load_my_formula() {
	//如果有内存字典里面没有的单词，查询
	console.log("load_my_formula - dict_find_one.php");
	$.get(
		"./dict_find_one.php",
		{
			word: "_formula_",
			dict_name: db_name,
			deep: 1,
		},
		function (data, status) {
			try {
				myFormula = JSON.parse(data);
			} catch (e) {
				console.error(e.error);
			}
		}
	);
}
/*
  |------------------------------------
  |当人工输入拆分意思后，更新拆分意思数组
  |------------------------------------
  |obj : 输入框
  |------------------------------------
*/
function input_om_change(obj){
	g_arrPartMean = obj.value().split('+');
}

/*
  |------------------------------------
  |当选择拆分意思菜单后，更新拆分意思输入框
  |------------------------------------
  | 
  |------------------------------------
*/
function part_mean_ok() {
	var part_mean_ok_str = g_arrPartMean.join("+");
	part_mean_ok_str = "#" + part_mean_ok_str + "#";
	part_mean_ok_str = part_mean_ok_str.replace(/\+ /g, "+");
	part_mean_ok_str = part_mean_ok_str.replace(/ \+/g, "+");
	part_mean_ok_str = part_mean_ok_str.replace(/\# /g, "");
	part_mean_ok_str = part_mean_ok_str.replace(/ \#/g, "");
	part_mean_ok_str = part_mean_ok_str.replace(/\#/g, "");
	document.getElementById("input_om").value = part_mean_ok_str; //.slice(0,-1);
}

/*
编辑窗口中拆分下拉菜单改变
*/
function meaningPartChange(index, newValue) {
	g_initPartMeaning = false;

	g_arrPartMean[index] = newValue;
	part_mean_ok();

	refreshPartMeaningSelect();
}
function input_org_switch(id_1, id_2) {
	document.getElementById(id_1).style.display = "none";
	document.getElementById(id_2).style.display = "inline-flex";
	document.getElementById(id_2).focus();
	refreshPartMeaningSelect();
}
function refreshPartMeaningSelect() {
	var part = document.getElementById("input_org").value;
	var arrPart = new Array();
	if (part == "" || part.lastIndexOf("+") == -1) {
		arrPart.push(part);
	} else {
		arrPart = part.split("+");
	}
	if (g_initPartMeaning) {
		g_arrPartMean = part.split("+");
	}
	var output = "<div id='om_dropdown_area' style='overflow-x: auto;white-space: nowrap;max-width: 13em;'>";
	//output="<span style='width:90%' onclick=\"input_org_switch('input_org_select','input_om')\"></span><br/>"
	for (iPart in arrPart) {
		output += getMeaningMenuList(iPart, arrPart[iPart]);
		if (arrPart.length == 1) {
			break;
		} else if (iPart < arrPart.length - 1) {
			output += "+";
		}
	}
	output += "</div>";
	output += "<div style='width: 5.5em;'>";
	output += "<button style='margin-left:auto; padding: 1px 6px;' onclick=\"copy_part_mean_to_mean()\">";
	output += '<svg class="icon"><use xlink="http://www.w3.org/1999/xlink" href="svg/icon.svg#ic_vertical_align_top">';
	output += "</button>";

	output +=
		"<button style='margin-left:auto; padding: 1px 6px;' onclick=\"input_org_switch('input_org_select','input_om')\">";
	output += "<svg class='icon'><use xlink='http://www.w3.org/1999/xlink' href='svg/icon.svg#ic_mode_edit'>";
	output += "</button>";
	output += "</div>";

	document.getElementById("input_org_select").innerHTML = output;
	//增加拆分意思所见即所得
	var part_mean_display_str = document.getElementById("input_om").value;
	var part_mean_display_array = new Array();
	if (part_mean_display_str.lastIndexOf("+") != -1) {
		part_mean_display_array = part_mean_display_str.split("+");
	} else {
		part_mean_display_array.push(part_mean_display_str);
	}

	if (part_mean_display_array.length <= arrPart.length) {
		for (i_display in part_mean_display_array) {
			document.getElementById("org_part_mean_" + i_display).innerHTML = part_mean_display_array[i_display];
		}
		if (part_mean_display_array.length < arrPart.length) {
			for (i_display = part_mean_display_array.length; i_display < arrPart.length; i_display++) {
				//document.getElementById("org_part_mean_" + i_display).innerHTML = "⬇";
			}
		}
	} else {
		for (i_display in arrPart) {
			if (i_display == arrPart.length - 1) {
				var part_mean_display_str_last = "";
				for (j_display = i_display; j_display < part_mean_display_array.length; j_display++) {
					part_mean_display_str_last += part_mean_display_array[j_display];
				}
				document.getElementById("org_part_mean_" + i_display).innerHTML = part_mean_display_str_last;
			} else {
				document.getElementById("org_part_mean_" + i_display).innerHTML = part_mean_display_array[i_display];
			}
		}
	}
}
//编辑窗口拆分意思复制到整体意思
function copy_part_mean_to_mean() {
	let meaning = g_arrPartMean.join("");
	if(testCJK(meaning)){
		$("#input_meaning").val(removeFormulaB(g_arrPartMean.join(""), "[", "]"));
	}else{
		$("#input_meaning").val(removeFormulaB(g_arrPartMean.join(" "), "[", "]"));
	}
	
}
//编辑窗口拆分意思下拉菜单
function getMeaningMenuList(index, word) {
	var currMeaningList0 = getWordMeaningList(word);
	var currMeaningList = new Array();
	var part_mean_display_str = document.getElementById("input_om").value;
	var part_mean_display_array = new Array();
	if (part_mean_display_str.lastIndexOf("+") != -1) {
		part_mean_display_array = part_mean_display_str.split("+");
	} else {
		part_mean_display_array.push(part_mean_display_str);
	}
	if (part_mean_display_array.length - 1 >= index) {
		currMeaningList.push(part_mean_display_array[index]);
	} else {
		currMeaningList.push(" ");
	}
	for (const iterator of currMeaningList0) {
		if (iterator != "") {
			currMeaningList.push(iterator);
		}
	}

	var output = "";
	output += "<div class=\"case_dropdown\" style='display:inline-block;'>";

	let currMean;
	output += "<p id='org_part_mean_" + index + "' class='case_dropbtn' >";
	if (g_initPartMeaning) {
		currMean = currMeaningList[0];
		g_arrPartMean[index] = currMeaningList[0];
	} else {
		currMean = g_arrPartMean[index];
	}
	if (currMean == "") {
		currMean = " ";
	}
	output += currMean + "</p>";

	output += '<div class="case_dropdown-content" id=\'part_mean_menu_' + index + "' style='left:0;right:0;'>";
	//直列菜单
	output += "<a onclick='meaningPartLookup(\"" +word +"\")'>🔍" +gLocal.gui.dict +"</a>";	
	for (const itMean of currMeaningList) {
		if(itMean!="?"){
			output += "<a onclick='meaningPartChange(" +index +	',"' +itMean +	"\")'>" +itMean +"</a>";					
		}

	}

	output += "</div>";
	output += "</div>";
	return output;
}
function meaningPartLookup(word){
	window.open("../dict/index.php?builtin=true&theme=dark&key="+word,target="dict");
}
function getWordMeaningList(word) {
	var currOM = "";
	var arrOM = new Array();
	var thisWord = word;
	//新的方法 内存字典用索引数组
	if (mDict[word]) {
		for (var j = 0; j < mDict[word].length; j++) {
			if (mDict[word][j].mean) {
				var currOM = mDict[word][j].mean.split("$");
				for (iMean in currOM) {
					if (currOM[iMean].length > 0 && currOM[iMean] != "?") {
						pushNewToList(arrOM, currOM[iMean]);
					}
				}
			}
		}
	}

	if (arrOM.length == 0) {
		arrOM.push("?");
	}
	return arrOM;
}

//删除连音词
function edit_un_remove(parentId) {
	edit_un_RemoveHtmlNode(parentId);

	let xWord = gXmlBookDataBody.getElementsByTagName("word");
	var count = 0;
	//移除内存数据

	for (iWord = xWord.length - 1; iWord >= 0; iWord--) {
		let id = getNodeText(xWord[iWord], "id");
		if (id.indexOf(parentId) == 0 && id != parentId) {
			xWord[iWord].parentNode.removeChild(xWord[iWord]);
			count++;
		}
	}
	//移除根单词的类标志
	var parentElement = document.getElementById("wb" + parentId);
	if (parentElement) {
		parentElement.classList.remove("un_parent");
		parentElement.classList.remove("comp_parent");
	}

	user_wbw_push_word(parentId);
	user_wbw_commit();
	com_XmlAllWordRefresh();
	modifyWordDetailByWordId(parentId);
	var_dump(gLocal.gui.removeword + ": " + count);
}

function edit_un_RemoveHtmlNode(parentId) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	for (wordIndex = 0; wordIndex < xAllWord.length; wordIndex++) {
		if (getNodeText(xAllWord[wordIndex], "un") == parentId) {
			var childId = getNodeText(xAllWord[wordIndex], "id");
			var element = document.getElementById("wb" + childId);
			element.parentNode.removeChild(element);
		}
	}
}

/*
拆连读词
*/
function edit_un_split(parentId) {
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	var iWordCount = 0;
	for (iBlock = 0; iBlock < xBlock.length; iBlock++) {
		var xData = xBlock[iBlock].getElementsByTagName("data")[0];
		xWord = xData.getElementsByTagName("word");
		for (iWord = 0; iWord < xWord.length; iWord++) {
			if (getNodeText(xWord[iWord], "id") == parentId) {
				mType = getNodeText(xWord[iWord], "case").split("#")[0];
				if (mType == ".un." || mType == ".comp.") {
					nextElement = com_get_nextsibling(xWord[iWord]);
					if (nextElement != null) {
						//下一个元素存在
						if (getNodeText(nextElement, "un") == parentId) {
							//若有孩子則不进行任何处理，直接返回
							return;
						}
					}
				} else {
					//若不是连读词則不进行任何处理，直接返回
					return;
				}
				if (getNodeText(xWord[iWord], "mean") == "?") {
					setNodeText(xWord[iWord], "mean", "_un_auto_mean_");
				}
				if (getNodeText(xWord[iWord], "om") == "?") {
					setNodeText(xWord[iWord], "om", "_un_auto_factormean_");
				}

				var parentElement = document.getElementById("wb" + parentId);
				if (parentElement) {
					if (mType == ".un.") {
						parentElement.classList.add("un_parent");
					} else {
						parentElement.classList.add("comp_parent");
					}
				}
				var nextWordNodeId = getNodeText(xWord[iWord + 1], "id");
				if (mType == ".un.") {
					var org = "[+" + getNodeText(xWord[iWord], "org") + "+]";
				} else {
					var org = getNodeText(xWord[iWord], "org").replace(/\+/g, "+-+");
				}
				var sSubPali = org.split("+");
				var orgReal = org.replace(/n\+/g, "ṃ+"); //智能識別結尾為n的拆分
				orgReal = orgReal.replace(/ñ\+/g, "ṃ+"); //智能識別結尾為n的拆分
				orgReal = orgReal.replace(/m\+/g, "ṃ+"); //智能識別結尾為n的拆分
				orgReal = orgReal.replace(/d\+/g, "ṃ+"); //智能識別結尾為n的拆分
				var sSubReal = orgReal.split("+");
				for (iNewWord = 0; iNewWord < sSubPali.length; iNewWord++) {
					var newNode = gXmlBookData.createElement("word");
					setNodeText(newNode, "pali", sSubPali[iNewWord]);
					var newGUID = parentId + "-" + iNewWord;
					setNodeText(newNode, "id", newGUID);
					setNodeText(newNode, "un", parentId);
					let newPali = sSubReal[iNewWord].toLowerCase();
					if (sSubPali[iNewWord] == "[") {
						setNodeText(newNode, "real", "");
						setNodeText(newNode, "case", ".un:begin.");
					} else if (sSubPali[iNewWord] == "]") {
						setNodeText(newNode, "real", "");
						setNodeText(newNode, "case", ".un:end.");
					} else {
						setNodeText(newNode, "real", newPali); //real转换为小写
						setNodeText(newNode, "case", "?");
					}
					let newMeaning = findFirstMeanInDict(newPali);
					let newParts = findFirstPartInDict(newPali);
					let newPartMean = findFirstPartMeanInDict(newPali);
					let newCase = findFirstCaseInDict(newPali);

					setNodeText(newNode, "mean", newMeaning);
					setNodeText(newNode, "org", newParts);
					setNodeText(newNode, "om", newPartMean);
					setNodeText(newNode, "case", newCase);

					if (iWord == xWord.length - 1) {
						xData.insertBefore(newNode, null);
						edit_un_AddNewHtmlNode(nextWordNodeId, sSubPali[iNewWord], newGUID, iWordCount + iNewWord + 1);
					} else {
						xData.insertBefore(newNode, xWord[iWord + iNewWord + 1]);
						edit_un_AddNewHtmlNode(nextWordNodeId, sSubPali[iNewWord], newGUID, iWordCount + iNewWord + 1);
					}
				}
				modifyWordDetailByWordId(parentId);
				word_mouse_event(); //添加鼠标事件，每次都执行效率低以后需要修改
				var_dump(gLocal.gui.unsplit + " " + gLocal.gui.ok);
				user_wbw_push_word(parentId);
				user_wbw_commit();
				return;
			}
			iWordCount++;
		}
	}
}
//添加连音词拆分html块
function edit_un_AddNewHtmlNode(nextNodeId, strInsert, guid, id) {
	let xWord = gXmlBookDataBody.getElementsByTagName("word");
	let iWordIndex = getWordIndex(guid);
	let parentId = guid.split("_")[0];
	let parentElement = document.getElementById("wb" + parentId);
	let element = document.getElementById("wb" + nextNodeId);

	let htmlNewWord = renderWordBlock(xWord[iWordIndex]);
	if (element) {
		//下一个词存在。
		$(element).before(htmlNewWord);
	} else {
		//下一个词不存在。
		$(parentElement).append(htmlNewWord);
	}
}

function file_export_html_validate_form(thisform) {
	with (thisform) {
		var tocstring = document.getElementById("content").innerHTML;
		var suttastring = document.getElementById("sutta_text").innerHTML;
		txt_toc.value = tocstring.replace(/onclick/g, "");
		txt_sutta.value = suttastring.replace(/onclick/g, "");
		return true;
	}
}

function show_case_input(obj) {
	if (obj.checked) {
		document.getElementById("input_case").style.display = "block";
	} else {
		document.getElementById("input_case").style.display = "none";
	}
}

function edit_tran_save() {
	let eBlock;
	switch (gTextEditMediaType) {
		case "translate":
			newText = document.getElementById("id_text_edit_area").value;
			newText = newText.replace(/\n\n/g, "<br />");
			newText = term_edit_to_std_str(newText);
			setTranText(gEditorTranslateEditBlockId, newText);

			eBlock = document.getElementById("id_tran_" + gEditorTranslateEditBlockId);
			if (eBlock) {
				eBlock.innerHTML = renderTranslateParBlockInnerById(gEditorTranslateEditBlockId);
				term_updata_translation();
			}
			break;
		case "note":
			setNoteText(gEditorNoteEditBlockId, document.getElementById("id_text_edit_area").value);
			newText = document.getElementById("id_text_edit_area").value;
			newText = newText.replace(/\n/g, "<br />");
			eBlock = document.getElementById("note_sen_" + gEditorNoteEditBlockId + "_0");
			if (eBlock) {
				eBlock.innerHTML = newText;
			}
			break;
		case "heading":
			var newHeadingInfo = new Object();
			newHeadingInfo.level = document.getElementById("id_heading_edit_level").value;
			newHeadingInfo.language = document.getElementById("id_text_edit_language").value;
			newHeadingInfo.author = document.getElementById("id_text_edit_author").value;
			newHeadingInfo.text = document.getElementById("id_text_edit_area").value;
			setHeadingInfo(gEditorHeadingEditBlockId, newHeadingInfo);
			newText = document.getElementById("id_text_edit_area").value;
			newText = newText.replace(/\n/g, "<br />");
			eBlock = document.getElementById("id_heading_text_" + gEditorHeadingEditBlockId);
			if (eBlock) {
				eBlock.innerHTML = newText;
			}
			updataToc();
			break;
		case "new_heading":
			var newHeadingInfo = new Object();
			newHeadingInfo.book = gEditorNewHeadingBookId;
			newHeadingInfo.paragraph = gEditorNewHeadingPar;
			newHeadingInfo.level = document.getElementById("id_heading_edit_level").value;
			newHeadingInfo.language = document.getElementById("id_text_edit_language").value;
			newHeadingInfo.author = document.getElementById("id_text_edit_author").value;
			newHeadingInfo.text = document.getElementById("id_text_edit_area").value;
			newHeadBlock(newHeadingInfo);
			updataToc();
			break;
	}
	document.getElementById("id_text_edit_form").style.display = "none";
	Dragging(getDraggingDialog).disable();
}
function edit_tran_cancal() {
	document.getElementById("id_text_edit_form").style.display = "none";
	Dragging(getDraggingDialog).disable();
}

function edit_tran_delete() {
	switch (gTextEditMediaType) {
		case "heading":
			xBlock = gXmlBookDataBody.getElementsByTagName("block");
			for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
				xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
				xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
				mId = getNodeText(xmlParInfo, "id");
				type = getNodeText(xmlParInfo, "type");

				if (mId == gEditorHeadingEditBlockId) {
					gXmlBookDataBody.removeChild(xBlock[iBlock]);
					return;
				}
			}
			htmlNode = document.getElementById("id_heading_" + gEditorHeadingEditBlockId);
			if (htmlNode) {
				htmlNode.parentNode.removeChild(htmlNode);
			}
			updataToc();
			break;
	}

	document.getElementById("id_text_edit_form").style.display = "none";
}

function editor_translate_edit(id) {
	gTextEditMediaType = "translate";
	gEditorTranslateEditBlockId = id;
	var headingObj = getTranslateText(id);
	document.getElementById("id_text_edit_language").value = headingObj.language;
	document.getElementById("id_text_edit_author").value = headingObj.author;
	document.getElementById("id_text_edit_area").value = term_std_str_to_edit(headingObj.text);
	//document.getElementById("id_heading_edit_level").style.display="none";
	document.getElementById("id_text_edit_delete").style.display = "none";
	document.getElementById("id_text_edit_form").style.display = "block";
	Dragging(getDraggingDialog).enable();
}

function editor_note_edit(id) {
	gTextEditMediaType = "note";
	gEditorNoteEditBlockId = id;
	var tranText = getNoteText(id);
	document.getElementById("id_text_edit_area").value = tranText;
	document.getElementById("id_heading_edit_level").style.display = "none";
	document.getElementById("id_text_edit_delete").style.display = "none";
	document.getElementById("id_text_edit_form").style.display = "block";
}
function getNoteText(id) {
	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		mId = getNodeText(xmlParInfo, "id");
		type = getNodeText(xmlParInfo, "type");

		if (mId == id) {
			xmlParDataSen = xmlParData.getElementsByTagName("sen");
			var currText = "";
			for (iSen = 0; iSen < xmlParDataSen.length; iSen++) {
				currText += getNodeText(xmlParDataSen[iSen], "text");
			}
			return currText;
		}
	}
	return "";
}

function editor_heading_add_new(inBook, inPar) {
	document.getElementById("id_text_edit_caption_text").innerHTML = gLocal.gui.newheading;
	gTextEditMediaType = "new_heading";
	gEditorHeadingEditBlockId = -1;
	gEditorNewHeadingBookId = inBook;
	gEditorNewHeadingPar = inPar;
	document.getElementById("id_heading_edit_level").value = "1";
	document.getElementById("id_text_edit_language").value = "pali";
	document.getElementById("id_text_edit_author").value = config_user_name;
	document.getElementById("id_text_edit_area").value = "";
	document.getElementById("id_heading_edit_level").style.display = "flex";
	document.getElementById("id_text_edit_delete").style.display = "none";
	document.getElementById("id_text_edit_form").style.display = "block";
}

function editor_heading_edit(id) {
	document.getElementById("id_text_edit_caption_text").innerHTML = "Heading";
	gTextEditMediaType = "heading";
	gEditorHeadingEditBlockId = id;
	var headingObj = getHeadingText(id);
	document.getElementById("id_heading_edit_level").value = headingObj.level;
	document.getElementById("id_text_edit_language").value = headingObj.language;
	document.getElementById("id_text_edit_author").value = headingObj.author;
	document.getElementById("id_text_edit_area").value = headingObj.text;
	document.getElementById("id_heading_edit_level").style.display = "flex";
	document.getElementById("id_text_edit_delete").style.display = "inline";
	document.getElementById("id_text_edit_form").style.display = "block";
}
function getHeadingText(id) {
	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		mId = getNodeText(xmlParInfo, "id");
		type = getNodeText(xmlParInfo, "type");

		if (mId == id) {
			var obj = new Object();
			obj.text = getNodeText(xmlParData, "text");
			obj.level = getNodeText(xmlParInfo, "level");
			obj.language = getNodeText(xmlParInfo, "language");
			obj.author = getNodeText(xmlParInfo, "author");
			return obj;
		}
	}
	return null;
}
function setHeadingInfo(id, objValue) {
	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		blockId = getNodeText(xmlParInfo, "id");
		if (blockId == id) {
			newText = objValue.text.replace(/\n/g, "<br />");
			setNodeText(xmlParData, "text", newText);
			setNodeText(xmlParInfo, "level", objValue.level);
			setNodeText(xmlParInfo, "author", objValue.author);
			setNodeText(xmlParInfo, "language", objValue.language);
			return;
		}
	}
}

function editor_openChannal(book, para, channal) {
	$.post(
		"../doc/load_channal_para.php",
		{
			book: book,
			para: para,
			channal: channal,
		},
		function (data) {
			editor_parse_doc_xml(data);
		}
	);
}

function render_channel_info(channel_id){
	fetch('/api/v2/channel/'+channel_id,{
        method: 'GET',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        }
    })
  .then(response => response.json())
  .then(function(data){
      console.log(data);
		let result = data.data;
		if(data.ok==true){
			$("#editor_doc_title").html("/" + data.data.owner_info.nickname + "/" + data.data.name);
		}
  });
}
//open project begin
var editor_openProjectXmlHttp = null;
function editor_openProject(strFileId, filetype) {
	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		editor_openProjectXmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		editor_openProjectXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (editor_openProjectXmlHttp != null) {
		var d = new Date();
		var strLink = "";
		if (filetype == "db") {
			strLink = "./project_load_db.php?id=" + strFileId;
		} else {
			strLink = "./project_load.php?id=" + strFileId;
		}
		editor_openProjectXmlHttp.onreadystatechange = editor_open_project_serverResponse;
		editor_openProjectXmlHttp.open("GET", strLink, true);
		editor_openProjectXmlHttp.send(null);
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function editor_parse_doc_xml(xmlText) {
	if (window.DOMParser) {
		parser = new DOMParser();
		gXmlBookData = parser.parseFromString(xmlText, "text/xml");
	} else {
		// Internet Explorer

		gXmlBookData = new ActiveXObject("Microsoft.XMLDOM");
		gXmlBookData.async = "false";
		gXmlBookData.loadXML(xmlText);
	}

	if (gXmlBookData == null) {
		alert("error:can not load Project. xml obj is null.");
		return;
	}

	projectDataParse(gXmlBookData);
	doc_file_info_get();
	doc_info_change("accese_time", "");
	//消息系统初始化
	let msg_id = doc_head("msg_db_max_id");
	if (msg_id != "" && !isNaN(msg_id)) {
		msg_init(msg_id);
	} else {
		msg_init(1);
	}
	updataDocParagraphList();
	updataToc();
	//渲染数据块
	blockShow(0);
	//refreshResource();
	editro_layout_loadStyle();
}

function editor_open_project_serverResponse() {
	// 4 = "loaded"
	if (editor_openProjectXmlHttp.readyState == 4) {
		if (editor_openProjectXmlHttp.status == 200) {
			// 200 = "OK"
			var xmlText = editor_openProjectXmlHttp.responseText;
			editor_parse_doc_xml(xmlText);
		} else {
			$("#sutta_text").html("Problem retrieving data:" + editor_openProjectXmlHttp.statusText);
		}
	}
}

//数据块显示
function blockShow(id) {
	xmlBlock = gXmlBookDataBody.getElementsByTagName("block");
	if (id < xmlBlock.length) {
		insertBlockToHtml(xmlBlock[id]);
		t = setTimeout("blockShow(" + (id + 1) + ")", 1);
		progress = id / xmlBlock.length; //计算比例
		strProgress = (progress * 100).toFixed(0) + "%"; //计算百分比，保留1位小数
		document.getElementById("load_progress_num").innerHTML = strProgress; //显示计算结果

		loading.setAttribute("stroke-dashoffset", 255 - progress * 140 + "%");
	} else {
		//文档载入完毕
		strProgress = "OK";
		loading.setAttribute("stroke-dashoffset", "0%");

		document.getElementById("load_progress_num").innerHTML = strProgress;

		setTimeout("hiddenProgressDiv()", 1000);

		term_array_updata();
		//单词块响应鼠标消息
		word_mouse_event();

		//刷新译文中的术语
		term_updata_translation();

		//自动将逐词译段落切分为句子
		layout_wbw_auto_cut();
	}
}

function word_mouse_event() {
	$(".word").mouseenter(on_word_mouse_enter);
	$(".word").mouseleave(on_word_mouse_leave);
}
var _curr_mouse_enter_wordid = "";
//单词快鼠标退出
function on_word_mouse_leave() {
	$("#mean_" + _curr_mouse_enter_wordid).html("loading");
	$("#parts_" + _curr_mouse_enter_wordid).html("loading");
	$("#partmean_" + _curr_mouse_enter_wordid).html("loading");
	$("#gramma_" + _curr_mouse_enter_wordid).html("loading");
	relation_link_hide();
	if (gRelationSelectWordBegin) {
		//$(this).css(gCurrWordDivBorder);
		$(this).css("border", "none");
	}
	gCurrMoseEnterWordId = "";
}

//单词块鼠标进入事件
var gCurrLookupWord = "";
//save the broder style when mouse leave recover
var gCurrWordDivBorder = "none";
var gWordHeadBarVisible = false;
var gCurrMoseEnterWordId = "";
function on_word_mouse_enter() {
	var wordid = $(this).attr("id");

	if (gCurrMoseEnterWordId == wordid) {
		return;
	}
	gCurrMoseEnterWordId = wordid;

	//remove the 'wb' in string head
	_curr_mouse_enter_wordid = wordid.substr(2);

	relation_link_show(_curr_mouse_enter_wordid);

	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	var iIndex = getWordIndex(_curr_mouse_enter_wordid);
	if (iIndex >= 0) {
		var paliword = getNodeText(xAllWord[iIndex], "real");
		//如果内存里有这个词，渲染单词下拉菜单
		if (mDict[paliword]) {
			render_word_menu(_curr_mouse_enter_wordid);
		} else {
			//如果内存里没有这个词，查字典
			if (!mDictQueue[paliword]) {
				if (gCurrLookupWord != paliword) {
					mDictQueue[paliword] = 1;
					gCurrLookupWord = paliword;
					$.ajax({
						type: "GET",
						url: "./dict_find_one.php",
						dataType: "json",
						data: "word=" + paliword,
					}).done(function (data) {
						inline_dict_parse(data);
						render_word_menu(_curr_mouse_enter_wordid);
					}).fail(function(jqXHR, textStatus, errorThrown){
						ntf_show(textStatus);
						switch (textStatus) {
							case "timeout":
								break;
							case "error":
								switch (jqXHR.status) {
									case 404:
										break;
									case 500:
										break;				
									default:
										break;
								}
								break;
							case "abort":
								break;
							case "parsererror":			
								console.log("parsererror",jqXHR.responseText);
								break;
							default:
								break;
						}
						
					});

				}
			}
		}
	}
	//如果显示relation
	if (gRelationSelectWordBegin) {
		gCurrWordDivBorder = $(this).css("border");
		$(this).css("border", "1px solid #65c5bd");
		let eHeadBar = document.getElementById("word_tool_bar");
		if (eHeadBar) {
			eHeadBar.style.display = "block";
		}
		let eWord = document.getElementById("ws_" + _curr_mouse_enter_wordid);
		let eWordHead = document.getElementById("whead_" + _curr_mouse_enter_wordid);
		eWord.insertBefore(eHeadBar, eWordHead);

		gWordHeadBarVisible = true;
	}
}

//解析字典数据
function inline_dict_parse(data) {
	/*
	if (data == "") {
		return;
	}
	
	try {
		var worddata = JSON.parse(data);
	} catch (e) {
		console.error(e + " data:" + data);
		return;
	}
	*/
	let worddata = data;
	if (worddata.length > 0) {
		//如果有数据 解析查询数据
		let spell = new Array();
		for (const iterator of worddata) {
			if (mDict[iterator.pali]) {
				spell[iterator.pali] = 1;
			} else {
				spell[iterator.pali] = 0;
			}
		}
		for (const key in spell) {
			if (spell.hasOwnProperty(key)) {
				const element = spell[key];
				if (element == 0) {
					mDict[key] = new Array();
				}
			}
		}

		for (const iterator of worddata) {
			if (spell[iterator.pali] == 0) {
				mDict[iterator.pali].push(iterator);
				mDictQueue[iterator.pali] = 0;
			}
		}
		let currWordParent = new Array();
		if (mDict[gCurrLookupWord]) {
			for (const iterator of mDict[gCurrLookupWord]) {
				if (typeof iterator.parent == "string") {
					if (iterator.parent.length > 1) {
						currWordParent[iterator.parent] = 1;
					}
				}
			}
			if (currWordParent.length == 0) {
				//
				inline_dict_auto_case(gCurrLookupWord);
			}
		} else {
			//如果没有查到数据  添加自动格位
			mDict[gCurrLookupWord] = new Array();
			inline_dict_auto_case(gCurrLookupWord);
		}
	} else {
		//如果没有查到数据  添加自动格位
		mDict[gCurrLookupWord] = new Array();
		inline_dict_auto_case(gCurrLookupWord);
	}
}
//添加自动格位数据到内存字典
function inline_dict_auto_case(paliword) {
	for (const it of gCaseTable) {
		if (it.type != ".v.") {
			let sEnd2 = gCurrLookupWord.slice(0 - it.end2.length);
			if (sEnd2 == it.end2) {
				let wordParent = gCurrLookupWord.slice(0, 0 - it.end2.length) + it.end1;
				let newWord = new Object();
				newWord.pali = gCurrLookupWord;
				newWord.type = it.type;
				newWord.gramma = it.gramma;
				newWord.parent = wordParent;
				newWord.mean = "";
				newWord.note = "";
				newWord.parts = wordParent + "+[" + it.end2 + "]";
				newWord.partmean = "";
				newWord.confidence = it.confidence;
				mDict[paliword].push(newWord);
			}
		}		
	}
}

function getAutoEnding(pali, base) {
	let ending = Array();
	for (let i in gCaseTable) {
		if (gCaseTable[i].type != ".v.") {
			let sEnd2 = pali.slice(0 - gCaseTable[i].end2.length);
			if (sEnd2 == gCaseTable[i].end2) {
				let wordParent = pali.slice(0, 0 - gCaseTable[i].end2.length) + gCaseTable[i].end1;
				if (base == wordParent) {
					ending[gCaseTable[i].end2] = 1;
				}
			}
		}
	}
	return ending;
}

//查字典结果
function on_dict_lookup(data, status) {
	//解析查询数据
	inline_dict_parse(data);
	render_word_menu(_curr_mouse_enter_wordid);
}

function render_word_menu(id) {
	$("#word_mean").html(render_word_menu_mean(id));
	$("#word_parts").html(render_word_menu_parts(id));
	$("#word_partmean").html(render_word_menu_partmean(id));
	$("#word_gramma").html(render_word_menu_gramma(id));

	show_word_menu_mean(id);
	show_word_menu_parts(id);
	show_word_menu_partmean(id);
	show_word_menu_gramma(id);
}

//根据单词长度排序  短词优先
function sortWordLen(a, b) {
	return a.length - b.length;
}
//渲染单词意思下拉菜单
function render_word_menu_mean(id, target = 0) {
	var output = "";
	var word_real = doc_word("#" + id).val("real");
	var word_parent = doc_word("#" + id).val("parent");
	var arrParent = new Array();
	//检索语基
	if (word_parent.length > 0) {
		//arrParent[word_parent]=1;
	}
	if (mDict[word_real]) {
		for (var i in mDict[word_real]) {
			if (mDict[word_real][i].parent && mDict[word_real][i].parent.length > 0) {
				if (word_parent != mDict[word_real][i].parent && word_real != mDict[word_real][i].parent) {
					arrParent[mDict[word_real][i].parent] = 1;
				}
			}
		}
	}
	var sWord = new Array();
	for (var sParent in arrParent) {
		sWord.push(sParent);
	}
	//按照base长度升序
	sWord.sort(sortWordLen);
	if (word_parent.length > 0) {
		sWord.unshift(word_parent);
	}
	sWord.unshift(word_real);

	output +=
		"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" +
		id +
		'","mean","")\'>' +
		gLocal.gui.empty1 +
		"</button>";
	output += '<div class="case_dropdown-org">';
	for (var iWord in sWord) {
		var pali = sWord[iWord];
		//该词字典数量
		var dict_count = 0;
		if (mDict[pali]) {
			for (iCount in mDict[pali]) {
				if (mDict[pali][iCount].mean && mDict[pali][iCount].mean.length > 0) {
					dict_count++;
				}
			}
		}

		if (pali == word_parent) {
			output += '<div class="case_dropdown-base">';
		} else {
			output += '<div class="case_dropdown-first">';
		}
		output +=
			'<a style="z-index:250; position:absolute; margin-right:2em;" onclick=\'dict_search("' + pali + "\")'>";
		if (pali == word_parent) {
			output += "<b>·" + pali + "·</b>";
		} else {
			output += pali;
		}
		output += "-" + dict_count + "</a>";
		output += '<span style="z-index:220" class="case_dropdown-title" onclick="submenu_show_detail(this)">';
		output += '<svg class="icon" style="fill:var(--main-color)"><use xlink:href="svg/icon.svg#ic_add"></use></svg>';
		output += "</span>";
		output += "<div class=\"case_dropdown-detail\" style='display:block;'>";
		var currWordMean = new Array();

		if (mDict[pali]) {
			for (var i in mDict[pali]) {
				var objMean = new Object();
				objMean.type = "";
				objMean.gramma = "";
				objMean.dict_name = "";
				objMean.mean = "";
				if (mDict[pali][i].type) {
					objMean.type = mDict[pali][i].type;
				}
				if (mDict[pali][i].gramma) {
					objMean.gramma = mDict[pali][i].gramma;
				}
				if (mDict[pali][i].dict_name) {
					objMean.dict_name = mDict[pali][i].dict_name;
				}
				if (mDict[pali][i].mean) {
					objMean.mean = mDict[pali][i].mean;
				}
				if (objMean.mean.length > 0) {
					_mean_push(currWordMean, objMean);
				}
			}
		}
		for (var i in currWordMean) {
			var htmlMean = "";
			var wId = id;
			output += "<a style='display:flex; flex-wrap: wrap;'>";
			output +=
				"<div id='div_dictname_" + wId + "_" + iWord + "_" + i + "' style='margin-right: auto; display:flex;'>";
			output +=
				"<span id='span_dictname_" +
				wId +
				"_" +
				iWord +
				"_" +
				i +
				"' style='height: 1.5em;' class='wm_dictname' >";
			output += getLocalDictname(currWordMean[i].dict_name) + "</span>";
			output += "</div>";
			output +=
				"<div id='div_type_" + wId + "_" + iWord + "_" + i + "' style='margin-left: 0.4em; display:flex'>";
			output +=
				"<span id='span_type_" +
				wId +
				"_" +
				iWord +
				"_" +
				i +
				"' style='height: 1.5em;' class='wm_wordtype'>" +
				getLocalGrammaStr(currWordMean[i].type) +
				"</span>";

			for (var iMean in currWordMean[i].mean) {
				if (currWordMean[i].mean[iMean] != "") {
					if (target == 0) {
						htmlMean +=
							"<span class='wm_one_mean' onclick='fieldListChanged(\"" +
							wId +
							'","mean","' +
							currWordMean[i].mean[iMean] +
							'" ';
						//parent 与意思联动
						if (iWord > 0) {
							htmlMean += ',"' + pali + '"';
						}
						htmlMean += " )'>" + currWordMean[i].mean[iMean] + "</span>";
					} else {
						htmlMean +=
							"<span class='wm_one_mean' onclick='_win_mean_change(\"" +
							currWordMean[i].mean[iMean] +
							"\" )'>" +
							currWordMean[i].mean[iMean] +
							"</span>";
					}
				}
			}
			output += "</div>";
			output += "<div style='width:15em; display:flex; flex-wrap: wrap;'>" + htmlMean + "</div>";
			output += "</a>";
		}

		output += "</div></div>";
	}

	output += "</div>";
	return output;
}

function _win_mean_change(newmean) {
	$("#input_meaning").val(newmean);
}
function _mean_push(arr, obj) {
	var arrMean = obj.mean.split("$");

	var strIndex = obj.dict_name + "-" + obj.type + "-" + obj.gramma;
	if (arr[strIndex] == null) {
		arr[strIndex] = new Object();
		arr[strIndex].dict_name = obj.dict_name;
		arr[strIndex].type = obj.type;
		arr[strIndex].gramma = obj.gramma;
		arr[strIndex].mean = new Array();
	}

	for (var i = 0; i < arrMean.length; i++) {
		var found = false;
		for (var j = 0; j < arr[strIndex].mean.length; j++) {
			if (arr[strIndex].mean[j] == arrMean[i]) {
				found = true;
				break;
			}
		}
		if (!found) {
			arr[strIndex].mean.push(arrMean[i]);
		}
	}
}

function show_word_menu_mean(id) {
	var word_menu_div = document.getElementById("mean_" + id);
	if (word_menu_div) {
		var menu_div = document.getElementById("word_mean");
		if (menu_div) {
			$("#mean_" + id).html($("#word_mean").html());
		}
	}
}

/*
渲染单词拆分下拉菜单
id	单词id
target	默认渲染目标
		0:主编辑窗口下拉菜
		1:编辑窗口下拉菜单

返回值	无
*/
function render_word_menu_parts(id, target = 0) {
	let output = "";
	let wordID = id;
	output += "<div>";
	output +=
		"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" +
		wordID +
		'","org","")\'>' +
		gLocal.gui.empty1 +
		"</button>";
	output +=
		"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='show_word_map(\"" +
		wordID +
		"\")'>" +
		gLocal.gui.wordmap +
		"</button>";
	output += "</div>";
	let pali = doc_word("#" + id).val("real");
	let wParent = doc_word("#" + id).val("parent");
	let wParts = doc_word("#" + id).val("org");
	let arrParts = new Array();
	let arrParent = new Array();
	if (wParent != "") {
		arrParts[wParent] = 1;
	}
	if (target == 1) {
		arrParts[pali] = 1;
	}
	if (mDict[pali]) {
		for (let iWord in mDict[pali]) {
			if (mDict[pali][iWord].parts && mDict[pali][iWord].parts != "") {
				arrParts[mDict[pali][iWord].parts] = 1;
			}
			if (mDict[pali][iWord].parent && mDict[pali][iWord].parent != "") {
				arrParent[mDict[pali][iWord].parent] = 1;
			}
		}
	}
	//加入base拆分
	if (mDict[wParent]) {
		let ending = getAutoEnding(pali, wParent);
		for (let iWord in mDict[wParent]) {
			if (mDict[wParent][iWord].parts && mDict[wParent][iWord].parts != "") {
				arrParts[mDict[wParent][iWord].parts] = 1;
				{
					for (let end in ending) {
						arrParts[mDict[wParent][iWord].parts + "+[" + end + "]"] = 1;
					}
				}
			}
		}
	}

	output += "<div>";
	let outputPart = "";
	for (let sPart in arrParts) {
		if (wParts == sPart) {
			outputPart = "<b>" + sPart + "</b>";
		} else {
			outputPart = sPart;
		}
		if (target == 0) {
			output += "<a onclick='fieldListChanged(\"" + wordID + '","org","' + sPart + "\")'>" + outputPart + "</a>";
		} else {
			output += "<a onclick='mdf_win_part_change(\"" + sPart + "\")'>" + outputPart + "</a>";
		}
	}
	output += "</div>";

	//base parts 信息
	for (let sParent in arrParent) {
		if (mDict[sParent]) {
			let arrParts = new Array();
			for (let iWord in mDict[sParent]) {
				if (mDict[sParent][iWord].parts && mDict[sParent][iWord].parts != "") {
					arrParts[mDict[sParent][iWord].parts] = 1;
				}
			}
			if (arrParts.length > 0) {
				output += '<div class="case_dropdown-org">';
				output += '<div class="case_dropdown-first">';
				output += "<a style='z-index:250; position:absolute; margin-right:2em;'>";
				output += sParent + "</a>";
				output += "<span style='z-index:220' class='case_dropdown-title'>";
				output += gLocal.gui.more + "»</span>";
				output += "</div>";

				output += "<div>";
				for (let sPart in arrParts) {
					output +=
						"<a onclick='fieldListChanged(\"" + wordID + '","org","' + sPart + "\")'>" + sPart + "</a>";
				}
				output += "</div>";

				output += "</div>";
			}
		}
	}
	return output;
}
function show_word_menu_parts(id) {
	var word_parts_div = document.getElementById("parts_" + id);
	if (word_parts_div) {
		var parts_div = document.getElementById("word_parts");
		if (parts_div) {
			//word_menu_div.appendChild(menu_div);
			$("#parts_" + id).html($("#word_parts").html());
		}
	}
}
//渲染单词拆分意思下拉菜单
function render_word_menu_partmean(id) {
	var wordID = id;
	var sHtml = "";
	var pali = doc_word("#" + id).val("real");
	var sOrg = doc_word("#" + id).val("org");
	var listFactorForFactorMean = sOrg.split("+");
	var currDefualtFM = "";
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

	sHtml +=
		"<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='fieldListChanged(\"" +
		wordID +
		'","om","")\'>' +
		gLocal.gui.empty1 +
		"</button>";
	sHtml +=
		"<a onclick='fieldListChanged(\"" +
		wordID +
		'","om","[a]' +
		currDefualtFM +
		"\")'>[" +
		gLocal.gui.auto +
		"]" +
		currDefualtFM +
		"</a>";

	var arrPartMean = new Array();
	if (mDict[pali]) {
		for (var i in mDict[pali]) {
			if (mDict[pali][i].partmean && mDict[pali][i].partmean.length > 0) {
				arrPartMean[mDict[pali][i].partmean] = 1;
			}
		}
	}
	for (var sPM in arrPartMean) {
		sHtml += "<a onclick='fieldListChanged(\"" + wordID + '","om","' + sPM + "\")'>" + sPM + "</a>";
	}
	return sHtml;
}

/*
渲染单词语基下拉菜单
id	单词id


return	无
*/
function render_word_menu_parent(id) {
	let output = "";
	let word_real = doc_word("#" + id).val("real");
	let word_parent = doc_word("#" + id).val("parent");
	let arrParent = new Array();
	//检索语基
	if (word_parent != "") {
		arrParent[word_parent] = 1;
	}
	if (mDict[word_real]) {
		for (let i in mDict[word_real]) {
			if (mDict[word_real][i].parent && mDict[word_real][i].parent.length > 0) {
				arrParent[mDict[word_real][i].parent] = 1;
			}
		}
	}
	let sWord = new Array();
	for (let sParent in arrParent) {
		sWord.push(sParent);
	}
	//按照base长度升序
	sWord.sort(sortWordLen);
	if (!str_in_array(word_real, sWord)) {
		sWord.push(word_real);
	}
	output += "<a onclick=\"ParentLookup('"+$("#id_text_parent").val()+"')\">🔍" +gLocal.gui.dict +"</a>";

	for (var iWord in sWord) {
		var pali = sWord[iWord];
		output += "<a onclick=\"mdf_win_data_change('id_text_parent','" + pali + "')\">";
		if (word_parent == pali) {
			output += "<b>" + pali + "</b>";
		} else {
			output += pali;
		}
		output += "</a>";
	}
	return output;
}
/*
渲染单词语基下拉菜单
id	单词id


return	无
*/
function render_word_menu_parent_parent(id) {
	let output = "";
	let word_parent = doc_word("#" + id).val("parent");
	let word_parent2 = doc_word("#" + id).val("parent2");
	let word_pg = doc_word("#" + id).val("pg");
	let arrParent = new Array();
	//检索语基
	if (word_parent2 != "") {
		//arrParent[word_parent2+"#"+word_pg] = 1;
	}
	if (mDict[word_parent]) {
		for (let i in mDict[word_parent]) {
			if (mDict[word_parent][i].parent && mDict[word_parent][i].parent!=word_parent && mDict[word_parent][i].parent.length > 0) {
				arrParent[mDict[word_parent][i].parent+"#"+mDict[word_parent][i].gramma] = 1;
			}
		}
	}
	let sWord = new Array();
	for (const key in arrParent) {
		if (arrParent.hasOwnProperty.call(arrParent, key)) {
			sWord.push(key);
		}
	}


	if($("#id_text_prt_prt").val()!=""){
		output += "<a onclick=\"ParentLookup('"+$("#id_text_prt_prt").val()+"')\">🔍" +gLocal.gui.dict +"</a>";
	}
	output += "<a onclick=\"parent_parent_changed('','')\">清空</a>";

	for (const it of sWord) {
		let pali = it.split("#");
		if(pali.length<2){
			pali[1]="";
		}
		output += "<a onclick=\"parent_parent_changed('" + pali[0] + "','" + pali[1] + "')\" style='display:flex;justify-content: space-between;'>";
		if (word_parent2 == pali[0]) {
			output += "<b>" + pali[0] + "</b>";
		} else {
			output += "<span>" +pali[0]+ "</span>";
		}
		output += "<span style='background-color: wheat;'>" +pali[1]+ "</span>";
		output += "</a>";		
	}
	for (let iWord in sWord) {

	}
	return output;
}

function parent_parent_changed(spell,grammar){
	mdf_win_data_change('id_text_prt_prt',spell);
	edit_parent_grammar_changed(grammar);
}

function ParentLookup(word){
	window.open("../dict/index.php?builtin=true&theme=dark&key="+word,target="dict");
}
function show_word_menu_partmean(id) {
	var word_partmean_div = document.getElementById("partmean_" + id);
	if (word_partmean_div) {
		var partmean_div = document.getElementById("word_partmean");
		if (partmean_div) {
			//word_menu_div.appendChild(menu_div);
			$("#partmean_" + id).html($("#word_partmean").html());
		}
	}
}

//语法按照信心指数排序
function sortWordConfidence(a, b) {
	return b - a;
}
/*渲染语法菜单
//@param 
	target 
		0:主窗口
		1:编辑窗口
			
*/
function render_word_menu_gramma(id, target = 0) {
	var wordID = id;
	var sHtml = "";
	var pali = doc_word("#" + id).val("real");

	var arrGramma = new Array();
	if (mDict[pali]) {
		for (var i in mDict[pali]) {
			var type = mDict[pali][i].type;
			var gramma = mDict[pali][i].gramma;
			if ((type && type.length > 0) || (gramma && gramma.length > 0)) {
				var sCase = type + "#" + gramma;
				if (arrGramma[sCase]) {
					if (mDict[pali][i].confidence > arrGramma[sCase]) {
						arrGramma[sCase] = mDict[pali][i].confidence;
					}
				} else {
					arrGramma[sCase] = 1;
				}
			}
		}
	}
	arrGramma.sort(sortWordConfidence);

	for (var sGramma in arrGramma) {
		var sLocalCase = getLocalGrammaStr(sGramma);
		if (target == 0) {
			sHtml +=
				"<a onclick='fieldListChanged(\"" +
				wordID +
				'","case","' +
				sGramma +
				"\")'>" +
				cutString(sLocalCase, 30) +
				"</a>";
		} else {
			sHtml += "<a onclick='mdf_win_case_change(\"" + sGramma + "\")'>" + cutString(sLocalCase, 30) + "</a>";
		}
	}
	return sHtml;
}

function show_word_menu_gramma(id) {
	var gramma_div = document.getElementById("gramma_" + id);
	if (gramma_div) {
		var word_gramma_div = document.getElementById("word_gramma");
		if (word_gramma_div) {
			//word_menu_div.appendChild(menu_div);
			$("#gramma_" + id).html($("#word_gramma").html());
		}
	}
}

function hiddenProgressDiv() {
	document.getElementById("loading_bar").style.animation = "opacityGo 1s both";
}

function editor_project_updataProjectInfo() {
	var strInfo = "";
	var iInlineDictCount = gXmlBookDataInlineDict.getElementsByTagName("word").length;
	var iWordCount = gXmlBookDataBody.getElementsByTagName("word").length;
	strInfo += gLocal.gui.wordnum + iWordCount + "<br />";
	strInfo += gLocal.gui.para + "：" + gArrayDocParagraph.length + "<br />";
	strInfo += gLocal.gui.innerdict + "：" + iInlineDictCount + "<br />";
	strInfo += gLocal.gui.vocabulary + CountVocabulary() + "<br />";

	//document.getElementById("id_editor_project_infomation").innerHTML = strInfo;
	//document.getElementById("doc_info_title").value = getNodeText(gXmlBookDataHead, "doc_title");
	document.getElementById("editor_doc_title").innerHTML = getNodeText(gXmlBookDataHead, "doc_title");
	document.getElementById("file_title").innerHTML = getNodeText(gXmlBookDataHead, "doc_title");
}

//import old ver file

var editor_importOldVerXmlHttp = null;
function editor_importOldVer(strFileName) {
	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		editor_importOldVerXmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		editor_importOldVerXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (editor_importOldVerXmlHttp != null) {
		var d = new Date();
		var strLink = "";
		strLink = "./com_fileopen.php?filename=" + strFileName;
		if (strLink.length > 0) {
			editor_importOldVerXmlHttp.onreadystatechange = editor_import_old_ver_serverResponse;
			editor_importOldVerXmlHttp.open("GET", strLink, true);
			editor_importOldVerXmlHttp.send(null);
			//document.getElementById('sutta_text').innerHTML="Importing..."+strFileName;
		} else {
			//document.getElementById('sutta_text').innerHTML="无法识别的文件类型";
		}
	} else {
		alert("Your browser does not support XMLHTTP.");
	}
}

function editor_import_old_ver_serverResponse() {
	// 4 = "loaded"
	if (editor_importOldVerXmlHttp.readyState == 4) {
		document.getElementById("sutta_text").innerHTML = '<div class="sutta_top_blank"></div>';
		if (editor_importOldVerXmlHttp.status == 200) {
			// 200 = "OK"
			var xmlText = editor_importOldVerXmlHttp.responseText;

			if (window.DOMParser) {
				parser = new DOMParser();
				gXmlOldVerData = parser.parseFromString(xmlText, "text/xml");
			} else {
				// Internet Explorer

				gXmlOldVerData = new ActiveXObject("Microsoft.XMLDOM");
				gXmlOldVerData.async = "false";
				gXmlOldVerData.loadXML(xmlText);
			}

			if (gXmlOldVerData == null) {
				alert("error:can not load. xml obj is null.");
				return;
			}

			oldVerDataParse(gXmlOldVerData);
		} else {
			document.getElementById("sutta_text").innerHTML =
				"Problem retrieving data:" + editor_openProjectXmlHttp.statusText;
		}
	}
}

//在段落前设置或取消分页
function editor_page_break(obj, book, par) {
	if (obj.checked) {
		document.getElementById("par_" + book + "_" + par).style.pageBreakBefore = "always";
	} else {
		document.getElementById("par_" + book + "_" + par).style.pageBreakBefore = "auto";
	}
}

function editor_heading_change(obj, book, par) {
	document.getElementById("content").innerHTML = "";
	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		type = getNodeText(xmlParInfo, "type");
		if (bookId == book && paragraph == par && type == "heading") {
			setNodeText(xmlParInfo, "level", obj.value);
		}
	}
	updataHeadingBlockInHtml(book, par);
	updataToc();
}

function editor_par_show(obj, book, par) {
	parId = "par_" + book + "_" + (par - 1);
	if (obj.checked) {
		document.getElementById(parId).style.display = "block";
	} else {
		document.getElementById(parId).style.display = "none";
	}

	var rootIndex = -1;
	var rootLevel = -1;
	allBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		type = getNodeText(xmlParInfo, "type");
		if (bookId == book && paragraph == par && type == "heading") {
			rootIndex = iBlock;
			rootLevel = getNodeText(xmlParInfo, "level");
			break;
		}
	}

	var opBegin = false;
	for (var iBlock = rootIndex + 1; iBlock < allBlock.length; iBlock++) {
		xmlParInfo = allBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = allBlock[iBlock].getElementsByTagName("data")[0];

		bookId = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		type = getNodeText(xmlParInfo, "type");
		if (type == "heading") {
			currLevel = getNodeText(xmlParInfo, "level");
			if (currLevel == 0 || currLevel > rootLevel) {
				opBegin = true;
				parId = "par_" + bookId + "_" + (paragraph - 1);
				if (obj.checked) {
					document.getElementById(parId).style.display = "block";
				} else {
					document.getElementById(parId).style.display = "none";
				}
			} else {
				if (opBegin) {
					break;
				}
			}
		}
	}
}

function editor_right_tool_bar_slide_toggle() {
	if (document.getElementById("right_tool_bar").style.left == "100%") {
		document.getElementById("right_tool_bar").style.display = "block";
		document.getElementById("right_tool_bar").style.left = "calc(100% - 28vw)";
		document.getElementById("right_tool_bar").style.width = "28vw";
	} else {
		document.getElementById("right_tool_bar").style.left = "100%";
	}
}
function right_panal_slide_toggle(idPanal) {
	if ($("#" + idPanal).hasClass("act")) {
		document.getElementById("right_tool_bar").style.display = "block";
		document.getElementById("right_tool_bar").style.left = "calc(100% - 28vw)";
		document.getElementById("right_tool_bar").style.width = "28vw";
	} else {
		document.getElementById("right_tool_bar").style.left = "100%";
	}
}

function editor_show_right_tool_bar(visible) {
	if (visible) {
		document.getElementById("right_tool_bar").style.display = "block";
		document.getElementById("right_tool_bar").style.left = "calc(100% - 28vw)";
		document.getElementById("right_tool_bar").style.width = "28vw";
	} else {
		document.getElementById("right_tool_bar").style.left = "100%";
	}
}

function editor_goto_link(bookId, parNo, strLink = "") {
	parIndex = getParIndex(bookId, parNo);
	scrollEventLock = true;
	setNewView(parIndex - 3, parIndex + 6);
	scrollEventLock = false;
	if (strLink == "") {
		window.location.assign("#par_begin_" + bookId + "_" + (parNo - 1));
	} else {
		window.location.assign("#" + strLink);
	}
}

function get_language_order(strLanguage) {
	for (iLan in dict_language_order) {
		if (dict_language_order[iLan] == strLanguage) {
			return iLan;
		}
	}
	return 1000;
}

function removeAllInlinDictItem() {
	var count;
	var xAllWord = gXmlBookDataInlineDict.getElementsByTagName("word");
	count = xAllWord.length;
	while (xAllWord.length) {
		gXmlBookDataInlineDict.removeChild(xAllWord[0]);
	}
	g_DictWordList = new Array();
	return count;
}

function editor_refresh_inline_dict(word) {
	currMatchingDictNum = 0;
	g_dictFindParentLevel = 0;
	g_dictFindAllDone = false;

	g_dict_search_one_dict_done = editor_dict_one_dict_done;
	g_dict_search_all_done = editor_dict_all_done;
	g_dict_search_one_pass_done = null;

	dict_mark_word_list_done();
	dict_push_word_to_download_list(word, 0);

	var arrBuffer = dict_get_search_list();
	if (arrBuffer.length > 0) {
		g_CurrDictBuffer = JSON.stringify(arrBuffer);
		dict_mark_word_list_done();
		editor_dict_match();
	} else {
		document.getElementById("editor_doc_notify").innerHTML = "no new part";
	}
}

function win_close(id) {
	document.getElementById(id).style.display = "none";
}

//利用下拉菜单修改单词信息

function fieldListChanged(inWordId, inField, inChangeTo, sParent = null) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	var wordIndex = getWordIndex(inWordId);
	let arr_id_word = inWordId.split("-");
	let book = arr_id_word[0].slice(1);
	let paragraph = arr_id_word[1];

	setNodeText(xAllWord[wordIndex], "status", "7");
	setNodeAttr(xAllWord[wordIndex], inField, "status", "7");

	if (inField == "om") {
		/*拆分意思去掉开头的[a]*/
		inChangeTo = inChangeTo.replace("[a]", "");
	}
	setNodeText(xAllWord[wordIndex], inField, inChangeTo);
	if (sParent) {
		setNodeText(xAllWord[wordIndex], "parent", sParent);
	}

	//提交用户逐词解析数据库
	user_wbw_push_word(inWordId);
	user_wbw_commit();

	//准备消息数据
	let d = new Date();
	let msg_doc_id;
	if (doc_info.sendmsg) {
		if (doc_info.parent_id != "") {
			msg_doc_id = doc_info.parent_id;
		} else {
			msg_doc_id = doc_info.doc_id;
		}
		let msg_data = new Object();
		msg_data.id = inWordId;
		msg_data[inField] = inChangeTo;
		msg_data.status = 7;
		msg_push(1, JSON.stringify(msg_data), msg_doc_id, d.getTime(), book, paragraph);
	}
	modifyWordDetailByWordIndex(wordIndex);

	//modify other same word with auto-mark
	var word = getNodeText(xAllWord[wordIndex], "real");
	for (var i = wordIndex + 1; i < xAllWord.length; i++) {
		let status = getNodeText(xAllWord[i], "status");
		if (status != 7) {
			if (getNodeText(xAllWord[i], "real") == word) {
				setNodeText(xAllWord[i], inField, inChangeTo);
				if (sParent) {
					setNodeText(xAllWord[i], "parent", sParent);
				}
				setNodeText(xAllWord[i], "status", "5");
				//准备消息数据
				if (doc_info.sendmsg) {
					let wordid = getNodeText(xAllWord[i], "id");
					let msg_data = new Object();
					msg_data.id = wordid;
					msg_data[inField] = inChangeTo;
					msg_data.status = 5;
					msg_push(1, JSON.stringify(msg_data), msg_doc_id, d.getTime(), book, paragraph);
				}
				modifyWordDetailByWordIndex(i);
			}
		}
	}
}

function editor_word_status_by_id(id, newStatus = null) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	return editor_word_status(xAllWord[getWordIndex(wordId)]), newStatus;
}
function editor_word_status(wElement, newStatus = null) {
	if (newStatus == null) {
		var wStatus = getNodeText(wElement, "status");
		if (wStatus == "") {
			var oldVerStauts = getNodeText(wElement, "bmc");
			if (oldVerStauts == "") {
				setNodeText(wElement, "status", "1"); //未处理
				return 1;
			} else if (oldVerStauts == "bmca") {
				setNodeText(wElement, "status", "3"); //自己机器自动
				return 3;
			} else {
				setNodeText(wElement, "status", "7"); //人工
				return 7;
			}
		} else {
			return wStatus;
		}
	} else {
		setNodeText(wElement, "status", newStatus.toString());
	}
}

//载入用户设置
function editor_setup_load() {
	$.post(
		"./user_setup.php",
		{
			op: "load",
		},
		function (data, status) {
			if (data.length > 0) {
				gUserSetup = JSON.parse(data);
			}
		}
	);
}
//修改用户设置
function editor_setup_save(key, value) {
	$.post(
		"./user_setup.php",
		{
			op: "save",
			key: key,
			value: value,
		},
		function (data, status) {
			if (data.length > 0) {
				gUserSetup = JSON.parse(data);
			}
		}
	);
}

function tran_sen_save_click(blockid, senBegin, senEnd, obj) {
	let textareaid = "ta_" + blockid + "_" + senBegin + "_" + senEnd;
	let newText = $("#" + textareaid).val();
	tran_sen_save(blockid, senBegin, senEnd, newText);
}
function tran_sen_save(blockid, senBegin, senEnd, input) {
	if (input.length > 0) {
		//input=input.replace(/\n\n/g,"<br />");
	}
	input = term_edit_to_std_str(input);
	setTranText(blockid, senEnd, input);
	doc_tran("#" + blockid).text(senBegin, senEnd, "status", 7);
}

function tran_text_onchange(blockid, senBegin, senEnd, obj) {
	let newText = obj.value;
	tran_sen_save(blockid, senBegin, senEnd, newText);
	//保存到数据库
	sen_save(blockid, senBegin, senEnd, newText);
}

/*
句子失去焦点
退出编辑状态
*/
function tran_sent_div_blur(blockId, senBegin, senEnd, obj) {
	obj.style.height = "28px";
}
function tran_sent_div_onfocus(blockId, senBegin, senEnd, obj) {
	obj.style.height = "100px";
}
//鼠标移到逐句翻译上 编辑状态
function tran_sent_div_mouseenter(blockId, wordSn) {
	/*
		$("#tran_sent_text_div_"+blockId+"_"+wordSn).show();
		if(_tran_show_preview_on_edit==true){
			$("#tran_pre_"+blockId+"_"+wordSn).show();
		}
		else{
			$("#tran_pre_"+blockId+"_"+wordSn).hide();
		}
		*/
}
function set_tran_show_mode(set, obj) {
	if (set == 1) {
		_tran_show_preview_on_edit = obj.checked;
	} else if (set == 2) {
		_tran_show_textarea_esc_edit = obj.checked;
		if (obj.checked) {
			$(".tran_sen_textarea").show();
		} else {
			$(".tran_sen_textarea").hide();
		}
	}
}

//按自动查词典按钮
var _para_list = new Array();

function menu_dict_match1() {
	var book;
	var para = new Array();
	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		book = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");

		para[book + "-" + paragraph] = { book: book, para: paragraph };
	}
	_para_list = new Array();
	for (var i in para) {
		_para_list.push(para[i]);
	}
	if (_para_list.length > 0) {
		auto_match_wbw(0);
	}
}

//自动查词典
function auto_match_wbw(para_index) {
	$.get(
		"./dict_find_auto.php",
		{
			book: _para_list[para_index].book,
			para: _para_list[para_index].para,
		},
		function (data, status) {
			if (data.length > 0) {
				var dict_data = new Array();
				try {
					dict_data = JSON.parse(data);
				} catch (error) {
					ntf_show("Error:" + error + "<br>" + data);
				}
				var counter = 0;
				var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
				for (var x = 0; x < xAllWord.length; x++) {
					let wordStatus = getNodeText(xAllWord[x], "status");
					if (parseInt(wordStatus) > 3) {
						//忽略已经被用户修改的词
						continue;
					}
					let wid = getNodeText(xAllWord[x], "id");
					let aid = wid.split("-");
					let book = aid[0].substr(1);
					let para = aid[1];
					let num = aid[2];
					for (var i = 0; i < dict_data.length; i++) {
						if (dict_data[i].book == book && dict_data[i].paragraph == para && dict_data[i].num == num) {
							if (dict_data[i].type) {
								setNodeText(xAllWord[x], "type", dict_data[i].type);
							}
							if (dict_data[i].gramma) {
								setNodeText(xAllWord[x], "gramma", dict_data[i].gramma);
							}
							setNodeText(xAllWord[x], "case", dict_data[i].type + "#" + dict_data[i].gramma);
							if (dict_data[i].mean) {
								setNodeText(xAllWord[x], "mean", dict_data[i].mean);
							}
							if (dict_data[i].parent) {
								setNodeText(xAllWord[x], "parent", dict_data[i].parent);
							}
							if (dict_data[i].parts) {
								setNodeText(xAllWord[x], "org", dict_data[i].parts);
							}
							if (dict_data[i].partmean) {
								setNodeText(xAllWord[x], "om", dict_data[i].partmean);
							}
							setNodeText(xAllWord[x], "status", "3");
							counter++;
							modifyWordDetailByWordId(wid);
							user_wbw_push_word(wid);
							break;
						}
					}
				}
				user_wbw_commit();
			}
			//计算查字典的进度
			var precent = (para_index * 100) / (_para_list.length - 1);
			ntf_show(
				gLocal.gui.auto_fill +
					_para_list[para_index].book +
					"-" +
					_para_list[para_index].para +
					"-" +
					precent.toFixed(1) +
					"%" +
					gLocal.gui.finished
			);
			para_index++;
			if (para_index < _para_list.length) {
				auto_match_wbw(para_index);
			}
		}
	);
}

//旧版本的xml解析
function oldVerDataParse(oldXmlData) {
	createXmlDoc();
	newBlockString = "<root><block><info></info><data></data></block></root>";
	if (window.DOMParser) {
		parser = new DOMParser();
		newXmlBlock = parser.parseFromString(newBlockString, "text/xml");
	} else {
		// Internet Explorer
		newXmlBlock = new ActiveXObject("Microsoft.XMLDOM");
		newXmlBlock.async = "false";
		newXmlBlock.loadXML(newBlockString);
	}

	if (newXmlBlock == null) {
		alert("error:can not load book index.");
		return;
	}

	var titleBlockInfo = new Array();
	var titleInfo = new Object();
	titleInfo.language = "pali";
	titleInfo.author = "author";
	titleBlockInfo.push(titleInfo);
	var titleInfo = new Object();
	titleInfo.language = "en";
	titleInfo.author = "author";
	titleBlockInfo.push(titleInfo);
	var titleInfo = new Object();
	titleInfo.language = "zh";
	titleInfo.author = "author";
	titleBlockInfo.push(titleInfo);

	var iPara = 1;
	var BookId = com_guid();
	var x = gXmlOldVerData.getElementsByTagName("sutta");
	for (var i = 0; i < x.length; i++) {
		//title begin
		xTitle = x[i].getElementsByTagName("title");
		/*if title node is */
		if (xTitle.length > 0) {
			/*text of title*/
			var xTitleText = xTitle[0].getElementsByTagName("text");
			if (xTitleText.length > 0) {
				for (var iTitleText = 0; iTitleText < xTitleText.length; iTitleText++) {
					cloneBlock = newXmlBlock.cloneNode(true);
					newBlock = cloneBlock.getElementsByTagName("block")[0];
					xmlNewInfo = newBlock.getElementsByTagName("info")[0];
					xmlNewData = newBlock.getElementsByTagName("data")[0];

					var titleLangauge = "en";
					var titleAuthor = "unkow";
					var xTitleTextInfo = xTitleText[iTitleText].getElementsByTagName("info");
					if (xTitleTextInfo.length > 0) {
						titleLangauge = getNodeText(xTitleTextInfo[0], "language");
						titleAuthor = getNodeText(xTitleTextInfo[0], "tranAuthor");
					}
					var strTitle = getNodeText(xTitleText[iTitleText], "data");

					setNodeText(xmlNewInfo, "type", "heading");
					setNodeText(xmlNewInfo, "paragraph", iPara.toString());
					setNodeText(xmlNewInfo, "book", BookId);
					setNodeText(xmlNewInfo, "author", "kosalla");
					setNodeText(xmlNewInfo, "language", titleLangauge);
					setNodeText(xmlNewInfo, "edition", "0");
					setNodeText(xmlNewInfo, "subedition", "0");
					setNodeText(xmlNewInfo, "level", "1");
					setNodeText(xmlNewInfo, "id", com_guid());
					setNodeText(xmlNewData, "text", strTitle);
					gXmlBookDataBody.appendChild(newBlock);
				}
			}
			/*end of text of title*/
		}
		//end of title

		xParagraph = x[i].getElementsByTagName("paragraph");
		for (var j = 0; j < xParagraph.length; j++) {
			//toc begin
			if (j > 0) {
				for (var iTran = 0; iTran < titleBlockInfo.length; iTran++) {
					cloneBlock = newXmlBlock.cloneNode(true);
					newBlock = cloneBlock.getElementsByTagName("block")[0];
					xmlNewInfo = newBlock.getElementsByTagName("info")[0];
					xmlNewData = newBlock.getElementsByTagName("data")[0];

					titleLangauge = titleBlockInfo[iTran].language;
					titleAuthor = titleBlockInfo[iTran].author;
					var strTitle = "new title";

					setNodeText(xmlNewInfo, "type", "heading");
					setNodeText(xmlNewInfo, "paragraph", iPara.toString());
					setNodeText(xmlNewInfo, "book", BookId);
					setNodeText(xmlNewInfo, "author", titleAuthor);
					setNodeText(xmlNewInfo, "language", titleLangauge);
					setNodeText(xmlNewInfo, "edition", "0");
					setNodeText(xmlNewInfo, "subedition", "0");
					setNodeText(xmlNewInfo, "level", "0");
					setNodeText(xmlNewInfo, "id", com_guid());
					setNodeText(xmlNewData, "text", strTitle);
					gXmlBookDataBody.appendChild(newBlock);
				}
			}
			//toc end
			//word by word paragraph begin
			xPali = xParagraph[j].getElementsByTagName("palipar");
			if (xPali.length > 0) {
				cloneBlock = newXmlBlock.cloneNode(true);
				newBlock = cloneBlock.getElementsByTagName("block")[0];
				xmlNewInfo = newBlock.getElementsByTagName("info")[0];
				xmlNewData = newBlock.getElementsByTagName("data")[0];
				setNodeText(xmlNewInfo, "type", "wbw");
				setNodeText(xmlNewInfo, "paragraph", iPara.toString());
				setNodeText(xmlNewInfo, "book", BookId);
				setNodeText(xmlNewInfo, "author", "kosalla");
				setNodeText(xmlNewInfo, "edition", "0");
				setNodeText(xmlNewInfo, "subedition", "0");
				setNodeText(xmlNewInfo, "id", com_guid());
				xWord = xPali[0].getElementsByTagName("word"); //如果只有一个palipar
				/*遍历此段落中所有单词*/
				var iSen = 0;
				var strTranWords = "";
				for (k = 0; k < xWord.length; k++) {
					newWord = xWord[k].cloneNode(true);
					xmlNewData.appendChild(newWord);
				}
				gXmlBookDataBody.appendChild(newBlock);
			}
			//word by word paragraph begin

			/*翻译块开始*/
			xTran = xParagraph[j].getElementsByTagName("translate");
			if (xTran.length > 0) {
				/*text of translate*/
				var xTranText = xTran[0].getElementsByTagName("text");
				if (xTranText.length > 0) {
					for (iTranText = 0; iTranText < xTranText.length; iTranText++) {
						cloneBlock = newXmlBlock.cloneNode(true);
						newBlock = cloneBlock.getElementsByTagName("block")[0];
						xmlNewInfo = newBlock.getElementsByTagName("info")[0];
						xmlNewData = newBlock.getElementsByTagName("data")[0];

						var tranLangauge = "";
						var tranAuthor = "";
						var xTranTextInfo = xTranText[iTranText].getElementsByTagName("info");
						if (xTranTextInfo.length > 0) {
							tranLangauge = getNodeText(xTranTextInfo[0], "language");
							tranAuthor = getNodeText(xTranTextInfo[0], "author");
						}
						var strTran = getNodeText(xTranText[iTranText], "data");

						setNodeText(xmlNewInfo, "type", "translate");
						setNodeText(xmlNewInfo, "paragraph", iPara.toString());
						setNodeText(xmlNewInfo, "book", BookId);
						setNodeText(xmlNewInfo, "author", tranAuthor);
						setNodeText(xmlNewInfo, "language", tranLangauge);
						setNodeText(xmlNewInfo, "edition", "0");
						setNodeText(xmlNewInfo, "subedition", "0");
						setNodeText(xmlNewInfo, "id", com_guid());
						newSen = newXmlBlock.createElement("sen");
						setNodeText(newSen, "a", "");
						setNodeText(newSen, "text", strTran);
						xmlNewData.appendChild(newSen);
						gXmlBookDataBody.appendChild(newBlock);
					}
				}
				/*end of text of translate*/
			}
			/*翻译块结束*/

			/*文件内note块开始*/
			xTran = xParagraph[j].getElementsByTagName("comm");
			if (xTran.length > 0) {
				/*text of translate*/
				var xTranText = xTran[0].getElementsByTagName("text");
				if (xTranText.length > 0) {
					for (iTranText = 0; iTranText < xTranText.length; iTranText++) {
						cloneBlock = newXmlBlock.cloneNode(true);
						newBlock = cloneBlock.getElementsByTagName("block")[0];
						xmlNewInfo = newBlock.getElementsByTagName("info")[0];
						xmlNewData = newBlock.getElementsByTagName("data")[0];

						var tranLangauge = "";
						var tranAuthor = "";
						var xTranTextInfo = xTranText[iTranText].getElementsByTagName("info");
						if (xTranTextInfo.length > 0) {
							tranLangauge = getNodeText(xTranTextInfo[0], "language");
							tranAuthor = getNodeText(xTranTextInfo[0], "author");
						}
						var strNote = getNodeText(xTranText[iTranText], "data");

						setNodeText(xmlNewInfo, "type", "note");
						setNodeText(xmlNewInfo, "paragraph", iPara.toString());
						setNodeText(xmlNewInfo, "book", BookId);
						setNodeText(xmlNewInfo, "author", tranAuthor);
						setNodeText(xmlNewInfo, "language", titleLangauge);
						setNodeText(xmlNewInfo, "edition", "0");
						setNodeText(xmlNewInfo, "subedition", "0");
						setNodeText(xmlNewInfo, "id", com_guid());
						newSen = newXmlBlock.createElement("sen");
						setNodeText(newSen, "a", "");
						setNodeText(newSen, "text", strNote);
						xmlNewData.appendChild(newSen);
						gXmlBookDataBody.appendChild(newBlock);
					}
				}
				/*end of text of translate*/
			}
			/*文件内翻译块结束*/

			iPara++;
		}
	}
	projectDataParse(gXmlBookData);
	updataToc();
	refreshResource();
}

function add_part(part) {
	$("#input_org").val(part);
}

function edit_show_prt_prt(obj){
	let o = obj.getElementsByTagName("svg");
	if(document.getElementById("edit_detail_prt_prt").style.display=="none"){
		document.getElementById("edit_detail_prt_prt").style.display="block";
		o[0].style.transform="rotate(90deg)";
	}
	else{
		document.getElementById("edit_detail_prt_prt").style.display="none";
		o[0].style.transform="rotate(0deg)";
	}
}

function edit_parent_grammar_changed(str){
	document.getElementById("parent_grammar").innerHTML=getLocalGrammaStr(str);
	document.getElementById("input_parent_grammar").value=str;
	
}