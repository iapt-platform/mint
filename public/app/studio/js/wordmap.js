var wm_max_deep=10;
var wm_curr_deep=0;
var wm_word_box_count=0;
var wm_word_array=null;
var wm_render_mode=false;  //false 横向  true 纵向
var wm_show_mode=0;  //0 最简单  1 复杂
var rootId;
var wm_array_word_for_save;
var wm_word_base_curr_focuse=-1;
var wm_case=new Array()

function show_word_map(g_currEditWord){

	//closeModifyWindow();//关闭单词修改窗口
	wm_word_box_count=0;
	wm_word_array=new Array();
	wm_array_word_for_save=new Array();
	wm_word_base_curr_focuse=-1;
	
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");	
	wid=getWordIndex(g_currEditWord);
	var sReal = getNodeText(xAllWord[wid],"real");
	rootId=wm_create_root_word(g_currEditWord);
	wm_updata_word(rootId,null);
	var strWordMapWin="<div class='wm_div'>";
	strWordMapWin += "<div id='wm_title'></div>";
	strWordMapWin += "<div id='wm_body'>";
	strWordMapWin += "<div id='wm_body_inner'>";
	strWordMapWin += "<div id='wm_word_block_0'>";//套殻
	strWordMapWin += "</div>";//套殻
	strWordMapWin += "</div>";
	strWordMapWin += "</div>";
	strWordMapWin += "</div>";
	getStyleClass("pop_win_full").style.display = "-webkit-flex";
	getStyleClass("pop_win_full").style.animation = "2s";
	document.getElementById("pop_win_inner").innerHTML=strWordMapWin;
	wm_updata_title();
	wm_updata_word_map();
	wm_render_line();
}

function pop_win_close(){
	document.getElementById("pop_win_inner").innerHTML = "";
	getStyleClass("pop_win_full").style.display = "none";
}
function wm_render_title(){
	var output = "<div><span id=\"wm_doc_notify\" style=\"position: absolute;top: 3em;\"></span>";
	output += "<button onclick='pop_win_close()'>Close</button>";

	if(wm_render_mode){
		output += "<ul class='radio-button'><li onclick=\"wm_change_mode(false)\">↔</li><li class='li_act'>↕</li></ul>";		

	}
	else{
		output += "<ul class='radio-button'><li class='li_act'>↔</li><li onclick=\"wm_change_mode(true)\">↕</li></ul>";
	}
	
	switch(wm_show_mode){
		case 0:
		output += "<ul class='radio-button'><li class='li_act'>−</li><li onclick=\"wm_change_show_mode(1)\">≡</li><li onclick=\"wm_change_show_mode(2)\">Edit</li></ul>";
		break;
		case 1:
		output += "<ul class='radio-button'><li onclick=\"wm_change_show_mode(0)\">−</li><li class='li_act'>≡</li><li onclick=\"wm_change_show_mode(2)\">Edit</li></ul>";
		break;
		case 2:
		output += "<ul class='radio-button'><li onclick=\"wm_change_show_mode(0)\">−</li><li onclick=\"wm_change_show_mode(1)\">≡</li><li  class='li_act'>Edit</li></ul>";
		break;

	}
	output += "</div>";
	
	output += "<div><span>Word Map</span></div>";
	output += "<div><button onclick='wm_refresh_inline_dict(0)'>Look Up</button><button onclick='wm_save_node_all()'>Save</button><div>";
	return(output);
}
function wm_updata_title(){
	document.getElementById("wm_title").innerHTML=wm_render_title();;
}

function wm_updata_word_map(){
	document.getElementById("wm_word_block_0").innerHTML=wm_render_word_map(rootId);;
//	document.getElementById("wm_body").innerHTML=wm_render_word_map(rootId);;
}

function wm_change_mode(newmode){
	wm_word_box_count=0;
	wm_render_mode=newmode;
	wm_updata_title();
	wm_updata_word_map();
	wm_render_line();
}

function wm_change_show_mode(newmode){
	wm_word_box_count=0;
	wm_show_mode=newmode;
	wm_updata_title();
	wm_updata_word_map();
	wm_render_line();
}

function wm_create_root_word(wordid){
	wid=getWordIndex(wordid);
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");	
	var sReal = getNodeText(xAllWord[wid],"real");
	
	var newWord=new Object();
	var indexOfExist=wm_find_exist_part(sReal);
	if(indexOfExist){
		newWord.is_new=false;
		newWord.id=g_DictWordList[indexOfExist].Guid;
		newWord.part=g_DictWordList[indexOfExist].Factors;
		newWord.partmean=g_DictWordList[indexOfExist].FactorMean;
		newWord.partid=g_DictWordList[indexOfExist].PartId;
	}
	else{
		newWord.is_new=true;
		newWord.id=com_guid();
		newWord.part=getNodeText(xAllWord[wid],"org");
		newWord.partmean=getNodeText(xAllWord[wid],"om");
		newWord.partid=wm_get_best_partid(newWord);
	}
	newWord.pali=sReal;
	newWord.real=sReal;
	newWord.type=getNodeText(xAllWord[wid],"case").split("#")[0];
	newWord.gramma=getNodeText(xAllWord[wid],"case").split("#")[1];
	newWord.mean=getNodeText(xAllWord[wid],"mean");
	newWord.note=getNodeText(xAllWord[wid],"note");

	newWord.wparent=getNodeText(xAllWord[wid],"parent").replace(/ /g,"");//去空格
	newWord.parentid="";

	newWord.parentNode=null;
	newWord.deep=0;
	wm_word_array.push(newWord);
	return(newWord.id);
}

function wm_find_exist_part(word){
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word && g_DictWordList[x].Guid!=""){
			return(x);
		}
	}
	return(false);
}

function wm_create_word(word,deep,parentNodeIndex){
	var newWord=new Object();
	newWord.id=com_guid();
	newWord.pali=word;
	if(word.substring(0,1)=='['){
		newWord.real=word;
	}
	else{
		newWord.real=com_getPaliReal(word);
	}
	newWord.type="";
	newWord.gramma="";
	newWord.mean="";
	newWord.note="";
	newWord.wparent="";
	newWord.parentid="";
	newWord.part='?';
	newWord.partmean="";
	newWord.partid="";
	newWord.parentNode=parentNodeIndex;
	newWord.deep=deep;
	newWord.is_new=true;
	wm_word_array.push(newWord);
	return(newWord.id);
}

function wm_create_word_by_index(index,deep,parentNodeIndex){
	var newWord=new Object();
	newWord.id=g_DictWordList[index].Guid;
	newWord.pali=g_DictWordList[index].Pali;
	if(g_DictWordList[index].Real){
		newWord.real=g_DictWordList[index].Real;
	}
	else{
		newWord.real=g_DictWordList[index].Pali;
	}
	newWord.type=g_DictWordList[index].Type;
	newWord.gramma=g_DictWordList[index].Gramma;
	newWord.mean=g_DictWordList[index].Mean;
	newWord.note=g_DictWordList[index].Note;
	newWord.wparent=g_DictWordList[index].Parent;
	newWord.parentid="";
	newWord.part=g_DictWordList[index].Factors;
	newWord.partmean=g_DictWordList[index].FactorMean;
	newWord.partid=g_DictWordList[index].PartId;
	newWord.deep=deep;
	newWord.parentNode=parentNodeIndex;
	newWord.is_new=false;
	wm_word_array.push(newWord);
	return(newWord.id);
}



function wm_updata_word(wordId,parentId){
	var wordindex=wm_find_word_by_id(wordId);
	var parentType="null";
	var parentPart="null";
	if(parentId){
		parentIndex=wm_find_word_by_id(parentId);
		parentType = wm_word_array[parentIndex].type;
		parentPart = wm_word_array[parentIndex].part;
	}
	
	if(wordindex){
		var word=wm_word_array[wordindex];
		if(word.deep>wm_max_deep){
			return;
		}
		if(word.type=="" && word.is_new){
			word.type=wm_get_best_type(word,parentType);
		}
		if(word.gramma=="" && word.is_new){
			wm_get_best_gramma(word);
		}
		if(word.mean==""  && word.is_new){
			word.mean=wm_get_best_mean(word);
		}
		if(word.wparent=="" && word.is_new){
			word.wparent=wm_get_best_parent(word);
		}
		if(word.part=='?'  && word.is_new){
			word.part=wm_get_best_part(word);
			if(word.part==""){
				word.part="?";
			}
		}
		if(word.part==parentPart){
			return;
		}
		var newDeep=word.deep+1;
		if(word.partid==""){/*没有现成的id 需要新建*/
			if(word.part!="" && word.part!="?"){
				var arrNewPartId= new Array();
				var arrPart=word.part.split("+");
				for(var iPart in arrPart){
					var find_guid_index=wm_find_first_part_index(arrPart[iPart],word.type);
					var newPartId="";
					if(find_guid_index){
						var find_guid=g_DictWordList[find_guid_index].Guid;
						if(!wm_id_exists(find_guid)){
							newPartId = wm_create_word_by_index(find_guid_index,newDeep,wordindex);
						}
						else{
							newPartId = find_guid;
						}
					}
					else{
						newPartId=wm_create_word(arrPart[iPart],newDeep,wordindex);
						
					}
					arrNewPartId.push(newPartId);
					wm_updata_word(newPartId,wordId);
				}
				word.partid=arrNewPartId.join("+");
			}
		}
		else{
			var arrPartId=word.partid.split("+");
			for(var iPart in arrPartId){
				if(arrPartId[iPart]==wm_word_array[wordindex].id){/*防止循环引用*/
					return;
				}
			}
			for(var iPart in arrPartId){
				if(arrPartId[iPart]!=""){
					if(wm_id_exists(arrPartId[iPart])){
						wm_updata_word(arrPartId[iPart],wordId);
					}
					else{
						for(var i in g_DictWordList){
							if(g_DictWordList[i].Guid==arrPartId[iPart]){
								newPartId = wm_create_word_by_index(i,newDeep,wordindex);
								wm_updata_word(newPartId,wordId);						
							}
						}
					}
				}
			}
		}
	}
}

//在内联字典中查找第一个匹配的零件 返回索引
function wm_find_first_part_index(word,parentType){
	var typeString="";
	for(var x in wordmap_child_type){
		if(wordmap_child_type[x].type==parentType){
			typeString=wordmap_child_type[x].value;
		}
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word && g_DictWordList[x].Type!=""){
			//language filter
			if(dict_language_enable.indexOf(g_DictWordList[x].Language)>=0){			
				if(typeString.indexOf(g_DictWordList[x].Type)>=0 && g_DictWordList[x].Guid.length>0){
					return(x);
				}
			}
		}
	}
	return(false);

}

function wm_get_best_partid(word){
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && g_DictWordList[x].PartId!=""){
			return(g_DictWordList[x].PartId);
		}
	}
	return("");
}


function wm_get_best_type(word,parentType){
	var typeString="";
	for(var x in wordmap_child_type){
		if(wordmap_child_type[x].type==parentType){
			typeString=wordmap_child_type[x].value;
		}
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && g_DictWordList[x].Type!=""){
			if(typeString==""){
				return(g_DictWordList[x].Type);
			}
			else{
				if(typeString.indexOf(g_DictWordList[x].Type)>=0){
					return(g_DictWordList[x].Type);
				}
			}
		}
	}
	return("");
}
		
function wm_get_best_gramma(word){
	if(word.type==""){
		return("");
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && wm_type_match(g_DictWordList[x].Type,word.type)){
			if(g_DictWordList[x].Gramma!=""){
				return(g_DictWordList[x].Gramma);
			}
		}
	}
	return("");	
}

//在内联字典中查找第一个匹配的意思
function wm_get_best_mean(word){
	if(word.type==""){
		return("");
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && wm_type_match(g_DictWordList[x].Type,word.type)){
			if(dict_language_enable.indexOf(g_DictWordList[x].Language)>=0){
				if(g_DictWordList[x].Mean!=""){
					return(g_DictWordList[x].Mean.split("$")[0]);
				}
			}
		}
	}
	return("");	
}

function wm_type_match(type1,type2){
	if(type1==".ti:base" || type1==".adj:base."){
		if(type2==type1==".ti:base" || type1==".adj:base."){
			return(true);
		}
	}
	else{
		if(type1==type2){
			return(true);
		}
	}
	return(false);
}

function wm_get_best_parent(word){
	if(word.type==""){
		return("");
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && wm_type_match(g_DictWordList[x].Type,word.type)){
			if(g_DictWordList[x].Parent!=""){
				return(g_DictWordList[x].Parent);
			}
		}
	}
	return("");		
}
function wm_get_best_part(word){
	if(word.type==""){
		return("");
	}
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word.real && wm_type_match(g_DictWordList[x].Type,word.type)){
			if(g_DictWordList[x].Factors!=""){
				return(g_DictWordList[x].Factors);
			}
		}
	}
	return("");		
}
		
function wm_find_word_by_id(wordId){
	for(xWord in wm_word_array){
		if(wm_word_array[xWord].id==wordId){
			return(xWord);
		}
	}
	return(false);
}

function wm_render_word_map(wordId){
	wm_curr_deep=0;
	if(wm_render_mode){
		return(wm_render_word_block_H(wordId));
	}
	else{
		return(wm_render_word_block(wordId));
	}
}

function wm_render_word_block(wordId){
	if(wm_curr_deep>=wm_max_deep){
		return;
	}
	wm_word_box_count++;
	wm_curr_deep++;
	var wordIndex = wm_find_word_by_id(wordId);
	if(wordIndex){
		var output="";
		output += "<div class='wm_word_block'>";
		output += "<table><tr>";
		output += "<td>";
		output += wm_render_word_base(wordId);
		output += "</td>";
		output += "<td id='wm_line_"+(wm_word_box_count-1)+"' width='50px'>";
		output += "</td>";
		output += "<td><div id='wm_children_"+(wm_word_box_count-1)+"'>";
		
		if(wm_word_array[wordIndex].partid.length>0){
			var parts=wm_word_array[wordIndex].partid.split("+");
			for(var x in parts){
				if(parts[x]==wm_word_array[wordIndex]){
					wm_notify("Error:part ID loop link!");
					return;
				}
			}			
			for(var x in parts){
				output += wm_render_word_block(parts[x]);
			}
		}
		output += "</div></td>";	
		output += "</tr></table>";	
		output += "</div>";
		wm_curr_deep--;

		return(output);
	}
}

function wm_render_word_block_H(wordId){
	if(wm_curr_deep>=wm_max_deep){
		return;
	}
	wm_word_box_count++;
	wm_curr_deep++;
	var wordIndex = wm_find_word_by_id(wordId);
	if(wordIndex){
		var output="";
		output += "<div class='wm_word_block'>";
		output += "<div class='wm_root_div'>";
		output += wm_render_word_base(wordId);
		output += "</div>";
		output += "<div id='wm_line_"+(wm_word_box_count-1)+"' class=\"wm_line\">";
		output += "</div>";
		output += "<div><div id='wm_children_"+(wm_word_box_count-1)+"' class=\"wm_child_div\">";
		
		if(wm_word_array[wordIndex].partid.length>0){
			var parts=wm_word_array[wordIndex].partid.split("+");
			for(var x in parts){
				output += wm_render_word_block_H(parts[x]);
			}
		}
		output += "</div></div>";	
		output += "</div>";
		wm_curr_deep--;

		return(output);
	}
}


function wm_render_word_base(wordId){
	var wordIndex = wm_find_word_by_id(wordId);
	var output="";
	output += "<div class='wm_word_block_root_shell' id='wm_word_block_root_shell_"+wordIndex+"'>";
	switch(wm_show_mode){
		case 0:
		output +=wm_render_word_base_read(wordIndex);
		break;
		case 1:
		output +=wm_render_word_base_read2(wordIndex);
		break;
		case 2://edit
		output +=wm_render_word_base_edit(wordIndex);
		break;
	}
	output += "</div>";
	return(output);
}

function wm_updata_word_root_edit(wordIndex){
		document.getElementById("wm_word_block_root_shell_"+wordIndex).innerHTML=wm_render_word_base_edit(wordIndex);	
}
function wm_updata_word_root_read(wordIndex){
	document.getElementById("wm_word_block_root_shell_"+wordIndex).innerHTML=wm_render_word_base_read(wordIndex);
}
function wm_updata_word_root_read2(wordIndex){
	document.getElementById("wm_word_block_root_shell_"+wordIndex).innerHTML=wm_render_word_base_read2(wordIndex);
}
//编辑模式渲染
function wm_render_word_base_edit(wordIndex){
	var isNewClass="";
	var wm_case = new Array();
	var typeList=wm_get_word_type_list(wordIndex);
	var caseList=wm_get_word_gramma_list(wordIndex);
	if(typeList.length!=0 && typeList[0].type){
		wm_case.push(typeList[0].type);
		if(caseList.length!=0 && caseList[0].gramma){
			var wm_case_array=caseList[0].gramma.split("$")
			for(get_case_i in wm_case_array){
				wm_case.push(wm_case_array[get_case_i]);
			}
		}
		else{
			wm_case[1]=""
			wm_case[2]=""
			wm_case[3]=""
		}
	}
	else{
		wm_case.push("?");
		wm_case[1]=""
		wm_case[2]=""
		wm_case[3]=""
	}


	var sameRecorderMenu=wm_render_same_word_recoder_menu_for_read(wordIndex);
	var output="";
	if(wm_word_array[wordIndex].is_new){
		isNewClass=" word_new ";
	}
	if(wm_render_mode){
		output += "<div class='wm_word_block_root wm_word_block_root_h"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	else{
		output += "<div class='wm_word_block_root wm_word_block_root_v"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	var sameRecorderCount=wm_get_same_word_recorder_count(wordIndex);
	output += "<div class='toolbar'>";
	output += "<span style='margin-right: auto'>"
	output += "<input type='checkbox' checked>";
	output += gLocal.gui.read_only;
	output += "</span>"
	output += "<button style='min-width:4em; display:inline-flex; padding:0.1em 0.5em; margin: auto 0;' ";
	output += "onclick='wm_recorder_change("+wordIndex+",null)'>"
	output += gLocal.gui.new
	output += "</button>";
	output += "</div>";
	output += "<div>";
	output += "<a onclick='wm_look_up(\""+wm_word_array[wordIndex].real+"\")'>"+wm_word_array[wordIndex].pali;
	output += "</a></div>";
	output += "<div id='wm_edit_gramma_"+wordIndex+"' style='height:1.6em; margin:0.3em 0'>";
	output += wm_render_word_typegramma_edit(wm_case[0],wm_case[1],wm_case[2],wm_case[3],wordIndex,0.5)+"</div>";
	//output += wm_render_word_type_edit(wordIndex);
	//output += wm_render_word_gramma_edit(wordIndex);
	output += "<div style='height:1.6em; margin:0.3em 0'>";
	output += wm_render_word_parent_edit(wordIndex)+"</div>";
	output += "<div style='height:1.6em; margin:0.3em 0'>";
	output += wm_render_word_mean_edit(wordIndex)+"</div>";
	output += "<div style='height:1.6em; margin:0.3em 0'>";
	output += wm_render_word_part_edit(wordIndex)+"</div>";	
	output += "</div>";	
	return(output);
}
//閲讀模式渲染
function wm_render_word_base_read(wordIndex){
	var output="";
	var sameRecorderMenu=wm_render_same_word_recoder_menu_for_read(wordIndex);
	var isNewClass="";
	if(wm_word_array[wordIndex].is_new){
		isNewClass=" word_new ";
	}
	if(wm_render_mode){
		output += "<div class='wm_word_block_root wm_word_block_root_h"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	else{
		output += "<div class='wm_word_block_root wm_word_block_root_v"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	var sameRecorderCount=wm_get_same_word_recorder_count(wordIndex);
	output += "<div>"
	output += "<a onclick='wm_look_up(\""+wm_word_array[wordIndex].real+"\")'>"
	output += wm_word_array[wordIndex].pali;//巴利拼寫加查詞
	output += "</a></div>"
	var arrMean=wm_word_array[wordIndex].mean.split("$");
	var strMeanMenu="";
	if(arrMean.length>1){
		strMeanMenu=wm_render_word_mean_menu(wordIndex,arrMean);
	}
	else{
		strMeanMenu=arrMean[0];
	}
	if(wm_word_array[wordIndex].wparent!=" " && wm_word_array[wordIndex].wparent.length>0){//有parent信息
		output += "<div>"+strMeanMenu+"</div>";	
		output += "<div class='case_dropdown wm_gramma_info'>";
		output += "<span class='cell'>";
		output += getLocalGrammaStr(wm_word_array[wordIndex].type);//類型
		output += "</span>";
		output += getLocalGrammaStr(wm_word_array[wordIndex].gramma);
		output += "<i> *";
		output += wm_word_array[wordIndex].wparent;
		output += "</i></div>";	
	}
	else{//无parent 信息
		output += "<div>"+strMeanMenu+"</div>";	
		output += "<div class='case_dropdown wm_gramma_info'>";
		output += "<span class='cell'>";
		output += getLocalGrammaStr(wm_word_array[wordIndex].type);//類型
		output += "</span>";
		output += getLocalGrammaStr(wm_word_array[wordIndex].gramma);
		output += "</div>";
	}

	output += "</div>";	
	return(output);
}
//閲讀模式二渲染
function wm_render_word_base_read2(wordIndex){
	var output="";
	var sameRecorderMenu=wm_render_same_word_recoder_menu_for_read(wordIndex);
	var isNewClass="";
	if(wm_word_array[wordIndex].is_new){
		isNewClass=" word_new ";
	}
	if(wm_render_mode){
		output += "<div class='wm_word_block_root wm_word_block_root_h"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	else{
		output += "<div class='wm_word_block_root wm_word_block_root_v"+isNewClass+"'>";
		output += sameRecorderMenu
	}
	
	var arrMean=wm_word_array[wordIndex].mean.split("$");
	var strMeanMenu="";
	strMeanMenu=wm_render_word_mean_menu2(wordIndex,arrMean);	

	var sameRecorderCount=wm_get_same_word_recorder_count(wordIndex);
	var sameRecorderMenu=wm_render_same_word_recoder_menu_for_read(wordIndex);
	output += "<div>"+"<a onclick='wm_look_up(\""+wm_word_array[wordIndex].real+"\")'>"+ wm_word_array[wordIndex].pali+ "<span class='type'>" + getLocalGrammaStr(wm_word_array[wordIndex].type)+"</span>"+"</div>";	
	output += "<div>"+ strMeanMenu+"</div>";
	output += "<div>" + getLocalGrammaStr(wm_word_array[wordIndex].gramma)+"</div>";	
	output += "<div>"+ wm_word_array[wordIndex].wparent+"</div>";	
	output += "<div>"+ wm_word_array[wordIndex].partmean+"</div>";	
	output += "</div>";	
	return(output);
}

//渲染meaning选择列表
function wm_render_word_mean_menu(wordIndex,meanList){
	//查找父节点中的对应的意思在本节点意思列表中的位置
	var indexOfMean = wm_get_part_mean_index_in_list(wordIndex);
	var defaultMean=meanList[0];
	if(indexOfMean>=0){
		defaultMean=meanList[indexOfMean];
	}
	var output="";
	output +="<div class=\"case_dropdown\" style='display:inline-block;'>";
	output += "<p class='case_dropbtn' >";
	output += defaultMean+"("+meanList.length+") ";
	output +="</p>";
	output +="<div class=\"case_dropdown-content\">";

	for(var i in meanList){
		output +="<a onclick='wm_part_mean_change(\""+wordIndex+"\",\""+meanList[i]+"\")'>"+meanList[i]+"</a>";
	}

	output +="</div></div>";
	return(output);
}

//渲染meaning选择列表
function wm_render_word_mean_menu2(wordIndex,meanList){
	var output="";
	var liclass="";
	output +="<ul class=\"radio-button\" style='display:inline-block;'>";
	//查找父节点中的对应的意思在本节点意思列表中的位置
	var indexOfMean = wm_get_part_mean_index_in_list(wordIndex);
	for(var i in meanList){
		if(indexOfMean==i){
			liclass="li_act";
		}
		else{
			liclass="";
		}
		output +="<li class='"+liclass+"'><a onclick='wm_part_mean_change(\""+wordIndex+"\",\""+meanList[i]+"\")'>"+meanList[i]+"</a></li>";
	}
	output +="</ul>";
	return(output);
}

//渲染相同拼写的成品零件选择列表
function wm_render_same_word_recoder_menu_for_read(wordIndex){
	//拿到零件數據列表
	var partList=wm_get_same_word_recorder(wordIndex);	
	//渲染數量角標
	var output="";
	output +="<div class=\"info_num_case_dropdown\" >";
	if(partList.length!=0){
	output +="<span class='info_num'>"//order-radius: 99px;
	output += partList.length;
	output += "</span>"
	}
	output +="<div class=\"case_dropdown-content\">";

		var L_width_dictname=0
		var L_width_typegramma=0
		var L_width_mean=0
	//首次抓取數據
	for(var string_max_length_i in partList){
		var dictname=g_DictWordList[partList[string_max_length_i]].dictname;
		var thisType=getLocalGrammaStr(g_DictWordList[partList[string_max_length_i]].Type);
		var thisGramma=getLocalGrammaStr(g_DictWordList[partList[string_max_length_i]].Gramma);
		var thisMean=g_DictWordList[partList[string_max_length_i]].Mean;
	//計算最適寬度
		var L_width=getLocalGrammaStr(thisMean).replace(/[\u0391-\uFFE5]/g,"aa").length;
		if(L_width_mean<L_width/1.7 && L_width_mean<12){
			L_width_mean=L_width/1.7;
		}
		else if(L_width_mean>=12){
			L_width_mean=12;
			break;
		}
	}
	for(var wm_for_read_i in partList){
		output +="<a class='wm_selection_menu_a' onclick='wm_recorder_change(\""+wordIndex+"\","+partList[wm_for_read_i]+")'>";
	//拿到詞典信息
		var dictname=g_DictWordList[partList[wm_for_read_i]].dictname;
	//渲染詞典信息
		output +="<div class='wm_dictname_div'>";
		output +="<span class='wm_wordtype' style='width:auto; display:inline-flex'>";
		output +=getLocalDictname(dictname);
		output +="</span>";
		output +="</div>";
	//拿到語法訊息
		var thisType=getLocalGrammaStr(g_DictWordList[partList[wm_for_read_i]].Type);
		var thisGramma=getLocalGrammaStr(g_DictWordList[partList[wm_for_read_i]].Gramma);
	//渲染語法信息
		output +="<div class='wm_wordtype_div'>";
		output +="<span class='wm_wordtype' style='width:auto; display:inline-flex'>";
		output +=getLocalGrammaStr(thisType);
		//output +=getLocalGrammaStr(thisGramma);
		output +="</span>";
		output +="</div>";
	//拿到含義信息
		var thisMean=g_DictWordList[partList[wm_for_read_i]].Mean;
	//整理含義格式
		thisMean=thisMean.replace(/\$/g,"; ");
		thisMean=thisMean.replace(/ \;/g,";");
		thisMean=thisMean.replace(/\;\;/g,";");
		//掐頭
		if(thisMean.charAt(0)==";"){
			thisMean=thisMean.slice(1,thisMean.length);
		}
		//去尾
		if(thisMean.charAt(thisMean.length)==";"){
			thisMean=thisMean.slice(0,-1);
		}
	//渲染含義信息
		output +="<span class='wm_wordmean' style='width:"+L_width_mean+"em;'>";
		output +=thisMean;
		output +="</span>";
		output +="</a>";
	}

	output +="</div></div>";
	return(output);
}



function wm_render_word_type_gramma_edit(wordIndex){
	var output="";
	var typeList=wm_get_word_type_list(wordIndex);
	output +="<div class=\"case_dropdown\" style='display:inline-block;'>";
	//output += "<p class='case_dropbtn' >";
	//output += "<input type='input' onchange='wm_field_change(\""+wordIndex+"\",\"type\",this.value)' size='7' value='"+wm_word_array[wordIndex].type+"' />";
	//output += "</p>";

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"type\",\"\")'>"+gLocal.gui.empty1+"</button>"
	for(var i in typeList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"type\",\""+typeList[i].type+"\")'>["+getLocalDictname(typeList[i].dict)+"]"+typeList[i].type+"</a>";
	}

	output +="</div></div>";
	return(output);
}

function wm_render_word_type_edit(wordIndex){
	var output="";
	var typeList=wm_get_word_type_list(wordIndex);
	output +="<span class='wm_inline_control'>";
	//渲染數量角標
	output +="<div id='info_num_type_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_gramma_dropdown\" ";
	output +="style='display:block'>";
	if(typeList.length && typeList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += typeList.length;
	output += "</span>"
	}

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"type\",\"\")'>"+gLocal.gui.empty1+"</button>"
	for(var type_edit_i in typeList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"type\",\""+typeList[type_edit_i].type+"\")'>["+getLocalDictname(typeList[type_edit_i].dict)+"]"+getLocalGrammaStr(typeList[type_edit_i].type)+"</a>";
	}

	output += "</div></div>";
	output +="<div class=\"wm_edit_type\">";
	//output += "<p class='case_dropbtn' >";
	//output += "<input type='input' onchange='wm_field_change(\""+wordIndex+"\",\"type\",this.value)' size='7' value='"+wm_word_array[wordIndex].type+"' />";
	//output +="</p>";

	if(wm_word_array[wordIndex].type!="" && wm_word_array[wordIndex].type!="?"){
		output += "<span id='type_"+wordIndex+"_span' class='wm_input_span cell' ";
		//output += "onclick=\"switch_input_span("+wordIndex+",\'type\')\"";
		output += ">";
		output += getLocalGrammaStr(wm_word_array[wordIndex].type);
		output += "</span>"
		output += "</div>";
		//output +="</p>";
	}
	else{
		output += "<span id='type_"+wordIndex+"_span' class='wm_input_span cell' ";
		output += "style='display:block'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'type\')\">";
		output += "<i>?</i>";
		output += "</span>"
		output += "</div>";
	}

	return(output);
}


function wm_render_word_gramma_edit(wordIndex){
	var output="";
	var itemList=wm_get_word_gramma_list(wordIndex);
	output +="<div class=\"wm_edit_gramma\">";
	//output += "<p class='case_dropbtn' >";
	//output += "<input type='input' onchange='wm_field_change(\""+wordIndex+"\",\"gramma\",this.value)' size='7' value='"+wm_word_array[wordIndex].gramma+"' />";
	//output +="</p>";
	if(wm_word_array[wordIndex].gramma!="" && wm_word_array[wordIndex].gramma!="?"){
		output += "<span id='gramma_"+wordIndex+"_span' class='wm_input_span' ";
		//output += "onclick=\"switch_input_span("+wordIndex+",\'gramma\')\"";
		output += ">";
		output += getLocalGrammaStr(wm_word_array[wordIndex].gramma);
		output += "</span>"
		output += "</div>";
		//output +="</p>";
	}
	else{
		output += "<span id='gramma_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:block'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'gramma\')\">";
		output += "<i>?</i>";
		output += "</span>"
		output += "</div>";
	}
	//渲染數量角標
	output +="<div id='info_num_gramma_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_gramma_dropdown\" ";
	output +="style='display:block'>";
	if(itemList.length && itemList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += itemList.length;
	output += "</span>"
	}

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"gramma\",\"\")'>"+gLocal.gui.empty1+"</button>"
	for(var gramma_edit_i in itemList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"gramma\",\""+itemList[gramma_edit_i].gramma+"\")'>["+getLocalDictname(itemList[gramma_edit_i].dict)+"]"+getLocalGrammaStr(itemList[gramma_edit_i].gramma)+"</a>";
	}

	output +="</div></div></span>";
	return(output);
}
function switch_input_span(id,position){
	document.getElementById("info_num_"+position+"_dropdown_"+id+"_div").style.display="none";
	document.getElementById(position+"_"+id+"_span").style.display="none";
	document.getElementById(position+"_"+id+"_input").style.display="block";
	document.getElementById(position+"_"+id+"_input").focus();
}
function switch_input_span2(id,position){
	document.getElementById("info_num_"+position+"_dropdown_"+id+"_div").style.display="block";
	document.getElementById(position+"_"+id+"_span").style.display="block";
	document.getElementById(position+"_"+id+"_input").style.display="none";
}
function wm_render_word_parent_edit(wordIndex){
	var output="";
	var parentList=wm_get_word_parent_list(wordIndex);
	output +="<span class='wm_inline_control'><div class=\"wm_edit_parent\">";

	//output += "<p class='case_dropbtn' >";
	if(wm_word_array[wordIndex].wparent!="" && wm_word_array[wordIndex].wparent!=" "){
		output += "<span id='parent_"+wordIndex+"_span' class='wm_input_span' ";
		output += "onclick=\"switch_input_span("+wordIndex+",\'parent\')\">";
		output += wm_word_array[wordIndex].wparent;
		output += "</span>"
		output += "<input id='parent_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"parent\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"parent\",this.value)' ";
		output += "value='"+wm_word_array[wordIndex].wparent+"' /></div>";
		//output +="</p>";
	}
	else{
		output += "<span id='parent_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:block;color: rgb(134, 134, 134)'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'parent\')\">";
		output += "<i>"+gLocal.gui.parent_input+"</i>";
		output += "</span>"
		output += "<input id='parent_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"parent\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"parent\",this.value)' ";
		//output += "onblur=\"switch_input_span2("+wordIndex+",\'mean\')\"";
		output += "value='' /></div>";
	}
	//渲染數量角標
	output +="<sup><div id='info_num_parent_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_detail_dropdown\" ";
	output +="style='display:block'>";
	if(parentList.length && parentList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += parentList.length;
	output += "</span>"
	}

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%; display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"parent\",\"\")'>"+gLocal.gui.empty1+"</button>";
	for(var i in parentList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"parent\",\""+parentList[i].wparent+"\")'>["+getLocalDictname(parentList[i].dict)+"]"+parentList[i].wparent+"</a>";
	}

	output +="</div></div></sup></span>";
	return(output);
}


function wm_render_word_mean_edit(wordIndex){
	var output="";
	var partList=wm_get_word_mean_list(wordIndex)


	output +="<span class='wm_inline_control'><div class=\"wm_edit_mean\">";
	//output += "<p class='case_dropbtn' >";
	if(wm_word_array[wordIndex].mean!=""){
		output += "<span id='mean_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:block'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'mean\')\">";
		output += wm_word_array[wordIndex].mean.replace(/\$/g,";");
		output += "</span>";
		output += "<input id='mean_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"mean\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"mean\",this.value)' ";
		//output += "onkeyup='wm_input_keyup(event,\""+wordIndex+"\",\"mean\",this.value)' ";
		//output += "onblur=\"switch_input_span2("+wordIndex+",\'mean\')\"";
		output += "value='"+wm_word_array[wordIndex].mean+"' /></div>";
	}
	else{
		output += "<span id='mean_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:block;color: rgb(134, 134, 134)'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'mean\')\">";
		output += "<i>"+gLocal.gui.meaning_input+"</i>";
		output += "</span>";
		output += "<input id='mean_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"mean\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"mean\",this.value)' ";
		//output += "onkeyup='wm_input_keyup(event,\""+wordIndex+"\",\"mean\",this.value)' ";
		output += "value='' /></div>";

	}


	//渲染數量角標
	output +="<sup><div id='info_num_mean_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_detail_dropdown\" ";
	output +="style='display:block'>";
	if(partList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += partList.length;
	output += "</span>"
	}

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"mean\",\"\")'>"+gLocal.gui.empty1+"</button>"
	for(var edti_mean_i in partList){
		var meanGroup = partList[edti_mean_i].mean.split('$');
		var htmlMean="";
		for(var iMean in meanGroup){
			htmlMean += "<span class='wm_one_mean' onclick='wm_field_change(\""+wordIndex+"\",\"mean_add\",\""+meanGroup[iMean]+"\")'>"
			htmlMean += meanGroup[iMean]+"</span>";
		}
		output +="<a ><span class='wm_dictname' onclick='wm_field_change(\""+wordIndex+"\",\"mean\",\""+partList[edti_mean_i].mean+"\")'>";
		output +=getLocalDictname(partList[edti_mean_i].dict);
		output +="</span>"+htmlMean+"</a>";
	}

	output +="</div></div></sup></span>";


	//output += "</p>";

	return(output);
}

function wm_render_word_part_edit(wordIndex){
	var output="";
	var partList=wm_get_word_part_list(wordIndex)
	output +="<span class='wm_inline_control'><div class=\"wm_edit_part\">";
	//output += "<p class='case_dropbtn' >";
	if(wm_word_array[wordIndex].part!=""){
		output += "<span id='part_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:inline-block'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'part\')\">";
		output += wm_word_array[wordIndex].part;
		output += "</span>";
		output += "<input id='part_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"part\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"part\",this.value)' ";
		//output += "onblur=\"switch_input_span2("+wordIndex+",\'part\')\"";
		output += "value='"+wm_word_array[wordIndex].part+"' /></div>";
	}
	else{
		output += "<span id='part_"+wordIndex+"_span' class='wm_input_span' ";
		output += "style='display:block;color: rgb(134, 134, 134)'"
		output += "onclick=\"switch_input_span("+wordIndex+",\'part\')\">";
		output += "<i>"+gLocal.gui.factors_input+"</i>";
		output += "</span>";
		output += "<input id='part_"+wordIndex+"_input' type='input' style='display:none' ";
		output += "onblur='wm_field_change(\""+wordIndex+"\",\"part\",this.value)' ";
		output += "onchange='wm_field_change(\""+wordIndex+"\",\"part\",this.value)' ";
		//output += "onclick=\"switch_input_span2("+wordIndex+",\'part\')\"";
		output += "value='' /></div>";

	}

	//渲染數量角標
	output +="<sup><div id='info_num_part_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_detail_dropdown\" ";
	output +="style='display:block'>";
	if(partList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += partList.length;
	output += "</span>"
	}
	output +="<div class=\"case_dropdown-content\">";
	output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"part\",\""+wm_word_array[wordIndex].real+"\")'>";
	output +="["+wm_word_array[wordIndex].real+"]";
	output +="</a>";
	for(var edit_part_x in partList){
		output +="<a>";
		output +="<span class='wm_dictname' ";
		output +="onclick='wm_field_change(\""+wordIndex+"\",\"part\",\""+partList[edit_part_x].part+"\")'>";
		output +=getLocalDictname(partList[edit_part_x].dict);
		output +="</span>"+partList[edit_part_x].part+"</a>";


	}


	//output +="</p>";

	output +="</div></div></sup></span>";
	return(output);
}

function wm_get_word_type_list(wordIndex){
	var wPali=wm_word_array[wordIndex].pali;
	var wReal=wm_word_array[wordIndex].real;
	var wType=wm_word_array[wordIndex].type;
	var output = Array();
	
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==wReal){
			if(g_DictWordList[x].Type!=""){
				var newItem= Object();
				newItem.dict=g_DictWordList[x].dictname;
				newItem.type=g_DictWordList[x].Type;
				output.push(newItem);
			}
		}
	}	
	return(output);
}

function wm_get_word_gramma_list(wordIndex){
	var wPali=wm_word_array[wordIndex].pali;
	var wReal=wm_word_array[wordIndex].real;
	var wType=wm_word_array[wordIndex].type;
	var output = Array();
	
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==wReal && wm_type_match(g_DictWordList[x].Type,wType)){
			if(g_DictWordList[x].Gramma!=""){
				var newPart= Object();
				newPart.dict=g_DictWordList[x].dictname;
				newPart.gramma=g_DictWordList[x].Gramma;
				output.push(newPart);
			}
		}
	}	
	return(output);
}


function wm_get_word_part_list(wordIndex){
	var wPali=wm_word_array[wordIndex].pali;
	var wReal=wm_word_array[wordIndex].real;
	var wType=wm_word_array[wordIndex].type;
	var output = Array();

	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==wReal && wm_type_match(g_DictWordList[x].Type,wType)){
			if(g_DictWordList[x].Factors!=""){
				var newPart= Object();
				newPart.dict=g_DictWordList[x].dictname;
				newPart.part=g_DictWordList[x].Factors;
				output.push(newPart);
			}
		}
	}	
	return(output);
}

function wm_get_word_mean_list(wordIndex){
	var wPali=wm_word_array[wordIndex].pali;
	var wReal=wm_word_array[wordIndex].real;
	var wType=wm_word_array[wordIndex].type;
	var output = Array();
	
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==wReal && ( wm_type_match(g_DictWordList[x].Type,wType) || g_DictWordList[x].Type=="")){
			if(dict_language_enable.indexOf(g_DictWordList[x].Language)>=0){
				if(g_DictWordList[x].Mean!=""){
					var newPart= Object();
					newPart.dict=g_DictWordList[x].dictname;
					newPart.mean=g_DictWordList[x].Mean;
					output.push(newPart);
				}
			}
		}
	}	
	return(output);
}
function wm_get_word_parent_list(wordIndex){
	var wPali=wm_word_array[wordIndex].pali;
	var wReal=wm_word_array[wordIndex].real;
	var wType=wm_word_array[wordIndex].type;
	var output = Array();
	
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==wReal && wm_type_match(g_DictWordList[x].Type,wType)){
			if(g_DictWordList[x].Parent!=""){
				var newPart= Object();
				newPart.dict=g_DictWordList[x].dictname;
				newPart.wparent=g_DictWordList[x].Parent;
				output.push(newPart);
			}
		}
	}	
	return(output);
}

function wm_get_word_index_with_part_from_dict(word){
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word){
			if(g_DictWordList[x].Factors!=""){
				
				return(x);
			}
		}
	}
	return(-1);
}

function wm_get_word_first_mean(word){
	for(var x in g_DictWordList){
		if(g_DictWordList[x].Pali==word){
			//语言过滤
			if(dict_language_enable.indexOf(g_DictWordList[x].Language)>=0){
				if(g_DictWordList[x].Mean!=""){
					return(g_DictWordList[x].Mean);
				}
			}
		}
	}
	return("");
}
function wm_render_line(){
	if(wm_render_mode){//纵向
		wm_render_line_h();
	}
	else{//横向划线
		wm_render_line_v();
	}
}

//横向划线
function wm_render_line_v(){
	for(iBox=0;iBox<wm_word_box_count;iBox++){
		var obj = document.getElementById("wm_line_"+iBox);
		var objChildren = document.getElementById("wm_children_"+iBox);
		if(obj){
			var objWH=getElementWH(obj);
			
			var width=50;
			var height=objWH.height;
			if(height>0)
			var output="";
			output += "<div class=\"wordmap_line_shell\" style='width:"+width+"px; height:"+height+"px'>";
			output += "<svg viewBox=\"0 0 "+width+" "+height+"\" class=\"wordmap_line\" xmlns=\"http://www.w3.org/2000/svg\">";
			var lineY=0;
			for(xNode in objChildren.childNodes){
				var newChildHeigh=getElementWH(objChildren.childNodes[xNode]).height;
				if(newChildHeigh>0){
				lineY+=newChildHeigh/2;
				output += "<line x1=\"3\" y1=\""+height/2+"\" x2=\""+(width-3)+"\" y2=\""+lineY+"\" stroke-width=\"2px\" stroke-linecap=\"round\"/>";
				lineY+=newChildHeigh/2;
				}
			}
			output += "</svg>";
			output += "</div> ";		
			obj.innerHTML=output;
		}
	}
}

function wm_render_line_h(){
	for(iBox=0;iBox<wm_word_box_count;iBox++){
		var obj = document.getElementById("wm_line_"+iBox);
		var objChildren = document.getElementById("wm_children_"+iBox);
		if(obj){
			var objWH=getElementWH(obj);
			var objChildrenWH=getElementWH(objChildren);
			
			var width=objWH.width;
			var height=objWH.height;
			if(width>0)
			var output="";
			output += "<div class=\"wordmap_line_shell\" style='width:"+width+"px; height:"+height+"px'>";
			output += "<svg viewBox=\"0 0 "+width+" "+height+"\" class=\"wordmap_line\" xmlns=\"http://www.w3.org/2000/svg\">";
			var lineX=0;
			for(xNode in objChildren.childNodes){
				var newChildWidth=getElementWH(objChildren.childNodes[xNode]).width;
				if(newChildWidth>0){
				lineX+=newChildWidth/2;
				output += "<line x1=\""+width/2+"\" y1=\"3\" x2=\""+lineX+"\" y2=\""+(height-3)+"\" stroke-width=\"2px\" stroke-linecap=\"round\"/>";
				lineX+=newChildWidth/2;
				}
			}
			output += "</svg>";
			output += "</div> ";		
			obj.innerHTML=output;
		}
	}
}

//获取同样拼写单词的已经存在的成品零件记录数量
function wm_get_same_word_recorder_count(wordIndex){
	return(wm_get_same_word_recorder(wordIndex).length);
}
//获取同样拼写单词的已经存在的成品零件记录
function wm_get_same_word_recorder(wordIndex){
	var output=new Array();
	var typeString="";
	for(var wm_get_same_word_recorder_x in g_DictWordList){
		if(g_DictWordList[wm_get_same_word_recorder_x].Pali==wm_word_array[wordIndex].real){
			if(g_DictWordList[wm_get_same_word_recorder_x].Guid && g_DictWordList[wm_get_same_word_recorder_x].Guid.length>0){//修改循環計數值的名字，并進行非空判斷
				var parentIndex=wm_word_array[wordIndex].parentNode;
				if(parentIndex){
					if(g_DictWordList[wm_get_same_word_recorder_x].Guid!=wm_word_array[parentIndex].id){
						output.push(wm_get_same_word_recorder_x);	
					}
				}
						
			}
		}
	}
	return(output);
}

function wm_field_change(wordIndex,field,value){
	switch(field){
		case "type":
			wm_word_array[wordIndex].type=value;
			wm_caseChanged(wordIndex,"type",value);
			wm_updata_word_root_edit(wordIndex);
			break;
		case "gramma":
			wm_word_array[wordIndex].gramma=value;
			var wm_field_change_gramma=value.split("$");
			if(wm_field_change_gramma.length>0 && wm_field_change_gramma.length<4){
				for(wm_field_change_gramma_i in wm_field_change_gramma){
					wm_caseChanged(wordIndex,"case"+(wm_field_change_gramma_i+1),value);
				}
			}
			else{
				wm_caseChanged(wordIndex,"case1",value);
			}
			wm_updata_word_root_edit(wordIndex);
			break;
		case "mean":
			wm_word_array[wordIndex].mean=value;
			wm_updata_word_root_edit(wordIndex);
			switch_input_span2(wordIndex,"mean");
			break;
		case "mean_add":
			wm_word_array[wordIndex].mean+="$"+value;
			wm_updata_word_root_edit(wordIndex);
			break;
		case "parent":
			wm_word_array[wordIndex].wparent=value;
			wm_updata_word_root_edit(wordIndex);
			break;
		case "part":
			if(wm_word_array[wordIndex].part!=value){
				wm_word_array[wordIndex].part=value;
				wm_word_array[wordIndex].partid="";
				wm_updata_word(rootId,null);
				wm_updata_word_map();
				wm_render_line();
				switch_input_span2(wordIndex,"part");
				//wm_refresh_inline_dict(wordIndex);
			}
			break;
	}

}

function wm_dict_one_done(dictIndex){
	wm_notify("pass:"+(g_dictFindParentLevel+1)+" "+g_dictList[dictIndex].name + "done");
}
function wm_dict_search_one_pass_done(pass){
	wm_notify("pass:"+(pass+1)+" is done");	
	wm_updata_word(rootId,null);
	wm_updata_word_map();
	wm_render_line();
}

function wm_notify(message){
	var objNotify=document.getElementById('wm_doc_notify');
	if(objNotify){
		objNotify.style.display="inline";
		objNotify.innerHTML=message;
		setTimeout("document.getElementById('wm_doc_notify').style.display='none'",5000);
	}
	
}



function wm_dict_all_done(){
	wm_updata_word(rootId,null);
	wm_updata_word_map();
	wm_render_line();
}
function wm_refresh_inline_dict(wordIndex){
	currMatchingDictNum=0;
	g_dictFindParentLevel=0;
	g_dictFindAllDone=false;
	g_dict_search_one_dict_done=wm_dict_one_done;
	g_dict_search_one_pass_done=wm_dict_search_one_pass_done;
	g_dict_search_all_done=wm_dict_all_done;
	dict_mark_word_list_done();
	//dict_push_word_to_download_list(wm_word_array[wordIndex].wparent,1);
	var arrPart=wm_word_array[wordIndex].part.split("+");
	for(var ipart in arrPart){
		dict_push_word_to_download_list(arrPart[ipart],1);
	}	
	var arrBuffer=dict_get_search_list();
	if(arrBuffer.length>0){
		g_CurrDictBuffer=JSON.stringify(arrBuffer);
		dict_mark_word_list_done();
		editor_dict_match();	
	}
	else{
		wm_notify("no new part");
	}
	
}

function wm_recorder_change(wordIndex,newIndex){
	var oldGuid=wm_word_array[wordIndex].id;
	if(newIndex>=0){
		var newGuid=g_DictWordList[newIndex].Guid;
		var parentNodeIndex=wm_word_array[wordIndex].parentNode;
		if(parentNodeIndex){
			wm_word_array[parentNodeIndex].partid = wm_word_array[parentNodeIndex].partid.replace(oldGuid,newGuid);
				wm_updata_word(rootId,null);
				wm_updata_word_map();
				wm_render_line();			
		}
	}
	else{//new
		newPartId=wm_create_word(wm_word_array[wordIndex].pali,wm_word_array[wordIndex].deep,wm_word_array[wordIndex].parentNode);
		var parentNodeIndex=wm_word_array[wordIndex].parentNode;
		if(parentNodeIndex){
			wm_word_array[parentNodeIndex].partid = wm_word_array[parentNodeIndex].partid.replace(oldGuid,newPartId);
				wm_updata_word(rootId,null);
				wm_updata_word_map();
				wm_render_line();			
		}
		//wm_updata_word(newPartId,wordId);
	}

}

function wm_part_mean_change(wordIndex,newMean){
	var wordId=wm_word_array[wordIndex].id;
	var parentNodeIndex=wm_word_array[wordIndex].parentNode;

		if(parentNodeIndex){
			var arrPartId=wm_word_array[parentNodeIndex].partid.split('+');
			var indexOfMean=-1;
			for(var i in arrPartId){
				if(arrPartId[i]==wordId){
					indexOfMean=i;
					break;
				}
			}
			if(indexOfMean>=0){
				var arrMean = wm_word_array[parentNodeIndex].partmean.split('+');
				arrMean.length=arrPartId.length;
				arrMean[indexOfMean]=newMean;
				wm_word_array[parentNodeIndex].partmean=arrMean.join('+');
				wm_updata_word(rootId,null);
				wm_updata_word_map();
				wm_render_line();	
			}
		}

}

//查找父节点中的对应的意思在本节点意思列表中的位置
function wm_get_part_mean_index_in_list(wordIndex){
	var wordId=wm_word_array[wordIndex].id;
	
	var parentNodeIndex=wm_word_array[wordIndex].parentNode;
	{
		if(parentNodeIndex){
			var arrPartId=wm_word_array[parentNodeIndex].partid.split('+');
			var indexOfMean=-1;
			for(var i in arrPartId){
				if(arrPartId[i]==wordId){
					indexOfMean=i;
					break;
				}
			}
			if(indexOfMean>=0){
				var arrMean = wm_word_array[parentNodeIndex].partmean.split('+');
				arrMean.length=arrPartId.length;
				var parentPartMean = arrMean[indexOfMean];
				var wordMeanArray=wm_word_array[wordIndex].mean.split("$");
				for(var j in wordMeanArray){
					if(wordMeanArray[j]==parentPartMean){
						return(j);
					}
				}
			}
		}
	}
	
	return(-1);
}

function wm_id_exists(findid){
	for(var i in wm_word_array){
		if(wm_word_array[i].id==findid){
			return(true);
		}
	}
	return(false);
}

function wm_save_node_all(){
	wm_array_word_for_save=new Array();
	wm_save_node(rootId);
	var_dump("save tree ok.");
	wm_WbwUpdata(wm_array_word_for_save)
	pop_win_close();
}

function wm_save_node(nodeId,withChild=true){
	var wid=-1;
	for(var i in wm_word_array){
		if(wm_word_array[i].id==nodeId){
			wid=i;
		}
	}
	if(wid==-1){
		return;
	}
	wm_array_word_for_save.push(wm_word_array[wid]);	
	
	var objDictItem=new Object();/*一个字典元素*/
	objDictItem.Id = 0;
	objDictItem.Guid = wm_word_array[wid].id;
	objDictItem.Pali = wm_word_array[wid].pali;
	objDictItem.Mean = wm_word_array[wid].mean;
	objDictItem.Type = wm_word_array[wid].type;
	objDictItem.Gramma = wm_word_array[wid].gramma;
	objDictItem.Parent = wm_word_array[wid].wparent;
	objDictItem.ParentId = wm_word_array[wid].parentid;
	objDictItem.Factors = wm_word_array[wid].part;
	objDictItem.PartId = wm_word_array[wid].partid;
	objDictItem.FactorMean = wm_word_array[wid].partmean;
	objDictItem.Note = "";
	objDictItem.Confer = "";
	objDictItem.Status = "1024";
	objDictItem.Enable = "true";
	objDictItem.Language = "sc";
	objDictItem.dictname = "user";
	objDictItem.dictType="user";
	objDictItem.fileName="user.db3";
	objDictItem.dictID="0";
	objDictItem.ParentLevel = g_dictFindParentLevel;
	g_DictWordList.unshift(objDictItem);
	editor_insertNewWordToInlineDict(objDictItem);
	
	if(withChild){
		if(wm_word_array[wid].partid!=""){
			var arrChild=wm_word_array[wid].partid.split("+");
			for(var i in arrChild){
				wm_save_node(arrChild[i]);
			}
		}
	}
}

// word by word dict updata
var wm_wbwUpdataXmlHttp=null;
function wm_WbwUpdata(arrWord){

	var xmlText="";
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		wm_wbwUpdataXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		wm_wbwUpdataXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (wm_wbwUpdataXmlHttp!=null)
	{
		var queryString="<wordlist>";
		var x = gXmlBookDataBody.getElementsByTagName("word");
		var iCount=0;
		var d_language=language_translation;
		for(var index in arrWord){
			var d_pali=arrWord[index].real;
			var d_guid=arrWord[index].id;
			var d_type = arrWord[index].type;
			var d_gramma = arrWord[index].gramma;
			var d_mean=arrWord[index].mean;
			var d_note=arrWord[index].note;
			var d_parent=com_getPaliReal(arrWord[index].wparent);
			var d_parent_id=arrWord[index].parentid;
			var d_factors=arrWord[index].part;
			var d_fm=arrWord[index].partmean;
			var d_part_id=arrWord[index].partid;
			var d_confer="";
			var d_status="1024";
			var d_enable="TRUE";
			
			if(d_pali.length>0 && !(d_mean=="" && d_factors=="" && d_fm=="" && d_type=="")){
				
				queryString+="<word>";
				queryString+="<pali>"+d_pali+"</pali>";
				queryString+="<guid>"+d_guid+"</guid>";
				queryString+="<type>"+d_type+"</type>";
				queryString+="<gramma>"+d_gramma+"</gramma>";
				queryString+="<parent>"+d_parent+"</parent>";
				queryString+="<parent_id>"+d_parent_id+"</parent_id>";
				queryString+="<mean>"+d_mean+"</mean>";
				queryString+="<note>"+d_note+"</note>";
				queryString+="<factors>"+d_factors+"</factors>";
				queryString+="<fm>"+d_fm+"</fm>";
				queryString+="<part_id>"+d_part_id+"</part_id>";
				queryString+="<confer>"+d_confer+"</confer>";
				queryString+="<status>"+d_status+"</status>";
				queryString+="<enable>"+d_enable+"</enable>";
				queryString+="<language>"+d_language+"</language>";
				queryString+="</word>";
				iCount++;
			}
		}
		queryString+="</wordlist>";
		if(iCount>0){
		wm_wbwUpdataXmlHttp.onreadystatechange=wm_wbwDictUpdata_serverResponse;
		debugOutput("updata user dict start.",0);
		wm_wbwUpdataXmlHttp.open("POST", "dict_updata_wbw.php", true);
		wm_wbwUpdataXmlHttp.send(queryString);
		}
		else{
			debugOutput("no user dicttionary data need updata.",0);
		}
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function wm_wbwDictUpdata_serverResponse(){
	if (wm_wbwUpdataXmlHttp.readyState==4)// 4 = "loaded"
	{
		debugOutput("server response.",0);
		if (wm_wbwUpdataXmlHttp.status==200)
		{// 200 = "OK"
			var serverText = wm_wbwUpdataXmlHttp.responseText;
			var_dump(serverText);
			debugOutput(serverText,0);
		}
		else
		{
			debugOutput(xmlhttp.statusText,0);
		}
	}
}

function wm_word_base_mouse_over(currWordIndex){
	if(currWordIndex==wm_word_base_curr_focuse){
		return;
	}
	wm_word_base_curr_focuse=currWordIndex;
	switch(wm_show_mode){
		case 0:
		wm_updata_word_root_read(currWordIndex);
		break;
		case 1:
		wm_updata_word_root_read2(currWordIndex);
		break;
		case 2://edit
		wm_updata_word_root_edit(currWordIndex);
		break;
	}
}

function wm_look_up(word){
	editor_show_right_tool_bar(true);
	document.getElementById("dict_ref_search_input").value=word;
	dict_search(word);
}
function wm_input_keyup(e,wordIndex,mean,value){
	var keynum
	//var keychar
	//var numcheck

	if(window.event) // IE
	  {
	  keynum = e.keyCode
	  }
	else if(e.which) // Netscape/Firefox/Opera
	  {
	  keynum = e.which
	  }
	//var keychar = String.fromCharCode(keynum)
	if(keynum==13){
		wm_field_change(wordIndex,mean,value);
	}
}
function wm_render_word_typegramma_edit(wm_type,case1,case2,case3,wordIndex,padding_width){
	var output="";
	var strTypeSelect="";
	var wm_case_Select_Array=new Array();
	var typeList=wm_get_word_type_list(wordIndex);
	var itemList=wm_get_word_gramma_list(wordIndex);

	output +="<span class='wm_inline_control'>";
	//渲染數量角標
	output +="<div id='info_num_type_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_gramma_dropdown\" ";
	output +="style='display:block'>";
	if(typeList.length && typeList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += typeList.length;
	output += "</span>";
	}
	//渲染type詞典數據下拉菜單
	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' ";
	output +="onclick='wm_field_change(\""+wordIndex+"\",\"type\",\"\")'>";
	output +=gLocal.gui.empty1;
	output +="</button>";
	for(var type_edit_i in typeList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"type\",\""+typeList[type_edit_i].type+"\")'>["+getLocalDictname(typeList[type_edit_i].dict)+"]"+getLocalGrammaStr(typeList[type_edit_i].type)+"</a>";
	}

	output += "</div></div>";
	output +="<div class=\"wm_edit_type\" style=\"display:inline-flex;\">";
	//output += "<p class='case_dropbtn' >";
	//output += "<input type='input' onchange='wm_field_change(\""+wordIndex+"\",\"type\",this.value)' size='7' value='"+wm_word_array[wordIndex].type+"' />";
	//output +="</p>";

	if(wm_type!="" && wm_type!="?"){
		wm_case[0]=wm_type;
		wm_case_Select_Array[0]=wm_type;
		strTypeSelect = "<div id=\"id_wm_case_dropdown_0_"+wordIndex+"\" ";
		strTypeSelect+= "class=\"case_dropdown gramma_selector\" ";
		strTypeSelect+= "style='min-width: unset;padding-right: ";
		strTypeSelect+= padding_width+"em;'>";
		strTypeSelect+= "<p class=\"case_dropbtn cell\" style='line-height: 1.2em;'>";
		strTypeSelect+= getLocalGrammaStr(wm_case_Select_Array[0])+"</p>";//顯示
	}
	else{
		wm_case[0]="";
		strTypeSelect = "<div id=\"id_wm_case_dropdown_0_"+wordIndex+"\" ";
		strTypeSelect+= "class=\"case_dropdown gramma_selector\" ";
		strTypeSelect+= "style='min-width: unset;padding-right: ";
		strTypeSelect+= padding_width+"em;'>";
		strTypeSelect+= "<p class=\"case_dropbtn  cell\" style='line-height: 1.2em;'>";
		strTypeSelect+= "?</p>";
	}

		strTypeSelect+="<div class=\"case_dropdown-content\">";
		//生成type下拉菜單
		for(var wm_iType in gLocal.type_str){
			strTypeSelect+="<a onclick=\"wm_caseChanged('"+wordIndex+"','type','"+gLocal.type_str[wm_iType].id+"')\">";
			strTypeSelect+=gLocal.type_str[wm_iType].value+"</a>"
		}
		strTypeSelect+="</div>";
		strTypeSelect+="</div>";
		output+="<span>"+strTypeSelect+"</span>";//輸出type下拉菜單
		
			for(var wm_iCase in gramma_str){//歷遍所有type數組
				if(gramma_str[wm_iCase].id==wm_type){//獲取與當前type相同的後續內容

					
					var strTypeSelect="";
					gramma=gramma_str[wm_iCase].a;//獲取該wm_case[0]下第一列內容
					if(gramma.length>0){//歷遍所有a列內容
						items=gramma.split("$");//以$分隔
						if(case1==""){//當抓取道的wm_case[0]為空
							wm_case_Select_Array[1]=items[0];//令wm_case[0]以列表默認第一個進行賦值
							wm_case[1]=wm_case_Select_Array[1];//以匹配的數據做為顯示的值
						}
						else{
							wm_case_Select_Array[1]=case1
							wm_case[1]=case1
						}
						//下拉菜單渲染
						strTypeSelect="<div id=\"id_wm_case_dropdown_1_"+wordIndex+"\" ";
						strTypeSelect+="class=\"case_dropdown gramma_selector\" ";
						strTypeSelect+="style='min-width: unset;padding-right: "+padding_width+"em;'>";
						strTypeSelect+="<p class=\"case_dropbtn\" style='line-height: 1.2em;'>";
						strTypeSelect+=getLocalGrammaStr(wm_case_Select_Array[1])+"</p>";
						strTypeSelect+="<div class=\"case_dropdown-content\">";
						//菜單主體
						for(iItem=0;iItem<items.length;iItem++){
							strTypeSelect+="<a onclick=\"wm_caseChanged('"+wordIndex+"','case1','"+items[iItem]+"')\">";
							strTypeSelect+=getLocalGrammaStr(items[iItem])+"</a>"
						}
						strTypeSelect+="</div>";
						strTypeSelect+="</div>";
					}
					else{
						wm_case[1]="";
					}
				output+="<span>"+strTypeSelect+"</span>";

					strTypeSelect="";
					gramma=gramma_str[wm_iCase].b;
					if(gramma.length>0){
						items=gramma.split("$");
						if(case2==""){
							wm_case_Select_Array[2]=items[0];
							wm_case[2]=wm_case_Select_Array[2];
						}
						else{
							wm_case_Select_Array[2]=case2
							wm_case[2]=case2
						}
						strTypeSelect="<div id=\"id_wm_case_dropdown_2_"+wordIndex+"\" ";
						strTypeSelect+="class=\"case_dropdown gramma_selector\" ";
						strTypeSelect+="style='min-width: unset;padding-right: "
						strTypeSelect+=padding_width+"em;'>";
						strTypeSelect+="<p class=\"case_dropbtn\" style='line-height: 1.2em;'>";
						strTypeSelect+=getLocalGrammaStr(wm_case_Select_Array[2])+"</p>";
						strTypeSelect+="<div class=\"case_dropdown-content\">";
						for(iItem=0;iItem<items.length;iItem++){
							strTypeSelect+="<a onclick=\"wm_caseChanged('"+wordIndex+"','case2','"+items[iItem]+"')\">"+getLocalGrammaStr(items[iItem])+"</a>"
						}
						strTypeSelect+="</div>";
						strTypeSelect+="</div>";
					}
					else{
						wm_case[2]="";
					}
					//eCaseItems[2].innerHTML=strTypeSelect;		
					output+="<span>"+strTypeSelect+"</span>";
					strTypeSelect="";
					gramma=gramma_str[wm_iCase].c;
					if(gramma.length>0){
						items=gramma.split("$");
						if(case3==""){
							wm_case_Select_Array[3]=items[0];
							wm_case[3]=wm_case_Select_Array[3];
						}
						else{
							wm_case_Select_Array[3]=case3
							wm_case[3]=case3
						}
						strTypeSelect="<div id=\"id_wm_case_dropdown_3_"+wordIndex+"\" ";
						strTypeSelect+="class=\"case_dropdown gramma_selector\" ";
						strTypeSelect+="style='min-width: unset;padding-right: "+padding_width+"em;'>";
						strTypeSelect+="<p class=\"case_dropbtn\" style='line-height: 1.2em;'>";
						strTypeSelect+=getLocalGrammaStr(wm_case_Select_Array[3])+"</p>";
						strTypeSelect+="<div class=\"case_dropdown-content\">";
						for(iItem=0;iItem<items.length;iItem++){
							strTypeSelect+="<a onclick=\"wm_caseChanged('"+wordIndex+"','case3','"+items[iItem]+"')\">"+getLocalGrammaStr(items[iItem])+"</a>"
						}
						strTypeSelect+="</div>";
						strTypeSelect+="</div>";
					}
					else{
						wm_case[3]="";
					}
					//eCaseItems[3].innerHTML=strTypeSelect;
					output+="<span>"+strTypeSelect+"</span>";
				}
			}					
		
		
		
		output +="</div>";//<div class=\"wm_edit_gramma\">
	//output += "<p class='case_dropbtn' >";
	//output += "<input type='input' onchange='wm_field_change(\""+wordIndex+"\",\"gramma\",this.value)' size='7' value='"+wm_word_array[wordIndex].gramma+"' />";
	//output +="</p>";
	if(wm_word_array[wordIndex].gramma=="   "/* && wm_word_array[wordIndex].gramma!="?"*/){
		output += "<span id='gramma_"+wordIndex+"_span' class='wm_input_span' ";
		//output += "onclick=\"switch_input_span("+wordIndex+",\'gramma\')\"";
		output += ">";
		output += getLocalGrammaStr(wm_word_array[wordIndex].gramma);
		output += "</span>"
		output += "</div>";
		//output +="</p>";
	}
	//else{
	//	output += "<span id='gramma_"+wordIndex+"_span' class='wm_input_span' ";
	//	output += "style='display:block'"
	//	output += "onclick=\"switch_input_span("+wordIndex+",\'gramma\')\">";
	//	output += "<i>?</i>";
	//	output += "</span>"
	//	output += "</div>";
	//}

	//渲染數量角標
	output +="<div id='info_num_gramma_dropdown_"+wordIndex+"_div' ";
	output +="class=\"info_num_gramma_dropdown\" ";
	output +="style='display:block'>";
	if(itemList.length && itemList.length!=0){
	output +="<span class='detail_info_num'>"//order-radius: 99px;
	output += itemList.length;
	output += "</span>"
	}

	output +="<div class=\"case_dropdown-content\">";
	output +="<button style='font-size:100%;display:inline-flex; padding:0.1em 0.5em' onclick='wm_field_change(\""+wordIndex+"\",\"gramma\",\"\")'>"+gLocal.gui.empty1+"</button>"
	for(var gramma_edit_i in itemList){
		output +="<a onclick='wm_field_change(\""+wordIndex+"\",\"gramma\",\""+itemList[gramma_edit_i].gramma+"\")'>["+getLocalDictname(itemList[gramma_edit_i].dict)+"]"+getLocalGrammaStr(itemList[gramma_edit_i].gramma)+"</a>";
	}

	output +="</div></div></span>";
	return(output);
}
function wm_caseChanged(wordIndex,position,newValue){
	switch(position){
		case "type":
			wm_case[0]=newValue;
			wm_case[1]+="";
			wm_case[2]+="";
			wm_case[3]+="";
			for(wm_caseChanged_i in wm_case){
				if(wm_case[wm_caseChanged_i]=="undefined"){
					wm_case[wm_caseChanged_i]=""
				}
			}
		break;
		case "case1":
			wm_case[0]+="";
			wm_case[1]=newValue;
			wm_case[2]+="";
			wm_case[3]+="";
			for(wm_caseChanged_i in wm_case){
				if(wm_case[wm_caseChanged_i]=="undefined"){
					wm_case[wm_caseChanged_i]=""
				}
			}
		break;
		case "case2":
			wm_case[0]+="";
			wm_case[1]+="";
			wm_case[2]=newValue;
			wm_case[3]+="";
			for(wm_caseChanged_i in wm_case){
				if(wm_case[wm_caseChanged_i]=="undefined"){
					wm_case[wm_caseChanged_i]=""
				}
			}
		break;
		case "case3":
			wm_case[0]+="";
			wm_case[1]+="";
			wm_case[2]+="";
			wm_case[3]=newValue;
			for(wm_caseChanged_i in wm_case){
				if(wm_case[wm_caseChanged_i]=="undefined"){
					wm_case[wm_caseChanged_i]=""
				}
			}
		break;
	}
	wm_refreshCaseSelect(wordIndex,wm_case);

}
function wm_refreshCaseSelect(wordIndex,wm_case){

	document.getElementById("wm_edit_gramma_"+wordIndex).innerHTML=wm_render_word_typegramma_edit(wm_case[0],wm_case[1],wm_case[2],wm_case[3],wordIndex,0.5);
	//var wm_newType=wm_case[0];
	if(wm_case[1]!="" && wm_case[1]){
		var wm_newGramma=wm_case[1];
	}
	if(wm_case[1]!="" && wm_case[1]){
		wm_newGramma+="$"+wm_case[2];
	}
	if(wm_case[1]!="" && wm_case[1]){
		wm_newGramma+="$"+wm_case[3];
	}
		wm_word_array[wordIndex].type=wm_case[0];
		wm_word_array[wordIndex].gramma=wm_newGramma;
	//document.getElementById("wm_input_case").value=wm_newCaseString;
}
