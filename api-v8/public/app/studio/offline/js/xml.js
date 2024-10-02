
var nWord=0;
var xmlDoc = null;
var suttaWordList=new Array();
var g_filename="";
var g_useMode;

function getNodeText(inNode,subTagName){
	try{
		if(inNode.getElementsByTagName(subTagName).length>0)
		{
			if(inNode.getElementsByTagName(subTagName)[0].childNodes.length>0){
				return(inNode.getElementsByTagName(subTagName)[0].childNodes[0].nodeValue); 
			}
		}
		
	}
	catch(error){
		var_dump(error);
		return("");
	}
	return("");
}

function setNodeText(inNode,subTagName,strValue){
	try{
		if(inNode.getElementsByTagName(subTagName).length>0)
		{
			if(inNode.getElementsByTagName(subTagName)[0].childNodes.length>0){
				inNode.getElementsByTagName(subTagName)[0].childNodes[0].nodeValue=strValue;
			}
			else{
				throw "can't accese text node";
			}
		}
		else{
			throw subTagName+ ":not a sub Taget";
		}
	}
	catch(error){
		var_dump(error);
		return(false);
	}
	return(true);			
}

//根据xmlDocument 对象中的单词序号和单词节点创建单词块
//返回 字符串
function createWordBlockByNode(id,wordNode){
}
//根据xmlDocument 对象中的单词序号修改单词块（不含Pali）
//返回 无	
function modifyWordDetailByWordId(wordId){
	try{
	var sDetail="detail"+wordId;
	var cDetail = document.getElementById(sDetail);
	if(cDetail!=null){
		var x = xmlDoc.getElementsByTagName("word");
		cDetail.innerHTML = makeWordDetailFromNode(x[wordId]);
	}
	}
	catch(error){
		var_dump(error);
	}
}
//根据xmlDocument 对象中的单词序号返回单词块字符串（不含Pali）
//返回 字符串		
function makeWordDetailFromNode(wordNode){
	sId = getNodeText(wordNode,"id");
	sOrg = getNodeText(wordNode,"org");
	sMean = getNodeText(wordNode,"mean");
	sCase = getNodeText(wordNode,"case");
	return(makeWordDetailByValue(sId,sOrg,sMean,sCase));
}

//根据xmlDocument 对象中的单词序号返回单词块字符串（不含Pali）
//返回 字符串		
function makeWordDetailByValue(inId,inOrg,inMean,inCase){
	var _txtOutDetail="";
	var _bgColor="";
	var _caseColor="";

	if(inMean=='?'){
		_bgColor=" style='background-color:#EEE' "
	}

	if(inMean.length>2 && inMean.substr(0,1)=='?'){
		_bgColor=" class='bookmarkcolor"+inMean.substr(1,1)+"' ";
		inMean = inMean.substring(3);
	}
	
	if(inCase=='?' && _bgColor==""){
		_caseColor=" style='background-color:#EEE' "
	}
	if(g_useMode=="read" || g_useMode=="chanting"){
		_bgColor="";
		_caseColor="";
		if(inOrg=="?"){inOrg="&nbsp;";}
		if(inMean=="?"){inMean="&nbsp;";}
		if(inCase=="?"){inCase="";}

	}
	
	_txtOutDetail = _txtOutDetail + "<div "+_bgColor+">";
	_txtOutDetail = _txtOutDetail + "<p class='ID'>";
	_txtOutDetail = _txtOutDetail + inId; 
	_txtOutDetail = _txtOutDetail + "</p>"; 	
	_txtOutDetail = _txtOutDetail + "<p class='org' name='w_org'>";
	_txtOutDetail = _txtOutDetail +  inOrg;
	_txtOutDetail = _txtOutDetail + "</p> ";		
	_txtOutDetail = _txtOutDetail + "<p class='mean'>";
	_txtOutDetail = _txtOutDetail + inMean;
	_txtOutDetail = _txtOutDetail + "</p> ";
	_txtOutDetail = _txtOutDetail + "<p class='case' "+_caseColor+">";
	//_txtOutDetail = _txtOutDetail + inCase;

	_sItem = new Array();
	_sItem = inCase.split("%");
	for(iItem=0;iItem<_sItem.length;iItem++){
		_sItem2 = new Array();
		_sItem2 = _sItem[iItem].split(",");
		if(_sItem2.length>1){
			_txtOutDetail = _txtOutDetail + "<span class='cell2'><a href='#'>"+_sItem2[0]+"<a></span>";
			_txtOutDetail = _txtOutDetail + "<span id=\"casew"+i+"\" class='hidden'>"+_sItem2[0]+"</span>";
		}
		else //单个值背景绿色
		{
			if(inCase==''){
				_txtOutDetail = _txtOutDetail + "&nbsp;";
			}
			else if(inCase=='?'){
			_txtOutDetail = _txtOutDetail + _sItem[iItem];
			}
			else{
			_txtOutDetail = _txtOutDetail + "<span class='cell'>"+_sItem[iItem]+"</span>";
			}
		}
	}

	_txtOutDetail = _txtOutDetail + "</p>";
	_txtOutDetail = _txtOutDetail + "</div>";

	return(_txtOutDetail);
}

//确认对单个词的修改
function modifyApply(sWordId){
	var bApplyAll = document.getElementById("B_Apply_All").checked;
	var eWin = document.getElementById("modifywin");
	eWin.style.display="none";
	var sDetail="detail"+sWordId;
	var cDetail = document.getElementById(sDetail);
	cDetail.style.display="block";
	
	sOrg = document.getElementById("input_org").value;
	sMeaning = document.getElementById("input_meaning").value;
	sCase = document.getElementById("input_case").value;

	var x = xmlDoc.getElementsByTagName("word");
	x[sWordId].getElementsByTagName("org")[0].childNodes[0].nodeValue = sOrg;
	x[sWordId].getElementsByTagName("mean")[0].childNodes[0].nodeValue = sMeaning;
	x[sWordId].getElementsByTagName("case")[0].childNodes[0].nodeValue = sCase;
	
	modifyWordDetailByWordId(sWordId);
	
	//apply all
	if(bApplyAll){
		sPaliWord = x[sWordId].getElementsByTagName("pali")[0].childNodes[0].nodeValue;
		var iSameWordCount = 0;
		for (i=0;i<x.length;i++){
			xmlNotePali = x[i].getElementsByTagName("pali")[0].childNodes[0].nodeValue;
			if(xmlNotePali.toLowerCase()==sPaliWord.toLowerCase()){						
				x[i].getElementsByTagName("org")[0].childNodes[0].nodeValue = sOrg;
				x[i].getElementsByTagName("mean")[0].childNodes[0].nodeValue = sMeaning;
				x[i].getElementsByTagName("case")[0].childNodes[0].nodeValue = sCase;
				
				modifyWordDetailByWordId(i);
				iSameWordCount = iSameWordCount+1;
			}
		}
		var_dump("same word:"+(iSameWordCount-1));
	}
	refreshBookMark();
}

//取消对单个词的修改
function modifyCancel(sWordId){
	var eWin = document.getElementById("modifywin");
	eWin.style.display="none";
	var sDetail="detail"+sWordId;
	document.getElementById(sDetail).style.display="block";
}

//显示修改单个词的窗口
function showModifyWin(sWordId){
	var xAllWord = xmlDoc.getElementsByTagName("word");	
	var wid=sWordId;
	var tWin="";
	var eWord=document.getElementById("wb"+sWordId);
	var eWin = document.getElementById("modifywin");
	
	var sOrg = xAllWord[wid].getElementsByTagName("org")[0].childNodes[0].nodeValue;
	var sMeaning = xAllWord[wid].getElementsByTagName("mean")[0].childNodes[0].nodeValue;
	var cCase = eWord.getElementsByTagName("p")[4].getElementsByTagName("span");
	var sCase = xAllWord[wid].getElementsByTagName("case")[0].childNodes[0].nodeValue;
	if(g_useMode=="edit"){
		tWin=tWin+"<p><input type=\"text\" id=\"input_org\" value=\""+sOrg+"\" name=\"in_org\"/></p>";
		tWin=tWin+"<p><input type=\"text\" id=\"input_meaning\" value=\""+sMeaning+"\" name=\"in_meaning\"  /></p>";
		tWin=tWin+"<p><input type=\"text\" id=\"input_case\" value=\""+sCase+"\" name=\"in_case\" /></p>";
		tWin=tWin+"<input id=\"B_Apply_All\" type=\"checkbox\"  />Apply to same words";
		tWin=tWin+"<div class=\"modifybutton\">";
		tWin=tWin+"<p class=\"modify_left\" align=\"center\"><a id=\"modify_ok\" onclick=\"modifyApply('"+sWordId+"')\">Apply</a></p>";
		tWin=tWin+"<p class=\"modify_right\" align=\"center\"><a id=\"modify_cancel\" onclick=\"modifyCancel('"+sWordId+"')\">cancel</a></p>";
		tWin=tWin+"</div>";
		eWin.innerHTML=tWin;
		eWin.style.display="block";
		eWord.appendChild(eWin);
		var sDetail="detail"+sWordId;
		document.getElementById(sDetail).style.display="none";
	}	

}


//用单词表中的一个记录更改经文中的单词
function updataWord(id)
{
	var debugstr;		
	try{
	var_dump(suttaWordList[id].insuttaid);
	sOrg = document.getElementById("wlorg"+id).value;
	sMean = document.getElementById("wlmean"+id).value;
	sCase = document.getElementById("wlcase"+id).value;
	
	var m_WordIdList=new Array();
	m_WordIdList = suttaWordList[id].insuttaid.split(",");
	var xAllWord = xmlDoc.getElementsByTagName("word");	
	//alert(xAllWord.length);

	for(i=0;i<m_WordIdList.length;i++){
		//将修改结果保存到xml DOM中
		xAllWord[m_WordIdList[i]].getElementsByTagName("org")[0].childNodes[0].nodeValue=sOrg;
		xAllWord[m_WordIdList[i]].getElementsByTagName("mean")[0].childNodes[0].nodeValue=sMean;
		xAllWord[m_WordIdList[i]].getElementsByTagName("case")[0].childNodes[0].nodeValue=sCase;
		var sId = xAllWord[m_WordIdList[i]].getElementsByTagName("id")[0].childNodes[0].nodeValue;
		var wordDetail = makeWordDetailByValue(sId,sOrg,sMean,sCase);

		//modifyWordDetailByWordId(m_WordIdList[i]);
		var strDetailName="detail" + m_WordIdList[i];
		document.getElementById(strDetailName).innerHTML = wordDetail;

	}
	var_dump("成功修改"+m_WordIdList.length+"词");
	}
	catch(e){
	var_dump(e);
	}

}

//比较两个词是否一样
function compareWordInList(word1,word2)
{
	var sItems1=new Array();
	sItem1 = word1.split(";");
	var sItems2=new Array();
	sItem2 = word2.split(";");
	var sConcat1=sItem1[0]+sItem1[1]+sItem1[2]+sItem1[3];
	var sConcat2=sItem2[0]+sItem1[1]+sItem1[2]+sItem1[3];
	if(sConcat1==sConcat2){
		return(true);
	}
	else{
		return(false);
	}
}
//生成单词列表
function makeWordList()
{
	var sPali = "";	
	var sOrg = "";
	var sMean = "";
	var sCase ="";
	var arrCombinWord=new Array();
	
	var arrCount=new Array();
	var iCount = 0;

	var sTableWordList = "<table border='0' cellpadding='3' ><tr  class='h'><th>序号</th><th>计数</th><th>原文</th><th>原型</th><th>译文</th><th>语法</th><th><input type='button' value='A'/></th><th><input type='button' value='Apply All'/></th></tr>";
	//提取所有词
	var xAllWord = xmlDoc.getElementsByTagName("word");	
	if(xAllWord.length==0){
		return("no word data.");
	}
	var outWordList="";
	for(iword=0;iword<xAllWord.length;iword++)
	{
		sPali = "";	
		sOrg = "";
		sMean = "";
		sCase ="";
		
		sPali = getNodeText(xAllWord[iword],"pali");
		sOrg = getNodeText(xAllWord[iword],"org");
		sMean = getNodeText(xAllWord[iword],"mean");
		sCase = getNodeText(xAllWord[iword],"case");

		sConcat = sPali.toLowerCase() +";"+ sOrg +";"+ sMean +";"+ sCase + ";" + iword;
		arrCombinWord[iword] = sConcat;

	}
	arrCombinWord.sort();
	arrCombinWord.reverse();
	var sLastWord = arrCombinWord[0];
	var iLastWord = 0;
	var iCountSameWord=1;
	var iCount=0;
	var sWordId=arrCombinWord[0].split(";")[4];
	for(iword=1;iword<xAllWord.length;iword++)
	{

		if(compareWordInList(arrCombinWord[iword],sLastWord)){
			iCountSameWord++;
			sWordId = sWordId + "," + arrCombinWord[iword].split(";")[4];
		}
		else{		
			var sItems=new Array();
			sItem = arrCombinWord[iLastWord].split(";");
			objWord=new Object();
			objWord.pali=sItem[0];
			objWord.org = sItem[1];
			objWord.mean = sItem[2];
			objWord.gramma = sItem[3];
			objWord.insuttaid = sWordId;
			objWord.newid = iCount;
			suttaWordList[iCount]=objWord;
			sTableWordList = sTableWordList + "<tr><td>"+iCount+"</td><td id='tablepali"+iCount+"'>"+iCountSameWord+"</td><td>" +sItem[0] + "</td>";
			sTableWordList = sTableWordList + "<td><input id=\"wlorg"+iCount+"\" value = '" + sItem[1] + "' /></td>";
			sTableWordList = sTableWordList + "<td><input id=\"wlmean"+iCount+"\"value = '" + sItem[2] + "' /></td>";
			sTableWordList = sTableWordList + "<td><input id=\"wlcase"+iCount+"\"value = '" + sItem[3] + "' /></td><td><input type='button' value='Fill'/></td><td><input onclick=\"updataWord('"+iCount+"')\" type='button' value='Apply'/></td></tr>";
			sLastWord=arrCombinWord[iword];
			iLastWord = iword;
			sWordId = arrCombinWord[iword].split(";")[4];
			iCountSameWord=1;
			iCount++;
		}
	}
			
	sTableWordList = sTableWordList + "</table>";
	return(sTableWordList);
}

function refreshWordList(){
	document.getElementById("word_table_inner").innerHTML = makeWordList();
}

//load xml
function loadxml(strFileName) {

	var txtOut="";
	var tContent="";
	var strNote="<ul>";
	var iWordCount=0;/*整个网页所有单词计数器*/
	var aNote=new Array();

	var arrDictWordList= new Array();
	var aDict=new Array();
	var iDictCount=0;
	var iInDict=0;
	/*
	此代码不支持ie9 及以上版本 所以放弃
	// in ie7 ie8 firefox no server can be use!!
            if (window.ActiveXObject) {// code for IE
                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
            }
            else if (document.implementation.createDocument) {// code for Mozilla, Firefox, Opera, etc.
                xmlDoc = document.implementation.createDocument("", "", null);
            }
            else {
                alert('Your browser cannot handle this script');
            }
	 if (xmlDoc != null) {
                xmlDoc.async = false;
                try {
                    xmlDoc.load("books/sutta.xml");
			}
	}
	*/
	
	/*
	if (window.DOMParser) {
	  // code for modern browsers
		parser = new DOMParser();
		xmlDoc = parser.parseFromString(text,"text/xml");
	} else {
	  // code for old IE browsers
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
	} 
	*/
	
	xmlHttp=null;
	if (window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		xmlHttp=new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{// code for IE6, IE5
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (xmlHttp!=null)
	{
		var d=new Date();
		xmlHttp.open("GET", strFileName+"?t="+d.getTime(), false);
		xmlHttp.send(null);
		xmlDoc=xmlHttp.responseXML;
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	if (xmlDoc == null){
		alert("error:can not open xml file.");
		return;
	}
    try {
            var x = xmlDoc.getElementsByTagName("sutta");
			if(x.length==0){
				alert('error: xml file data error, can not found out sutta data-block.');
				return;
			}
			//提取已经填好的词作为字典
			var xDict = xmlDoc.getElementsByTagName("word");					
			var tOut="";
			var sDictPali="";
			var sDictId="";
			var sDictOrg="";
			var sDictMean="";
			var sDictCase="";
			for(iword=0;iword<xDict.length;iword++)
			{
				var objDictItem=new Object();/*一个字典元素*/
				objDictItem.Pali = getNodeText(xDict[iword],"pali");
				objDictItem.Org = getNodeText(xDict[iword],"org");
				objDictItem.Mean = getNodeText(xDict[iword],"mean");
				objDictItem.Case = getNodeText(xDict[iword],"case");

				if(objDictItem.Mean!="?" && objDictItem.Mean!="??"){
					arrDictWordList[iDictCount]=objDictItem;
					iDictCount++;
					tOut += (sDictPali+":"+sDictMean+"$");
				}

			}
			//dict end
			
			/*遍历所有经*/
			for (i=0;i<x.length;i++)
			  {
				//load noet
				//var xNote = x[i].getElementsByTagName("note");
				
				//for (iNote=0;i<xNote.length;iNote++){
					//aNote[iNote]=xNote[iNote].getAttribute("anchor_id");
				//}
				
				tSuttaTitle=x[i].getAttribute("id");
				txtOut = txtOut + "<div class='sutta' id='"+tSuttaTitle+"'>\n";
				xTitle = x[i].getElementsByTagName("title");
				txtOut = txtOut + "<div class='sutta_title'>\n";
				var cPaliTitle = getNodeText(xTitle[0],"pali"); /*xTitle[0].getElementsByTagName("pali")[0].childNodes[0].nodeValue;*/
				var cTranEn = getNodeText(xTitle[0],"en");/*xTitle[0].getElementsByTagName("en")[0].childNodes[0].nodeValue ;*/
				var cTranCn = getNodeText(xTitle[0],"cn");/*xTitle[0].getElementsByTagName("cn")[0].childNodes[0].nodeValue;*/
				txtOut = txtOut + "<a name='"+tSuttaTitle+"' href='#_Content' >返回目录</a>";
				txtOut = txtOut + "<h1>" + cPaliTitle + "</h1>\n";
				txtOut = txtOut + "<p class='tran_h1_en' >" + cTranEn + "</p>\n";
				txtOut = txtOut + "<p class='tran_h1_cn' >" +  cTranCn + "</p>\n";
				txtOut = txtOut + "</div>\n"; 
				
				tContent = tContent + "<p><a href='#"+ tSuttaTitle + "' >"+cPaliTitle +  cTranCn + " </a></p> ";
				
				var sPaliWord="";
				var sWordId="";
				var sOrgWord="";
				var sMeanWord="";
				var sCaseWord="";
				
				xParagraph = x[i].getElementsByTagName("paragraph");
				for (j=0;j<xParagraph.length;j++)
				{
					txtOut = txtOut + "<div class='sutta_paragraph' >\n";
					if(g_is_mobile){
						txtOut = txtOut + "<div class='pali_par_mobile'>\n";
					}
					else{
						txtOut = txtOut + "<div class='pali_par'>\n";
					}
					xPali = xParagraph[j].getElementsByTagName("palipar");
					if(xPali.length>0)
					{
						xWord = xPali[0].getElementsByTagName("word");//如果只有一个palipar
						/*遍历此段落中所有单词*/
						for(k=0;k<xWord.length;k++)
						{
							sPaliWord = getNodeText(xWord[k],"pali");
							sOrgWord = getNodeText(xWord[k],"org");
							sMeanWord = getNodeText(xWord[k],"mean");
							sCaseWord = getNodeText(xWord[k],"case");
							sIdWord = getNodeText(xWord[k],"id");
							sWordId = iWordCount;/*自动的单词计数器*/
							

							if(sPaliWord=="###"){
								txtOut = txtOut + "<div class=\"enter\"></div>\n";
							}
							else{									
								var txtStyleColor="";
								/*将这个词与字典匹配，*/
								var iDict=0;
								for(iDict=0;iDict<arrDictWordList.length;iDict++)
								{
									if(sMeanWord=="?")
									{
										if(sPaliWord==arrDictWordList[iDict].Pali)
										{
											sOrgWord = arrDictWordList[iDict].Org;
											sMeanWord = "?a?"+arrDictWordList[iDict].Mean;
											sCaseWord = arrDictWordList[iDict].Case;
											setNodeText(xWord[k],"org",sOrgWord);
											setNodeText(xWord[k],"mean",sMeanWord);
											setNodeText(xWord[k],"case",sCaseWord);
										}
									}
								}
								
								/*输出Pali单词部分*/
								
								/*长度为1的为标点符号*/
								if(sPaliWord.length<=1){
									txtOut = txtOut + "<div id=\"wb"+sWordId+"\" class='word_punc'> "; 
									txtOut = txtOut + "<p class='pali' name='wPali'> <span name=\"spali\">";
									txtOut = txtOut + sPaliWord;
									txtOut = txtOut + "</span></p>\n";
								}
								else{
									txtOut = txtOut + "<div id=\"wb"+sWordId+"\" class='word'> "; 
									txtOut = txtOut + "<p class='pali' name='wPali'>";
									txtOut = txtOut +"<a name='w"+sWordId+"' title=\""+sMeanWord+"\" onclick='showModifyWin(\""+sWordId+"\")'>";
									txtOut = txtOut + "<span name=\"spali\">"+sPaliWord+"</span>";
									txtOut = txtOut + "</a></p>\n";
								}
								/*输出Detail块部分*/
								/*设置detail 块可见性。非巴利词不可见*/

								/*如果不是Pali词隐藏detail*/
								if(sPaliWord.match(/[a-z,A-Z]/)==null || sPaliWord.length<=1){
									txtStyleColor = " class='hidden' ";
								}
								if(sPaliWord.match(/[qxw]/)!=null){
									txtStyleColor = " class='hidden' ";
								}
								txtOut = txtOut + "<div "+txtStyleColor+" id='detail"+sWordId+"'>";
								txtOut = txtOut + makeWordDetailByValue(sIdWord,sOrgWord,sMeanWord,sCaseWord);
								txtOut = txtOut + "</div>";/*detail块结束*/
								txtOut = txtOut + "</div>\n";/*单词块结束*/
								if( sPaliWord=="." || sPaliWord=="," || sPaliWord=="?"){
									txtOut = txtOut + "<div class=\"chanting_enter\"></div>\n";
								}
							}
							iWordCount = iWordCount + 1;
						}
						txtOut = txtOut + "<div class='clr'></div> ";
						txtOut = txtOut + "</div>\n"; /*Pali段落块结束*/
					}
					
					/*翻译块开始*/
					xTran = xParagraph[j].getElementsByTagName("tran");
					if(xTran.length>0)
					{
						if(g_is_mobile){
							txtOut = txtOut + "<div class='tran_par_mobile'>";
						}
						else{
							txtOut = txtOut + "<div class='tran_par'>";
						}
						var strTranEn = getNodeText(xTran[0],"en");
						var strTranCn = getNodeText(xTran[0],"cn");
						txtOut = txtOut + "<p class=tran_par_en>" + strTranEn + "</p>";
						txtOut = txtOut + "<p class='tran_par_cn'>" + strTranCn + "</p>";
						txtOut = txtOut + "</div>\n";
					}
					txtOut = txtOut + "<div class='clr'></div>";	
					txtOut = txtOut + "</div>\n";
				}
				txtOut = txtOut + "</div>\n";
			  }

			 document.getElementById("sutta_text").innerHTML = txtOut;
			 document.getElementById("htmlstring").value = txtOut;
			 /*document.getElementById("htmlwholestring").value = document.documentElement.innerHTML;*/
			 document.getElementById("xmlout").value = xmlToString(xmlDoc);
			 document.getElementById("content").innerHTML = tContent;
			 document.getElementById("navi_bookmark_inner").innerHTML = bookMark();

			 nWord=iWordCount;
                    return;
                }	
                catch (e) {
			
                    var_dump("<p>error:"+e+"</p>");
                }
            
}

function save()
{
xmlHttp=null;
var_dump("loading");
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

/*make book mark*/
function bookMark(){
	var colorStyle="";
	var strBookMark="";
	var xSutta = xmlDoc.getElementsByTagName("sutta");
	var iWordCount=0;

	/*遍历所有经*/
	for (i=0;i<xSutta.length;i++)
	{
		xTitle = xSutta[i].getElementsByTagName("title");
		strPaliTitle = xTitle[0].getElementsByTagName("pali")[0].childNodes[0].nodeValue;
		strBookMark = strBookMark + "<h3>"+strPaliTitle+"</h3>";
		xWord = xSutta[i].getElementsByTagName("word");
		/*遍历此经中所有单词*/
		for(k=0;k<xWord.length;k++)
		{
			strWordPali = getNodeText(xWord[k],"pali");
			strWordMean = getNodeText(xWord[k],"mean");
			if(strWordMean.length>2 && strWordMean.substr(0,1)=="?"){
				var markString = strWordMean.substr(1,1);
				colorStyle = "bookmarkcolor"+markString;
				var bookMarkId = "w"+iWordCount;
				strBookMark = strBookMark + "<p class=\"bm"+markString+"\"><span class='bookmarkcolorblock , "+colorStyle+"'>"+markString+"</span><a href=\"#"+bookMarkId+"\">"+strWordPali+":"+strWordMean.substr(3,10)+"</a></p>";
			}
			iWordCount++;
		}
	}
	return(strBookMark);
}

function setBookmarkVisibility(className,controlID){
	var isVisible = document.getElementById(controlID).checked;
	getStyleClass(className).style.display = (isVisible ? 'block' : 'none');
}
/*刷新书签*/
function refreshBookMark(){
	document.getElementById("navi_bookmark_inner").innerHTML = bookMark();
}
/*Apply all system match words*/
function applyAllSysMatch(){
	var xSutta = xmlDoc.getElementsByTagName("sutta");
	var iWordCount=0;
	var iModified=0;
	/*遍历所有经*/
	for (i=0;i<xSutta.length;i++)
	{
		xWord = xSutta[i].getElementsByTagName("word");
		/*遍历此经中所有单词*/
		for(k=0;k<xWord.length;k++)
		{
			strWordMean = getNodeText(xWord[k],"mean");
			if(strWordMean.length>2){
				if(strWordMean.substr(0,3)=="?a?"){
					setNodeText(xWord[k],"mean",strWordMean.substr(3));
					modifyWordDetailByWordId(iWordCount);
					iModified++;
				}
			}
			iWordCount++;
		}
	}
	if(iWordCount>0){
		document.getElementById("navi_bookmark_inner").innerHTML = bookMark();
	}
	var_dump(iModified+"个单词被确认。")
}

function setUseMode(strUseMode){
	if(strUseMode=="edit"){
		document.getElementById("use_mode").innerHTML = "Edit";
		g_useMode="edit";
		document.getElementById("xmldata").style.display="block";
		getStyleClass('chanting_enter').style.display = "none";
	}
	else if(strUseMode=="chanting"){
		document.getElementById("use_mode").innerHTML = "Chanting";
		g_useMode="chanting";
		document.getElementById("xmldata").style.display="none";
		getStyleClass('chanting_enter').style.display = "block";
	}	
	else{
		document.getElementById("use_mode").innerHTML = "Read";
		g_useMode="read";
		document.getElementById("xmldata").style.display="none";
		getStyleClass('chanting_enter').style.display = "none";
	}

	
	if(xmlDoc!=null){
		var mWordNode = xmlDoc.getElementsByTagName("word");
		/*遍历所有单词*/
		for(k=0;k<mWordNode.length;k++)
		{
			modifyWordDetailByWordId(k);
		}
	}
	getStyleClass('dropdown-content').style.display = "none";	
	
}

function dropbtnClick(menuId){
	if(document.getElementById(menuId).style.display=="block"){
		document.getElementById(menuId).style.display="none";
	}
	else{
		document.getElementById(menuId).style.display="block";
	}
}

function setUseMode_Static(strUseMode){
	if(strUseMode=="chanting"){
		document.getElementById("use_mode").innerHTML = "Chanting";
		g_useMode="chanting";
		getStyleClass('chanting_enter').style.display = "block";
	}	
	else{
		document.getElementById("use_mode").innerHTML = "Read";
		g_useMode="read";
		getStyleClass('chanting_enter').style.display = "none";
	}
	
	dropbtnClick("menu01");
			
}

function hiddenMenu(){
	getStyleClass('dropdown-content').style.display = 'none';
}

function xmlToString(elem){
	var serialized;
	try{
		serializer = new XMLSerializer();
		serialized = serializer.serializeToString(elem);
	}
	catch(e){
		serialized = elem.xml;
	}
	return(serialized);
}