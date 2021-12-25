var g_findWord=""; // word for find
var g_findMode=""; // 1.parent 2.children 3.self
var g_currShowDeep=0;
var g_DictWordList= new Array();
var g_DocWordMean= new Array();
var g_dictList = new Array();
var g_DictWordNew = new Object();
var g_DictWordUpdataIndex=0;//正在更新的记录在内存字典表中的索引号

var g_DictCount=0;
var g_currEditWord=-1;
var g_currBookMarkColor="0";
var g_dictFindParentLevel=0;
var g_dictFindAllDone=false;

function setNaviVisibility(){
	var objNave = document.getElementById('leftmenuinner');
	var objMainView = document.getElementById("body_mainview");
	if(objNave.style.display=="none"){
		objNave.style.display="block";
		getStyleClass('mainview').style.margin = "0px 0px 0px 30em";
	}
	else{
		objNave.style.display="none";
		getStyleClass('mainview').style.margin = "0px";
	}
}



function menuSelected(obj){
	var objMenuItems=document.getElementsByClassName("menu");
	for (var i=0;i<objMenuItems.length;i++){
		objMenuItems[i].style.display="none";
	}
	var objThisItem = document.getElementById(obj.value);
	objThisItem.style.display="block";
}

function menu_file_print_printpreview(isPrev){
	printpreview(true);
}
function printpreview(isPrev){
	var objNave = document.getElementById('leftmenuinner');
	if(isPrev){
		setNaviVisibility();
		document.getElementById("toolbar").style.display="none";
		document.getElementById("word_table").style.display="none";
	}
	else{
		setNaviVisibility();
		document.getElementById("toolbar").style.display="block";
		document.getElementById("word_table").style.display="block";
	}
}

function menu_view_script_sinhala(){
	var xmlHttp=null;
	var xmlText="";
	
	var xmlScript=null;
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		xmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (xmlHttp!=null)
	{
		var d=new Date();
		var strLink = "./spt_get.php";
		xmlHttp.open("GET", strLink, false);
		xmlHttp.send(null);
		xmlText=xmlHttp.responseText;
		//xmlDict=xmlHttp.responseXML;
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
	if (window.DOMParser)
	  {
	  parser=new DOMParser();
	  xmlScript=parser.parseFromString(xmlText,"text/xml");
	  }
	else // Internet Explorer
	  {
	  xmlScript=new ActiveXObject("Microsoft.XMLDOM");
	  xmlScript.async="false";
	  xmlScript.loadXML(xmlText);
	  }

	  var xScriptWord = xmlScript.getElementsByTagName("word");
	  var xPaliWords = document.getElementsByClassName("paliword");
	  var arrPaliWords="";
	  for(k=0;k<xPaliWords.length;k++){
		arrPaliWords = arrPaliWords + "$" + xPaliWords[k].innerHTML;
	  }
	  arrPaliWords = arrPaliWords.toLowerCase();
	  for(i=0;i<xScriptWord.length;i++){
		var src = getNodeText(xScriptWord[i],"src");
		var dest = getNodeText(xScriptWord[i],"dest");
		var strReplace = "arrPaliWords = arrPaliWords.replace(/"+src+"/g, dest);";
		eval("arrPaliWords = arrPaliWords.replace(/"+src+"/g, dest);");
		//arrPaliWords = arrPaliWords.replace(src, dest);

	  }	  
	  var arrDestWords = arrPaliWords.split("$");
	  for(k=0;k<xPaliWords.length;k++){
		xPaliWords[k].innerHTML = arrDestWords[k+1];
	  }
	  document.getElementById("scriptinner").innerHTML=arrPaliWords;
	if (xmlScript == null){
		alert("error:can not load dict.");
		return;
	}

}

		var dict_xmlhttp;
		var arrDictFileList;
		var currMatchingDictNum=0;
		function dict_getDictFileList()
		{

		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  dict_xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  dict_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		var d=new Date();
		dict_xmlhttp.onreadystatechange=dict_serverResponse;
		dict_xmlhttp.open("GET","./dict_get_list.php?t="+d.getTime(),true);
		dict_xmlhttp.send();
		}

		function dict_serverResponse()
		{
			if (dict_xmlhttp.readyState==4)// 4 = "loaded"
			{
			  if (dict_xmlhttp.status==200)
				{// 200 = "OK"
				//arrDictFileList = dict_xmlhttp.responseText.split(",");
				var DictFileList = new Array;
				eval(dict_xmlhttp.responseText);
				for (x in arrDictFileList)
				{
					g_dictList[x]=arrDictFileList[x];
				}
				var fileList="";
				for (x in arrDictFileList)
				{
					if(arrDictFileList[x].used){
						fileList = fileList + "<p><input id='id_dict_file_list_"+x+"'  type='checkbox' style='width: 20px; height: 20px' checked />"+arrDictFileList[x].filename+"</p>";
					}
					else{
						fileList = fileList + "<p><input id='id_dict_file_list_"+x+"'  type='checkbox' style='width: 20px; height: 20px' />"+arrDictFileList[x].filename+"</p>";
					}
				}
				document.getElementById('basic_dict_list').innerHTML=fileList;
				if(g_findWord.length>0){
				menu_dict_match();
				}
				}
			  else
				{
				document.getElementById('basic_dict_list')="Problem retrieving data:" + xmlhttp.statusText;
				}
			}
		}

function dict_windowsInit(){
	var strSertch = location.search;
	if(strSertch.length>0){
		strSertch = strSertch.substr(1);
		var sertchList=strSertch.split('&');
		for (x in sertchList){
			var item = sertchList[x].split('=');
			if(item[0]=="word"){
				//g_findWord=item[1];
			}
			if(item[0]=="mode"){
				//g_findMode=item[1];
			}
		}
	}
	checkCookie();
	
	dict_getDictFileList();
	document.getElementById('id_info_window_select').value="view_dict_curr";
	windowsSelected(document.getElementById('id_info_window_select'));
}


function menu_dict_match(){
	currMatchingDictNum=0;
	g_dictFindParentLevel=0;
	g_dictFindAllDone=false;
	g_currShowDeep=0;
	dict_dict_match();
}


function dict_dict_match(){
	if(currMatchingDictNum<g_dictList.length){
		if(g_dictList[currMatchingDictNum].used){
			dict_loadDictFromDB(g_filename,g_dictList[currMatchingDictNum]);
		}
		else{
			currMatchingDictNum++;
			dict_dict_match();
		}
		if(g_dictFindAllDone){
			dictShowCurrWordList();
			//dictFindShow();
		}
	}
	else{
		document.getElementById('id_dict_match_result_inner').innerHTML=dictShowAsTable();

		if(g_findMode=="parent"){
			if(g_dictFindParentLevel<3){
				currMatchingDictNum=0;
				g_dictFindParentLevel++;
				document.getElementById('id_dict_match_inner').innerHTML+="finding parent level "+g_dictFindParentLevel+"<br>";
				dict_dict_match();
			}
			else{
				document.getElementById('id_dict_match_inner').innerHTML+="Max Parent Level "+g_dictFindParentLevel+" Stop!<br>";
				dictShowCurrWordList();
				//dictFindShow();		
			}
		}
		if(g_findMode=="children"){
			if(g_dictFindParentLevel>-2){
				currMatchingDictNum=0;
				g_dictFindParentLevel--;
				document.getElementById('id_dict_match_inner').innerHTML+="finding parent level "+g_dictFindParentLevel+"<br>";
				dict_dict_match();
			}
			else{
				document.getElementById('id_dict_match_inner').innerHTML+="Max Children Level "+g_dictFindParentLevel+" Stop!<br>";
				dictShowCurrWordList();
				//dictFindShow();		
			}
		}
	}
}

var dict_DictXmlHttp=null;
function dict_loadDictFromDB(strFileName,dictName){

	var xmlText="";
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_DictXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_DictXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_DictXmlHttp!=null)
	{
		var d=new Date();

		dict_DictXmlHttp.onreadystatechange=dict_dict_serverResponse;

		var wordList=dict_getAllWordList();
		if(wordList!=null){
			document.getElementById('id_dict_msg').innerHTML="开始匹配字典"+dictName.name;
			dict_DictXmlHttp.open("POST", "./dict_find2.php", true);
			dict_DictXmlHttp.send(dictName.type+"$"+dictName.filename+"$"+g_dictFindParentLevel+"$"+wordList);
		}
		else{
			g_dictFindAllDone=true;
			document.getElementById('id_dict_match_inner').innerHTML+="all done!";
		}
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function dict_dict_serverResponse(){
			if (dict_DictXmlHttp.readyState==4)// 4 = "loaded"
			{
				document.getElementById('id_dict_msg').innerHTML="已经获取字典数据";
			  if (dict_DictXmlHttp.status==200)
				{// 200 = "OK"
				var xmlText = dict_DictXmlHttp.responseText;
				
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlDict=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlDict=new ActiveXObject("Microsoft.XMLDOM");
				  xmlDict.async="false";
				  xmlDict.loadXML(xmlText);
				  }
			  
				if (xmlDict == null){
					alert("error:can not load dict.");
					return;
				}

				document.getElementById('id_dict_match_inner').innerHTML+=g_dictList[currMatchingDictNum].name+":"+xmlDict.getElementsByTagName("word").length+"<br />";
				dictDataParse(xmlDict,g_dictList[currMatchingDictNum].name);
				}
			  else
				{
				document.getElementById('id_dict_match_inner')="Problem retrieving data:" + xmlhttp.statusText;
				}
				
				currMatchingDictNum++;
				dict_dict_match();
				
			}
}

/*解析字典数据*/
function dictDataParse(xmlDictData,dictname){
	document.getElementById('id_dict_msg').innerHTML="正在解析字典数据";
			var xDict = xmlDictData.getElementsByTagName("word");				
			var tOut="";
			var sDictPali="";
			var sDictId="";
			var sDictOrg="";
			var sDictMean="";
			var sDictCase="";
			for(iword=0;iword<xDict.length;iword++)
			{
				var objDictItem=new Object();/*一个字典元素*/
				objDictItem.Id = getNodeText(xDict[iword],"id");
				objDictItem.Pali = getNodeText(xDict[iword],"pali");
				objDictItem.Mean = getNodeText(xDict[iword],"mean");
				objDictItem.Type = getNodeText(xDict[iword],"type");
				objDictItem.Gramma = getNodeText(xDict[iword],"gramma");
				objDictItem.Parent = getNodeText(xDict[iword],"parent");
				objDictItem.Factors = getNodeText(xDict[iword],"factors");
				objDictItem.FactorMean = getNodeText(xDict[iword],"factormean");
				objDictItem.Note = getNodeText(xDict[iword],"note");
				objDictItem.Confer = getNodeText(xDict[iword],"confer");
				objDictItem.Status = getNodeText(xDict[iword],"status");
				objDictItem.Delete = getNodeText(xDict[iword],"delete");
				objDictItem.Tag = getNodeText(xDict[iword],"tag");
				objDictItem.dictname=dictname;
				objDictItem.ParentLevel = g_dictFindParentLevel;

				if(objDictItem.Type==".v." && objDictItem.Pali.slice(-2)=="ti" && objDictItem.Pali.slice(-3)!="nti"){
					if(objDictItem.Pali!=objDictItem.Parent){
						objDictItem.Type=".v:base.";
					}
				}
				if(objDictItem.Case!="?" || objDictItem.Org!="?" || objDictItem.Mean!="?")
				{
					g_DictWordList[g_DictCount]=objDictItem;
					g_DictCount++;
				}

			}
			//dict end
}

function dictShowAsTable(){
	var outData="<table>";
	for(var i=0;i<g_DictWordList.length;i++){
		outData+="<tr class='dict_row"+g_DictWordList[i].ParentLevel+"'>";
		outData=outData+"<td>"+g_DictWordList[i].dictname+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Pali+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Type+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Gramma+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Parent+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Mean+"</td>";
		outData=outData+"<td>"+g_DictWordList[i].Factors+"</td>";
		outData=outData+"</tr>";
	}
	outData+="</table>";
	return outData;
}

var g_CurrActiveRecorder="new";
function setCurrActiveRecorder(recorderName){
	g_CurrActiveRecorder=recorderName;
}

function updataCurrActiveRecorder(filder,value){
	if(filder=="all"){
	}
	else{
		document.getElementById(filder+"_"+g_CurrActiveRecorder).value=value;
		mean_change(g_CurrActiveRecorder);
	}
}
function addToCurrActiveRecorder(filder,value){
	if(filder=="all"){
	}
	else{
		meanString=document.getElementById(filder+"_"+g_CurrActiveRecorder).value;
		meanList=meanString.split("$");
		for(i in meanList)
		{
			if(meanList[i]==value){
				return;
			}
		}
		document.getElementById(filder+"_"+g_CurrActiveRecorder).value+="$"+value;
		mean_change(g_CurrActiveRecorder);
	}
}

function updataFactorMeanPrev(id,strNew){
	//if(strNew!=null){
	//document.getElementById("id_factormean_prev_"+id).value=strNew;
	//}
}

function factorMeanItemChange(id,iPos,count,newMean){
	//alert(id+":"+iPos+":"+newMean);
	
	var factorMeanPrevString = document.getElementById("id_factormean_prev_"+id).value;
	currFactorMeanPrevList=factorMeanPrevString.split("+");
	currFactorMeanPrevList[iPos]=newMean;
	document.getElementById("id_factormean_prev_"+id).value=currFactorMeanPrevList.join("+");
	
}

function makeFactorBlock(factorStr,id){
	var output="";
	var factorList=factorStr.split("+");
	var defualtFactorMeanList=new Array;
	
	for(iFactor in factorList){
		arrFM=findAllMeanInDict(factorList[iFactor],10);
		if(arrFM.length==0){
			arrFM[0]="unkow";
		}
		output +="<div class='case_dropdown'>";
		output += "<p class='case_dropbtn' >";
		output += arrFM[0];
		defualtFactorMeanList.push(arrFM[0]);
		output +="</p>";
		output+="<div class=\"case_dropdown-content\">";	
		for(iFM in arrFM){
			output+="<a onclick=\"factorMeanItemChange('"+id+"','"+iFactor+"','"+factorList.length+"','"+arrFM[iFM]+"')\" >"+arrFM[iFM]+"</a>";
		}
		output+="</div>";
		output+="</div>";
		if(iFactor<factorList.length-1){
			output+="+";
		}		
	}
	//updataFactorMeanPrev(id,defualtFactorMeanList.join("+"));
	g_FactorMean=defualtFactorMeanList.join("+");
	return(output);
}
function factor_change(id){
	var factorString = document.getElementById("id_dict_user_factors_"+id).value;
	document.getElementById("id_factor_block_"+id).innerHTML=makeFactorBlock(factorString,id);
}


function makeMeanBlock(meanStr,id){
	var output="";
	var meanList=meanStr.split("$");
	for(i in meanList){
		output+="<div class=\"mean_cell\">";
		output+="<div class=\"button_shell\">";
		output+="<p class=\"mean_button\" onclick=\"meanBlockMove('"+id+"',"+i+","+(i-1)+")\">«</p>";
		output+="</div>";
		output+="<p class=\"mean_inner\" onclick=\"meanBlockMove('"+id+"',"+i+","+0+")\">"+meanList[i]+"</p>";
		output+="<div class=\"button_shell\">";
		output+="<p class=\"mean_button\" onclick=\"meanBlockDelete('"+id+"',"+i+")\">x</p>";
		output+="</div>";
		output+="</div>";
	}
	return(output);
}



function mean_change(id){
	var meanString = document.getElementById("id_dict_user_mean_"+id).value;
	document.getElementById("id_mean_block_"+id).innerHTML=makeMeanBlock(meanString,id);
}
function meanBlockDelete(id,indexDelete){
	var meanString = document.getElementById("id_dict_user_mean_"+id).value;
	var meanBlock="";
	var meanList=meanString.split("$");
	meanList.splice(indexDelete,1);
	var newString = meanList.join("$");
	document.getElementById("id_dict_user_mean_"+id).value=newString;
	mean_change(id);	
}

function meanBlockMove(id,moveFrom,moveTo){
	var meanString = document.getElementById("id_dict_user_mean_"+id).value;
	var meanBlock="";
	var meanList=meanString.split("$");
	if(moveTo<0){
		moveTo=0;
	}
	if(moveFrom==moveTo){
		return;
	}
	var temp=meanList[moveTo];
	meanList[moveTo]=meanList[moveFrom];
	for(i=moveFrom-1;i>moveTo;i--){
		meanList[i+1]=meanList[i];
	}	
	meanList[moveTo+1]=temp;
	var newString = meanList.join("$");
	/*
	for(x in meanList){
		newString+=meanList[x]+"$";
	}
	*/
	document.getElementById("id_dict_user_mean_"+id).value=newString;
	mean_change(id);
}

function addAutoMeanToFactorMean(id){
		document.getElementById("id_dict_user_fm_"+id).value=document.getElementById("id_factormean_prev_"+id).value;

}
//show current selected word in the word window to modify
var g_WordTableCurrWord="";

function dictCurrWordShowAsTable(inCurrWord){
	g_WordTableCurrWord = inCurrWord;
	g_CurrActiveRecorder="new";
	var outData="";
	var listParent= new Array();
	var listFactors= new Array();
	var listChildren = new Array();
	outData+="<p class='word_parent'>Parent:";
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(g_DictWordList[i].Parent.length>0){
				var find=false;
				for(x in listParent){
					if(listParent[x]==g_DictWordList[i].Parent){
						find=true;
						break;
					}
				}
				if(!find){
					listParent.push(g_DictWordList[i].Parent);
				}
			}
			if(g_DictWordList[i].Factors.length>0){
				arrFactors=g_DictWordList[i].Factors.split("+");
				for(iFactors in arrFactors){
					var find=false;
					for(x in listFactors){
						if(listFactors[x]==arrFactors[iFactors]){
							find=true;
							break;
						}
					}
					if(!find){
						listFactors.push(arrFactors[iFactors]);
					}
				}
			}
		}
	}
	for(x in listParent){
		outData+="<a onclick=\"showCurrWordTable('"+listParent[x]+"')\">"+listParent[x]+"</a> "
	}
	for(x in listFactors){
		outData+="[<a onclick=\"showCurrWordTable('"+listFactors[x]+"')\">"+listFactors[x]+"</a>] "
	}
	outData+="</p>";
	
	outData=outData+"<p class=\"word_current\">└"+inCurrWord+"</p>";
	
	outData+="<p class='word_child'>└Children: ";
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Parent==inCurrWord){
			if(g_DictWordList[i].Pali.length>0){
				var find=false;
				for(x in listChildren){
					if(listChildren[x]==g_DictWordList[i].Pali){
						find=true;
						break;
					}
				}
				if(!find){
					listChildren.push(g_DictWordList[i].Pali);
				}
			}
		}
	}
	for(x in listChildren){
		outData+="<a onclick=\"showCurrWordTable('"+listChildren[x]+"')\">"+listChildren[x]+"</a> "
	}
	outData+="</p>";		

	//get new recorder filder
	var newRecorder = new Object();
	newRecorder.Type="";
	newRecorder.Gramma="";
	newRecorder.Parent="";
	newRecorder.Mean="";
	newRecorder.Note="";
	newRecorder.Factors="";
	newRecorder.FactorMean="";
	newRecorder.Confer="";
	newRecorder.Status="";
	newRecorder.Lock="";
	newRecorder.Tag="";
	var newMeanList= new Array();
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(newRecorder.Type=="" && g_DictWordList[i].Type.length>0){
				newRecorder.Type=g_DictWordList[i].Type
			}
			if(newRecorder.Gramma=="" && g_DictWordList[i].Gramma.length>0){
				newRecorder.Gramma=g_DictWordList[i].Gramma;
			}
			if(newRecorder.Parent=="" && g_DictWordList[i].Parent.length>0){
				newRecorder.Parent=g_DictWordList[i].Parent;
			}
			if(g_DictWordList[i].Mean.length>0){
				otherMean = g_DictWordList[i].Mean.split("$");
				for(iMean in otherMean){
					pushNewToList(newMeanList,otherMean[iMean]);
				}
				newRecorder.Mean=newMeanList.join("$");
			}
			if(newRecorder.Factors=="" && g_DictWordList[i].Factors.length>0){
				newRecorder.Factors=g_DictWordList[i].Factors;
			}
			if(newRecorder.FactorMean=="" && g_DictWordList[i].FactorMean.length>0){
				newRecorder.FactorMean=g_DictWordList[i].FactorMean;
			}
			if(newRecorder.Note=="" && g_DictWordList[i].Note.length>0){
				newRecorder.Note=g_DictWordList[i].Note;
			}
		}
	}
	newMeanBlock=makeMeanBlock(newRecorder.Mean,"new");
	newFactorBlock=makeFactorBlock(newRecorder.Factors,"new");
	newFactorMeanPrevString=g_FactorMean;
	
	//draw new 
	outData=outData+"<h3>New:</h3>";
	outData+="<table>";
	
	outData+="<tr class='dict_row_new'><td></td><td>Type</td><td>Parent</td><td>Meaning</td></tr>";
	outData+="<tr class='dict_row_new'><td><input type='radio' name='dictupdata' checked onclick=\"setCurrActiveRecorder('new')\" /></td>"+
	"<td><input type=\"input\" id=\"id_dict_user_id_new\" hidden value=\"0\" >"+
	"<input type=\"input\" id=\"id_dict_user_pali_new\" hidden value=\""+inCurrWord+"\" >"+
	"	<select name=\"type\" id=\"id_dict_user_type_new\" onchange=\"typeChange(this)\">";
		for (x in gLocal.type_str){
		if(gLocal.type_str[x].id==newRecorder.Type){
			outData=outData+"<option value=\""+gLocal.type_str[x].id+"\" selected>"+gLocal.type_str[x].value+"</option>";
		}
		else{
			outData=outData+"<option value=\""+gLocal.type_str[x].id+"\">"+gLocal.type_str[x].value+"</option>";
		}
	}
	outData=outData+"	</select>";
	outData=outData+"</td>";
	outData=outData+"<td><input type=\"input\" id=\"id_dict_user_parent_new\" size=\"12\" value=\""+newRecorder.Parent+"\" /></td>";
	outData=outData+"<td><input type=\"input\" size='50' id=\"id_dict_user_mean_new\" value=\""+newRecorder.Mean+"\" onkeyup=\"mean_change('new')\"/><div class='mean_block' id='id_mean_block_new'>"+newMeanBlock+"</div></td>";
	outData+="</tr>";
	outData+="<tr class='dict_row_new'><td></td><td>Gramma</td><td>Parts</td><td>Parts Meaning</td></tr>";
	outData+="<tr class='dict_row_new'><td><button type=\"button\" onclick=\"dict_UserDictUpdata('new',this)\">Submit</button></td>";
	outData+="<td><input type=\"input\" id=\"id_dict_user_gramma_new\" size=\"12\" value=\""+newRecorder.Gramma+"\" /></td>";
	outData+="<td><input type=\"input\" id=\"id_dict_user_factors_new\" size=\""+inCurrWord.length*1.2+"\" value=\""+newRecorder.Factors+"\" onkeyup=\"factor_change('new')\" />";
	outData+="<br /><input type='text' id='id_factormean_prev_new' value='"+newFactorMeanPrevString+"' hidden />";
	outData+="<div class='factor' id='id_factor_block_new'>"+newFactorBlock+"</div>";
	outData+="<button type=\"button\" onclick=\"addAutoMeanToFactorMean('new')\" >√</button></td>";
	outData+="<td><input type=\"input\" id=\"id_dict_user_fm_new\" size=\""+inCurrWord.length*1.5+"\" value=\""+newRecorder.FactorMean+"\" /></td></tr>";
	outData+="<tr class='dict_row_new'><td>Note</td>";
	outData+="<td colspan=3><textarea id=\"id_dict_user_note_new\" rows='3' cols='100'>"+newRecorder.Note+"</textarea></td></tr>"
	
	outData+="</table>";

	outData+="<h3>User Dict</h3>";
	outData+="<table>";
/*	
	outData=outData+"<tr><th></th><th>dict</th> <th>Type</th> <th>Gramma</th> <th>Parent</th> <th>Meaning</th> <th>Parts</th> <th>Parts Meaning</th> <th></th> </tr>";
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(g_DictWordList[i].dictname=="用户字典"){
				outData+="<tr class='dict_row"+g_DictWordList[i].ParentLevel+"'>";
				outData+="<td><input type=radio name='dictupdata' onclick=\"setCurrActiveRecorder('"+i+"')\" /></td>";
				outData=outData+"<td>"+g_DictWordList[i].dictname+"</td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_id_"+i+"\" hidden value=\""+g_DictWordList[i].Id+"\" >";
				outData=outData+"<input type=\"input\" id=\"id_dict_user_pali_"+i+"\" hidden value=\""+g_DictWordList[i].Pali+"\" >";
				outData=outData+"	<select name=\"type\" id=\"id_dict_user_type_"+i+"\" onchange=\"typeChange(this)\">";
				for (x in gLocal.type_str){
					if(gLocal.type_str[x].id==g_DictWordList[i].Type){
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\" selected>"+gLocal.type_str[x].value+"</option>";
					}
					else{
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\">"+gLocal.type_str[x].value+"</option>";
					}
				}
				outData=outData+"	</select>";
				outData=outData+"</td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_gramma_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Gramma+"\" /></td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_parent_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Parent+"\" />";
				outData=outData+"<button type='button' onclick=\"showCurrWordTable('"+g_DictWordList[i].Parent+"')\">»</button></td>";
				outData=outData+"<td><input type=\"input\" size='50' id=\"id_dict_user_mean_"+i+"\" value=\""+g_DictWordList[i].Mean+"\" onkeyup='mean_change("+i+")' /><div class='mean_block' id='id_mean_block_"+i+"'></div></td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_factors_"+i+"\" size=\"15\" value=\""+g_DictWordList[i].Factors+"\" /></td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_fm_"+i+"\" size=\"15\" value=\""+g_DictWordList[i].FactorMean+"\" /></td>";
				outData=outData+"<td><button type=\"button\" onclick=\"dict_UserDictUpdata('"+i+"',this)\">Updata</button></td>";
				outData=outData+"</tr>";
			}
		}
	}
	*/
	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(g_DictWordList[i].dictname=="用户字典"){
				outData+="<tr ><td></td><td>Type</td><td>Parent</td><td>Meaning</td></tr>";
				outData+="<tr ><td><input type='radio' name='dictupdata' checked onclick=\"setCurrActiveRecorder('new')\" /></td>"+
				"<td><input type=\"input\" id=\"id_dict_user_id_new"+i+"\" hidden value=\"0\" >"+
				"<input type=\"input\" id=\"id_dict_user_pali_"+i+"\" hidden value=\""+inCurrWord+"\" >"+
				"	<select name=\"type\" id=\"id_dict_user_type_"+i+"\" onchange=\"typeChange(this)\">";
					for (x in gLocal.type_str){
					if(gLocal.type_str[x].id==g_DictWordList[i].Type){
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\" selected>"+gLocal.type_str[x].value+"</option>";
					}
					else{
						outData=outData+"<option value=\""+gLocal.type_str[x].id+"\">"+gLocal.type_str[x].value+"</option>";
					}
				}
				outData=outData+"	</select>";
				outData=outData+"</td>";
				outData=outData+"<td><input type=\"input\" id=\"id_dict_user_parent_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Parent+"\" /></td>";
				outData=outData+"<td><input type=\"input\" size='50' id=\"id_dict_user_mean_"+i+"\" value=\""+g_DictWordList[i].Mean+"\" onkeyup=\"mean_change('new')\"/><div class='mean_block' id='id_mean_block_new'>"+newMeanBlock+"</div></td>";
				outData+="</tr>";
				outData+="<tr ><td></td><td>Gramma</td><td>Parts</td><td>Parts Meaning</td></tr>";
				outData+="<tr ><td><button type=\"button\" onclick=\"dict_UserDictUpdata('"+i+"',this)\">Submit</button></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_gramma_"+i+"\" size=\"12\" value=\""+g_DictWordList[i].Gramma+"\" /></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_factors_"+i+"\" size=\""+inCurrWord.length*1.2+"\" value=\""+g_DictWordList[i].Factors+"\" onkeyup=\"factor_change('new')\" />";
				outData+="<br /><input type='text' id='id_factormean_prev_"+i+"' value='"+newFactorMeanPrevString+"' hidden />";
				outData+="<div class='factor' id='id_factor_block_"+i+"'>"+newFactorBlock+"</div>";
				outData+="<button type=\"button\" onclick=\"addAutoMeanToFactorMean('"+i+"')\" >√</button></td>";
				outData+="<td><input type=\"input\" id=\"id_dict_user_fm_"+i+"\" size=\""+inCurrWord.length*1.5+"\" value=\""+g_DictWordList[i].FactorMean+"\" /></td></tr>";
				outData+="<tr ><td>Note</td>";
				outData+="<td colspan=3><textarea id=\"id_dict_user_note_"+i+"\" rows='3' cols='100'>"+g_DictWordList[i].Note+"</textarea></td></tr>"
			
			}
		}
	}
	
	outData=outData+"</table>";
	
	outData+="<h3>Other Dict</h3>";
	outData+="<table>";
	outData=outData+"<tr><th></th><th>dict</th> <th>Type</th> <th>Gramma</th> <th>Parent</th> <th>Meaning</th> <th>Parts</th> <th>Parts Meaning</th> <th>use</th> </tr>";

	for(var i=0;i<g_DictWordList.length;i++){
		if(g_DictWordList[i].Pali==inCurrWord){
			if(g_DictWordList[i].dictname=="用户字典"){
			}
			else{
				outData+="<tr class='dict_row"+g_DictWordList[i].ParentLevel+"'>";
				outData+="<td><button type=\"button\" >√</button></td>";
				outData=outData+"<td>"+g_DictWordList[i].dictname+"</td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_type','"+g_DictWordList[i].Type+"')\" >√</button><span id=\"id_dict_user_gramma_"+i+"\">"+g_DictWordList[i].Type+"</span></td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_gramma','"+g_DictWordList[i].Gramma+"')\">√</button>"+g_DictWordList[i].Gramma+"</td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_parent','"+g_DictWordList[i].Parent+"')\">√</button>"+g_DictWordList[i].Parent;
				outData=outData+"<button type='button' onclick=\"showCurrWordTable('"+g_DictWordList[i].Parent+"')\">»</button></td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_mean','"+g_DictWordList[i].Mean+"')\">√</button>"+g_DictWordList[i].Mean+"<br />"+makeMeanLink(g_DictWordList[i].Mean)+"</td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_factors','"+g_DictWordList[i].Factors+"')\">√</button>"+g_DictWordList[i].Factors+"</td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_fm','"+g_DictWordList[i].FactorMean+"')\">√</button>"+g_DictWordList[i].FactorMean+"</td>";
				outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('all','"+g_DictWordList[i].Type+"')\">√</button></td>";
				outData=outData+"</tr>";
			}
		}
	}
	outData+="</table>";
	
	//children
	for(x in listChildren){
		wordChildren=listChildren[x]
		outData+="<h4>"+wordChildren+"</h4> ";
		outData+="<table>";
		outData=outData+"<tr><th></th><th>dict</th> <th>Type</th> <th>Gramma</th> <th>Parent</th> <th>Meaning</th> <th>Parts</th> <th>Parts Meaning</th> <th></th> </tr>";
		for(var i=0;i<g_DictWordList.length;i++){
			if(g_DictWordList[i].Pali==wordChildren){
				{
					outData+="<tr class='dict_row"+g_DictWordList[i].ParentLevel+"'>";
					outData+="<td><button type=\"button\" >√</button></td>";
					outData=outData+"<td>"+g_DictWordList[i].dictname+"</td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_type','"+g_DictWordList[i].Type+"')\" >√</button><span id=\"id_dict_user_gramma_"+i+"\">"+g_DictWordList[i].Type+"</span></td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_gramma','"+g_DictWordList[i].Gramma+"')\">√</button>"+g_DictWordList[i].Gramma+"</td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_parent','"+g_DictWordList[i].Parent+"')\">√</button>"+g_DictWordList[i].Parent;
					outData=outData+"<button type='button' onclick=\"showCurrWordTable('"+g_DictWordList[i].Parent+"')\">»</button></td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_mean','"+g_DictWordList[i].Mean+"')\">√</button>"+g_DictWordList[i].Mean+"<br />"+makeMeanLink(g_DictWordList[i].Mean)+"</td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_factors','"+g_DictWordList[i].Factors+"')\">√</button>"+g_DictWordList[i].Factors+"</td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('id_dict_user_fm','"+g_DictWordList[i].FactorMean+"')\">√</button>"+g_DictWordList[i].FactorMean+"</td>";
					outData=outData+"<td><button type=\"button\" onclick=\"updataCurrActiveRecorder('all','"+g_DictWordList[i].Type+"')\">√</button></td>";
					outData=outData+"</tr>";
				}
			}
		}		
		outData+="</table>";
	}
	return outData;
}
function makeMeanLink(inStr){
	var arrList=inStr.split("$");
	var output="";
	for(i in arrList){
		output+="<a onclick=\"addToCurrActiveRecorder('id_dict_user_mean','"+arrList[i]+"')\">"+arrList[i]+"</a> "
	}
	return(output);
}

function showCurrWordTable(currWord){
document.getElementById('id_dict_curr_word_inner').innerHTML=dictCurrWordShowAsTable(currWord);
}





//匹配字典数据到文档
function dictMatchXMLDoc(){
document.getElementById('id_dict_msg').innerHTML=gLocal.gui.dict_match;
var docWordCounter=0;
var matchedCounter=0;

	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	for(var iword=0;iword<xDocWords.length;iword++){
		var sPaliWord = getNodeText(xDocWords[iword],"real");
		var sFactorsWord = getNodeText(xDocWords[iword],"org");
		var sMeanWord = getNodeText(xDocWords[iword],"mean");
		var sTypeWord = getNodeText(xDocWords[iword],"case");
		
		if(isPaliWord(sPaliWord)){
			docWordCounter++;
		
		/*将这个词与字典匹配，*/
		var iDict=0;
		//if(sMeanWord=="?"){
			var thisWord = sPaliWord
			for(iDict=0;iDict<g_DictWordList.length;iDict++){
				if(thisWord==g_DictWordList[iDict].Pali  && g_DictWordList[iDict].ParentLevel==0){
					if(sMeanWord=="?"){
						setNodeText(xDocWords[iword],"bmc","bmca");	
					}
					modifyWordDetailByWordIndex(iword);
					matchedCounter++;
					break;
				}
			}
		//}
		/*
		else{
			if(isPaliWord(sPaliWord)){
				matchedCounter++;
			}
		}*/
		
		}
	}
	
	var progress=matchedCounter*100/docWordCounter;
	
	document.getElementById('id_dict_msg').innerHTML=gLocal.gui.match_end+Math.round(progress)+"%";
}


function dictGetFirstMean(strMean){
	var arrMean=strMean.split("$");
	if(arrMean.length>0){
		for(var i=0;i<arrMean.length;i++){
			if(arrMean[i].length>0){
				return(arrMean[i]);
			}
			else{
				return "";
			}
		}
		return "";
	}
	else{
		return "";
	}
}
//test word is pali word or not
function isPaliWord(inWord){
	if(inWord.length<2){
		return false;
	}
	if(inWord.match(/[x]/)){
		return false;
	}
	if(inWord.match(/[q]/)){
		return false;
	}
	if(inWord.match(/[w]/)){
		return false;
	}
	if(inWord.match(/[a-y]/)){
		return true;
	}
	else{
		return false;
	}
}

function submenu_show_detail(obj){
	eParent = obj.parentNode;
	var x=eParent.getElementsByTagName("div");
	if(x[0].style.display=="none"){
		x[0].style.display="block";
		obj.getElementsByTagName("span")[0].innerHTML="-";
	}
	else{
		x[0].style.display="none";
		obj.getElementsByTagName("span")[0].innerHTML="+";
	}
	
}

//在导航窗口中显示与此词匹配的字典中的词
function showMatchedWordsInNavi(wordId){
	//var matchedCounter=0;
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	//var outText="";
	//var sLastDict="";

	var sPaliWord = getNodeText(xDocWords[wordId],"real");
	showWordInNavi(sPaliWord);
}

//在导航窗口中显示与此词匹配的字典中的词
function showWordInNavi(inWord){
	var matchedCounter=0;
	var outText="";
	var sLastDict="";

	var sPaliWord = inWord;
	outText=outText+"<h3>"+sPaliWord+"</h3>";
		/*将这个词与字典匹配，*/
		var iDict=0;
			var thisWord = sPaliWord;
			for(iDict=0;iDict<g_DictWordList.length;iDict++){
				if(thisWord==g_DictWordList[iDict].Pali){
					if(g_DictWordList[iDict].dictname!=sLastDict){
						outText=outText+"<dict><span>"+g_DictWordList[iDict].dictname+"<span></dict>";
						sLastDict=g_DictWordList[iDict].dictname;
					}
					outText=outText+"<input type='input' id=\"id_dict_word_list_"+iDict+"\" size='5' value='"+g_DictWordList[iDict].Type+"' />";
					outText=outText+"<input type='input' size='15' value='"+g_DictWordList[iDict].Gramma+"' /><br />";
					outText=outText+"<input type='input' size='20' value='"+g_DictWordList[iDict].Parent+"' /> <button type='button' onclick=\"showWordInNavi('"+g_DictWordList[iDict].Parent+"')\">»</button><br />";
					outText=outText+"<textarea name=\"dict_mean\" rows=\"3\" col=\"25\" style=\"width:20em;\">"+g_DictWordList[iDict].Mean+"</textarea>"
					outText=outText+"<input type='input' size='20' value='"+g_DictWordList[iDict].Factors+"' /><br />";
					outText=outText+"<input type='input' size='20' value='"+g_DictWordList[iDict].FactorMean+"' /><br />";
					outText=outText+"<button type='button' onclick=\"updataDict('"+iDict+"','userdict')\">Modify</button><br />";
					/*
					outText=outText+"<mean onclick=\"updataWordFromDict(this,'mean')\">"+g_DictWordList[iDict].Mean+"</mean>";				
					outText=outText+"<org onclick=\"updataWordFromDict(this,'org')\">"+g_DictWordList[iDict].Factors+"</org>";
					outText=outText+"<om onclick=\"updataWordFromDict(this,'om')\">"+g_DictWordList[iDict].FactorMean+"</om>";					
					outText=outText+"<case onclick=\"updataWordFromDict(this,'case')\">"+g_DictWordList[iDict].Type+"#"+g_DictWordList[iDict].Gramma+"</case>";
					*/					
					matchedCounter++;
				}
			}

	document.getElementById("id_dict_matched").innerHTML=outText;
	document.getElementById('id_dict_curr_word_inner').innerHTML=dictCurrWordShowAsTable(inWord);
}

function updataWordFromDict(obj,field){
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	var strValue = obj.innerHTML;
	var applayTo = document.getElementById("id_dict_applay_to").value;
	var strCurrPali=getNodeText(xDocWords[g_currEditWord],"pali");
	switch(applayTo)
	{
	case "current":
		setNodeText(xDocWords[g_currEditWord],field,strValue);
		modifyWordDetailByWordIndex(g_currEditWord);	  
		break;
	case "sys":
		for(i=0;i<xDocWords.length;i++){
			var strPali=getNodeText(xDocWords[i],"pali");
			if(strCurrPali==strPali){
				var isAuto = getNodeText(xDocWords[i],"bmc");
				if(isAuto=="bmca"){
					setNodeText(xDocWords[g_currEditWord],field,strValue);
					modifyWordDetailByWordIndex(i);
				}
			}
		}
		break;
	case "all":
		for(i=0;i<xDocWords.length;i++){
			var strPali=getNodeText(xDocWords[i],"pali");
			if(strCurrPali==strPali){
				setNodeText(xDocWords[g_currEditWord],field,strValue);
				modifyWordDetailByWordIndex(i);
			}
		}
		break;	  
	}

}
/*
function select_modyfy_type(itemname){
	document.getElementById("modify_detaile").style.display="none";
	document.getElementById("modify_bookmark").style.display="none";
	document.getElementById("modify_note").style.display="none";
	document.getElementById(itemname).style.display="block";
}*/

function select_modyfy_type(itemname,idname){
	document.getElementById("modify_detaile").style.display="none";
	document.getElementById("modify_bookmark").style.display="none";
	document.getElementById("modify_note").style.display="none";
	document.getElementById("modify_spell").style.display="none";
	document.getElementById("modify_apply").style.display="block";
	document.getElementById("detail_li").className = "common-tab_li";
	document.getElementById("mark_li").className = "common-tab_li";
	document.getElementById("note_li").className = "common-tab_li";
	document.getElementById("spell_li").className = "common-tab_li";
	

	document.getElementById(itemname).style.display="block";
	document.getElementById(idname).className = "common-tab_li_act";
}

function setBookMarkColor(obj,strColor){
	var items = obj.parentNode.getElementsByTagName("li");
	for(var i=0;i<items.length;i++)
	{
		items[i].style.border="2px solid #EEE";
	}
	if(strColor!="bmc0"){
		obj.style.border="2px solid #555";
	}
	g_currBookMarkColor=strColor;
}

function getBookMarkColor(idColor){
	var items = document.getElementById("id_book_mark_color_select").getElementsByTagName("li");
	for(var i=0;i<items.length;i++)
	{
		items[i].style.border="2px solid #EEE";
	}
	if(document.getElementById("id_"+idColor)){
		document.getElementById("id_"+idColor).style.border="2px solid #555";
	}
}
/*
function casePrev(obj){
	var strCase = obj.value;
	strCase=strCase.replace(/f./g,"阴");
	strCase=strCase.replace(/m./g,"阳");
	strCase=strCase.replace(/n./g,"中");
	strCase=strCase.replace(/sg./g,"单");
	strCase=strCase.replace(/pl./g,"复");
	strCase=strCase.replace(/acc./g,"宾");
	document.getElementById("id_case_prev").innerHTML=strCase;
}
*/
function menu_file_convert(){
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	var outText="";
	var sLastDict="";
	for(var iword=0;iword<xDocWords.length;iword++){
		var sPaliWord = getNodeText(xDocWords[iword],"pali");
		var sPaliMean = getNodeText(xDocWords[iword],"mean");
		var thisWord = sPaliWord.toLowerCase();
		thisWord = thisWord.replace(/-/g,"");
		thisWord = thisWord.replace(/'/g,"");
		thisWord = thisWord.replace(/’/g,"");
		setNodeText(xDocWords[iword],"real",thisWord);
		setNodeText(xDocWords[iword],"om",sPaliMean);
	}
	alert("convert "+xDocWords.length+"words.");
}
function save(){
	xmlHttp=null;
	var_dump(gLocal.gui.loading);
	if (window.XMLHttpRequest)
	  {// code for IE7, Firefox, Opera, etc.
	  xmlHttp=new XMLHttpRequest();
	  var_dump("test XMLHttpRequest<br/>");
	  }
	else if (window.ActiveXObject)
	  {// code for IE6, IE5
	  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  var_dump("testing Microsoft.XMLHTTP<br/>");
	  }
	  
	if (xmlHttp!=null)
	  {
	  var_dump("ok");
	  xmlHttp.open("POST", "./dom_http.php", false);
	  var sendHead="filename="+g_filename+"#";
	  xmlHttp.send(sendHead+xmlToString(xmlDoc));
	  var_dump(xmlHttp.responseText);
	  }
	else
	  {
	  alert("Your browser does not support XMLHTTP.");
	  }
}

function menu_file_export_csv(){
	xmlHttp=null;
	var_dump(gLocal.gui.loading);
	if (window.XMLHttpRequest)
	  {// code for IE7, Firefox, Opera, etc.
	  xmlHttp=new XMLHttpRequest();
	  var_dump("test XMLHttpRequest<br/>");
	  }
	else if (window.ActiveXObject)
	  {// code for IE6, IE5
	  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  var_dump("testing Microsoft.XMLHTTP<br/>");
	  }
	  
	if (xmlHttp!=null)
	  {
	  var_dump("ok");
	  xmlHttp.open("POST", "./export_csv.php", false);
	  var sendHead="filename="+g_filename+"#";
	  xmlHttp.send(sendHead+xmlToString(xmlDoc));
	  var_dump(xmlHttp.responseText);
	  }
	else
	  {
	  alert("Your browser does not support XMLHTTP.");
	  }
}


function menu_file_import_csv(){
	var filename = document.getElementById('import_csv_filename').value;
	dict_loadDataFromCSV(filename);
}
//import csv begin
var dict_CSVXmlHttp=null;
function dict_loadDataFromCSV(strFileName){
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_CSVXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_CSVXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_CSVXmlHttp!=null)
	{
		var d=new Date();
		var strLink = "./import_csv.php?filename="+strFileName;
		dict_CSVXmlHttp.onreadystatechange=dict_csv_serverResponse;
		dict_CSVXmlHttp.open("GET", strLink, true);
		dict_CSVXmlHttp.send(null);
		document.getElementById('id_csv_msg_inner').innerHTML="Importing..."+strFileName;
		
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function dict_csv_serverResponse()
{
			if (dict_CSVXmlHttp.readyState==4)// 4 = "loaded"
			{
				document.getElementById('id_csv_msg_inner').innerHTML="receve csv data";
			  if (dict_CSVXmlHttp.status==200)
				{// 200 = "OK"
				var xmlText = dict_CSVXmlHttp.responseText;
				
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlCsv=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlCsv=new ActiveXObject("Microsoft.XMLDOM");
				  xmlCsv.async="false";
				  xmlCsv.loadXML(xmlText);
				  }
			  
				if (xmlCsv == null){
					alert("error:can not load dict.");
					return;
				}

				csvDataParse(xmlCsv);
				}
			  else
				{
				document.getElementById('id_dict_match_inner')="Problem retrieving data:" + xmlhttp.statusText;
				}
					
			}
}

/*Parse csv data and fill this document*/
function csvDataParse(xmlCSVData){
	document.getElementById('id_csv_msg_inner').innerHTML="Parseing CSV Data";
	var xCSV = xmlCSVData.getElementsByTagName("word");	
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");	
	
	for(iword=0;iword<xCSV.length;iword++){
		setNodeText(xDocWords[iword],"pali",getNodeText(xCSV[iword],"pali"));
		setNodeText(xDocWords[iword],"real",getNodeText(xCSV[iword],"real"));		
		setNodeText(xDocWords[iword],"id",getNodeText(xCSV[iword],"id"));
		setNodeText(xDocWords[iword],"mean",getNodeText(xCSV[iword],"mean"));
		setNodeText(xDocWords[iword],"org",getNodeText(xCSV[iword],"org"));
		setNodeText(xDocWords[iword],"om",getNodeText(xCSV[iword],"om"));
		setNodeText(xDocWords[iword],"case",getNodeText(xCSV[iword],"case"));
		setNodeText(xDocWords[iword],"bmc",getNodeText(xCSV[iword],"bmc"));
		setNodeText(xDocWords[iword],"bmt",getNodeText(xCSV[iword],"bmt"));
		setNodeText(xDocWords[iword],"note",getNodeText(xCSV[iword],"note"));
		setNodeText(xDocWords[iword],"lock",getNodeText(xCSV[iword],"lock"));
		modifyWordDetailByWordIndex(iword);
	}
	document.getElementById('id_csv_msg_inner').innerHTML="Updata Document Data OK!";
}

//import csv end
function menu_file_tools_empty(opt){
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");
	if(opt=='all'){
		for(var iword=0;iword<xDocWords.length;iword++){
			setNodeText(xDocWords[iword],"mean","?");
			setNodeText(xDocWords[iword],"org","?");
			setNodeText(xDocWords[iword],"om","?");
			setNodeText(xDocWords[iword],"case","?");
			setNodeText(xDocWords[iword],"parent","?");
			setNodeText(xDocWords[iword],"bmc","");
			setNodeText(xDocWords[iword],"bmt","");
			setNodeText(xDocWords[iword],"note","");
			setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}	
	}
	else if(opt=='mean'){
		for(var iword=0;iword<xDocWords.length;iword++){
			setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			setNodeText(xDocWords[iword],"om","");
			//setNodeText(xDocWords[iword],"case","?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	}
	else if(opt=='case'){
			for(var iword=0;iword<xDocWords.length;iword++){
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			setNodeText(xDocWords[iword],"case","?#?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	}
	else if(opt=='bookmark'){
			for(var iword=0;iword<xDocWords.length;iword++){
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			//setNodeText(xDocWords[iword],"case","?#?");
			setNodeText(xDocWords[iword],"bmc","");
			setNodeText(xDocWords[iword],"bmt","");
			//setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}
	}
	else if(opt=='note'){
			for(var iword=0;iword<xDocWords.length;iword++){
			//setNodeText(xDocWords[iword],"mean","[]");
			//setNodeText(xDocWords[iword],"org","?");
			//setNodeText(xDocWords[iword],"om","");
			//setNodeText(xDocWords[iword],"case","?#?");
			//setNodeText(xDocWords[iword],"bmc","");
			//setNodeText(xDocWords[iword],"bmt","");
			setNodeText(xDocWords[iword],"note","");
			//setNodeText(xDocWords[iword],"lock","FALSE");
			modifyWordDetailByWordIndex(iword);
		}	
	}
}

function showDebugPanal(){
var w=window.innerWidth
|| document.documentElement.clientWidth
|| document.body.clientWidth;

var h=window.innerHeight
|| document.documentElement.clientHeight
|| document.body.clientHeight;


}

function show_popup(strMsg)
{
var p=window.createPopup()
var pbody=p.document.body
pbody.style.backgroundColor="red"
pbody.style.border="solid black 1px"
pbody.innerHTML=strMsg+"<br />外面点击，即可关闭它！"
p.show(150,150,200,50,document.body)
}

function setInfoPanalSize(inSize){
var w=window.innerWidth
|| document.documentElement.clientWidth
|| document.body.clientWidth;

var h=window.innerHeight
|| document.documentElement.clientHeight
|| document.body.clientHeight;

	var objInfoPanal = document.getElementById("id_info_panal");
	//show_popup(w);
	//alert(objInfoPanal.style.right);
	objInfoPanal.style.left="0px";
	objInfoPanal.style.width=(w-20)+"px";
	
	switch(inSize){
		case "hidden"://min
			objInfoPanal.style.display="none";
		break;

	case "min"://min
			objInfoPanal.style.top=(h-30)+"px";
			objInfoPanal.style.height=(30)+"px";
		break;
		case "half"://half
			objInfoPanal.style.top=h/2+"px";
			objInfoPanal.style.height=h/2+"px";
		break;
		case "0.6"://2/3
			objInfoPanal.style.top=(h*0.4)+"px";
			objInfoPanal.style.height=(h*0.6)+"px";
		break;
		case "max"://max
			objInfoPanal.style.top="0px";
			objInfoPanal.style.height=(h)+"px";
		break;
	}
}

function windowsSelected(obj){
	document.getElementById('word_table').style.display = "none";
	document.getElementById('id_dict_match_result').style.display = "none";
	document.getElementById('id_dict_curr_word').style.display = "none";
	document.getElementById('id_debug').style.display = "none";
	switch(obj.value){
		case "view_vocabulary":
			document.getElementById('word_table').style.display = "block";
			break;
		case "view_dict_all":
			document.getElementById('id_dict_match_result').style.display = "block";
			break;
		case "view_dict_curr":
			document.getElementById('id_dict_curr_word').style.display = "block";
			break;
		case "view_debug":
			document.getElementById('id_debug').style.display = "block";
			break;
		}
}

function userDictUpdata(){

}

var dict_DictUpdataXmlHttp=null;
function dict_UserDictUpdata(recorderName,thisObj){
	thisObj.disabled=true;
	var xmlText="";
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_DictUpdataXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_DictUpdataXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_DictUpdataXmlHttp!=null)
	{
		var queryString="<wordlist>";
		queryString+="<word>";
		var d_id=document.getElementById('id_dict_user_id_'+recorderName).value;
		var d_pali=document.getElementById('id_dict_user_pali_'+recorderName).value;
		var d_type=document.getElementById('id_dict_user_type_'+recorderName).value;
		var d_gramma=document.getElementById('id_dict_user_gramma_'+recorderName).value;
		var d_parent=document.getElementById('id_dict_user_parent_'+recorderName).value;
		var d_mean=document.getElementById('id_dict_user_mean_'+recorderName).value;
		var d_note=document.getElementById('id_dict_user_note_'+recorderName).value;
		var d_factors=document.getElementById('id_dict_user_factors_'+recorderName).value;
		var d_fm=document.getElementById('id_dict_user_fm_'+recorderName).value;
		var d_confer="";
		var d_status="";
		var d_delete="";
		var d_tag="";
		queryString+="<id>"+d_id+"</id>";
		queryString+="<pali>"+d_pali+"</pali>";
		queryString+="<type>"+d_type+"</type>";
		queryString+="<gramma>"+d_gramma+"</gramma>";
		queryString+="<parent>"+d_parent+"</parent>";
		queryString+="<mean>"+d_mean+"</mean>";
		queryString+="<note>"+d_note+"</note>";
		queryString+="<factors>"+d_factors+"</factors>";
		queryString+="<fm>"+d_fm+"</fm>";
		queryString+="<confer>"+d_confer+"</confer>";
		queryString+="<status>"+d_status+"</status>";
		queryString+="<delete>"+d_delete+"</delete>";
		queryString+="<tag>"+d_tag+"</tag>";
		queryString+="</word>";
		queryString+="</wordlist>";
		dict_DictUpdataXmlHttp.onreadystatechange=dict_UserDictUpdata_serverResponse;
		debugOutput("updata user dict start.",0);
		dict_DictUpdataXmlHttp.open("POST", "./dict_updata_user.php", true);
		dict_DictUpdataXmlHttp.send(queryString);
		
		var i=recorderName;
		g_DictWordUpdataIndex=i;
			g_DictWordNew.Id = d_id;
			g_DictWordNew.Pali = d_pali;
			g_DictWordNew.Mean = d_mean;
			g_DictWordNew.Type = d_type;
			g_DictWordNew.Gramma = d_gramma;
			g_DictWordNew.Parent = d_parent;
			g_DictWordNew.Factors = d_factors;
			g_DictWordNew.FactorMean = d_fm;
			g_DictWordNew.Note = d_note;
			g_DictWordNew.Confer = d_confer;
			g_DictWordNew.Status = d_status;
			g_DictWordNew.Delete = d_delete;
			g_DictWordNew.dictname="用户字典";
			g_DictWordNew.ParentLevel=0;
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function dict_UserDictUpdata_serverResponse(){
	if (dict_DictUpdataXmlHttp.readyState==4)// 4 = "loaded"
	{
		debugOutput("server response.",0);
		if (dict_DictUpdataXmlHttp.status==200)
		{// 200 = "OK"
			var serverText = dict_DictUpdataXmlHttp.responseText;
			debugOutput(serverText,0);
			obj = JSON.parse(serverText);
			if(obj.msg[0].server_return==-1){
				alert(obj.msg[0].server_error);
			}
			else{
				var_dump("user dict "+obj.msg[0].server_op+" ok");
				switch(obj.msg[0].server_op){
					case "insert":
						g_DictWordNew.Id=obj.msg[0].server_return;
						g_DictWordList.unshift(g_DictWordNew);
					break;
					case "update":
						g_DictWordList[g_DictWordUpdataIndex]=g_DictWordNew;
					break;
				}
			}
			showCurrWordTable(g_WordTableCurrWord);
			modifyWordDetailByWordIndex(g_currEditWord);
		}
		else
		{
			debugOutput(xmlhttp.statusText,0);
		}
	}
}

// word by word dict updata
var dict_wbwUpdataXmlHttp=null;
function dict_WbwUpdata(wordIdFrom,wordIdTo){

	var xmlText="";
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_wbwUpdataXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_wbwUpdataXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_wbwUpdataXmlHttp!=null)
	{
		var queryString="<wordlist>";
		var x = gXmlBookDataBody.getElementsByTagName("word");
		
		for(var wordID=wordIdFrom; wordID<=wordIdTo;wordID++){
			var wordNode = x[wordID];
			var d_pali=getNodeText(wordNode,"real");	
			var d_mean=getNodeText(wordNode,"mean");		
			var d_factors=getNodeText(wordNode,"org");
			var d_fm=getNodeText(wordNode,"om");		
			var d_case = getNodeText(wordNode,"case");
			if(d_pali.length>0 && !(d_mean=="?" && d_factors=="?" && d_fm=="?" && d_case=="?")){
				queryString+="<word>";
				var iPos=d_case.indexOf("#");
				if(iPos>=0){
					var d_type=d_case.substring(0,iPos);
					if(iPos<d_case.length-1){
						var d_gramma=d_case.substring(iPos+1);
					}
					else{
						var d_gramma="";
					}
				}
				else{
					var d_type="";
					var d_gramma=d_case;
				}
				var d_parent="";

				var d_note="";

				var d_confer="";
				var d_status="";
				var d_lock="";
				var d_tag="";
				queryString+="<pali>"+d_pali+"</pali>";
				queryString+="<type>"+d_type+"</type>";
				queryString+="<gramma>"+d_gramma+"</gramma>";
				queryString+="<parent>"+d_parent+"</parent>";
				queryString+="<mean>"+d_mean+"</mean>";
				queryString+="<note>"+d_note+"</note>";
				queryString+="<factors>"+d_factors+"</factors>";
				queryString+="<fm>"+d_fm+"</fm>";
				queryString+="<confer>"+d_confer+"</confer>";
				queryString+="<status>"+d_status+"</status>";
				queryString+="<lock>"+d_lock+"</lock>";
				queryString+="<tag>"+d_tag+"</tag>";
				queryString+="</word>";
			}
		}
		queryString+="</wordlist>";
		dict_wbwUpdataXmlHttp.onreadystatechange=dict_wbwDictUpdata_serverResponse;
		debugOutput("updata user dict start.",0);
		dict_wbwUpdataXmlHttp.open("POST", "./dict_updata_wbw.php", true);
		dict_wbwUpdataXmlHttp.send(queryString);
		

	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function dict_wbwDictUpdata_serverResponse(){
	if (dict_wbwUpdataXmlHttp.readyState==4)// 4 = "loaded"
	{
		debugOutput("server response.",0);
		if (dict_wbwUpdataXmlHttp.status==200)
		{// 200 = "OK"
			var serverText = dict_wbwUpdataXmlHttp.responseText;
			alert(serverText);
			debugOutput(serverText,0);
			
		}
		else
		{
			debugOutput(xmlhttp.statusText,0);
		}
	}
}


function uploadAllWordData(){
	var x = gXmlBookDataBody.getElementsByTagName("word");
	if(x.length>0){
		dict_WbwUpdata(0,x.length-1);
	
	}
	else{
		
	}
}

function setInfoPanalVisibility(){
	document.getElementById("id_info_panal").style.display="block";
}

function removeFormula(inStr){
	if(inStr.indexOf("[")>=0){
		return(inStr);
	}
	pos=0;
	copy=true;
	var output="";
	for(i=0;i<inStr.length;i++){
		if(inStr[i]=="{"){
			copy=false;
		}
		if(copy){
			output+=inStr[i];
		}		
		if(inStr[i]=="}"){
			copy=true;
		}

	}
	return output;
}

function dict_getAllWordList(){
	var output=new Array();
	if(g_findMode=="parent"){
		if(g_dictFindParentLevel==0){
			output.push(g_findWord);
		}
		else{
			var currLevel=g_dictFindParentLevel-1;
			for(i=0;i<g_DictWordList.length;i++){
				if(g_DictWordList[i].ParentLevel==currLevel){
					if(g_DictWordList[i].Parent.length>0 && g_DictWordList[i].Parent!=g_DictWordList[i].Pali){
						var arrList=g_DictWordList[i].Parent.split("$");
						var paliInParent=false;
						for(x=0;x<arrList.length;x++){
							if(arrList[x]==g_DictWordList[i].Pali){
								paliInParent=true;
							}
						}
						if(paliInParent==false){
							output.push(g_DictWordList[i].Parent);
						}
					}
					if(g_DictWordList[i].Factors.length>0){
						arrList=g_DictWordList[i].Factors.split("+");
						for(x=0;x<arrList.length;x++){
							if(arrList[x]!=g_DictWordList[i].Pali){
								output.push(arrList[x]);
							}
						}
					}
				}
			}
		}
	}
	
	if(g_findMode=="children"){
		if(g_dictFindParentLevel==0){
			output.push(g_findWord);
		}
		else{
			var currLevel=g_dictFindParentLevel+1;
			for(i=0;i<g_DictWordList.length;i++){
				if(g_DictWordList[i].ParentLevel==currLevel){
					if(g_DictWordList[i].Type==".v:base." || g_DictWordList[i].Type==".root.")
					{
						var paliInList=false;
						for(x=0;x<output.length;x++){
							if(output[x]==g_DictWordList[i].Pali){
								paliInList=true;
							}
						}
						if(paliInList==false){
							output.push(g_DictWordList[i].Pali);
						}
					}
				}
			}
		}
	}
	
	if(output.length>0){
		return output.join("$");
	}
	else{
		return null;
	}
}


function showChildrenList(arrChildren){
	
	var outChildrenString="";
	var arrChildrenList = new Array();
	g_currShowDeep++;
	if(g_currShowDeep>3){
		g_currShowDeep=g_currShowDeep-1;
		return("");
	}
	
	outChildrenString+="<div class=\"subnote\">";
	outChildrenString+="<ol>";
	for(iChild in arrChildren){

					outChildrenString+="<li>";
					newChild = getChildrenList(arrChildren[iChild]);
					if(newChild.length>0){
						outChildrenString+="<button type='button' onclick='showSubNode(this)'>+</button>";
					}					
					outChildrenString+=g_DictWordList[arrChildren[iChild]].Pali;
					outChildrenString+='['+g_DictWordList[arrChildren[iChild]].Type+"@"+g_DictWordList[arrChildren[iChild]].Gramma+"]"+g_DictWordList[arrChildren[iChild]].Mean+"["+g_DictWordList[arrChildren[iChild]].Factors+"]";
					if(newChild.length>0){
						outChildrenString+=showChildrenList(newChild);
					}
					outChildrenString+="</li>";
	}
	outChildrenString+="</ol>";
	outChildrenString+="</div>";
	
	g_currShowDeep--;
	return(outChildrenString);
	
}
function preOpParentInfo(){
	for(iDict=0;iDict<g_DictWordList.length;iDict++){
		var arrParentFactor=g_DictWordList[iDict].Factors.split("+");
		if(g_DictWordList[iDict].Type==".root."){
			for(iSub=0;iSub<g_DictWordList.length;iSub++){
				if(iSub!=iDict && g_DictWordList[iSub].Parent.length==0 && g_DictWordList[iSub].Type==".v:base."){
					var arrFactor=g_DictWordList[iSub].Factors.split("+");
					if(arrFactor.length>2){
						if(arrFactor[arrFactor.length-3]==g_DictWordList[iDict].Pali && arrFactor[arrFactor.length-2]!="ṇe"){
							g_DictWordList[iSub].Parent=g_DictWordList[iDict].Pali;
						}
					}
				}
			}
		}
		
		if(g_DictWordList[iDict].Type==".v:base."){
			var arrHead=arrParentFactor;
			arrHead.pop();
			var sHead=arrHead.join("+");
			for(iSub=0;iSub<g_DictWordList.length;iSub++){
				if(iSub!=iDict && g_DictWordList[iSub].Parent.length==0){
					{
						if(g_DictWordList[iSub].Factors.indexOf(sHead)==0){
							g_DictWordList[iSub].Parent=g_DictWordList[iDict].Pali;
						}
					}
				}
			}
		}

	}
	
	document.getElementById('id_dict_match_result_inner').innerHTML=dictShowAsTable();
}
function getChildrenList(wordId){
	var arrChildList = new Array();
	if(g_DictWordList[wordId].Type==".n." || g_DictWordList[wordId].Type==".adj." || g_DictWordList[wordId].Type==".ti." || g_DictWordList[wordId].Type==".v."){
		return(arrChildList);
	}
			var pos=g_DictWordList[wordId].Factors.lastIndexOf("+");
			var head=g_DictWordList[wordId].Factors.substring(0,pos);
			var end=g_DictWordList[wordId].Factors.substring(pos);
	for(iDict=0;iDict<g_DictWordList.length;iDict++){
		/*
			var isSameHead=false;
			if(end=="+ti"){
				if(g_DictWordList[iDict].Factors.indexOf(head)==0){
					isSameHead=true;
				}
			}	
		*/
		if(g_DictWordList[wordId].Pali==g_DictWordList[iDict].Parent)// || isSameHead==true || g_DictWordList[iDict].Factors.indexOf("+"+g_DictWordList[wordId].Pali+"+")>0 || g_DictWordList[iDict].Factors.indexOf(g_DictWordList[wordId].Pali+"+")==0)
		{


			{
				thisChildPali=g_DictWordList[iDict].Pali;
				thisChildType=g_DictWordList[iDict].Type;
				if(thisChildPali==g_DictWordList[wordId].Pali && g_DictWordList[iDict].Type==g_DictWordList[wordId].Type){
				}
				else
				{
					isFind=false;
					for(iWord in arrChildList){
						if(g_DictWordList[arrChildList[iWord]].Pali==thisChildPali && g_DictWordList[arrChildList[iWord]].Type==thisChildType){
							isFind=true;
						}
					}
					if(!isFind){
						arrChildList.push(iDict);
					}
				}
			}
		}
	}
	return(arrChildList);
}

function showParentList(arrParent){
	var outChildrenString="";
	var arrParentList = new Array();
	g_currShowDeep++;
	if(g_currShowDeep>3){
		g_currShowDeep=g_currShowDeep-1;
		return("");
	}
	

	outChildrenString+="<ol>";
	for(iParent in arrParent){

					outChildrenString+="<li>";
					outChildrenString+=arrParent[iParent];
					newParent = getParentList(arrParent[iParent],"base");
					outChildrenString+=newParent.length;
					if(newParent.length>0){
						outChildrenString+=showParentList(newParent);
					}
					outChildrenString+="</li>";



	}
	outChildrenString+="</ol>";
	
	g_currShowDeep--;
	return(outChildrenString);
}

function getParentList(word,findIn){
	var arrParentList = new Array();
	for(iDict=0;iDict<g_DictWordList.length;iDict++){
		if(word==g_DictWordList[iDict].Pali){
			if(findIn=="base"){
				if(g_DictWordList[iDict].Type.slice(-5)=="base."){
					thisParent=g_DictWordList[iDict].Parent;
					if(thisParent.length>0){
						isFind=false;
						for(iWord in arrParentList){
							if(arrParentList[iWord]==thisParent){
								isFind=true;
							}
						}
						if(!isFind){
							arrParentList.push(thisParent);
						}
					}
				}
			}
			else{
				thisParent=g_DictWordList[iDict].Parent;
				if(thisParent.length>0){
					isFind=false;
					for(iWord in arrParentList){
						if(arrParentList[iWord]==thisParent){
							isFind=true;
						}
					}
					if(!isFind){
						arrParentList.push(thisParent);
					}
				}
			}
		}
	}
	
	return(arrParentList);
}

function dictShowCurrWordList(){
	preOpParentInfo();
	var strWord="";
	strWord+="<h2>"+g_findWord+"</h2>";
	strWord+="<p>select a word</p>";
	for(iDict in g_DictWordList){
		if(g_findWord==g_DictWordList[iDict].Pali){
			strWord+="<p><a onclick='dictFindShow("+iDict+")'>"+g_DictWordList[iDict].Pali+"</a>["+g_DictWordList[iDict].Type+"@"+g_DictWordList[iDict].Gramma+"]"+g_DictWordList[iDict].Mean+"["+g_DictWordList[iDict].Factors;
		}
	}
	document.getElementById('id_dict_curr_word_inner').innerHTML=strWord;
}


//show dict find result
function dictFindShow(showId){
	var strWordFamily="";
	strWordFamily+="<h2>"+g_findWord+"</h2>";
	strWordFamily+='<p>['+g_DictWordList[showId].Type+"@"+g_DictWordList[showId].Gramma+"]"+g_DictWordList[showId].Mean+"["+g_DictWordList[showId].Factors+"]</p>";
	if(g_findMode=="parent"){
		pList = getParentList(g_findWord,"");
		if(pList.length>0){
		strWordFamily+=showParentList(pList);
		}
	}
	if(g_findMode=="children"){
		cList = getChildrenList(showId);
		if(cList.length>0){
		strWordFamily+=showChildrenList(cList); 
		}
	}
	document.getElementById('id_dict_curr_word_inner').innerHTML=strWordFamily;
}

function showSubNode(obj){
	eParent = obj.parentNode;
	var x=eParent.getElementsByTagName("div");
	if(x[0].style.display=="none"){
		x[0].style.display="block";
		obj.innerHTML="-";
	}
	else{
		x[0].style.display="none";
		obj.innerHTML="+";
	}
	
}