var user_wbw_data_buffer = new Array();

function user_wbw_push_paragraph(blockid) {}

function user_wbw_push_word_element(xWord) {
	let wordid = getNodeText(xWord, "id");
	let wId = wordid.split("-")[2];
	let mWord = doc_word("#" + wordid);
	let blockid = mWord.block.info("id");
	user_wbw_push(blockid, wId, com_xmlToString(xWord),getNodeText(xWord,'status'));
}

function user_wbw_push_word(wordid) {
	let xWord = doc_word("#" + wordid);
	let blockid = xWord.block.info("id");
	let book = xWord.block.info("book");
	let para = xWord.block.info("paragraph");

	let aWordid = wordid.split("-");
	aWordid.length = 3;
	if (para != aWordid[1]) {
		alert("error：paragraph sn.");
		return;
	}
	let newWordid = aWordid.join("-");
	let wId = aWordid[2];
	let xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	let index = getWordIndex(newWordid);
	let wordData = "";
	if (xAllWord[index]) {
		for (let i = index; i < xAllWord.length; i++) {
			if (getNodeText(xAllWord[i], "id").split("-")[2] != wId) {
				break;
			}
			wordData += com_xmlToString(xAllWord[i]);
		}
		user_wbw_push(blockid, wId, wordData,getNodeText(xAllWord[i],'status'));
	}
}

function user_wbw_push(block_id, wid, data,status=7) {
	let d = new Date();
	let objData = new Object();
	objData.block_id = block_id;
	objData.word_id = wid;
	objData.data = data;
	objData.time = d.getTime();
	objData.book = doc_block("#" + block_id).info("book");
	objData.para = doc_block("#" + block_id).info("paragraph");
	objData.status = status;
	user_wbw_data_buffer.push(objData);
}
var commitTimes = 0;
function user_wbw_commit() {
	if (user_wbw_data_buffer.length == 0) {
		return;
	}
	var jqxhr = $.post(
		"../uwbw/update.php",
		{
			data: JSON.stringify(user_wbw_data_buffer),
		},
		function (data, status) {
			try {
				let result = JSON.parse(data);
				if (result.status == 0) {
					ntf_show("user wbw " + result.message);
					user_wbw_data_buffer = new Array();
				} else {
					ntf_show("user wbw error" + result.message);
				}
			} catch (e) {
				console.error("user_wbw_update:" + e + " data:" + data);
				ntf_show("wbw fail");
			}
		}
	);
	jqxhr.done(function () {
		notify_bar.hide();
	});
	jqxhr.fail(function (xhr, error, data) {
		switch (xhr.status) {
			case 500:
				if (commitTimes < 5) {
					commitTimes++;
					notify_bar.show("发送失败，重试。第" + commitTimes + "次。");
					user_wbw_commit();
				} else {
					notify_bar.show("重试次数过多，请稍后再试。");
					commitTimes = 0;
				}
				break;
			default:
				notify_bar.show(xhr.statusText);
				break;
		}
		switch (error) {
			case "timeout":
				notify_bar.show(
					"服务器长时间没有回应。等待发送队列" +
						user_wbw_data_buffer.length +
						"<button onclick='user_wbw_commit()'>重试</button>"
				);
				break;
			case "error":
				notify_bar.show(
					"与服务器通讯失败，您可能没有连接到网络。等待发送队列" +
						user_wbw_data_buffer.length +
						"<button onclick='user_wbw_commit()'>重试</button>"
				);
				break;
			case "notmodified":
				break;
			default:
				break;
		}
	});
}
