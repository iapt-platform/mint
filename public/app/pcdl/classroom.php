<?php
//require 'checklogin.inc';
require 'config.php';

if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
	$_COOKIE["language"]=$currLanguage;
}
else{
	if(isset($_COOKIE["language"])){
		$currLanguage=$_COOKIE["language"];
	}
	else{
		$currLanguage="en";
		$_COOKIE["language"]=$currLanguage;
	}
}

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
	<script src="../public/js/jquery.js"></script>
	
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/style_new.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	<title>PCD Libery</title>
	
	<script language="javascript" src="../app/js/data.js"></script>
	<script language="javascript" src="../app/js/common.js"></script>
	<script language="javascript" src="../app/js/render.js"></script>	
	<script language="javascript" src="../app/js/xml.js"></script>
	<script language="javascript" src="../app/js/editor.js"></script>
	<script language="javascript" src="js/wizard.js"></script>
	<script language="javascript" src="js/index.js"></script>
	<script language="javascript" src="js/search.js"></script>

	<!--<script language="javascript" src="<?php echo $dir_user_base.$userid.$dir_myApp; ?>/userinfo.js"></script>-->

	<script language="javascript" src="../app/module/editor/language/default.js"></script>	
	<script language="javascript" src="../app/module/editor/language/<?php echo $currLanguage; ?>.js"></script>
	
	<script language="javascript" src="../app/module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="../app/module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>
	

	<!--加载语言文件 -->
	<script language="javascript" src="language/default.js"></script>
	<?php

	?>
	<!--加载语言文件结束 -->
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script type="text/javascript">
	
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
				window.location.assign("index_pc.php?language="+g_language);
			}

	</script>

</head>
<body class="indexbody" onload="index_page_init()">

	<!-- tool bar begin-->
	<div id="header_nav" class='index_toolbar tool_bar_bg'>
			<div id="index_search">
				<input id="search_input" placeholder="搜作者/书名/标题"  type="input" onfocus="main_menu_show(4)" onkeyup="search_input_keyup(event,this)">
			</div>
			<div id="type">
				<button onclick="main_menu_show(0)">推荐</button>
				<button onclick="main_menu_show(1)">圣典</button>
				<button onclick="main_menu_show(2)">分类</button>
				<button onclick="main_menu_show(3)">我</button>
			</div>
			<div id="tool_bar_right" class="toolgroup1">
				<select id="id_language" name="menu" onchange="menuLangrage(this)">
					<option value="en" >English</option>
					<option value="sinhala" >සිංහල</option>
					<option value="zh" >简体中文</option>
					<option value="tw" >繁體中文</option>
				</select>
			
			<?php 
				echo $_local->gui->welcome;
				echo "<a href=\"setting.php?item=account\">";
				//echo $_COOKIE["nickname"];
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
	<a name="pagetop"></a>	
	<div class="index_inner" style="width: 100%;">
		<!--推荐-->
		<div id="page_0" class="page">
			<div class="page_header">
				<div id="main_menu_0">
					<div class="main_menu">
						<div class="main_menu_inner">
							<ul id="main_menu_2_inner" class="main_menu_select">
								<li class="menu_list_item" onclick="search_best('all')">全部</li>
								<li class="menu_list_item" onclick="search_best('new')">最新</li>
								<li class="menu_list_item" onclick="search_best('hot')">热门</li>
								<li class="menu_list_item" onclick="search_best('禅修')">推荐</li>
							</ul>
						</div>				
					</div>
				</div>
			</div>
			
			<div class="page_body">

					<!--左侧过滤器-->
					<div id="par_select_filter" class="left_nav_bar fixedsticky">
			
					</div>
					<!--左侧过滤器 结束-->
					
					<div id="page_best_content" class="book_list">
		
					</div>
					
					<!--右侧段落信息-->
					<div  id="res_info_0"  class="right_nav_bar" >
					</div>
					<!--右侧段落信息 结束-->
			</div>	
		
		</div>

<!--三藏-->
		<div id="page_1" class="page" >
			<div class="page_header">
				<div id="main_menu_1">
					<div class="main_menu">
						<div class="main_menu_inner">
							<ul id="id_wizard_palicannon_index_c1" class="main_menu_select">
							</ul>
						</div>				
					</div>
				</div>
			</div>
			
			<div class="page_body">

					<!--左侧过滤器-->
				<div id="par_select_filter" class="left_nav_bar fixedsticky">
					<div id="uhome_nav">
							<ul>
								<li><input type="checkbox" />中文(234)</li>
								<li><input type="checkbox" />英文(10)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />翻译(2)</li>
								<li><input type="checkbox" />改编(0)</li>
								<li><input type="checkbox" />书摘(6)</li>
								<li><input type="checkbox" />逐词译(206)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />玛欣德尊者(2)</li>
								<li><input type="checkbox" />庄春江(0)</li>
								<li><input type="checkbox" />元亨寺(6)</li>
								<li><input type="checkbox" />Ven. Bodhi(6)</li>
							</ul>
							<br />
							<div>
							标签：<br>
							故事 法句 持戒
							</div>
						</div>				
				</div>
				<!--左侧过滤器 结束-->
					
				<div id="pali_book_item_list" class="book_list">
		
				</div>
					
					<!--右侧段落信息-->
				<div id="res_info_1" class="right_nav_bar_off" >
				</div>
					<!--右侧段落信息 结束-->
			</div>	
		
		</div>

<!--分类-->
		<div id="page_2" class="page">
			<div class="page_header">
				<div id="main_menu_2">
					<div class="main_menu">
						<div class="main_menu_inner">
							<ul id="main_menu_2_inner" class="main_menu_select">
								<li class="menu_list_item" onclick="search_tag('故事')">故事</li>
								<li class="menu_list_item" onclick="search_tag('布施')">布施</li>
								<li class="menu_list_item" onclick="search_tag('持戒')">持戒</li>
								<li class="menu_list_item" onclick="search_tag('禅修')">禅修</li>
								<li class="menu_list_item" onclick="search_tag('比库')">比库</li>
								<li class="menu_list_item" onclick="search_tag('僧团')">僧团</li>
								<li class="menu_list_item" onclick="search_tag('传记')">传记</li>
							</ul>
						</div>				
					</div>
				</div>
			</div>
			
			<div class="page_body">

					<!--左侧过滤器-->
					<div id="par_select_filter" class="left_nav_bar fixedsticky">
						<div id="uhome_nav">
							<ul>
								<li><input type="checkbox" />中文(234)</li>
								<li><input type="checkbox" />英文(10)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />翻译(2)</li>
								<li><input type="checkbox" />改编(0)</li>
								<li><input type="checkbox" />书摘(6)</li>
								<li><input type="checkbox" />逐词译(206)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />玛欣德尊者(2)</li>
								<li><input type="checkbox" />庄春江(0)</li>
								<li><input type="checkbox" />元亨寺(6)</li>
								<li><input type="checkbox" />Ven. Bodhi(6)</li>
							</ul>
							<br />
							<div>
							标签：<br>
							故事 法句 持戒
							</div>
						</div>				
					</div>
					<!--左侧过滤器 结束-->
					
					<div id="category_item_list" class="book_list">
		
					</div>
					
					<!--右侧段落信息-->
					<div  id="res_info_2"  class="right_nav_bar_off" >
					</div>
					<!--右侧段落信息 结束-->
			</div>	
		
		</div>
	
	<!--我的-->
		<div id="page_3" class="page">
			<div class="page_header">
				<div id="main_menu_3">
					<div class="main_menu">
						<div class="main_menu_inner">
							<ul id="main_menu_3_inner" class="main_menu_select">
								<li class="menu_list_item" onclick="my_dighest()">书摘</li>
								<li class="menu_list_item" onclick="my_document()">我的文档</li>
								<li class="menu_list_item" onclick="my_favorite()">收藏</li>
							</ul>
						</div>				
					</div>
				</div>
			</div>
			
			<div class="page_body">

					<!--左侧过滤器-->
					<div id="par_select_filter" class="left_nav_bar fixedsticky">
						<div id="uhome_nav">
							<ul>
								<li><input type="checkbox" />中文(234)</li>
								<li><input type="checkbox" />英文(10)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />翻译(2)</li>
								<li><input type="checkbox" />改编(0)</li>
								<li><input type="checkbox" />书摘(6)</li>
								<li><input type="checkbox" />逐词译(206)</li>
							</ul>
							<br />
							<ul>
								<li><input type="checkbox" />玛欣德尊者(2)</li>
								<li><input type="checkbox" />庄春江(0)</li>
								<li><input type="checkbox" />元亨寺(6)</li>
								<li><input type="checkbox" />Ven. Bodhi(6)</li>
							</ul>
							<br />
							<div>
							标签：<br>
							故事 法句 持戒
							</div>
						</div>				
					</div>
					<!--左侧过滤器 结束-->
					
					<div class="book_list">
		
					</div>
					
					<!--右侧段落信息-->
					<div  id="res_info_3"  class="right_nav_bar_off" >
					</div>
					<!--右侧段落信息 结束-->
			</div>	
		
		</div>
		<!--我的结束-->

	<!--搜索-->
		<div id="page_4" class="page">
			<div id="page_header_4" class="page_header">
				<div id="main_menu_4">
					<div class="main_menu">
						<div class="main_menu_inner">
							<input id="search_input2" placeholder="搜作者/书名/标题"  type="input" onkeyup="search_input_keyup(event,this)">
						</div>				
					</div>
				</div>
			</div>
			
			<div class="page_body">
				<!--左侧过滤器-->
				<div id="search_left_nav" class="left_nav_bar fixedsticky">
				
				</div>
				<!--左侧过滤器 结束-->
					
				<div id="search_result" class="book_list">
		
				</div>
					
				<!--右侧段落信息-->
				<div id="res_info_4" class="right_nav_bar_off" >
				</div>
				<!--右侧段落信息 结束-->
			</div>	
		
		</div>
	</div>
	
	<div id="para_res_list_shell" class="page_bg">
		<div id="para_res_list_header" class="tool_bar_bg" >
		<div class="ui-icon-button ui-icon ui-icon-carat-l"></div><a  onclick="close_res_info()">返回</a>
		</div>
		<div id="res_list_back"></div>
		<div id="para_res_list">
		</div>
	</div>

<div id="footer_nav" class="footer_navbar tool_bar_bg">
	<div class="navbar_button" onclick="main_menu_show(0)">
		<div class="nav_icon">
			<svg class="small_icon">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../app/svg/icon.svg#ic_star"></use>
			</svg>
		</div>
		<div class="nav_text btn_color">推荐</div>
	</div>
	
	<div class="navbar_button" onclick="main_menu_show(1)">
		<div class="nav_icon">
			<svg class="small_icon">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../app/svg/icon.svg#ic_grid"></use>
			</svg>
		</div>
		<div class="nav_text btn_color">三藏</div>
	</div>
	
	<div class="navbar_button" onclick="main_menu_show(2)">
		<div class="nav_icon">
			<svg class="small_icon">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../app/svg/icon.svg#ic_list"></use>
			</svg>
		</div>
		<div class="nav_text btn_color">分类</div>
	</div>

	<div class="navbar_button" onclick="main_menu_show(4)">
		<div class="nav_icon">
			<svg class="small_icon">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../app/svg/icon.svg#ic_search"></use>
			</svg>
		</div>
		<div class="nav_text btn_color">搜索</div>
	</div>
	

	<div class="navbar_button" onclick="main_menu_show(3)">
		<div class="nav_icon">
			<svg class="small_icon">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../app/svg/icon.svg#ic_user"></use>
			</svg>
		</div>
		<div class="nav_text btn_color">我的</div>
	</div>
</div>
	
<div class="foot_div">

<?php echo $_local->gui->poweredby;?>
</div>
</body>
</html>

