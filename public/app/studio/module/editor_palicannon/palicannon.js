
var gCurrSelectedBook;
var gCurrParBegin;
var gCurrParEnd;
var gCurrLoadPar;
var gLoadSteamCanceled=false;

var strC1;
var strC2;
var strC3;
var strC4;
var strBookTitle;
var strBookFolder;

var loadSteamBeginTime="";

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode
 *
 * Advanced example:
 * @code
 * @endcode
 */
function palicannon_init(){
}

	/*
	 * When Select Changed , updata select object.
	 *
	 * @param obj (object) - changed object
	 * @return none
	 */

function pc_pushNewToList(inArray,strNew){
	//var isExist=false;
	for(x in inArray){
		if(inArray[x]==strNew){
			return;
		}
	}
	inArray.push(strNew);
}


var palicannon_xmlhttp;
function palicannon_show_filelist(strBook){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		palicannon_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		palicannon_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	gCurrSelectedBook=strBook;
	var d=new Date();
	palicannon_xmlhttp.onreadystatechange=palicannon_serverResponse;
	palicannon_xmlhttp.open("GET","./pc_get_book_index.php?t="+d.getTime()+"&book="+strBook,true);
	palicannon_xmlhttp.send();
}

function palicannon_serverResponse(){
	if (palicannon_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (palicannon_xmlhttp.status==200)
		{// 200 = "OK"

			var parList="";
			var xmlText = palicannon_xmlhttp.responseText;
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlBookIndex=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlBookIndex=new ActiveXObject("Microsoft.XMLDOM");
				  xmlBookIndex.async="false";
				  xmlBookIndex.loadXML(xmlText);
				  }
			  
				if (xmlBookIndex == null){
					alert("error:can not load book index.");
					return;
				}
				gXmlParIndex = xmlBookIndex.getElementsByTagName("paragraph");
			document.getElementById('id_palicannon_index_filelist').innerHTML=renderBookToc();
			//removeNavTreeButton();
		}
		else
		{
			document.getElementById('id_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}

function palicannon_showParInfo(parBeginIndex){
	var outPut="";
	var begin=parBeginIndex;
	var end=-1;
	parBeginLevel=getNodeText(gXmlParIndex[parBeginIndex],"level");
	for(i=begin+1;i<gXmlParIndex.length;i++){
		var iLevel=getNodeText(gXmlParIndex[i],"level");
		if(iLevel==parBeginLevel){
			end = i-1;
			break;
		}
	}
	if(end==-1){
		end=gXmlParIndex.length-1;
	}
	outPut +="begin:"+(begin+1)+" end:"+(end+1)+"<br>"
	outPut += "<button type=\"button\" onclick=\"palicannon_loadStream('"+gCurrSelectedBook+"',"+(begin+1)+","+(end+1)+")\">Load</button>"
	outPut += "<button type=\"button\" onclick=\"palicannon_loadStreamCancel()\">Cancel</button>"
	document.getElementById('id_palicannon_selected_par_info').innerHTML=outPut;
}

function palicannon_loadStreamCancel(){
	gLoadSteamCanceled=true;
}

/*
 * load paragraph from database
 * public
 * @param book(string)  book GUID
 * @param parBegin(int) 
 * @param parEnd(int)
 */
function palicannon_loadStream(book,parBegin,parEnd){
	document.getElementById("wizard_div").style.display="none";
	
	gCurrParBegin=parBegin;
	gCurrParEnd=parEnd;
	gCurrLoadPar=parBegin;
	gLoadSteamCanceled=false;
	//清空单词节点数组
	gXmlAllWordInWBW = new Array();
	
	var d=new Date();
	loadSteamBeginTime=d.getTime()
	
	document.getElementById("modifyDiv").appendChild(document.getElementById("modifywin"));
	insertTocToXmlBookHead(parBegin,parEnd);

	palicannon_load_book_par(book,gCurrLoadPar,"vri");
}

var palicannon_xmlLoadBookhttp;
function palicannon_load_book_par(strBook,parIndex,strVer){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		palicannon_xmlLoadBookhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		palicannon_xmlLoadBookhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
	palicannon_xmlLoadBookhttp.onreadystatechange=palicannon_load_book_serverResponse;
	palicannon_xmlLoadBookhttp.open("GET","./pc_get_book_xml.php?t="+d.getTime()+"&book="+strBook+"&paragraph="+parIndex+"&ver="+strVer,true);
	palicannon_xmlLoadBookhttp.send();
}

function palicannon_load_book_serverResponse(){
	if (palicannon_xmlLoadBookhttp.readyState==4)// 4 = "loaded"
	{
		if (palicannon_xmlLoadBookhttp.status==200)
		{// 200 = "OK"
			var xmlText = palicannon_xmlLoadBookhttp.responseText;
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlBookPar=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlBookPar=new ActiveXObject("Microsoft.XMLDOM");
				  xmlBookPar.async="false";
				  xmlBookPar.loadXML(xmlText);
				  }
			  
				if (xmlBookPar == null){
					alert("error:can not load book index.");
					return;
				}
				xmlParBlocks = xmlBookPar.getElementsByTagName("block");
			for(iXml=0;iXml<xmlParBlocks.length;iXml++){
				insertBlockToHtml(xmlParBlocks[iXml])
				insertBlockToXmlBookData(xmlParBlocks[iXml])
			}
			
			if(gLoadSteamCanceled){
				var_dump("load cancel");
				refreshResource();
				updataToc()
			}
			else{
				var d=new Date();
				loadSteamCurrTime=d.getTime()
				passTime=(loadSteamCurrTime-loadSteamBeginTime)/1000;
				gCurrLoadPar++;
				if(gCurrLoadPar<gCurrParEnd){
					progress=(gCurrLoadPar-gCurrParBegin)/(gCurrParEnd-gCurrParBegin)
					remainTime=(passTime/progress)-passTime;
					document.getElementById('id_book_load_progress').innerHTML=(progress*100).toFixed(1)+"%<br />pass:"+passTime.toFixed(1)+"s remain: "+remainTime.toFixed(1)+"s";
					palicannon_load_book_par(gCurrSelectedBook,gCurrLoadPar,"vri");
				}
				else{
					document.getElementById('id_book_load_progress').innerHTML="Load Finished<br />耗时:"+passTime.toFixed(1)+" s";
					var_dump("doc load finished");
					refreshResource();
					updataToc()
				}
			}
		}
		else
		{
			document.getElementById('id_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}

//将目录转为目录数据包 插入文档
function insertTocToXmlBookHead(from,to){
	count=0;
	var newBlockString="<root><block><info></info><data></data></block></root>"
	if (window.DOMParser){
		parser=new DOMParser();
		newXmlBlock=parser.parseFromString(newBlockString,"text/xml");
	}
	else{ // Internet Explorer
		newXmlBlock=new ActiveXObject("Microsoft.XMLDOM");
		newXmlBlock.async="false";
		newXmlBlock.loadXML(newBlockString);
	}
			  
	if (newXmlBlock == null){
		alert("error:can not load book index.");
			return;
	}
	
	for(var i=from;i<=to;i++){
		var bookId = getNodeText(gXmlParIndex[i-1],"book");
		var parId = getNodeText(gXmlParIndex[i-1],"par");
		var level = getNodeText(gXmlParIndex[i-1],"level");
		
		var strClass = getNodeText(gXmlParIndex[i-1],"class");
		var titleLangauge="en";
		var titleAuthor="unkow";	
		titleLanguage=getNodeText(gXmlParIndex[i-1],"language");
		titleAuthor=getNodeText(gXmlParIndex[i-1],"author");
		var strTitle = getNodeText(gXmlParIndex[i-1],"title");

		cloneBlock=newXmlBlock.cloneNode(true)
		newBlock=cloneBlock.getElementsByTagName("block")[0]
		xmlNewInfo = newBlock.getElementsByTagName("info")[0];
		xmlNewData = newBlock.getElementsByTagName("data")[0];
		setNodeText(xmlNewInfo,"type","heading");
		setNodeText(xmlNewInfo,"paragraph",parId);
		setNodeText(xmlNewInfo,"book",bookId);
		setNodeText(xmlNewInfo,"author",titleAuthor);
		setNodeText(xmlNewInfo,"language",titleLanguage);
		setNodeText(xmlNewInfo,"level",level);
		setNodeText(xmlNewInfo,"style",strClass);	
		setNodeText(xmlNewInfo,"edition",'1');
		setNodeText(xmlNewInfo,"subver",'0');
		setNodeText(xmlNewInfo,"id",com_guid());
		setNodeText(xmlNewData,"text",strTitle);
		
		insertBlockToHtml(newBlock)
		gXmlBookDataBody.appendChild(newBlock);

		count++;
	}
}

//新建目录项目 插入文档
function newHeadBlock(headingInfo){
	count=0;
	var newBlockString="<root><block><info></info><data></data></block></root>"
	if (window.DOMParser){
		parser=new DOMParser();
		newXmlBlock=parser.parseFromString(newBlockString,"text/xml");
	}
	else{ // Internet Explorer
		newXmlBlock=new ActiveXObject("Microsoft.XMLDOM");
		newXmlBlock.async="false";
		newXmlBlock.loadXML(newBlockString);
	}
			  
	if (newXmlBlock == null){
		alert("error:can not load book index.");
			return;
	}
	
		cloneBlock=newXmlBlock.cloneNode(true)
		newBlock=cloneBlock.getElementsByTagName("block")[0]
		xmlNewInfo = newBlock.getElementsByTagName("info")[0];
		xmlNewData = newBlock.getElementsByTagName("data")[0];
		setNodeText(xmlNewInfo,"type","heading");
		setNodeText(xmlNewInfo,"paragraph",headingInfo.paragraph);
		setNodeText(xmlNewInfo,"book",headingInfo.book);
		setNodeText(xmlNewInfo,"author",headingInfo.author);
		setNodeText(xmlNewInfo,"language",headingInfo.language);
		setNodeText(xmlNewInfo,"edition",'1');
		setNodeText(xmlNewInfo,"subedition",'0');
		setNodeText(xmlNewInfo,"level",headingInfo.level);
		setNodeText(xmlNewInfo,"id",com_guid());
		setNodeText(xmlNewData,"text",headingInfo.text);
		
		insertBlockToHtml(newBlock)
		gXmlBookDataBody.appendChild(newBlock);

}

function palicannon_nav_level_show(showLevel){
	getStyleClass('palicannon_nav_level_1').style.display="none";
	getStyleClass('palicannon_nav_level_2').style.display="none";
	getStyleClass('palicannon_nav_level_3').style.display="none";

	switch(showLevel){
		case 3:
			getStyleClass('palicannon_nav_level_3').style.display="block";
			getStyleClass('tree_expand_3').style.display="none";
			getStyleClass('tree_collapse_3').style.display="inline";
		case 2:
			getStyleClass('palicannon_nav_level_2').style.display="block";
			getStyleClass('tree_expand_2').style.display="none";
			getStyleClass('tree_collapse_2').style.display="inline";			
		case 1:
			getStyleClass('palicannon_nav_level_1').style.display="block";
			getStyleClass('tree_expand_1').style.display="inline";
			getStyleClass('tree_collapse_1').style.display="none";
	}
	
	switch(showLevel){
		case 1:
		getStyleClass('tree_expand_1').style.display="none";
		getStyleClass('tree_collapse_1').style.display="inline";
		break;
		case 2:
		getStyleClass('tree_expand_2').style.display="none";
		getStyleClass('tree_collapse_2').style.display="inline";
		break;
		case 3:
		getStyleClass('tree_expand_3').style.display="none";
		getStyleClass('tree_collapse_3').style.display="inline";
		break;
	}

}

function removeNavTreeButton(){
	for(i=0;i<gXmlParIndex.length;i++){
		obj=document.getElementById("id_pc_nav_par_"+i)
		if(obj){
		if(obj.getElementsByTagName("ul").length==0){
			document.getElementById('id_pc_nav_co_'+i).style.display="none";
			document.getElementById('id_pc_nav_ex_'+i).style.display="none";
		}
		}
	}
}

function renderBookToc(){
	var output="<ul>";
	for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
		parTitle=getNodeText(gXmlParIndex[iPar],"title");
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		if(parHeadingLevel>0){
			output += "<li id=\"id_pc_nav_par_"+iPar+"\" class=\"palicannon_nav_level_" + parHeadingLevel + "\">";
			output += "<span id=\"id_pc_nav_ex_"+iPar+"\" class=\"tree_expand_"+parHeadingLevel+"\">▼</span><span id=\"id_pc_nav_co_"+iPar+"\" class=\"tree_collapse_"+parHeadingLevel+"\">▶</span>";			
			output += "<a onclick=\"palicannon_showParInfo("+iPar+")\">" + parTitle +"</a>";
			output += "</li>"
		}
	}
	output+="</ul>";
	return output;
}

function editor_palicanon_bookmark_goto(){
	window.location.assign("#"+document.getElementById('editor_palicanon_goto').value)
}

function pc_show_res_selecter(visible){
	document.getElementById('wizard_div_palicannon').style.display = (visible ? 'block' : 'none');
}

