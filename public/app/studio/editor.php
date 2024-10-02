<?php
require_once 'checklogin.inc';
require_once '../public/config.php';
require_once '../public/load_lang.php';

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

	<title id="file_title"><?php echo $_local->gui->pcd_studio; ?></title>	

	<link rel="shortcut icon" href="../favicon.icon" />
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	
	<link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>
	<?php
		if(file_exists($dir_user_base.$userid.$dir_myApp."/style.css")){
			echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"".$dir_user_base.$userid.$dir_myApp."/style.css\"/>";
		}
	?>

	<script src="../guide/guide.js"></script>
	<link type="text/css" rel="stylesheet" href="../guide/guide.css"/>
	<script src="../public/js/marked.js"></script>

	<script src="../public/js/jquery-3.6.js"></script>
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>

	<script language="javascript" src="config.js"></script>
	<script language="javascript" src="../pali_sent/pali_sent_list.js"></script>
	<script language="javascript" src="../usent/usent_ref.js"></script>
	<script language="javascript" src="js/data.js"></script>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/render.js"></script>	
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="js/wizard.js"></script>
	<script language="javascript" src="js/wordmap.js"></script>
	<script language="javascript" src="js/dict.js"></script>
	<script language="javascript" src="js/relation.js"></script>

	<script language="javascript" src="js/relation_list.js"></script>
	
	<script language="javascript" src="sent/sent.js"></script>
	<script language="javascript" src="../public/js/notify.js"></script>

	<script language="javascript" src="../widget/notify.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/notify.css"/>

	<script language="javascript" src="../public/js/comm.js"></script>
	<script language="javascript" src="../public/js/localforage.min.js"></script>
	<script language="javascript" src="../public/script/my.js"></script>
	<script src="../../node_modules/mermaid/dist/mermaid.min.js"></script>

	
	<script language="javascript" src="module/editor/language/default.js"></script>	


	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="../term/term_popup.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term.css"/>

	<script src="./js/message.js"></script>
	<script src="../uwbw/update.js"></script>
	<script src="../usent/usent.js"></script>
	<script src="../fileindex/file_info.js"></script>

	<script  src="../channal/channal.js"></script>
	<script>
		get_channel_list_callback=renderChannelList();
		

	</script>

	
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
		var gConfigDirMydocument="<?php echo _DIR_USER_DOC_."/".$userid._DIR_MYDOCUMENT_; ?>/";
		
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
	
</head>
<body class="mainbody" id="mbody" onLoad="editor_windowsInit()">
	<style>
	.term_link:hover .guide_contence {
		display: inline-block;
	}
	
	.trans_text_block{
		border-left: 5px solid #ccd1ff;
		border-color: #ccd1ff;
		background-color:unset;
		margin-bottom: 0.5em;
		padding: 0.5em 0.5em 0.1em 0.5em;
		padding-top:0;
	}
	.translate_sent_head{
		height: 17em;
		overflow-y: scroll;
	}
	.translate_sent_head_content .readonly{
		border-color: #d1d1d1;
		background-color: #f1f1f1;
	}
	.trans_text_content{
		color: unset;
	}
	.trans_text_content p{
		margin-block-start: 0.3em;
		margin-block-end: 0;
	}
	.trans_text_info{
		font-size: 80%;
		border-top: 1px solid gray;
		padding-top: 5px;
		color: var(--detail-color);
		display:flex;
		justify-content: space-between;
		border:none;
	}

	.trans_text_info .tag{
		font-size: 75%;
	}
	.trans_text_info .tools{
		/*visibility: hidden;*/
	}
	.trans_text_block:hover .trans_text_info .tools{
		visibility: visible;
	}
	wnh{
	background-color: var(--link-hover-color);
    color: var(--btn-color);
    border-radius: 0.8em;
    cursor: pointer;
    padding: 2px;
    font-size: 80%;
    display: inline-block;
	min-width: 1.2em;
	width: fit-content;
	height: 1.2em;
	line-height: 1.2em;
    text-align: center;
	}	
	#left_tool_bar {
		position: fixed;
		top: 0;
		left: 0;
		background-color: var(--tool-bg-color);
		color: var(--tool-color);
		height: 100%;
		width: 2.5em;	
		font-size: 100%;
		z-index: 16;

		display: -webkit-flex;
		display: -moz-flex;
		display: flex;
		flex-direction: column;
		-webkit-align-items: center;
		-moz-align-items: center;
		align-items: center;
		-webkit-justify-content: space-between;
		-moz-justify-content: space-between;
		justify-content: space-between;
		padding: 4em 4px 1em 4px;
		border-right: 1px solid var(--tool-bg-color3);
	}
	#left_tool_bar::-webkit-scrollbar {
		display: none;
	}
	.border_top{
		border-top: 1px solid var(--tool-line-color);
		width: 1.5em;
		margin: 10px 5px 10px 5px;
		display: inline-block;
	}
	.left_panal_caption{
		border-bottom: 1px solid var(--tool-bg-color3);
		padding: 8px 0 8px 3.2em;
		font-weight: 700;
	}
	.left_panal_content{
		padding: 8px 0 8px 3.2em;
	}
	#svg_parent2{
		width: 1em;
		margin-left: -1em;
	}
	guide {
		margin: 0 15px 0 -8px;
	}
	.translate_sent_head_toolbar {
		display: none;
	}
	div#input_org_select{
		position: relative;
	}
	div#input_org_select .case_dropbtn {
		padding: 0 6px;
		border-radius: 4px;
		min-width: 1em;
		min-height: 1.3em;
		background: url(../../node_modules/bootstrap-icons/icons/chevron-double-down.svg);
		background-repeat: no-repeat;
		background-position-x: center;
		background-position-y: bottom;
		background-origin: content-box;
		background-color: var(--main-color1);
		white-space: nowrap;
	}
	#parent_grammar{
		padding: 0 6px;
		border-radius: 4px;
		min-width: 1em;
		min-height: 1.3em;
		background: url(../../node_modules/bootstrap-icons/icons/chevron-double-down.svg);
		background-repeat: no-repeat;
		background-position-x: center;
		background-position-y: bottom;
		background-origin: content-box;
		background-color: var(--main-color1);
		white-space: nowrap;
	}

	#word_mdf_parts_dropdown  a {
		width: 100%;
		display: inline-block;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}
	#merge_button{
		position: absolute;
		left: 7em;
		border: unset;
		height: 6em;
		width: 3em;
		padding: unset;
		transform: scaleX(0.6);
	}
	#merge_button>.icon{
		fill: var(--link-color);
	}

	#merge_button:hover{
		background: unset;
	}
	#merge_button:hover>.icon{
		fill: var(--link-hover-color);
	}
	#om_dropdown_area::-webkit-scrollbar {
		width: 10px;
		height: 10px;
	}
	#om_dropdown_area::-webkit-scrollbar-thumb {
		background-color: var(--link-color);
	}
	#om_dropdown_area::-webkit-scrollbar-thumb:hover {
		background-color:var(--tool-link-hover-color);
	}
	#om_dropdown_area::-webkit-scrollbar-button:single-button {
		width: 10px
		height: 10px;
		display: block;
		background-color: var(--link-color);
		border-radius: 10px;
	}

	#om_dropdown_area::-webkit-scrollbar-button:single-button:hover {
		background-color:var(--tool-link-hover-color);
	}

		</style>
	<link type="text/css" rel="stylesheet" href="css/print.css" media="print" />
	<style id="display_set">
	</style>
	<!--左侧工具栏-->
	<div id="left_tool_bar">
		<div>
<?php
$plugin_list = json_decode(file_get_contents("plugin/index.json"));
foreach($plugin_list as $info){
	if($info->attach=="left_top" && $info->enable=="true"){
		echo "<button id=\"left_toc\" type=\"button\" class=\"icon_btn\" onclick=\"setNaviVisibility('{$info->id}')\" title=\"";
		if(substr($info->tooltip,0,1)=="#"){
			$realTipText = substr($info->tooltip,1);
			if(isset($_local_arr[$realTipText])){
				echo $_local_arr[$realTipText];
			}
			else{
				echo $realTipText;
			}
		}
		else{
			echo $info->tooltip;
		}
		echo "\">";
		echo "<svg class=\"icon\">";
		$iconFile = "plugin/".$info->dir."/".$info->icon;
		echo "<use xlink:href=\"{$iconFile}\"></use>";
		echo "</svg>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "</button>";
	}
}
?>
		</div>
		<div>
			<div>
<?php
foreach($plugin_list as $info){
	if($info->attach=="left_mid" && $info->enable=="true"){
		echo "<button id=\"left_toc\" type=\"button\" class=\"icon_btn\" onclick=\"setNaviVisibility('{$info->id}')\" title=\"";
		if(substr($info->tooltip,0,1)=="#"){
			$realTipText = substr($info->tooltip,1);
			if(isset($_local_arr[$realTipText])){
				echo $_local_arr[$realTipText];
			}
			else{
				echo $realTipText;
			}
		}
		else{
			echo $info->tooltip;
		}
		echo "\">";
		echo "<svg class=\"icon\">";
		$iconFile = "plugin/".$info->dir."/".$info->icon;
		echo "<use xlink:href=\"{$iconFile}\"></use>";
		echo "</svg>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "</button>";
	}
}
?>	
			</div>
			<span class="border_top"></span>
			<div>
<?php
foreach($plugin_list as $info){
	if($info->attach=="left_bottom" && $info->enable=="true"){
		echo "<button id=\"left_toc\" type=\"button\" class=\"icon_btn\" onclick=\"setNaviVisibility('{$info->id}')\" title=\"";
		if(substr($info->tooltip,0,1)=="#"){
			$realTipText = substr($info->tooltip,1);
			if(isset($_local_arr[$realTipText])){
				echo $_local_arr[$realTipText];
			}
			else{
				echo $realTipText;
			}
		}
		else{
			echo $info->tooltip;
		}		
		echo "\">";
		echo "<svg class=\"icon\">";
		$iconFile = "plugin/".$info->dir."/".$info->icon;
		echo "<use xlink:href=\"{$iconFile}\"></use>";
		echo "</svg>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "</button>";
	}
}
?>
			</div>
		</div>
	</div>
	
	<!--关闭打印模式 -->
	<div id="btn_close_printprev">
		<a href="#" onclick="printpreview(false)">&nbsp;&nbsp; </a>
	</div>
	
	<!--顶部工具栏tool bar begin-->
	<div id='toolbar'>
		<!--左侧的区块-->
		<div style="display: inline-flex;flex: 4;max-width:40vw">

			<svg class="icon" style="height: 2.7em; width: 12em;">
				<use xlink:href="../public/images/svg/wikipali_banner.svg#wikipali_banner"></use>
			</svg>

			<span style="height: fit-content;margin: auto 0;">
				<span id="net_up" style="display:none;"></span>
				<span id="msg_tool_bar"><svg class='icon'><use xlink:href='../studio/svg/icon.svg#pause_ms'></use></svg></span>
				<span id="net_down" style="display:none;"></span>
			</span>
			<span id="editor_doc_title" style="margin: auto 0;max-width: 20em;height: fit-content;position: inherit;"></span>
		</div>
		
		<!--工具栏中间的区块-->
		<div style="flex: 2;display: flex;justify-content: center;">
			<!--手机版显示工具栏按钮-->
			<span class="dropdown toolbtn" onmouseover="switchMenu(this,'topTools')">
				<div>
				<button class="icon_btn" onClick="switchMenu(this,'topTools')" id="tools_view">
					<svg class="icon">
						<use xlink:href="svg/icon.svg#ic_toc"></use>
					</svg>
				</button>
				</div>
			</span>
			

			<div class="toolgroup1" id="topTools">

<?php
#头部中间的插件
foreach($plugin_list as $info){
	if($info->attach=="top_mid" && $info->enable=="true"){
		echo "<style>";
		include "plugin/{$info->dir}/style.css";
		echo "</style>";
		echo "<script src=\"plugin/{$info->dir}/module_function.js\"></script>";
		
		echo "<div class='dropdown' onmouseover=\"switchMenu(this,'".$info->id."')\" onmouseout=\"hideMenu()\">";
		echo "<button class=\"dropbtn icon_btn\">";
		echo "<svg class=\"icon\">";
		$iconFile = "plugin/".$info->dir."/".$info->icon;
		echo "<use xlink:href=\"{$iconFile}\"></use>";
		echo "</svg>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "</button>";
		echo "<div class=\"dropdown-content\" id=\"".$info->id."\" style=\"width:auto\">";
		require "plugin/{$info->dir}/gui.html";
		echo "</div>";
		echo "</div>";	
				
	}
}
?>

					
				<!--显示模式-->
				<div class="dropdown" onmouseover="switchMenu(this,'menuUseMode')" onmouseout="hideMenu()" style="display:none;">
						<div style="">
							<button class="dropbtn icon_btn" onClick="switchMenu(this,'menuUseMode')" id="use_mode">	
								<svg class="icon">
									<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_reader_mode"></use>
								</svg>
							</button>
						</div>
						<div class="dropdown-content black_background" id="menuUseMode">
							<a href="#" onclick="setUseMode('Edit')">
								<svg class="icon">
									<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_mode_edit"></use>
								</svg>
								编辑模式
							</a>
							<a href="#" onclick="setUseMode('note')">
								<svg class="icon">
									<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_reader_mode"></use>
								</svg>
								笔记模式
							</a>
							<a href="#" onclick="setUseMode('Read')">
								<svg class="icon">
									<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_reader_mode"></use>
								</svg>
								阅读模式
							</a>
						</div>
					</div>

				<!--infomation panal-->
				<button id="info_panal" class="icon_btn" type="button" onclick="setInfoPanalVisibility()" title="<?php echo $_local->gui->info_panal;?>" style="display:none;">
					<svg class="icon">
						<use xlink:href="svg/icon.svg#ic_info_outline"></use>
					</svg>
				</button>

				
				<!--apply all-->
				<button id="B_ApplyAuto" class="icon_btn" onclick="applyAllSysMatch()" type="button" title="apply all" style="display:none;">
						<svg class="icon">
							<use xlink:href="svg/icon.svg#ic_done_all"></use>
						</svg>
					</button> 
				
				<!--Save-->
				<button id="B_Save" class="icon_btn" onclick="editor_save()" type="button" style="display:none;" title="<?php echo $_local->gui->save;?>">
						<svg class="icon">
							<use xlink:href="svg/icon.svg#ic_save"></use>
						</svg>
					</button>					
			
			</div>
			
		</div>
		
		<!--工具栏右侧的区块开始-->
		<div class="tab_a1" style="flex: 4;display: flex;justify-content: flex-end;">
<?php
foreach($plugin_list as $info){
	if($info->attach=="top_right" && $info->enable=="true"){
		echo "<button id=\"tab_rb_{$info->id}\" type=\"button\" class=\"icon_btn\" onclick=\"tab_click_b('{$info->id}','tab_rb_{$info->id}',right_panal_slide_toggle,'tab_rb_{$info->id}')\"  title=\"";
		if(substr($info->tooltip,0,1)=="#"){
			$realTipText = substr($info->tooltip,1);
			if(isset($_local_arr[$realTipText])){
				echo $_local_arr[$realTipText];
			}
			else{
				echo $realTipText;
			}
		}
		else{
			echo $info->tooltip;
		}
		echo "\">";
		echo "<svg class=\"icon\">";
		$iconFile = "plugin/".$info->dir."/".$info->icon;
		echo "<use xlink:href=\"{$iconFile}\"></use>";
		echo "</svg>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "<span class='icon_notify' id='icon_notify_{$info->id}'></span>";
		echo "</button>";
			
	}
}
?>		
		
		</div>

	</div>
	<!--顶部工具栏结束-->
	
	<!--文档载入进度条-->
	<div id="load_progress_div">
		<svg id="circleProcess" xmlns="http://www.w3.org/2000/svg">
			<circle id="circle" cx="50%" cy="50%" r="32%" ></circle>
		</svg>
	</div>	
				
	<span id="load_progress_num" ></span>
	<!--tool bar end -->
	
	<!--loading -->
	<svg viewBox="0 0 1000 4" id="loading_bar" xmlns="http://www.w3.org/2000/svg">
			<line x1="0" y1="2" x2="1000" y2="2" id="loading" stroke-width="5px" stroke-linecap="round"/>
	</svg>
	<!--tool bar end -->
	<!--文档载入进度条结束-->

	
	<style id="mycss">
	</style>
	
<div class="main">
		<!-- leftmenu begin--> 
		<div id="leftmenuinner" class="viewswitch_off" style="z-index:13;">

			<div id="menubartoolbar" style="display: none">
				<select id="id_editor_menu_select" name="menu" onchange="menuSelected(this)">
					<option value="menu_toc" selected><?php echo $_local->gui->content;?></option>				
					<option value="menu_pali_cannon"><?php echo $_local->gui->pali_canon;?></option>
					<option value="menu_bookmark"><?php echo $_local->gui->mark;?></option>
					<option value="menu_project"><?php echo $_local->gui->project;?></option>
					<option value="menu_dict"><?php echo $_local->gui->dict;?></option>
					<option value="menu_layout"><?php echo $_local->gui->layout;?></option>
					<option value="menu_plugin"><?php echo $_local->gui->plugin;?></option>
				</select>
			</div>
			<div id="menubartoolbar_New" style="display:none;">
				<ul class="common-tab">
					<li id="menu_toc_li" class="common-tab_li_act" onclick="menuSelected_2(menu_toc,'menu_toc_li','menu' )"><?php echo $_local->gui->content;?></li>
					<li id="menu_bookmark_li" class="common-tab_li" onclick="menuSelected_2(menu_bookmark,'menu_bookmark_li','menu' )"><?php echo $_local->gui->mark;?></li>
					<li id="menu_project_li" class="common-tab_li" onclick="menuSelected_2(menu_project,'menu_project_li','menu' )"><?php echo $_local->gui->project;?></li>
					<li id="menu_dict_li" class="common-tab_li" onclick="menuSelected_2(menu_dict,'menu_dict_li','menu' )"><?php echo $_local->gui->dict;?></li>
					<li id="menu_layout_li" class="common-tab_li" onclick="menuSelected_2(menu_layout,'menu_layout_li','menu' )"><?php echo $_local->gui->layout;?></li>
					<li id="menu_plugin_li" class="common-tab_li" onclick="menuSelected_2(menu_plugin,'menu_plugin_li','menu' )"><?php echo $_local->gui->plugin;?></li>
				</ul>
			</div>			
			
			<div class='toc' id='leftmenuinnerinner'>	
			<!-- toc begin -->
<?php
foreach($plugin_list as $info){
	if(($info->attach=="left_top" || $info->attach=="left_mid" ||$info->attach=="left_bottom") && $info->enable=="true"){
		echo "<div id=\"{$info->id}\">";
		echo "<style>";
		include "plugin/{$info->dir}/style.css";
		echo "</style>";
		echo "<script src=\"plugin/{$info->dir}/module_function.js\"></script>";
		echo "<div class=\"left_panal_caption\">";
		echo $info->caption;
		echo "</div>";
		echo "<div class=\"left_panal_content\">";
		require "plugin/{$info->dir}/gui.html";
		echo "</div>";
		echo "</div>";
	}
}
?>

			

			
			<!-- dictionary begin -->
				<style>
				<?php include 'module/editor_dictionary/style.css';?>
				</style>
				<?php 
				//require 'module/editor_dictionary/language/default.php';
				//require "module/editor_dictionary/language/$currLanguage.php";
				require 'module/editor_dictionary/gui.html';
				?>

			<!-- dictionary end -->
			
				<style>
				<?php include 'module/editor_palicannon/style.css';?>
				</style>
				<?php 
				//require 'module/editor_palicannon/language/default.php';
				//require "module/editor_palicannon/language/$currLanguage.php";
				//require 'module/editor_palicannon/gui.html';
				?>
						
			</div>
		
		</div>
		<!-- leftmenu end -->	
		<!-- leftmenu background begin--> 
		<div id='BV' class='blackscreen' onClick='setNaviVisibility()' style="z-index:10;"></div>
		
		<!--main edit begin-->
	<div class='mainview' id='body_mainview'>
		
		<!--  逐词解析与译文编辑区 -->
		<div id="sutta_text">
			<div class="sutta_top_blank"></div>
		</div>
			
		<!--  infomation panal -->	
		<div id="id_info_panal">
			<select id="id_info_window_select" name="menu" onchange="windowsSelected(this)">
				<option value="view_vocabulary"><?php echo $_local->gui->vocabulary;?></option>
				<option value="view_dict_all"><?php echo $_local->gui->inline_dictionary;?></option>
				<option value="view_dict_curr"><?php echo $_local->gui->my_dictionary;?></option>
				<option value="view_debug"><?php echo $_local->gui->debug;?></option>
			</select>
			<button type="button" onclick="setInfoPanalSize('hidden')"><?php echo $_local->gui->hidden;?></button>
			<button type="button" onclick="setInfoPanalSize('min')"><?php echo $_local->gui->min;?></button>
			<button type="button" onclick="setInfoPanalSize('half')"><?php echo $_local->gui->half;?></button>
			<button type="button" onclick="setInfoPanalSize('max')"><?php echo $_local->gui->max;?></button>
		
			<div id='id_info_panal_inner'>
				<div id="word_table">
					<p><br/><?php echo $_local->gui->word_list;?><input id="button_wordlist_refresh" onclick="refreshWordList()" type="button" value="<?php echo $_local->gui->refresh;?>" /> </p>
					<div id="word_table_inner"></div>
				</div>

				<div id="id_dict_match_result">
					<p><br/><?php echo $_local->gui->dictionary_match_result;?></p>
					<div id="id_dict_match_result_inner"></div>
				</div>
				
				<div id="id_dict_curr_word">
					<div id="id_dict_curr_word_inner"></div>
				</div>		
				
				<div class="debugMsg" id="id_debug"><!--调试信息-->
					<div id="id_debug_output"></div>
				</div>
			</div>
	
		</div>		

		<!--  infomation panal end -->				
	</div>
		<!-- end-->
	<!--class="main"-->
	<div class="debug_info"><span id="debug"></span></div>
	
	<!--逐词解析编辑窗口-->
	<div id="modifyDiv">
		<div id="modifywin" style="">
			<div>
				<ul id="id_select_modyfy_type" class="common-tab">
					<li id="detail_li" class="common-tab_li_act" onclick="select_modyfy_type('modify_detaile','detail_li')"><?php echo $_local->gui->detaile;?></li>
					<li id="mark_li" class="common-tab_li" onclick="select_modyfy_type('modify_bookmark','mark_li')"><?php echo $_local->gui->mark;?></li>
					<li id="note_li" class="common-tab_li" onclick="select_modyfy_type('modify_note','note_li')"><?php echo $_local->gui->note;?></li>
					<li id="spell_li" class="common-tab_li" onclick="select_modyfy_type('modify_spell','spell_li')"><?php echo "高级";//.$_local->gui->source?></li>
					
				</ul>
			</div>
			<div id="modify_detaile">
				<!-- 意思 -->
				<div class="edit_detail_p">
					<guide gid="studio_general_meaning">⑤</guide>
					<span class="edit_detail_span"><?php echo $_local->gui->meaning;?>：</span>
					<!--<button id="merge_button" onclick="copy_part_mean_to_mean()">
						<svg class="icon" style="transform: rotate(315deg);height: 4em;width: 6em;">
							<use xlink="http://www.w3.org/1999/xlink" href="../../node_modules/bootstrap-icons/bootstrap-icons.svg#arrow-90deg-right">
						</svg>
					</button>-->
					<input type="text" id="input_meaning" class="input_bar" value="" name="in_meaning">
					<div class="case_dropdown">
						<svg class="edit_icon">
							<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use>
						</svg>
						<div id="word_mdf_mean_dropdown" class="case_dropdown-content">
						</div>
					</div>
				</div>
				<!-- 拆分 -->
				<div class="edit_detail_p">
					<guide gid="studio_break_down">②</guide>
					<span class="edit_detail_span"><?php echo $_local->gui->part;?>：</span>
					<input type="text" id="input_org" class="input_bar" value="" name="in_org" onkeydown="match_key(this)" onkeyup="unicode_key(this) " onchange="input_org_change()">
					<div class="case_dropdown">
						<svg class="edit_icon">
							<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use>
						</svg>
						<div id="word_mdf_parts_dropdown" class="case_dropdown-content">
						</div>
					</div>				
				</div>
				<!-- 拆分意思 -->

				<div class="edit_detail_p" >
					<guide gid="studio_part_meaning">④</guide>
					<span class="edit_detail_span"><?php echo $_local->gui->partmeaning;?>：</span>
					<div id="input_org_select" class="input_bar" style="width:80%; display:inline-flex;justify-content: space-between;"></div>
					<input type="text" id="input_om" class="input_bar" value="" name="in_om" onchange='input_om_change(this)' onblur="input_org_switch('input_om','input_org_select')">
				</div>
				<!-- 格位 -->
				<div class="edit_detail_p">
					<guide gid="grammar_abbr">③</guide>
					<span class="edit_detail_span"><?php echo $_local->gui->gramma;?>：</span>
					<p><input type="text" id="input_case" value="" name="in_case" onblur="input_org_switch('input_case','input_select_case')" ></p>
					<div id="input_select_case" class="input_bar" style="width:80%; display:inline-flex;">
						<div style="display:inline-flex;">
							<span></span>
							<span></span>
							<span></span>
							<span></span>
						</div>
						<button style="margin-left:auto; display:none;" onclick="input_org_switch('input_select_case','input_case')">
							<?php echo $_local->gui->source;?>
						</button>
					</div>				
					<div class="case_dropdown">
							<svg class="edit_icon">
								<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use>
							</svg>
							<!--下拉菜单-->
							<div id="word_mdf_case_dropdown" class="case_dropdown-content">
							</div>
						</div>								
				</div>
				<!-- 语基 -->
				<div class="edit_detail_p">
					<guide gid="studio_parent">①</guide>
				<script>

				</script>
				<span class="thin_icon_btn" onclick="edit_show_prt_prt(this)">
					<svg id="svg_parent2" class="icon">
						<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_chevron_right_24px"></use>
					</svg>
				</span>
					<span class="edit_detail_span"><?php echo $_local->gui->parent;?>：</span>
					<input type="text" id="id_text_parent" class="input_bar" onkeydown="match_key(this)" onkeyup="unicode_key(this)" />
					<div class="case_dropdown">
						<svg class="edit_icon">
							<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use>
						</svg>
						<div id="word_mdf_parent_dropdown" class="case_dropdown-content">
						</div>
					</div>				
				</div>
				<!-- 词源 -->
				<div id="edit_detail_prt_prt" class="edit_detail_p" style="display:none;">
					<span style="display:flex;">
						<guide gid="studio_parent2">衍生原型</guide>
						<div class="case_dropdown" style="padding-left: 0.5em;width: 6em;display: flex;">
							<span style="padding-right: 4px;"></span>
							<span id="parent_grammar" style="display: inline-block;min-width: 2em;border-radius: 4px;padding: 0 4px;background-color: #8484843d;"></span>
							<input type="hidden" id="input_parent_grammar" />
							<div id="word_mdf_prt_prt_grammar_dropdown" style="margin-top: 22px;" class="case_dropdown-content">
								<a onclick="edit_parent_grammar_changed('')"><?php echo $_local->gui->empty; ?></a>
								<a onclick="edit_parent_grammar_changed('.pp.')"><?php echo $_local->gui->pp; ?></a>
								<a onclick="edit_parent_grammar_changed('.prp.')"><?php echo $_local->gui->prp; ?></a>				
								<a onclick="edit_parent_grammar_changed('.fpp.')"><?php echo $_local->gui->fpp; ?></a>
								<a onclick="edit_parent_grammar_changed('.caus.')"><?php echo $_local->gui->caus; ?></a>
								<a onclick="edit_parent_grammar_changed('.pass.')"><?php echo $_local->gui->pass; ?></a>
							</div>
						</div>
						<input type="text" id="id_text_prt_prt" class="input_bar" onkeydown="match_key(this)" onkeyup="unicode_key(this)" />
						<div class="case_dropdown">
							<svg class="edit_icon">
								<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use>
							</svg>
							<div id="word_mdf_prt_prt_dropdown" class="case_dropdown-content">
							</div>
						</div>
					</span>
					<div>
						<?php echo $_local->gui->relation; ?>
						<imput type="hidden" id="id_relation_text" value="" />
						<button onclick="rela_add_word()">➕</button>
						<div id="relation_div">
						</div>
					</div>
				</div>
				
			</div>
			
			<div id="modify_bookmark">
				<ul id="id_book_mark_color_select" class="bookmark-tab">
					<li id="id_bmc0" class="bookmarkcolorblock bookmarkcolor0" onclick="setBookMarkColor(this,'bmc0')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_clear">×</use>
						</svg>
					</li>
					<li id="id_bmc1" class="bookmarkcolorblock bookmarkcolor1" onclick="setBookMarkColor(this,'bmc1')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">1</use>
						</svg>
					</li>
					<li id="id_bmc2" class="bookmarkcolorblock bookmarkcolor2" onclick="setBookMarkColor(this,'bmc2')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">2</use>
						</svg>
					</li>
					<li id="id_bmc3" class="bookmarkcolorblock bookmarkcolor3" onclick="setBookMarkColor(this,'bmc3')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">3</use>
						</svg>
					</li>
					<li id="id_bmc4" class="bookmarkcolorblock bookmarkcolor4" onclick="setBookMarkColor(this,'bmc4')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">4</use>
						</svg>
					</li>
					<li id="id_bmc5" class="bookmarkcolorblock bookmarkcolor5" onclick="setBookMarkColor(this,'bmc5')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">5</use>
						</svg>
					</li>
					<li id="id_bmca" class="bookmarkcolorblock bookmarkcolora" onclick="setBookMarkColor(this,'bmca')">
						<svg class="icon">
								<use xlink:href="svg/icon.svg#ic_bookmark_on">A</use>
						</svg>
					</li>
				</ul>
				<textarea id="id_text_bookmark" rows="6" col="28" style="margin-left: 3px; margin-right: 3px; width: 95%;"></textarea>
			</div>
			
			<div id="modify_note">
				<textarea id="id_text_note" rows="7" col="28" style="margin-left: 3px; margin-right: 3px; width: 95%;"></textarea>
			</div>
			
			<div id="modify_spell">
				<span id="id_text_id"></span><br/>
				<?php echo $_local->gui->show;?><br />
				<input type="input" id="id_text_pali" onkeydown="match_key(this)" onkeyup="unicode_key(this)" /><br/>
				<?php echo $_local->gui->spell;?><br />
				<input type="input" id="id_text_real"  onkeydown="match_key(this)" onkeyup="unicode_key(this)" /><br/>
				<br/>
			</div>
			
			<div id="modify_apply">
			</div>
		</div>
	</div>
	<!--逐词解析编辑窗口结束-->
	
	<!--译文编辑窗口-->
	<div id="id_text_edit_form">
		<div id="id_text_edit_caption"  class="dialog-title">
			<div><button id="id_text_edit_cancel" type="button" onclick="edit_tran_cancal()"><?php echo $_local->gui->cancel;?></button></div>
			<div><span id="id_text_edit_caption_text">
				<?php echo $_local->gui->translation; ?></span></div>
			<div id="id_text_edit_bottom">
				<button id="id_text_edit_delete" type="button" onclick="edit_tran_delete()">
					<?php echo $_local->gui->delete;?>
				</button>
			</div>
			<div><button id="id_text_edit_save" type="button" onclick="edit_tran_save()"><?php echo $_local->gui->save;?></button></div>
		</div>
		<div id="id_text_edit_info">
			<select id="id_heading_edit_level" >
					<option value="0"><?php echo $_local->gui->normal;?></option>
					<option value="1"><?php echo $_local->gui->heading_1;?></option>
					<option value="2"><?php echo $_local->gui->heading_2;?></option>
					<option value="3"><?php echo $_local->gui->heading_3;?></option>
					<option value="4"><?php echo $_local->gui->heading_4;?></option>
					<option value="5"><?php echo $_local->gui->heading_5;?></option>
					<option value="6"><?php echo $_local->gui->heading_6;?></option>
					<option value="7"><?php echo $_local->gui->heading_7;?></option>
					<option value="8"><?php echo $_local->gui->heading_8;?></option>
			</select>
			<select id="id_text_edit_language">
					<option value="pali">Pali</option>
					<option value="en">English</option>
					<option value="zh">简体中文</option>
					<option value="tw">正體中文</option>
			</select>
			<span>
				<?php echo $_local->gui->author;?>
				<input type="input" id="id_text_edit_author" onkeydown="match_key(this)" onkeyup="unicode_key(this)"/>
				<?php echo $_local->gui->smart_spell;?>
				<input id="id_text_edit_area_smart_switch" type="checkbox" checked="">
			</span>
		</div>
		<textarea id="id_text_edit_area" rows="10" width="100%" onkeydown="match_key(this)" onkeyup="unicode_key(this)">
		</textarea>

	</div>
	
	<!--  Tool bar on right side -->
	<div id="right_tool_bar" >
	
		<div id="right_tool_bar_inner">
<?php
#头部右侧插件
foreach($plugin_list as $info){
	if($info->attach=="top_right" && $info->enable=="true"){
		echo "<div id=\"{$info->id}\" >";
		require "plugin/{$info->dir}/gui.html";
		echo "</div>";
			
	}
}
?>			

			<!--  三藏购物车 -->
			<div id="pc_res_loader">
				<div id="pc_res_load_button">
					<button  id="id_open_editor_load_stream"  onclick="pc_loadStream(0)">
						<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_cloud_download"></use></svg>
					</button>
					<button  id="id_cancel_stream" onclick="pc_cancelStream()"><?php //echo $_local->gui->cancel;?>
						<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_note_add"></use></svg>
					</button>
					<button  id="pc_empty_download_list" onclick="pc_empty_download_list()"><?php //echo $module_gui_str['editor']['1045'];?>
						<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_delete"></use></svg>
					</button>
					<button onclick="get_pc_res_download_list_from_cookie()"><?php //echo $_local->gui->refresh;?>
						<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_autorenew"></use></svg>
					</button>
				</div>
				
				<div id="pc_res_list_div">
				</div>

				<div id="id_book_res_load_progress"></div>
				<canvas id="book_res_load_progress_canvas" width="300" height="30"></canvas>
			</div>
		
		</div>
	</div>
	<!--  Tool bar on right side end -->
	
	<div class="pop_win_full"><div id="pop_win_inner"></div></div>
	
	<!--  Term pop window-->
	<div class="pop_win_full" id="term_win">
		<div class="pop_win_inner">
			<div class="win_title">
				<div>
					<button onclick="win_close('term_win')">
						<svg class="button_icon" style="fill: var(--btn-hover-bg-color);">
							<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_clear">
							</use>
						</svg>
					</button>
				</div>
				<div>
					<span>
						<?php echo $_local->gui->relational_map; ?>
					</span>
				</div>
				<div>
					<button onclick="term_save()">
					<svg class="button_icon" style="fill: var(--btn-hover-bg-color);">
							<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_save">
							</use>
						</svg>
					</button>
				</div>
			</div>
			<div class="win_body" id="term_body_parent">
				<div class="win_body_inner" id="term_body">
				</div>
			</div>
		</div>
	</div>

	<!-- 逐词解析下拉菜单-->
	<div class="display_off">
		<div id="word_mean" style="max-width:22em;"></div>
		<div id="word_parts" style="max-width:22em;"></div>
		<div id="word_partmean" style="max-width:22em;"></div>
		<div id="word_gramma" style="max-width:22em;"></div>
	</div>

	<!-- 逐词解析 词头工具栏-->
	<div id="word_tool_bar_div">
		<div id="word_tool_bar" class="word_head_bar"style="font-size: 70%">
			<button  onclick="rela_link_click()">
				<?php echo $_local->gui->link; ?>
			</button>
			<button  onclick="rela_link_click(false)">
				<?php echo $_local->gui->cancel; ?>
			</button>
		</div>
	</div>
	
	<div id="end_of_page" class="borderT textS textAc">
	<?php echo $_local->gui->page_end; ?><br>
	——wikipāḷi <?php echo $_local->gui->studio; ?>——
	</div>
	
	<style>
	#word_note_pop{
	border-radius: 6px;
    width: 95%;
	max-height: calc(30vh);
    position: fixed;
    bottom: 10px;
    left: 3em;
	background-color: var(--drop-bg-color);
	overflow-y: auto;
	display:none;

}


	</style>
	<script>
	function close_word_note_pop(){
		$("#word_note_pop").hide("500");
	}
	</script>
	<div id="word_note_pop">
		<div id="word_note_pop_title" style="background-color: var(--border-shadow);">
			<span onclick="close_word_note_pop()" style="display: flex;">
				<svg style="fill: var(--main-color);margin: 5px;height: 15px;width: 15px;"  t="1597905254064" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2185" ><path d="M573.207 527.834l404.184-404.22a52.811 52.811 0 0 0 0-74.401l-2.217-2.217a52.836 52.836 0 0 0-74.413 0l-404.209 404.73-404.22-404.9a52.823 52.823 0 0 0-74.401 0l-2.217 2.217a51.915 51.915 0 0 0 0 74.414l404.22 404.377-404.22 404.22a52.823 52.823 0 0 0 0 74.402l2.217 2.217a52.811 52.811 0 0 0 74.401 0l404.22-404.22 404.22 404.22a52.823 52.823 0 0 0 74.414 0l2.217-2.217a52.811 52.811 0 0 0 0-74.401z m0 0" p-id="2186"></path>
				</svg>
			</span>
		</div>
		<div id="word_note_pop_content">
		</div>
	</div>
</div>
	
	
<script type="text/javascript"> 
//侦测页面滚动	


var scrollEventLock=false;
var suttaDom = document.getElementById('sutta_text');
 window.addEventListener('scroll',winScroll);
 function winScroll(e){ 
	 if(scrollEventLock){
		console.log("scroll Event Lock");
		return;
	 }
	 if(getElementHeight(suttaDom)<getWindowHeight()){
		return;
	 }
	 var top = getElementViewTop(suttaDom);
	 //console.log(Date() +"-"+top);
	 //top < 0 ? fixedDom.classList.add("fixed") : fixedDom.classList.remove("fixed");
	 if(top>-500){
		scrollEventLock=true;
		prev_page();
		scrollEventLock=false;
		//console.log("goto prev page");
	 }
	 if(getElementBottomOutsideHeight(suttaDom)<1500){
		scrollEventLock=true;
		next_page();
		scrollEventLock=false;
		//console.log("goto next page");
	 }
	//debugOutput( document.body.scrollTop);
	//debugOutput("top:"+top+"; outside:"+getElementBottomOutsideHeight(suttaDom));
	//debugOutput("scrollHeight="+suttaDom.scrollHeight+ ";  clientHeight="+suttaDom.clientHeight +";offsetHeight="+suttaDom.offsetHeight);
 }
 
 function getElementViewTop(element){
 　　　　var actualTop = element.offsetTop;
	var elementScrollTop=GetPageScroll().y;//document.body.scrollTop;
 　　　　return actualTop-elementScrollTop;
 　　} 
function getElementBottomOutsideHeight(element){
	var winHeight=getWindowHeight();//document.body.clientHeight;
	var elementHeight=getElementHeight(element);//suttaDom.scrollHeight;
	var elementTop=getElementViewTop(element);
	return(elementHeight+elementTop-winHeight);
}

function getElementHeight(element){
	var scrW, scrH; 
	if(element.innerHeight && element.scrollMaxY) 
	{	// Mozilla	
		scrW = element.innerWidth + element.scrollMaxX;	
		scrH = element.innerHeight + element.scrollMaxY; 
	} 
	else if(element.scrollHeight > element.offsetHeight)
	{	// all but IE Mac	
		scrW = element.scrollWidth;	
		scrH = element.scrollHeight; 
	} else if(element) 
	{ // IE Mac	
		scrW = element.offsetWidth;	
		scrH = element.offsetHeight;
	} 
	return(scrH);
}
function getWindowHeight(){
	var winW, winH; 
	if(window.innerHeight) 
	{ // all except IE	
		winW = window.innerWidth; 
		winH = window.innerHeight; 
	} else if (document.documentElement && document.documentElement.clientHeight)
	{	// IE 6 Strict Mode	
		winW = document.documentElement.clientWidth;	 
		winH = document.documentElement.clientHeight; 
	} else if (document.body) { // other	
		winW = document.body.clientWidth;	
		winH = document.body.clientHeight; 
	}  
	return(winH);
}
//滚动条位置
function GetPageScroll() 
{ 
	var pos=new Object();
	var x, y; 
	if(window.pageYOffset) 
	{	// all except IE	
		y = window.pageYOffset;	
		x = window.pageXOffset; 
	} else if(document.documentElement && document.documentElement.scrollTop) 
	{	// IE 6 Strict	
		y = document.documentElement.scrollTop;	
		x = document.documentElement.scrollLeft; 
	} else if(document.body) {	// all other IE	
		y = document.body.scrollTop;	
		x = document.body.scrollLeft;   
	} 
	pos.x=x;
	pos.y=y;
	return(pos);
}


var Dragging=function(validateHandler){
 //参数为验证点击区域是否为可移动区域，如果是返回欲移动元素，负责返回null 
var draggingObj=null;
//dragging Dialog 
var diffX=0;
var diffY=0;
function mouseHandler(e){
	 switch(e.type){
	 case 'mousedown':
		draggingObj=validateHandler(e);
		//验证是否为可点击移动区域 
		if(draggingObj!=null){
			diffX=e.clientX-draggingObj.offsetLeft;
			diffY=e.clientY-draggingObj.offsetTop;
		 }
	 break;
	 case 'mousemove':
		 if(draggingObj){
			 draggingObj.style.left=(e.clientX-diffX)+'px';
			 draggingObj.style.top=(e.clientY-diffY)+'px';
		 }
		 break;
	 case 'mouseup': 
	 draggingObj =null;
		 diffX=0;
		 diffY=0;
		 break;
	 }
}
;
 return {
	 enable:function(){
		 document.addEventListener('mousedown',mouseHandler);
		 document.addEventListener('mousemove',mouseHandler);
		 document.addEventListener('mouseup',mouseHandler);
		}
	, disable:function(){
		 document.removeEventListener('mousedown',mouseHandler);
		 document.removeEventListener('mousemove',mouseHandler);
		 document.removeEventListener('mouseup',mouseHandler);
		}
	 }
 }
 
 function getDraggingDialog(e){
try{
	var target=e.target;
	if(target && target.className){
		while(target ){
			if(target.className){
				if(target.className.indexOf('dialog-title')==-1){
					target=target.offsetParent;
				}
				else{
					break;
				}
			}
			else{
				target=target.offsetParent;
			}
		}
		
		if(target!=null){
			return target.offsetParent;
		}
		else{
			return null;
		}
	}
}
catch(e){

}
 
}
// Dragging(getDraggingDialog).enable();

</script> 

	<script language="javascript" src="module/editor/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_toc/module_function.js"></script>
	<script language="javascript" src="module/editor_toc/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_bookmark/module_function.js"></script>
	<script language="javascript" src="module/editor_bookmark/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_layout/module_function.js"></script>
	<script language="javascript" src="module/editor_layout/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_project/module_function.js"></script>
	<script language="javascript" src="module/editor_project/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_dictionary/module_function.js"></script>
	<script language="javascript" src="module/editor_dictionary/language/<?php echo $currLanguage; ?>.js"></script>
	<script language="javascript" src="module/editor_plugin/module_function.js"></script>
	<script language="javascript" src="module/editor_plugin/language/<?php echo $currLanguage; ?>.js"></script>
	<script>
		guide_init();
	</script>
</body>

</html>

