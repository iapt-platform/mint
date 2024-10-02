<?php
require_once "../config.php";
include "../public/_pdo.php";
if (isset($_POST["op"])) {
    $op = $_POST["op"];
} else {
    echo "no op type";
    exit;
}

if (isset($_POST["lastid"])) {
    $lastid = $_POST["lastid"];
} else {
    echo "no lastid ";
    exit;
}

if (isset($_POST["doclist"])) {
    $doclist = $_POST["doclist"];
} else {
    echo "no doclist id";
    exit;
}

if (isset($_POST["data"])) {
    $data = $_POST["data"];
} else {
    echo "no data";
    exit;
}

if (isset($_POST["book"])) {
    $book = $_POST["book"];
} else {
    $book = 0;
}

if (isset($_POST["paragraph"])) {
    $para = $_POST["paragraph"];
} else {
    $para = 0;
}
if (isset($_COOKIE["uid"]) ) {
    $uid = $_COOKIE["uid"];
    $username = $_COOKIE["username"];
} else {
    echo "not login";
    exit;
}
//消息轉入user文件夾，方便升級
PDO_Connect(_FILE_DB_MESSAGE_);

if ($op == "send") {
    // 开始一个事务，关闭自动提交
    $PDO->beginTransaction();
    $query = "INSERT INTO message ('id',
								 'sender',
								 'type',
								 'book',
								 'paragraph',
								 'data',
								 'doc_id',
								 'time')
						VALUES (NULL,'{$username}',?,?,?,?,?,?)";
    $stmt = $PDO->prepare($query);

    $arrMsgData = json_decode($data);
    foreach ($arrMsgData as $oneMsg) {
        $newData = array($oneMsg->type, $oneMsg->book, $oneMsg->para, $oneMsg->data, $oneMsg->docid, time());
        $stmt->execute($newData);
    }

    // 提交更改
    $PDO->commit();
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        echo "error - $error[2] <br>";
    } else {

    }
}
$query = "select id from message where 1 order by id DESC limit 0,1";
$maxId = PDO_FetchOne($query);

//查询相关文档的消息
echo "<message>";
echo "<msg><type>maxid</type><data>$maxId</data></msg>";
$query = "select * from message where \"doc_id\" = '$doclist' AND \"id\" > {$lastid}   limit 0,10000";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        echo "<msg>";
        echo "<id>" . $Fetch[$i]["id"] . "</id>";
        echo "<sender>" . $Fetch[$i]["sender"] . "</sender>";
        echo "<type>" . $Fetch[$i]["type"] . "</type>";
        echo "<data>" . $Fetch[$i]["data"] . "</data>";
        echo "<docid>" . $Fetch[$i]["doc_id"] . "</docid>";
        echo "<time>" . $Fetch[$i]["time"] . "</time>";
        echo "<read>0</read>";
        echo "</msg>";
    }
}
echo "</message>";
