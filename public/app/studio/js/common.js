var g_is_mobile = false;
var g_language = "langrage_cn";

var gConfigDirMydocument = "../user/My Document/";

//输出调试信息
function debugOutput(str, level = 0) {
	console.log(str);
}
function var_dump(str) {
	ntf_show(str);
	/*
	getStyleClass("debug_info").style.display = "-webkit-flex";
	getStyleClass("debug_info").style.animation = "viewbug 2s";
	document.getElementById("debug").innerHTML = str;
	var t = setTimeout("clearDebugMsg()", 2000);
	*/
}
function clearDebugMsg() {
	document.getElementById("debug").innerHTML = "";
	getStyleClass("debug_info").style.display = "none";
}

//页面初始字体大小 单位 %
var iStartFontSize = 100;
function setHitsVisibility(isVisible) {
	var c = getStyleClass("hit");
	if (isVisible) {
		c.style.backgroundColor = "blue";
		c.style.color = "white";
	} else {
		c.style.backgroundColor = "white";
		c.style.color = "black";
	}
}

const root = document.documentElement;
function setPageColor(sColor) {
	var cssobj = document.getElementById("colorchange");
	switch (sColor) {
		case 0:
			cssobj.setAttribute("href", "css/color_day.css");
			break;
		case 1:
			cssobj.setAttribute("href", "css/color_dawn.css");
			break;
		case 2:
			cssobj.setAttribute("href", "css/color_night.css");
			break;
	}
}

//修改页面字体大小
function setPageFontSize(fChange) {
	iStartFontSize = iStartFontSize * fChange;
	var myBody = document.getElementById("mbody");
	myBody.style.fontSize = iStartFontSize + "%";
	setCookie("fontsize", iStartFontSize, 65);
}

function editSandhiDisplay(item, obj) {
	var isVisible = obj.checked;
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	switch (item) {
		case "parent":
			getStyleClass("un_parent").style.display = isVisible ? "block" : "none";
			break;
		case "comp_parent":
			getStyleClass("comp_parent").style.display = isVisible ? "block" : "none";
			break;
	}
	for (iWordIndex = 0; iWordIndex < xAllWord.length; iWordIndex++) {
		var un = getNodeText(xAllWord[iWordIndex], "un");
		if (un.length > 0) {
			switch (item) {
				case "begin":
					if (getNodeText(xAllWord[iWordIndex], "case") == ".un:begin.") {
						wordid = "wb" + getNodeText(xAllWord[iWordIndex], "id");
						document.getElementById(wordid).style.display = isVisible ? "block" : "none";
					}
					break;
				case "end":
					if (getNodeText(xAllWord[iWordIndex], "case") == ".un:end.") {
						wordid = "wb" + getNodeText(xAllWord[iWordIndex], "id");
						document.getElementById(wordid).style.display = isVisible ? "block" : "none";
					}
					break;
				case "word":
					if (
						getNodeText(xAllWord[iWordIndex], "case") != ".un:begin." ||
						getNodeText(xAllWord[iWordIndex], "case") != ".un:end." ||
						getNodeText(xAllWord[iWordIndex], "case") != ".ctl.#.a."
					) {
						//既不是開始也不是結束
						wordid = "wb" + getNodeText(xAllWord[iWordIndex], "id");
						document.getElementById(wordid).style.display = isVisible ? "block" : "none";
					}
					break;
				case "parent":
					//document.getElementById("wb"+un).style.display=(isVisible ? 'block' : 'none');
					break;
			}
		}
	}
}

function setFootnotesVisibility(isVisible) {
	getStyleClass("note").style.display = isVisible ? "inline" : "none";
}
function setIdVisibility() {
	var isVisible = document.getElementById("B_Id").checked;
	getStyleClass("ID").style.display = isVisible ? "block" : "none";
}
function setMeaningVisibility(obj) {
	var isVisible = obj.checked;
	getStyleClass("mean").style.display = isVisible ? "flex" : "none";
}
function setOrgVisibility(obj) {
	var isVisible = obj.checked;
	getStyleClass("org").style.display = isVisible ? "flex" : "none";
}
function setOrgMeaningVisibility(obj) {
	var isVisible = obj.checked;
	getStyleClass("om").style.display = isVisible ? "flex" : "none";
}

function setGrammaVisibility(obj) {
	var isVisible = obj.checked;
	getStyleClass("case").style.display = isVisible ? "flex" : "none";
}

function set_WBW_ALL_Visibility(obj) {
	let isVisible = obj.checked;
	getStyleClass("mean").style.display = isVisible ? "flex" : "none";
	getStyleClass("org").style.display = isVisible ? "flex" : "none";
	getStyleClass("om").style.display = isVisible ? "flex" : "none";
	getStyleClass("case").style.display = isVisible ? "flex" : "none";

	document.getElementById("WBW_B_Meaning").checked = obj.checked;
	document.getElementById("WBW_B_Org").checked = obj.checked;
	document.getElementById("WBW_B_OrgMeaning").checked = obj.checked;
	document.getElementById("WBW_B_Gramma").checked = obj.checked;
}

//显示英译
function setParTranEnVisibility(obj) {
	var isVisible = obj.checked;
	//getStyleClass('tran_par_en').style.display = (isVisible ? 'block' : 'none');
	getStyleClass("en_text").style.display = isVisible ? "block" : "none";
}
//显示中译
function setParTranCnVisibility(obj) {
	var isVisible = obj.checked;
	//getStyleClass('tran_par_cn').style.display = (isVisible ? 'block' : 'none');
	getStyleClass("zh_text").style.display = isVisible ? "block" : "none";
	getStyleClass("tw_text").style.display = isVisible ? "block" : "none";
}
/*
//显示模式
_display_para_arrange=0;//0:横向 排列 1:纵向排列
_display_sbs=0; //0:逐段  1:逐句
*/
function setArrange(mode) {
	if (_display_para_arrange == mode) {
		return;
	}
	_display_para_arrange = mode;
	if (_display_para_arrange == 1) {
		/* 上下对读 */
		getStyleClass("wbwdiv").style.flex = "1";
		getStyleClass("trandiv").style.flex = "1";
		getStyleClass("pardiv").style.flexDirection = "column";
		getStyleClass("sent_wbw_trans").style.flexDirection = "column";
        	$(".translate_sent_head").each(function(){
                    $(this).height('auto');
                });
            $('.translate_sent').css('margin-top','unset');
        getStyleClass("sent_wbw_trans").style.margintop = '0';
	} else if (_display_para_arrange == 0) {
		/* 0 左右对读 */
		getStyleClass("wbwdiv").style.flex = "7";
		getStyleClass("trandiv").style.flex = "3";
		getStyleClass("pardiv").style.flexDirection = "row";
		getStyleClass("sent_wbw_trans").style.flexDirection = "row";
	    $(".translate_sent_head").each(function(){
            $(this).height($(this).parent()[0].scrollHeight+"px");
            });
        getStyleClass("sent_wbw_trans").style.margintop = '-20px';
        $('.translate_sent').css('margin-top','-20px');
	}
	if (_display_sbs == 1) {
		/* 逐句对读 */
		getStyleClass("pardiv").style.flexDirection = "column";
	}
}

function setSbs(mode) {
	if (_display_sbs != mode) {
		_display_sbs = mode;
		if (_display_sbs == 1) {
			/* 逐句对读 */
			getStyleClass("translate_sent").style.display = "block";
			getStyleClass("pardiv").style.flexDirection = "column";
			updateWordParBlockInnerAll();
			var eAllSent = document.getElementsByClassName("tran_sent");
			for (var iSen = 0; iSen < eAllSent.length; iSen++) {
				var senA = eAllSent[iSen].getAttributeNode("sn").value;
				var blockId = eAllSent[iSen].getAttributeNode("block").value;
				var eSBSDiv = document.getElementById("sent_" + senA);
				if (eSBSDiv) {
					var eBlockSenDiv = document.getElementById("sent_" + senA + "_" + blockId);
					if (!eBlockSenDiv) {
						//没有 添加
						var divSen = document.createElement("div");
						var typ = document.createAttribute("class");
						typ.nodeValue = "sbs_sent_block";
						divSen.attributes.setNamedItem(typ);

						var typId = document.createAttribute("id");
						typId.nodeValue = "sent_" + senA + "_" + blockId;
						divSen.attributes.setNamedItem(typId);

						var sn = document.createAttribute("sn");
						sn.nodeValue = senA;
						divSen.attributes.setNamedItem(sn);

						var block = document.createAttribute("block");
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
		} else {
			/* 逐段对读 */
			//因为wbw重绘会覆盖逐句html
			//所以将逐句对读里的句子 转移到逐段div
			getStyleClass("translate_sent").style.display = "none";
			var eAllSent = document.getElementsByClassName("sbs_sent_block");
			for (var iSen = 0; iSen < eAllSent.length; iSen++) {
				var senA = eAllSent[iSen].getAttributeNode("sn").value;
				var blockId = eAllSent[iSen].getAttributeNode("block").value;
				var eSBSDiv = document.getElementById("tran_sent_" + blockId + "_" + senA);
				if (eSBSDiv) {
					//逐段div
					eSBSDiv.innerHTML = eAllSent[iSen].innerHTML;
					eAllSent[iSen].innerHTML = "";
				} else {
					ntf_show("error: 没有找到div:" + "tran_sent_" + blockId + "_" + senA);
				}
			}

			updateWordParBlockInnerAll();
		}
	}
	refreshNoteNumber()//切换逐句后刷新note编号
}

//显示段对译模式

function setParTranShowMode(obj) {
	var isVisible = obj.checked;
	if (isVisible) {
		/* 上下对读 */
		_display_para_arrange = 1;
		getStyleClass("wbwdiv").style.flex = "1";
		getStyleClass("trandiv").style.flex = "1";
		getStyleClass("pardiv").style.flexDirection = "column";
		getStyleClass("sent_wbw_trans").style.flexDirection = "column";
	} else {
		/* 左右对读 */
		_display_para_arrange = 0;
		getStyleClass("wbwdiv").style.flex = "7";
		getStyleClass("trandiv").style.flex = "3";
		getStyleClass("pardiv").style.flexDirection = "row";
		getStyleClass("sent_wbw_trans").style.flexDirection = "row";
	}
}
//顯示逐句對讀模式
function setSentTranShowMode(obj) {
	var isVisible = obj.checked;
	if (isVisible) {
		/* 逐句对读 */
		_display_sbs = 1;
		updateWordParBlockInnerAll();
		var eAllSent = document.getElementsByClassName("tran_sent");
		for (var iSen = 0; iSen < eAllSent.length; iSen++) {
			var senA = eAllSent[iSen].getAttributeNode("sn").value;
			var eSBSDiv = document.getElementById("sent_" + senA);
			if (eSBSDiv) {
				eSBSDiv.innerHTML = eAllSent[iSen].innerHTML;
				eAllSent[iSen].innerHTML = "";
			}
		}
	} else {
		/* 逐段对读 */
		_display_sbs = 0;
		//getStyleClass('trandiv').style.display="block";
		//getStyleClass('translate_sent').style.display="none";
		updateWordParBlockInnerAll();
	}
}

//显示单词表
function setWordTableVisibility() {
	var isVisible = document.getElementById("B_WordTableShowMode").checked;
	document.getElementById("word_table").style.display = isVisible ? "block" : "none";
}

function getStyle(styleName) {
	for (var s = 0; s < document.styleSheets.length; s++) {
		if (document.styleSheets[s].rules) {
			for (var r = 0; r < document.styleSheets[s].rules.length; r++) {
				if (document.styleSheets[s].rules[r].selectorText == styleName) {
					return document.styleSheets[s].rules[r];
				}
			}
		} else if (document.styleSheets[s].cssRules) {
			for (var r = 0; r < document.styleSheets[s].cssRules.length; r++) {
				if (document.styleSheets[s].cssRules[r].selectorText == styleName)
					return document.styleSheets[s].cssRules[r];
			}
		}
	}

	return null;
}

function getStyleClass(className) {
	for (var s = 0; s < document.styleSheets.length; s++) {
		if (document.styleSheets[s].rules) {
			for (var r = 0; r < document.styleSheets[s].rules.length; r++) {
				if (document.styleSheets[s].rules[r].selectorText == "." + className) {
					return document.styleSheets[s].rules[r];
				}
			}
		} else if (document.styleSheets[s].cssRules) {
			for (var r = 0; r < document.styleSheets[s].cssRules.length; r++) {
				if (document.styleSheets[s].cssRules[r].selectorText == "." + className)
					return document.styleSheets[s].cssRules[r];
			}
		}
	}

	return null;
}

//读取cookie 中的字体大小
function checkCookie() {
	mFontSize = getCookie("fontsize");
	if (mFontSize != null && mFontSize != "") {
		iStartFontSize = mFontSize;
	} else {
		mFontSize = 100;
		iStartFontSize = 100;
		setCookie("fontsize", mFontSize, 365);
	}
	setPageFontSize(1);
	setPageColor(0);
}

function setObjectVisibilityAlone(strIdGroup, strId) {
	var hiden = new Array();
	hiden = strIdGroup.split("&");
	for (i = 0; i < hiden.length; i++) {
		document.getElementById(hiden[i]).style.display = "none";
	}
	var obj = document.getElementById(strId);
	obj.style.display = "block";
}

function setObjectVisibility(strId) {
	var obj = document.getElementById(strId);
	if (obj.style.display == "none") {
		obj.style.display = "block";
	} else {
		obj.style.display = "none";
	}
}

function setObjectVisibility2(ControllerId, ObjId) {
	var isVisible = document.getElementById(ControllerId).checked;
	document.getElementById(ObjId).style.display = isVisible ? "block" : "none";
}
function setObjectVisibility3(Controller, ObjId) {
	var isVisible = Controller.checked;
	document.getElementById(ObjId).style.display = isVisible ? "block" : "none";
}
function setAllTitleVisibility(Controller, numTitle) {
	for (var i = 0; i < numTitle; i++) {
		document.getElementById("titlevisable" + numTitle).checked = Controller.checked;
	}
}

function windowsInit() {
	var strSertch = location.search;
	if (strSertch.length > 0) {
		strSertch = strSertch.substr(1);
		var sertchList = strSertch.split("&");
		for (x in sertchList) {
			var item = sertchList[x].split("=");
			if (item[0] == "filename") {
				g_filename = item[1];
			}
		}
	}
	checkCookie();
	setUseMode("Read");
	if (g_filename.length > 0) {
		loadDictFromDB(g_filename);
		loadxml(g_filename);
	} else {
		alert("error:没有指定文件名。");
	}
}

/*静态页面使用的初始化函数*/
function windowsInitStatic() {
	checkCookie();
	setUseMode_Static("Read");
}

function indexInit() {
	showUserFilaList();
}

function goHome() {
	var r = confirm("在返回前请保存文件。否则所有的更改将丢失。\n 按<确定>回到主页。按<取消>留在当前页面。");
	if (r == true) {
		window.location.assign("./index.php?device=" + g_device);
	}
}

function get_Local_Code_Str(inStr, language) {
	var get_Local_Code_Str_i = 0;
	switch (language) {
		case "sinhala":
			for (get_Local_Code_Str_i in local_codestr_sinhala) {
				inStr = inStr.replace(
					local_codestr_sinhala[get_Local_Code_Str_i].id,
					local_codestr_sinhala[get_Local_Code_Str_i].value
				);
			}
			break;
		/*case "sc":
		for(get_Local_Code_Str_i in local_codestr_sc){
			inStr=inStr.replace(local_codestr_sc[get_Local_Code_Str_i].id,local_codestr_sc[get_Local_Code_Str_i].value);
		}
		break;
		case "tc":
		for(get_Local_Code_Str_i in local_codestr_tc){
			inStr=inStr.replace(local_codestr_tc[get_Local_Code_Str_i].id,local_codestr_tc[get_Local_Code_Str_i].value);
		}
		break;*/
		case "pali":
			break;
	}
	return inStr;
}

//将字符串变为本地化字符串
function getLocalGrammaStr(inStr) {
	if (typeof inStr == "undefined") {
		return "";
	} else {
		let str = inStr;
		for (const iterator of gLocal.grammastr) {
			str = str.replace(iterator.id, iterator.value);
		}
		return str;
	}
}

function getLocalGrammaStr_a(inStr) {
	var inStr_array_0 = new Array();
	i_inStr_array_0 = 0;
	if (inStr.lastIndexOf("<br>") != -1) {
		var split_str = "<br>";
	} else if (inStr.lastIndexOf("#") != -1) {
		var split_str = "#";
	} else {
		inStr_array_0.push(inStr);
	}
	inStr_array_0 = inStr.split(split_str);
	for (i_inStr_array_0 in inStr_array_0) {
		if (inStr_array_0[i_inStr_array_0].lastIndexOf("$") != -1) {
			var inStr_array = inStr_array_0[i_inStr_array_0].split("$");
			for (i_instr in inStr_array) {
				inStr_array[i_instr] = inStr_array[i_instr].slice(1, inStr_array[i_instr].length - 1);
				inStr_array[i_instr] = "。" + inStr_array[i_instr] + "。";
			}
			inStr_array_0.splice(i_inStr_array_0, 1, inStr_array.join("@"));
		} else if (inStr_array_0[i_inStr_array_0] != "" /*&& inStr_array_0[i_inStr_array_0].lastIndexOf(\s)=-1*/) {
			inStr_array_0[i_inStr_array_0] = inStr_array_0[i_inStr_array_0].slice(
				1,
				inStr_array_0[i_inStr_array_0].length - 1
			);
			inStr_array_0[i_inStr_array_0] = "。" + inStr_array_0[i_inStr_array_0] + "。";
		}
	}

	inStr = inStr_array_0.join(split_str);
	for (getLocalGrammaStr_i in gLocal.grammastr) {
		var str_Gramma = gLocal.grammastr[getLocalGrammaStr_i].id;
		if (str_Gramma != "$") {
			str_Gramma = str_Gramma.slice(1, str_Gramma.length - 1); //剝離前後的“.”
			str_Gramma = "。" + str_Gramma + "。"; //完成“.”到“。”的替換
			var special_RE = RegExp(str_Gramma, "g");
			inStr = inStr.replace(special_RE, gLocal.grammastr[getLocalGrammaStr_i].value);
		} else {
			var special_RE = RegExp("。@。", "g"); //轉化為正則表達式全局變量
			inStr = inStr.replace(special_RE, "。·。");
		}
	}
	//inStr=inStr.replace(/。/g,"");
	return inStr;
}

function getLocalDictname(inStr) {
	if (inStr) {
		var LocalDictname = inStr;
	} else {
		var LocalDictname = "NaN";
	}

	for (getLocalDictname_i in gLocal.dictname) {
		LocalDictname = LocalDictname.replace(
			gLocal.dictname[getLocalDictname_i].id,
			gLocal.dictname[getLocalDictname_i].value
		);
	}
	return LocalDictname;
}

function getLocalFormulaStr(inGramma, inStr) {
	if (inStr.indexOf("[") >= 0) {
		return inStr;
	}
	var output = inStr;
	for (i in gLocal.formula) {
		if (gLocal.formula[i].id == inGramma) {
			fList = gLocal.formula[i].value.split("$");
			output = fList[0].replace("~", inStr);
		}
	}
	return output;
}
function getFormulaList(strGramma) {
	var output = new Array();
	//先加载用户字典里的格位公式

	if (myFormula.length > 0) {
		for (let i in myFormula) {
			if (myFormula[i].gramma == strGramma) {
				if (myFormula[i].mean && myFormula[i].mean != "") {
					output.push(myFormula[i].mean);
				}
			}
		}
	}

	for (i in gLocal.formula) {
		if (gLocal.formula[i].id == strGramma) {
			return output.concat(gLocal.formula[i].value.split("$"));
		}
	}
}
function getLocalParentFormulaStr(inGramma, inStr) {
	var output = inStr;
	for (i in gLocal.parent_formula) {
		if (gLocal.parent_formula[i].id == inGramma) {
			output = gLocal.parent_formula[i].value.replace("~", inStr);
		}
	}
	return output;
}
function cutString(inString, cutLen) {
	if (inString) {
		if (inString.length > cutLen) {
			return inString.substring(0, cutLen - 1) + "…";
		} else {
			return inString;
		}
	} else {
		return "";
	}
}

function getElementWH(element) {
	var scrW, scrH;
	if (element.innerHeight && element.scrollMaxY) {
		// Mozilla
		scrW = element.innerWidth + element.scrollMaxX;
		scrH = element.innerHeight + element.scrollMaxY;
	} else if (element.scrollHeight > element.offsetHeight) {
		// all but IE Mac
		scrW = element.scrollWidth;
		scrH = element.scrollHeight;
	} else if (element) {
		// IE Mac
		scrW = element.offsetWidth;
		scrH = element.offsetHeight;
	}
	var obj = new Object();
	obj.width = scrW;
	obj.height = scrH;
	return obj;
}

/*
函数：tab_click
功能：点击选项卡时切换选项卡
参数：
panalId：选项卡对应的面板id
tabid：选项卡id
callback:回调函数
parm：回调函数参数
返回值：无
*/
function tab_click(panalId, tabid, callback = null, parm = null) {
	$("#" + tabid)
		.siblings()
		.removeClass("act");
	$("#" + tabid).addClass("act");
	if (panalId != "") {
		$("#" + panalId).show();
		$("#" + panalId)
			.siblings()
			.hide();
		$("#" + panalId + "_head").show();
		$("#" + panalId + "_head")
			.siblings()
			.hide();
	}
	if (callback != null) {
		if (parm != null) {
			callback(parm);
		}
	}
	guide_init();

}

/*
函数：tab_click_b
功能：点击选项卡时切换选项卡 一个
参数：
panalId：选项卡对应的面板id
tabid：选项卡id
callback:回调函数
parm：回调函数参数
返回值：无
*/
function tab_click_b(panalId, tabid, callback = null, parm = null) {
	if ($("#" + tabid).hasClass("act")) {
		$("#" + tabid).removeClass("act");
	} else {
		$("#" + tabid)
			.siblings()
			.removeClass("act");
		$("#" + tabid).addClass("act");
	}
	if (panalId != "") {
		$("#" + panalId).show();
		$("#" + panalId)
			.siblings()
			.hide();
		$("#" + panalId + "_head").show();
		$("#" + panalId + "_head")
			.siblings()
			.hide();
	}
	if (callback != null) {
		if (parm != null) {
			callback(parm);
		}
	}
}

function get_string_lang(inString) {
	let pattern2 = new RegExp("[A-Za-z]+");
	if (pattern2.test(inString)) {
		return "en";
	}
	let pattern = new RegExp("[\u4E00-\u9FA5]+");
	if (pattern.test(inString)) {
		return "zh";
	}

	return "en";
}

//查询某个字符串是否在字符串数组中出现‘
//@parm:str 被查询的字符串
//@parm:arr 数组
//@return:true 查到 false 没查到
function str_in_array(str, arr) {
	for (let x in arr) {
		if (arr[x] == str) {
			return true;
		}
	}
	return false;
}
