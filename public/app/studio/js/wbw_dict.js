//var ApiWbwLookup = "./dict_find_one.php";
var ApiWbwLookup = "/api/v2/wbwlookup";

/**
 * 
 * @param {array} words 要查询的单词数组
 * @param {function} callback 
 */
function dictFetch(words,callback=null){

	//将单词加入查询队列
	//0 已经查询完，1，正在查询 2，等待查询
	for (const word of words) {
		if (!isEmpty(word) && (typeof mDictQueue[word] == "undefined" || typeof mDict[word] == "undefined" )) {
			mDictQueue[word] = 2;
		}
	}

	for (const word in mDictQueue) {
		if (Object.hasOwnProperty.call(mDictQueue, word)) {
			//上次的查询尚未完成，退出
			if(mDictQueue[word] == 1) return;
		}
	}

	let wq = new Array();
	for (const word in mDictQueue) {
		if (Object.hasOwnProperty.call(mDictQueue, word)) {
			//上次的查询尚未完成，退出
			if(mDictQueue[word] == 2){
				wq.push(word);
				mDictQueue[word] = 1;
			}
		}
	}

	if (wq.length==0) {
		//队列为空，没有要查询的词。
		if(callback){
			callback();
		}
	} else {
		console.log('lookup',wq);
		//查询队列不为空，查字典
		gCurrLookupWord = words[0];
		$.ajax({
			type: "GET",
			url: ApiWbwLookup,
			dataType: "json",
			data: {
				word:wq.join(),
			},
		}).done(function (data) {
			if(data.ok){
				for (const word in mDictQueue) {
					if (Object.hasOwnProperty.call(mDictQueue, word)) {
						//设置正在查询的为已经查询过的
						if(mDictQueue[word] == 1){
							mDictQueue[word] = 0;
						}
					}
				}
				inline_dict_parse(data.data.rows);
				if(callback){
					callback();
				}					
			}else{
				alert(data.message);
			}

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

const db_name = "../../tmp/user/wbw.db3";
//载入我的字典中的各位公式
function load_my_formula() {
	//如果有内存字典里面没有的单词，查询
	console.log("load_my_formula - dict_find_one.php");
	$.get(
		ApiWbwLookup,
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





//解析字典数据
function inline_dict_parse(data) {
	let worddata = data;
	if (worddata.length > 0) {
		//如果有数据 解析查询数据
		let spell = new Array();
		for (const iterator of worddata) {
			if (mDict[iterator.word]) {
				spell[iterator.word] = 1;
			} else {
				spell[iterator.word] = 0;
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
			if (spell[iterator.word] == 0) {
				mDict[iterator.word].push(iterator);
				mDictQueue[iterator.word] = 0;
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


//查词
function lookupNewWord(param,callback){
	$.get(
		ApiWbwLookup,
		param,
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
			callback();
		}
	);
}

/**
 * 根据拆分，自动给出拆分意思
 * @param {string} factors 
 * @returns 
 */
function getAutoFactorMeaning(factors){
	let fm = new Array();
	for (const part of factors.split('+')) {
		fm.push($.trim(findFirstPartMeanInDict(part))); 
	}
	return fm.join('+');
}


//自动查词典
var _para_list = new Array();

function AutoLookup() {
	let book;
	let para = new Array();
	xBlock = gXmlBookDataBody.getElementsByTagName("block");
	for (const block of xBlock) {
		xmlParInfo = block.getElementsByTagName("info")[0];
		xmlParData = block.getElementsByTagName("data")[0];
		book = getNodeText(xmlParInfo, "book");
		paragraph = getNodeText(xmlParInfo, "paragraph");
		
		let xWord = block.getElementsByTagName("word");
		let words = [];
		for (const word of xWord) {
			let real = getNodeText(word,'real');
			let type = getNodeText(word,'type');
			if(real != '' && type != '.ctl.'){
				words.push(real);
			}
		}
		//不查询重复的段落
		para[book + "-" + paragraph] = { book: book, para: paragraph,word:words };
	}

	_para_list = new Array();
	for (const key in para) {
		if (Object.hasOwnProperty.call(para, key)) {
			const element = para[key];
			_para_list.push(element);
		}
	}

	if (_para_list.length > 0) {
		auto_lookup_wbw(0);
	}
}

//自动查词典
function auto_lookup_wbw(para_index) {
	dictFetch(_para_list[para_index].word,function(){
		FillAllWord();

		//计算查字典的进度
		let precent = (para_index * 100) / (_para_list.length - 1);
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
		//查询下一个段落
		para_index++;
		if (para_index < _para_list.length) {
			auto_lookup_wbw(para_index);
		}
	});
	return;
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

/**
 * 清空逐词译数据 
 */
function InitWbw(){
	let xWord = gXmlBookDataBody.getElementsByTagName("word");
	for (let index = 0; index < xWord.length; index++) {
		let word = xWord[index];
		setNodeText(word,'mean','');
		setNodeText(word,'org','');
		setNodeText(word,'om','');
		setNodeText(word,'parent','');
		setNodeText(word,'gramma','');
		setNodeText(word,'type','');
		setNodeText(word,'case','');
		setNodeText(word,'status',0);

		setNodeAttr(word,'org','status',0);
		setNodeAttr(word,'om','status',0);
		setNodeAttr(word,'parent','status',0);
		setNodeAttr(word,'gramma','status',0);
		setNodeAttr(word,'type','status',0);
		setNodeAttr(word,'case','status',0);		
		let wid = getNodeText(word, "id");
		modifyWordDetailByWordId(wid);
		user_wbw_push_word(wid);	
	}
		
	user_wbw_commit();

	return 'init wbw ' + xWord.length;
}