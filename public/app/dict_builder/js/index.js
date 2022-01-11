var new_word_list2=new Array();
var dict_id=-1;
var gCurrWordId=-1;

	function init(){
		$("#replace_table_main").load("replace_table/"+gDictId+"_main.txt");
		$("#replace_table_pali").load("replace_table/"+gDictId+"_pali.txt");
		$("#replace_table_type").load("replace_table/"+gDictId+"_type.txt");
		$("#replace_table_part").load("replace_table/"+gDictId+"_part.txt");
	}
	
	function mean_keyup(obj,id){
		var newmean=obj.value;
		$("#mean_list_"+id).html(render_mean_list(newmean));
	}
	function render_mean_list(strMean){
		var output="";
		var arrMean=strMean.split("$");
		for(var i in arrMean){
			output+="<div class='one_mean'>"+arrMean[i]+"<span>X</span></div>";
		}
		return(output);
	}	
	
	function dict_changed(obj){
				var dict_id=obj.value;
				window.location.assign("index.php?dict_id="+dict_id);
	}
	function select_word(id){
		gCurrWordId=id;
		  $("#org_data").load('./get_one_word.php?id='+id,function(responseTxt,statusTxt,xhr){
			if(statusTxt=="success"){
				var orgword=$("#word_org_text").html();
				$("#org_edit_text").val(split_word(orgword));
				org_edit_changed();
			}
			if(statusTxt=="error")
				alert("Error: "+xhr.status+": "+xhr.statusText);
		  });
	}
	function refresh_word(){
		var orgword=$("#word_org_text").html();
		$("#org_edit_text").val(split_word(orgword));
		org_edit_changed();
	}
	function split_word(strIn){
		var output="w@"+strIn;
		
		var replace_table=$("#replace_table_main").val().split("\n");
		try{
			
			for(var x in replace_table){
				if(replace_table[x].replace(/\s/g,"").length>0){
					eval("output = output.replace("+replace_table[x]+");");
				}
			}
		}
		catch(e){
			notify(e.message);
		}
		

		return(output);
	}
	
	function word_obj_clone(obj){
		var new_word=create_word_obj();
		new_word.pali=obj.pali;
		new_word.type=obj.type;
		new_word.gramma=obj.gramma;
		new_word.parent=obj.parent;
		new_word.mean=obj.mean;
		new_word.note=obj.note;
		new_word.factor=obj.factor;
		new_word.factor_mean=obj.factor_mean;
		return(new_word);
	}
	
	function create_word_obj(){
		var new_word=new Object();
		new_word.pali="";
		new_word.type="";
		new_word.gramma="";
		new_word.parent="";
		new_word.mean="";
		new_word.note="";
		new_word.factor="";
		new_word.factor_mean="";
		//new_word.clone=word_obj_clone(new_word);
		return(new_word);
	}
	
	function org_edit_changed(){
		var word_string = document.getElementById("org_edit_text").value;
		var arrField=word_string.split("\n");
		var mather_word=new Array();
		var arrWord=new Array();
		var iCurrWord=-1;
		for(var x in arrField){
			var arrOne = arrField[x].split("@");
			switch(arrOne[0]){
				case "w":
					var new_word=create_word_obj();
					
					arrWord.push(new_word);
					iCurrWord=arrWord.length-1;
					if(arrOne[1].length>1 && mather_word==""){
						mather_word=arrOne[1].replace(/，/g,"$");
					}
					arrWord[iCurrWord].pali=arrOne[1].replace(/，/g,"$");
					
					
					if(arrWord[iCurrWord].pali.substring(0,1)=="-"){
						var cp=mather_word+arrWord[iCurrWord].pali;
						var arr_cp=cp.split(" ");
						arrWord[iCurrWord].pali=arr_cp[0]
						arrWord[iCurrWord].factor=arr_cp[0];
						if(arr_cp[1]){
						arrWord[iCurrWord].mean=arr_cp[1];
						}
					}
					if(arrWord[iCurrWord].pali[0]=="【"){
						var pali = arrWord[iCurrWord].pali;
						arrWord.splice(iCurrWord,1);
						var arr_word_case=pali.split("】");
						var type=arr_word_case[0];
						var arr_word=arr_word_case[1].split("，");
						for(var x in arr_word){
							var new_word=create_word_obj();
							new_word.pali=arr_word[x];
							new_word.type=type+"】";
							new_word.parent=mather_word;
							arrWord.push(new_word);
							iCurrWord=arrWord.length-1;	
						}
					}
					
					if(arrWord[iCurrWord].pali.substring(0,1)=="{"){
						arrWord[iCurrWord].pali=mather_word;
					}
					
					break;
				case "c":
					arrWord[iCurrWord].type=arrOne[1];
					if(arrOne[1].indexOf(" の ")>=0){
						var arr_type=arrOne[1].split(" の ");
						arrWord[iCurrWord].type=arr_type[1];
						arrWord[iCurrWord].parent=arr_type[0];
					}
				break;
				case "g":
					arrWord[iCurrWord].gramma=arrOne[1];
				break;
				case "p":
					arrWord[iCurrWord].parent=arrOne[1];
				break;
				case "m":
					arrWord[iCurrWord].mean=arrOne[1].replace(/，/g,"$");
				break;
				case "n":
					arrWord[iCurrWord].note=arrOne[1];
				break;
				case "f":
					arrWord[iCurrWord].factor=arrOne[1];
				break;
				case "fm":
					arrWord[iCurrWord].factor_mean=arrOne[1];
				break;
				case "cw":
				arrWord[iCurrWord].pali=arrOne[1];
				arrWord[iCurrWord].parent=mather_word;
				break;
			}
		}
		
		//将一条记录包含多词头的 拆开
		var new_word_list=new Array();
		for(var row in arrWord){
			if(arrWord[row].pali.indexOf("$")>=0){
				var pali=arrWord[row].pali.split("$");
				for(var iWord in pali){
					var new_word=word_obj_clone(arrWord[row]);
					new_word.pali=pali[iWord];
					new_word_list.push(new_word);
				}
			}
			else{
				new_word_list.push(arrWord[row]);
			}
		}
		
		new_word_list2=new Array();
		for(var row in new_word_list){
			if(new_word_list[row].parent.indexOf("$")>=0){
				var wparent=new_word_list[row].parent.split("$");
				for(var iWord in wparent){
					var new_word=word_obj_clone(new_word_list[row]);
					new_word.parent=wparent[iWord];
					new_word_list2.push(new_word);
				}				
			}
			else{
				new_word_list2.push(new_word_list[row]);
			}
		}
		
		//整理type
		for(var row in new_word_list2){
			if(new_word_list2[row].type=="" && new_word_list2[row].pali.slice(-2)=="ti"){
				new_word_list2[row].type=".v:base.";
			}
			new_word_list2[row].type+="#";
			
			var replace_table=$("#replace_table_type").val().split("\n");
			try{
				
				for(var x in replace_table){
					if(replace_table[x].replace(/\s/g,"").length>0){
						eval("new_word_list2[row].type = new_word_list2[row].type.replace("+replace_table[x]+");");
					}
				}
			}
			catch(e){
				notify(e.message);
			}
			
			var arrcase=new_word_list2[row].type.split("#");
			new_word_list2[row].type=arrcase[0];
			new_word_list2[row].gramma=arrcase[1];
		}				
		
		var output ="<table>";
			output += "<tr>";
				output += "<th>pali</th>";	
				output += "<th>type</th>";	
				output += "<th>gramma</th>";	
				output += "<th>parent</th>";	
				output += "<th>mean</th>";	
				output += "<th>note</th>";					
				output += "<th>factor</th>";	
			output += "</tr>";		
		for(var row in new_word_list2){
			if(new_word_list2[row].pali[0]=="【"){
				//break;
			}
			output += "<tr>";
				output += "<td>";
				output += new_word_list2[row].pali;
				output += "</td>";	
				output += "<td>";
				output += new_word_list2[row].type;
				output += "</td>";	
				output += "<td>";
				output += new_word_list2[row].gramma;
				output += "</td>";	
				output += "<td>";
				output += new_word_list2[row].parent;
				output += "</td>";	
				output += "<td>";
				output += new_word_list2[row].mean;
				output += "</td>";	
				output += "<td>";
				output += new_word_list2[row].note;
				output += "</td>";					
				output += "<td>";
				output += new_word_list2[row].factor;
				output += "</td>";	
			output += "</tr>";
		}
		output +="</table>";
		$("#word_table").html(output);
	}
	
function res_word_selected(obj,word_id){
	gCurrWordId=word_id;
	select_word(word_id);
	$(".ref_word").removeClass("active");
	obj.classList.add("active");
}

function replace_table_show(obj){
	$(".replace_table").hide();
	$("#replace_table_"+obj.value).show();
}

function save(edit_status){
	
	var word_data=JSON.stringify(new_word_list2);
	 $.post("./save.php",
	  {
		dict_id:gDictId,
		word_id:gCurrWordId,
		word_status:edit_status,
		data:word_data
	  },
	  function(data,status){
		  notify(data + "<br>Status: " + status);
		  $("#word_"+gCurrWordId).removeClass("status_2 status_3 status_10");
		  $("#word_"+gCurrWordId).addClass("status_"+edit_status);
		  
	  });

}
function save_replace_table(){
	 $.post("./save_replace_table.php",
	  {
		dict_id:gDictId,
		main:$("#replace_table_main").val(),
		pali:$("#replace_table_pali").val(),
		part:$("#replace_table_part").val(),
		type:$("#replace_table_type").val()
	  },
	  function(data,status){
		  notify(data + "<br>Status: " + status);
		  
	  });
}

function notify(message,time=5000){
	 $("#message_text").html(message);
	 $("#message").slideDown();
	 
}

function close_notify(){
	 $("#message").slideUp();
}

function final_word_show_hide(){
	$("#final_word_body").slideToggle();
}

function goto_page(obj,dict_id,totle_page){
	var newpage=obj.value;
	if(newpage>=0 && newpage<totle_page){
		window.location.assign("./index.php?dict_id="+dict_id+"&page_no="+newpage);

	}
}