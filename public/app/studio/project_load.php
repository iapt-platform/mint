<?php
require_once 'checklogin.inc';
require_once "../config.php";
require_once "../public/_pdo.php";

PDO_Connect(_FILE_DB_FILEINDEX_);
$query = "select file_name from fileindex where user_id='{$_COOKIE["uid"]}' AND  id='{$_GET["id"]}'";
$Fetch = PDO_FetchOne($query);
$FileName = _DIR_USER_DOC_ . "/" . $userid . _DIR_MYDOCUMENT_ . "/" . $Fetch;
if (file_exists($FileName)) {
    echo file_get_contents($FileName);
} else {
    echo "文件{$FileName}不存在";
}
