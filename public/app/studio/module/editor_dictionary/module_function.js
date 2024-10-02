

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function edit_dictionary_init(){

}

function menu_file_import_wbw(filename){
	editor_loadDataFromILD(filename);
}

function menu_file_import_ild(){
	var filename = g_filename+".ild";
	editor_loadDataFromILD(filename);
}
//import csv begin
var editor_ILDXmlHttp=null;
function editor_loadDataFromILD(strFileName){
	
	if(window.XMLHttpRequest)
	{// code for IE7, Firefox, Opera, etc.
		editor_ILDXmlHttp=new XMLHttpRequest();
	}
	else if(window.ActiveXObject)
	{// code for IE6, IE5
		editor_ILDXmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	  
	if (editor_ILDXmlHttp!=null)
	{
		var d=new Date();
		var strLink ="";
		if(strFileName.substr(-4)==".ild"){
			strLink = "./import_ild.php?filename="+strFileName;
		}
		else if(strFileName.substr(-4)==".pcs"){
			strLink = "./import_wbw.php?filename="+strFileName;
		}
		if(strLink.length>0){
			editor_ILDXmlHttp.onreadystatechange=editor_ild_serverResponse;
			editor_ILDXmlHttp.open("GET", strLink, true);
			editor_ILDXmlHttp.send(null);
			document.getElementById('id_ild_msg_inner').innerHTML="Importing..."+strFileName;
		}
		else{
			document.getElementById('id_ild_msg_inner').innerHTML="无法识别的文件类型";
		}
		
	}
	else
	{
		alert("Your browser does not support XMLHTTP.");
	}
	
}

function editor_ild_serverResponse(){
			if (editor_ILDXmlHttp.readyState==4)// 4 = "loaded"
			{
				document.getElementById('id_csv_msg_inner').innerHTML="receve csv data";
			  if (editor_ILDXmlHttp.status==200)
				{// 200 = "OK"
				var xmlText = editor_ILDXmlHttp.responseText;
				
				if (window.DOMParser)
				  {
				  parser=new DOMParser();
				  xmlILD=parser.parseFromString(xmlText,"text/xml");
				  }
				else // Internet Explorer
				  {
				  xmlILD=new ActiveXObject("Microsoft.XMLDOM");
				  xmlILD.async="false";
				  xmlILD.loadXML(xmlText);
				  }
			  
				if (xmlILD == null){
					alert("error:can not load inline dictionary xml obj is null.");
					return;
				}

				ildDataParse(xmlILD);
				dictMatchXMLDoc();
				}
			  else
				{
				document.getElementById('id_ild_msg_inner')="Problem retrieving data:" + xmlhttp.statusText;
				}
					
			}
}

/*Parse ild data and fill this document*/
function ildDataParse(xmlILDData){
	document.getElementById('id_ild_msg_inner').innerHTML+="<br>Parseing ILD Data";
	var xILD = xmlILDData.getElementsByTagName("word");	
	var xDocWords = gXmlBookDataBody.getElementsByTagName("word");	
	
	for(iword=0;iword<xILD.length;iword++){
		objNewDict= new Object();
		
		objNewDict.Id = getNodeText(xILD[iword],"id");
		objNewDict.Guid = getNodeText(xILD[iword],"guid");
		objNewDict.Pali = getNodeText(xILD[iword],"pali");
		objNewDict.Mean = getNodeText(xILD[iword],"mean");
		objNewDict.Type = getNodeText(xILD[iword],"type");
		objNewDict.Gramma = getNodeText(xILD[iword],"gramma");
		objNewDict.Parent = getNodeText(xILD[iword],"parent");
		objNewDict.Factors = getNodeText(xILD[iword],"factors");
		objNewDict.FactorMean = getNodeText(xILD[iword],"factorMean");
		objNewDict.PartId = getNodeText(xILD[iword],"partid");
		objNewDict.Note = getNodeText(xILD[iword],"note");
		objNewDict.Confer = getNodeText(xILD[iword],"confer");
		objNewDict.Status = getNodeText(xILD[iword],"status");
		objNewDict.Delete = getNodeText(xILD[iword],"delete");
		objNewDict.Language = getNodeText(xILD[iword],"language");
		objNewDict.dictname=getNodeText(xILD[iword],"dict_name");
		objNewDict.dictType=getNodeText(xILD[iword],"dictType");
		objNewDict.fileName=getNodeText(xILD[iword],"fileName");
		objNewDict.ParentLevel=getNodeText(xILD[iword],"parentLevel");
		g_DictWordList.push(objNewDict);
	}
	document.getElementById('id_ild_msg_inner').innerHTML+="<br>Updata inline Dictionary Data OK!";
	document.getElementById('id_dict_match_result_inner').innerHTML=dictShowAsTable();
	
}

//import ild end
