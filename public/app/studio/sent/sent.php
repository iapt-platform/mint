<?php
//查询term字典
require_once "../../public/_pdo.php";
require_once "../../public/function.php";
require_once '../../vendor/autoloader.php';
require_once '../../config.php';

$username = "";

$op = $_POST["op"];

if (isset($_POST["uid"])) {
    $UID = $_POST["uid"];
} else {
    if (isset($_COOKIE["username"]) && !empty($_COOKIE["username"])) {
        $UID = $_COOKIE["uid"];
    }
}

if (isset($_POST["book"])) {
    $_book = $_POST["book"];
}

if (isset($_POST["para"])) {
    $_para = $_POST["para"];
}

if (isset($_POST["begin"])) {
    $_begin = $_POST["begin"];
}

if (isset($_POST["end"])) {
    $_end = $_POST["end"];
}

if (isset($_POST["tag"])) {
    $_tag = $_POST["tag"];
}

if (isset($_POST["text"])) {
    $_text = $_POST["text"];
}

if (isset($_POST["author"])) {
    $_author = $_POST["author"];
}

if (isset($_POST["lang"])) {
    $_lang = $_POST["lang"];
}

if (isset($_POST["status"])) {
    $_status = $_POST["status"];
}

if (isset($_POST["id"])) {
    $_id = $_POST["id"];
}

if (isset($_POST["block_id"])) {
    $_block_id = $_POST["block_id"];
}

global $PDO;

PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_, _DB_PASSWORD_);

switch ($op) {
    case "save":
        /*
        $client = new \GuzzleHttp\Client();
        $parm = ['form_params'=>['op'=>$op,
        'id'=>$_id,
        'block_id'=>$_block_id,
        'parent_id'=>$_parent_id,
        'uid'=>$UID,
        'book'=>$_book,
        'paragraph'=>$_para,
        'begin'=>$_begin,
        'end'=>$_end,
        'style'=>$_style,
        'text'=>$_text,
        'stage'=>$_stage,
        'author'=>$_author,
        'lang'=>$_lang
        ]];

        $response = $client->request('POST', 'http://10.10.1.100/wikipalipro/app/studio/sent/sent.php',$parm);

        $status = $response->getStatusCode();
        $head_type = $response->getHeaderLine('content-type');
        //echo $response->getBody();
         */
        if ($_id == 0) {
            $query = "SELECT * from "._TABLE_SENTENCE_." where
						book='{$_book}' and
						paragraph='{$_para}' and
						begin='{$_begin}' and
						end='{$_end}'  and
						tag='{$_style}'  and
						author='{$_author}'  and
						editor='{$UID}'  and
						language='{$_lang}'
						";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                $_id = $Fetch[0]["id"];
                $_block_id = $Fetch[0]["block_id"];
            }
        }
        $recodeId = $_id;
        $time = mTime();
        if ($_id != 0) { //修改旧记录
            $query = "UPDATE sentence SET text='$_text' ,
									receive_time='{$time}'
							where id=" . $PDO->quote($_id);
        } else { //新建记录
            $uuid = UUID::v4();
            $query = "INSERT INTO sentence VALUES ('{$uuid}',
											'$_block_id',
											'$_book',
											'$_para',
											'$_begin',
											'$_end',
											'$_tag',
											'$_author',
											'$UID',
											'$_text',
											'$_lang',
											'1',
											'1',
											'$time',
											'$time')";
        }
        $stmt = @PDO_Execute($query);
        $respond = array("status" => 0, "message" => "");
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            $respond['status'] = 1;
            $respond['error'] = $error[2];
        } else {
            $respond['status'] = 0;
            $respond['error'] = "";
            if ($recodeId == 0) {
                $respond['id'] = $uuid;
            } else {
                $respond['id'] = $recodeId;
            }

            $respond['block_id'] = $_block_id;
            $respond['begin'] = $_begin;
            $respond['end'] = $_end;
        }
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        break;

}
