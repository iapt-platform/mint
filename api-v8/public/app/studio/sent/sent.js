
function sent_new(bid,begin,end,strInput){
	let mBlock = doc_tran("#"+bid);
	let dbId=mBlock.text(begin,end,"id");
	let dbParentId=mBlock.text(begin,end,"db_parent_id");
	if(dbId==""){
		dbId=0;
	}
	if(dbParentId==""){
		dbParentId=0;
	}	
	$.post("./sent/sent.php",
	{
		op:"save",
		block_id:bid,
		id:dbId,
		parent_id:dbParentId,
		book:mBlock.info("book"),
		para:mBlock.info("paragraph"),
		begin:begin,
		end:end,
		tag:"_tran_",
		author:mBlock.info("author"),
		text:strInput,
		lang:mBlock.info("language"),
		status:1
	},
	function(data,status){
		try{
			let arrData=JSON.parse(data);
			let mBlock = doc_tran("#"+arrData.block_id);
			mBlock.text(arrData.begin,arrData.end,"id",arrData.id);
			ntf_show("new id:"+arrData.id);
		}
		catch(e){
			console.error(e);
		}
	}
	);
}

function sen_save(bid,begin,end,str){

	let mBlock = doc_tran("#"+bid);
	let uuid=mBlock.text(begin,end,"id");
	if(uuid==""){
		sent_new(bid,begin,end,str);
	}
	else{
		usent_update(bid,begin,end,str);
		usent_commit();
	}

	//
}
