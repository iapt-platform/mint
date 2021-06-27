var arrMsgBuffer = new Array();
var msgTime = 1000;
var msg_timer;
var iMsgLastUpdateId = 0;
var strMsgDocList = "";
var msg_curr_show_content_id = "";
var msg_curr_show_content_type = "";
var msg_my_id = "sys_message";
/*
初始化消息模块
msgInitId：上次消息的最大id
*/
function msg_init(msgInitId, time = 3) {
	iMsgLastUpdateId = msgInitId;
	msgTime = time * 1000;
}

function msg_start() {
	msg_send();
}
function msg_stop() {
	clearTimeout(msg_timer);
}

function msg_push(type, data, docid, time = 0, book = 0, paragraph = 0) {
	/*
	data.type
	data.data
	data.doc
  */
	let send_time = time;
	if (time == 0) {
		let d = new Date();
		send_time = d.getTime();
	}
	var newMessage = {
		type: type,
		data: data,
		docid: docid,
		time: send_time,
		book: book,
		para: paragraph,
	};
	arrMsgBuffer.push(newMessage);

	var obj = new Object();
	obj.id = 0;
	obj.sender = getCookie("username");
	obj.type = newMessage.type;
	obj.docid = newMessage.docid;
	obj.time = newMessage.time / 5000;
	obj.read = 1;
	obj.data = JSON.parse(newMessage.data);
	//doc_msg_push(obj);
	msg_show_content(msg_curr_show_content_type, msg_curr_show_content_id);
}
function msg_reload() {
	iMsgLastUpdateId = 1;
}
function msg_send() {
	$("#net_up").html("");
	$("#msg_tool_bar").html("<svg class='icon'><use xlink:href='../studio/svg/icon.svg#upload_ms'></use></svg>");

	if (arrMsgBuffer.length > 0) {
		var operation = "send";
	} else {
		var operation = "get";
	}

	$.post(
		"./message.php",
		{
			op: operation,
			lastid: iMsgLastUpdateId,
			doclist: strMsgDocList,
			data: JSON.stringify(arrMsgBuffer),
		},
		function (data, status) {
			let xmlText = data;
			console.log("receive mseeage:" + data);
			let msgXml;
			if (window.DOMParser) {
				parser = new DOMParser();
				msgXml = parser.parseFromString(xmlText, "text/xml");
			} else {
				// Internet Explorer

				msgXml = new ActiveXObject("Microsoft.XMLDOM");
				msgXml.async = "false";
				msgXml.loadXML(xmlText);
			}

			if (msgXml == null) {
				console.error("error:can not load Project. xml obj is null.");
				return;
			}
			let arrMsg = msgXml.getElementsByTagName("msg");
			var arrWordNewMsg = new Array();

			//开始一个事务，关闭自动提交
			doc_beginTransaction();
			for (var x = 0; x < arrMsg.length; x++) {
				switch (getNodeText(arrMsg[x], "type")) {
					case "maxid":
						iMsgLastUpdateId = getNodeText(arrMsg[x], "data");
						//TO DO 用local storage解决
						//doc_head("msg_db_max_id", iMsgLastUpdateId);
						console.log("iMsgLastUpdateId:" + iMsgLastUpdateId);
						break;
					default:
						//if (gXmlBookDataMsg)
						{
							var obj = new Object();
							obj.id = getNodeText(arrMsg[x], "id");
							obj.sender = getNodeText(arrMsg[x], "sender");
							obj.type = getNodeText(arrMsg[x], "type");
							obj.docid = getNodeText(arrMsg[x], "docid");
							obj.time = getNodeText(arrMsg[x], "time");
							obj.read = getNodeText(arrMsg[x], "read");
							let objData = getNodeText(arrMsg[x], "data");
							try {
								obj.data = JSON.parse(objData);
							} catch (e) {
								obj.data = null;
								console.error("err:message.js-msg_send" + e + "data:" + objData);
								break;
							}
							//尝试用此消息更新文档数据
							if (msg_apply_data(obj)) {
								//如果已经使用此消息，xml标记为已读
								//setNodeText(arrMsg[x], "read", "2");
							}
							doc_msg_push(obj);
							msg_show_content(msg_curr_show_content_type, msg_curr_show_content_id);

							switch (obj.type) {
								case "1": //wbw
									var iFind = false;
									for (var iWordId = 0; iWordId < arrWordNewMsg.length; iWordId++) {
										if (arrWordNewMsg[iWordId] == obj.data.id) {
											iFind = true;
											break;
										}
									}
									if (!iFind) {
										arrWordNewMsg.push(obj.data.id);
									}
									break;
								case "2":
									break;
							}
							//gXmlBookDataMsg.appendChild(arrMsg[x].cloneNode(true));
						}
						break;
				}
			}
			//提交一个事务
			doc_commit();
			user_wbw_commit();

			for (let i = 0; i < arrWordNewMsg.length; i++) {
				updataWordHeadById(arrWordNewMsg[i]);
			}
			refreshNoteNumber();
			msg_set_tool_bar_msg_counter();
			msg_update_msg_list();
			$("#net_down").html(" ");
			$("#msg_tool_bar").html("<svg class='icon'><use xlink:href='../studio/svg/icon.svg#pause_ms'></use></svg>");
		}
	);

	$("#net_up").html("");
	$("#net_down").html("");
	$("#msg_tool_bar").html("<svg class='icon'><use xlink:href='../studio/svg/icon.svg#download_ms'></use></svg>");

	arrMsgBuffer = new Array();

	msg_timer = setTimeout("msg_send()", msgTime);
}
function msg_new_msg_id_push() {}

function msg_stop() {}

function msg_read(msg_obj, status = null) {
	if (msg_obj) {
		if (status) {
			let oldStatus = msg_obj.read;
			msg_obj.read = status;
			for (let i = 0; i < gDocMsgList.length; i++) {
				if (gDocMsgList[i].data.id == msg_obj.id) {
					gDocMsgList[i].read = status;
					break;
				}
			}
			return oldStatus;
		} else {
			return msg_obj.read;
		}
	}
}

//将消息数据应用(apply)到文档
function msg_apply_data(obj) {
	if (obj.sender == getCookie("username")) {
		//忽略自己的消息
		msg_read(obj, 1); //设置为已读
		return true;
	}
	doc_info.sendmsg = false; //不发送消息
	try {
		switch (obj.type) {
			case "1": //逐词译
				let wIndex = getWordIndex(obj.data.id);
				if (wIndex >= 0) {
					let xAllWord = gXmlBookDataBody.getElementsByTagName("word");
					let xWord = xAllWord[wIndex];
					let sReal = getNodeText(xWord, "real");
					let wordStatus = parseInt(getNodeText(xWord, "status"));
					let wordBodyChange = false;
					let wordHeadChange = false;
					let wordNoteChange = false;
					let wordRelationChange = false;
					let newWord = new Object();
					newWord.real = sReal;
					newWord.vaild = false;
					let wordChanged = false;
					if (wordStatus != 7 && wordStatus != 5 && obj.sender != getCookie("username")) {
						msg_read(obj, 2);
						if (obj.data.real != null && obj.data.real != "") {
							//setNodeText(xWord,"real",obj.data.real);
							//newWord.real = obj.data.real;
							//wordHeadChange=true;
						}
						if (obj.data.pali != null) {
							//setNodeText(xWord,"pali",obj.data.pali);
							//wordHeadChange=true;
						}
						if (obj.data.mean != null) {
							setNodeText(xWord, "mean", obj.data.mean);
							newWord.mean = obj.data.mean;
							newWord.vaild = true;
							wordBodyChange = true;
							wordChanged = true;
						}
						if (obj.data.org != null) {
							setNodeText(xWord, "org", obj.data.org);
							newWord.parts = obj.data.org;
							newWord.vaild = true;
							wordBodyChange = true;
							wordChanged = true;
						}
						if (obj.data.om != null) {
							setNodeText(xWord, "om", obj.data.om);
							newWord.partmean = obj.data.om;
							newWord.vaild = true;
							wordBodyChange = true;
							wordChanged = true;
						}
						if (obj.data.case != null) {
							setNodeText(xWord, "case", obj.data.case);
							newWord.case = obj.data.case;
							newWord.vaild = true;
							wordBodyChange = true;
							wordChanged = true;
						}
						if (obj.data.parent != null) {
							setNodeText(xWord, "parent", obj.data.parent);
							wordChanged = true;
						}
						if (obj.data.note != null) {
							setNodeText(xWord, "note", obj.data.note);
							wordNoteChange = true;
							wordChanged = true;
						}
						if (obj.data.rela != null) {
							//setNodeText(xWord,"rela",decodeURI(obj.data.rela));
							setNodeText(xWord, "rela", obj.data.rela);
							wordRelationChange = true;
							wordChanged = true;
						}
						if (obj.data.bmc != null) {
							setNodeText(xWord, "bmc", obj.data.bmc);
							wordBodyChange = true;
							wordChanged = true;
						}
						if (obj.data.bmt != null) {
							setNodeText(xWord, "bmt", obj.data.bmt);
							wordChanged = true;
						}
						if (obj.data.lock != null) {
							setNodeText(xWord, "lock", obj.data.lock);
							wordBodyChange = true;
							wordChanged = true;
						}
						if (wordChanged) {
							setNodeText(xWord, "status", 6);
							//提交用户逐词解析数据库
							user_wbw_push_word(obj.data.id);
						}
					}

					if (wordHeadChange) {
						updataWordHeadByIndex(wIndex);
					}
					if (wordBodyChange) {
						modifyWordDetailByWordIndex(wIndex);
					}
					if (wordNoteChange || wordRelationChange) {
						//updateWordNote(xWord);
						refreshWordNoteDiv(xWord.parentNode.parentNode);
					}

					if (newWord.vaild) {
						if (!mDict[sReal]) {
							mDict[sReal] = new Array();
						}
						let isExsit = false;
						for (let x in mDict[sReal]) {
							if (
								mDict[sReal].mean &&
								mDict[sReal].mean == newWord.mean &&
								mDict[sReal].parts &&
								mDict[sReal].parts == newWord.parts &&
								mDict[sReal].partmean &&
								mDict[sReal].mean == newWord.partmean &&
								mDict[sReal].case &&
								mDict[sReal].case == newWord.case
							) {
								isExsit = true;
							}
						}
						if (!isExsit) {
							mDict[sReal].push(newWord);
						}
					}
				}
				break;
			case "2": //译文
				let book = obj.data.book;
				let para = obj.data.para;
				let begin = obj.data.begin;
				let end = obj.data.end;
				$("[pcds='sent-net-all'][book='" + book + "'][para='" + para + "'][begin='" + begin + "']").html(
					obj.data.text
				);

				$("[pcds='sent-net-div'][book='" + book + "'][para='" + para + "'][begin='" + begin + "']")
					.find(".author")
					.html(obj.sender);
				let tranBlock = doc_tran("#" + obj.data.id);
				if (tranBlock == null) {
					tranBlock = doc_tran("#" + obj.data.id, true);
				}
				if (tranBlock) {
					/*
					if (tranBlock.text(obj.data.begin, obj.data.end, "status") != 7) {
						msg_read(obj, 2);//设置为自动采纳
						console.log("句子 自动采纳");
						tranBlock.text(obj.data.begin, obj.data.end, "text", obj.data.text);
						tranBlock.text(obj.data.begin, obj.data.end, "status", 5);
						sen_save(tranBlock.info("id"), obj.data.begin, obj.data.end, obj.data.text);
					}
					*/
				}

				break;
		}
	} catch (e) {
		console.error(e.message);
		console.error(e.stack);
	}
	doc_info.sendmsg = true; //发送消息

	if (obj.read > 0) {
		return true;
	} else {
		return false;
	}
}

function msg_word_msg_num(wid) {
	if (gDocMsgList == null) {
		return;
	}
	var iMsg = 0;
	for (var i = 0; i < gDocMsgList.length; i++) {
		if (gDocMsgList[i].type == 1) {
			if (gDocMsgList[i].data.id == wid && gDocMsgList[i].read == 0) {
				iMsg++;
			}
		}
	}
	return iMsg;
}

function msg_set_tool_bar_msg_counter() {
	if (gDocMsgList == null) {
		return;
	}
	var iMsg = 0;
	for (var i = 0; i < gDocMsgList.length; i++) {
		if (gDocMsgList[i].read == 0) {
			iMsg++;
		}
	}
	if (iMsg == 0) {
		$("#icon_notify_" + msg_my_id).html("");
		$("#icon_notify_" + msg_my_id).hide();
	} else {
		if (iMsg > 100) {
			iMsg = "+99";
		}
		$("#icon_notify_" + msg_my_id).html(iMsg.toString());
		$("#icon_notify_" + msg_my_id).show();
	}
}
function time_standardize(date) {
	var today_date = new Date();
	var Local_time = date.toLocaleTimeString();
	//將時間去掉秒的信息
	if (Local_time && Local_time.split(":").length == 3) {
		var Local_time_string = Local_time.split(":")[0] + ":" + Local_time.split(":")[1];
		Local_time_string += Local_time.split(":")[2].slice(2);
	} else {
		var Local_time_string = d.toLocaleTimeString();
	}
	if (date.toLocaleDateString() == today_date.toLocaleDateString()) {
		//如果是今天的消息，只显示时间
		return Local_time_string;
	} else if (date.toLocaleDateString().slice(0, 5) == today_date.toLocaleDateString().slice(0, 5)) {
		//如果是今年但非今天的消息，只显示月日
		var date_length = date.toLocaleDateString().length;
		return date.toLocaleDateString().slice(5, date_length);
	} else {
		//如果不是今年的消息，显示年月日
		return date.toLocaleDateString();
	}
}

//显示消息内容
function msg_show_content(type, id) {
	if (gDocMsgList == null) {
		return;
	}
	if (type == "" || id == "") {
		return;
	}
	let arrid;
	let sen_begin, sen_end;
	type = parseInt(type);

	if (type == 2) {
		arrid = id.split("#");
		id = arrid[0];
		sen_begin = arrid[1];
		sen_end = arrid[2];
	}

	msg_curr_show_content_id = id;
	msg_curr_show_content_type = type;
	var iMsg = 0;
	var outHtml = "";
	var iLastTime = 0;
	for (var i = 0; i < gDocMsgList.length; i++) {
		let isFound = false;
		switch (type) {
			case 1:
				if (gDocMsgList[i].type == type && gDocMsgList[i].data.id == id) {
					isFound = true;
				}
				break;
			case 2:
				if (
					gDocMsgList[i].type == type &&
					gDocMsgList[i].data.id == id &&
					gDocMsgList[i].data.begin == sen_begin &&
					gDocMsgList[i].data.end == sen_end
				) {
					isFound = true;
				}
				break;
		}
		if (isFound) {
			iMsg++;
			if (gDocMsgList[i].read == 0) {
				//如果未读，设置为已读
				msg_read(gDocMsgList[i], 1);
			}

			//三分钟之内的消息只显示一个时间标记
			if (gDocMsgList[i].time - iLastTime > 60 * 3) {
				var d = new Date();
				d.setTime(gDocMsgList[i].time * 1000);
				//var Local_date=d.toLocaleDateString().split("/");
				var time_standardize_string = time_standardize(d);
				outHtml += "<div class='msgbox_time'><span>" + time_standardize_string + "</span></div>"; //d.toLocaleeString()
			}
			iLastTime = gDocMsgList[i].time;

			var myName = getCookie("username");
			if (gDocMsgList[i].sender == myName) {
				outHtml += "<div class='msgbox_div'>";
				outHtml += "<div class='msgbox_s'>";
				outHtml += "<div class='head'><span>" + gDocMsgList[i].sender + "</span></div>";
			} else {
				outHtml += "<div class='msgbox_r'>";
				outHtml += "<div class='head'><span>" + gDocMsgList[i].sender + "</span><span>Apply</span></div>";
			}

			switch (parseInt(type)) {
				case 1:
					if (gDocMsgList[i].data.pali != null) {
						outHtml +=
							"<div>spell:<a onclick=\"fieldListChanged('" +
							id +
							"','pali','" +
							gDocMsgList[i].data.pali +
							"')\">" +
							gDocMsgList[i].data.pali +
							"</a></div>";
					}
					if (gDocMsgList[i].data.real != null) {
						outHtml +=
							"<div>real:<a onclick=\"fieldListChanged('" +
							id +
							"','real','" +
							gDocMsgList[i].data.real +
							"')\">" +
							gDocMsgList[i].data.real +
							"</a></div>";
					}
					if (gDocMsgList[i].data.mean != null) {
						outHtml +=
							"<div>mean:<a onclick=\"fieldListChanged('" +
							id +
							"','mean','" +
							gDocMsgList[i].data.mean +
							"')\">" +
							gDocMsgList[i].data.mean +
							"</a></div>";
					}
					if (gDocMsgList[i].data.org != null) {
						outHtml +=
							"<div>part:<a onclick=\"fieldListChanged('" +
							id +
							"','org','" +
							gDocMsgList[i].data.org +
							"')\">" +
							gDocMsgList[i].data.org +
							"</a></div>";
					}
					if (gDocMsgList[i].data.om != null) {
						outHtml +=
							"<div>part mean:<a onclick=\"fieldListChanged('" +
							id +
							"','om','" +
							gDocMsgList[i].data.om +
							"')\">" +
							gDocMsgList[i].data.om +
							"</a></div>";
					}
					if (gDocMsgList[i].data.case != null) {
						outHtml +=
							"<div>case:<a onclick=\"fieldListChanged('" +
							id +
							"','case','" +
							gDocMsgList[i].data.case +
							"')\">" +
							gDocMsgList[i].data.case +
							"</a></div>";
					}
					if (gDocMsgList[i].data.parent != null) {
						outHtml +=
							"<div>base:<a onclick=\"fieldListChanged('" +
							id +
							"','parent','" +
							gDocMsgList[i].data.parent +
							"')\">" +
							gDocMsgList[i].data.parent +
							"</a></div>";
					}
					if (gDocMsgList[i].data.note != null) {
						outHtml +=
							"<div>note:<a onclick=\"fieldListChanged('" +
							id +
							"','note','" +
							gDocMsgList[i].data.note +
							"')\">" +
							gDocMsgList[i].data.note +
							"</a></div>";
					}
					if (gDocMsgList[i].data.rela != null) {
						let strRelation = decodeURI(gDocMsgList[i].data.rela);
						outHtml +=
							"<div>Relation:<a onclick=\"fieldListChanged('" + id + "','rela','" + strRelation + "')\">";
						outHtml += renderWordRelationByString("", strRelation, id);
						outHtml += "</a></div>";
					}
					if (gDocMsgList[i].data.lock != null) {
						outHtml +=
							"<div>lock:<a onclick=\"fieldListChanged('" +
							id +
							"','lock','" +
							gDocMsgList[i].data.lock +
							"')\">" +
							gDocMsgList[i].data.lock +
							"</a></div>";
					}
					if (gDocMsgList[i].data.bmc != null) {
						outHtml +=
							"<div>Bookmark:<a onclick=\"fieldListChanged('" +
							id +
							"','bmc','" +
							gDocMsgList[i].data.bmc +
							"')\">" +
							gDocMsgList[i].data.bmc +
							"</a></div>";
					}
					if (gDocMsgList[i].data.bmt != null) {
						outHtml +=
							"<div>Bookmark:<a onclick=\"fieldListChanged('" +
							id +
							"','bmt','" +
							gDocMsgList[i].data.bmt +
							"')\">" +
							gDocMsgList[i].data.bmt +
							"</a></div>";
					}
					break;
				case 2:
					outHtml +=
						"<div>sentence:<a onclick=\"setTranText('" +
						gDocMsgList[i].data.id +
						"','" +
						gDocMsgList[i].data.end +
						"','" +
						gDocMsgList[i].data.text +
						"')\">" +
						gDocMsgList[i].data.text +
						"</a></div>";

					break;
			}
			switch (gDocMsgList[i].read) {
				case 1:
					break;
				case 2:
					outHtml += "<div>已经自动采纳</div>";
					break;
				case 3:
					outHtml += "<div>已被采纳</div>";
					break;
			}
			outHtml += "</div></div>";
		}
	}

	$("#msg_panal_content").html(outHtml);

	switch (type) {
		case 1:
			updataWordHeadById(id);
			var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
			var wordSpell = getNodeText(xAllWord[getWordIndex(id)], "real");
			var wordId = getNodeText(xAllWord[getWordIndex(id)], "id");
			wordSpell = "<a href='#w" + wordId + "'>" + wordSpell + "</a>";
			$("#msg_content_title").html(wordSpell);
			break;
		case 2:
			break;
	}

	msg_set_tool_bar_msg_counter();
}

//刷新消息列表
function msg_update_msg_list() {
	if (gDocMsgList == null) {
		return;
	}
	let strHtml = "";
	let msgList = new Array();

	for (var i = 0; i < gDocMsgList.length; i++) {
		switch (gDocMsgList[i].type) {
			case "2":
			case "1":
				var iFind = _msg_find_id_in_list(msgList, gDocMsgList[i].data.id);
				if (iFind >= 0) {
					if (gDocMsgList[i].read == 0) {
						msgList[iFind].unread++;
					}
					msgList[iFind].counter++;
					msgList[iFind].newTime = gDocMsgList[i].time;
					msgList[iFind].sender = gDocMsgList[i].sender;
				}
				//没找到
				else {
					objMsg = new Object();
					objMsg.id = gDocMsgList[i].data.id;
					objMsg.data = gDocMsgList[i].data;
					objMsg.type = gDocMsgList[i].type;
					if (gDocMsgList[i].read == 0) {
						objMsg.unread = 1;
					} else {
						objMsg.unread = 0;
					}
					objMsg.counter = 1;
					objMsg.newTime = gDocMsgList[i].time;
					objMsg.sender = gDocMsgList[i].sender;
					msgList.push(objMsg);
				}
				break;
		}
	}
	strHtml += "<ul class='msg_list'>";

	msgList.sort(sortNumber);
	for (var j = 0; j < 2; j++) {
		for (var i = 0; i < msgList.length; i++) {
			//先显示未读的 再显示已经读的
			var times;
			if (msgList[i].unread > 0) {
				times = 0;
			} else {
				times = 1;
			}
			if (times == j) {
				strHtml += "<li>";
				var d = new Date();
				d.setTime(msgList[i].newTime * 1000);
				switch (msgList[i].type) {
					case "1":
						var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
						var wIndex = getWordIndex(msgList[i].id);
						if (wIndex >= 0) {
							var wordSpell = getNodeText(xAllWord[getWordIndex(msgList[i].id)], "real");
						} else {
							var wordSpell = "unkow";
						}
						strHtml += "<span>";
						if (msgList[i].unread > 0) {
							strHtml +=
								"<span class='word_msg'  onclick=\"msg_show_msg_content('1','" +
								msgList[i].id +
								"')\">" +
								msgList[i].unread +
								"</span>";
						}
						strHtml += "<a href='#w" + msgList[i].id + "'>[«]</a>";
						strHtml +=
							"<a onclick=\"msg_show_msg_content('1','" +
							msgList[i].id +
							"')\">" +
							wordSpell +
							"</a></span>";
						strHtml += "<span>" + time_standardize(d) + "</span>";
						break;
					case "2":
						let sent_id = msgList[i].id + "#" + msgList[i].data.begin + "#" + msgList[i].data.end;
						let sent_msg_title =
							msgList[i].data.begin + "-" + msgList[i].data.end + "-" + msgList[i].data.text.slice(0, 5);
						strHtml +=
							"<a onclick=\"msg_show_msg_content('2','" +
							sent_id +
							"')\">" +
							sent_msg_title +
							"</a></span>";
						strHtml += "<span>" + time_standardize(d) + "</span>";
						break;
				}
				strHtml += "</li>";
			}
		}
	}
	strHtml += "</ul>";

	$("#msg_panal_list").html(strHtml);
}
function _msg_find_id_in_list(arrList, id) {
	for (var i = 0; i < arrList.length; i++) {
		if (arrList[i].id == id) {
			return i;
		}
	}
	return -1;
}

function sortNumber(a, b) {
	return b.newTime - a.newTime;
}

function show_tran_msg(bid, begin, end) {
	msg_show_msg_content(2, bid + "-" + begin + "-" + end);
}

function word_msg_counter_click(wordId) {
	msg_show_content(1, wordId);
	msg_show_content_panal();
	//tab_click('msg_panal_right', 'rb_msg');
	tab_click_b("sys_message", "tab_rb_sys_message", editor_show_right_tool_bar, true);
	editor_show_right_tool_bar(true);
}
function msg_show_msg_content(type, id) {
	msg_show_content(type, id);
	msg_show_content_panal();
	tab_click_b("sys_message", "tab_rb_sys_message", editor_show_right_tool_bar, true);
}

function show_tran_net(book, para, begin, end) {
	tab_click_b("sys_message", "tab_rb_sys_message", editor_show_right_tool_bar, true);
	$.get(
		"../usent/get.php",
		{
			book: book,
			para: para,
			begin: begin,
			end: end,
		},
		function (data, status) {
			let arrSent = JSON.parse(data);
			let strHtml = "";
			for (const iterator of arrSent) {
				strHtml += "<div class='trans_text_block'>";
				strHtml += "<div class='trans_text_content' >";
				strHtml += iterator.text;
				strHtml += "</div>";
				strHtml +=
					"<div class='trans_text_info'>" +
					"<span><span class='author'>" +
					iterator.c_name +
					"@" +
					iterator.c_owner.nickname +
					"</span><span class='tag'></span></span>" +
					"<span class='tools'>" +
					"<button>采纳</button>" +
					"</span>" +
					"</div>";
				strHtml += "</div>";
			}
			$("#msg_panal_content").html(strHtml);
			$("#msg_panal_content").show();
			$("#msg_panal_list").hide();
		}
	);
}

function msg_show_list_panal() {
	$("#msg_panal_content_toolbar").hide();
	$("#msg_panal_content").hide();

	$("#msg_panal_list_toolbar").show();
	$("#msg_panal_list").show();
}
function msg_show_content_panal() {
	$("#msg_panal_content_toolbar").show();
	$("#msg_panal_content").show();

	$("#msg_panal_list_toolbar").hide();
	$("#msg_panal_list").hide();
}
