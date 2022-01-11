<?php
require_once "../public/_pdo.php";
require_once "../config.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<script language="javascript">
	<?php 
	//加载js语言包
	//require_once '../public/load_lang_js.php';
	?>
	<?php
	//加载js语言包
	if(file_exists(_DIR_LANGUAGE_."/".$currLanguage.".json")){
		echo "var gLocal = ".file_get_contents(_DIR_LANGUAGE_."/".$currLanguage.".json").";";
	}
	else{
		echo "var gLocal = ".file_get_contents(_DIR_LANGUAGE_."/default.json").";";
	}
	?>
		var gDownloadListString="";
		
		var g_device="computer";
		var strSertch = location.search;
		
		if(strSertch.length>0){
			strSertch = strSertch.substr(1);
			var sertchList=strSertch.split('&');
			for (x in sertchList){
				var item = sertchList[x].split('=');
				if(item[0]=="device"){
					g_device=item[1];
				}
			}
		}
		if(g_device=="mobile"){
			g_is_mobile=true;
		}
		else{
			g_is_mobile=false;
		}
		
		var gCaseTable=<?php echo file_get_contents("../public/js/case.json"); ?>
	</script>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="../pcdl/css/font.css"/>

	<link type="text/css" rel="stylesheet" href="../pcdl/css/reader.css"/>
	<link type="text/css" rel="stylesheet" href="../pcdl/css/reader_mob.css" media="screen and (max-width:800px)">
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>
	<title id="page_title"><?php echo $_local->gui->setting; ?></title>
	<script src="../public/js/jquery-3.5.1.js"></script>
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>	
	<script src="../public/js/fixedsticky.js"></script>
	<script src="../lang/lang.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../public/js/notify.js"></script>

	<script src="./setting.js"></script>

</head>
<body class="reader_body" >
<?php
if(!isset($_COOKIE["userid"])){
    echo "请您先登陆";
    exit;
}
?>
<a name="page_head"></a>


<style>
.pcd_notify{
	margin-left: 20em;
	background-color: rgb(201 137 15 / 64%);
}
		#para_nav {
			display: flex;
			justify-content: space-between;
			padding: 5px 1em;
			border-top: 1px solid gray;
		}

	.word{
		display:inline-block;
		padding: 1px 3px;
	}
	.mean{
		font-size: 65%;
	}
		/* 下拉内容 (默认隐藏) */
	#mean_menu {
		margin: 0.3em;
		position: absolute;
		background-color: white;
		min-width: 8em;
		max-width: 30em;
		margin: -1px 0px;
		box-shadow: 0px 3px 13px 0px black;
		color: var(--main-color);
		z-index: 200;
	}

	/* 下拉菜单的链接 */
	#mean_menu a {
		/*padding: 0.3em 0.4em;*/
		line-height: 160%;
		text-decoration: none;
		display: block;
		cursor: pointer;
		text-align: left;
		font-size:80%;
	}

	/* 鼠标移上去后修改下拉菜单链接颜色 */
	.mean_menu a:hover {
		background-color: blue;
		color: white;
	}

.par_pali_div{
	margin-top:1em;
}
.par_pali_div{
	font-weight:700;
}
sent{
	font-weight:500;
	font-size:110%;
	line-height: 150%;
}
sent:hover{
	background-color:#fefec1;
}
para {
    color: white;
    background-color:  #F1CA23;
    min-width: 2em;
    display: inline-block;
    text-align: center;
    padding: 3px 6px;
    border-radius: 99px;
	margin-right: 5px;
	cursor:pointer;
	font-size:80%;
}
para:hover{

}

.sent_count{
	font-size:80%;
    color: white;
    background-color: #1cb70985;
    min-width: 2em;
    display: inline-block;
    text-align: center;
    padding: 2px 0;
    border-radius: 99px;
	margin-left: 5px;	
	cursor:pointer;
}
.toc_1{
	padding: 5px;
    cursor: pointer;
	border-left: 2px solid #aaaaaa;
}
.toc_1_title{
	font-weight:700;
}
.toc_2{
	font-weight:500;
	padding-left:1em;
	display:none;
}
.curr_chapter{
	border-color: #4d4dff;
	color: #4d4dff;
}
.toc_curr_chapter2{
	display:block;
}
.toc_title2 a{
	color:black;
	line-height:1.4em;
	text-decoration: none;
}
.toc_title2 a:hover{
	text-decoration: underline;
}
.curr_chapter_title2 a{
	color:#4d4dff;
	font-weight:900;
}
#leftmenuinner{
    width: 20em;
    max-width: 90%;
		overflow-y: scroll;
		border-right: unset;	
}
#leftmenuinnerinner{

}
#leftmenuinnerinner{
	margin-left: 2em;
    font-size: 0.8em;
	border-right: unset;
}
.sent_toc{
	font-weight:700;
	font-family: Noto serif;
    font-size: 150%;
}
.dict_lang{
	/*border: 1px solid gray;*/
    min-height: 4em;
	margin:0.5em;
}
.setting_shell{
	border-bottom: 1px solid gray;
    margin-bottom: 3em;
    padding-bottom: 3em;
}
</style>
		<!-- tool bar begin-->
		<div id="main_tool_bar" class='reader_toolbar'>
			<div id="index_nav"> 
				<button onclick="setNaviVisibility()">
					<svg t='1598084571450' class='icon' viewBox='0 0 1029 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='6428' width='20px' height='20px'><path d='M159.744 69.632 53.248 69.632C28.672 69.632 4.096 90.112 4.096 118.784l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0C184.32 266.24 208.896 241.664 208.896 212.992L208.896 118.784C208.896 90.112 184.32 69.632 159.744 69.632zM970.752 69.632 368.64 69.632c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152L1024 118.784C1028.096 94.208 999.424 69.632 970.752 69.632zM159.744 413.696 53.248 413.696c-28.672 0-53.248 24.576-53.248 49.152l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0c28.672 0 53.248-24.576 53.248-49.152l0-94.208C208.896 438.272 184.32 413.696 159.744 413.696zM970.752 413.696 368.64 413.696c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152l0-94.208C1028.096 438.272 999.424 413.696 970.752 413.696zM159.744 757.76 53.248 757.76c-28.672 0-53.248 24.576-53.248 49.152l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0c28.672 0 53.248-24.576 53.248-49.152l0-94.208C208.896 782.336 184.32 757.76 159.744 757.76zM970.752 761.856 368.64 761.856c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152l0-94.208C1028.096 782.336 999.424 761.856 970.752 761.856z' fill='#757AF7' p-id='6429'></path></svg>
				</button>
			</div>
			<div>
				<span id="tool_bar_title" style="font-family: 'Noto Serif';"><?php echo $_local->gui->setting; ?></span>
			</div>
			<div style="display: flex;">	

			</div>
		</div>	
		

		
	<div id="main_text_view" style="padding-bottom: 10em;">
		<?php 
			echo "<div id='setting_account_shell' class='setting_shell'>";
            echo "<a name='account'></a>";
			echo "<h2>{$_local->gui->account}</h2>";
			echo "{$_local->gui->avatar}：<span id='head_img'></span>";

            echo "{$_local->gui->username}：{$_COOKIE["username"]} <button>{$_local->gui->modify}</button><br />";
            echo "{$_local->gui->nick_name}：{$_COOKIE["nickname"]} <button>{$_local->gui->modify}</button><br />";
            //echo "{$_local->gui->e_mail}：{$_COOKIE["email"]}<button>{$_local->gui->modify}</button><br />";
            echo "<a href='../ucenter/pwd_set.php'>{$_local->gui->change_password}</a><br>";
			echo "<a href='login.php?op=logout'>{$_local->gui->logout}</a>";
			echo "</div>";


			echo "<div id='setting_general_shell' class='setting_shell'>";
			echo "<a name='general'></a>";
            echo "<h2>{$_local->gui->general}</h2>";
            echo "<div id='setting_general' class='setting_content'>";
			echo "</div>";
			echo "</div>";


			echo "<div id='setting_library_shell' class='setting_shell'>";
			echo "<a name='library'></a>";
            echo "<h2>{$_local->gui->library}</h2>";
            echo "<div id='setting_library' class='setting_content'>";
            echo "</div>";
            echo "</div>";

			echo "<div id='setting_studio_shell' class='setting_shell'>";
			echo "<a name='studio'></a>";
            echo "<h2>{$_local->gui->studio}</h2>";
            echo "<div id='setting_studio' class='setting_content'>";
            echo "</div>";
            echo "</div>";

			echo "<div id='setting_dictionary_shell' class='setting_shell'>";
			echo "<a name='dictionary'></a>";
            echo "<h2>{$_local->gui->dictionary}</h2>";
            echo "<div id='setting_dictionary' class='setting_content'>";
            echo "</div>";
            echo "</div>";

        ?>


	</div><!--main_text_view end-->



	<div id="right_panal_toc" style="position: fixed;top:3em;width:17em;left: calc(100% - 17em);height:auto; min-height:30em;border-left: 1px solid gray;    font-size: 80%;padding: 2em 0.5em;">


	</div>

	</div>
	<!-- 全屏 黑色背景 -->	
	<div id="BV" class="blackscreen" onclick="setNaviVisibility()"></div>
		<!-- nav begin--> 

	<div id="leftmenuinner" class="viewswitch_off">
			<div class="win_caption">
				<div><button id="left_menu_hide" onclick="setNaviVisibility()">返回</button></div>
			</div>

		<div class='toc' id='leftmenuinnerinner' style="font-family: 'Noto Serif';">	
			<!-- toc begin -->
			<div class="menu" id="menu_toc">
				<a name="_Content" ></a>
				<div  id="toc_content">
                <?php
                    echo "<div class='toc_1 toc_1_title'><a href='#account'>{$_local->gui->account}</a></div>";
                    echo "<div class='toc_1 toc_1_title'><a href='#general'>{$_local->gui->general}</a></div>";
                    echo "<div class='toc_1 toc_1_title'><a href='#studio'>{$_local->gui->studio}</a></div>";
                    echo "<div class='toc_1 toc_1_title'><a href='#library'>{$_local->gui->library}</a></div>";
                    echo "<div class='toc_1 toc_1_title'><a href='#dictionary'>{$_local->gui->dictionary}</a></div>";
                ?>
                </div>


			</div>
		
		</div>
		<!-- nav end -->	
	</div>

	<div id="mean_menu" ></div>
<script>
$(document).ready(setting_onload);

ntf_init(1);

function setNaviVisibility(strObjId = "") {
    var objNave = document.getElementById("leftmenuinner");
    var objblack = document.getElementById("BV");
  
    if (objNave.className == "viewswitch_off") {
      objblack.style.display = "block";
      objNave.className = "viewswitch_on";
    } else {
      objblack.style.display = "none";
      objNave.className = "viewswitch_off";
    }
  }
</script>
	
</body>
</html>