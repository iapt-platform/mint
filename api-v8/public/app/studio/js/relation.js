var gRelationSelectWordBegin=false;
function rela_link_click(add=true){
	$("#word_tool_bar").hide(1000);
	$("#"+gCurrMoseEnterWordId).css("border","none");
	gWordHeadBarVisible = false;
	var eWin = document.getElementById("modifywin");
	if(eWin){
		eWin.style.display="block";
		gRelationSelectWordBegin=false;
		if(add){
			let linkOrg = g_currEditWord;
			let linkTo = _curr_mouse_enter_wordid;
			rela_add(linkOrg , linkTo);
			rela_refresh();
		}
	}
}

function rela_refresh(){
	$("#relation_div").html(rela_render_one_word());
}
function rela_add_word(){
	var eWin = document.getElementById("modifywin");
	if(eWin){
		eWin.style.display="none";
		gRelationSelectWordBegin=true;
	}
}

function rela_word_cancel(){

}

function rela_add(sour,dest){

	let xSour = doc_word("#"+sour);
	let xDest = doc_word("#"+dest);
	let newLink = new Object();
	newLink.sour_id=sour;
	newLink.sour_spell=xSour.val("real");
	newLink.dest_id = dest;
	newLink.dest_spell=xDest.val("real");
	newLink.relation="";
	newLink.note="";
	let wordRelation = $("#id_relation_text").val();
	let relaData; 
	if(wordRelation==""){
		relaData = new Array();
		relaData.push(newLink);
	}
	else{
		try{
			relaData=JSON.parse(wordRelation);
			relaData.push(newLink);
		}
		catch(e){
			console.error(e+" data:"+wordRelation);
			return(false);
		}
	}
	$("#id_relation_text").val(JSON.stringify(relaData));
	console.log(relaData);
	
}
function rela_render_one_word(wordid){
	let wordRelation = $("#id_relation_text").val();
	let relaData; 
	let output="";
	if(wordRelation==""){
		return("");
	}
	else{
		try{
			relaData=JSON.parse(wordRelation);
			for(x in relaData){
				output += "<div><button style='padding: 1px 6px;' onclick=\"rela_del('"+x+"')\">"
				output += "<svg class='icon'>";
				output += "<use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='svg/icon.svg#ic_clear'></use>";
				output += "</svg>";

				output += "</button>";
				let grammar = "";
				grammar = $("#input_case").val().split("#")[1];

				output += relaData[x].dest_spell + ":"+rela_render_context(wordid,x,relaData[x].relation,grammar);
				output += "</div>";
			}
		}
		catch(e){
			console.error(e+" data:"+wordRelation);
			return("error");
		}
	}
	
	return(output);
}

function rela_del(index){
	let wordRelation = $("#id_relation_text").val();
	let relaData; 
	if(wordRelation!=""){
		try{
			relaData=JSON.parse(wordRelation);
			if(index>=0 && index<relaData.length){
				relaData.splice(index,1);
				$("#id_relation_text").val(JSON.stringify(relaData));
				rela_refresh();
			}
			
		}
		catch(e){
			console.error(e+" data:"+wordRelation);
			return(false);
		}
	}
	

}
//
function rela_render_context(wordid,index,strRela,grammar){
	let output = "";
	output += "<div class=\"case_dropdown rela_menu\" style='display: inline-block'>";
	output += "<p class=\"case_dropbtn\">";
	if(strRela==""){
		output += "<span class='relation_blank'>"+gLocal.gui.relation+"</span>";
	}
	else{
		output += "<span class='relation_text'>"+strRela+"</span>";
	}
	output += "</p>";
	output += "<div class=\"case_dropdown-content\">";
	output += "<div >";
	let language=getCookie("language")
	for(let x in list_relation){
		if((grammar.indexOf(list_relation[x].case)>=0 || list_relation[x].case=="") && language==list_relation[x].language){
			output += "<a onclick=\"rela_menu_item_click('"+wordid+"','"+index+"','"+list_relation[x].id+"')\"><span class='rela_bold'>"+list_relation[x].id+"</span>("+list_relation[x].note+")</a>";
		}
	}
	output += "	</div>";
	output += "</div>";
	output += "</div>";
	return(output);
}

function rela_menu_item_click(wordid,index,str){
	let xWord = doc_word("#"+wordid);
	let wordRelation = $("#id_relation_text").val();
	let relaData; 
	let output;
	if(wordRelation==""){
		return;
	}
	else{
		try{
			relaData=JSON.parse(wordRelation);
			if(index<relaData.length){
				relaData[index].relation=str;
			}
			else{
				console.error("rela_menu_item_click-out of relaData array");
			}
			
		}
		catch(e){
			console.error(e+" data:"+wordRelation);
			return;
		}
	}
	$("#id_relation_text").val(JSON.stringify(relaData));

	rela_refresh();
}
//set visibility of relation
function rela_visibility(obj){
	
}

function relation_link_show(wordid){
	let strRelation = doc_word("#"+wordid).val("rela");
	let relaData;
		try{
			if(strRelation!=""){
				relaData=JSON.parse(strRelation);
				if(relaData.length>0){
					for(let x =0 ; x<relaData.length;x++){
						let wordid=relaData[x].dest_id;
						let divRelaTag=document.createElement("rela");
						let typ=document.createAttribute("class");
						
						typ.nodeValue="relation_tag";
						divRelaTag.attributes.setNamedItem(typ);

						let typId=document.createAttribute("id");
						typId.nodeValue="rt_"+x;
						divRelaTag.attributes.setNamedItem(typId);

						let eWord=document.getElementById("wb"+wordid);
						let eWordHead=document.getElementById("whead_"+wordid);
						eWord.insertBefore(divRelaTag,eWordHead);
						divRelaTag.innerHTML=relaData[x].relation;
					}
				}
			}

		}
		catch(e){
			console.error(e);
		}

}
function relation_link_hide(){
	$("rela").remove();	
}

function updateWordRelation(element){
	let id=getNodeText(element,"id");
	$("#wnr_"+id).html(renderWordRelation(element));
}
function renderWordRelationByString(pali,rela,wid){
	let paliword=pali;
	let wRelation=rela;
	let id=wid;
	let output="";
	
	if(wRelation==""){
		return("");
	}
	else{
		let relaData;
		try{
			relaData=JSON.parse(wRelation);
			if(relaData.length>0){
				for(let x in relaData){
					output += "<strong>"+paliword +"</strong>—<span class='relation_note'>"+relaData[x].relation+"</span>→<strong>"+relaData[x].dest_spell+"</strong><br>";
				}
			}
			else{
				return("");
			}
		}
		catch(e){
			return("");
			console.error(e+" data:"+wRelation);
			return(false);
		}
		return(output);	
	}	
}
function renderWordRelation(element){
	let paliword=getNodeText(element,"real");
	let wRelation=getNodeText(element,"rela");
	let id=getNodeText(element,"id");

	return(renderWordRelationByString(paliword,wRelation,id));
}