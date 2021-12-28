<?php
if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
}
else{
	$currLanguage="en";
}

if(isset($_POST["word"])){
	$currWord=$_POST["word"];
}
else{
	$currWord="";
}

if(isset($_POST["mode"])){
	$currMode=$_POST["mode"];
}
else{
	$currMode="children";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	<link type="text/css" rel="stylesheet" href="css/alertify.css" id="alertifyCSS">
	<link type="text/css" rel="stylesheet" href="../user/App/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/dictionary.css"/>
	
	<title>Maha Paritta</title>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/dictionary.js"></script>
	<script language="javascript" src="language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="../user/Config/dictlist.js"></script>
	<script language="javascript">
	</script>
	<script language="javascript">
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
		<?php
			echo "g_findWord='".$currWord."';";
			echo "g_findMode='".$currMode."';";
		?>


	</script>
</head>
<body class="mainbody" id="mbody" onLoad="dict_windowsInit()">
		<!-- tool bar begin-->
		<div id="btn_close_printprev"><a href="#" onclick="printpreview(false)">&nbsp;&nbsp; </a></div>
		<div id='toolbar'>
			<div class='mainview'>
				<button id="B_Navi" onclick="setNaviVisibility()" type="button">≡</button>
				<button id="menu_button_home" onclick="goHome()" type="button">Home</button> 
				<button id="B_FontReduce" type="button" onclick="setPageFontSize(0.9)">A-</button> 
				<button id="B_FontGain" type="button" onclick="setPageFontSize(1.1)">A+</button>
				<button id="B_Day" type="button" onclick="setPageColor(0)">White</button>
				<button id="B_Sunset" type="button" onclick="setPageColor(1)">dawn</button>
				<button id="B_Night" type="button" onclick="setPageColor(2)">Night</button>
				<span id="debug"></span>
			</div>
		</div>	
		<!--tool bar end -->

	<div class="main">
		<br/><br/>
		<!-- nav begin--> 
		<div id="leftmenuinner">
			<div id="menubartoolbar">
				<select name="menu" onchange="menuSelected(this)">
					<option value="menu_pali_cannon">Pali Cannon</option>
					<option value="menu_toc" >Table Of Contents</option>
					<option value="menu_bookmark">Book Mark</option>
					<option value="menu_file">File</option>
					<option value="menu_display">Display</option>
					<option value="dict_menu_dict"  selected>Dictionary</option>
				</select>
				<!--<button id="menu_button_note" onclick="setObjectVisibilityAlone('navi_toc&navi_bookmark','navi_note')" type="button">Note</button> -->
			</div>

			<div class='toc' id='leftmenuinnerinner'>
				<div class="menu" id="dict_menu_dict">
						<h1>Dictionary</h1>
						<div class="submenu">
							<p class="submenu_title" onclick="submenu_show_detail(this)"><span>-</span>Tools</p>
							<div class="submenu_details">
								<div>
								<a href="dictadmin/user/phpliteadmin.php" target="user_dict_admin">User Dictionary</a><br >
								<a href="dictadmin/system/phpliteadmin.php" target="system_dict_admin">System Dictionary</a><br >
								<a href="dictadmin/3rd/phpliteadmin.php" target="3rd_dict_admin">3rd Dictionary</a><br >
								</div>
							</div>
						</div>
						
						<div class="submenu">
							<p class="submenu_title" onclick="submenu_show_detail(this)"><span>-</span>Search</p>
							<div class="submenu_details">
								<div id="basic_dict_list">
								loading Dictionary List...
								</div>
								<div>
								<button type="button" onclick="menu_dict_match()">Search</button>
								<div id="id_dict_match_inner">
								</div>
								<p id="id_dict_msg"></p>
								</div>
							</div>
						</div>
						
						
						<div class="submenu">
							<p class="submenu_title" onclick="submenu_show_detail(this)"><span>-</span>User Dictionary</p>
							<div class="submenu_details">
							<div id="id_dict_user">
							</div>
							</div>
						</div>
						
				</div>
				
			</div>
		</div>
		<!-- nav end -->	
		
		<!--right side begin-->
		<div class='mainview' id='body_mainview'>
		
			<div id="sutta_text">
			</div>
			
	<!--  infomation panal -->	
		<div id="id_func_panal">
				<select id="id_info_window_select" name="menu" onchange="windowsSelected(this)">
					<option value="view_vocabulary">Vocabulary</option>
					<option value="view_dict_all">Dictionary</option>
					<option value="view_dict_curr">Word Family</option>
					<option value="view_debug">Debug</option>
				</select>
			<div id='id_func_panal_inner'>
				<div id="word_table">
					<p><br/>Word List <input id="button_wordlist_refresh" onclick="refreshWordList()" type="button" value="Refresh" /> </p>
					<div id="word_table_inner"></div>
				</div>

				<div id="id_dict_match_result">
					<p><br/>Dictionary Match Result </p>
					<div id="id_dict_match_result_inner"></div>
				</div>
				
				<div id="id_dict_curr_word">
					<form action="dictionary.php"  method="POST">
						<select  name="mode" >
							<option value="parent" >Parent</option>
							<option value="children" selected>Children</option>
						</select>	
						<input type="text" name="word" size="20" value="<?php echo $currWord; ?>" />
						Type:
						<input type="text" name="type" size="10">
						
						<input type="submit" value="Submit">
					</form> 
					<div id="id_dict_curr_word_inner"></div>
				</div>		
				
				<div class="debugMsg" id="id_debug"><!--调试信息-->
					<div id="id_debug_output"></div>
					<p>HTML 正文数据</p>
					<textarea id="htmlstring" rows="10" cols="80"></textarea>
					<p>XML 数据</p>
					<textarea id="xmlout" rows="10" cols="80"></textarea>	
				</div>
			</div>
		</div>	

				
<!--  infomation panal end -->				
		</div>
		<!--right side end-->
	</div>



</body>
</html>

