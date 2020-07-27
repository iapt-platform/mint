<?php
require_once 'checklogin.inc';
require_once '../public/config.php';
require_once '../public/load_lang.php';
require_once "../public/_pdo.php";

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
	<script language="javascript" src="js/index_mydoc.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="../public/js/jquery.js"></script>
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
			window.location.assign("index.php?language="+g_langrage);
		}

	</script>

</head>
<body class="indexbody" >

		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
				<button><?php echo $_local->gui->recent_scan;?></button>
				<button><a href="index_pc.php?language=<?php echo $currLanguage; ?>"><?php echo $_local->gui->pali_canon;?></a></button>
				<button class="selected" ><a href="group.php?language=<?php echo $currLanguage; ?>">Group</a></button>
				<button><a href="filenew.php?language=<?php echo $currLanguage; ?>"><?php echo $_local->gui->new_project;?></a></button>
				<button><a href="index_tools.php?language=<?php echo $currLanguage; ?>"><?php echo $_local->gui->tools;?></a></button>
			</div>
			<div>
			
			</div>
			<div class="toolgroup1">
				<span><?php echo $_local->gui->language;?></span>
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
				echo "</a>";;
			?>
				<button class="icon_btn" id="file_select" >
					<a href="setting.php" target='_blank'>
					<svg class="icon">
						<use xlink:href="svg/icon.svg#ic_settings"></use>
					</svg>
					</a>
				</button>			
			</div>
		</div>	
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
		</script>
	<div class="index_inner">
		<div id="id_app_name"><?php echo $module_gui_str['editor']['1051'];?>
			<span style="font-size: 70%;">1.6</span><br />
			<?php if($currLanguage=="en"){ ?>
				<span style="font-size: 70%;">Pali Cannon Database Studio</span>
			<?php 
			}
			else{
			?>
				<span style="font-size: 70%;">PCD Studio</span>
			<?php
			}
			?>
		</div>
				
		<div class="fun_block">
			<div id="userfilelist">
			<?php
			if(isset($_GET["list"])){
				$list=$_GET["list"];
			}
			else{
				$list="group";
			}
			$db_file = '../user/fileindex.db';
			PDO_Connect("sqlite:{$_file_db_group}");
			switch($list){
				case "group":
					echo "<div class='group_path'>Group</div>";
					$query="select * from \"group_member\" where user_id='{$UID}' ";
					$Fetch = PDO_FetchAll($query);
					$iFetch=count($Fetch);
					$sGroupId="('";
					if($iFetch>0){
						foreach($Fetch as $group_id){
							$sGroupId .= "{$group_id["group_id"]}','";
						}
						$sGroupId = substr($sGroupId,0,-2);
						$sGroupId .= ")";
						$query="select * from \"group_info\" where id in {$sGroupId} ";
						$Fetch = PDO_FetchAll($query);
						foreach($Fetch as $group){
							echo "<div><a href=\"group.php?list=project&group={$group["id"]}\">{$group["name"]}</a>";
							echo "<a href=\"group.php?list=group_info&group={$group["id"]}\"> [详情]</a></div>";
						}
					}
					break;
				case "group_info":
					echo "<div><a href=\"group.php?list=group\">返回Back</a></div>";
					if(isset($_GET["group"])){
						$group=$_GET["group"];
					}
					else{
						$group="0";
					}
					$query="select * from \"group_info\" where id = '{$group}' ";
					$Fetch = PDO_FetchAll($query);
					$group_name=$Fetch[0]["name"];
					if(count($Fetch)>0){
						
						
						echo "<H2>{$group_name}</H2>";
						echo "<table>";
						echo "<tr><td>建立Create Time</td><td>{$Fetch[0]["create_time"]}</td></tr>";
						echo "<tr><td>文件Files</td><td>{$Fetch[0]["file_number"]}</td></tr>";
						echo "<tr><td>成员Member</td><td>{$Fetch[0]["member_number"]}</td></tr>";
						$query="select user_id from \"group_member\" where group_id = '{$group}' ";
						$Fetch = PDO_FetchAll($query);
						$sUserId="('";
						foreach($Fetch as $user){
							$sUserId .= "{$user["user_id"]}','";
						}
						$sUserId = substr($sUserId,0,-2);
						$sUserId .= ")";
						
						$query="select nickname from \"user\" where id in {$sUserId} ";

						$userlist = PDO_FetchAll($query);	
						
						echo "<tr><td></td><td>";
						foreach($userlist as $user){
							echo "{$user["nickname"]}<br>";
						}
						echo "</td></tr>";
						echo "</table>";
					}

					break;
				case "project";

					if(isset($_GET["group"])){
						$group=$_GET["group"];
					}
					else{
						$group="0";
					}
					$query="select group_name from \"group_member\" where group_id = \"{$group}\" AND user_id=\"{$UID}\"";
					$group_name = PDO_FetchOne($query);
					if($group_name == ""){
						$query="select name from \"group_info\" where group_id = \"{$group}\" ";
						$group_name = PDO_FetchOne($query);
					}
					
					echo "<div class='group_path'>";
					echo "<a href='group.php?list=group'>群组Group</a> >> {$group_name}";
					echo "</div>";				
					$query="select file_id from \"group_file_power\" where group_id = \"{$group}\" AND user_id='{$UID}' group by file_id";
					$Fetch = PDO_FetchAll($query);
					$sFileId="('";
					foreach($Fetch as $file){
						$sFileId .= "{$file["file_id"]}','";
					}
					$sFileId = substr($sFileId,0,-2);
					$sFileId .= ")";
					$query="select project , count(*) as co from \"group_file\" where id in {$sFileId} group by project";
					$Fetch = PDO_FetchAll($query);
					echo "<table>";
					echo "<tr><td>Project</td><td>File Number</td></tr>";
					foreach($Fetch as $project){
						echo "<tr class='group_file_list'>";
						echo "<td ><a href=\"group.php?list=file&group={$group}&project={$project["project"]}\">{$project["project"]}</a></td>";
						echo "<td>{$project["co"]}</td> ";
						echo "</tr>";
					}	
					echo "</table>";
				break;
				case "file":

					if(isset($_GET["group"])){
						$group=$_GET["group"];
					}
					else{
						$group="0";
					}
					if(isset($_GET["project"])){
						$project=$_GET["project"];
					}
					else{
						$project="0";
					}
					$query="select group_name from \"group_member\" where group_id = \"{$group}\" AND user_id=\"{$UID}\"";
					$group_name = PDO_FetchOne($query);
					if($group_name == ""){
						$query="select name from \"group_info\" where group_id = \"{$group}\" ";
						$group_name = PDO_FetchOne($query);
					}
					
					echo "<div class='group_path'>";
					echo "<a href='group.php?list=group'>群组Group</a> >> ";
					echo "<a href=\"group.php?list=project&group={$group}\">{$group_name}</a> >> ";
					echo "{$project}";
					echo "</div>";
					
				?>
							<div  id="file_filter">
				<div style="display:flex;justify-content: space-between;">
					<div>
						<select id="id_index_status"  onchange="showUserFilaList()">
							<option value="all" >
								<?php echo $module_gui_str['editor_dictionary']['1018'];//全部?>
							</option>
							<option value="share" >
								<?php echo $module_gui_str['tools']['1017'];//已共享?>
							</option>
							<option value="recycle" >
								<?php echo $module_gui_str['tools']['1007'];//回收站?>
							</option>
						</select>					
					</div>
					<div><?php echo $module_gui_str['editor']['1059'];//排序方式?>
						<select id="id_index_orderby"  onchange="showUserFilaList()">
							<option value="accese_time" ><?php echo $module_gui_str['editor']['1060'];//訪問?></option>
							<option value="modify_time" ><?php echo $module_gui_str['editor']['1061'];//修改?></option>
							<option value="create_time" ><?php echo $module_gui_str['editor']['1062'];//創建?></option>
							<option value="title" ><?php echo $module_gui_str['editor']['1063'];//標題?></option>
						</select>					
						<select id="id_index_order"  onchange="showUserFilaList()">
							<option value="DESC" ><?php echo $module_gui_str['editor']['1111'];//降序?></option>
							<option value="ASC" ><?php echo $module_gui_str['editor']['1110'];//升序?></option>
						</select>	
						<button id="file_select" onclick="mydoc_file_select(true)">
							选择
						</button>
					</div>
				</div>
				<div>
				<input id="keyword" type="input"  placeholder=<?php echo $module_gui_str['editor']['1114'].$module_gui_str['editor']['1115'];?>  onkeyup="file_search_keyup()"/>
				</div>

			</div>
			
			<div id="file_tools" style="display:none;">
				<div  style="display:flex;justify-content: space-between;">
					<div>
						<span id="button_group_nomal" >
						<button onclick="file_del()"><?php echo $module_gui_str['tools']['1009'];//刪除?></button>
						<button onclick="file_share(true)"><?php echo $module_gui_str['tools']['1008'];//共享?></button>
						<button onclick="file_share(false)"><?php echo $module_gui_str['tools']['1010'];//取消共享?></button>
						</span>
						<span id="button_group_recycle" style="dispaly:none">
						<button onclick="file_remove()" style="background-color:red;"><?php echo $module_gui_str['tools']['1016'];//彻底删除?></button>
						<button onclick="file_remove_all()"><?php echo $module_gui_str['tools']['1015'];//清空回收站?></button>
						</span>
					</div>
					<div>
						<button onclick="mydoc_file_select(false)"><?php echo $module_gui_str['editor']['1028'];//取消?></button>
					</div>
				</div>
			</div>
			
				<?php
					$query="select * from \"group_process\" where group_id = \"{$group}\" ";
					$stage = PDO_FetchAll($query);
					$aStage = array();
					echo "<button>全部All</button>";
					foreach($stage as $one){
						$aStage[$one["stage"]]=$one["name"];
						echo "<button>{$one["name"]}</button>";
					}
					
					$query="select file_id from \"group_file_power\" where group_id = \"{$group}\" AND user_id='{$UID}' group by file_id";
					$Fetch = PDO_FetchAll($query);
					$sFileId="('";
					foreach($Fetch as $file){
						$sFileId .= "{$file["file_id"]}','";
					}
					$sFileId = substr($sFileId,0,-2);
					$sFileId .= ")";
					$query="select * from \"group_file\" where id in {$sFileId} AND project = \"{$project}\"";
					$Fetch = PDO_FetchAll($query);
					echo "<table>";
					echo "<tr><td>Title</td><td>File Size</td><td>Date</td><td>Stage</td><td></td></tr>";
					foreach($Fetch as $file){
						echo "<tr class='group_file_list'>";
						echo "<td class='group_file_title'><a href=\"group.php?list=stage&file={$file["id"]}\">{$file["file_title"]}</a></td>";
						echo "<td>{$file["file_size"]}</td> ";
						echo "<td>{$file["modify_time"]}</td> ";
						$stage_name=$aStage["{$file["stage"]}"];
						echo "<td><span class='tag'>{$stage_name}</span></td>";
						echo "<td><a href=\"group.php?list=stage&file={$file["id"]}\">详情Details</a></td>";
						echo "</tr>";
					}	
					echo "</table>";
				break;
				case "stage":
					if(isset($_GET["file"])){
						$file_id=$_GET["file"];
					}
					else{
						$file_id="0";
					}
					$query="select * from \"group_file\" where id = {$file_id} ";
					$Fetch = PDO_FetchAll($query);
					$group_id=$Fetch[0]["group_id"];
					$file_title=$Fetch[0]["file_title"];
					$project = $Fetch[0]["project"];
					$curr_stage=$Fetch[0]["stage"];
					$query="select name from \"group_info\" where id = \"{$group_id}\" ";
					$group_name = PDO_FetchOne($query);
					$query="select group_name from \"group_member\" where group_id = \"{$group_id}\" AND user_id='{$UID}'  ";
					$my_group_name = PDO_FetchOne($query);
					if(empty($my_group_name)){
						$my_group_name = $group_name;
					}
					$query="select * from \"group_process\" where group_id = \"{$group_id}\" ";
					$stage = PDO_FetchAll($query);
					echo "<div class='group_path'>";
					echo "<a href=\"group.php?list=group\">群组Group</a> / ";
					echo "<a href=\"group.php?list=project&group={$group_id}\">{$my_group_name}</a> / ";
					echo "<a href=\"group.php?list=file&group={$group_id}&project={$project}\">{$project}</a>";
					echo "</div>";
					echo "<h2>{$file_title}</h2>";
					foreach($stage as $one){
						echo "<button>{$one["stage"]}</button>{$one["name"]}-";
						if($one["stage"]<$curr_stage){
							echo "<span style='color:green'>已经完成Done</span>";
						}
						else if($one["stage"]==$curr_stage){
							echo "正在进行Runing<button>完成Done</button>";
						}
						else{
							echo "尚未开始 Not Ready";
						}
						echo"<br>";
					}	
				break;
			}
			?>
			</div>
			
		</div>
		
	</div>
<div class="foot_div">
<?php echo $module_gui_str['editor']['1066'];?>
</div>
</body>
</html>

