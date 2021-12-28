var search_pre_searching=false;
var search_pre_search_curr_word="";
var search_search_xml_http=null;


function pre_search(type="pre",find_word){
	$.get("./search.php",
		{
			op:type,
			word:find_word
		},
		function(data,status){
			$("#search_result").html(data);
		});	
}

function search_input_keyup(e,obj){
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
		pre_search("pre",obj.value);
	}
	else{
		pre_search("pre",obj.value);
	}
}
