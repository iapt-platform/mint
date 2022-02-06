<?php
require_once 'checklogin.inc';
require_once "../config.php";
require_once "../public/_pdo.php";

PDO_Connect(_FILE_DB_FILEINDEX_);
$query = "SELECT file_name from "._TABLE_FILEINDEX_." where user_id=? AND  uid=?";
$Fetch = PDO_FetchOne($query,array($_COOKIE["uid"],$_GET["id"]));
$FileName = _DIR_USER_DOC_ . "/" . $userid . _DIR_MYDOCUMENT_ . "/" . $Fetch;
if (file_exists($FileName)) {
    echo file_get_contents($FileName);
} else {
    echo "文件{$FileName}不存在";
}
