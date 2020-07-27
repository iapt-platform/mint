//var sync_db_list= new Array("term","usent");
var sync_db_list= new Array("doc/sync_index.php","term/sync.php","usent/sync.php");
var sync_curr_do_db = 0;
function sync_index_init(){
	
}


function sync_start(){
	sync_curr_do_db = 0;
	$("#sync_result").html("working"); // 
	sync_do_db();

}

function sync_do_db(){
	
	$.get("sync.php", 
	{
	"server": $("#sync_server_address").val(),
	"localhost": $("#sync_local_address").val(),
	"path": sync_db_list[sync_curr_do_db]
	},
   function(data){
     $("#sync_result").html($("#sync_result").html()+"<br>"+data); // 
	 sync_curr_do_db++;
	 if(sync_curr_do_db<sync_db_list.length){
		 sync_do_db();
	 }
	 else{
		 $("#sync_result").html($("#sync_result").html()+"<br>All Done"); // 
	 }
   });	
}