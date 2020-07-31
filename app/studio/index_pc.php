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
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:767px)">
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_user_base.$userid.$dir_myApp; ?>/style.css"/>
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css" id="colorchange" />
	
	<title><?php echo $_local->gui->pcd_studio; ?></title>
	<script language="javascript" src="config.js"></script>
	<script language="javascript" src="js/data.js"></script>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/render.js"></script>	
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="js/wizard.js"></script>
	<script language="javascript" src="../public/js/notify.js"></script>
	<script language="javascript" src="<?php echo $dir_user_base.$userid.$dir_myApp; ?>/userinfo.js"></script>
	<script language="javascript" src="module/editor/language/default.js"></script>	
	<script language="javascript" src="module/editor/language/<?php echo $currLanguage; ?>.js"></script>
	
	<script language="javascript" src="module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>
	
	<!--加载语言文件 -->
	<script language="javascript" src="language/default.js"></script>
	<?php
	if(file_exists("../user/App/language/$currLanguage.js")){
		echo("<script language=\"javascript\" src=\"../user/App/language/$currLanguage.js\"></script>");
	}
	else{
		echo("<script language=\"javascript\" src=\"language/$currLanguage.js\"></script>");
	}
	?>
	<!--加载语言文件结束 -->
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
				//setCookie('language',g_langrage,365);
				window.location.assign("index_pc.php?language="+g_langrage);
			}
	</script>
</head>
<body class="indexbody" onload="wizard_palicannon_init()">
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
				<button><a href="index.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1018'];?></a></button>
				<button class="selected"><?php echo $module_gui_str['editor_wizard']['1002'];?></button>
				<button><a href="filenew.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1064'];?></a></button>
				<button><a href="index_tools.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1052'];?></a></button>
				
			
			</div>
			<div class="toolgroup1">
				
				<span><?php echo $module_gui_str['editor']['1050'];?></span>
				<select id="id_language" name="menu" onchange="menuLangrage(this)">
					<option value="en" >English</option>
					<option value="si" >සිංහල</option>
					<option value="my" >myanmar</option>
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
	<div class="index_inner" style="width: 100%;">
		<div id="search_div">
			<input type="input" style="display: inline;width: 15em;" />
			<button><?php echo $module_gui_str['editor_palicannon']['1002'];?></button>	
		</div>
<div class="editor_wizard">
	<div id="wizard_div"></div>
	<div class="editor_wizard_pali_cannon">
		<div class="pali_book_select_div_shell">
			<div class="pali_book_select_div">
				<div class="book_index_shell">
					<ul id="id_wizard_palicannon_index_c1" class="pali_book_select">
					</ul>
				</div>				
					
				<div class="book_index_shell">				
					<div id="id_wizard_palicannon_index_c2" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">				
					<div id="id_wizard_palicannon_index_c3" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">					
					<div id="id_wizard_palicannon_index_c4" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_index_book" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h1" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h2" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h3" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h4" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h5" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h6" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h7" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h8" class="pali_book_select">
					</div>
				</div>
				
				<div class="enter"></div>
			</div>
		</div>
			
			<div id="wizard_palicannon_par_select">
				<a name="toc_root"></a>
				<div id="wizard_palicannon_par_select_toc" class="fixedsticky">
				
				</div>
				<div id="wizard_palicannon_par_select_text">
					<div id="wizard_palicannon_par_select_text_head">
						<h2 id="wizard_palicannon_par_select_text_head_bookname"><span  ><?php echo $module_gui_str['editor_wizard']['1012'];?></span></h2><span id="star_lv"></span>		
						<div class="wizard_palicannon_par_select_text_head_inner">
							<div id='para_res_list'></div>
							<div id="wizard_palicannon_par_select_text_head_res">
							</div>
						</div>
						
						<div id="wizard_palicannon_par_select_text_head_button">
							<form action="project.php" method="post" onsubmit="return pali_canon_edit_now(this)" target="_blank">
								<div style="display:none;">
									<input type="input" name="op" value="create" />
									<textarea id="project_new_res_data" rows="3" cols="18" name="data"></textarea><br>
								</div>
								<input type="submit" value="立即编辑" />
							</form>
							<!--
							<button onclick="book_res_edit_now(1)">
								<svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_assignment_returned"></use></svg>
								<?php echo $module_gui_str['editor_wizard']['1013'];?>
							</button>
							-->
							<button  onclick="explorer_res_add_to_list()">
								<svg class="icon" viewBox="0 0 24 24" id="ic_add_shopping_cart_24px" ><path d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4h-.01l-1.1 2-2.76 5H8.53l-.13-.27L6.16 6l-.95-2-.94-2H1v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.13 0-.25-.11-.25-.25z"></path></svg>
								添加到编辑列表
							</button>
						</div>
					</div>
					<div id="wizard_palicannon_par_select_text_body">
					</div>
				</div>
				<div class="enter"></div>
			</div>
		<div id="palicannon_par_res_list_shell">
		<div id="palicannon_par_res_list">
		</div>
		</div>
	</div>
</div>
		
</div>
	<!--  Tool bar on right side -->
	<div class="right_tool_btn">
		<button onclick="editor_show_right_tool_bar(true)">
		<svg class="icon">
    		<use xlink:href="svg/icon.svg#ic_move_to_inbox"></use>
		</svg>
		</button>
	</div>
	<div id="right_tool_bar" onmouseover="editor_show_right_tool_bar(true)">
		<div id="right_tool_bar_title">
		<button class="res_button" style="padding: 0" onclick="editor_show_right_tool_bar(false)">
			<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_clear"></use></svg>
		</button>
		</div>
		<div id="right_tool_bar_inner">
		<div id="pc_res_loader">
			<div id="pc_res_load_button">
				<button  id="id_open_editor_load_stream"  onclick="open_editor_load_stream()"><?php //echo $module_gui_str['editor']['1030'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_cloud_download"></use></svg>
				</button>
				<button  id="id_cancel_stream" onclick="append_stream()"><?php //echo $module_gui_str['editor']['1028'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_note_add"></use></svg>
				</button>
				<button  id="pc_empty_download_list" onclick="pc_empty_download_list()"><?php //echo $module_gui_str['editor']['1045'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_delete"></use></svg>
				</button>
				<button onclick="get_pc_res_download_list_from_cookie()"><?php //echo $module_gui_str['editor']['1081'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_autorenew"></use></svg>
				</button>
				<input type="checkbox" style="display:none;" checked /><?php //echo $module_gui_str['editor']['1088'];?>
			</div>
			
			<div id="pc_res_list_div">
			</div>
			<div id="id_book_res_load_progress"></div>
			<canvas id="book_res_load_progress_canvas" width="300" height="30"></canvas>
		</div>
		</div>
	</div>
	<!--  Tool bar on right side end -->
	
<div class="foot_div">
<?php echo $module_gui_str['editor']['1066'];?>
</div>
</body>
</html>

