var user_wbw_data_buffer=new Array();

function user_wbw_push_paragraph(blockid){
	
}

function user_wbw_push_word_element(xWord){
	let wordid=getNodeText(xWord,"id");
	let wId = wordid.split("-")[2];
	let mWord = doc_word("#"+wordid);
	let blockid=mWord.block.info("id");
	user_wbw_push(blockid,wId,com_xmlToString(xWord));	
}

function user_wbw_push_word(wordid){
	let xWord = doc_word("#"+wordid);
	let blockid=xWord.block.info("id");	
	
	let aWordid = wordid.split("-");
	aWordid.length=3;
	let newWordid=aWordid.join("-");
	let wId = aWordid[2];
	let xAllWord = gXmlBookDataBody.getElementsByTagName("word");	
	let wid=getWordIndex(newWordid);
	let wordData = "";
	if(xAllWord[wid]){
		for(i=wid;i<xAllWord.length;i++){
			if(getNodeText(xAllWord[i],"id").split("-")[2]!=wId){
				break;
			}
			wordData += com_xmlToString(xAllWord[i]);
			
		}
		user_wbw_push(blockid,wId,wordData);
	}

}

function user_wbw_push(block_id,wid,data){
	let d = new Date();
	let objData = new Object();
	objData.block_id=block_id;
	objData.word_id = wid;
	objData.data = data;
	objData.time = d.getTime();
	user_wbw_data_buffer.push(objData);
}

function user_wbw_commit(){

	if(user_wbw_data_buffer.length==0){
		return;
	}
	$.post("../uwbw/update.php",
	{
		data:JSON.stringify(user_wbw_data_buffer)
	},
	function(data,status){
		try{
			let result= JSON.parse(data);
			if(result.status==0){
				ntf_show("user wbw"+result.message);
			}
			else{
				ntf_show("user wbw error"+result.message);
			}
		}
		catch(e){
			console.error("user_wbw_update:"+e+" data:"+data);
			ntf_show("user wbw");
		}
	});
	user_wbw_data_buffer=new Array();
}