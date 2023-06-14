var nWord = 0;

var suttaWordList = new Array();
var tranParagraph = new Array();
var g_useMode;
var g_countWordList = 0;
var g_wordListOrderby = "count";
var g_wordListOrder = false;
var g_isDictFavo = false;
var g_autoUpdataDB = true;

var gTextEditMediaType = "";
var gSutta = 0;
var gPar = 0;
var gTran = 0;
var g_bookMark_array = ["a", "x", "1", "2", "3", "4", "5"];

var wordList = new Array();

function getNodeText(inNode, subTagName) {
	try {
		if (inNode && inNode.getElementsByTagName(subTagName).length > 0) {
			if (inNode.getElementsByTagName(subTagName)[0].childNodes.length > 0) {
				var mValue = inNode.getElementsByTagName(subTagName)[0].childNodes[0].nodeValue;
				mValue = mValue.replace("&lt;", "<");
				mValue = mValue.replace("&gt;", ">");
				return mValue;
			} else {
				return "";
			}
		} else {
			return "";
		}
	} catch (error) {
		console.warn(error);
		return "";
	}
	return "";
}

function setNodeText(inNode, subTagName, strValue) {
	if (strValue == null) {
		return;
	}
	var mValue = strValue.toString();
	mValue = mValue.replace("<", "&lt;");
	mValue = mValue.replace(">", "&gt;");
	try {
		if (inNode && inNode.getElementsByTagName(subTagName).length == 0) {
			var newNode = gXmlBookData.createElement(subTagName);
			var textnode = gXmlBookData.createTextNode(" ");
			newNode.appendChild(textnode);
			inNode.appendChild(newNode);
		}
		if (inNode.getElementsByTagName(subTagName).length > 0) {
			if (inNode.getElementsByTagName(subTagName)[0].childNodes.length == 0) {
				var textnode = gXmlBookData.createTextNode(" ");
				inNode.getElementsByTagName(subTagName)[0].appendChild(textnode);
			}
			if (inNode.getElementsByTagName(subTagName)[0].childNodes.length > 0) {
				inNode.getElementsByTagName(subTagName)[0].childNodes[0].nodeValue = strValue;
			} else {
				throw "can't accese text node";
			}
		} else {
			throw subTagName + ":not a sub Taget";
		}
	} catch (error) {
		var_dump(error);
		return false;
	}
	return true;
}

function getNodeAttr(inNode, subTagName, attr) {
	try {
		return inNode.getElementsByTagName(subTagName)[0].getAttribute(attr);
	} catch (error) {
		console.warn(error);
		return "";
	}
}

function setNodeAttr(inNode, subTagName, attr, strValue) {
	if ( inNode == null || subTagName === "" || attr === "") {
		return;
	}
	let mValue = strValue.toString();
	try {
		if (inNode.getElementsByTagName(subTagName).length == 0) {
			let newNode = gXmlBookData.createElement(subTagName);
			let textnode = gXmlBookData.createTextNode(" ");
			newNode.appendChild(textnode);
			inNode.appendChild(newNode);
		}
		try {
			inNode.getElementsByTagName(subTagName)[0].setAttribute(attr, mValue);
		} catch (e) {
			newatt = gXmlBookData.createAttribute(attr);
			newatt.nodeValue = mValue;
			inNode.getElementsByTagName(subTagName)[0].setAttributeNode(newatt);
			return true;
		}
	} catch (error) {
		console.error(error);
		return false;
	}
	return true;
}
//根据xmlDocument 对象中的单词序号和单词节点创建单词块
//返回 字符串
function createWordBlockByNode(id, wordNode) {}

function pushNewToList(inArray, strNew) {
	//var isExist=false;
	for (x in inArray) {
		if (inArray[x] == strNew) {
			return;
		}
	}
	inArray.push(strNew);
}
function findFirstCaseInDict(inWord) {
	var output = "?";
	var pali = com_getPaliReal(inWord);
	if (mDict[pali]) {
		for (var iWord in mDict[pali]) {
			{
				if (mDict[pali][iWord].parts) {
					if (mDict[pali][iWord].parts.length > 0) {
						return mDict[pali][iWord].type + "#" + mDict[pali][iWord].gramma;
					}
				}
			}
		}
	}
	return output;
}

function findFirstPartInDict(inWord) {
	var output = "?";
	var pali = com_getPaliReal(inWord);
	if (mDict[pali]) {
		for (var iWord in mDict[pali]) {
			{
				if (mDict[pali][iWord].parts) {
					if (mDict[pali][iWord].parts.length > 0) {
						return mDict[pali][iWord].parts;
					}
				}
			}
		}
	}
	return output;
}



function LangInclude(lang,testLang){
	if(lang.length>0){
		return lang.includes(testLang);
	}else{
		return true;
	}
}


/**
 * 查找某单词的意思
 * @param {string} inWord 要查找的单词拼写
 * @returns 
 */
function findFirstMeanInDict(inWord) {
	let output = "?";
	let pali = com_getPaliReal(inWord);
	if (mDict[pali]) {
		for (const iterator of mDict[pali]) {
			//if(dict_language_enable.indexOf(mDict[pali][iWord].language)>=0)
			{
				if (!isEmpty(iterator.mean)) {
					return iterator.mean.split("$")[0];
				}
			}
		}
	}
	return output;
}
/**
 * 查找某单词在复合词中的意思
 * @param {string} inWord 要查找的单词拼写
 * @returns 
 */
function findFirstPartMeanInDict(inWord) {
	let pali = inWord;
	if (Object.hasOwnProperty.call(mDict, pali)) {
		for (const iterator of mDict[pali]) {
			if(LangInclude(setting['dict.lang'],iterator.language))
			{
				if (iterator.type == ".part." && !isEmpty(iterator.mean)) {
					return iterator.mean.split("$")[0];
				}
			}
		}
		for (const iterator of mDict[pali]) {
			if(LangInclude(setting['dict.lang'],iterator.language))
			{
				if (!isEmpty(iterator.mean)) {
					return iterator.mean.split("$")[0];
				}
			}
		}
	}
	return "?";
}

function findAllMeanInDict(inWord, limit) {
	output = new Array();
	for (var iCurrWord = 0; iCurrWord < g_DictWordList.length; iCurrWord++) {
		if (g_DictWordList[iCurrWord].Pali == inWord) {
			meanList = g_DictWordList[iCurrWord].Mean.split("$");
			for (iMean in meanList) {
				if (meanList[iMean].length > 0) {
					output.push(meanList[iMean]);
					if (output.length > limit) {
						return output;
					}
				}
			}
		}
	}
	return output;
}

//确认对单个词的修改
function modifyApply(sWordId, update_user_dict) {
	let wordIndex = getWordIndex(sWordId);

	let arr_id_word = sWordId.split("-");
	let book = arr_id_word[0].slice(1);
	let paragraph = arr_id_word[1];
	let wId = arr_id_word[2];

	let strApplyTo;
	if (document.getElementById("checkbox_apply_same").checked) {
		strApplyTo = "all";
	} else {
		strApplyTo = "current";
	}

	let wordCurrStatus = 9; //草稿
	if (update_user_dict) {
		wordCurrStatus = 7; //保存为正式
	}

	//关闭单词修改窗口
	closeModifyWindow();
	let x = gXmlBookDataBody.getElementsByTagName("word");

	let msg_data = new Object();
	msg_data.id = sWordId;

	//原来的值
	let sPaliReal = getNodeText(x[wordIndex], "real");
	let sPaliParent = getNodeText(x[wordIndex], "parent");

	//new value
	let sPali = document.getElementById("id_text_pali").value;
	let oldPali = getNodeText(x[wordIndex], "pali");
	if (sPali != oldPali) {
		setNodeText(x[wordIndex], "pali", sPali);
		msg_data.pali = sPali;
	}
	setNodeAttr(x[wordIndex], "pali", "status", wordCurrStatus);

	let sReal = document.getElementById("id_text_real").value;
	let oldReal = getNodeText(x[wordIndex], "real");
	if (sReal != oldReal) {
		setNodeText(x[wordIndex], "real", sReal);
		msg_data.real = sReal;
	}
	setNodeAttr(x[wordIndex], "real", "status", wordCurrStatus);

	let sMeaning = document.getElementById("input_meaning").value;
	let oldMean = getNodeText(x[wordIndex], "mean");
	if (sMeaning != oldMean) {
		setNodeText(x[wordIndex], "mean", sMeaning);
		msg_data.mean = sMeaning;
	}
	setNodeAttr(x[wordIndex], "mean", "status", wordCurrStatus);

	let sParent = document.getElementById("id_text_parent").value;
	let oldParent = getNodeText(x[wordIndex], "parent");
	if (sParent != oldParent) {
		setNodeText(x[wordIndex], "parent", sParent);
		msg_data.parent = sParent;
	}
	setNodeAttr(x[wordIndex], "parent", "status", wordCurrStatus);

	let sParentGrammar = document.getElementById("input_parent_grammar").value;
	let oldParentGrammar = getNodeText(x[wordIndex], "pg");
	if (sParentGrammar != oldParentGrammar) {
		setNodeText(x[wordIndex], "pg", sParentGrammar);
		msg_data.pg = sParentGrammar;
	}
	setNodeAttr(x[wordIndex], "pg", "status", wordCurrStatus);

	let sParentParent = document.getElementById("id_text_prt_prt").value;
	let oldParentParent = getNodeText(x[wordIndex], "parent2");
	if (sParentParent != oldParentParent) {
		setNodeText(x[wordIndex], "parent2", sParentParent);
		msg_data.parent2 = sParentParent;
	}
	setNodeAttr(x[wordIndex], "parent2", "status", wordCurrStatus);

	let sOrg = document.getElementById("input_org").value;
	let oldOrg = getNodeText(x[wordIndex], "org");
	if (sOrg != oldOrg) {
		setNodeText(x[wordIndex], "org", sOrg);
		msg_data.org = sOrg;
	}
	setNodeAttr(x[wordIndex], "org", "status", wordCurrStatus);

	let sOm = document.getElementById("input_om").value;
	let oldOm = getNodeText(x[wordIndex], "om");
	if (oldOm != sOm) {
		setNodeText(x[wordIndex], "om", sOm);
		msg_data.om = sOm;
	}
	setNodeAttr(x[wordIndex], "om", "status", wordCurrStatus);

	let sCase = document.getElementById("input_case").value;
	let oldCase = getNodeText(x[wordIndex], "case");
	if (oldCase != sCase) {
		setNodeText(x[wordIndex], "case", sCase);
		msg_data.case = sCase;
	}
	setNodeAttr(x[wordIndex], "case", "status", wordCurrStatus);

	let bLocked = document.getElementById("input_lock").checked;
	let oldLock = getNodeText(x[wordIndex], "lock");
	if (bLocked != oldLock) {
		setNodeText(x[wordIndex], "lock", bLocked);
		msg_data.lock = bLocked;
	}
	setNodeAttr(x[wordIndex], "lock", "status", wordCurrStatus);

	let txtBookMark = document.getElementById("id_text_bookmark").value;
	let oldBookMarkText = getNodeText(x[wordIndex], "bmt");
	if (oldBookMarkText != txtBookMark) {
		setNodeText(x[wordIndex], "bmt", txtBookMark);
		msg_data.bmt = txtBookMark;
	}
	setNodeAttr(x[wordIndex], "bmt", "status", wordCurrStatus);

	let oldBookMarkColor = getNodeText(x[wordIndex], "bmc");
	if (oldBookMarkColor == "") {
		oldBookMarkColor = "bmc0";
	}
	if (oldBookMarkColor != g_currBookMarkColor) {
		setNodeText(x[wordIndex], "bmc", g_currBookMarkColor);
		msg_data.bmc = g_currBookMarkColor;
		g_currBookMarkColor = "";
	}
	setNodeAttr(x[wordIndex], "bmc", "status", wordCurrStatus);

	let updateNoteNum = false;
	let txtNote = document.getElementById("id_text_note").value;
	let prevNote = getNodeText(x[wordIndex], "note");
	if (prevNote != txtNote) {
		setNodeText(x[wordIndex], "note", txtNote);
		//refreshWordNote(x[wordIndex].parentNode.parentNode);
		msg_data.note = txtNote;
		//updateWordNote(x[wordIndex]);
		updateNoteNum = true;
	}

	let sRalation = $("#id_relation_text").val();
	let oldRalation = getNodeText(x[wordIndex], "rala");
	if (oldRalation != sRalation) {
		setNodeText(x[wordIndex], "rela", sRalation);
		msg_data.rela = sRalation;
		//updateWordRelation(x[wordIndex]);
		updateNoteNum = true;
	}

	{
		setNodeText(x[wordIndex], "status", wordCurrStatus); //自己手动 或 草稿
		msg_data.status = wordCurrStatus;
	}

	user_wbw_push_word(sWordId);

	modifyWordDetailByWordIndex(wordIndex);
	updataWordHeadByIndex(wordIndex);
	if (updateNoteNum) {
		refreshWordNoteDiv(x[wordIndex].parentNode.parentNode);
		refreshNoteNumber();
	}

	//send message
	let d = new Date();
	let msg_doc_id;
	if (doc_info.sendmsg) {
		if (doc_info.parent_id != "") {
			msg_doc_id = doc_info.parent_id;
		} else {
			msg_doc_id = doc_info.doc_id;
		}

		msg_push(1, JSON.stringify(msg_data), msg_doc_id, d.getTime(), book, paragraph);
	}
	//The end of send message

	let objWord = new Object();
	objWord.Pali = getNodeText(x[wordIndex], "real");
	sCase = getNodeText(x[wordIndex], "case");
	let mGramma = sCase.split("#");
	if (mGramma.length >= 2) {
		mType = sCase.split("#")[0];
		mGramma = sCase.split("#")[1];
	} else {
		mType = "";
		mGramma = sCase.split("#")[0];
	}

	//将单词加入内存字典
	var objDictItem = new Object(); /*一个字典元素*/
	objDictItem.id = 0;
	objDictItem.guid = "";
	objDictItem.pali = getNodeText(x[wordIndex], "pali");
	objDictItem.type = mType;
	objDictItem.gramma = mGramma;
	objDictItem.parent = getNodeText(x[wordIndex], "parent");
	objDictItem.mean = getNodeText(x[wordIndex], "mean");
	objDictItem.note = getNodeText(x[wordIndex], "note");
	objDictItem.parts = getNodeText(x[wordIndex], "org");
	objDictItem.partmean = getNodeText(x[wordIndex], "om");
	objDictItem.status = 0;
	objDictItem.dict_name = "Memo";
	objDictItem.language = "zh";
	objDictItem.confidence = 100;
	if (objDictItem.type == "" || objDictItem.type.indexOf("?") != -1) {
		objDictItem.confidence = objDictItem.confidence * 0.9;
	}
	if (objDictItem.type != ".un." && objDictItem.type != ".comp.") {
		if (objDictItem.gramma == "" || objDictItem.gramma.indexOf("?") != -1 || objDictItem.gramma.indexOf("$") != 0) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.mean == "" || objDictItem.mean.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.partmean == "" || objDictItem.partmean.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
	}
	if (objDictItem.parts == "" || objDictItem.parts.indexOf("?") != -1) {
		objDictItem.confidence = objDictItem.confidence * 0.9;
	}
	if (!mDict[objDictItem.pali]) {
		mDict[objDictItem.pali] = new Array();
	}
	//insert to top of memory dict
	mDict[objDictItem.pali].unshift(objDictItem);

	let parts = getNodeText(x[wordIndex], "org");
	let partmean = getNodeText(x[wordIndex], "om");

	//add parent infomation

	switch (mType) {
		case ".n.":
			mType = ".n:base.";
			mGramma = mGramma.split("$")[0];
			if (mGramma == ".m." || mGramma == ".f." || mGramma == ".nt.") {
			} else {
				mGramma = "";
			}
			break;
		case ".adj.":
			mType = ".adj:base.";
			mGramma = "";
			break;
		case ".ti.":
			mType = ".ti:base.";
			mGramma = "";
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
			mType = ".v:base.";
			mGramma = "";
			break;
	}

	//将单词base加入内存字典
	if (getNodeText(x[wordIndex], "parent") != "") {
		var objDictItem = new Object(); /*一个字典元素*/
		objDictItem.id = 0;
		objDictItem.guid = "";
		objDictItem.pali = getNodeText(x[wordIndex], "parent");
		objDictItem.type = mType;
		objDictItem.gramma = mGramma;
		objDictItem.parent = "";
		objDictItem.mean = removeFormulaB(getNodeText(x[wordIndex], "mean"), "[", "]");
		objDictItem.mean = removeFormulaB(objDictItem.mean, "{", "}");
		objDictItem.note = getNodeText(x[wordIndex], "note");

		//remove the "[***]" in the end
		let d_factors = getNodeText(x[wordIndex], "org");
		let fc = d_factors.split("+");
		if (fc.length > 0 && fc[fc.length - 1].slice(0, 1) == "[" && fc[fc.length - 1].slice(-1) == "]") {
			fc.pop();
		}
		objDictItem.parts = fc.join("+");
		let fm = getNodeText(x[wordIndex], "om").split("+");
		fm.length = fc.length;
		objDictItem.partmean = fm.join("+");

		objDictItem.status = 0;
		objDictItem.confidence = 100;
		if (objDictItem.type == "" || objDictItem.gramma.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.gramma == "" || objDictItem.gramma.indexOf("?") != -1 || objDictItem.gramma.indexOf("$") != 0) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.mean == "" || objDictItem.gramma.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.parts == "" || objDictItem.gramma.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (objDictItem.partmean == "" || objDictItem.gramma.indexOf("?") != -1) {
			objDictItem.confidence = objDictItem.confidence * 0.9;
		}
		if (!mDict[objDictItem.pali]) {
			mDict[objDictItem.pali] = new Array();
		}
		objDictItem.dict_name = "Memo";
		objDictItem.language = "zh";
		if (!mDict[objDictItem.pali]) {
			mDict[objDictItem.pali] = new Array();
		}
		mDict[objDictItem.pali].unshift(objDictItem);
	}
	// The end of memory dictionary

	//apply all
	let searchBegin = 0;
	let searchEnd = 0;
	switch (strApplyTo) {
		case "all":
			searchBegin = 0;
			searchEnd = x.length;
			break;
		case "up":
			searchBegin = 0;
			searchEnd = wordIndex;
			break;
		case "down":
			searchBegin = wordIndex;
			searchEnd = x.length;
			break;
	}
	if (strApplyTo != "current") {
		//sPaliWord = x[sWordId].getElementsByTagName("pali")[0].childNodes[0].nodeValue;
		let iSameWordCount = 0;
		setNodeText(x[wordIndex], "pali", sPali); //拼寫顯示修改僅僅應用當前詞——Kosalla

		for (iSearch = searchBegin; iSearch < searchEnd; iSearch++) {
			if (sWordId != iSearch) {
				//xmlNotePali = x[i].getElementsByTagName("pali")[0].childNodes[0].nodeValue;
				xmlNotePali = getNodeText(x[iSearch], "real");
				if (xmlNotePali == sPaliReal) {
					if (getNodeText(x[iSearch], "lock") != "true") {
						setNodeText(x[iSearch], "real", sReal);
						setNodeText(x[iSearch], "parent", sParent);
						setNodeText(x[iSearch], "mean", sMeaning);
						setNodeText(x[iSearch], "org", sOrg);
						setNodeText(x[iSearch], "om", sOm);
						setNodeText(x[iSearch], "case", sCase);
						setNodeText(x[iSearch], "bmc", g_currBookMarkColor);
						setNodeText(x[iSearch], "note", txtNote);
						switch (editor_word_status(x[iSearch])) {
							case 0:
							case 1:
							case 2:
							case 3:
							case 4:
							case 6:
								editor_word_status(x[iSearch], 5);
								break;
						}
						user_wbw_push_word(getNodeText(x[iSearch], "id"));
						modifyWordDetailByWordIndex(iSearch);
						updataWordHeadByIndex(iSearch);
						console.log("updata:" + iSearch + "<br />", 0);
						iSameWordCount = iSameWordCount + 1;
					}
				}
				xmlNoteParent = getNodeText(x[iSearch], "parent");
				if (
					xmlNotePali != sPaliReal &&
					xmlNoteParent == sPaliParent &&
					xmlNoteParent != "" &&
					xmlNoteParent != " "
				) {
					if (getNodeText(x[iSearch], "lock") != "true") {
						setNodeText(x[iSearch], "mean", sMeaning);
						user_wbw_push_word(getNodeText(x[iSearch], "id"));
						modifyWordDetailByWordIndex(iSearch);
						updataWordHeadByIndex(iSearch);
						iSameWordCount = iSameWordCount + 1;
					}
				}
			}
		}
		var_dump("same word:" + (iSameWordCount - 1));
	}
	refreshBookMark();
	user_wbw_commit();
	refreshNoteNumber();
	//上传到用户字典
	if (update_user_dict) {
		upload_to_my_dict();
	}
}

function getWordIndex(GUID) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	for (var iWord = 0; iWord < xAllWord.length; iWord++) {
		if (getNodeText(xAllWord[iWord], "id") == GUID) {
			return iWord;
		}
	}
	return -1;
}

function addFavorite() {
	g_isDictFavo = !g_isDictFavo;
	if (g_isDictFavo) {
		document.getElementById("temp").innerHTML = "★";
	} else {
		document.getElementById("temp").innerHTML = "☆";
	}
}
//用单词表中的一个记录更改经文中的单词
function updataWord(id) {
	var debugstr;
	try {
		sPali = document.getElementById("wlpali" + id).value;
		sReal = document.getElementById("wlreal" + id).value;
		sOrg = document.getElementById("wlorg" + id).value;
		sMean = document.getElementById("wlmean" + id).value;
		sCase = document.getElementById("wlcase" + id).value;

		var m_WordIdList = new Array();
		m_WordIdList = wordList[id].wordid.toString().split("$");
		var xAllWord = gXmlBookDataBody.getElementsByTagName("word");

		for (indexWordList = 0; indexWordList < m_WordIdList.length; indexWordList++) {
			//将修改结果保存到xml DOM中
			if (m_WordIdList[indexWordList] >= 0) {
				setNodeText(xAllWord[m_WordIdList[indexWordList]], "pali", sPali);
				setNodeText(xAllWord[m_WordIdList[indexWordList]], "real", sReal);
				setNodeText(xAllWord[m_WordIdList[indexWordList]], "org", sOrg);
				setNodeText(xAllWord[m_WordIdList[indexWordList]], "mean", sMean);
				setNodeText(xAllWord[m_WordIdList[indexWordList]], "case", sCase);
				var sId = getNodeText(xAllWord[m_WordIdList[indexWordList]], "id");
				var wordDetail = renderWordDetailById(sId);
				var strDetailName = "detail" + sId;
				document.getElementById(strDetailName).innerHTML = wordDetail;
				updataWordHeadById(sId);
			}
		}
		var_dump("" + m_WordIdList.length + "");
		document.getElementById("wlApply" + id).disabled = true;
	} catch (e) {
		var_dump(e);
	}
}

//比较两个词是否一样
function compareWordInList(word1, word2) {
	var sItems1 = new Array();
	sItem1 = word1.split(";");
	var sItems2 = new Array();
	sItem2 = word2.split(";");
	var sConcat1 = sItem1[0] + sItem1[1] + sItem1[2] + sItem1[3];
	var sConcat2 = sItem2[0] + sItem1[1] + sItem1[2] + sItem1[3];
	if (sConcat1 == sConcat2) {
		return true;
	} else {
		return false;
	}
}

function sortWordList(strOrderby) {
	g_wordListOrderby = strOrderby;
	g_wordListOrder = !g_wordListOrder;
	refreshWordList();
}
function CountVocabulary() {
	var sPali = "";
	var sOrg = "";
	var sMean = "";
	var sCase = "";
	var wordList1 = new Array();
	var arrCombinWord = new Array();
	var arrCombinWordOrder = new Array();

	var arrCount = new Array();
	var iCount = 0;
	var sTableWordList = "";
	var arrowCount = "";
	var arrowPali = "";
	var arrowReal = "";

	//提取所有词
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	if (xAllWord.length == 0) {
		return "no word data.";
	}
	var outWordList = "";
	for (iword = 0; iword < xAllWord.length; iword++) {
		var objWord = new Object();
		objWord.id = getNodeText(xAllWord[iword], "id");
		objWord.pali = getNodeText(xAllWord[iword], "pali");
		objWord.real = getNodeText(xAllWord[iword], "real");
		objWord.mean = getNodeText(xAllWord[iword], "mean");
		objWord.org = getNodeText(xAllWord[iword], "org");
		objWord.om = getNodeText(xAllWord[iword], "om");
		objWord.case = getNodeText(xAllWord[iword], "case");
		objWord.index = iword;
		objWord.count = 1;
		objWord.wordid = iword;
		if (objWord.real != "") {
			addWordToWordList(wordList1, objWord);
		}
	}
	return wordList1.length;
}
//生成单词列表
function makeWordList() {
	var sPali = "";
	var sOrg = "";
	var sMean = "";
	var sCase = "";

	var arrCombinWord = new Array();
	var arrCombinWordOrder = new Array();

	var arrCount = new Array();
	var iCount = 0;
	var sTableWordList = "";
	var arrowCount = "";
	var arrowPali = "";
	var arrowReal = "";

	//提取所有词
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	if (xAllWord.length == 0) {
		return "no word data.";
	}
	var outWordList = "";
	for (iword = 0; iword < xAllWord.length; iword++) {
		var objWord = new Object();
		objWord.id = getNodeText(xAllWord[iword], "id");
		objWord.pali = getNodeText(xAllWord[iword], "pali");
		objWord.real = getNodeText(xAllWord[iword], "real");
		objWord.mean = getNodeText(xAllWord[iword], "mean");
		objWord.org = getNodeText(xAllWord[iword], "org");
		objWord.om = getNodeText(xAllWord[iword], "om");
		objWord.case = getNodeText(xAllWord[iword], "case");
		objWord.index = iword;
		objWord.count = 1;
		objWord.wordid = iword;
		if (objWord.real != "") {
			addWordToWordList(wordList, objWord);
		}
	}
	switch (g_wordListOrderby) {
		case "count":
			if (g_wordListOrder) {
				wordList.sort(sortCountDesc);
				arrowCount = "↓";
			} else {
				wordList.sort(sortCountAsc);
				arrowCount = "↑";
			}
			break;
		case "pali":
			if (g_wordListOrder) {
				wordList.sort(sortPaliDesc);
				arrowPali = "↓";
			} else {
				wordList.sort(sortPaliAsc);
				arrowPali = "↑";
			}
			break;
		case "real":
			if (g_wordListOrder) {
				wordList.sort(sortRealDesc);
				arrowReal = "↓";
			} else {
				wordList.sort(sortRealAsc);
				arrowReal = "↑";
			}
			break;
	}

	sTableWordList = sTableWordList + "<table border='0' cellpadding='3' ><tr  class='h'>";
	sTableWordList = sTableWordList + "<th>序号</th>";
	sTableWordList =
		sTableWordList + '<th><a herf="" onclick="sortWordList(\'count\')">计数' + arrowCount + "</a></th>";
	sTableWordList = sTableWordList + '<th><a herf="" onclick="sortWordList(\'pali\')">Pali' + arrowPali + "</a></th>";
	sTableWordList = sTableWordList + '<th><a herf="" onclick="sortWordList(\'real\')">Real' + arrowReal + "</a></th>";
	sTableWordList = sTableWordList + "<th>原型</th>";
	sTableWordList = sTableWordList + "<th>译文</th>";
	sTableWordList = sTableWordList + "<th>语法</th>";
	sTableWordList =
		sTableWordList +
		"<th><button type='button' id='btnApplyAll' onclick=\"applyAllWordInList(this)\" disabled>Apply All</button></th></tr>";

	for (var i = 0; i < wordList.length; i++) {
		objWord = wordList[i];
		sTableWordList += "<tr><td>" + i + "</td>";
		sTableWordList += "<td id='tablepali" + i + "'>" + objWord.count + "</td>";
		//sTableWordList += "<td>" +objWord.pali + "</td>";
		//sTableWordList += "<td>" +objWord.real + "</td>";
		sTableWordList +=
			'<td><input id="wlpali' +
			i +
			'" onkeyup="wordListItemChanged(\'wlApply' +
			i +
			"')\" value = '" +
			objWord.pali +
			"' />";
		sTableWordList +=
			'<td><input id="wlreal' +
			i +
			'" onkeyup="wordListItemChanged(\'wlApply' +
			i +
			"')\" value = '" +
			objWord.real +
			"' /></td>";
		sTableWordList +=
			'<td><input id="wlorg' +
			i +
			'" onkeyup="wordListItemChanged(\'wlApply' +
			i +
			"')\" value = '" +
			objWord.org +
			"' />";
		sTableWordList +=
			'<td><input id="wlmean' +
			i +
			'" onkeyup="wordListItemChanged(\'wlApply' +
			i +
			"')\" value = '" +
			objWord.mean +
			"' /></td>";
		sTableWordList +=
			'<td><input id="wlcase' +
			i +
			'" onkeyup="wordListItemChanged(\'wlApply' +
			i +
			"')\" value = '" +
			objWord.case +
			"' /></td>";
		sTableWordList +=
			'<td><button id="wlApply' +
			i +
			'" onclick="updataWord(\'' +
			i +
			"')\" type='button' disabled >Apply</button></td></tr>";
	}

	sTableWordList = sTableWordList + "</table>";
	g_countWordList = wordList.length;
	return sTableWordList;
}
function sortCountDesc(a, b) {
	return a.count - b.count;
}
function sortCountAsc(a, b) {
	return b.count - a.count;
}

function sortPaliDesc(a, b) {
	return a.pali.localeCompare(b.pali);
}
function sortPaliAsc(a, b) {
	return b.pali.localeCompare(a.pali);
}

function sortRealDesc(a, b) {
	return a.real.localeCompare(b.real);
}
function sortRealAsc(a, b) {
	return b.real.localeCompare(a.real);
}

function addWordToWordList(wordArray, newWord) {
	var index = -1;

	for (var i = 0; i < wordArray.length; i++) {
		if (wordArray[i].pali == newWord.pali) {
			if (wordArray[i].real == newWord.real) {
				if (wordArray[i].mean == newWord.mean) {
					if (wordArray[i].org == newWord.org) {
						if (wordArray[i].om == newWord.om) {
							if (wordArray[i].case == newWord.case) {
								index = i;
								break;
							}
						}
					}
				}
			}
		}
	}
	if (index >= 0) {
		wordArray[index].count++;
		wordArray[index].wordid += "$" + newWord.index;
	} else {
		wordArray.push(newWord);
	}
}

function refreshWordList() {
	document.getElementById("word_table_inner").innerHTML = makeWordList();
}

function wordListItemChanged(btnApplyId) {
	try {
		document.getElementById(btnApplyId).disabled = false;
		document.getElementById("btnApplyAll").disabled = false;
	} catch (e) {
		alert(e);
	}
}

function applyAllWordInList() {
	for (var i = 0; i < g_countWordList; i++) {
		if (document.getElementById("wlApply" + i).disabled == false) {
			updataWord(i);
		}
	}
	document.getElementById("btnApplyAll").disabled = true;
}

function tran_edit(iSutta, iPar, iTran) {
	gTextEditMediaType = "translate";
	gSutta = iSutta;
	gPar = iPar;
	gTran = iTran;
	var tranText = getTranText(iSutta, iPar, iTran);
	document.getElementById("id_text_edit_area").value = tranText;
	document.getElementById("id_text_edit_form").style.display = "block";
}

/*make book mark*/
function bookMark() {
	var colorStyle = "";
	var strBookMark = "";
	var iWordCount = 0;

	xWord = gXmlBookDataBody.getElementsByTagName("word");
	/*遍历所有单词*/
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	var iWordCount = 0;
	for (iBlock = 0; iBlock < xBlock.length; iBlock++) {
		var xData = xBlock[iBlock].getElementsByTagName("data")[0];
		parInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		book = getNodeText(parInfo, "book");
		parNo = getNodeText(parInfo, "paragraph");

		xWord = xData.getElementsByTagName("word");
		for (k = 0; k < xWord.length; k++) {
			strWordPali = getNodeText(xWord[k], "pali");
			strWordMean = getNodeText(xWord[k], "mean");
			strWordId = getNodeText(xWord[k], "id");
			strWordBookMarkColor = getNodeText(xWord[k], "bmc");
			if (strWordBookMarkColor.length > 0) {
				if (strWordBookMarkColor.substr(3, 1) != 0) {
					/*屏蔽显示注释的Bug*/
					var markString = strWordBookMarkColor.substr(3, 1);
					colorStyle = "bookmarkcolor" + markString;
					var bookMarkId = "w" + strWordId;
					strBookMark +=
						'<p class="bm' +
						markString +
						"\"><span class='bookmarkcolorblock , " +
						colorStyle +
						"'>" +
						markString +
						"</span>";
					//strBookMark += "<a href=\"#"+bookMarkId+"\">"+strWordPali+":"+strWordMean.substr(3,10)+"</a></p>";
					strBookMark +=
						"<a onclick=\"editor_goto_link('" +
						book +
						"'," +
						parNo +
						",'" +
						bookMarkId +
						"')\">" +
						strWordPali +
						":" +
						strWordMean.substr(3, 10) +
						"</a></p>";
				}
			}
			iWordCount++;
		}
	}

	return strBookMark;
}
function setBookmarkVisibility_all() {
	var book_MarkId_array = new Array();
	var book_MarkClass_array = new Array();
	for (bookMark_i in g_bookMark_array) {
		book_MarkId_array.push("B_Bookmark_" + g_bookMark_array[bookMark_i]);
		book_MarkClass_array.push("bm" + g_bookMark_array[bookMark_i]);
	}
	var isVisible = document.getElementById("B_Bookmark_All").checked;
	for (bookMark_j in book_MarkId_array) {
		eval("document.getElementById('" + book_MarkId_array[bookMark_j] + "').checked=isVisible");
		getStyleClass(book_MarkClass_array[bookMark_j]).style.display = isVisible ? "block" : "none";
		var book_mark_spanId = "";
		book_mark_spanId = book_MarkId_array[bookMark_j] + "_span";
		if (isVisible == true) {
			eval("lock_key(" + book_mark_spanId + ".id,'on','" + book_MarkId_array[bookMark_j] + "','bookmark')");
		} else {
			eval("lock_key(" + book_mark_spanId + ".id,'off','" + book_MarkId_array[bookMark_j] + "','bookmark')");
		}
	}
}
function setBookmarkVisibility(className, controlID) {
	var isVisible = document.getElementById(controlID).checked;
	getStyleClass(className).style.display = isVisible ? "flex" : "none";
}
/*刷新书签*/
function refreshBookMark() {
	document.getElementById("navi_bookmark_inner").innerHTML = bookMark();
}
/*Apply all system match words*/
function applyAllSysMatch() {
	var iWordCount = 0;
	var iModified = 0;

	xWord = gXmlBookDataBody.getElementsByTagName("word");
	/*遍历此经中所有单词*/
	for (k = 0; k < xWord.length; k++) {
		{
			if (getNodeText(xWord[k], "bmc") == "bmca") {
				setNodeText(xWord[k], "bmc", "bmc0");
				updataWordBodyByElement(xWord[k]);
				iModified++;
			}
		}
		iWordCount++;
	}

	if (iWordCount > 0) {
		document.getElementById("navi_bookmark_inner").innerHTML = bookMark();
	}
	var_dump(iModified + "个单词被确认。");
}

function setUseMode(strUseMode) {
	var multi_trans_strUseMode = gLocal.gui.edit;
	if (strUseMode == "Read") {
		multi_trans_strUseMode =
			'<svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_reader_mode"></use></svg>';
	} else {
		multi_trans_strUseMode =
			'<svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_mode_edit"></use></svg>';
	}
	document.getElementById("use_mode").innerHTML =
		multi_trans_strUseMode + '<svg class="small_icon"><use xlink:href="svg/icon.svg#ic_down"></use></svg>';
	switch (strUseMode) {
		case "Read":
			g_useMode = "read";
			getStyleClass("edit_tran_button").style.display = "none";
			getStyleClass("edit_tool").style.display = "none";
			getStyleClass("tran_input").style.display = "none";
			break;
		case "Edit":
			g_useMode = "edit";
			getStyleClass("edit_tran_button").style.display = "inline";
			getStyleClass("edit_tool").style.display = "inline";
			getStyleClass("tran_input").style.display = "none";
			break;
		case "Translate":
			g_useMode = "translate";
			getStyleClass("edit_tran_button").style.display = "inline";
			getStyleClass("tran_input").style.display = "block";
			break;
	}

	if (gXmlBookDataBody != null) {
		var mWordNode = gXmlBookDataBody.getElementsByTagName("word");
		/*遍历所有单词*/
		for (k = 0; k < mWordNode.length; k++) {
			modifyWordDetailByWordIndex(k);
		}
	}

	document.getElementById("menuUseMode").style.display = "none";
}

function setUseMode_Static(strUseMode) {
	if (strUseMode == "chanting") {
		document.getElementById("use_mode").innerHTML = "Chanting";
		g_useMode = "chanting";
		getStyleClass("chanting_enter").style.display = "block";
	} else {
		document.getElementById("use_mode").innerHTML = "Read";
		g_useMode = "read";
		getStyleClass("chanting_enter").style.display = "none";
	}

	dropbtnClick("menu01");
}

function hiddenMenu() {
	getStyleClass("dropdown-content").style.display = "none";
}

function sortMeanByDictOrder(wa, wb) {
	var w1 = wa.split("$")[0];
	var w2 = wb.split("$")[0];
	var index1 = wa.split("$")[1];
	var index2 = wb.split("$")[1];
	//order by dictionary index
	order = w1 - w2;
	if (order == 0) {
		//if dictionay is same order by index of meaning array
		order = index1 - index2;
	}

	return order;
}

function sortMeanByLanguageOrder(wa, wb) {
	var w1 = wa.split("$")[4];
	var w2 = wb.split("$")[4];
	//order by dictionary index
	order = w1 - w2;
	return order;
}

function removeSameWordInArray(wordList) {
	var output = new Array();

	for (indexWord in wordList) {
		oneWord = wordList[indexWord].split("$");
		var isExist = false;
		for (x in output) {
			if (output[x].word == oneWord[3]) {
				isExist = true;
			}
		}
		if (!isExist) {
			var objWord = new Object();
			objWord.word = oneWord[3];
			objWord.parent = oneWord[2];
			output.push(objWord);
		}
	}
	return output;
}
