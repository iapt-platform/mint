<?php
/*
修改术语
 */
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

#未登录不能修改
if (isset($_COOKIE["userid"]) == false) {
    $respond['status'] = 1;
    $respond['message'] = "not yet log in";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

$respond = array("status" => 0, "message" => "");
PDO_Connect("" . _FILE_DB_TERM_);

if ($_POST["id"] != "") {
    #更新
    $query = "UPDATE term SET meaning= ? ,other_meaning = ? , tag= ? ,channal = ? ,  language = ? , note = ? , receive_time= ?, modify_time= ?   where guid= ? and owner = ? ";
    $stmt = @PDO_Execute($query, array($_POST["mean"],
        $_POST["mean2"],
        $_POST["tag"],
        $_POST["channal"],
        $_POST["language"],
        $_POST["note"],
        mTime(),
        mTime(),
        $_POST["id"],
        $_COOKIE["userid"],
    ));
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2] . $query;
    } else {
        $respond['status'] = 0;
        $respond['message'] = $_POST["word"];
    }
} else {
    #新建
    $parm[] = UUID::v4();
    $parm[] = $_POST["word"];
    $parm[] = pali2english($_POST["word"]);
    $parm[] = $_POST["mean"];
    $parm[] = $_POST["mean2"];
    $parm[] = $_POST["tag"];
    $parm[] = $_POST["channal"];
    $parm[] = $_POST["language"];
    $parm[] = $_POST["note"];
    $parm[] = $_COOKIE["userid"];
    $parm[] = 0;
    $parm[] = mTime();
    $parm[] = mTime();
    $parm[] = mTime();
    $query = "INSERT INTO term (id, guid, word, word_en, meaning, other_meaning, tag, channal, language,note,owner,hit,create_time,modify_time,receive_time )
	VALUES (NULL, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
    $stmt = @PDO_Execute($query, $parm);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2] . $query;
    } else {
        $respond['status'] = 0;
        $respond['message'] = $_POST["word"];
    }
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
