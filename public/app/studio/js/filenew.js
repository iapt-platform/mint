var numOfTranInput = 1;
var fileName = "";
var txtXML="";

function fileNew(){
	window.location.assign("filenew.html");
}

function fileNewSave()
{
if(fileName==""){
  var inputFileName=prompt("File Name","filename.xml");
  if (inputFileName==null || inputFileName=="")
    {
    alert("File Name?");
	return;
    }
	else{
		fileName=inputFileName;
	}
}

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
  xmlHttp.open("POST", "./xml_save.php", false);
  var sendHead="filename="+"../user/My Document/"+fileName+"#";
  xmlHttp.send(sendHead+txtXML);
  var_dump(xmlHttp.responseText);
  }
else
  {
  alert("Your browser does not support XMLHTTP.");
  }
}
function new_getPaliReal(inStr){
	var paliletter="abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
	var output="";
	inStr=inStr.toLowerCase();
	inStr = inStr.replace(/ṁ/g,"ṃ");
	inStr = inStr.replace(/ŋ/g,"ṃ");
	for(x in inStr){
		if(paliletter.indexOf(inStr[x])!=-1){
			output+=inStr[x];
		}
	}
	return(output);
}
function fileNewPreview(){
try{
	var strData = document.getElementById("txtNewInput").value;
	var strPar=strData.split("\n");
	var strDataTran1 = document.getElementById("txtNewInputTran1").value;
	strDataTran1=strDataTran1.replace(/#br#/g,"&lt;br&gt;");
	var strParTran1=strDataTran1.split("\n");
	var strDataTran2 = document.getElementById("txtNewInputTran2").value;
	strDataTran2=strDataTran2.replace(/#br#/g,"&lt;br&gt;");
	var strParTran2=strDataTran2.split("\n");
	
	var strWord;
	var x;
	var iContentStart=0;
	var iWordCount=0;
	txtXML="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	var txtHtml;
	txtHtml = "<div class='sutta'>";
	txtXML = txtXML + "<book>\n<sutta id=\"suttaid\">\n";
	
	var tranLangauge1 = document.getElementById("tranlanguage1").value;	
	var tranLangauge2 = document.getElementById("tranlanguage2").value;	
	

	if(document.getElementById("chk_title").checked){
		txtXML = txtXML + "<title>";
		txtXML = txtXML + "<text>";
		txtXML = txtXML + "<info><language>pali</language><author>author</author></info>";
		txtXML = txtXML + "<data>"+strPar[0]+"</data>";
		txtXML = txtXML + "</text>";
		
		if(strDataTran1.length>0){
			txtXML = txtXML + "<text>";
			txtXML = txtXML + "<info><language>"+tranLangauge1+"</language><author>author</author></info>";
			txtXML = txtXML + "<data>"+strParTran1[0]+"</data>";
			txtXML = txtXML + "</text>";
		}
		if(strDataTran2.length>0){
			txtXML = txtXML + "<text>";
			txtXML = txtXML + "<info><language>"+tranLangauge2+"</language><author>author</author></info>";
			txtXML = txtXML + "<data>"+strParTran2[0]+"</data>";
			txtXML = txtXML + "</text>";
		}		
		txtXML = txtXML + "</title>";

		iContentStart=1;
		
		txtHtml = txtHtml + "<div class='sutta_title'>\n";
		txtHtml = txtHtml + "<h1>" + strPar[0] + "</h1>\n";
		if(strDataTran1.length>0){
			txtHtml = txtHtml + "<p class='tran_h1_"+tranLangauge1+"' >" + strParTran1[0] + "</p>\n";
		}
		if(strDataTran2.length>0){
			txtHtml = txtHtml + "<p class='tran_h1_"+tranLangauge2+"' >" + strParTran2[0] + "</p>\n";
		}
		txtHtml = txtHtml + "</div>\n";
	}

	
	
	for (var i=iContentStart;i<strPar.length;i++)
	{
		txtHtml = txtHtml + "<div class='sutta_paragraph' >\n";
		txtHtml = txtHtml + "<div class='pali_par_mobile'>\n";
		txtXML = txtXML + "<paragraph>\n";
		txtXML = txtXML + "<palipar>\n";
		
		strWord = strPar[i].split(" ");
		for (var k=0;k<strWord.length;k++){
			sPaliWord = strWord[k];
			sOrgWord = "?";
			sMeanWord = "?";
			sCaseWord = "?";
			sIdWord = k;
			sWordId = iWordCount;/*自动的单词计数器*/
			txtXML = txtXML + "<word><pali>"+sPaliWord+"</pali><real>"+new_getPaliReal(sPaliWord)+"</real><id>"+sWordId+"</id><mean>?</mean><org>?</org><om>?</om><case>?</case></word>\n";
			
							if(sPaliWord=="#br#"){
								txtHtml = txtHtml + "<div class=\"enter\"></div>\n";
							}
							else{																	
								/*输出Pali单词部分*/
								
								/*长度为1的为标点符号*/
								//if(sPaliWord.length<=1)
								//{
								//	txtHtml = txtHtml + "<div id=\"wb"+sWordId+"\" class='word_punc'> "; 
								//	txtHtml = txtHtml + "<p class='pali' name='wPali'> <span name=\"spali\">";
								//	txtHtml = txtHtml + sPaliWord;
								//	txtHtml = txtHtml + "</span></p>\n";
								//}
								//else
								{
									txtHtml = txtHtml + "<div id=\"wb"+sWordId+"\" class='word'> "; 
									txtHtml = txtHtml + "<p class='pali' name='wPali'>";
									txtHtml = txtHtml +"<a name='w"+sWordId+"' title=\""+sMeanWord+"\" >";
									txtHtml = txtHtml + "<span name=\"spali\">"+sPaliWord+"</span>";
									txtHtml = txtHtml + "</a></p>\n";
								}
								/*输出Detail块部分*/
								/*设置detail 块可见性。非巴利词不可见*/


								txtHtml = txtHtml + "<div id='detail"+sWordId+"' class='bg"+(iWordCount%2)+"'>";
								txtHtml = txtHtml + "<span >?</span>"
								txtHtml = txtHtml + "</div>";/*detail块结束*/
								txtHtml = txtHtml + "</div>\n";/*单词块结束*/

							}
							iWordCount = iWordCount + 1;
		}
		
		txtXML = txtXML + "</palipar>\n";
		txtHtml = txtHtml + "</div>";/*end of pali par*/
		txtHtml = txtHtml + "<div class='clr'></div> ";
		
		/*翻译块开始*/
		txtHtml = txtHtml + "<div class='tran_par_mobile'>";
		txtXML = txtXML + "<translate>\n";
		
		if(strDataTran1.length>0){
			if(i<strParTran1.length){					
				var strTranEn = strParTran1[i];
				txtHtml = txtHtml + "<p class=tran_par_"+tranLangauge1+">" + strParTran1[i] + "</p>";
				txtXML = txtXML + "<text>\n";
				txtXML = txtXML + "<info>\n";
				txtXML = txtXML + "<language>"+tranLangauge1+"</language>\n";
				txtXML = txtXML + "<author>"+document.getElementById("tranauthor1").value+"</author>\n";
				txtXML = txtXML + "</info>\n";
				txtXML = txtXML + "<data>"+strParTran1[i]+"</data>\n";
				txtXML = txtXML + "</text>\n";
			}
		}
		if(strDataTran2.length>0){
			if(i<strParTran2.length){					
				var strTranEn = strParTran2[i];
				txtHtml = txtHtml + "<p class=tran_par_"+tranLangauge2+">" + strParTran2[i] + "</p>";
				txtXML = txtXML + "<text>\n";
				txtXML = txtXML + "<info>\n";
				txtXML = txtXML + "<language>"+tranLangauge2+"</language>\n";
				txtXML = txtXML + "<author>"+document.getElementById("tranauthor2").value+"</author>\n";
				txtXML = txtXML + "</info>\n";
				txtXML = txtXML + "<data>"+strParTran2[i]+"</data>\n";
				txtXML = txtXML + "</text>\n";
			}
		}

		
		txtHtml = txtHtml + "</div>\n";
		txtXML = txtXML + "</translate>\n";
		/*end of translate block*/

		txtHtml = txtHtml + "</div>";
		txtXML = txtXML + "</paragraph>\n";
		/*end of paragraph*/
	}
	txtHtml = txtHtml + "</div>";
	txtXML = txtXML + "</sutta>\n";
	txtXML = txtXML + "</book>\n";
	document.getElementById("sutta_text").innerHTML = txtHtml;
	
}
catch(e){
	alert(e);
}
}

function newTran(){
	var para=document.createElement("div");
	para.innerHTML = "<select name=\"language\"><option value=\"cn\">Chinese</option><option value=\"en\">English</option></select><input type=\"input\" value=\"*\"/><p><textarea id=\"txtNewInputTran1\" rows=\"15\" cols=\"80\" ></textarea></p>";
	var element=document.getElementById("inputTran");
	element.appendChild(para);
}

function inputMode(obj){
	switch(obj.value){
		case "single":
			document.getElementById("linenum").style.display="none";
			document.getElementById("divlineinfo").style.display="none";
			document.getElementById("inputTran").style.display="block";
			document.getElementById("file_new").style.display="block";
			break;
		case "mix":
			document.getElementById("linenum").style.display="inline";
			document.getElementById("divlineinfo").style.display="block";
			document.getElementById("inputTran").style.display="none";
			document.getElementById("file_new").style.display="none";
		break;

	}
}
function lineNum(obj){
	document.getElementById("divline2").style.display="none";
	document.getElementById("divline3").style.display="none";
	document.getElementById("divline4").style.display="none";
	switch(obj.value){
		case "4":
			document.getElementById("divline4").style.display="block";
		case "3":
			document.getElementById("divline3").style.display="block";
		case "2":
			document.getElementById("divline2").style.display="block";
		}
}