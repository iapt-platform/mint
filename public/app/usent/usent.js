var user_sentence_create_buffer = new Array();
var user_sentence_update_buffer = new Array();

var user_sentence_block_create_buffer = new Array();
var user_sentence_block_update_buffer = new Array();

function usent_block_create(blockid, book, paragraph, lang, author, editor, tag) {
	var d = new Date();
	let newBlock = new Object();
	newBlock.id = blockid;
	newBlock.book = book;
	newBlock.paragraph = paragraph;
	newBlock.tag = tag;
	newBlock.lang = lang;
	newBlock.author = author;
	newBlock.editor = editor;
	newBlock.time = d.getTime();
	user_sentence_block_create_buffer.push(newBlock);
}
function usent_block_commit() {

	if (user_sentence_block_create_buffer.length > 0) {
		$.post("../usent/new_block.php",
			{
				data: JSON.stringify(user_sentence_block_create_buffer)
			},
			usent_server_responce);
		user_sentence_block_create_buffer = new Array();
	}

	if (user_sentence_block_update_buffer.length > 0) {
		$.post("../usent/updata.php",
			{
				data: JSON.stringify(user_sentence_block_update_buffer)
			},
			usent_server_responce);
		user_sentence_block_update_buffer = new Array();
	}
}

function usent_block_fork(blockid, newBlockId) {

}

function usent_change(blockid, begin, end, text) {

}


function usent_update(blockid, begin, end, text) {
	var d = new Date();
	let mBlock = doc_tran("#" + blockid);
	let dbId = mBlock.text(begin, end, "id");
	let sent_status = mBlock.text(begin, end, "status");
	let newData = new Object();
	newData.id = dbId;
	newData.text = text;
	newData.status = sent_status;
	newData.time = d.getTime();
	user_sentence_update_buffer.push(newData);
}


function usent_create(blockid, id, book, paragraph, begin, end, text, tag, lang, author, editor) {
	let newData = new Object();
	newData.blockid = blockid;
	newData.id = id;
	newData.book = book;
	newData.paragraph = paragraph;
	newData.begin = begin;
	newData.end = end;
	newData.text = text;
	newData.tag = tag;
	newData.lang = lang;
	newData.author = author;
	newData.editor = editor;
	user_sentence_create_buffer.push(newData);
}

function usent_commit() {

	if (user_sentence_create_buffer.length > 0) {
		$.post("../usent/new.php",
			{
				data: JSON.stringify(user_sentence_create_buffer)
			},
			usent_server_responce);
		user_sentence_create_buffer = new Array();
	}

	if (user_sentence_update_buffer.length > 0) {
		$.post("../usent/update.php",
			{
				data: JSON.stringify(user_sentence_update_buffer)
			},
			usent_server_responce);
		user_sentence_update_buffer = new Array();
	}
}

function usent_server_responce(data, status) {
	try {
		let result = JSON.parse(data);
		if (result.status == 0) {
			ntf_show("user sentence" + result.message);
			return ("user sentence" + result.message);
		}
		else {
			ntf_show("user sentence error" + result.message);
			return ("user sentence error" + result.message);
		}
	}
	catch (e) {
		console.error("user_sentence_update:" + e + " data:" + data);
		ntf_show("user sentence error");
	}
}