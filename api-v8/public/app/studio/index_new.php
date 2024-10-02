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


			var g_language="en";
			function menuLangrage(obj){
				g_language=obj.value;
				setCookie('language',g_language,365);
				window.location.assign("index_new.php?language="+g_language);
			}

	</script>

</head>
<body class="indexbody" >
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
					<option value="si" >සිංහල</option>
					<option value="my" >မြန်မာ</option>
					<option value="zh-cn" >简体中文</option>
					<option value="zh-tw" >正體中文</option>
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
	<div class="index_inner" style="width: 60em;">
		<div class="sutta_top_blank"></div>
		<div class="editor_wizard">
			<div class="editor_wizard_caption"><h1><?php echo $module_gui_str['editor_wizard']['1001'];?></h1></div>
			<div>
			<a href="index_pc.php?language=<?php echo $currLanguage; ?>">
			<button class="importbtn" onclick="editor_wizard_next('wizard_div_palicannon')">
				<h1><?php echo $module_gui_str['editor_wizard']['1002'];?></h1>
				<p><?php echo $module_gui_str['editor_wizard']['1003'];?></p>
			</button>
			</a>
			<a href="index_new.php?language=<?php echo $currLanguage; ?>">
			<button class="importbtn" onclick="editor_wizard_next('wizard_div_mybook')">
				<h1><?php echo $module_gui_str['editor_wizard']['1004'];?></h1>
				<p><?php echo $module_gui_str['editor_wizard']['1005'];?></p>
			</button>
			<a href="index_pc.php?language=<?php echo $currLanguage; ?>">
			</div>
		</div>
	</div>
<div class="foot_div">
<?php echo $_local->gui->poweredby;?>
</div>
</body>
</html>

