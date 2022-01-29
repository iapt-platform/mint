<?php
#更新一个句子
include("../log/pref_log.php");
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../usent/function.php";
require_once "../ucenter/active.php";
require_once "../share/function.php";
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
//回传数据
$respond = array("status" => 0, "message" => "");
$respond['id'] = $_POST["id"];
$respond['book'] = $_POST["book"];
$respond['para'] = $_POST["para"];
$respond['begin'] = $_POST["begin"];
$respond['end'] = $_POST["end"];
$respond['channal'] = $_POST["channal"];
$respond['text'] = $_POST["text"];
$respond['editor'] = $_COOKIE["userid"];
$respond['commit_type'] = 0; #0 未提交 1 插入 2 修改 3pr 

add_edit_event(_SENT_EDIT_, "{$_POST["book"]}-{$_POST["para"]}-{$_POST["begin"]}-{$_POST["end"]}@{$_POST["channal"]}");

#先查询对此channal是否有权限修改
$cooperation = 0;
$text_lang = "en";
$channel_status = 0;
if (isset($_POST["channal"])) {
    PDO_Connect( _FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
    $query = "SELECT owner_uid, lang , status FROM "._TABLE_CHANNEL_." WHERE uid=?";
    $fetch = PDO_FetchRow($query, array($_POST["channal"]));

    if ($fetch) {
        $text_lang = $fetch["lang"];
		$channel_status = $fetch["status"];
    }
    $respond['lang'] = $text_lang;
    if ($fetch && $fetch["owner_uid"] == $_COOKIE["user_uid"]) {
        #自己的channal
        $cooperation = 30;
    } else {
		$sharePower = share_get_res_power($_COOKIE["user_uid"],$_POST["channal"]);
		$cooperation = $sharePower;
		if($channel_status>=30 && $cooperation<10){
			#全网公开的 可以提交pr
			$cooperation = 10;
		}
    }
} else {
    $respond["status"] = 1;
    $respond["message"] = 'error channal id';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

if($cooperation==0){
	$respond['message'] = "no power";
	$respond['status'] = 1;
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_,_DB_PASSWORD_);

$_id = false;
if ((isset($_POST["id"]) && empty($_POST["id"])) || !isset($_POST["id"])) {

    # 判断是否已经有了
    $query = "SELECT uid FROM "._TABLE_SENTENCE_." WHERE book_id = ? AND paragraph = ? AND word_start = ? AND word_end = ? AND channel_uid = ? ";
    $_id = PDO_FetchOne($query, array($_POST["book"], $_POST["para"], $_POST["begin"], $_POST["end"], $_POST["channal"]));
} else {
    $_id = $_POST["id"];
}

if ($_id == false) {
    # 没有id新建
    if ($cooperation >=20) {
        #有写入权限
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
            status,
            strlen,
            modify_time,
            create_time
            )
            VALUES (? , ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        $stmt = $PDO->prepare($query);
        $newId = UUID::v4();
        $stmt->execute(array(
            $snowflake->id(),
            $newId,
            "",
            $_POST["book"],
            $_POST["para"],
            $_POST["begin"],
            $_POST["end"],
            $_POST["channal"],
            "",
            $_COOKIE["userid"],
            $_POST["text"],
            $text_lang,
            $channel_status,
            mb_strlen($_POST["text"], "UTF-8"),
            mTime(),
            mTime(),
        ));
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            /*  识别错误  */
            $error = PDO_ErrorInfo();
            $respond['message'] = $error[2];
            $respond['status'] = 1;
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            # 没错误
            # 更新historay
			#没错误 更新历史记录
			$respond['commit_type'] = 1;
            $respond['message'] = update_historay($newId, $_COOKIE["userid"], $_POST["text"], $_landmark);
            if ($respond['message'] !== "") {
                $respond['status'] = 1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    } else {
		#没写入权限 插入pr数据
		$query = "INSERT INTO "._TABLE_SENTENCE_PR_." 
        (
            id,
            book_id,
            paragraph,
            word_start,
            word_end,
            channel_uid,
            author,
            editor_uid,
            content,
            language,
            status,
            strlen,
            modify_time,
            create_time
            )
            VALUES ( ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$stmt = $PDO->prepare($query);
		# 初始状态 1 未处理
		$stmt->execute(array(
            $snowflake->id(),
            $_POST["book"],
            $_POST["para"],
            $_POST["begin"],
            $_POST["end"],
            $_POST["channal"],
            "",
            $_COOKIE["userid"],
            $_POST["text"],
            $text_lang,
            1,
            mb_strlen($_POST["text"], "UTF-8"),
            mTime(),
            mTime()
							));
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			/*  识别错误  */
			$error = PDO_ErrorInfo();
			$respond['message'] = $error[2];
			$respond['status'] = 1;
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;
		} else {
		# 没错误
			$respond['message'] = "已经提交修改建议";
			$respond['status'] = 0;
			$respond['commit_type'] = 3;
		}
    }
} else {
    /* 修改现有数据 */
    #判断是否有修改权限
    if ($cooperation >=20) {
        #有写入权限
        $query = "UPDATE "._TABLE_SENTENCE_." SET content= ?  , strlen = ? , editor_uid = ? ,  modify_time= ?   where  uid= ?  ";
        $stmt = PDO_Execute($query,
            array($_POST["text"],
                mb_strlen($_POST["text"], "UTF-8"),
                $_COOKIE["userid"],
                mTime(),
                $_id));
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            /*  识别错误  */
            $error = PDO_ErrorInfo();
            $respond['message'] = $error[2];
            $respond['status'] = 1;
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            #没错误 更新历史记录
			$respond['commit_type'] = 2;
            $respond['message'] = update_historay($_id, $_COOKIE["userid"], $_POST["text"], $_landmark);
            if ($respond['message'] !== "") {
                $respond['status'] = 1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    } else {
        #TO DO没权限 插入pr数据
		#没写入权限 插入pr数据
		$query = "INSERT INTO "._TABLE_SENTENCE_PR_." (
            id,
			book_id,
			paragraph,
			word_start,
			word_end,
			channel_uid,
			author,
			editor_uid,
			content,
			language,
			status,
			strlen,
			modify_time,
			create_time
			)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$stmt = $PDO->prepare($query);
		# 初始状态 1 未处理
		$stmt->execute(array(
            $snowflake->id(),
            $_POST["book"],
            $_POST["para"],
            $_POST["begin"],
            $_POST["end"],
            $_POST["channal"],
            "",
            $_COOKIE["userid"],
            $_POST["text"],
            $text_lang,
            1,
            mb_strlen($_POST["text"], "UTF-8"),
            mTime(),
            mTime()
            ));
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			/*  识别错误  */
			$error = PDO_ErrorInfo();
			$respond['message'] = $error[2];
			$respond['status'] = 1;
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;
		} else {
		# 没错误
			$respond['commit_type'] = 3;
			$respond['message'] = "已经提交修改建议";
			$respond['status'] = 0;
		}
    }
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);

PrefLog();