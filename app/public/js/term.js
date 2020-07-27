var arrTerm = new Array();
var arrTerm2 = new Array();
var arrMyTerm = new Array();
var arrAllMean = new Array();
var arrTermAllPali = new Array();
var arrWordIdTermId = new Array();

var strTermTanslationTmp="[%mean%](%pali% %mean2% %mymean%)";
var strTermTanslationTmp2="[%mean%]";
var termCounter=new Array();
var noteCounter=0; //正文内注释计数器

function note(noteId,strDef="unkow"){
	document.write("haha");
}

function note_replace(strIn){
	/*
	
	*/
	var output = strIn.replace("/*","<script>");
	output = output.replace("*/","</script>");
	return(output);
}

//将存储状态的字符串转换为预显示字符串
//设置状态为 0：未处理的原始状态
function term_std_str_to_tran(strIn){
	return(strIn.replace(/\[\[/g,"<term status='0'>").replace(/\]\]/g,"</term>"));	
}

function term_std_str_to_edit(strIn){
	var arrText = strIn.split("/");
	for(var i in arrText){
		//头尾是*
		if(arrText[i].substring(0,1)=="*" && arrText[i].substring(arrText[i].length-1)=="*"){
			var arrOneTermWord=arrText[i].split("@");
			if(arrOneTermWord.length==2){
				arrText[i]="*"+arrOneTermWord[1];
			}
		}
	}

	return(arrText.join("/"));
}

function term_tran_edit_replace(strIn){
	var strEdit=strIn;
	for(var x=0;x<arrTerm2.length;x++){
		var strReplace="strEdit=strEdit.replace(/"+arrTerm2[x].meaning+"/g,\"/*"+arrTerm2[x].meaning+"*/\")";
		eval(strReplace);
	}
	//strEdit=strEdit.replace(/\/*\/*/g,"\/*");
	//strEdit=strEdit.replace(/\*\/\*\//g,"*\/");
	return(strEdit);
}

function term_edit_to_std_str(strIn){
	var arrText = strIn.split("/");
	for(var i in arrText){
		//头尾是*
		if(arrText[i].substring(0,1)=="*" && arrText[i].substring(arrText[i].length-1)=="*"){
			var wordMeaning=arrText[i].substring(1,arrText[i].length-1);
			arrText[i]="*"+term_get_std_str(wordMeaning)+"*";
		}
	}
	return(arrText.join("/"));
}
function term_get_std_str(strMean){
	for(var x=0;x<arrTerm2.length;x++){
		if(arrTerm2[x].meaning==strMean){
			return(arrTerm2[x].guid+"@"+strMean);
		}
	}
	return("unkow@"+strMean);
}
function term_get_my_std_str(strMean){
		for(var x in arrMyTerm){
		if(arrMyTerm[x].meaning==strMean){
			return(arrMyTerm[x].guid+"@"+strMean);
		}
	}
	return("unkow@"+strMean);
}

function note_lookup(word,showto){
	$("#"+showto).load("term.php?op=search&word="+word,function(responseTxt,statusTxt,xhr){
    if(statusTxt=="success"){
		$(".term_note").each(function(index,element){
			$(this).html(note_init($(this).html()));
			$(this).attr("status",1);
			note_refresh_new();
		});			
	}
	else if(statusTxt=="error"){
      console.error("Error: "+xhr.status+": "+xhr.statusText);
	}
  });
}

function term_apply(guid){
	if(g_eCurrWord){
		setNodeText(g_eCurrWord,"note","=term("+guid+")");
		term_array_updata();
	}
}

function term_data_copy_to_me(guid){
	$("#term_dict").load("term.php?op=copy&wordid="+guid);
}

//我的术语字典进入编辑模式
function term_edit(guid){
	$("#term_edit_btn1_"+guid).hide();
	$("#term_edit_btn2_"+guid).show();
	document.getElementById("term_dict_my_"+guid).style.display="none";
	document.getElementById("term_dict_my_edit_"+guid).style.display="block";
}

//我的术语字典退出编辑模式
function term_data_esc_edit(guid){
	$("#term_edit_btn1_"+guid).show();
	$("#term_edit_btn2_"+guid).hide();
	document.getElementById("term_dict_my_"+guid).style.display="block";
	document.getElementById("term_dict_my_edit_"+guid).style.display="none";
	
}
//我的术语字典 编辑模式 保存

function term_data_save(guid){
	if(guid==""){
		var strWord=$("#term_new_word").val();
		var strMean=$("#term_new_mean").val();
		var strMean2=$("#term_new_mean2").val();
		var strNote=$("#term_new_note").val();
		var strTag=$("#term_new_tag").val();
		let newTerm = new Object();
		newTerm.guid=com_guid();
		newTerm.word=strWord;
		newTerm.meaning = strMean;
		newTerm.other_meaning = strMean2;
		arrMyTerm.push(newTerm);
	}
	else{
		var strWord=$("#term_edit_word_"+guid).val();
		var strMean=$("#term_edit_mean_"+guid).val();
		var strMean2=$("#term_edit_mean2_"+guid).val();
		var strNote=$("#term_edit_note_"+guid).val();
	}
	$.get("term.php",
	{
		op:"save",
		guid:guid,
		word:strWord,
		mean:strMean,
		mean2:strMean2,
		tag:strTag,
		note:strNote
	},
	function(data,status){
		try{
			let result= JSON.parse(data);
			if(result.status==0){
				note_lookup(result.message,"term_dict");
			}
			else{
				ntf_show("term error"+result.message);
			}
		}
		catch(e){
			console.error("term_get_all_pali:"+e+" data:"+data);
			ntf_show("term error");
		}
		
		
	});
}
function term_get_all_pali(){
 	$.get("term.php",
	{
		op:"allpali"
	},
	function(data,status){
		if(data.length>0){
			try{
			arrTermAllPali = JSON.parse(data);
			}
			catch(e){
				console.error("term_get_all_pali:"+e+" data:"+data);
			}
		}
	});
}
function term_lookup_all(pali){
	for(var x in arrTermAllPali){
		if(arrTermAllPali[x].word==pali){
			return(arrTermAllPali[x]);
		}
	}
	return(null);
}

function term_get_my(){
 	$.get("term.php",
	{
		op:"my"
	},
	function(data,status){
		if(data.length>0){
			try{
				arrMyTerm = JSON.parse(data);
			}
			catch(e){
				console.error(e.error+" data:"+data);
			}
		}
	});
}

//在我的术语字典里查询
function term_lookup_my(pali){
	for(var x in arrMyTerm){
		if(arrMyTerm[x].meaning==pali){
			return(arrMyTerm[x]);
		}		
		if(arrMyTerm[x].word==pali){
			return(arrMyTerm[x]);
		}
	}
	return(null);
}
function term_lookup_my_id(id){
	for(var x in arrMyTerm){
		if(arrMyTerm[x].guid==id){
			return(arrMyTerm[x]);
		}		
	}
	return(null);
}

function term_get_all_meaning(word){
 	$.get("term.php",
	{
		op:"allmean",
		word:word
	},
	function(data,status){
		$("#term_win_other_mean").html(data);
	});
}

//刷新文档正在使用的术语数据
function term_array_updata(){
	arrTerm2=new Array();
	var arrTermDownLoadList=new Array();
	var arrWordIdTermId=new Array();
	var xAllWord = gXmlBookDataBody.getElementsByTagName("word");
	for(var x=0;x<xAllWord.length;x++){
		var sNote = getNodeText(xAllWord[x],"note");
		var wid=getNodeText(xAllWord[x],"id");
		if(sNote.substring(0,6)=="=term("){
			var termId=sNote.slice(6,-1);
			if(!arrTerm[termId]){
				arrTermDownLoadList.push(termId);
			}
			//person={wid:wid,tid:termId};
			arrWordIdTermId.push({wid:wid,tid:termId});
		}
	}
	if(arrTermDownLoadList.length>0){
		var idlist=arrTermDownLoadList.join();
		idlist = idlist.replace(/,/g,"','");
		idlist = "'"+ idlist + "'";
 		$.get("term.php",
		{
		op:"extract",
		list:idlist
		},
		function(data,status){
			var obj = JSON.parse(data);
			for(var x in obj){
				arrTerm[obj[x].guid]=obj[x];
				arrTerm2.push(obj[x]);
			}
			if(g_eCurrWord){
				updataWordHeadById(getNodeText(g_eCurrWord,"id"));
				refreshWordNote(g_eCurrWord.parentNode.parentNode);
			}
			for(var i=0;i<arrWordIdTermId.length;i++){
				var wid=arrWordIdTermId[i].wid;
				var sMean=arrTerm[arrWordIdTermId[i].tid].meaning;
				doc_setWordDataById(wid,"mean",sMean);
				updateWordBodyById(wid);
			}
				
		});
		
	}
}

function term_updata_translation(){
	termCounter=new Array();
	noteCounter=1;
	$("term").each(function(){
		let status=$(this).attr("status");
		let termText = $(this).text();
		
		if(termText.slice(0,1)=="#"){
			if(status==0){
				$(this).attr("status","1");
				$(this).attr("type","1");
				$(this).attr("text",termText.slice(1));
			}
			let noteText=$(this).attr("text");
			$(this).html("<a onclick=\"alert('"+noteText+"')\">["+noteCounter+"]</a>");
			noteCounter++;
		}
		else{
			if(status==0){
				let myterm=term_lookup_my(termText);//我的术语字典
				if(myterm){
					$(this).attr("status","1");
					$(this).attr("type","0");
					$(this).attr("guid",myterm.guid);
					$(this).attr("pali",myterm.word);
					$(this).attr("mean",myterm.meaning);
					$(this).attr("mean2",myterm.other_meaning);
					$(this).attr("replace",myterm.meaning);
				}
				else{
					$(this).attr("status","2");
				}
			}
			let guid=$(this).attr("guid");
			let pali=$(this).attr("pali");
			let mean=$(this).attr("mean");
			let mean2=$(this).attr("mean2");
			var renderTo=$(this).attr("pos");
			var noteText="";
			
			if(termCounter[guid]){
				termCounter[guid]=2;
			}
			else{
				termCounter[guid]=1;
			}
			var myterm=term_lookup_my(pali);//我的术语字典
			let linkclass="";
			if(myterm){
				linkclass="term_link";
			}
			else{
				linkclass="term_link_new";
			}			
			if(guid){
				
				if(renderTo=="wbw"){
					noteText="%note%";
				}
				else{
					if(termCounter[guid]==1){
						noteText=strTermTanslationTmp;
					}
					else{
						noteText=strTermTanslationTmp2;
					}
				}

				noteText=noteText.replace("[","<span class='"+linkclass+"' onclick=\"term_show_win('"+guid+"')\">");
				noteText=noteText.replace("]","</span>");
				noteText=noteText.replace("%mean%","<span class='term_mean'>"+mean+"</span>");
				noteText=noteText.replace("%pali%","<span class='term_pali'>"+pali+"</span>");
				noteText=noteText.replace("%mean2%","<span class='term_mean2'>"+mean2+"</span>");
				noteText=noteText.replace("%note%","<span class='term_note'>"+""+"</span>");
				if(myterm){
					if(myterm.meaning!=mean){
						noteText=noteText.replace("%mymean%","<span class='term_mean_my'>"+myterm.meaning+"</span>");
					}
					else{
						noteText=noteText.replace("%mymean%","");
					}
				}
				else{
					noteText=noteText.replace("%mymean%","");
				}
			}
			else{
				noteText="<span class='"+linkclass+"'  onclick=\"term_show_win('','"+termText+"')\">"+termText+"</span>";
			}
			$(this).html(noteText);
		}
	});
}

function term_show_win(guid,keyWord=""){
	if(guid==""){
		$(term_body).html("当前词条未创建。<br /><a onclick=\"term_add_new('"+keyWord+"')\">现在创建</a>");
	}
	else{
		let currWord = term_lookup_my_id(guid);
		if(currWord){
			let termString="";
			let pali=currWord.word;
			let pali_1= pali.substring(0,1).toUpperCase();
			pali=pali_1+pali.substring(1);
			let mean=currWord.meaning;
			let myterm=term_lookup_my(currWord.word);//我的术语字典
			termString+="<div class='term_win_mean'>"+pali+"</div>";
			termString+="<div class='term_win_pali'>意思："+currWord.meaning+"</div>";
			termString+="<div class='term_win_mean2'>其他意思："+currWord.other_meaning+"</div>";
			termString+="<div class='term_win_mymean'>我的词库：";
			if(myterm){
				termString+="<b>"+myterm.meaning+"</b> ";
			}
			else{
				termString+="<input type='input'  placeholder='我的释义'>";
			}
			termString+="<span>其他:</span><span id='term_win_other_mean'></span>";
			termString+="</div>";
			
			if(currWord.note){
				termString+="<div class='term_win_note'>"+currWord.note+"</div>";
			}
			else{
				termString+="<div class='term_win_note'>Loading</div>";
			}
			$(term_body).html(termString);
			term_get_all_meaning(currWord.word);
			
			if(!currWord.note){
				$.get("term.php",
				{
					op:"load_id",
					id:currWord.guid
				},
				function(data,status,xhr){
					switch(status){
						case "success":
							try{
								let loadWord = JSON.parse(data);
								$("#term_win_note").html(loadWord[0].note);
								//修改内存数据
								for(let x in arrMyTerm){
									if(arrMyTerm[x].guid==loadWord[0].guid){
										arrMyTerm[x].note=loadWord[0].note;
										return;
									}
								}
							}
							catch(e){
								console.error(e+" data:"+data);
							}						
						break;
						case "error":
							console.error("Error: "+xhr.status+": "+xhr.statusText);
						break;
					}
				});

			}
		}
		else{
			$(term_body).html("undefined guid");
		}
	}
	document.getElementById("term_win").style.display="flex";
	
}

function term_tmp(type,tmp){
	if(tmp=="new"){
		switch(type){
			case "a":
				strTermTanslationTmp=$("#term_my_tmp").val();
				break;
			case "a2":
				strTermTanslationTmp2=$("#term_my_tmp").val();
			break;
		}		
		
	}
	else{
		switch(type){
			case "a":
				strTermTanslationTmp=tmp;
				break;
			case "a2":
				strTermTanslationTmp2=tmp;
			break;
		}
	}
	term_updata_translation();
}

function term_add_new(keyword){
	document.getElementById("term_win").style.display="none";
	editor_show_right_tool_bar(true);
	tab_click_b('term_dict','tab_rb_dict');
	note_lookup(keyword,"term_dict");
}