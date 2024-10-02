<?php
require 'checklogin.inc';
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
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_user_base.$userid.$dir_myApp; ?>/style.css"/>
	<title>PCD Studio</title>
	<script language="javascript" src="config.js"></script>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/xml.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="js/dict.js"></script>
	<script language="javascript" src="js/wizard.js"></script>
	<script language="javascript" src="js/search.js"></script>
	<script language="javascript" src="term_sys_list.js"></script>
	<script language="javascript" src="charcode/sinhala.js"></script>
	<script language="javascript" src="charcode/unicode.js"></script>
	<link type="text/css" rel="stylesheet" href="css/style.css"/>

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
				window.location.assign("search.php?language="+g_language);
			}
var g_selected_array=new Array();
var g_unselected_array=new Array();

var g_add_drop_str=new Array();
var g_replace_list=new Array();
var g_protector_list=new Array();
var g_protector_list2=new Array();


function item_select(obj){
	switch(obj.checked){
		case true:
			var id_array=obj.id.split("_");
			id_array.pop();
			g_selected_array.push(id_array.join("_"))
			var i_selected=0;
			for(i_selected in g_unselected_array){
				if(g_unselected_array[i_selected]==id_array.join("_")){
					g_unselected_array.splice(i_selected,1)
				}
			}
			break;
		case false:
			var id_array=obj.id.split("_");
			id_array.pop();
			g_unselected_array.push(id_array.join("_"))
			var i_selected=0;
			for(i_selected in g_selected_array){
				if(g_selected_array[i_selected]==id_array.join("_")){
					g_selected_array.splice(i_selected,1)
				}
			}
			break;
		}
	refresh_replace_list();
}

function add_button(dropdown_id){
	var add_drop_str="";
	var i_add=Number(dropdown_id.split("_")[2])+1;
	add_drop_str+="<select id='output_str_"+i_add+"' class='term_sys_dropdown'; onchange=\"item_change(this,"+i_add+")\";>";
	add_drop_str+="<option value='0'>None</option>"
	for(str_add in g_unselected_array){
		add_drop_str+="<option value='"+g_unselected_array[str_add]+"'>"+g_unselected_array[str_add]+"</option>";
	}
	add_drop_str+="</select>";
	g_add_drop_str.push(add_drop_str);
	add_drop_str="";
	for(i_output in g_add_drop_str){
		add_drop_str+=g_add_drop_str[i_output];
	}
	$("#output_div").html(add_drop_str);

}
function load_protector_list(){
	g_protector_list=term_list_str_new
	for(var i_protect=0;i_protect<g_protector_list.length;){
		if(g_protector_list[i_protect].type=="a_protect"){
			if(g_protector_list[i_protect].id.lastIndexOf(" ")==-1){
				g_protector_list[i_protect].text="【"+g_protector_list[i_protect].text+"】";
				i_protect++;
			}
			else{
				var word_obj_list2=new Object;
				word_obj_list2.id=g_protector_list[i_protect].id
				word_obj_list2.text="【"+g_protector_list[i_protect].text+"】"
				g_protector_list2.push(word_obj_list2);
				g_protector_list.splice(i_protect,1);
			}
		}
		else{
			g_protector_list.splice(i_protect,1);

		}
	}
}
function protector_replace(){
	var output=$("#txtInput").val();
	var enter_RE=new RegExp("\\n")
	var en_par_array=output.split(enter_RE);
	for(i_par_en in en_par_array){
		var en_word_array=en_par_array[i_par_en].split(" ");
		for(i_word_en in en_word_array){
			var i_protect=0;
			for(i_protect in g_protector_list){
				if(en_word_array[i_word_en].toLowerCase()==g_protector_list[i_protect].id.toLowerCase()){
					en_word_array[i_word_en]=g_protector_list[i_protect].text;
				}
			}
		}
		en_par_array[i_par_en]=en_word_array.join(" ");
	}
	output=en_par_array.join("\n");
	//替換帶空格的
	for(i_space in g_protector_list2){
		eval("output=output.replace(/"+g_protector_list2[i_space].id+"/g,g_protector_list2[i_space].text);");
	}

	$("#txtOutput").html(output);
}
function refresh_replace_list(){
	g_replace_list=new Array();
	for(i_refresh in term_list_str){
		var replace_obj=new Object;
		replace_obj.id="【"+term_list_str[i_refresh].id+"】";
		replace_obj.value=""
		for(j_refresh in g_selected_array){
			var term_str=eval("term_list_str[i_refresh]."+g_selected_array[j_refresh]+"");
			if(term_str!=""){
				replace_obj.value+=term_str
				if(j_refresh<g_selected_array.length-1){
					replace_obj.value+="#"
				}
			}
		}
		replace_obj.value="#"+replace_obj.value+"#";
		replace_obj.value=replace_obj.value.replace(/##/g,"");
		if(replace_obj.value.lastIndexOf("#")==replace_obj.value.length-1 && replace_obj.value.length!=0){
			replace_obj.value=replace_obj.value.slice(0,replace_obj.value.length-1)
		}
		if(replace_obj.value.indexOf("#")==0){
			replace_obj.value=replace_obj.value.slice(1)
		}
		if(replace_obj.value.lastIndexOf("#")!=-1){
			term_str=replace_obj.value.split("#");
			var head=term_str[0]
			term_str.shift();//删除第一个数组
			if(term_str.length>1){
				replace_obj.value=head+"(";
				replace_obj.value+=term_str.join(",");
				replace_obj.value+=")"
				replace_obj.value.replace(/,\)/g,")");
			}
			else if(term_str.length==1 && term_str[0]!=""){
				replace_obj.value=head+"(";
				replace_obj.value+=term_str[0];
				replace_obj.value+=")"
			}

		}
		g_replace_list.push(replace_obj);
		//g_replace_list[i_refresh].id=replace_obj.id.toString();
		//g_replace_list[i_refresh].value=replace_obj.value.toString();
	}
	
	
}
function glossary_replace(){
	var output=$("#txtInput").val();
	

	for(i_term in g_replace_list){
		eval("output=output.replace(/"+g_replace_list[i_term].id+"/g,g_replace_list[i_term].value);");
	}
	$("#txtOutput").html(output);
}
function transfer_glossary(){
	var type=$("#templet_type").val();
	switch(type){
		case "same_language":
			glossary_replace();
		break;
		case "pali_templet":
		break;
		case "protect_templet":
			protector_replace();
		break;
	}
}


	</script>

</head>
<body class="indexbody" onload="">
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
					<option value="sinhala" >සිංහල</option>
					<option value="zh" >简体中文</option>
					<option value="tw" >繁體中文</option>
				</select>
			
			<?php 
				echo $_local->gui->welcome;
				echo "<a href=\"setting.php?item=account\">";
				echo $_COOKIE["nickname"];
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

			<div style="text-align:center; float:left;width:40%;height:45em;">
				My Text Is In
				<textarea id="txtInput" rows="30" cols="" style="float:left; font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', Verdana, sans-serif; font-size:16px; width:100%;height:100%;" ></textarea>
			</div>
			<div style="text-align:center; float:left;width:15%; display: flex; height: 45em;">
				<select id="templet_type" style="margin-top: auto; margin-bottom: auto;">
						<option value="same_language">
							<?php echo $module_gui_str['tools']['1001']; ?>
						</option>
						<option value="pali_templet">
							<?php echo $module_gui_str['tools']['1002']; ?>
						</option>
						<option value="protect_templet">
							<?php echo $module_gui_str['tools']['1003']; ?>
						</option>
				</select>
				<button id="transfer" style="margin-top: auto; margin-bottom: auto;" type="button"  onclick="transfer_glossary()">
					<svg class="button_icon">
						<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_autorenew"></use>
					</svg>
				</button>
			</div>
			<div style="display: flex;flex-direction: column;">
				<span>
				I Need the Text In
				</span>
				<span id="output_span"; style="text-align:center;height:80%;">
					
				</span>
				<div style="height: 30em; border: 0.2em solid silver;display: flex;">
				<textarea id="txtOutput" style="font-family: 'Noto Sans','Noto Sans CJK TC', 'Noto Sans CJK SC', 'Noto Sans TC', 'Noto Sans SC', 'Noto Sans CJK', Verdana, sans-serif; font-size:16px; text-align: left; width:100%;height:100%"></textarea>
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
			<div id="dict_ref_search_input_div">
				<div id="dict_ref_search_input_head">
					<table>
						<tr>
							<td>
								<div class="case_dropdown">
									<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_more"></use></svg>
									<div id="dict_ref_dict_link" class="case_dropdown-content">
										<a onclick="">[dict]</a>
									</div>
								</div>
							</td>
							<td style="width: 95%;">
								<input id="dict_ref_search_input" type="input" onkeyup="dict_input_keyup(event,this)">
							</td>
						</tr>
					</table>
				</div>
				<div><span id="input_parts"><span></div>
			</div>
		</div>
		<div id="right_tool_bar_inner">
		<div id="dict_ref_search">

			<div id="dict_ref_search_result">
			</div>
		</div>
		<div id="pc_res_loader">
			<div id="pc_res_load_button">
				<button  id="id_open_editor_load_stream"  onclick="pc_loadStream(0)"><?php //echo $module_gui_str['editor']['1030'];?>
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
	
<div class="foot_div">

<?php echo $_local->gui->poweredby;?>
</div>
<div class="debugMsg" id="id_debug" style="display: none;"><!--调试信息-->
	<div id="id_debug_output"></div>
</div>

</body>
</html>
<script language="javascript">
	//function make_dropdown_opt(dropdown_id){
var opt_str="";
var iCount_term_select=3
for(i_opt in term_list_str[0]){
	if(iCount_term_select % 3==0){
		opt_str+="<div class='term_select_div'>"
	}
	if(i_opt=="id"){}
	else{
		opt_str+="<span class='term_select_span'>";
		opt_str+="<input id='"+i_opt+"_checkbox' type='checkbox' class='checkbox_1em' onclick='item_select(this)'>";
		opt_str+=i_opt+"</span>";
		g_unselected_array.push(i_opt);
	}
	if(iCount_term_select % 3==2 || iCount_term_select==term_list_str[0].length-1){
		opt_str+="</div>"
	}
	iCount_term_select++
}
document.getElementById("output_span").innerHTML=opt_str
load_protector_list();
//}
</script>
