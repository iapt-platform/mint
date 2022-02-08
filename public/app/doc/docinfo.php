<?php
/*
 *
 *
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/function.php";

$userid = "";
$isLogin = false;
if ($_COOKIE["userid"]) {
    $userid = $_COOKIE["userid"];
    $isLogin = true;
}

PDO_Connect(_FILE_DB_FILEINDEX_);
if (isset($_GET["id"])) {

    $doc_id = $_GET["id"];
    $query = "SELECT * from "._TABLE_FILEINDEX_." where uid = ? ";
    $Fetch = PDO_FetchAll($query, array($doc_id));
    $iFetch = count($Fetch);
    if ($iFetch > 0) {
        //文档信息
        echo "<h2>";
        echo "<a href='../ucenter/info.php?id={$Fetch[0]["user_id"]}'>" . ucenter_get($Fetch[0]["user_id"], "") . "</a>/";
        echo "<a>{$Fetch[0]["title"]}</a>";
        echo "</h2>";
        echo "<div>" . _get_para_path($Fetch[0]["book"], $Fetch[0]["paragraph"]) . "</div>";
        echo "<div>标签:</div>";
        echo "<div>创建时间：{$Fetch[0]["create_time"]} / 更新时间：{$Fetch[0]["modify_time"]}</div>";
        echo "<h3>简介</h3>";
        echo "<a href='../pcdl/reader.php'>[阅读]</a>";
        if ($owner == $uid) {
            //自己的文档
            echo "<a href='coop.php?id={$doc_id}'>[协作管理]</a>";
        } else {
            //别人的的文档
            echo "<a href='fork.php?doc_id={$doc_id}'>[复刻]</a>";

        }
    }
} else {
    echo "无效的文档编号";
}
