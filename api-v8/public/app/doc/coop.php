	<style>
	#coop_selector_title{
		margin-top: 15px;
		padding-top: 10px;
		border-top: 1px solid var(--border-line-color);
	}
	#coop_u_list li{
		display:flex;
		justify-content: space-between;
	}
	</style>
<?php
/*
 *
list (doc_id)
add (doc_id ,userid)
del (doc_id, userid)
set (doc_id ,userid ,value)
 *
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../public/load_lang.php";
require_once "../ucenter/function.php";
require_once "../group/function.php";

$userid = "";
$isLogin = false;
if ($_COOKIE["userid"]) {
    $userid = $_COOKIE["userid"];
    $isLogin = true;
}
if ($_GET["do"]) {
    $_do = $_GET["do"];
} else {
    echo "Error:缺乏必要的参数 do";
    exit;
}
if ($_GET["doc_id"]) {
    $_doc_id = $_GET["doc_id"];
} else {
    echo "Error:缺乏必要的参数 doc_id";
    exit;
}

$powerlist["10"] = "仅阅读";
//$powerlist["20"] = "建议";
$powerlist["30"] = "可修改";
//$powerlist["40"] = "管理员";

PDO_Connect(_FILE_DB_FILEINDEX_);

echo "<input id='doc_coop_docid' type='hidden' value='{$_doc_id}' />";
$query = "SELECT * from "._TABLE_FILEINDEX_." where uid = ? ";
$Fetch = PDO_FetchAll($query, array($_doc_id));
$iFetch = count($Fetch);
if ($iFetch > 0) {

    $owner = $Fetch[0]["user_id"];
    $uid = $_COOKIE["uid"];
    if ($owner == $uid) {
        //自己的文档
        switch ($_do) {
            case "list":
                break;
            case "add":
                $query = "INSERT INTO power ('id','doc_id','user','power','status','create_time','modify_time','receive_time','type')
                        VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);
                $stmt->execute(
                    array(UUID::v4(),
                        $_GET["doc_id"],
                        $_GET["user_id"],
                        10,
                        1,
                        mTime(),
                        mTime(),
                        mTime(),
                        $_GET["type"],
                    )
                );
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                }
                break;
            case "del":
                $query = "DELETE FROM power WHERE doc_id = ? AND user = ? ";
                $stmt = $PDO->prepare($query);
                $stmt->execute(
                    array($_GET["doc_id"],
                        $_GET["user_id"])
                );
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                }
                break;
            case "set":
                $query = "UPDATE power SET power = ? , modify_time = ? WHERE doc_id = ? AND user = ? ";
                $stmt = $PDO->prepare($query);
                $stmt->execute(
                    array($_GET["value"],
                        mTime(),
                        $_GET["doc_id"],
                        $_GET["user_id"])
                );
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                }
                break;

        }

        $query = "SELECT * from power where doc_id = ? ";
        $Fetch = PDO_FetchAll($query, array($_doc_id));

        echo "<ul id='coop_u_list'>";
        foreach ($Fetch as $row) {
            echo "<li>";
            echo "<span>";
            if ($row["type"] == 0) {
                //个人
                echo "<svg class='icon' style='margin: 0 5px;'>";
                echo '<use xlink:href="./svg/icon.svg#ic_person"></use>';
                echo "</svg>";
                echo ucenter_getA($row["user"], "username");
            } else if ($row["type"] == 1) {
                //群组
                echo "<svg class='icon' style='margin: 0 5px;'>";
                echo '<use xlink:href="./svg/icon.svg#ic_two_person"></use>';
                echo "</svg>";
                echo group_get_name($row["user"]);
            }
            echo "</span>";
            echo "<span>";

            echo "<select onchange=\"coop_power_change('{$row["user"]}',this)\">";
            foreach ($powerlist as $key => $value) {
                echo "<option value='{$key}' ";
                if ($row["power"] == $key) {
                    echo "selected";
                }
                echo ">{$value}</option>";
            }
            echo "</select>";
            echo "<button onclick=\"coop_del('{$row["user"]}')\">" . $_local->gui->delete . "</button>";
            echo "</span>";

            echo "</li>";
        }
        echo "</ul>";
        ?>
				<div id="coop_selector_title">
                <?php echo $_local->gui->add . " " . $_local->gui->cooperators; ?>
				<input type="radio" id="cooperator_type_user" name="cooperator_type" checked><?php echo $_local->gui->person; ?>
				<input type="radio" id="cooperator_type_group" name="cooperator_type"><?php echo $_local->gui->group; ?>
				</div>
                <div id="wiki_search" style="width:100%;">
                    <div><input id="username_input" type="input" placeholder="<?php echo $_local->gui->username; ?>" onkeyup="username_search_keyup(event,this)"/></div>
                    <div id="search_result">
                    </div>
			    </div>
                <?php
} else {
        //别人的的文档
        echo "<a href='fork.php?doc_id={$doc_id}'>[复刻]</a>";

    }
}

?>
