var txtXML="";
var gXmlResList=null;
var gTocList=new Array();//目录信息数组
var gResList=new Array();
var gCurrResIndex=0;
var gResDownloadList = new Array()
var gCurrResListArray=new Array()
var gCurrResListArray2=new Array()

var gTocLanguage = new Array()
var gTocLanguageItem = new Array()
var gTocCurrLanguage="pali";

var gTocCurrRoot = -1;

var gResTypeList = new Array("wbw","translate");
var gCurrQueryResType=0;

var gCurrBookType="";

var gDownloadListString="";

function editor_wizard_next(id){
	document.getElementById('wizard_div').style.display="none";
	document.getElementById(id).style.display="flex";
	if(id=="wizard_div_palicannon"){
	wizard_palicannon_init();
	}
/*
	var xmlHttp=null;
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
	  var wizardDir="module/editor_wizard/"
	  xmlHttp.open("POST", wizardDir+url, false);
	  xmlHttp.send(data);
	  document.getElementById('wizard_div').innerHTML = xmlHttp.responseText;
	  }
	else
	  {
	  alert("Your browser does not support XMLHTTP.");
	  }
*/
}

function wizard_new_finish(){

	var xmlText = txtXML;
	var newFileName = new_save(xmlText);
	if(newFileName){
		window.open("./editor.php?op=open&filename="+newFileName,"_blank");
		//window.location.assign("index_new.php");
		window.history.back();
	}
}

function new_save(strData){
	xmlHttp=null;
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
		if(g_filename.length==""){
			var inputFileName=prompt("Project Name","new project");
			if (inputFileName==null || inputFileName==""){
				alert("Project Name Can not Empty");
				return;
			}
			else{
				g_filename=gConfigDirMydocument+inputFileName+".pcs";
			}
		}
	$.post("./dom_http.php",
	  {
		filename:g_filename,
		fileid:0,
		xmldata:strData
	  },
	  function(data,status){
		alert("Data: " + data + "\nStatus: " + status);
	  });
	}
}

function wizard_new_getPaliReal(inStr){
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

function wizard_fileNewPreview(){
try{
	var strData = document.getElementById("txtNewInput").value;
	var strPar1=new Array()
	strPar0=strData.split("\n");
	var strPar=new Array()
	for(var i=0;i<strPar0.length;i++){//增加層級信息/t分隔
	var Line=strPar0[i].split("\t")
	var newLine=new Object()
	newLine.lvl=Line[0]
	newLine.txt=Line[1]
	strPar.push(newLine);
		
	}
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
	var bookId = com_guid();
	txtXML="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	txtXML += "<set>\n"
	txtXML += "    <head>\n"
	txtXML += "        <type>pcdsset</type>\n"
	txtXML += "        <mode>package</mode>\n"
	txtXML += "        <ver>1</ver>\n"
	txtXML += "        <toc></toc>\n"
	txtXML += "        <style></style>\n"
	txtXML += "    </head>\n"
	txtXML += "    <dict></dict>\n"
	txtXML += "    <body>\n"

	
	var txtHtml;
	txtHtml = "<div class='sutta'>";
	
	var tranlanguage1 = document.getElementById("tranlanguage1").value;	
	var tranlanguage2 = document.getElementById("tranlanguage2").value;	

	var tranAuthor1 = document.getElementById("tranauthor1").value;	
	var tranAuthor2 = document.getElementById("tranauthor2").value;		
	
	var paliAuthor = document.getElementById("paliauthor").value;
	
	if(document.getElementById("chk_title").checked){		
		//txtXML +=  "<block>";
		//txtXML +=  "<info><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level><language>pali</language><author>author</author></info>";
		//txtXML +=  "<data><sen><a></a><text>"+strPar[0]+"</text><sen></data>";
		//txtXML +=  "</block>";
		txtXML +=  "<block>\n";
		txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level>"
		txtXML +=  "<language>pali</language>\n";
		txtXML +=  "<author>"+paliAuthor+"</author>\n";
		txtXML +=  "</info>\n";
		txtXML +=  "<data><a></a><text>"+strPar[0].txt+"</text></data>\n";
		txtXML +=  "</block>\n";

		if(strDataTran1.length>0){
			//txtXML +=  "<block>";
			//txtXML +=  "<info><type>heading</type><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level><language>"+tranlanguage1+"</language><author>"+tranAuthor1+"</author></info>";
			//txtXML +=  "<data><sen><a></a><text>"+strParTran1[0]+"</text><sen></data>";
			//txtXML +=  "</block>";
			txtXML +=  "<block>\n";
			txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level>"
			txtXML +=  "<language>"+tranlanguage1+"</language>\n";
			txtXML +=  "<author>"+tranAuthor1+"</author>\n";
			txtXML +=  "</info>\n";
			txtXML +=  "<data><a></a><text>"+strParTran1[0]+"</text></data>\n";
			txtXML +=  "</block>\n";
		}
		if(strDataTran2.length>0){
			//txtXML +=  "<block>";
			//txtXML +=  "<info><type>heading</type><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level><language>"+tranlanguage2+"</language><author>"+tranAuthor2+"</author></info>";
			//txtXML +=  "<data><sen><a></a><text>"+strParTran2[0]+"</text><sen></data>";
			//txtXML +=  "</block>";
			txtXML +=  "<block>\n";
			txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level>"
			txtXML +=  "<language>"+tranlanguage2+"</language>\n";
			txtXML +=  "<author>"+tranAuthor2+"</author>\n";
			txtXML +=  "</info>\n";
			txtXML +=  "<data><a></a><text>"+strParTran2[0]+"</text></data>\n";
			txtXML +=  "</block>\n";
		}		

		iContentStart=1;
		
		txtHtml = txtHtml + "<div class='sutta_title'>\n";
		txtHtml = txtHtml + "<h1>" + strPar[0].txt + "</h1>\n";
		if(strDataTran1.length>0){
			txtHtml = txtHtml + "<p class='tran_h1_"+tranlanguage1+"' >" + strParTran1[0] + "</p>\n";
		}
		if(strDataTran2.length>0){
			txtHtml = txtHtml + "<p class='tran_h1_"+tranlanguage2+"' >" + strParTran2[0] + "</p>\n";
		}
		txtHtml = txtHtml + "</div>\n";
	}
	
	var parCounter=1;
	for (var i=iContentStart;i<strPar.length;i++)
	{
		if(strPar[i].lvl>0){
			txtXML +=  "<block>\n";
			txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>heading</type><level>"+strPar[i].lvl+"</level>"
			txtXML +=  "<language>pali</language>\n";
			txtXML +=  "<author>"+paliAuthor+"</author>\n";
			txtXML +=  "</info>\n";
			txtXML +=  "<data><a></a><text>"+strPar[i].txt+"</text></data>\n";
			txtXML +=  "</block>\n";

			txtHtml = txtHtml + "<div class='sutta_title'>\n";
			txtHtml = txtHtml + "<h1>" + strPar[i].txt + "</h1>\n";

			if(strDataTran1.length>0){
			//txtXML +=  "<block>";
			//txtXML +=  "<info><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level><language>"+tranlanguage1+"</language><author>"+tranAuthor1+"</author></info>";
			//txtXML +=  "<data><sen><a></a><text>"+strParTran1[0]+"</text><sen></data>";
			//txtXML +=  "</block>";
				txtXML +=  "<block>\n";
				txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>heading</type><level>"+strPar[i].lvl+"</level>"
				txtXML +=  "<language>"+tranlanguage1+"</language>\n";
				txtXML +=  "<author>"+tranAuthor1+"</author>\n";
				txtXML +=  "</info>\n";
				txtXML +=  "<data><a></a><text>"+strParTran1[i]+"</text></data>\n";
				txtXML +=  "</block>\n";

				txtHtml = txtHtml + "<p class='tran_h1_"+tranlanguage1+"' >" + strParTran1[i] + "</p>\n";
			}
			if(strDataTran2.length>0){
			//txtXML +=  "<block>";
			//txtXML +=  "<info><book>"+bookId+"</book><paragraph>1</paragraph><type>heading</type><level>1</level><language>"+tranlanguage2+"</language><author>"+tranAuthor2+"</author></info>";
			//txtXML +=  "<data><sen><a></a><text>"+strParTran2[0]+"</text><sen></data>";
			//txtXML +=  "</block>";
				txtXML +=  "<block>\n";
				txtXML +=  "<info><id>"+com_guid()+"</id><type>heading</type><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>heading</type><level>"+strPar[i].lvl+"</level>"
				txtXML +=  "<language>"+tranlanguage2+"</language>\n";
				txtXML +=  "<author>"+tranAuthor2+"</author>\n";
				txtXML +=  "</info>\n";
				txtXML +=  "<data><a></a><text>"+strParTran2[i]+"</text></data>\n";
				txtXML +=  "</block>\n";
			txtHtml = txtHtml + "<p class='tran_h1_"+tranlanguage2+"' >" + strParTran2[i] + "</p>\n";

			}
			txtHtml = txtHtml + "</div>\n";
			
	}
	else{
		txtHtml = txtHtml + "<div class='sutta_paragraph' >\n";
		txtHtml = txtHtml + "<div class='pali_par_mobile'>\n";
		
		txtXML +=  "<block>\n";
		txtXML +=  "<info><id>"+com_guid()+"</id><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>wbw</type><language>pali</language><author>author</author></info>";
		txtXML +=  "<data>\n";
		
		strWord = strPar[i].txt.split(" ");
		for (var k=0;k<strWord.length;k++){
			sPaliWord = strWord[k];
			sOrgWord = "?";
			sMeanWord = "?";
			sCaseWord = "?";
			sIdWord = k;
			sWordId = iWordCount;/*自动的单词计数器*/
			txtXML +=  "<word><pali>"+sPaliWord+"</pali><real>"+wizard_new_getPaliReal(sPaliWord)+"</real><id>"+sWordId+"</id><mean>?</mean><org>?</org><om>?</om><case>?</case></word>\n";
			
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
		
		
		txtXML +=  "</data></block>\n";
		txtHtml = txtHtml + "</div>";/*end of pali par*/
		txtHtml = txtHtml + "<div class='clr'></div> ";
		
		/*翻译块开始*/
		txtHtml = txtHtml + "<div class='tran_par_mobile'>";
		
		
		if(strDataTran1.length>0){
			if(i<strParTran1.length){					
				var strTranEn = strParTran1[i];
				txtHtml = txtHtml + "<p class=tran_par_"+tranlanguage1+">" + strParTran1[i] + "</p>";
							
				txtXML +=  "<block>\n";
				txtXML +=  "<info><id>"+com_guid()+"</id><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>translate</type>"
				txtXML +=  "<language>"+tranlanguage1+"</language>\n";
				txtXML +=  "<author>"+document.getElementById("tranauthor1").value+"</author>\n";
				txtXML +=  "</info>\n";
				txtXML +=  "<data><sen><a></a><text>"+strParTran1[i]+"</text></sen></data>\n";
				txtXML +=  "</block>\n";
			}
		}
		if(strDataTran2.length>0){
			if(i<strParTran2.length){					
				var strTranEn = strParTran2[i];
				txtHtml = txtHtml + "<p class=tran_par_"+tranlanguage2+">" + strParTran2[i] + "</p>";
				
				txtXML +=  "<block>\n";
				txtXML +=  "<info><id>"+com_guid()+"</id><book>"+bookId+"</book><paragraph>"+parCounter+"</paragraph><type>translate</type>"
				txtXML +=  "<language>"+tranlanguage2+"</language>\n";
				txtXML +=  "<author>"+document.getElementById("tranauthor2").value+"</author>\n";
				txtXML +=  "</info>\n";
				txtXML +=  "<data><sen><a></a><text>"+strParTran2[i]+"</text></sen></data>\n";
				txtXML +=  "</block>\n";
			}
		}

		
		txtHtml = txtHtml + "</div>\n";
		/*end of translate block*/

		txtHtml = txtHtml + "</div>";
		/*end of paragraph*/
		
		parCounter++;
	}
	}
	txtHtml = txtHtml + "</div>";
	
	
	txtXML += "    </body>\n"
	txtXML += "</set>\n"
	
	
	document.getElementById("wizard_sutta_preview").innerHTML = txtHtml;
	
}
catch(e){
	alert(e);
}
}

function wizard_show_input(itemId,liTab){
	document.getElementById("new_input_pali").style.display="none";
	document.getElementById("new_input_Tran1").style.display="none";
	document.getElementById("new_input_Tran2").style.display="none";

	document.getElementById("NewFilePali").className = "common-tab_li";
	document.getElementById("NewFileTran1").className = "common-tab_li";
	document.getElementById("NewFileTran2").className = "common-tab_li";

	
	document.getElementById(itemId).style.display="block";
	document.getElementById(liTab).className = "common-tab_li_act";
}

function wizard_palicannon_init(){
	ntf_init();
	document.getElementById("id_wizard_palicannon_index_c2").style.display="none";
	document.getElementById("id_wizard_palicannon_index_c3").style.display="none";
	document.getElementById("id_wizard_palicannon_index_c4").style.display="none";
	document.getElementById("id_wizard_palicannon_index_book").style.display="none";
	document.getElementById('wizard_palicannon_par_select_text_body').innerHTML="";
	
	wizard_palicannon_index_render_c1("");

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
			var cssItem="pali_book_item selected";
		}
		else{
			var cssItem="pali_book_item";
		}
		objC1.innerHTML += "<li class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_c1('"+list[index]+"')\">"+list[index]+"</li>"
	}
}
function loadUserWBWList(){
	$.getJSON(
		"../uwbw/get_index.php"
	).done(function (data) {
		let html ="";
		for (const it of data) {
			html += "<div class='file_list_row'>";
			link = "<a href='./editor.php?op=openchannel&book="+it.book+"&par="+it.paragraph+"&channel="+it.channel_uid+"' target='_blank'>";
			html += "<div style='flex:5;'>"+link+it.book_id+"-"+it.paragraph+"</a></div>";
			html += "<div style='flex:2;'>"+it.updated_at+"</div>";
			html += "</div>"
		}
		$("#file_list").html(html);

	}).fail(function(jqXHR, textStatus, errorThrown){
		console.log("loadUserWBWList",textStatus);
		switch (textStatus) {
			case "timeout":
				break;
			case "error":
				switch (jqXHR.status) {
					case 404:
						break;
					case 500:
						break;				
					default:
						break;
				}
				break;
			case "abort":
				break;
			case "parsererror":
				console.log("loadUserWBWList",jqXHR.responseText);
				break;
			default:
				break;
		}
	});
}
function wizard_palicannon_index_changed_c1(indexSelected){
	document.getElementById("id_wizard_palicannon_index_c2").style.display="none";
	document.getElementById("id_wizard_palicannon_index_c3").style.display="none";
	document.getElementById("id_wizard_palicannon_index_c4").style.display="none";
	document.getElementById("id_wizard_palicannon_index_book").style.display="none";
	wizard_palicannon_heading_div_cls(1);
	wizard_palicannon_palitext_div_cls();
	
	wizard_palicannon_index_render_c1(indexSelected);
	
	wizard_palicannon_index_render_c2(indexSelected,"");
}

function wizard_palicannon_index_render_c2(strParent,strSelected){
	var objC2 = document.getElementById("id_wizard_palicannon_index_c2");
	strC1=strParent;
	objC2.innerHTML="";
	var currStr="";
	var list= new Array();
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1){
			pc_pushNewToList(list,local_palicannon_index[index].c2);
		}
	}
	for(index in list){
		if(list[index]==strSelected){
			var cssItem="pali_book_item selected";
		}
		else{
			var cssItem="pali_book_item";
		}
		objC2.innerHTML += "<div class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_c2('"+strC1+"','"+list[index]+"')\">"+list[index]+"</div>";
	}

	objC2.style.display="block";	
}

function wizard_palicannon_index_changed_c2(strParent,value){
	document.getElementById("id_wizard_palicannon_index_c3").style.display="none";
	document.getElementById("id_wizard_palicannon_index_c4").style.display="none";
	document.getElementById("id_wizard_palicannon_index_book").style.display="none";
	wizard_palicannon_heading_div_cls(1);
	wizard_palicannon_palitext_div_cls();
	
	//渲染自己 增加选择状态显示
	wizard_palicannon_index_render_c2(strParent,value);
	
	
	//渲染c3
	wizard_palicannon_index_render_c3(value,"");
}

function wizard_palicannon_index_render_c3(strParent,strSelected){
	var objC3 = document.getElementById("id_wizard_palicannon_index_c3");
	strC2=strParent;
	objC3.innerHTML="";
	var currStr="";
	var list= new Array();

	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2){
			if(local_palicannon_index[index].c3!=""){
				pc_pushNewToList(list,local_palicannon_index[index].c3);
			}

		}
	}
	if(list.length==0){
		wizard_palicannon_index_render_book(2,strParent,"");

	}
	else{
		for(index in list){
			if(list[index]==strSelected){
				var cssItem="pali_book_item selected";
			}
			else{
				var cssItem="pali_book_item";
			}
			objC3.innerHTML += "<div class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_c3('"+strC2+"','"+list[index]+"')\">"+list[index]+"</div>";
		}
		objC3.style.display="block";
	}	
}

function wizard_palicannon_index_changed_c3(strParent,value){
	document.getElementById("id_wizard_palicannon_index_c4").style.display="none";
	document.getElementById("id_wizard_palicannon_index_book").style.display="none";
	wizard_palicannon_heading_div_cls(1);
	wizard_palicannon_palitext_div_cls();
	
	//渲染自己 增加选择状态显示
	wizard_palicannon_index_render_c3(strParent,value);
	
	var objC4 = document.getElementById("id_wizard_palicannon_index_c4");
	strC3=value;
	objC4.innerHTML="";
	var currStr="";
	var list= new Array();
	
	
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].c1==strC1 && local_palicannon_index[index].c2==strC2 && local_palicannon_index[index].c3==strC3){
			if(local_palicannon_index[index].c4!=""){
				pc_pushNewToList(list,local_palicannon_index[index].c4);
			}
			
			
		}
	}
	if(list.length==0){
		wizard_palicannon_index_render_book(3,value,"");
	}
	else{
		for(index in list){
			objC4.innerHTML += "<div class=\"pali_book_item\" onclick=\"wizard_palicannon_index_changed_book('"+list[index]+"')\">"+list[index]+"</div>";

		}
	}
}

//渲染书章节目录
function wizard_palicannon_index_render_book(parentLevel,strParent,strSelected){
		var bookTitle= new Array();
		var bookId= new Array();	
		switch(parentLevel){
			case 2:
				strC2=strParent;
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
		


		var objBook = document.getElementById("id_wizard_palicannon_index_book");
		objBook.innerHTML="";
		for(index in bookTitle){
			if(bookTitle[index]==strSelected){
				var cssItem="pali_book_item selected";
			}
			else{
				var cssItem="pali_book_item";
			}
			objBook.innerHTML += "<div class=\""+cssItem+"\" onclick=\"wizard_palicannon_index_changed_book("+parentLevel+",'"+strParent+"','"+bookTitle[index]+"','"+bookId[index]+"')\">"+bookTitle[index]+"</div>";
		}
		document.getElementById("id_wizard_palicannon_index_book").style.display="block";	
}

//点击书，显示目录，书籍信息，巴利原文
function wizard_palicannon_index_changed_book(parentLevel,strParent,strBookTitle,strBookId){
	gCurrBookTitle=strBookTitle;
	gCurrBookId=strBookId;
	wizard_palicannon_heading_div_cls(1);
	wizard_palicannon_index_render_book(parentLevel,strParent,strBookTitle)
	wizard_palicannon_show_filelist(strBookId);
	explorer_show_res_list(gCurrBookId,-1);
}


var arrResData=null;
function explorer_res_add_to_list(){
	var iCounter=0;
	for(i in arrResData){
		if(arrResData[i].enable){
			pc_res_add_to_download_list(i);
			iCounter++;
		}
	}
	ntf_show("添加"+iCounter+"资源到列表");
	wizard_save_download_list();
}
function set_res_enable(obj,resIndex){
	arrResData[resIndex].enable=obj.checked;
}
	//显示资源列表
function explorer_show_res_list(book,para){
	$.get("get_res_json.php",
	{
		book:book,
		paragraph:para
	},
	function(data,status){
		try{
			arrResData=JSON.parse(data);
		}
		catch(error){
			ntf_show(error+"<br>"+data);
		}
		var html="";
		html += "<input type='checkbox' checked />All";
		html += "<ul>";
		var resCount=0;
		for(var i=0;i<arrResData.length;i++){
			arrResData[i].enable=false;
			html += "<li>";
			if(arrResData[i].type=="share"){
				var edit_link="../studio/project.php?op=open&doc_id="+arrResData[i].id;
				html += "[share]<a href='"+edit_link+"' target='_blank'>"+arrResData[i].title+"</a>-"+arrResData[i].author;
			}
			else if(arrResData[i].type=="mydoc"){
				var edit_link="../studio/project.php?op=open&doc_id="+arrResData[i].id;
				html += "[mydoc]<a href='"+edit_link+"' target='_blank'>"+arrResData[i].title+"</a>-"+arrResData[i].author;  
			}
			else{
				arrResData[i].enable=true;
				var read_link="../reader/?view=para&book="+arrResData[i].book+"&channal="+arrResData[i].album_id+"&par="+arrResData[i].paragraph;
				var check="<input type='checkbox' id='res_check_"+resCount+"' checked onclick=\"set_res_enable(this,"+i+")\" />";
				html+=check+"["+arrResData[i].type+"]<a href='"+read_link+"' target='_blank'>"+arrResData[i].title+"</a>-"+arrResData[i].author;
				resCount++;
			}
			html += "</li>";
		}
		html += "<input type='hidden' id='res_count' value='"+resCount+"' />";
		html += "</ul>";
		$("#para_res_list").html(html);
	});
}

function wizard_palicannon_heading_click(pearent,index){
	var currLevel = getNodeText(gXmlParIndex[index],"level");
	currLevel++;
	wizard_palicannon_heading_div_cls(currLevel);
	wizard_palicanon_heading_change(pearent,index);
	wizard_palicanon_heading_change(index,-1);
	book_res_win_close();//关闭资源小窗口
	document.getElementById('wizard_palicannon_par_select_toc').innerHTML=wizard_palicannon_renderBookToc(index);

	explorer_show_res_list(gCurrBookId,getNodeText(gXmlParIndex[index],"par"));

}

function wizard_palicannon_heading_div_cls(from){
	for(var iPar=from;iPar<=8;iPar++){
	document.getElementById("id_wizard_palicannon_book_h"+iPar).innerHTML="";
	}
}

function wizard_palicannon_palitext_div_cls(){
	document.getElementById("wizard_palicannon_par_select_toc").innerHTML="";
	document.getElementById("wizard_palicannon_par_select_text_body").innerHTML="";
	document.getElementById("wizard_palicannon_par_select_text_head").style.display="none";

}

//目录改变
function wizard_palicanon_heading_change(base=-1,select=-1){

	var output="";
	var maxLevel=100;
	var endOfPar = wizard_palicanon_get_par_end_index(base);
	if(base==-1){
		endOfPar = gXmlParIndex.length-1
		wizard_palicannon_heading_div_cls(1);
	}
	else{
		var baseLevel = getNodeText(gXmlParIndex[base],"level");
		wizard_palicannon_heading_div_cls(baseLevel+1);
	}
	
		for(var iPar=base+1;iPar<=endOfPar;iPar++){
			parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
			if(parHeadingLevel>0 && parHeadingLevel<maxLevel){
				maxLevel = parHeadingLevel;
			}
		}
		for(var iPar=base+1;iPar<=endOfPar;iPar++){
			parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
			if(parHeadingLevel==maxLevel){
				title = getNodeText(gXmlParIndex[iPar],"title");
				newTitle=wizard_ger_toc_title(iPar,gTocCurrLanguage);
				if(newTitle!=null){
					title=newTitle;
				}
				var cssSelected="";
				if(iPar==select){
					cssSelected="selected";
				}
				output += "<div class=\"pali_book_item "+cssSelected+"\" onclick=\"wizard_palicannon_heading_click("+base+","+iPar+")\">"+title+"</div>";
				parNum=getNodeText(gXmlParIndex[iPar],"par");
				
			}
		}
		
	obj=document.getElementById("id_wizard_palicannon_book_h"+maxLevel);
	if(obj){
		obj.innerHTML=output;
	}
	
	//updata res infomation div
	
	//当改变当前标题时 更新资源列表
	wizard_palicannon_updata_res_info(base);
	//当改变当前标题时 隐藏不需要显示的巴利文本
	wizard_palicanon_updata_pali_text(base);

}

//当改变当前标题时 更新资源列表
function wizard_palicannon_updata_res_info(base=-1){
	if(base==-1){
		var strTitle = "《"+gCurrBookTitle+"》";
		var iParNo = -1;
	}
	else{
		var strTitle = gTocList[base].title;
		var iParNo = gTocList[base].parNum;
	}
	document.getElementById("wizard_palicannon_par_select_text_head_bookname").innerHTML=strTitle;
	$("#wizard_palicannon_par_select_text_head_res").html(wizard_palicannon_render_res_list_onepart(base,1));

}

//当改变当前标题时 隐藏不需要显示的巴利文本
function wizard_palicanon_updata_pali_text(base=-1){
	if(base==-1){
		for(i in gTocList){
			parObj=document.getElementById("wizard_pali_par_"+gTocList[i].parNum);
			if(parObj){
				parObj.style.display="block";
			}
		}
	}
	else{
		iBegin=base;
		iEnd=wizard_palicanon_get_par_end_index(iBegin);
		for(var i=0;i<iBegin;i++){
			document.getElementById("wizard_pali_par_"+gTocList[i].parNum).style.display="none";
		}
		for(var i=iBegin;i<=iEnd;i++){
			document.getElementById("wizard_pali_par_"+gTocList[i].parNum).style.display="block";
		}
		if(iEnd<gTocList.length-1){
			for(var i=iEnd+1;i<gTocList.length;i++){
				document.getElementById("wizard_pali_par_"+gTocList[i].parNum).style.display="none";
			}		
		}

	}
	
}

//当段落没有勾选时变成灰色
function wizard_palicannon_updata_pali_par_text_enable(parNum,enable){
	if(document.getElementById("wizard_pali_par_"+parNum)){
		if(enable){
			document.getElementById("wizard_pali_par_text_"+parNum).style.color="#000";
		}
		else{
			document.getElementById("wizard_pali_par_text_"+parNum).style.color="#d6d6d6";
		}
	}
}

//从服务器获取书的目录
var wizard_palicannon_xmlhttp;
function wizard_palicannon_show_filelist(strBook){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		wizard_palicannon_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		wizard_palicannon_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	gCurrSelectedBook=strBook;
	var d=new Date();
	wizard_palicannon_xmlhttp.onreadystatechange=wizard_palicannon_serverResponse;
	wizard_palicannon_xmlhttp.open("GET","./pc_get_book_index.php?t="+d.getTime()+"&book="+strBook,true);
	wizard_palicannon_xmlhttp.send();
}

function wizard_palicannon_serverResponse(){
	if (wizard_palicannon_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (wizard_palicannon_xmlhttp.status==200)
		{// 200 = "OK"
			var parList="";
			var xmlText = wizard_palicannon_xmlhttp.responseText;
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
			//get book TOC array
			gXmlParIndex = xmlBookIndex.getElementsByTagName("paragraph");
			gTocList=new Array();
			for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
				tocItem=new Object();		
				tocItem.book=getNodeText(gXmlParIndex[iPar],"book");
				tocItem.parNum=getNodeText(gXmlParIndex[iPar],"par");
				tocItem.level=getNodeText(gXmlParIndex[iPar],"level");
				tocItem.type=getNodeText(gXmlParIndex[iPar],"class");
				tocItem.title=getNodeText(gXmlParIndex[iPar],"title");
				tocItem.author=getNodeText(gXmlParIndex[iPar],"author");		
				tocItem.language=getNodeText(gXmlParIndex[iPar],"language");
				tocItem.edition=getNodeText(gXmlParIndex[iPar],"edition");
				tocItem.subver=getNodeText(gXmlParIndex[iPar],"subver");		
				tocItem.enable=true;
				tocItem.visible=true;
				gTocList.push(tocItem);
			}
			//目录数组创建结束
			book_res_win_close();//关闭资源小窗口
			gResList=new Array();
			document.getElementById('wizard_palicannon_par_select_toc').innerHTML=wizard_palicannon_renderBookToc();
			//获取巴利原文
			wizard_palicannon_pali_text_query(gCurrSelectedBook);
			

			wizard_get_toc_from_res();
			wizard_palicanon_heading_change();
		}
		else
		{
			document.getElementById('id_wizard_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}
/*
//添加模板资源到资源列表
function wizard_palicannon_add_res_wbw_templet(){
	for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		parNum=getNodeText(gXmlParIndex[iPar],"par");
		if(parHeadingLevel>0){
		var newItem=new Object();
		newItem.type="wbw";
		newItem.book=getNodeText(gXmlParIndex[iPar],"book");
		newItem.parNum=getNodeText(gXmlParIndex[iPar],"par");
		newItem.parEnd=wizard_palicannon_get_par_end(newItem.parNum);
		newItem.parCurrLoading=newItem.parNum;
		newItem.author="templet";
		newItem.editor="templet";
		newItem.revision="";
		newItem.language="com";
		newItem.edition="1";
		newItem.subver="0";
		newItem.title=getNodeText(gXmlParIndex[iPar],"title");
		gResList.push(newItem);
		}
	}
}

//添加模板资源到资源列表
function wizard_palicannon_add_res_toc_templet(){
	for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		parNum=getNodeText(gXmlParIndex[iPar],"par");
		if(parHeadingLevel>0){
		var newItem=new Object();
		newItem.type="heading";
		newItem.book=getNodeText(gXmlParIndex[iPar],"book");
		newItem.parNum=getNodeText(gXmlParIndex[iPar],"par");
		newItem.parEnd=wizard_palicannon_get_par_end(newItem.parNum);
		newItem.parCurrLoading=newItem.parNum;
		newItem.author="templet";
		newItem.editor="templet";
		newItem.revision="";
		newItem.language="pali";
		newItem.edition="1";
		newItem.subver="0";
		newItem.title=getNodeText(gXmlParIndex[iPar],"title");
		gResList.push(newItem);
		}
	}

}
*/
function wizard_palicannon_nav_level_change(obj){
	var iLevle=obj.value;
	iLevle++;
	wizard_palicannon_nav_level_show(iLevle-1);
}

//渲染书的目录
function wizard_palicannon_renderBookToc(root=-1){
	gTocCurrRoot = root;
	
	var output="";
	var tocBegin=-1;
	var tocEnd=-1;
	var beginLevel=1;
	
	if(root==-1){
		tocBegin=0;
		tocEnd=gTocList.length-1;
	}
	else{
		tocBegin=root;
		tocEnd = wizard_palicanon_get_par_end_index(tocBegin);
		beginLevel=gTocList[tocBegin].level;
	}
	//目录上面的层级显示选项
	output += "<select onchange=\"wizard_palicannon_nav_level_change(this)\">";
	for(var iSelect=beginLevel;iSelect<8;iSelect++){
		output += "<option value=\""+iSelect+"\">"+gLocal.gui.level+iSelect+"</option>";
	}
	output += "<option selected value=\""+8+"\">"+gLocal.gui.level+8+"</option>";
	output += "</select>";
	
	//目录上面的语言显示选项
	output += "<select onchange=\"wizard_palicannon_nav_language_change(this)\">";
	for(var iLanguage=0;iLanguage<gTocLanguage.length;iLanguage++){
		if(gTocLanguage[iLanguage]==gTocCurrLanguage){
			var isSelect="selected";
		}
		else{
			var isSelect="";
		}
		output += "<option "+isSelect+" value=\""+gTocLanguage[iLanguage]+"\">"+gTocLanguage[iLanguage]+"</option>";
	}
	output += "</select>";
	//开始生成目录代码
	output += "<ul>";
	for(var iPar=tocBegin;iPar<=tocEnd;iPar++){
		parTitle=gTocList[iPar].title;
		parHeadingLevel=gTocList[iPar].level;
		parNum=gTocList[iPar].parNum;
		if(parTitle==""){
			parTitle=parNum;
		}
		if(parHeadingLevel>=0){	
			output  += "<li class=\"palicannon_nav_level_" + parHeadingLevel + "\" id=\"id_pc_nav_par_"+iPar+"\">";
			if(parHeadingLevel>0){	
			output += "<span id=\"id_pc_nav_ex_"+iPar+"\" class=\"tree_expand_"+parHeadingLevel+"\" onclick=\"tree_co("+iPar+")\">▼</span>"
			output += "<span id=\"id_pc_nav_co_"+iPar+"\" class=\"tree_collapse_"+parHeadingLevel+"\" onclick=\"tree_expand("+iPar+")\">▶</span>";		
			}
			//勾选段落有效性 Index
			output += "<input id='toc_par_enable_"+iPar+"' onclick='wizard_toc_par_enable(this,"+iPar+")' type=\"checkbox\" checked/>";
			if(parHeadingLevel>0){
				newTitle=wizard_ger_toc_title(iPar,gTocCurrLanguage);
				if(newTitle!=null){
				parTitle=newTitle;
				}
			}
			tocLink = "<a href=\"#pali_text_par_"+parNum+"\" class=\"palicannon_nav_item\">" + parTitle +"</a>";
			
			if(parHeadingLevel>0){
				output  += "<span  class=\"tooltip_menu\">"+tocLink+"<span class=\"tooltiptext tooltip_menu-bottom\"><a onclick=\"palicannon_par_toc_reset_root("+iPar+")\">进入</a><a onclick=\"palicannon_par_res_show_window("+iPar+")\">详情</a></span></span> ";	
			}
			else{
				output +=tocLink;
			}
			output  += "</li>";
		}
		
	}
	output += "</ul>";

	return output;
}
//目录语言选择
function pc_res_toc_language_change(obj){
}

function add_res_to_doc(resType,parNum){
	var iStartPar=0
	var iStartLevel=0
	for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
		currParNum=getNodeText(gXmlParIndex[iPar],"par");
		if(currParNum==parNum){
			iStartPar=iPar;
			iStartLevel=getNodeText(gXmlParIndex[iPar],"level");
			break;
		}
	}
	for(var iPar=iStartPar+1;iPar<gXmlParIndex.length;iPar++){
		parLevel=getNodeText(gXmlParIndex[iPar],"level");
		if(parLevel>0){
			if(parLevel <= iStartLevel){
				iEndPar=getNodeText(gXmlParIndex[iPar],"par")
				palicannon_loadStream(gCurrSelectedBook,parNum,iEndPar)
				return;
			}
		}
	}
	
}

function tree_co(pid){
	currLevel=getNodeText(gXmlParIndex[pid],"level");
	document.getElementById("id_pc_nav_ex_"+pid).style.display="none"
	document.getElementById("id_pc_nav_co_"+pid).style.display="inline"
	for(var iPar=pid+1;iPar<gXmlParIndex.length;iPar++){
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		if(parHeadingLevel>0){
			if(parHeadingLevel > currLevel){
				document.getElementById("id_pc_nav_par_"+iPar).style.display="none"
			}
			else{
				return;
			}
		}
		else{
			document.getElementById("id_pc_nav_par_"+iPar).style.display="none"			
		}
	}
}
function tree_expand(pid){
	currLevel=getNodeText(gXmlParIndex[pid],"level");
	document.getElementById("id_pc_nav_ex_"+pid).style.display="inline"
	document.getElementById("id_pc_nav_co_"+pid).style.display="none"
	for(var iPar=pid+1;iPar<gXmlParIndex.length;iPar++){
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		if(parHeadingLevel>0){
			if(parHeadingLevel > currLevel){
				document.getElementById("id_pc_nav_par_"+iPar).style.display="block"
			}
			else{
				return;
			}
		}
		else{
				document.getElementById("id_pc_nav_par_"+iPar).style.display="block"
		}
	}
}

//获取pali原文
var wizard_palicannon_pali_text_xmlhttp;
function wizard_palicannon_pali_text_query(bookId){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		wizard_palicannon_pali_text_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		wizard_palicannon_pali_text_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
	wizard_palicannon_pali_text_xmlhttp.onreadystatechange=wizard_palicannon_pali_text_serverResponse;
	wizard_palicannon_pali_text_xmlhttp.open("GET","./pc_get_pali_text.php?book="+bookId,true);
	wizard_palicannon_pali_text_xmlhttp.send();
}

//获取巴利原文
function wizard_palicannon_pali_text_serverResponse(){
	if (wizard_palicannon_pali_text_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (wizard_palicannon_pali_text_xmlhttp.status==200)
		{// 200 = "OK"
			var xmlText = wizard_palicannon_pali_text_xmlhttp.responseText;
			$("#wizard_palicannon_par_select_text_body").html(xmlText);
			$("#wizard_palicannon_par_select_text_head").show();

			$("a[name]").each(function(){
				let name = $(this).attr("name");
				if(name.slice(0,1)=="M"){
					$(this).css("background-color","rgb(255 255 0 / 42%)");
					$(this).css("position","absolute");
					$(this).css("right","0");
					$(this).html(name);
				}
			});			
		}
		else
		{
			document.getElementById('wizard_palicannon_par_select_text_body').innerHTML="Problem retrieving data:" + wizard_palicannon_pali_text_xmlhttp.statusText;

		}
	}
}

/*
//get res list from database
function wizard_palicannon_get_res_list(){
	gCurrQueryResType=0;
	wizard_palicannon_res_list_query(gResTypeList[gCurrQueryResType],gCurrSelectedBook);
}

//从服务器获取资源列表
var wizard_palicannon_res_xmlhttp;
function wizard_palicannon_res_list_query(resType,bookId){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		wizard_palicannon_res_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		wizard_palicannon_res_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
	var parList=wizard_palicannon_get_par_list();
	wizard_palicannon_res_xmlhttp.onreadystatechange=wizard_palicannon_res_serverResponse;
	wizard_palicannon_res_xmlhttp.open("GET","pc_get_res_list.php?t="+d.getTime()+"&book="+bookId+"&res_type="+resType+"&par_list="+parList,true);
	wizard_palicannon_res_xmlhttp.send();
}

//收到资源列表
function wizard_palicannon_res_serverResponse(){
	if (wizard_palicannon_res_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (wizard_palicannon_res_xmlhttp.status==200)
		{// 200 = "OK"

			var parList="";
			var xmlText = wizard_palicannon_res_xmlhttp.responseText;
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlBookRes=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlBookRes=new ActiveXObject("Microsoft.XMLDOM");
				  xmlBookRes.async="false";
				  xmlBookRes.loadXML(xmlText);
				  }
			  
				if (xmlBookRes == null){
					alert("error:can not load book res.");
					return;
				}
				gXmlResList = xmlBookRes.getElementsByTagName("res");
				
				add_new_res_list(gXmlResList);
				//如果还有等待加载的资源 继续加载
				if(gCurrQueryResType<gResTypeList.length-1){
					gCurrQueryResType++;
					wizard_palicannon_res_list_query(gResTypeList[gCurrQueryResType],gCurrSelectedBook);
				}
				else{
				//如果没有了 显示资源列表
					//wizard_palicannon_render_res_list();
					wizard_get_toc_from_res();
					document.getElementById('wizard_palicannon_par_select_toc').innerHTML=wizard_palicannon_renderBookToc(gTocCurrRoot);
				}
		}
		else
		{
			document.getElementById('id_wizard_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}
*/

//添加新的资源 到 内存数组
function add_new_res_list(xNewList){
	for(var iItem=0;iItem<xNewList.length;iItem++){
		var newItem=new Object();
		newItem.type=getNodeText(xNewList[iItem],"type");
		newItem.book=getNodeText(xNewList[iItem],"book");
		newItem.parNum=getNodeText(xNewList[iItem],"par");
		newItem.parEnd=wizard_palicannon_get_par_end(newItem.parNum);
		newItem.parCurrLoading=newItem.parNum;
		newItem.author=getNodeText(xNewList[iItem],"author");
		newItem.editor=getNodeText(xNewList[iItem],"editor");
		newItem.revision=getNodeText(xNewList[iItem],"revision");
		newItem.language=getNodeText(xNewList[iItem],"language");
		newItem.edition=getNodeText(xNewList[iItem],"edition");
		newItem.subver=getNodeText(xNewList[iItem],"subver");
		newItem.text=getNodeText(xNewList[iItem],"text");
		newItem.title=getNodeText(xNewList[iItem],"text");
		gResList.push(newItem);
	}
}


function wizard_palicannon_get_par_list(){
	var output="";
	for(var iPar=0;iPar<gXmlParIndex.length;iPar++){
		parTitle=getNodeText(gXmlParIndex[iPar],"title");
		parHeadingLevel=getNodeText(gXmlParIndex[iPar],"level");
		parNum=getNodeText(gXmlParIndex[iPar],"par");
		if(parHeadingLevel>0){
			output+=parNum+","
		}
	}
	return(output.slice(0,-1))
}


//获取段落终止点 
//输入：索引
//输出：索引
function wizard_palicanon_get_par_end_index(beginIndex){
	var iStartPar=0
	var iStartLevel=0
	if(beginIndex==-1){
		return(gTocList.length-1);
	}
	if(gTocList[beginIndex].level==0){
		return(beginIndex);
	}
	for(var iPar=beginIndex+1;iPar<gTocList.length;iPar++){
		parLevel=gTocList[iPar].level;
		if(parLevel>0){
			if(parLevel <= gTocList[beginIndex].level){
				return(iPar-1);
			}
		}
	}
	//没找到 返回数组最后一个索引号
	return(gTocList.length-1);
}

function wizard_palicannon_get_par_end(beginParNum){
	var iStartPar=0
	var iStartLevel=0
	for(var iPar=0;iPar<gTocList.length;iPar++){
		currParNum=gTocList[iPar].parNum;
		if(currParNum==beginParNum){
			iStartPar=iPar;
			break;
		}
	}
	var iEnd = wizard_palicanon_get_par_end_index(iStartPar);
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
	return(gTocList[iEnd].parNum);
}

function book_res_edit_now(resWin){
	var resNum = book_res_add_to_list(resWin);
	if(resNum>0){
		open_editor_load_stream();
	}

}

//加入到下载列表
//resWin=1 主列表  resWin=2 浮动列表
function book_res_add_to_list(resWin){
	var iCounter=0;
	switch(resWin){
		case 1:
		currList=gCurrResListArray;
		break;
		case 2: //for float windows
		currList=gCurrResListArray2;
		break;
		default:
		return;
	}	
	if(currList){
		for(i in currList){
			if(currList[i].enable){
				pc_res_add_to_download_list(currList[i].res);
				iCounter++;
			}
		}
	}
	
	return(iCounter);
}

function pali_canon_edit_now(thisform)
{
	var download_res_data=new Array();
	for(i in arrResData){
		if(arrResData[i].enable){
			var resDownloadItem=new Object();
			resDownloadItem.album_id=arrResData[i].album_id;
			resDownloadItem.type=arrResData[i].type;
			resDownloadItem.book=arrResData[i].book;
			resDownloadItem.parNum=arrResData[i].paragraph;
			resDownloadItem.author=arrResData[i].author;
			resDownloadItem.editor=arrResData[i].editor;
			resDownloadItem.language=arrResData[i].language;
			resDownloadItem.edition=arrResData[i].edition;
			resDownloadItem.version=arrResData[i].version;
			resDownloadItem.title=arrResData[i].title;
			
			var strParList="";
			var firstIndex=-1;
			var firstLevel=-1;
			var endIndex=-1
			//计算段落区间
			for(var iPar=0;iPar<gTocList.length;iPar++){
				if(resDownloadItem.parNum==gTocList[iPar].parNum){
					firstIndex=iPar;
					firstLevel=gTocList[iPar].level;
					break;
				}
			}
			for(var iPar=firstIndex+1;iPar<gTocList.length;iPar++){
				var newLevel=gTocList[iPar].level;
				if(newLevel>0 && newLevel<=firstLevel){
					endIndex=iPar-1;
					break;
				}
			}
			if(firstIndex==-1){
				return;
			}
			if(endIndex==-1){
				endIndex=gTocList.length-1;
			}
			//查找被选择的段落
			for(var iPar=firstIndex;iPar<=endIndex;iPar++){
				if(gTocList[iPar].enable){
					strParList+=gTocList[iPar].parNum;
					if(iPar<endIndex){
						strParList += ",";
					}
				}
			}
			
			resDownloadItem.parlist=strParList;

			download_res_data.push(resDownloadItem);

		}
	}

	if(download_res_data.length>0){
		$("#project_new_res_data").val(JSON.stringify(download_res_data));
		return true;
	}
	else{
		return false;
	}

}

//勾选资源项目
//resWin=1 主列表  resWin=2 浮动列表
function setResEnable(obj,index,resWin){
	switch(resWin){
		case 1:
		gCurrResListArray[index].enable=obj.checked;
		break;
		case 2: //for float windows
		gCurrResListArray2[index].enable=obj.checked;
		break;
		default:
		return;
	}
	
}

//渲染资源内容
//resWin=1 主列表  resWin=2 浮动列表
function wizard_palicannon_render_res_list_onepart(parIndex,resWin){
	if(parIndex>=0){
		var parNo=gTocList[parIndex].parNum;
		var paraEndIndex=wizard_palicanon_get_par_end_index(parIndex);
		var parNumber=gTocList[paraEndIndex].parNum-gTocList[parIndex].parNum+1;
	}
	else{
		var parNo=-1;
		var parNumber=0;
	}
	
	switch(resWin){
		case 1:
		gCurrResListArray=new Array();
		break;
		case 2: //for float windows
		gCurrResListArray2=new Array();
		break;
		default:
		return;
	}
		
	var iResCounter=0;
	
	var output="<div class=\"resouse_info\">";

	output += "</div>";
	
	output += "<div class=\"chapter_info\">"+gLocal.gui.statistical_data+"<br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.totally+parNumber+gLocal.gui.para+"</span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.wordnum+"</span><span id=\"pc_res_par_word_num\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.vocabulary+"</span><span id=\"pc_res_par_vocabulary\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.word_length+"：</span><span id=\"pc_res_par_word_length\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.repeat+"</span><span id=\"pc_res_par_quotiety\"></span><br/>";
	output += "</div>";
	output += "<div class=\"chapter_info\" style='margin-right:0'>"+gLocal.gui.workload+"<br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.tran_workload+"</span><span id=\"pc_res_par_tran_workload\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.wbw+"：</span><span id=\"pc_res_par_wbw_workload\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.total_workload+"：</span><span id=\"pc_res_par_total_workload\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.product+"：</span><span id=\"pc_res_par_product\"></span><br/>";
	output += "<span style=\"width: 12em;\">"+gLocal.gui.difficulty+"：</span><span id=\"pc_res_par_difficulty\"></span>";
	output += "</div>";
	if(parIndex>=0){
		wizard_palicannon_res_word_num_query(gTocList[parIndex].book,gTocList[parIndex].parNum,gTocList[wizard_palicanon_get_par_end_index(parIndex)].parNum);
	}
	else{
		wizard_palicannon_res_word_num_query(gTocList[0].book,0,-1);
	}
	return(output);
}


//添加新的资源到下载列表
function pc_res_add_to_download_list(resIndex)
{
	set_pali_loader_visible(true);

	var resDownloadItem=new Object();
	resDownloadItem.album_id=arrResData[resIndex].album_id;
	resDownloadItem.resIndex=resIndex;
	resDownloadItem.res=arrResData[resIndex].type;
	resDownloadItem.type=arrResData[resIndex].type;
	resDownloadItem.book=arrResData[resIndex].book;
	resDownloadItem.parNum=arrResData[resIndex].paragraph;
	resDownloadItem.parEnd = 0;
	resDownloadItem.author=arrResData[resIndex].author;
	resDownloadItem.editor=arrResData[resIndex].editor;
	resDownloadItem.revision=1;
	resDownloadItem.language=arrResData[resIndex].language;
	resDownloadItem.edition=arrResData[resIndex].edition;
	resDownloadItem.subver=1;
	resDownloadItem.title=arrResData[resIndex].title;
	
	var strParList="";
	var firstIndex=-1;
	var firstLevel=-1;
	var endIndex=-1
	//计算段落区间
	for(var iPar=0;iPar<gTocList.length;iPar++){
		if(resDownloadItem.parNum==gTocList[iPar].parNum){
			firstIndex=iPar;
			firstLevel=gTocList[iPar].level;
			break;
		}
	}
	for(var iPar=firstIndex+1;iPar<gTocList.length;iPar++){
		var newLevel=gTocList[iPar].level;
		if(newLevel>0 && newLevel<=firstLevel){
			endIndex=iPar-1;
			break;
		}
	}
	if(firstIndex==-1){
		return;
	}
	if(endIndex==-1){
		endIndex=gTocList.length-1;
	}
	//查找被选择的段落
	for(var iPar=firstIndex;iPar<=endIndex;iPar++){
		if(gTocList[iPar].enable){
			strParList+=gTocList[iPar].parNum;
			if(iPar<endIndex){
				strParList += ",";
			}
		}
	}
	
	resDownloadItem.parlist=strParList;

	//查询是否有重复
	var isInList=false;
	for(i in gResDownloadList){
		if(gResDownloadList[i].parNum==resDownloadItem.parNum && gResDownloadList[i].album_id==resDownloadItem.album_id ){
			isInList = true;
			break;
		}
	}
	//无重复 加载
	if(!isInList){
		gResDownloadList.push(resDownloadItem);
		pc_res_updata_download_list();
	}
}

function remove_pc_res_download_list_cookie(){
	gDownloadListString="";
	setCookie("loadlist","","1");
}

function add_pc_res_download_list_to_cookie(){
	var cookieString = "{\"loadlist\": [";
	for(i in gResDownloadList){
		cookieString  += "{";
		cookieString  += "\"resIndex\":\""+gResDownloadList[i].resIndex+"\" ,";
		cookieString  += "\"res\":\""+gResDownloadList[i].res+"\" ,";
		cookieString  += "\"book\":\""+gResDownloadList[i].book+"\" ,";
		cookieString  += "\"parNum\":\""+gResDownloadList[i].parNum+"\" ,";
		cookieString  += "\"parEnd\":\""+gResDownloadList[i].parEnd+"\" ,";
		cookieString  += "\"author\":\""+gResDownloadList[i].author+"\" ,";
		cookieString  += "\"editor\":\""+gResDownloadList[i].editor+"\" ,";
		cookieString  += "\"revision\":\""+gResDownloadList[i].revision+"\" ,";
		cookieString  += "\"language\":\""+gResDownloadList[i].language+"\" ,";
		cookieString  += "\"edition\":\""+gResDownloadList[i].edition+"\" ,";
		cookieString  += "\"subver\":\""+gResDownloadList[i].subver+"\" ,";
		cookieString  += "\"parlist\":\""+gResDownloadList[i].parlist+"\" ,";
		cookieString  += "\"title\":\""+gResDownloadList[i].title+"\"";
		cookieString  += "}";
		if(i<gResDownloadList.length-1){
			cookieString  += ",";
		}
	}	
	cookieString  += "]}";
	gDownloadListString=cookieString;
	setCookie("loadlist",cookieString,"1");
}

function get_pc_res_download_list_from_cookie(){
	var resListString=getCookie("loadlist");
	objCookieLoadList = JSON.parse(resListString);
	ntf_show(resListString);
	gResDownloadList = new Array();
	for(i in objCookieLoadList.loadlist){
		gResDownloadList.push(objCookieLoadList.loadlist[i]);
	}
	pc_res_updata_download_list();
	
}

function get_pc_res_download_list_from_string(strDownload){
	objCookieLoadList = JSON.parse(strDownload);
	//debugOutput(resListString);
	gResDownloadList = new Array();
	for(i in objCookieLoadList.loadlist){
		gResDownloadList.push(objCookieLoadList.loadlist[i]);
	}
	pc_res_updata_download_list();
	
}

//显示资源预览
function pc_res_preview(resIndex){
		res=gResList[resIndex].type;
		book=gResList[resIndex].book;
		thisParNum=gResList[resIndex].parNum;
		thisParEnd = gResList[resIndex].parEnd;
		author=gResList[resIndex].author;
		editor=gResList[resIndex].editor;
		revision=gResList[resIndex].revision;
		language=gResList[resIndex].language;
		edition=gResList[resIndex].edition;
		subver=gResList[resIndex].subver;
		
		var link="./pc_get_res_preview.php?res_type="+res+"&book="+book+"&begin="+thisParNum+"&end="+thisParEnd+"&author="+author+"&editor="+editor+"&revision="+revision+"&language="+language+"&edition="+edition+"&subver="+subver;
	
	return(link);
}

//删除加载列表中的一项
function pc_resListRemove(indexDelete){
	gResDownloadList.splice(indexDelete,1);
	pc_res_updata_download_list();
	wizard_save_download_list();
}
//清空购物车
function pc_empty_download_list(){
	gResDownloadList=new Array();
	pc_res_updata_download_list();
	wizard_save_download_list();
}

function pc_resListMove(moveFrom,moveTo){
	if(moveTo<0){
		moveTo=0;
	}
	if(moveFrom==moveTo){
		return;
	}
	var temp=gResDownloadList[moveTo];
	gResDownloadList[moveTo]=gResDownloadList[moveFrom];
	for(i=moveFrom-1;i>moveTo;i--){
		gResDownloadList[i+1]=gResDownloadList[i];
	}	
	gResDownloadList[moveTo+1]=temp;	
	pc_res_updata_download_list();
	wizard_save_download_list();
}
function getBookTitleById(bookId){
	for(index in local_palicannon_index){
		if(local_palicannon_index[index].id==bookId){
			return(local_palicannon_index[index].title)
		}
	}
	return("");
}

function pc_res_updata_download_list(){
	var resListString=""
	
	for(var iRes=0;iRes<gResDownloadList.length;iRes++){
		var type=gResDownloadList[iRes].type;
		var book=gResDownloadList[iRes].book;
		var parNum=gResDownloadList[iRes].parNum;
		var parEnd=gResDownloadList[iRes].parEnd;
		var author=gResDownloadList[iRes].author;
		var editor=gResDownloadList[iRes].editor;
		var revision=gResDownloadList[iRes].revision;
		var language=gResDownloadList[iRes].language;
		var edition=gResDownloadList[iRes].edition;
		var subver=gResDownloadList[iRes].subver;
		var title=gResDownloadList[iRes].title;
		var paraList=gResDownloadList[iRes].parlist;

		var parCount=paraList.split().length;

		resListString += "<div class=\"res_item\">"
		resListString += "	<table>"
		resListString += "		<tr>"
		resListString += "			<td class=\"tool_bar\">"
		resListString += "		<p class=\"res_button\" onclick=\"pc_resListMove("+iRes+","+(iRes-1)+")\">▲</p>"
		resListString += "		<p class=\"res_type\" >W</p>"
		resListString += "		<p class=\"res_button\" onclick=\"pc_resListRemove("+iRes+")\">"
		resListString += "			<svg id=\"icon_Edit_Dialog_on\" style=\"display:block;\" class=\"icon\">"
		resListString += "				<use xmlns:xlink=\"http://www.w3.org/1999/xlink\" xlink:href=\"svg/icon.svg#ic_remove_shopping_cart\"></use>"
		resListString += "			</svg>"
		resListString += "		</p>"
		resListString += "			</td>"
		resListString += "			<td class=\"res_info\" >"
		resListString += "		<div class=\"res_info_1\"><span class=\"book_name\">"+gCurrBookType+"-《"+getBookTitleById("p"+book)+"》</span><br/>";
		resListString += "		  <span class=\"chapter\">"+title+"|</span><span class=\"author\">"+gLocal.gui.translate1+author+"</span></div>"
		resListString += "	 	<div class=\"res_info_2\">|"+"<span>"+local_media_type[type]+"</span>|"+gLocal.gui.language+language+"|"+gLocal.gui.totally+parCount+gLocal.gui.para+"|"+gLocal.gui.edit1+editor+"|"+gLocal.gui.revision+revision+"|"+gLocal.gui.edition+edition+"|</div>"
		resListString += "			</td>"
		resListString += "		</tr>"
		resListString += "	</table>"
		resListString += "<canvas class=\"res_load_progress_canvas\" id=\"book_res_load_progress_canvas_"+iRes+"\" width='300' height='5'></canvas>"

		resListString += "</div>"
	}

	document.getElementById("pc_res_list_div").innerHTML=resListString;
	
	add_pc_res_download_list_to_cookie();
	
	if(gResDownloadList && gResDownloadList.length>0){
		if(obj=document.getElementById("id_open_editor_load_stream")){obj.disabled=false;}
		if(obj=document.getElementById("id_append_stream")){obj.disabled=false;}
		if(obj=document.getElementById("pc_empty_download_list")){obj.disabled=false;}
		if(obj=document.getElementById("id_cancel_stream")){obj.disabled=false;}
		
	}
	else{
		if(obj=document.getElementById("id_open_editor_load_stream")){obj.disabled=true;}
		if(obj=document.getElementById("id_append_stream")){obj.disabled=true;}
		if(obj=document.getElementById("pc_empty_download_list")){obj.disabled=true;}
		if(obj=document.getElementById("id_cancel_stream")){obj.disabled=true;}
	}

}


function wizard_palicannon_nav_level_show(showLevel){
	getStyleClass('palicannon_nav_level_0').style.display="none";

	getStyleClass('palicannon_nav_level_1').style.display="none";
	getStyleClass('palicannon_nav_level_2').style.display="none";
	getStyleClass('palicannon_nav_level_3').style.display="none";
	getStyleClass('palicannon_nav_level_4').style.display="none";
	getStyleClass('palicannon_nav_level_5').style.display="none";
	getStyleClass('palicannon_nav_level_6').style.display="none";
	getStyleClass('palicannon_nav_level_7').style.display="none";
	getStyleClass('palicannon_nav_level_8').style.display="none";

	switch(showLevel){
		case 0:
			getStyleClass('palicannon_nav_level_0').style.display="block";
			//getStyleClass('tree_expand_0').style.display="inline";
			//getStyleClass('tree_collapse_0').style.display="none";
		case 8:
			getStyleClass('palicannon_nav_level_8').style.display="block";
			getStyleClass('tree_expand_8').style.display="inline";
			getStyleClass('tree_collapse_8').style.display="none";
		case 7:
			getStyleClass('palicannon_nav_level_7').style.display="block";
			getStyleClass('tree_expand_7').style.display="inline";
			getStyleClass('tree_collapse_7').style.display="none";
		case 6:
			getStyleClass('palicannon_nav_level_6').style.display="block";
			getStyleClass('tree_expand_6').style.display="inline";
			getStyleClass('tree_collapse_6').style.display="none";
		case 5:
			getStyleClass('palicannon_nav_level_5').style.display="block";
			getStyleClass('tree_expand_5').style.display="none";
			getStyleClass('tree_collapse_5').style.display="inline";
		case 4:
			getStyleClass('palicannon_nav_level_4').style.display="block";
			getStyleClass('tree_expand_4').style.display="inline";
			getStyleClass('tree_collapse_4').style.display="none";
		case 3:
			getStyleClass('palicannon_nav_level_3').style.display="block";
			getStyleClass('tree_expand_3').style.display="inline";
			getStyleClass('tree_collapse_3').style.display="none";
		case 2:
			getStyleClass('palicannon_nav_level_2').style.display="block";
			getStyleClass('tree_expand_2').style.display="inline";
			getStyleClass('tree_collapse_2').style.display="none";			

		case 1:
			getStyleClass('palicannon_nav_level_1').style.display="block";
			getStyleClass('tree_expand_1').style.display="inline";
			getStyleClass('tree_collapse_1').style.display="none";
	}
	
	switch(showLevel){
		case 8:
			getStyleClass('tree_expand_8').style.display="none";
			getStyleClass('tree_collapse_8').style.display="inline";
		break;
		case 7:
			getStyleClass('tree_expand_7').style.display="none";
			getStyleClass('tree_collapse_7').style.display="inline";
		break;
		case 6:
			getStyleClass('tree_expand_6').style.display="none";
			getStyleClass('tree_collapse_6').style.display="inline";
		break;
		case 5:
			getStyleClass('tree_expand_5').style.display="none";
			getStyleClass('tree_collapse_5').style.display="inline";
		break;
		case 4:
			getStyleClass('tree_expand_4').style.display="none";
			getStyleClass('tree_collapse_4').style.display="inline";
		break;
		case 3:
			getStyleClass('tree_expand_3').style.display="none";
			getStyleClass('tree_collapse_3').style.display="inline";
		break;
		case 2:
			getStyleClass('tree_expand_2').style.display="none";
			getStyleClass('tree_collapse_2').style.display="inline";
		break;
		case 1:
			getStyleClass('tree_expand_1').style.display="none";
			getStyleClass('tree_collapse_1').style.display="inline";
		break;
		
	}

}

//
/*
 * 在服务器端保存下载列表
 * public
 * @param 
 */
function wizard_save_download_list(){
	var strDownload = JSON.stringify(gResDownloadList);
	$.post("./user_file.php",
	{
		op:"save",
		filename:"dl.json",
		data:strDownload
	},
	function(data,status){
		ntf_show("Data: " + data + "\nStatus: " + status);
	});
	
}

//载入编辑列表
function open_editor_load_stream(){
	window.open("project.php?op=create","_blank");
}
/*
 * load paragraph from database
 * public
 * @param book(string)  book GUID
 * @param parBegin(int) 
 * @param parEnd(int)
 */
function pc_loadStream(resIndex){
	//document.getElementById("wizard_div_palicannon").style.display="none";
	
	gCurrResIndex=resIndex;
	gCurrBook=gResDownloadList[resIndex].book;
	gCurrType=gResDownloadList[resIndex].type;
	
	//找到第一個應該加載的段落
	
	gCurrParBegin=gResDownloadList[resIndex].parNum;
	gCurrParEnd=gResDownloadList[resIndex].parEnd;
	gCurrParList=gResDownloadList[resIndex].parlist;
	gCurrParBegin = find_first_enable_par(gCurrParBegin,gCurrParList);
	gResDownloadList[resIndex].parCurrLoading=gCurrParBegin;
	gCurrLoadPar=gCurrParBegin;
	
	gLoadSteamCanceled=false;
	//清空单词节点数组
	gXmlAllWordInWBW = new Array();
	
	var d=new Date();
	loadSteamBeginTime=d.getTime();

	//关闭单词修改窗口
	closeModifyWindow();
	
	//insertTocToXmlBookHead(parBegin,parEnd);
	pc_load_book_par();
}

function pc_cancelSteam(){
	gLoadSteamCanceled = true;
}



var pc_xmlLoadBookhttp;
function pc_load_book_par(){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		pc_xmlLoadBookhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		pc_xmlLoadBookhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
		res=gResDownloadList[gCurrResIndex].res;
		book=gResDownloadList[gCurrResIndex].book;
		thisParNum=gResDownloadList[gCurrResIndex].parNum;
		thisParEnd = gResDownloadList[gCurrResIndex].parEnd;
		
		gResDownloadList[gCurrResIndex].parCurrLoading=gCurrLoadPar;
		
		author=gResDownloadList[gCurrResIndex].author;
		editor=gResDownloadList[gCurrResIndex].editor;
		revision=gResDownloadList[gCurrResIndex].revision;
		language=gResDownloadList[gCurrResIndex].language;
		edition=gResDownloadList[gCurrResIndex].edition;
		subver=gResDownloadList[gCurrResIndex].subver;
		
		var link="pc_get_book_res.php?t="+d.getTime()+"&res_type="+res+"&book="+book+"&paragraph="+gCurrLoadPar+"&author="+author+"&editor="+editor+"&revision="+revision+"&language="+language+"&edition="+edition+"&subver="+subver;
	
	
	pc_xmlLoadBookhttp.onreadystatechange=pc_load_book_serverResponse;
	pc_xmlLoadBookhttp.open("GET",link,true);
	pc_xmlLoadBookhttp.send();
}

function pc_load_book_serverResponse(){
	if (pc_xmlLoadBookhttp.readyState==4)// 4 = "loaded"
	{
		if (pc_xmlLoadBookhttp.status==200)
		{// 200 = "OK"
			var xmlText = pc_xmlLoadBookhttp.responseText;
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
				insertBlockToXmlBookData(xmlParBlocks[iXml])					
				insertBlockToHtml(xmlParBlocks[iXml])
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
				gCurrLoadPar--;
				
				/*
				var trueLoadPar=-1;
				for(var iSelectPar=gCurrLoadPar+1;iSelectPar<=gCurrParEnd;iSelectPar++){
					var parVisible=document.getElementById("par_enable_"+iSelectPar).checked;
					if(parVisible){
						trueLoadPar=iSelectPar;
						break;
					}
					
				}
				*/
				//gCurrLoadPar=trueLoadPar;
				
				gCurrLoadPar = find_first_enable_par(gCurrLoadPar+1,gCurrParList);

				if(gCurrLoadPar<=gCurrParEnd && gCurrLoadPar!=-1){
					pc_load_book_par(gCurrSelectedBook,gCurrLoadPar,"vri");
					progress=(gCurrLoadPar-gCurrParBegin)/(gCurrParEnd-gCurrParBegin)
					var c=document.getElementById("book_res_load_progress_canvas_"+gCurrResIndex);
					var cxt=c.getContext("2d");
					cxt.fillStyle="#6baaff";
					cxt.fillRect(0,0,300*progress,5);
					remainTime=(passTime/progress)-passTime;
					strProgress=(progress*100).toFixed(1)+"%"
					document.getElementById('id_book_res_load_progress').innerHTML=strProgress+"<br />pass:"+passTime.toFixed(1)+"s remain: "+remainTime.toFixed(1)+"s";
				}
				else{
					progress=(gCurrLoadPar-gCurrParBegin)/(gCurrParEnd-gCurrParBegin)
					var c=document.getElementById("book_res_load_progress_canvas_"+gCurrResIndex);
					var cxt=c.getContext("2d");
					cxt.fillStyle="#6baaff";
					cxt.fillRect(0,0,300*progress,5);
					remainTime=(passTime/progress)-passTime;
					strProgress=(progress*100).toFixed(1)+"%"

					document.getElementById('id_book_res_load_progress').innerHTML="Load Finished<br />耗时:"+passTime.toFixed(1)+" s";
					document.getElementById('id_book_res_load_progress').innerHTML += "one res load finished";
					refreshResource();
					updataToc();
					
					if(gCurrResIndex==(gResDownloadList.length-1)){
						//全部资源加载完
						var_dump("doc load finished");
					}
					else{
						//一个资源加载完毕 加载另一个资源
						pc_loadStream(gCurrResIndex+1);
					}
				}
				
			}
		}
		else
		{
			//document.getElementById('id_palicannon_index_filelist')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}

function set_pali_loader_visible(visible){
	if(visible){
		document.getElementById('pc_res_loader').style.display="block"
		editor_show_right_tool_bar(true);
	}
	else{
		document.getElementById('pc_res_loader').style.display="none"
		editor_show_right_tool_bar(false);
	}
}


//目录段落勾选动作
function wizard_toc_par_enable(obj,iParIndex){
	gTocList[iParIndex].enable=obj.checked
	document.getElementById("par_enable_"+gTocList[iParIndex].parNum).checked=obj.checked;
	wizard_palicannon_updata_pali_par_text_enable(gTocList[iParIndex].parNum,obj.checked);
	
	if(gTocList[iParIndex].level>0){
		endIndex=wizard_palicanon_get_par_end_index(iParIndex)
		for(var iPar=iParIndex+1;iPar<=endIndex;iPar++){
			gTocList[iPar].enable=obj.checked
			document.getElementById("toc_par_enable_"+iPar).checked=obj.checked;
			document.getElementById("par_enable_"+gTocList[iPar].parNum).checked=obj.checked;
			wizard_palicannon_updata_pali_par_text_enable(gTocList[iPar].parNum,obj.checked);
		}
	}
}
//文章段落勾选动作
function par_enable_change(iParNum,obj){
	var parIndex=-1;
	for(var iPar=0;iPar<gTocList.length;iPar++){
		if(iParNum==gTocList[iPar].parNum){
			parIndex=iPar;
			break;
		}
	}
	if(parIndex>=0){
		gTocList[parIndex].enable=obj.checked;
		document.getElementById("toc_par_enable_"+parIndex).checked=obj.checked;
	}
	
	wizard_palicannon_updata_pali_par_text_enable(iParNum,obj.checked);
}

//找到当前第一个可用的段落
//找到:返回段落号
//没找到：-1
function find_first_enable_par(iBeginParNum,strParList){
	var arrayParList=strParList.split(",");
	for(iPar in arrayParList){
		curr = arrayParList[iPar];
		curr++;
		curr--;
		if(curr>=iBeginParNum){
			output = arrayParList[iPar];
			output++;
			output--;
			return(output);
			break;
		}
	}
	return(-1);
}

//显示浮动的段落资源窗口
function palicannon_par_res_show_window(parIndex){
	eWin=document.getElementById("palicannon_par_res_list");
	output="";
	output += "<div id=\"palicannon_par_res_list_title\">";
	output += "<button onclick='book_res_edit_now(2)'>";
	output += "立即编辑</button>";
	output += "<button  onclick='book_res_add_to_list(2)'>加入到编辑列表</button><button  onclick='book_res_win_close()'>关闭</button>"
	output += "</div>"
			
	output += "<div id=\"palicannon_par_res_list_body\">";
	output += wizard_palicannon_render_res_list_onepart(parIndex,2);
	output += "</div>";
	eWin.innerHTML = output;
	eWin.style.display="block";
	objParent = document.getElementById("id_pc_nav_par_"+parIndex);
	objInsert = null;
	objParent.insertBefore(eWin,objInsert);
}

function book_res_win_close(){
	eWin=document.getElementById("palicannon_par_res_list");
	objParent=document.getElementById("palicannon_par_res_list_shell");
	objInsert=null;
	objParent.insertBefore(eWin,objInsert);
}


function wizard_get_toc_from_res(){
	gTocLanguage = new Array()
	gTocLanguageItem = new Array()
	gTocCurrLanguage="pali";

	for(var iPar=0;iPar<gTocList.length;iPar++){
		parTitle=gTocList[iPar].title;
		parHeadingLevel=gTocList[iPar].level;
		parNum=gTocList[iPar].parNum;
		var newObj=Object();
		newObj.par=parNum;
		newObj.language="pali";
		newObj.title=parTitle;
		wizard_push_new_to_toc_language_list("pali");
		var newArray = Array();
		newArray.push(newObj);
		gTocLanguageItem.push(newArray);
	}
	for(x in gResList){
		if(gResList[x].type=="translate"){
			var newObj=Object();
			newObj.par=gResList[x].parNum;
			newObj.language=gResList[x].language;
			newObj.title=gResList[x].title;
			wizard_push_new_to_toc_language_list(newObj.language);
			gTocLanguageItem[newObj.par-1].push(newObj);
		}
	}

}

function wizard_push_new_to_toc_language_list(newLanguage){
	for(x in gTocLanguage){
		if(gTocLanguage[x]==newLanguage){
			return;
		}
	}
	gTocLanguage.push(newLanguage)
}

function wizard_ger_toc_title(parIndex,language){
	for(var x in gTocLanguageItem[parIndex]){
		if(gTocLanguageItem[parIndex][x].language==language){
			return(gTocLanguageItem[parIndex][x].title);
		}
	}
	return(null);
}

function wizard_palicannon_nav_language_change(obj){
	gTocCurrLanguage=obj.value;
	$("#wizard_palicannon_par_select_toc").html(wizard_palicannon_renderBookToc(gTocCurrRoot));
}

//重新设定目录的根层级
function palicannon_par_toc_reset_root(newRoot){
	document.getElementById('wizard_palicannon_par_select_toc').innerHTML=wizard_palicannon_renderBookToc(newRoot);
	var parentArray = new Array();
	
	currPar=newRoot;
	do{
		iParent=palicannon_par_get_parent(currPar);
		var objParent = new Object();
		objParent.parent=iParent;
		objParent.curr=currPar;
		parentArray.push(objParent);
		//wizard_palicanon_heading_change(iParent,currPar);
		currPar=iParent;
	}
	while(iParent>-1)
	for(var i=parentArray.length-1;i>=0;i--){
		wizard_palicanon_heading_change(parentArray[i].parent,parentArray[i].curr);
	}
	
	//当改变当前标题时 更新资源列表
	wizard_palicannon_updata_res_info(newRoot);
	//当改变当前标题时 隐藏不需要显示的巴利文本
	wizard_palicanon_updata_pali_text(newRoot);
}
function palicannon_par_get_parent(index){
	var currLevel=gTocList[index].level;
	for(var iPar=currLevel;iPar>=0;iPar--){
		if(gTocList[iPar].level>0 && gTocList[iPar].level<currLevel){
			return(iPar)
		}
	}
	return(-1);
}


//从服务器获取段落单词数
var wizard_palicannon_res_word_num_xmlhttp;
function wizard_palicannon_res_word_num_query(bookId,begin,end){
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		wizard_palicannon_res_word_num_xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		wizard_palicannon_res_word_num_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var d=new Date();
	wizard_palicannon_res_word_num_xmlhttp.onreadystatechange=wizard_palicannon_res_word_num_serverResponse;
	wizard_palicannon_res_word_num_xmlhttp.open("GET","pc_get_word_num.php?t="+d.getTime()+"&book="+bookId+"&begin="+begin+"&end="+end,true);
	wizard_palicannon_res_word_num_xmlhttp.send();
}
function get_time_by_min(min){
			if(Math.round(min)==0){
				workload_time="";
			}
			else if(min<60){
				var workload_time=min;
				workload_time=Math.round(workload_time)+gLocal.gui.min;
			}
			else if(min>=60 && min<2400){//60min-40h
				var workload_time=min/60;
				left_lime=(workload_time-Math.round(workload_time))*60;
				workload_time=Math.round(workload_time)+gLocal.gui.h;
			}
			else if(min/60/8<261 && min>=2400){//40h-261天
				var workload_time=min/60/40;
				left_lime=(workload_time-Math.floor(workload_time))*60*40;
				workload_time=Math.floor(workload_time)+gLocal.gui.week
				if(left_lime>=60){
					workload_time+=get_time_by_min(left_lime);
				}
			}
			else if(min/60/8>=261){//一年以上
				var workload_time=min/60/8/261;
				left_lime=(workload_time-Math.floor(workload_time))*60*8*261;
				workload_time=Math.floor(workload_time)+gLocal.gui.year
				if(left_lime>=60){
					workload_time+=get_time_by_min(left_lime);
				}
			}
			return(workload_time);

}
function get_shortnum_by_num(num){
	var new_num=""
	if(num<900){
		new_num=Math.round(num);
	}
	else if(num>=900 && num<10000){
		num=num/1000
		new_num=Math.round(num*100)/100+gLocal.gui.num_k
	}
	else if(num>=10000 && num<100000){
		num=num/1000
		new_num=Math.round(num*10)/10+gLocal.gui.num_k
	}
	else if(num>=100000 && num<900000){
		num=num/1000
		new_num=Math.round(num)+gLocal.gui.num_k
	}
	else{
		num=num/1000000
		new_num=Math.round(num*100)/100+gLocal.gui.num_m
	}
	return(new_num);
}
//收到资源列表
function wizard_palicannon_res_word_num_serverResponse(){
	if (wizard_palicannon_res_word_num_xmlhttp.readyState==4)// 4 = "loaded"
	{
		if (wizard_palicannon_res_word_num_xmlhttp.status==200)
		{// 200 = "OK"
			var xmlText = wizard_palicannon_res_word_num_xmlhttp.responseText;
			var wordInfo=xmlText.split(",");
			document.getElementById('pc_res_par_word_num').innerHTML=get_shortnum_by_num(wordInfo[0])+gLocal.gui.words+" "+get_shortnum_by_num(wordInfo[2])+gLocal.gui.letters;//總詞數
			document.getElementById('pc_res_par_vocabulary').innerHTML=get_shortnum_by_num(wordInfo[1])+gLocal.gui.words+" "+get_shortnum_by_num(wordInfo[3])+gLocal.gui.letters;//詞匯量
			document.getElementById('pc_res_par_word_length').innerHTML=Math.round(wordInfo[2]/wordInfo[0]*100)/100+"-"+Math.round(wordInfo[3]/wordInfo[1]*100)/100//平均词长,取2位小數
			document.getElementById('pc_res_par_quotiety').innerHTML=Math.round(wordInfo[2]/wordInfo[3]*100)/100+"-"+Math.round(wordInfo[0]/wordInfo[1]*100)/100;//重複率,取2位小數
			document.getElementById('pc_res_par_tran_workload').innerHTML=get_time_by_min(wordInfo[2]/22.95);//翻译工作量
			document.getElementById('pc_res_par_wbw_workload').innerHTML=get_time_by_min(wordInfo[3]/5.61);//逐词解析工作量
			document.getElementById('pc_res_par_total_workload').innerHTML=get_time_by_min(wordInfo[3]/5.61+wordInfo[2]/22.95);//工作总量
			document.getElementById('pc_res_par_product').innerHTML=get_shortnum_by_num(wordInfo[2]*0.37);//译文预期字数
			var difficulty_lv=Math.round(wordInfo[3]/5.61/wordInfo[0]*100)/10
			document.getElementById('pc_res_par_difficulty').innerHTML=difficulty_lv;//整体复杂度
			document.getElementById('star_lv').innerHTML=get_star_by_lv(difficulty_lv,10,5);//整体复杂度


		}
		else
		{
			//document.getElementById('id_pc_')="Problem retrieving data:" + xmlhttp.statusText;
		}
	}
}
function get_star_by_lv(lv,max_lv,max_star){
	var lv=lv*max_star/max_lv;
	var lv_base=Math.floor(lv);
	var lv_end=lv-lv_base;
	var star_str=""
	for(i=0;i<lv_base;i++){
		star_str += "<svg class=\"icon\"><use xlink:href=\"svg/icon.svg#ic_star\"></use></svg>"
	}
	if(lv_end<0.5){
		star_str += "<svg class=\"icon\"><use xlink:href=\"svg/icon.svg#ic_star_border\"></use></svg>"
	}
	else{
		star_str += "<svg class=\"icon\"><use xlink:href=\"svg/icon.svg#ic_star_half\"></use></svg>"
	}
	return(star_str)
}