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
	<script language="javascript" src="js/index_mydoc.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="../public/js/jquery.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script src="../public/js/notify.js"></script>
	<script src="../public/js/comm.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/css/notyfy.css"/>

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
			window.location.assign("index.php?language="+g_language);
		}

	var gCurrPage="recycle_bin";
	</script>
	<style>
	#recycle_bin {
		background-color: var(--btn-border-color);
		
	}
	#recycle_bin:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>
</head>
<body id="file_list_body" onLoad="recycleInit()">

	<?php
	require_once 'index_tool_bar.php';
	?>
	<script>
		document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
	</script>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div class="file_list_block">
			<div class="tool_bar">
				<div>
				<?php echo $_local->gui->recycle_bin;?>
				</div>
				<div>
				
			
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->restore;?></span>
					<button id="edit_title" type="button" class="icon_btn" onclick="file_restore()" >
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_restore_24px"></use>
						</svg>
					</button>
				</span>	
				
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->completely_delete;?></span>
					<button id="to_recycle" type="button" class="icon_btn" onclick="" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_delete"></use>
						</svg>
					</button>
				</span>	
				
				</div>
			</div>
			<div  id="file_filter" style="display:none;">
				<div style="display:flex;justify-content: space-between;">
					<div>
						<select id="id_index_status"  onchange="showUserFilaList()">
							<option value="all" >
								<?php echo $_local->gui->all;//全部?>
							</option>
							<option value="share" >
								<?php echo $_local->gui->shared;//已共享?>
							</option>
							<option value="recycle" selected>
								<?php echo $_local->gui->recycle_bin;//回收站?>
							</option>
						</select>					
					</div>
					<div><?php echo $_local->gui->order_by;//排序方式?>
						<select id="id_index_orderby"  onchange="showUserFilaList()">
							<option value="accese_time" ><?php echo $_local->gui->accessed;//訪問?></option>
							<option value="modify_time" ><?php echo $_local->gui->modified;//修改?></option>
							<option value="create_time" ><?php echo $_local->gui->created;//創建?></option>
							<option value="title" ><?php echo $_local->gui->title;//標題?></option>
						</select>					
						<select id="id_index_order"  onchange="showUserFilaList()">
							<option value="DESC" ><?php echo $_local->gui->desc;//降序?></option>
							<option value="ASC" ><?php echo $_local->gui->asc;;//升序?></option>
						</select>	
						<button id="file_select" onclick="mydoc_file_select(true)">
							选择
						</button>
					</div>
				</div>
				<div>
				<input id="keyword" type="input"  placeholder='<?php echo $_local->gui->title.$_local->gui->search;?>' onkeyup="file_search_keyup()"/>
				</div>

				<div>
				<?php echo $_local->gui->tag;?>：<span id="tag_list"><span class="tag"><?php echo $_local->gui->lesson?><a>×</a></span></span><input type="input" style="width:10em;">
				</div>
			</div>
			<div id="file_tools" style="display:none;">
				<div  style="display:flex;justify-content: space-between;">
					<div>
						<span id="button_group_nomal" >
						<button onclick="file_del()"><?php echo $_local->gui->delete;//刪除?></button>
						<button onclick="file_share(true)"><?php echo $_local->gui->share;//共享?></button>
						<button onclick="file_share(false)"><?php echo $_local->gui->undo_shared;//取消共享?></button>
						</span>
						<span id="button_group_recycle" style="dispaly:none">
						<button onclick="file_remove()" style="background-color:red;"><?php echo $_local->gui->completely_delete;//彻底删除?></button>
						<button onclick="file_remove_all()"><?php echo $_local->gui->empty_the_recycle_bin;//清空回收站?></button>
						</span>
					</div>
					<div>
						<button onclick="mydoc_file_select(false)"><?php echo $_local->gui->cancel;//取消?></button>
					</div>
				</div>
			</div>
			<div>
				<div class="file_list_row" style="border-top: none;">
					<div class="file_list_col_1"><input type="checkbox" checked /></div>
					<div class="file_list_col_2"><?php echo $_local->gui->title;?></div>
					<div class="file_list_col_3"><?php echo "删除时间";?></div>
					<div class="file_list_col_4"><?php echo $_local->gui->time;?></div>
					<div class="file_list_col_5"><?php echo $_local->gui->size;?></div>
				</div>
			</div>
			<div id="userfilelist">
			<?php echo $_local->gui->loading;?>
			</div>
			
		</div>
		
	</div>
	
	<div class="foot_div">
	<?php echo $_local->gui->poweredby;?>
	</div>

</body>
</html>

