<?php
require 'checklogin.inc';
include "./config.php";

if(isset($_GET["language"])){$currLanguage=$_GET["language"];}
else{$currLanguage="en";}

//load language file
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}
else{
	include $dir_language."default.php";
}


if(isset($_GET["device"])){$currDevice=$_GET["device"];}
else{$currDevice="computer";}

//require "module/editor/language/$currLanguage.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_user_base.$userid.$dir_myApp; ?>/style.css"/>
	<title>PCD Studio</title>
	<script language="javascript" src="config.js"></script>
	<script language="javascript" src="js/data.js"></script>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/render.js"></script>	
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="js/wizard.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script language="javascript" src="module/editor/language/default.js"></script>	
	<script language="javascript" src="module/editor/language/<?php echo $currLanguage; ?>.js"></script>
	
	<script language="javascript" src="module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>


	<!--加载语言文件 -->
	<script language="javascript" src="language/default.js"></script>
	<?php
	$myApp=$dir_user_base.$userid.$dir_myApp;
	if(file_exists($dir_user_base.$userid.$dir_myApp."/language/$currLanguage.js")){
		echo("<script language=\"javascript\" src=\"$myApp"."/language/$currLanguage.js\"></script>");
	}
	else{
		echo("<script language=\"javascript\" src=\"language/$currLanguage.js\"></script>");
	}
	?>
	<!--加载语言文件结束 -->
	<script language="javascript">
		var g_device="computer";
		var strSertch = location.search;
		var gConfigDirMydocument="<?php echo $dir_user_base.$userid.$dir_mydocument; ?>/";

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
			
			var g_language="en";
			function menuLangrage(obj){
				g_language=obj.value;
				setCookie('language',g_language,365);
				window.location.assign("filenew.php?language="+g_language);
			}

	</script>
</head>
<body class="indexbody">
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
				<button><a href="index.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1018'];?></a></button>
				<button><a href="index_pc.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor_wizard']['1002'];?></a></button>
				<button class="selected"><?php echo $module_gui_str['editor']['1064'];?></button>
				<button><a href="index_tools.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1052'];?></a></button>
			</div>
			<div class="toolgroup1">
				<span><?php echo $module_gui_str['editor']['1050'];?></span>
				<select id="id_language" name="menu" onchange="menuLangrage(this)">
					<option value="en" >English</option>
					<option value="zh" >简体中文</option>
					<option value="tw" >繁體中文</option>
				</select>
			
			<?php 
				echo $_local->gui->welcome;
				echo "<a href=\"setting.php?item=account\">";
				echo $_COOKIE["nickname"];
				echo "</a>";
				echo $_local->gui->to_the_dhamma;
				echo "<a href='login.php?op=logout'>Logout</a>";
			?>
			</div>
		</div>	
		<!--tool bar end -->

	<div class="index_inner">
		<div id="wizard_div_mybook">
			<div class="editor_wizard">
				<div class="editor_wizard_caption"><?php echo $module_gui_str['editor_wizard']['1006'];?></div>
					<!--right side begin-->
						<div class="mybook_l">
								<div id="wizard_sutta_preview">
									<?php echo $module_gui_str['editor_wizard']['1007'];?>
								</div>
						</div>
						<div class="mybook_r">
						<div >
							
							<div>
								<button type="button" onclick="wizard_fileNewPreview()"><?php echo $module_gui_str['editor_wizard']['1007'];?></button>
								<button type="button" onclick="wizard_new_finish()"><?php echo $module_gui_str['editor_wizard']['1011'];?></button>

								<input id="chk_title" type="checkbox" checked="true" /><?php echo $module_gui_str['editor_wizard']['1008'];?>
								<span><input id="input_smart_switch" style="width: 14px; height: 14px" type="checkbox" checked="">
									<svg class="icon">
										<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_keyboard"></use>
									</svg>
								</span>
							</div>
							<ul class="common-tab">
								<li id="NewFilePali" class="common-tab_li" onclick="wizard_show_input('new_input_pali','NewFilePali')"><?php echo $module_gui_str['editor_wizard']['1009'];?></li>
								<li id="NewFileTran1" class="common-tab_li" onclick="wizard_show_input('new_input_Tran1','NewFileTran1')"><?php echo $module_gui_str['editor_wizard']['1010'];?> 1</li>
								<li id="NewFileTran2" class="common-tab_li" onclick="wizard_show_input('new_input_Tran2','NewFileTran2')"><?php echo $module_gui_str['editor_wizard']['1010'];?> 2</li>
							</ul>
							<div id="new_input_pali">
							<h2><?php echo $module_gui_str['editor_wizard']['1009'];?></h2>
							<input id="paliauthor" type="input" value="author" onkeydown="match_key(this)" onkeyup="unicode_key(this) " />
							<p><textarea id="txtNewInput" rows="15" cols="100%" onkeydown="match_key(this)" onkeyup="unicode_key(this) " ></textarea></p>
							</div>
							
							<div id="new_input_Tran1"  >
								<h2><?php echo $module_gui_str['editor_wizard']['1010'];?> 1</h2>
								<div id="tran1">				
									<select id="tranlanguage1">
										<option value="en" selected>English</option>				
										<option value="zh" >简体中文</option>
										<option value="tw" >繁體中文</option>
									</select>
									<input id="tranauthor1" type="input" value="author"/>
									<p><textarea id="txtNewInputTran1" rows="15" cols="100%" ></textarea></p>
								</div>
							</div>
							<div id="new_input_Tran2"  >
								<h2><?php echo $module_gui_str['editor_wizard']['1010'];?> 2</h2>
								<div id="tran2">
									<select id="tranlanguage2">
										<option value="en" >English</option>						
										<option value="zh" selected>简体中文</option>
										<option value="tw" >繁體中文</option>
									</select>
									<input id="tranauthor2" type="input" value="author"/>
									<p><textarea id="txtNewInputTran2" rows="15" cols="100%" ></textarea></p>
								</div>				
							</div>
						</div>
						</div>
			</div>
				
		<!--right side end-->

	
		</div>
		

		<div id="sutta_text">
			<div class="sutta_top_blank"></div>
		</div>
			
	
		<!--right side end-->
	<!--class="main"-->
	<div class="debug_info"><span id="debug"></span></div>
</body>
</html>

