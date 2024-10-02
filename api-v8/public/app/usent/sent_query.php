<?php
/*
get user sentence from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/function.php";

if(!isset($_COOKIE['user_uid'])){
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$sent = $_POST["sent"];
$filter = $_POST["filter"];

$sentList = json_decode($sent);
$output = array();

$dns =  _FILE_DB_SENTENCE_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/* 开始一个事务，关闭自动提交 */

$query = "SELECT uid as id,
                 parent_uid as parent,
				 block_uid as block_id,
				 channel_uid as channal,
				 book_id as book,
				 paragraph,
				 word_start as begin,
				 word_end as end,
				 author,
				 editor_uid as editor,
				 content as text,
				 language,
				 version as ver,
				 status,
				 strlen,
				 modify_time FROM "._TABLE_SENTENCE_.
        " WHERE (book_id = ?  AND paragraph = ? AND word_start = ? AND word_end = ? AND strlen > 0)
                 and (status = 30 or editor_uid = ? )
                 order by modify_time DESC limit 10";

$stmt = $dbh->prepare($query);
foreach ($sentList as $key => $value) {
    # code...
    $stmt->execute(array($value->book, $value->para, $value->start, $value->end,$_COOKIE['user_uid']));
    $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for ($i = 0; $i < count($Fetch); $i++) {
        $Fetch[$i]["nickname"] = ucenter_getA($Fetch[$i]["editor"]);
    }
    $sent = array();
    $sent["info"] = $value;
    $sent["data"] = $Fetch;
    $sent["count"] = count($Fetch); //句子个数
    $output[] = $sent;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
