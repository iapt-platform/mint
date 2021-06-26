var gCurrPage=0;
var gCurrBookTitle="";
var gCurrBookId="";
var gPrevTocLevelBase=-1;
function index_page_init(){
	main_menu_show(0);
	search_best("all");
	wizard_palicannon_index_render_c1("");

}
function close_res_info(){
	document.getElementById("res_info_"+gCurrPage).className = "right_nav_bar_off";
}
function wizard_palicannon_index_changed_book(parentLevel,strParent,strBookTitle,strBookId){
	gCurrBookTitle=strBookTitle;
	gCurrBookId=strBookId;
	wizard_palicannon_heading_div_cls(1);
	//wizard_palicannon_index_render_book(parentLevel,strParent,strBookTitle)
	//wizard_palicannon_show_filelist(strBookId);
	index_render_res_list(strBookId,-1,"-1");
	

}
var res_list_stack=new Array();
function index_render_res_list(book_id,album_id,paragraph,goback=false){
  if(goback){
	  book_id=res_list_stack[res_list_stack.length-2].book;
	  album_id=res_list_stack[res_list_stack.length-2].album;
	  paragraph=res_list_stack[res_list_stack.length-2].paragraph;
	  res_list_stack.pop();
  }
  else{
	res_list_stack.push({ book: book_id, album: album_id,paragraph: paragraph });
  }
  $.get("./get_res_index.php", { book: book_id, album: album_id,paragraph: paragraph },
  function(data){
	$("#para_res_list").html(data);
	document.getElementById("para_res_list_shell").style.display="block";
	document.getElementById("res_info_"+gCurrPage).appendChild(document.getElementById("para_res_list_shell"));
	document.getElementById("res_info_"+gCurrPage).className = "right_nav_bar_on";	
	if(res_list_stack.length>1){
		var back_like="<a onclick=\"index_render_res_list(1,1,1,true)\">Back</a>";
		$("#res_list_back").html(back_like);
	}
  });
}
function show_par_res_in_toc(book,paragraph){
	if($("#toc_para_res_"+paragraph).html()==""){
		$.get("./get_res_index.php", { book: book, album: "-1",paragraph: paragraph },
		function(data){
			$(".toc_para_res").html("");
			$(".toc_para_res").slideUp();
			$("#toc_para_res_"+paragraph).html(data);
			$("#toc_para_res_"+paragraph).slideDown();
		});
	}
	else{
		$("#toc_para_res_"+paragraph).slideUp(300,function(){$("#toc_para_res_"+paragraph).html("");});
		
	}
}

function wizard_palicannon_heading_change(base=-1,select=-1){
	
	var output="";
	var maxLevel=100;
	var endOfPar = wizard_palicannon_get_par_end_index(base);
	if(base==-1){
		endOfPar = toc_info.length-1
		wizard_palicannon_heading_div_cls(1);
	}
	else{
		var baseLevel = toc_info[base].level;
		wizard_palicannon_heading_div_cls(baseLevel+1);
	}
	
		for(var iPar=base+1;iPar<=endOfPar;iPar++){
			parHeadingLevel=toc_info[iPar].level;
			if(parHeadingLevel>0 && parHeadingLevel<maxLevel){
				maxLevel = parHeadingLevel;
			}
		}

		for(var iPar=base+1;iPar<=endOfPar;iPar++){
			parHeadingLevel=toc_info[iPar].level;
			if(parHeadingLevel==maxLevel){
				title = toc_info[iPar].title;
				newTitle=wizard_ger_toc_title(iPar,gTocCurrLanguage);
				if(newTitle!=null){
					title=newTitle;
				}
				var cssSelected="";
				if(iPar==select){
					cssSelected="selected";
					var parent_title="<div class=\"book_list_item \"><span onclick='wizard_palicannon_heading_click("+base+","+iPar+")'>"+title+"</span></div>";
				}
				else{
					if(select!=-1){
						cssSelected="item_hidden";
					}
				}
				parNum=toc_info[iPar].paragraph;
				output += "<div class=\"book_list_item "+cssSelected+" level_"+maxLevel+"\" >";
				if(toc_type=="album"){
					output  += "<button><a href='./reader.php?book="+gCurrBookId+"&album="+toc_album+"&paragraph="+parNum+"' target='_blank'>读</a></button>";
				}
				else{
					output  += "<button onclick=\"show_par_res_in_toc('"+gCurrBookId+"',"+parNum+")\">资</button>";
				}
				output  += "<span onclick='wizard_palicannon_heading_click("+base+","+iPar+")'>"+title+"</span></div>";
				output += "<div id='toc_para_res_"+parNum+"' class='toc_para_res'></div>";
				
				
			}
		}
		$("#toc_h"+maxLevel).html(output);
		if(select==-1){
			$("#toc_h"+maxLevel).show();
		}
		else{
			$("#toc_p_h"+maxLevel).hide();
			$("#toc_p_h"+maxLevel).html(parent_title);
			$("#toc_p_h"+maxLevel).show(200);
			$("#toc_h"+maxLevel).slideUp(1000);
		}

	
	gPrevTocLevelBase=base;



}
function wizard_palicannon_heading_div_cls(from){
	
	for(var i=from;i<9;i++){
		$("#toc_p_h"+i).html("");
		$("#toc_h"+i).html("");
	}
	
}
function wizard_palicannon_heading_click(pearent,index){
	var currLevel = toc_info[index].level;
	currLevel++;
	//wizard_palicannon_heading_div_cls(currLevel);
	wizard_palicannon_heading_change(pearent,index);
	wizard_palicannon_heading_change(index,-1);
}
//获取段落终止点 
//输入：索引
//输出：索引
function wizard_palicannon_get_par_end_index(beginIndex){
	var iStartPar=0
	var iStartLevel=0
	if(beginIndex==-1){
		return(toc_info.length-1);
	}
	if(toc_info[beginIndex].level==0){
		return(beginIndex);
	}
	for(var iPar=beginIndex+1;iPar<toc_info.length;iPar++){
		parLevel=toc_info[iPar].level;
		if(parLevel>0){
			if(parLevel <= toc_info[beginIndex].level){
				return(iPar-1);
			}
		}
	}
	//没找到 返回数组最后一个索引号
	return(toc_info.length-1);
}

function wizard_palicannon_get_par_end(beginParNum){
	var iStartPar=0
	var iStartLevel=0
	for(var iPar=0;iPar<toc_info.length;iPar++){
		currParNum=toc_info[iPar].paragraph;
		if(currParNum==beginParNum){
			iStartPar=iPar;
			break;
		}
	}
	var iEnd = wizard_palicannon_get_par_end_index(iStartPar);
	/*
	for(var iPar=iStartPar+1;iPar<gXmlParIndex.length;iPar++){
		parLevel=getNodeText(gXmlParIndex[iPar],"level");
		if(parLevel>0){
			if(parLevel <= iStartLevel){
				//iEndPar=getNodeText(gXmlParIndex[iPar],"par")
				return(iPar-1);
			}
		}
	}
	return(gXmlParIndex.length-1);
	*/
	return(toc_info[iEnd].paragraph);
}
//渲染书列表
function wizard_palicannon_index_render_book(parentLevel,strParent,strSelected){
		var bookTitle= new Array();
		var bookId= new Array();	
		switch(parentLevel){
			case 1:
				var strC1=strParent;
				for(index in local_palicannon_index){
					if(local_palicannon_index[index].c1==strC1){
						pc_pushNewToList(bookTitle,local_palicannon_index[index].title);
						pc_pushNewToList(bookId,local_palicannon_index[index].id);
					}
				}						
			break;

			case 2:
				var strC2=strParent;
				for(index in local_palicannon_index){
					if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2){
						pc_pushNewToList(bookTitle,local_palicannon_index[index].title);
						pc_pushNewToList(bookId,local_palicannon_index[index].id);
					}
				}						
			break;
			case 3:
				strC3=strParent;
				for(index in local_palicannon_index){
					if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2 && local_palicannon_index[index].c3==strC3){
						pc_pushNewToList(bookTitle,local_palicannon_index[index].title);
						pc_pushNewToList(bookId,local_palicannon_index[index].id);
					}
				}				
			break;
			case 4:
			break;
		}
		

		
		
		var objBook = document.getElementById("pali_book_item_list");
		objBook.innerHTML="";
		for(index in bookTitle){
			if(bookTitle[index]==strSelected){
				var cssItem="pali_res_list_item selected";
			}
			else{
				var cssItem="pali_res_list_item";
			}
			var bookitem="";
			bookitem += "<div class='book_block' onclick=\"wizard_palicannon_index_changed_book("+parentLevel+",'"+strParent+"','"+bookTitle[index]+"','"+bookId[index]+"')\">";
			
			bookitem += "<div class='book_block_cover'>";
			bookitem += "<span class='type_flag'>mula</span>";
			bookitem += "</div>";	
			
			bookitem += "<div class='book_block_info'>";
			bookitem += "<div class='book_block_title'>";
			bookitem +=bookTitle[index];
			bookitem += "</div>";

			bookitem += "<div class='book_block_detail'>";
			bookitem += "本母";
			bookitem += "</div>";
			bookitem += "</div>";
			
			bookitem += "</div>";
			objBook.innerHTML+=bookitem;
			//objBook.innerHTML += "<div class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_book("+parentLevel+",'"+strParent+"','"+bookTitle[index]+"','"+bookId[index]+"')\">"+bookTitle[index]+"</div>";
		}
		//document.getElementById("id_wizard_palicannon_index_book").style.display="block";	
}

function wizard_palicannon_index_changed_c1(indexSelected){

	wizard_palicannon_heading_div_cls(1);
	wizard_palicannon_palitext_div_cls();
	
	wizard_palicannon_index_render_c1(indexSelected);
	wizard_palicannon_index_render_book(1,indexSelected,"");
	
	//document.getElementById("item_list").style.display="block";	
	//document.getElementById("book_info_div").style.display="none";	
	//wizard_palicannon_index_render_c2(indexSelected,"");
}

function wizard_palicannon_index_render_c1(strSelected){
	gCurrBookType=strSelected;
	
	var objC1 = document.getElementById("id_wizard_palicannon_index_c1");
	objC1.innerHTML="";

	var currStr="";
	var list= new Array();
	for(index in local_palicannon_index){
		pc_pushNewToList(list,local_palicannon_index[index].c1);
	}

	for(index in list){
		if(list[index]==strSelected){
			var cssItem="menu_list_item selected";
		}
		else{
			var cssItem="menu_list_item";
		}
		objC1.innerHTML += "<li class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_c1('"+list[index]+"')\">"+list[index]+"</li>"
	}
}

function main_menu_show(id){
	$("#page_0").hide(200);
	$("#page_1").hide(200);
	$("#page_2").hide(200);
	$("#page_3").hide(200);
	$("#page_4").hide(200);
	$("#page_"+id).show(200);
	gCurrPage=id;
}

function search_tag(search_tag){
	$.post("./tag_search.php",
			{
				tag:search_tag,
				order:"hit"
			},
			function(data,status){
				$("#category_item_list").html(data);
			});	
}
function search_best(search_type){
	$.get("./get_best.php",
			{
				op:search_type,
			},
			function(data,status){
				$("#page_best_content").html(data);
			});	
}

function toc_show_level(level){
	for(var i=1;i<8;i++){
		if(i<=level){
			$(".toc_level_"+i).show();
		}
		else{
			$(".toc_level_"+i).hide();
		}
	}
	$(".toc_para_res").html("");
}

function toc_hide_show(){
	$("#album_info_head").slideToggle("slow");
}