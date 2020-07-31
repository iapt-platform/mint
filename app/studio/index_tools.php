<?php
require 'checklogin.inc';
require_once '../public/config.php';
require_once '../public/load_lang.php';

//load language file
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}
else{
	include $dir_language."default.php";
}

if(isset($_GET["device"])){
	$currDevice=$_GET["device"];
}
else{
	if(isset($_COOKIE["device"])){
		$currDevice=$_COOKIE["device"];
	}
	else{
		$currDevice="computer";
	}
}
//获取当前年
$year=date('Y');
//获取当年月
$month=date('m');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<title><?php echo $_local->gui->pcd_studio; ?></title>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/filenew.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script type="text/javascript">
	<?php require_once '../public/load_lang_js.php';//加载js语言包?>
		var g_device = "computer";
		var strSertch = location.search;
		if(strSertch.length>0){
			strSertch = strSertch.substr(1);
			var sertchList=strSertch.split('&');
			for ( i in sertchList){
				var item = sertchList[i].split('=');
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


			var g_langrage="en";
			function menuLangrage(obj){
				g_langrage=obj.value;
				setCookie('language',g_langrage,365);
				window.location.assign("index_tools.php?language="+g_langrage);
			}

	</script>

</head>
<body class="indexbody" >
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
				<button><a href="index.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1018'];?></a></button>
				<button><a href="index_pc.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor_wizard']['1002'];?></a></button>
				<button><a href="filenew.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1064'];?></a></button>
				<button class="selected"><?php echo $module_gui_str['editor']['1052'];?></button>
			</div>
			<div class="toolgroup1">
				<span><?php echo $module_gui_str['editor']['1050'];?></span>
				<select id="id_language" name="menu" onchange="menuLangrage(this)">
					<option value="en" >English</option>
					<option value="si" >සිංහල</option>
					<option value="my" >myanmar</option>
					<option value="zh-cn" >简体中文</option>
					<option value="zh-tw" >繁體中文</option>
				</select>
			
			<?php 
				echo $_local->gui->welcome;
				echo "<a href=\"setting.php?item=account\">";
				echo urldecode($_COOKIE["nickname"]);
				echo "</a>";
				echo $_local->gui->to_the_dhamma;
				echo "<a href='login.php?op=logout'>";
				echo $_local->gui->logout;
				echo "</a>";
			?>
			</div>
		</div>	
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
		</script>
	<div class="index_inner">
		<div class="sutta_top_blank"></div>
		<div class="fun_block">
			<h2><?php echo $module_gui_str['editor']['1052'];?></h2>
			<div >
			<p>
			<a class="tool_button" href="../tools/translate_import.html" target="tc"><?php echo $module_gui_str['editor']['1054'];?></a>
			</p>
			<p>
			<a class="tool_button" href="../tools/unicode.html" target="unicode"><?php echo $module_gui_str['editor']['1055'];?></a>
			</p>
			<p>
			<a class="tool_button" href="../tools/Pa_auk_dict/Pa_auk_dict.htm" target="Pa-auk Dictionary"><?php echo $module_gui_str['editor']['1056'];?></a>
			</p>
			<p>
			<a class="tool_button" href="dictionary.php?language=<?php echo($currLanguage); ?>" target="dictionary"><?php echo $module_gui_str['editor']['1057'];?></a>
			</p>
			<p>
			<a class="tool_button" href="../tools/guid.php?language=<?php echo($currLanguage); ?>" target="guid"><?php echo $module_gui_str['editor']['1088'];?></a>
			</p>
			<p>
			<a class="tool_button" href="../tools/pali_word_analysis/index.php?language=<?php echo($currLanguage); ?>" target="analysis"><?php echo $module_gui_str['editor']['1102'];?></a>
			</p>
			<p>
			<a class="tool_button" href="search.php?language=<?php echo($currLanguage); ?>" target="search"><?php echo $module_gui_str['editor_palicannon']['1002'];?></a>
			</p>
			<p>
			<a class="tool_button" href="term_sys_tool.php?language=<?php echo($currLanguage); ?>" target="search"><?php echo $module_gui_str['editor_plugin']['1007'];?></a>
			</p>
			<p>
			<a class="tool_button" href="buddhist_calendar.php?language=<?php echo($currLanguage); ?>&y=<?php echo($year); ?>&m=<?php echo($month); ?>" target="search"><?php echo $module_gui_str['tools']['1004'];?></a>
			</p>
			<p>
				<a class="tool_button" href="../dict_builder/index.php?language=<?php echo($currLanguage); ?>" target="dict_builder">PCD Dict-Builder</a>
			</p>
			<p>
				<a class="tool_button" href="../term/index.php?language=<?php echo($currLanguage); ?>" target="dict_builder">PCD Term</a>
			</p>
			
			</div>
		</div>
	</div>
<div class="foot_div">
<?php echo $module_gui_str['editor']['1066'];?>
</div>
</body>
</html>

