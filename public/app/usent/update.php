<?php
/*
向句子库中插入或更新数据
 */
include("../log/pref_log.php");
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../usent/function.php";
require_once "../channal/function.php";
require_once "../ucenter/active.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

#检查是否登陆
if (!isset($_COOKIE["userid"])) {
    $respond["status"] = 1;
    $respond["message"] = 'not login';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}
if (isset($_POST["landmark"])) {
    $_landmark = $_POST["landmark"];
} else {
    $_landmark = "";
}

$aData = json_decode($_POST["data"], true);

PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_, _DB_PASSWORD_);

//查询没有id的哪些是数据库里已经存在的，防止多次提交同一条记录造成一个句子 多个channal
$newList = array();
$new_id = array();
$oldList = array();
$query = "SELECT uid FROM "._TABLE_SENTENCE_." WHERE book_id = ? and paragraph = ? and  word_start = ? and word_end = ? and channel_uid = ? limit  1 ";
foreach ($aData as $data) {
    if (!isset($data["id"]) || empty($data["id"])) {
        $id = PDO_FetchOne($query, array($data["book"],
            $data["paragraph"],
            $data["begin"],
            $data["end"],
            $data["channal"],
        ));
        if (empty($id)) {
            $newList[] = $data;
        } else {
            $data["id"] = $id;
            $oldList[] = $data;
        }
    } else {
        $oldList[] = $data;
    }
}
$update_list = array(); //已经成功修改数据库的数据 回传客户端

/* 修改现有数据 */
if (count($oldList) > 0) {
    add_edit_event(_SENT_EDIT_, "{$oldList[0]["book"]}-{$oldList[0]["paragraph"]}-{$oldList[0]["begin"]}-{$oldList[0]["end"]}@{$oldList[0]["channal"]}");

    $PDO->beginTransaction();
    $query = "UPDATE "._TABLE_SENTENCE_." SET content= ?  , status = ? , strlen = ? , modify_time= ? , updated_at=now()   where  uid= ?  ";
    $sth = $PDO->prepare($query);

    foreach ($oldList as $data) {
        if (isset($data["id"])) {
            if (isset($data["time"])) {
                $modify_time = $data["time"];
            } else {
                $modify_time = mTime();
            }
            $sth->execute(array($data["text"], $data["status"], mb_strlen($data["text"], "UTF-8"), mTime(),  $data["id"]));
        }
    }

    $PDO->commit();

    $respond = array("status" => 0, "message" => "", "insert_error" => "", "new_list" => array());

    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        /*  识别错误且回滚更改  */
        $PDO->rollBack();
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
    } else {
        #没错误 添加log 更新历史记录
        foreach ($oldList as $data) {
            $respond['message'] = update_historay($data["id"], $_COOKIE["userid"], $data["text"], $_landmark);
            if ($respond['message'] !== "") {
                $respond['status'] = 1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        $respond['status'] = 0;
        $respond['message'] = "成功";
        foreach ($oldList as $key => $value) {
            $update_list[] = array("id" => $value["id"], "book" => $value["book"], "paragraph" => $value["paragraph"], "begin" => $value["begin"], "end" => $value["end"], "channal" => $value["channal"], "text" => $value["text"]);

        }
    }
}

/* 插入新数据 */
//查询channel语言

if (count($newList) > 0) {
    add_edit_event(_SENT_NEW_, "{$newList[0]["book"]}-{$newList[0]["paragraph"]}-{$newList[0]["begin"]}-{$newList[0]["end"]}@{$newList[0]["channal"]}");
    $PDO->beginTransaction();
    $query = "INSERT INTO "._TABLE_SENTENCE_." (
        id,
        uid,
        parent_uid,
        book_id,
        paragraph,
        word_start,
        word_end,
        channel_uid,
        author,
        editor_uid,
        content,
        language,
        version,
        status,
        strlen,
        modify_time,
        create_time
        )
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? )";
    $sth = $PDO->prepare($query);

    $channel_info = new Channal();

    foreach ($newList as $data) {
        $uuid = UUID::v4();
        if (isset($data["parent"])) {
            $parent = $data["parent"];
        } else {
            $parent = "";
        }

        $queryChannel = $channel_info->getChannal($data["channal"]);
        if ($queryChannel == false) {
            $lang = $data["language"];
            $status = 10;
        } else {
            $lang = $queryChannel["lang"];
            $status = $queryChannel["status"];
        }
        $sth->execute(array(
            $snowflake->id(),
            $uuid,
            $parent,
            $data["book"],
            $data["paragraph"],
            $data["begin"],
            $data["end"],
            $data["channal"],
            $data["author"],
            $_COOKIE["userid"],
            $data["text"],
            $lang,
            1,
            $status,
            mb_strlen($data["text"], "UTF-8"),
            mTime(),
            mTime(),
        ));
        $new_id[] = array($uuid, $data["book"], $data["paragraph"], $data["begin"], $data["end"], $data["channal"], $data["text"]);
    }
    $PDO->commit();

    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        /*  识别错误且回滚更改  */
        $PDO->rollBack();
        $error = PDO_ErrorInfo();
        $respond['insert_error'] = $error[2];
        $respond['new_list'] = array();
    } else {
        $respond['insert_error'] = 0;
        foreach ($new_id as $key => $value) {
            $update_list[] = array("id" => $value[0], "book" => $value[1], "paragraph" => $value[2], "begin" => $value[3], "end" => $value[4], "channal" => $value[5], "text" => $value[6]);
        }
    }
}

$respond['update'] = $update_list;

echo json_encode($respond, JSON_UNESCAPED_UNICODE);

PrefLog();