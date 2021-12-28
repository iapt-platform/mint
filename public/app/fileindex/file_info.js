var fileinfoNewBlockList=new Array();

function fileinfo_add_block(docid,type,blockid){
	let newBlock = new Object();
	newBlock.type = type;
	newBlock.block_id =	blockid;
	fileinfoNewBlockList.push(newBlock);
	return(blockid);
}

function fileinfo_add_block_commit(docid){
	fileindex_get(docid,function(data,status){
		try{
			let result= JSON.parse(data);
			if(result.length>0){
				let blocklist= JSON.parse(result[0].doc_block);
				for(x in fileinfoNewBlockList){
					blocklist.push(fileinfoNewBlockList[x]);
				}
				fileinfo_set(result[0].id,"doc_block",JSON.stringify(blocklist));
				console.log("add"+fileinfoNewBlockList.length);
				fileinfoNewBlockList=new Array();
				
			}
		}
		catch(e){
			console.error("user_sentence_update:"+e+" data:"+data);
			ntf_show("user sentence error");
		}
	});
}

function fileinfo_remove_block(){
}

function fileindex_get(docid,callback){
		$.post("../studio/file_index.php",
		{
			op:"get",
			doc_id:docid
		},
		callback);
}
function fileinfo_set(docid,key,value){
		$.post("../studio/file_index.php",
		{
			op:"set",
			doc_id:docid,
			field:key,
			value:value
		},
		function(data,status){
			console.log(data);
		});
}

function fileinfo_list(filename,del=false,time_desc=false){

}

