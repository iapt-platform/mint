<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Vandana</title>

	<script language="javascript">
var g_is_mobile=false;
//输出调试信息
function var_dump(str){
	getStyleClass("debug_info").style.display = "-webkit-flex";
	getStyleClass("debug_info").style.opacity = "0.5";
	document.getElementById("debug").innerHTML = str;
	var t=setTimeout("clearDebugMsg()",2000);
}
function clearDebugMsg(){
	document.getElementById("debug").innerHTML = "";
	getStyleClass("debug_info").style.opacity = "0";
	getStyleClass("debug_info").style.display = "none";
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

function showTOC(){
	setObjectVisibility("leftmenuinner_mobile");
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
		getStyleClass('mainview').style.margin = "0,0,0,18em";
	}
	else{
		objNave.style.display="none";
		//objMainView.style.margin = "0";
		getStyleClass('mainview').style.margin = "0";
	}
}



/*静态页面使用的初始化函数*/
function windowsInitStatic(){
	checkCookie();
}

function showMenu(){
	var obj = document.getElementById("toolbar_mobile");
	var objX = document.getElementById("btnX");
	var objToc = document.getElementById("leftmenuinner_mobile");
	if(obj.style.display=="none"){
		obj.style.display="inline";
		objX.style.opacity ="1.0";
	}
	else{
		obj.style.display="none";
		objX.style.opacity ="0.3";
		objToc.style.display="none";
	}

}

var VisibleMenu = ''; // 記錄目前顯示的子選單的 ID

// 顯示或隱藏子選單
function switchMenu( theMainMenu, theSubMenu, theEvent ){
    var SubMenu = document.getElementById( theSubMenu );
    if( SubMenu.style.display == 'none' ){ // 顯示子選單
        SubMenu.style.display = 'block';
        hideMenu(); // 隱藏子選單
        VisibleMenu = theSubMenu;
    }
    else{ // 隱藏子選單
        if( theEvent != 'MouseOver' || VisibleMenu != theSubMenu ){
            SubMenu.style.display = 'none';
            VisibleMenu = '';
        }
    }
}

// 隱藏子選單
function hideMenu(){
    if( VisibleMenu != '' ){
        document.getElementById( VisibleMenu ).style.display = 'none';
    }
    VisibleMenu = '';
}

	</script>
	
	<style>
	body {
    font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', 'Source Han Sans', source-han-sans-simplified-c, Verdana, sans-serif;
    font-style: normal;
    color: #444444;
    font-weight: 400;
    font-size: 14px;
}

.main{
	 padding: 0;
}
.mainbody{
	padding: 0;
    margin: 0;
    font-size:100%;
    height: 100%;
    width: 100%;
    overflow-x:hidden; 
}
.sutta{
	font-size: 100%;
}		
.sutta_top_blank {
    height: 50px;
}
.chapter1 h1{
	width:Auto;			
	float:left;	
	color:#550;
}
.chapter1 a{
	float:right;
}
.heading {
  font-weight: bold;
  font-size: 158%;
  text-align: center;
}

h1 {
    font-size: 150%;
    font-weight: 800;
}

h2 {
    font-weight: 800;
}

h3 {
    font-size: 100%;
    font-weight: 800;
}

a:link,
a:visited {
    color: #4688F1;
    text-decoration: none;
}

a:focus {
    outline: 1px dotted;
}

a:hover,
a:active {
    color: #4688F1;
    outline: none;
    text-decoration: underline;
}

::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    -webkit-border-radius: 10px;
    border-radius: 10px;
    background: rgba(85,85,85,0.1); 
}
::-webkit-scrollbar-thumb {
    -webkit-border-radius: 10px;
    border-radius: 10px;
    background: rgba(85,85,85,0.2); 
}
:hover::-webkit-scrollbar-thumb {
    -webkit-border-radius: 10px;
    border-radius: 10px;
    background: rgba(85,85,85,0.5); 
}

.toc {
    color: #444444;
    margin: 0;
    padding: 0 5px;
    overflow-y: scroll;
    overflow-x: hidden;
    height:calc(100% - 40px); 
    height:-moz-calc(100% - 40px); 
    height:-webkit-calc(100% - 40px); 
    background-color: #f5f5f5;
}

#leftmenuinner {
	position:fixed;
    display: inline-block;
    top: 42px;
    left: 0;
    padding: 0;
    height: 100%;
    width: 18em;
    background-color: #f5f5f5;
    float: left;
    z-index: 2;
    -webkit-transition-duration: 0.4s;/* Safari */
    transition-duration: 0.4;
}
#leftmenuinnerinner {
}
#leftmenuinner_mobile {
	position:fixed;
	top:42px;
	left:0;
	padding-top:0;
	padding-bottom:0;    
	height:100%;
	width:100%;
	display:none;
	background-color: #f5f5f5;
	-webkit-transition-duration: 0.4s;/* Safari */
    transition-duration: 0.4;
}




#leftmenuinnerinner_mobile {
}

.mainview{
    padding: 20px;
    margin-left:18em;
    float: left;
    -webkit-transition-duration: 0.4s;
    transition-duration: 0.4s;
}
.mainview_mobile{
	padding: 20px;
}
.toc p {
	font-size: 90%;
	margin:4px 0 4px 0.5em;
	line-height:1.2;
}
.toc ul {
	font-size: 90%;
  list-style-type: none;
  padding-left: 0px;
  line-height:1.2;
  text-indent:1px;
}
.toc ul li{
	text-indent:1px;
	line-height:1.2;
	margin:5px 0;
}

#content {
  margin: 0px 5px 10px 5px;
  padding: 1px 3px;		  
  /*border: 1px solid #BBB;*/
  /*background-color: #dda;*/
  /*background: #EEE;*/
  /*color: #000;*/
  }
 
#navi_bookmark{
	display:none;
}
#navi_note{
	display:none;
}
  
.toolbar , #toolbar_mobile{
	position:fixed;
    top: 0;
    left: 0;
    padding: 0 5px;
    border-top: 1px solid #FFFFFF;
    border-bottom: 1px solid #D2D2D2;
    display: inline-block;
    background-color: #F8F8F8;
    box-shadow: 0px 28px 24px 0px #FFFFFF inset;
    color: #444444;
    padding-top: 5px;
    width: 100%;
    height: 40px;
    font-size: 13px;
    -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
}
.index_toolbar{
	top:0;
	left:0;
	border-bottom: 1px solid #BBB;
	margin: 0;
	padding: 5px 10px;
	background-color: #555;
	color:#FFF;
}

.debug{
	display:none;
}
#word_table{
	margin-top:20px;
	display:none;
}
#sutta_text{
	margin-top:30px;
}

.sutta_title{
	border-left:15px solid #BBB;
	margin:30px 2px 20px 1em;
	padding: 1px 0.5em;
}
.sutta_title a {
	float:right;
}

.tran_h1_cn, .tran_h1_en {
	font-size: 100%;
	font-weight: 500;
	line-height:1.1;
	margin: 0px 2px;
}

.sutta_paragraph{
	margin: 0;
	padding: 1em 0 0 0;
}
.pali_par{
	width:100%;
	/*float:left;*/
	padding: 0pt 8pt;
}
.pali_par_gatha{
	width:100%;
	/*float:left;*/
	padding: 0;
	margin-left:2em;
}
.pali_par_mobile{
	/*width:100%;*/
	padding: 0pt 8pt;
}
.pali_par_gatha_mobile{
	/*width:100%;*/
	padding: 0;
	margin-left:2em;
}
.tran_par
{
	padding: 0 5px;
	margin: 0 ;
}
.tran_par_mobile
{
	padding: 0 5px;
	margin: 0 ;
}
.tran_par_en
{
	font-size:80%;
	text-indent:0;
}
.tran_par_cn
{
	font-size:90%;
	text-indent:0;
}

.par_note{
	font-size:75%;
	border-left: 5px solid #CDC;
	margin: 2px 5px 2px 1em;
	padding:2px;
	clear:left;
}

.word {
    width: Auto;
    float: left;
    padding: 0 3pt;
    height: 7.5em;
}
.word_hightlight {
    width: Auto;
    float: left;
    padding: 0 3pt;
    background-color: #dda;
}
.word_punc {
    width: Auto;
    float: left;
    margin-right: 0.3em;
    height: 7.5em;
}

.word_detail {}

.pali {
    font-family: 'Noto Sans', 'Noto Sans CJK TC', 'Noto Serif', 'Noto Serif CJK TC', 'Noto Serif CJK SC', Times;
    font-weight: 700;
    font-size: 110%;
    padding: 0pt;
    margin: 0pt;
}
.mean {
    font-family: 'Noto Sans', 'Noto Sans CJK TC', 'Noto Serif', 'Noto Serif CJK TC', 'Noto Serif CJK SC', Times;
    font-weight: 700;
    font-size: 90%;
    line-height: 1.2;
    margin: 3pt 0;
    float: none;
    clear: both;
}
.ID {
    font-size: 70%;
    line-height: 1.2;
    margin: 0;
    float: none;
    clear: both;
    display: none;
}
.org {
    font-size: 90%;
    line-height: 1.2;
    margin: 3px 0;
    float: none;
    clear: both;
    color: #009191;
}
.om {
    font-size: 75%;
    line-height: 1.2;
    margin: 3px 0;
    float: none;
    clear: both;
    color: #009191;
}
.case {
    font-size: 75%;
    line-height: 1.2;
    margin: 3px 0;
    float: none;
    clear: both;
    color: #009191;
}

.case .cell{
	background-color: #009191;
    color: #FFFFFF;
    padding: 0px 2px;
    font-weight: 300;
}
/*不确定的显示为红色*/
.case .cell2 a{
	background-color:#F80;
	color:#FFFFFF;
	padding: 0px 2px;
    font-weight: 300;
}


.clr{
	clear:left;
}
.enter{
	clear:left;
}

.chanting_enter{
	display:none;
	clear:left;
}

.hidden{
	display:none;
}
#modifywin {
    width: auto;
    max-width: 20em;
    background-color: #FFFFFF;
    margin: 0;
    padding: 10px;
    position: absolute;
    border: 1px solid #D5D5D5;
    border-radius: 2px;
    box-shadow: 0px 4px 8px 0px rgba(0, 0, 0, 0.2);
    display: none;
}
#modifywin p {
    margin: 0;
    padding: 0;
}
#modifywin button {
    font-size: 12px;
}

#modifywin .modifybutton {
    margin: 6px 0 0 0;
    padding: 5px 0;
    border-top: 1px solid #E4E4E4;
}
#modifywin .modifybutton .modify_left {
    width: 50%;
    float: left;
    border-right: 1px solid #AAB;
}
#modifywin a {
    width: 100%;
    cursor: pointer;
}
.bookmarkcolorblock {
    padding: 1px 5px;
}

.bookmarkcolor0 {
    background-color: rgba(0,0,0,0);
}
.bookmarkcolor1 {
    background-color: #F99;
}
.bookmarkcolor2 {
    background-color: #FF9;
}
.bookmarkcolor3 {
    background-color: #9F9;
}
.bookmarkcolor4 {
    background-color: #9FF;
}
.bookmarkcolor5 {
    background-color: #99F;
}

/*?x?没找到*/
.bookmarkcolorx {
    background-color: #CCCCCC;
}

/*?a?自动匹配*/
.bookmarkcolora {
    background-color: #DDF;
}
.bma {
    display: none;
}
.bmx{
}
.bm1{
}
.bm2{
}
.bm3{
}
.bm4{
}
.bm5{
}

#navi_bookmark_inner h3{
	font-size:100%;
	font-weight: 800;
}

.indexbody{
	margin:0;
	padding: 0;
	background-color: #f5f5f5;
}

.editor{
	display:inline;
}

.debugMsg{
	display:none;
}
table {border-collapse: collapse;}
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
.h {background-color: #9999cc; font-weight: bold; color: #000000;}

.center {text-align: center;}
.index_inner{ width:400px; margin-left: auto; margin-right: auto; text-align: left;}

#device a:link ,#device a:visited{
  color: #AAA;
  text-decoration: none;
}

#device a:focus {
  outline:1px dotted;
}

#device a:hover, #device a:active {
  color: #FFF;
  outline: none;
  text-decoration: underline;
}

.select_mode a:link ,#device a:visited{
  color: #AAA;
  text-decoration: none;
}

.select_mode a:focus {
  outline:1px dotted;
}

.select_mode a:hover, #device a:active {
  color: #000;
  outline: none;
  text-decoration: underline;
}


button {
    font-family: 'Noto Sans', 'Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', 'Source Han Sans', source-han-sans-simplified-c, Verdana, sans-serif;
    /*position: relative;*/
    /*float: left;*/
    border: 1px solid #DCDCDC;
    padding: 0.3em 0.6em;
    font-size: 13px;
    font-weight: 400;
    min-height: 1em;
    color: #009191;
    background-color: #F9F9F9;
    border-radius: 3px;
    margin: 0px 1px;
    -webkit-transition-duration: 0.2s;
    /* Safari */
    transition-duration: 0.2;
    cursor: pointer;
    box-shadow: 0px 0.6px 0px 0px rgba(0, 0, 0, 0.2);
    -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
}
button:hover {
    background-color: #4688F1;
    color: #FFFFFF;
    border: 1px solid #4688F1;
}

.btn-group {
    position: relative;
    display: inline-block;
    width: auto;
    font-size: 0;
}
.btn-group .btn+.btn {
    margin-left: -3px;
}

.btn-group>.btn:not(:first-child):not(:last-child) {
    border-radius: 0;
}

.btn-group>.btn:first-child:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.btn-group>.btn:last-child:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.dropdown {
    position: relative;
    display: inline-block;
}



/* 下拉内容 (默认隐藏) */
.dropdown-content {
    border-radius: 3px;
    border: 1px solid #D2D2D2;
    display: none;
    position: absolute;
    background-color: #FFFFFF;
    color: #444444;
    min-width: 60px;
    box-shadow: 0px 3px 16px 0px rgba(0, 0, 0, 0.2);
    /*border-radius: 6px;*/

    z-index: 6;
    top: 100%;
    right: 0;
}




/*使用一半宽度 (120/2 = 60) 来居中提示工具*/
.dropdown-content::after {
    content: " ";
    position: absolute;
    bottom: 100%;
    right: 0;
    margin-right: 20px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent #FFFFFF transparent;
}





/* 下拉菜单的链接 */
.dropdown-content a {
    padding: 6px 6px;
    text-decoration: none;
    display: block;
    color: #009191;
    white-space: nowrap;
}



/* 鼠标移上去后修改下拉菜单链接颜色 */
.dropdown-content a:hover {
    background-color: #4688F1;
    color: #FFFFFF;
    border-radius: 3px;
}



/* 当下拉内容显示后修改下拉按钮的背景颜色 */
.dropdown:hover .dropbtn {
    background-color: #4688F1;
    color: #FFFFFF;
}

/*此经启动不显示*/
#dasadhammasutta2{
	display:none;
}

#btn_menu_show{
	/*position:fixed;*/
	top:0;
	right:0;
	background-color: #AAF;
	padding:0.5em;
	font-size:2em;
	color:#FFF;
	border-radius: 10px; 
	opacity: 0.5; 
}

.tran_input{
	display:none;
	clear:both;
}

.case{																														
	font-size:80%;															
	line-height: 1.2;																
	margin: 0;	
	float:none;
	clear:both;	
}

/*.case_dropbtn .cell{
	margin:1px;
	padding:1pt;
	background-color:#050;
	color:#FFF;
}
/*不确定的显示为红色
.case_dropbtn .cell2 a{
	margin:1px;
	padding:1pt;
	background-color:#F80;
	color:#000;
}
*/
/* 下拉按钮样式 */
.case_dropbtn {
    padding: 2px;
    font-size: 80%;
    border: none;
    margin: 0px;
}
.case_dropdown .case_dropbtn {
    /*background-color: #F80;*/
    /*color: white;*/

    padding-left: 3px;
    padding-right: 3px;
    padding-top: 0px;
    padding-bottom: 0px;
    border: none;
    font-size: 100%;
    cursor: pointer;
    margin: 0px;
}


/* 容器 <div> - 需要定位下拉内容 */
.case_dropdown {
    /*position: relative;*/

    display: inline-block;
}


/* 下拉内容 (默认隐藏) */
.case_dropdown-content {
    display: none;
    position: absolute;
    background-color: #FFFFFF;
    min-width: 8em;
    margin: -1px 0px;
    box-shadow: 0px 4px 8px 0px rgba(0, 0, 0, 0.25);
    color: #444444;
}


/* 下拉菜单的链接 */
.case_dropdown-content a {
    color: black;
    padding: 2px 4px;
    text-decoration: none;
    display: block;
    cursor: pointer;
}


/* 鼠标移上去后修改下拉菜单链接颜色 */
.case_dropdown-content a:hover {
    background-color: #4688F1;
    color: #FFFFFF;
}


/* 在鼠标移上去后显示下拉菜单 */
.case_dropdown:hover .case_dropdown-content {
    display: block;
}


/* 当下拉内容显示后修改下拉按钮的背景颜色 */
.case_dropdown:hover .case_dropbtn {
    opacity: 0.4;
}

.edit_tran_button {
    display: inline;
    height: 1.5em;
    padding: 0 5px;
    margin: 0 3px 0 0;
    opacity: 0.5;
}
.edit_tran_button:hover {
    opacity: 1;
}

.tocitems li{
overflow: hidden; 
}
.toc_h_0{
	display:none;
}
.toc_h_1{
	padding-left:0;
}
.toc_h_2{
	padding-left:1em;
}
.toc_h_3{
	padding-left:2em;
}
.toc_h_4{
	padding-left:3em;
}

.head_par .head_pali_1{
font-weight: 700;
font-size:200%;
}
.head_par .head_pali_2{
font-weight: 700;
font-size:150%;
}
.head_par .head_pali_3{
font-weight: 400;
font-size:120%;
}
.head_par .head_pali_4{
font-weight: 200;
font-size:100%;
}

.edit_tran_button{
	display:none;
}
	</style>
</head>


<body class="mainbody" id="mbody" onLoad="windowsInitStatic()">
		<!-- tool bar begin-->
				<!-- content begin--> 
		<div id="leftmenuinner_mobile">
			<div class='toc' id='leftmenuinnerinner_mobile'>
				<div id="navi_toc">
					<h1>Content 目录</h1>
					<script>
					function test(){
						showTOC();
					}
					</script>
					<div id="content" onclick="test()">
					<?php echo $_POST["txt_toc"];?>
					</div>
				</div>
			</div>
		</div>
		<!-- content end -->	
		<div id='toolbar_mobile'>
			<div>
				<span id="toobar_items">
				<button  type="button" onclick="showTOC()">≡</button>
				<div class="btn-group">
				<button id="B_FontReduce" class="btn" type="button" onclick="setPageFontSize(0.9)">A-</button> 
				<button id="B_FontGain" class="btn" type="button" onclick="setPageFontSize(1.1)">A+</button>
				</div>
				<span class="dropdown" onmouseover="switchMenu(this,'menuColorMode','MouseOver')" onmouseout="hideMenu()">
						<div><button class="dropbtn" id="color_mode">Color ▾</button></div>
						<div class="dropdown-content" id="menuColorMode">
							<a href="#" onclick="setPageColor(0)">白色</a>
							<a href="#" onclick="setPageColor(1)">黄昏</a>
							<a href="#" onclick="setPageColor(2)">夜间</a>
						</div>
				</span>
				<span class="dropdown" onmouseover="switchMenu(this,'menuViewSet','MouseOver')" onmouseout="hideMenu()">
						<div><button class="dropbtn" id="view_setting">View ▾</button></div>
						<div class="dropdown-content" id="menuViewSet">
							<a><span><input id="B_Org" onclick="setOrgVisibility()" type="checkbox" checked />原型</span></a>
							<a><span><input id="B_Meaning" onclick="setMeaningVisibility()" type="checkbox" checked />意思</span></a>
							<a><span><input id="B_Gramma" onclick="setGrammaVisibility()" type="checkbox" checked />语法</span></a>
							<a><span><input id="B_ParTranEn" onclick="setParTranEnVisibility()" type="checkbox" checked />英译</span></a>
							<a><span><input id="B_ParTranCn" onclick="setParTranCnVisibility()" type="checkbox" checked />中译</span></a>
							<a><span><input id="B_ParTranShowMode" onclick="setParTranShowMode()" type="checkbox" checked />上下对译</span></a>
						</div>
				</span>
				</span>
				
			</div>
		</div>
		<button type="button" id="btnX"style="float:right; right:5px; top:5px; position:fixed;" onclick="showMenu()">×</button>
		<div class="debug_info"><span id="debug"></span></div>
		<!--tool bar end -->

	<div class="main">

		
		<!--right side begin-->
		<div class='mainview_mobile' id='body_mainview'>

		
			<div id="sutta_text">
			<!--经文起始-->
			<?php echo $_POST["txt_sutta"];?>

			<!--经文结束-->
			</div>
		</div>
		<!--right side end-->
	</div>

</body>
</html>

