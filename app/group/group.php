<?php
require_once '../studio/index_head.php';
require_once '../public/_pdo.php';
?>

<body class="indexbody" >
<script >
	var gCurrPage="group_index";
</script>

<style>
	#group_index {
		background-color: var(--btn-border-color);

	}
	#group_index:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>
		<!-- tool bar begin-->
		<?php
require_once '../studio/index_tool_bar.php';
?>
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo ($currLanguage); ?>";
		</script>
	<div class="index_inner">

		<div class="fun_block">
			<div id="userfilelist">
			<?php
if (isset($_GET["list"])) {
    $list = $_GET["list"];
} else {
    $list = "group";
}

PDO_Connect("" . _FILE_DB_GROUP_);
switch ($list) {
    case "group":
        echo "<div class='group_path'>Group</div>";
        $query = "select * from \"group_member\" where user_id='{$UID}' ";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        $sGroupId = "('";
        if ($iFetch > 0) {
            foreach ($Fetch as $group_id) {
                $sGroupId .= "{$group_id["group_id"]}','";
            }
            $sGroupId = substr($sGroupId, 0, -2);
            $sGroupId .= ")";
            $query = "select * from \"group_info\" where id in {$sGroupId} ";
            $Fetch = PDO_FetchAll($query);
            foreach ($Fetch as $group) {
                echo "<div><a href=\"group.php?list=project&group={$group["id"]}\">{$group["name"]}</a>";
                echo "<a href=\"group.php?list=group_info&group={$group["id"]}\"> [详情]</a></div>";
            }
        }
        break;
    case "group_info":
        echo "<div><a href=\"group.php?list=group\">返回Back</a></div>";
        if (isset($_GET["group"])) {
            $group = $_GET["group"];
        } else {
            $group = "0";
        }
        $query = "select * from \"group_info\" where id = '{$group}' ";
        $Fetch = PDO_FetchAll($query);
        $group_name = $Fetch[0]["name"];
        if (count($Fetch) > 0) {

            echo "<H2>{$group_name}</H2>";
            echo "<table>";
            echo "<tr><td>建立Create Time</td><td>{$Fetch[0]["create_time"]}</td></tr>";
            echo "<tr><td>文件Files</td><td>{$Fetch[0]["file_number"]}</td></tr>";
            echo "<tr><td>成员Member</td><td>{$Fetch[0]["member_number"]}</td></tr>";
            $query = "select user_id from \"group_member\" where group_id = '{$group}' ";
            $Fetch = PDO_FetchAll($query);
            $sUserId = "('";
            foreach ($Fetch as $user) {
                $sUserId .= "{$user["user_id"]}','";
            }
            $sUserId = substr($sUserId, 0, -2);
            $sUserId .= ")";

            $query = "select nickname from \"user\" where id in {$sUserId} ";

            $userlist = PDO_FetchAll($query);

            echo "<tr><td></td><td>";
            foreach ($userlist as $user) {
                echo "{$user["nickname"]}<br>";
            }
            echo "</td></tr>";
            echo "</table>";
        }

        break;
    case "project";

        if (isset($_GET["group"])) {
            $group = $_GET["group"];
        } else {
            $group = "0";
        }
        $query = "select group_name from \"group_member\" where group_id = \"{$group}\" AND user_id=\"{$UID}\"";
        $group_name = PDO_FetchOne($query);
        if ($group_name == "") {
            $query = "select name from \"group_info\" where group_id = \"{$group}\" ";
            $group_name = PDO_FetchOne($query);
        }

        echo "<div class='group_path'>";
        echo "<a href='group.php?list=group'>群组Group</a> >> {$group_name}";
        echo "</div>";
        $query = "select file_id from \"group_file_power\" where group_id = \"{$group}\" AND user_id='{$UID}' group by file_id";
        $Fetch = PDO_FetchAll($query);
        $sFileId = "('";
        foreach ($Fetch as $file) {
            $sFileId .= "{$file["file_id"]}','";
        }
        $sFileId = substr($sFileId, 0, -2);
        $sFileId .= ")";
        $query = "select project , count(*) as co from \"group_file\" where id in {$sFileId} group by project";
        $Fetch = PDO_FetchAll($query);
        echo "<table>";
        echo "<tr><td>Project</td><td>File Number</td></tr>";
        foreach ($Fetch as $project) {
            echo "<tr class='group_file_list'>";
            echo "<td ><a href=\"group.php?list=file&group={$group}&project={$project["project"]}\">{$project["project"]}</a></td>";
            echo "<td>{$project["co"]}</td> ";
            echo "</tr>";
        }
        echo "</table>";
        break;
    case "file":

        if (isset($_GET["group"])) {
            $group = $_GET["group"];
        } else {
            $group = "0";
        }
        if (isset($_GET["project"])) {
            $project = $_GET["project"];
        } else {
            $project = "0";
        }
        $query = "select group_name from \"group_member\" where group_id = \"{$group}\" AND user_id=\"{$UID}\"";
        $group_name = PDO_FetchOne($query);
        if ($group_name == "") {
            $query = "select name from \"group_info\" where group_id = \"{$group}\" ";
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
								<?php echo $_local->gui->all; //全部 ?>
							</option>
							<option value="share" >
								<?php echo $_local->gui->shared; //已共享 ?>
							</option>
							<option value="recycle" >
								<?php echo $_local->gui->recycle_bin; //回收站 ?>
							</option>
						</select>
					</div>
					<div><?php echo $_local->gui->order_by; //排序方式 ?>
						<select id="id_index_orderby"  onchange="showUserFilaList()">
							<option value="accese_time" ><?php echo $_local->gui->accessed; //訪問 ?></option>
							<option value="modify_time" ><?php echo $_local->gui->modified; //修改 ?></option>
							<option value="create_time" ><?php echo $_local->gui->created; //創建 ?></option>
							<option value="title" ><?php echo $_local->gui->title; //標題 ?></option>
						</select>
						<select id="id_index_order"  onchange="showUserFilaList()">
							<option value="DESC" ><?php echo $_local->gui->desc; //降序 ?></option>
							<option value="ASC" ><?php echo $_local->gui->asc; //升序 ?></option>
						</select>
						<button id="file_select" onclick="mydoc_file_select(true)">
							选择
						</button>
					</div>
				</div>
				<div>
				<input id="keyword" type="input"  placeholder=<?php echo $_local->gui->title . $_local->gui->search; ?>  onkeyup="file_search_keyup()"/>
				</div>

			</div>

			<div id="file_tools" style="display:none;">
				<div  style="display:flex;justify-content: space-between;">
					<div>
						<span id="button_group_nomal" >
						<button onclick="file_del()"><?php echo $_local->gui->delete; //刪除 ?></button>
						<button onclick="file_share(true)"><?php echo $_local->gui->share; //共享 ?></button>
						<button onclick="file_share(false)"><?php echo $_local->gui->undo_shared; //取消共享 ?></button>
						</span>
						<span id="button_group_recycle" style="dispaly:none">
						<button onclick="file_remove()" style="background-color:red;"><?php echo $_local->gui->completely_delete; //彻底删除 ?></button>
						<button onclick="file_remove_all()"><?php echo $_local->gui->empty_the_recycle_bin; //清空回收站 ?></button>
						</span>
					</div>
					<div>
						<button onclick="mydoc_file_select(false)"><?php echo $_local->gui->cancel; //取消 ?></button>
					</div>
				</div>
			</div>

				<?php
$query = "select * from \"group_process\" where group_id = \"{$group}\" ";
        $stage = PDO_FetchAll($query);
        $aStage = array();
        echo "<button>全部All</button>";
        foreach ($stage as $one) {
            $aStage[$one["stage"]] = $one["name"];
            echo "<button>{$one["name"]}</button>";
        }

        $query = "select file_id from \"group_file_power\" where group_id = \"{$group}\" AND user_id='{$UID}' group by file_id";
        $Fetch = PDO_FetchAll($query);
        $sFileId = "('";
        foreach ($Fetch as $file) {
            $sFileId .= "{$file["file_id"]}','";
        }
        $sFileId = substr($sFileId, 0, -2);
        $sFileId .= ")";
        $query = "select * from \"group_file\" where id in {$sFileId} AND project = \"{$project}\"";
        $Fetch = PDO_FetchAll($query);
        echo "<table>";
        echo "<tr><td>Title</td><td>File Size</td><td>Date</td><td>Stage</td><td></td></tr>";
        foreach ($Fetch as $file) {
            echo "<tr class='group_file_list'>";
            echo "<td class='group_file_title'><a href=\"group.php?list=stage&file={$file["id"]}\">{$file["file_title"]}</a></td>";
            echo "<td>{$file["file_size"]}</td> ";
            echo "<td>{$file["modify_time"]}</td> ";
            $stage_name = $aStage["{$file["stage"]}"];
            echo "<td><span class='tag'>{$stage_name}</span></td>";
            echo "<td><a href=\"group.php?list=stage&file={$file["id"]}\">详情Details</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        break;
    case "stage":
        if (isset($_GET["file"])) {
            $file_id = $_GET["file"];
        } else {
            $file_id = "0";
        }
        $query = "select * from \"group_file\" where id = {$file_id} ";
        $Fetch = PDO_FetchAll($query);
        $group_id = $Fetch[0]["group_id"];
        $file_title = $Fetch[0]["file_title"];
        $project = $Fetch[0]["project"];
        $curr_stage = $Fetch[0]["stage"];
        $query = "select name from \"group_info\" where id = \"{$group_id}\" ";
        $group_name = PDO_FetchOne($query);
        $query = "select group_name from \"group_member\" where group_id = \"{$group_id}\" AND user_id='{$UID}'  ";
        $my_group_name = PDO_FetchOne($query);
        if (empty($my_group_name)) {
            $my_group_name = $group_name;
        }
        $query = "select * from \"group_process\" where group_id = \"{$group_id}\" ";
        $stage = PDO_FetchAll($query);
        echo "<div class='group_path'>";
        echo "<a href=\"group.php?list=group\">群组Group</a> / ";
        echo "<a href=\"group.php?list=project&group={$group_id}\">{$my_group_name}</a> / ";
        echo "<a href=\"group.php?list=file&group={$group_id}&project={$project}\">{$project}</a>";
        echo "</div>";
        echo "<h2>{$file_title}</h2>";
        foreach ($stage as $one) {
            echo "<button>{$one["stage"]}</button>{$one["name"]}-";
            if ($one["stage"] < $curr_stage) {
                echo "<span style='color:green'>{$_local->gui->already_over}</span>";
            } else if ($one["stage"] == $curr_stage) {
                echo "{$_local->gui->in_progress}<button>完成Done</button>";
            } else {
                echo "尚未开始 Not Ready";
            }
            echo "<br>";
        }
        break;
}
?>
			</div>

		</div>

	</div>
<div class="foot_div">
<?php echo $_local->gui->poweredby; ?>
</div>
</body>
</html>

