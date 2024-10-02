/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function editor_layout_init() {}

function layoutWordHeadCode(showTo, obj) {
	menu_view_script(showTo, obj.value);
}

function menu_view_script(showto, code) {
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");

	if (showto == 0 && code == "org") {
		for (iWord = 0; iWord < xAllWord.length; iWord++) {
			wordId = getNodeText(xAllWord[iWord], "id");
			xDiv = document.getElementById("wb" + wordId);
			xPali1 = xDiv.getElementsByClassName("paliword1")[0];
			xPali1.innerHTML = getNodeText(xAllWord[iWord], "pali");
		}
		return;
	}
	if (showto == 1) {
		if (code == "none") {
			$(".paliword2").css("display", "none");
			return;
		} else {
			$(".paliword2").css("display", "block");
		}
	}

	var xmlHttp = null;
	var xmlText = "";

	var xmlScript = null;

	if (window.XMLHttpRequest) {
		// code for IE7, Firefox, Opera, etc.
		xmlHttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// code for IE6, IE5
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (xmlHttp != null) {
		var d = new Date();
		var strLink = "./spt_get.php?code=" + code;
		xmlHttp.open("GET", strLink, false);
		xmlHttp.send(null);
		xmlText = xmlHttp.responseText;
		//xmlDict=xmlHttp.responseXML;
	} else {
		alert("Your browser does not support XMLHTTP.");
	}

	if (window.DOMParser) {
		parser = new DOMParser();
		xmlScript = parser.parseFromString(xmlText, "text/xml");
	} // Internet Explorer
	else {
		xmlScript = new ActiveXObject("Microsoft.XMLDOM");
		xmlScript.async = "false";
		xmlScript.loadXML(xmlText);
	}

	var arrPaliWords = "";
	var xScriptWord = xmlScript.getElementsByTagName("word");
	var xPaliWords = document.getElementsByClassName("paliword");

	for (iWord = 0; iWord < xAllWord.length; iWord++) {
		arrPaliWords = arrPaliWords + "$" + getNodeText(xAllWord[iWord], "real");
	}
	for (i = 0; i < xScriptWord.length; i++) {
		var src = getNodeText(xScriptWord[i], "src");
		var dest = getNodeText(xScriptWord[i], "dest");
		eval("arrPaliWords = arrPaliWords.replace(/" + src + "/g, dest);");
	}
	// document.getElementById("scriptinner").innerHTML=arrPaliWords;

	var arrDestWords = arrPaliWords.split("$");
	for (iWord = 0; iWord < xAllWord.length; iWord++) {
		wordId = getNodeText(xAllWord[iWord], "id");
		xDiv = document.getElementById("wb" + wordId);
		if (xDiv) {
			if (showto == 0) {
				xPali = xDiv.getElementsByClassName("paliword1")[0];
			} else {
				xPali = xDiv.getElementsByClassName("paliword2")[0];
			}
			if (arrDestWords[iWord + 1].length > 0) {
				xPali.innerHTML = arrDestWords[iWord + 1];
			} else {
				xPali.innerHTML = getNodeText(xAllWord[iWord], "pali");
			}
		}
	}

	if (xmlScript == null) {
		alert("error:can not load dict.");
		return;
	}
}

function editro_layout_loadStyle() {
	var strStyle = getNodeText(gXmlBookDataHead, "style");
	document.getElementById("id_layout_style").value = strStyle;
	document.getElementById("mycss").innerHTML = strStyle;
}

function editor_layout_applyNewStyle() {
	var strStyle = document.getElementById("id_layout_style").value;
	document.getElementById("mycss").innerHTML = strStyle;
	setNodeText(gXmlBookDataHead, "style", strStyle);
}

//自动将逐词译段落切分为句子
function layout_wbw_auto_cut() {
	var xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (var iBlock = 0; iBlock < xBlock.length; iBlock++) {
		xmlParInfo = xBlock[iBlock].getElementsByTagName("info")[0];
		xmlParData = xBlock[iBlock].getElementsByTagName("data")[0];
		var mId = getNodeText(xmlParInfo, "id");
		var par_num = getNodeText(xmlParInfo, "paragraph");
		var type = getNodeText(xmlParInfo, "type");

		if (type == "wbw") {
			var splited = getNodeText(xmlParInfo, "splited");
			if (splited != 1) {
				var Note_Mark = 0;
				var Note_Mark1 = 0;
				var Note_Mark2 = 0;
				var sent_gramma_i = 0;
				var word_length_count = 0;
				var sent_num = 0;
				var arr_Para_ID = new Array();
				var allWord = xmlParData.getElementsByTagName("word");
				for (var iWord = 0; iWord < allWord.length; iWord++) {
					var wID = getNodeText(allWord[iWord], "id");
					var wPali = getNodeText(allWord[iWord], "pali");
					var wReal = getNodeText(allWord[iWord], "real");
					var wType = getNodeText(allWord[iWord], "type");
					var wGramma = getNodeText(allWord[iWord], "gramma");
					var wCase = getNodeText(allWord[iWord], "case");
					var wUn = getNodeText(allWord[iWord], "un");
					var wStyle = getNodeText(allWord[iWord], "style");

					if ((wType == "" || wType == "?") && wCase != "") {
						wType = wCase.split("#")[0];
					}
					word_length_count += wPali.length;
					
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
						Note_Mark1 = 1;
					} else if ((pre_pali_spell == ")" || wPali == ")") && Note_Mark1 == 1) {
						Note_Mark1 = 0;
					} else {
					}
					if (next_pali_spell == "[" || wPali == "[") {
						Note_Mark2 = 1;
					} else if ((pre_pali_spell == "]" || wPali == "]") && Note_Mark2 == 1) {
						Note_Mark2 = 0;
					} else {
					}
					Note_Mark = Note_Mark1 + Note_Mark2
					var isEndOfSen = false;
					if (
						wPali == "." &&
						iWord >= 1 &&
						isNaN(pre_pali_spell) &&
						iWord != allWord.length - 1 &&
						Note_Mark == 0
					) {
						//以.結尾且非註釋
						if (next_pali_spell != "(" && next_pali_spell != "[") {
							isEndOfSen = true;
						}
					} else if (
						/*else if(wPali=="," && iWord>=1 && isNaN(pre_pali_spell) && iWord!=allWord.length-1 && pre_pali_type==".v." && Note_Mark==0){
						isEndOfSen=true;
					}导致自动匹配前后，句子切分不一致，注释*/
						wPali == "–" &&
						allWord.length >= iWord + 2 &&
						next_pali_spell == "‘" &&
						iWord != allWord.length - 1 &&
						Note_Mark == 0
					) {
						isEndOfSen = true;
					} else if (allWord.length >= iWord + 2 && iWord != allWord.length - 1 && Note_Mark == 0) {
						//以!或?或;結尾
						if (/*wPali=="!" || */ wPali == ";" || wPali == "?") {
							if (next_pali_spell != "(" && next_pali_spell != "[") {
								isEndOfSen = true;
							}
						}
					}
					if (isEndOfSen == true) {
						var wEnter = getNodeText(allWord[iWord], "enter");
						if (wEnter == "" || wEnter == 0) {
							setNodeText(allWord[iWord], "enter", "1");
						}
						var sent_ID = "sent_" + par_num + "_" + sent_num;
						sent_num += 1;
						word_length_count = 0;
						sent_gramma_i = 0;
						arr_Para_ID.push(wID);
					}

					if (wPali == ",") {
						if (next_pali_Case.lastIndexOf(".voc.") != -1 || pre_pali_Case.lastIndexOf(".voc.") != -1) {
							sent_gramma_i += 0;
						} else {
							sent_gramma_i += 1;
						}
					}
					if (wType == ".v:ind." && allWord.length >= iWord + 2 && next_pali_spell != ",") {
						sent_gramma_i += 1;
					} else if (
						wType == ".v." &&
						allWord.length >= iWord + 2 &&
						next_pali_spell != "." &&
						next_pali_spell != ","
					) {
						sent_gramma_i += 1;
					}
				} //段落结束

				//重绘
				//_display_sbs=1;
				//updateWordParBlockInner(xBlock[iBlock]);
				var sent_ID = "sent_" + par_num + "_" + sent_num;
				arr_Para_ID.push(wID);
				arr_par_sent_num.push(sent_ID);
				g_arr_Para_ID[par_num] = arr_Para_ID;

				//设置已经切分标志
				setNodeText(xmlParInfo, "splited", "1");
			}
		}
	}
}
//channel显示隐藏
function channelDisplay(obj) {
	let id = $(obj).attr("channel_id");
	$(".trans_text_block[channel_id='" + id + "']").toggle();
	let allLen = $(obj).parent().parent().children("li").length;
	let checkLen = $(obj).parent().parent().children("li").children("input:checked").length;
	if(checkLen==0){
		$("#layout_channel_display_all").prop("checked",false);

	}else if(allLen===checkLen){
		$("#layout_channel_display_all").prop("checked",true);
	}else{
		$("#layout_channel_display_all").prop({checked:false,indeterminate:true});
	}
}
//全选或全不选
function channelDisplayAll(obj) {
	let all = $(obj).prop("checked");
	if(all){
		$("#layout_channel_display").children("li").children("input").prop("checked",true);
		$(".trans_text_block").show();
	}else{
		$("#layout_channel_display").children("li").children("input").prop("checked",false);
		$(".trans_text_block").hide();
	}
}

function renderChannelList(){
	let html ="";
	html += "<input type='checkbox' id='layout_channel_display_all' checked channel_id='-1' onclick=\"channelDisplayAll(this)\" />全选</li>"
	html +="<ul id='layout_channel_display'>";
	html += "<li><input type='checkbox' checked channel_id='0' onclick=\"channelDisplay(this)\" />其他</li>"
	if (_my_channal != null) {
		for (const iterator of _my_channal) {
			html += "<li><input type='checkbox' checked channel_id='"+iterator.id+"' onclick=\"channelDisplay(this)\" />"+iterator.name+"</li>"
		}
		html +="</ul>";
		$("#layout_channel_innter").html(html);
	}
}