//var ApiWbwLookup = "./dict_find_one.php";
var ApiWbwLookup = "/api/v2/wbwlookup";

function dictFetch(words,callback=null){
	if (mDict[words]) {
		if(callback){
			callback();
		}
	} else {
		//如果内存里没有这个词，查字典
		if (!mDictQueue[words]) {
			//查询队列里没有，加入队列
			if (gCurrLookupWord != words) {
				mDictQueue[words] = 1;
				gCurrLookupWord = words;
				$.ajax({
					type: "GET",
					url: ApiWbwLookup,
					dataType: "json",
					data: {
						word:words,
					},
				}).done(function (data) {
					if(data.ok){
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

