var g_is_mobile=false;
//输出调试信息
function var_dump(str){
	document.getElementById("debug").innerHTML = str;
	var t=setTimeout("clearDebugMsg()",5000);

}
function clearDebugMsg(){
	document.getElementById("debug").innerHTML = "";
}
//页面初始字体大小 单位 %
var iStartFontSize = 100;
function setHitsVisibility(isVisible)
{ 
  var c = getStyleClass('hit');
  if (isVisible)
  {
    c.style.backgroundColor = 'blue';
    c.style.color = 'white';
  }
  else
  {
    c.style.backgroundColor = 'white';
    c.style.color = 'black';
  }
}

function setPageColor(sColor)
{ 
  var c = getStyleClass('mainbody');
	var cToc = getStyleClass('toc');
  switch(sColor){
	case 0:
		c.style.backgroundColor = 'white';
		c.style.color = 'black';
		cToc.style.backgroundColor = "#F1F1F1";
		break;
	case 1:
		c.style.backgroundColor = '#ffead7';
		c.style.color = '#844200';
		cToc.style.backgroundColor = '#fff0e4';
		break;
	case 2:
		c.style.backgroundColor = '#000010';
		c.style.color = '#e0e0e0';
		cToc.style.backgroundColor = '#335';
		break;
  }
}

//修改页面字体大小
function setPageFontSize(fChange)
{
	iStartFontSize = iStartFontSize * fChange;
	var myBody = document.getElementById("mbody");
	myBody.style.fontSize= iStartFontSize + "%";
	setCookie('fontsize',iStartFontSize,65)
}

function setFootnotesVisibility(isVisible)
{
	
  getStyleClass('note').style.display = (isVisible ? 'inline' : 'none');
}
function setIdVisibility()
{
	var isVisible = document.getElementById("B_Id").checked;
  getStyleClass('ID').style.display = (isVisible ? 'block' : 'none');


}
function setOrgVisibility()
{
	var isVisible = document.getElementById("B_Org").checked;
  getStyleClass('org').style.display = (isVisible ? 'block' : 'none');


}

function setMeaningVisibility()
{
	var isVisible = document.getElementById("B_Meaning").checked;
  getStyleClass('mean').style.display = (isVisible ? 'block' : 'none');

}

function setGrammaVisibility()
{
	var isVisible = document.getElementById("B_Gramma").checked;
  getStyleClass('case').style.display = (isVisible ? 'block' : 'none');

}


//显示英译
function setParTranEnVisibility()
{
	var isVisible = document.getElementById("B_ParTranEn").checked;
  getStyleClass('tran_par_en').style.display = (isVisible ? 'block' : 'none');
}
//显示中译
function setParTranCnVisibility()
{
	var isVisible = document.getElementById("B_ParTranCn").checked;
  getStyleClass('tran_par_cn').style.display = (isVisible ? 'block' : 'none');
}


//显示段对译模式
function setParTranShowMode()
{
	var isVisible = document.getElementById("B_ParTranShowMode").checked;
	if(isVisible){  /* 上下对读 */
		getStyleClass('pali_par').style.width = "auto";
		getStyleClass('pali_par').style.float="none";
		getStyleClass('pali_par_gatha').style.width = "auto";
		getStyleClass('pali_par_gatha').style.float="none";
		//getStyleClass('tran_par').style.margin = "0";

	}
	else{ /* 左右对读 */
		getStyleClass('pali_par').style.width = "50%";
		getStyleClass('pali_par').style.float = "left";
		getStyleClass('pali_par_gatha').style.width = "50%";
		getStyleClass('pali_par_gatha').style.float = "left";
		//getStyleClass('tran_par').style.margin = "0 0 0 50%-220px";

  }
}

//显示单词表
function setWordTableVisibility()
{
	var isVisible = document.getElementById("B_WordTableShowMode").checked;
  document.getElementById("word_table").style.display = (isVisible ? 'block' : 'none');
}

function getStyle (styleName) {
	for (var s = 0; s < document.styleSheets.length; s++)
	{
		if(document.styleSheets[s].rules)
		{
			for (var r = 0; r < document.styleSheets[s].rules.length; r++)
			{
				if (document.styleSheets[s].rules[r].selectorText == styleName)
				{
					return document.styleSheets[s].rules[r];
				}
			}
		}
		else if(document.styleSheets[s].cssRules)
		{
			for (var r = 0; r < document.styleSheets[s].cssRules.length; r++)
			{
				if (document.styleSheets[s].cssRules[r].selectorText == styleName)
					return document.styleSheets[s].cssRules[r];
			}
		}
	}
	
	return null;
}

function getStyleClass (className) {
	for (var s = 0; s < document.styleSheets.length; s++)
	{
		if(document.styleSheets[s].rules)
		{
			for (var r = 0; r < document.styleSheets[s].rules.length; r++)
			{
				if (document.styleSheets[s].rules[r].selectorText == '.' + className)
				{
					return document.styleSheets[s].rules[r];
				}
			}
		}
		else if(document.styleSheets[s].cssRules)
		{
			for (var r = 0; r < document.styleSheets[s].cssRules.length; r++)
			{
				if (document.styleSheets[s].cssRules[r].selectorText == '.' + className)
					return document.styleSheets[s].cssRules[r];
			}
		}
	}
	
	return null;
}

function getCookie(c_name)
{
	if (document.cookie.length>0)
	{ 
		c_start=document.cookie.indexOf(c_name + "=")
		if (c_start!=-1)
		{ 
			c_start=c_start + c_name.length+1 
			c_end=document.cookie.indexOf(";",c_start)
			if (c_end==-1) 
				c_end=document.cookie.length
			return unescape(document.cookie.substring(c_start,c_end))
		} 
	}
return ""
}

function setCookie(c_name,value,expiredays)
{
	var exdate=new Date()
	exdate.setDate(exdate.getDate()+expiredays)
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toGMTString())
}
//读取cookie 中的字体大小
function checkCookie(){
	mFontSize=getCookie('fontsize')
	if (mFontSize!=null && mFontSize!="")
	{
		iStartFontSize = mFontSize
	}
	else 
	{
	  mFontSize=100
	  iStartFontSize = 100
	  setCookie('fontsize',mFontSize,365)
	}
	setPageFontSize(1);
	setPageColor(0);
}


function setObjectVisibilityAlone(strIdGroup , strId){

	var hiden = new Array();
	hiden=strIdGroup.split('&');
	for(i=0;i<hiden.length;i++){
		document.getElementById(hiden[i]).style.display="none";
	}
	var obj = document.getElementById(strId);
	obj.style.display="block";


}

function setObjectVisibility(strId){
	var obj = document.getElementById(strId);
	if(obj.style.display=="none"){
		obj.style.display="block";
	}
	else{
		obj.style.display="none";
	}
}

function setObjectVisibility2(ControllerId,ObjId){
	var isVisible = document.getElementById(ControllerId).checked;
	document.getElementById(ObjId).style.display = (isVisible ? 'block' : 'none');
}

function setNaviVisibility(){
	var objNave = document.getElementById('leftmenuinner');
	var objMainView = document.getElementById("body_mainview");
	if(objNave.style.display=="none"){
		objNave.style.display="block";
		//objMainView.style.margin = "0,0,0,18em";
		getStyleClass('mainview').style.margin = "0,0,0,42em";
	}
	else{
		objNave.style.display="none";
		//objMainView.style.margin = "0";
		getStyleClass('mainview').style.margin = "0";
	}
}

function windowsInit(){
	var strSertch = location.search;
	if(strSertch.length>0){
		strSertch = strSertch.substr(1);
		var sertchList=strSertch.split('&');
		for (x in sertchList){
			var item = sertchList[x].split('=');
			if(item[0]=="filename"){
				g_filename=item[1];
			}
		}
	}
	checkCookie();
	setUseMode("read");
	if(g_filename.length>0){
		loadxml(g_filename);
	}
	else{
		alert("error:没有指定文件名。");
	}
}

/*静态页面使用的初始化函数*/
function windowsInitStatic(){

	checkCookie();
	setUseMode_Static("read");

}

function indexInit(){

	showUserFilaList();
}

function goHome(){
var r=confirm("在返回前请保存文件。否则所有的更改将丢失。\n 按<确定>回到主页。按<取消>留在当前页面。");
if (r==true)
  {
  window.location.assign("index.html?device="+g_device);
  }
}