var dict_pre_searching=false;
var dict_pre_search_curr_word="";
var dict_search_xml_http=null;
var g_filename="";
var g_take_split;
var g_curr_word="";

function dict_turbo_split(){
	let word = $("#dict_ref_search_input").val();
	$.post("./dict_find4.php",
	{
	word:word
	},
	function(data,status){
		$("#dict_ref_search_result").html(data);
	});	
}

function dict_jump(obj){
	let jumpto=obj.innerHTML;
	dict_search(jumpto);
}

function part_click(obj){

	let word = obj.innerHTML;
	dict_search(word,false);
}
function add_part_to_input(parts){
	$("#dict_ref_search_input").val(parts);
	dict_input_split(parts)
}

function add_part_to_word(parts){
	$("#input_org").val(parts);
}

function dict_search(word,take_split=true){
	g_take_split = take_split;
	g_curr_word = word;
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_search_xml_http=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_search_xml_http=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_search_xml_http!=null)
	{
		dict_search_xml_http.onreadystatechange=dict_search_serverResponse;
		word=word.replace(/\+/g,"%2b");
		dict_search_xml_http.open("GET", "./dict_find3.php?op=search&word="+word, true);
		dict_search_xml_http.send();
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
	$("part").click(function(){
		add_part($(this).html());
  });
}
function dict_search_serverResponse(){
	if (dict_search_xml_http.readyState==4)// 4 = "loaded"
	{
		debugOutput("server response.",0);
		if (dict_search_xml_http.status==200)
		{// 200 = "OK"
			var serverText = dict_search_xml_http.responseText;
			dict_result=document.getElementById("dict_ref_search_result");
			if(dict_result){
				dict_result.innerHTML=serverText;
				document.getElementById("dict_ref_dict_link").innerHTML=document.getElementById("dictlist").innerHTML;
				document.getElementById("dictlist").innerHTML="";	
				if(g_take_split){
					$("#dict_word_auto_split").html($("#auto_splite").html());
					$("#auto_splite").html("");
				}
				var gramma_info_array=document.getElementsByClassName("dict_find_gramma");
				for(i_gramma_info=0;i_gramma_info<gramma_info_array.length;i_gramma_info++){
					if(gramma_info_array[i_gramma_info]!=null){
						gramma_info_array[i_gramma_info].innerHTML=getLocalGrammaStr(gramma_info_array[i_gramma_info].innerHTML);
					}
				}
				//当点击<see>标签时自动查字典
				$("see").click(function(){
				var to =$(this).attr("to");
				var link;
				if(to){
					link=to;
				}
				else{
					link=$(this).text();
				}
				dict_search(link);

				});
			}
		}
		else
		{
			debugOutput(dict_pre_search_xml_http.statusText,0);
		}
	}
}


var dict_pre_search_xml_http=null;
function dict_pre_search(word){
	if(dict_pre_searching==true){return;}
	dict_pre_searching=true;
	dict_pre_search_curr_word=word;
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		dict_pre_search_xml_http=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		dict_pre_search_xml_http=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (dict_pre_search_xml_http!=null)
	{
		dict_pre_search_xml_http.onreadystatechange=dict_pre_search_serverResponse;
		dict_pre_search_xml_http.open("GET", "./dict_find3.php?op=pre&word="+word, true);
		dict_pre_search_xml_http.send();
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function dict_pre_search_serverResponse(){
	if (dict_pre_search_xml_http.readyState==4)// 4 = "loaded"
	{
		debugOutput("server response.",0);
		if (dict_pre_search_xml_http.status==200)
		{// 200 = "OK"
			var serverText = dict_pre_search_xml_http.responseText;
			if (window.DOMParser){
				  var parser=new DOMParser();
				  var wordData=parser.parseFromString(serverText,"text/xml");
			}
			else{ // Internet Explorer
			
				  var wordData=new ActiveXObject("Microsoft.XMLDOM");
				  wordData.async="false";
				  wordData.loadXML(serverText);
			}
			if(wordData){
				var wordlist = wordData.getElementsByTagName("word")
				//var obj = JSON.parse(serverText);
				var dict_word="";
				for(var iword=0; iword<wordlist.length;iword++){
					dict_word += "<div class='dict_word_list'><a onclick='dict_pre_word_click(\""+getNodeText(wordlist[iword],"pali")+"\")'>"+getNodeText(wordlist[iword],"pali")+"-"+getNodeText(wordlist[iword],"count")+"</a></div>"
				}
				dict_result=document.getElementById("dict_ref_search_result");
				if(dict_result){
					dict_result.innerHTML=dict_word;
				}
			}

		}
		else
		{
			debugOutput(dict_pre_search_xml_http.statusText,0);
		}
		dict_pre_searching=false;
		var newword = document.getElementById("dict_ref_search_input").value;
		if(newword!=dict_pre_search_curr_word){
			dict_pre_search(newword);
		}
	}

}
function dict_pre_word_click(word){
	document.getElementById("dict_ref_search_input").value=word;
	dict_search(word);
}

function dict_input_change(obj){
	dict_pre_search(obj.value);
}

function dict_input_keypress(e,obj){
	var keynum
	var keychar
	var numcheck

	if(window.event) // IE
	  {
	  keynum = e.keyCode
	  }
	else if(e.which) // Netscape/Firefox/Opera
	  {
	  keynum = e.which
	  }
	var keychar = String.fromCharCode(keynum)
	if(keynum==13){
		
	}
}

function dict_input_keyup(e,obj){
	var keynum
	var keychar
	var numcheck

	if(window.event) // IE
	  {
	  keynum = e.keyCode
	  }
	else if(e.which) // Netscape/Firefox/Opera
	  {
	  keynum = e.which
	  }
	var keychar = String.fromCharCode(keynum)
	if(keynum==13){
		dict_search(obj.value);
	}
	else{
		dict_input_split(obj.value);
		dict_pre_search(obj.value);
	}
}

function dict_input_split(word){
	if(word.indexOf("+")>=0){
		var wordParts=word.split("+");
		var strParts="";
		for(var i in wordParts){
			strParts+="<a onclick='dict_search(\""+wordParts[i]+"\")'>"+wordParts[i]+"</a>";
		}
		strParts+=" <a onclick='add_part(\""+word+"\")'>[√]</a> ";
		document.getElementById("input_parts").innerHTML=strParts;
	}
	else{
		document.getElementById("input_parts").innerHTML="";
	}
		
}