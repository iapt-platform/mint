var dict_pre_searching=false;
var dict_pre_search_curr_word="";
var dict_search_xml_http=null;
function dict_search(word){
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
}
function dict_search_serverResponse(){
	if (dict_search_xml_http.readyState==4)// 4 = "loaded"
	{
		if (dict_search_xml_http.status==200)
		{// 200 = "OK"
			var serverText = dict_search_xml_http.responseText;
			dict_result=document.getElementById("dict_ref_search_result");
			if(dict_result){
				dict_result.innerHTML=serverText;
				document.getElementById("dict_ref_dict_link").innerHTML=document.getElementById("dictlist").innerHTML;
				document.getElementById("dictlist").innerHTML="";				
			}
		}
		else
		{
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
		if (dict_pre_search_xml_http.status==200)
		{// 200 = "OK"
			var serverText = dict_pre_search_xml_http.responseText;

				dict_result=document.getElementById("dict_ref_search_result");
				if(dict_result){
					dict_result.innerHTML=serverText;
				}


		}
		else
		{
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
		document.getElementById("input_parts").innerHTML=strParts;
	}
	else{
	document.getElementById("input_parts").innerHTML="";
	}
		
}