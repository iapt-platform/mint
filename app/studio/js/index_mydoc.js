var my_file_title="";
var my_file_status="all";//recycle
var my_file_order="DESC";//ASC

function file_search_keyup(){
	file_list_refresh();
}

function recycleInit(){
		ntf_init();
	file_list_refresh();
}
function indexInit(){
	ntf_init();
	file_list_refresh();
}


function file_list_refresh(){
	var d=new Date();
	$.get("getfilelist.php",
	  {
		t:d.getTime(),
		keyword:my_file_title,
		status:$("#id_index_status").val(),
		orderby:"accese_time",
		order:"DESC",
		currLanguage:$("#id_language").val()
	  },
	  function(data,status){
		  try{
			let file_list = JSON.parse(data);
			let html="";
			for(x in file_list){
				html += "<div class=\"file_list_row\">";
				html += "<div class=\"file_list_col_1\">";
				html += "<input id='file_check_"+x+"' type=\"checkbox\" />";
				html += "<input id='file_id_"+x+"' value='"+file_list[x].id+"' type=\"hidden\" />";
				html += "</div>";
				if(file_list[x].doc_info && file_list[x].doc_info.length>1){
					$link="<a href='editor.php?op=opendb&fileid="+file_list[x].id+"' target='_blank'>[db]";
				}
				else{
					$link="<a href='editor.php?op=open&fileid="+file_list[x].id+"' target='_blank'>";
				}
				html += "<div class=\"file_list_col_2\">";
				if((file_list[x].parent_id == null || file_list[x].parent_id == "") && parseInt(file_list[x].share)==1){
						//shared
						html += "<span onclick=\"file_show_coop_win('"+file_list[x].id+"')\">";
				}
				else{
					html += "<span>";
				}
				
				html += "<svg class='icon' style='margin: 0 5px;'>";
				if(file_list[x].parent_id == null || file_list[x].parent_id == ""){
					if(parseInt(file_list[x].share)==1){
						//shared
						html += "<use xlink:href=\"./svg/icon.svg#share_to_other\"></use>";
					}
					else{
					//my document
					html += "<use xlink:href=\"./svg/icon.svg#ic_person\"></use>";							
					}
				}
				else{
					//fork
					html += "<use xlink:href=\"./svg/icon.svg#other_share_to_me\"></use>";
				}
				html += "</svg>";
				html += "</span>";
				html += "<div id='coop_show_"+file_list[x].id+"' style='display:inline;'></div>";
				html += $link+file_list[x].title;
				html += "</a>";

				
				html += "</div>";
				html += "<div class=\"file_list_col_3\">";

				if((file_list[x].parent_id && file_list[x].parent_id.length>10) || parseInt(file_list[x].share)==1){
					html += "<svg class='icon'>";
					html += "<use xlink:href=\"./svg/icon.svg#ic_two_person\"></use>";
					html += "</svg>";
				}
				html += "</div>";
				html += "<div class=\"file_list_col_4\">";
				let d = new Date();
				let today_date=d.toLocaleDateString();
				d.setTime(file_list[x].accese_time);
				let Local_time = "";
				if(d.getHours()<=9){
					Local_time +=":0"+d.getHours();
				}
				else{
					Local_time += ":"+d.getHours();
				}
				if(d.getMinutes()<=9){
					Local_time +=":0"+d.getMinutes();
				}
				else{
					Local_time += ":"+d.getMinutes();
				}
				let Local_date = d.toLocaleDateString();
				if(today_date==Local_date){
					html += gLocal.gui.today+Local_time;
				}
				else{
					html += Local_date;
				}
				
				html += "</div>";
				html += "<div class=\"file_list_col_5\">";
				
				if(file_list[x].file_size<102){
					$str_size=file_list[x].file_size+"B";
				}
				else if(file_list[x].file_size<(1024*902)){
					$str_size=(file_list[x].file_size/1024).toFixed(0)+"KB";
				}
				else{
					$str_size=(file_list[x].file_size/(1024*1024)).toFixed(1)+"MB";
				}
				html += $str_size;
				if(!(file_list[x].doc_info && file_list[x].doc_info.length>1)){
					html += "<a href='../doc/pcs2db.php?doc_id="+file_list[x].id+"' target='_blank'>转数据库格式</a>";
				}
				html += "</div>";
				html += "</div>";
			}
			html += "<input id='file_count' type='hidden' value='"+file_list.length+"'/>"
			$("#userfilelist").html(html);
		  }
		  catch(e){
			  console.error(e.message);
		  }
	  });
}

function showUserFilaList()
{
	file_list_refresh();
}

		
function mydoc_file_select(doSelect){
	if(doSelect){
		$("#file_tools").show();
		$("#file_filter").hide();
		$(".file_select_checkbox").show();
		$(".file_select_checkbox").css("display","inline-block");
		
		if($("#id_index_status").val()=="recycle"){
			$("#button_group_recycle").show();
			$("#button_group_nomal").hide();
		}
		else{
			$("#button_group_recycle").hide();
			$("#button_group_nomal").show();			
		}
	}
	else{
		$("#file_tools").hide();
		$("#file_filter").show();
		$(".file_select_checkbox").hide();
	}
}

function file_del(){
	var file_list=new Array();
	var file_count=$("#file_count").val();
	for(var i=0;i<file_count;i++){
		if(document.getElementById("file_check_"+i).checked){
			file_list.push($("#file_id_"+i).val());
		}
	}
	if(file_list.length>0){
	$.post("file_index.php",
	  {
		op:"delete",
		file:file_list.join()
	  },
	  function(data,status){
		ntf_show(data);
		file_list_refresh();
	  });
	}
}
//彻底删除
function file_remove(){
	var file_list=new Array();
	var file_count=$("#file_count").val();
	for(var i=0;i<file_count;i++){
		if(document.getElementById("file_check_"+i).checked){
			file_list.push($("#file_id_"+i).val());
		}
	}
	if(file_list.length>0){
	$.post("file_index.php",
	  {
		op:"remove",
		file:file_list.join()
	  },
	  function(data,status){
		ntf_show(data);
		file_list_refresh();
	  });
	}
}
//从回收站中恢复
function file_restore(){
	var file_list=new Array();
	var file_count=$("#file_count").val();
	for(var i=0;i<file_count;i++){
		if(document.getElementById("file_check_"+i).checked){
			file_list.push($("#file_id_"+i).val());
		}
	}
	if(file_list.length>0){
	$.post("file_index.php",
	  {
		op:"restore",
		file:file_list.join()
	  },
	  function(data,status){
		ntf_show(data);
		file_list_refresh();
	  });
	}
}
function file_share(isShare){
	var file_list=new Array();
	var file_count=$("#file_count").val();
	for(var i=0;i<file_count;i++){
		if(document.getElementById("file_check_"+i).checked){
			file_list.push($("#file_id_"+i).val());
		}
	}
	if(file_list.length>0){
		if(isShare){
			var share=1;
		}
		else{
			var share=0;
		}
		$.post("file_index.php",
		{
			op:"share",
			share:share,
			file:file_list.join()
		},
		function(data,status){
		alert(data);
		//mydoc_file_select(false);
		file_list_refresh();
		});
	}
}

function file_show_coop_win(doc_id){
	let xFileHead = document.getElementById("coop_show_"+doc_id);
	let xCoopWin = document.getElementById("rs_doc_coop_win");
	xFileHead.appendChild(xCoopWin);
	coop_init(doc_id,"rs_doc_coop_win_inner");
	coop_list();
}

function file_coop_win_close(){
	let xShell = document.getElementById("rs_doc_coop_shell");
	let xCoopWin = document.getElementById("rs_doc_coop_win");
	xShell.appendChild(xCoopWin);	
}