

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function plugin_pcds_find_init(){

}

function plugin_pcds_find_run(){
	document.getElementById("id_plugin_pcds_find_result").innerHTML="";
	var input=document.getElementById("id_plugin_pcds_find_input").value;
	var xDocWords = gXmlAllWordInWBW;//xmlDoc.getElementsByTagName("word");
	for(var iword=0;iword<xDocWords.length;iword++){
		var sReal = getNodeText(xDocWords[iword],"real");
		var sPali = getNodeText(xDocWords[iword],"pali");
		var sMean = getNodeText(xDocWords[iword],"mean");
		var sId = getNodeText(xDocWords[iword],"id");
		if(sReal==input){
		document.getElementById("id_plugin_pcds_find_result").innerHTML+="<a href=\"#wb"+sId+"\">"+sPali+"</a>:"+sMean+"<br>";
		document.getElementById("whead1_"+sId).style.background="yellow";
		}
	}
	
}

function plugin_pcds_find_clear(){
	document.getElementById("id_plugin_pcds_find_result").innerHTML="";
	document.getElementById("id_plugin_pcds_find_result").innerHTML="";

	var xDocWords = gXmlAllWordInWBW;//xmlDoc.getElementsByTagName("word");
	for(var iword=0;iword<xDocWords.length;iword++){
		var sId = getNodeText(xDocWords[iword],"id");
		document.getElementById("whead1_"+sId).style.background="transparent";

	}
	
}