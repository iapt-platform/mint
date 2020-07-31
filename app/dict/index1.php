<?php
require_once '../public/config.php';
require_once "../public/load_lang.php";

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
	<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
	<link type="text/css" rel="stylesheet" href="../studio/css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="./css/style.css"/>
	<title><?php echo $_local->gui->pcd_studio;?></title>
	<script language="javascript" src="../studio/js/common.js"></script>
	<script language="javascript" src="js/dict.js"></script>
	<script src="../public/js/jquery.js"></script>
	<script src="../studio/js/fixedsticky.js"></script>
	<script >//type="text/javascript"
	
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
		function menuLanguage(obj){
			g_language=obj.value;
			//setCookie('language',g_language,365);
			window.location.assign(location.pathname+"?language="+g_language);
		}

	</script>

</head>
	<body class="indexbody" onLoad="">
		<!-- tool bar begin-->
		<div class='index_toolbar' style=" height: initial;">

			<div>		
				<div style="display:block;">
					<input id="dict_ref_search_input" type="input" onkeyup="dict_input_keyup(event,this)" onfocus="dict_input_onfocus()" placeholder="<?php echo $_local->gui->search;?>">
				</div>
				<div style="display:block;">
					<ul id="dict_type" class="tab_a" >
						<li id="dt_dict" class="act" onclick="tab_click('dict_ref','dt_dict')"><?php echo $_local->gui->dict;//字典?></li>
						<li id="dt_bold"  onclick="tab_click('dict_bold','dt_bold')"><?php echo $_local->gui->vannana;//注疏?></li>
						<li id="dt_term"  onclick="tab_click('dict_term','dt_term')"><?php echo $_local->gui->adhivacana;//术语?></li>
						<li id="dt_wordmap"  onclick="tab_click('dict_wordmap','dt_wordmap')"><?php echo $_local->gui->word_base;//词源?></li>				
						<li id="dt_user"  onclick="tab_click('dict_user','dt_user')"><?php echo $_local->gui->user;//用户?></li>				
					</ul>
				</div>
			</div>
			<div >
				<span><?php echo $_local->gui->language;//選擇語言?></span>
				<select id="id_language" name="menu" onchange='menuLanguage(this)'>
					<option value="en" >English</option>
					<option value="si" >සිංහල</option>
					<option value="my" >မြန်မာဘာသာ</option>
					<option value="zh-cn" >简体中文</option>
					<option value="zh-tw" >繁體中文</option>
				</select>
			
			</div>
		</div>	
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
		</script>
	<div >
		<!--  查词工具 拆分 -->
		<div><span id="input_parts"><span></div>
	</div>
	
		<div id="dict_ref_search_result" style="background-color: var(--tool-bg-color);color: white;">
		</div>
	
		<div class="foot_div">
			<?php echo $_local->gui->poweredby;?>
		</div>
	</body>
</html>

