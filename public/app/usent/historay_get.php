<?php
#句子的历史记录
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/function.php";

$respond['id'] = $_GET["id"];
$respond['status'] = 0;
$respond['error'] = "";
$respond['data'] = array();
PDO_Connect("" . _FILE_DB_USER_SENTENCE_HISTORAY_);
$query = "SELECT sent_uid as sent_id,  user_uid as user_id, content as  text,  create_time as date, landmark , created_at FROM  "._TABLE_SENTENCE_HISTORAY_."  WHERE sent_uid = ? LIMIT 200";
$fetch = PDO_FetchAll($query, array($_GET["id"]));

$_userinfo = new UserInfo();

foreach ($fetch as $key => $value) {
    # code...
    $fetch[$key]["userinfo"] = $_userinfo->getName($value["user_id"]);
}
$respond['data'] = $fetch;
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
