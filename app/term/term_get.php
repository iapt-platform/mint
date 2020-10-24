<?php
/*
查询term字典
输入单词列表
输出查到的结果
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

PDO_Connect("sqlite:"._FILE_DB_TERM_);

$output = array();
if(isset($_POST["words"])){
    $wordlist = json_decode($_POST["words"]);

    foreach ($wordlist as $key => $value) {
        # code...
        $pali = $value->pali;
        $parm = array();
        $parm[] = $pali;       

        $otherCase = "";
        if($value->channal != ""){
            $otherCase .= " channal = ? ";
            $parm[] = $value->channal;
        }

        if($value->editor != ""){
            if($otherCase != ""){
                $otherCase .= " OR ";
            }
            $otherCase .= " owner = ? ";
            $parm[] = $value->editor;
        }

        if($value->lang != ""){
            if($otherCase != ""){
                $otherCase .= " OR ";
            }
            $otherCase .= " language = ? ";
            $parm[] = $value->lang;
        }

        if($otherCase==""){
            $query = "SELECT * FROM term WHERE word = ? ";
        }
        else{
            $query = "SELECT * FROM term WHERE word = ? AND ( $otherCase )";
        }

        $fetch = PDO_FetchAll($query,$parm);
        $userinfo = new UserInfo();
        foreach ($fetch as $key => $value) {
            # code...
            $fetch[$key]["user"]=$userinfo->getName($value["owner"]);
            $output[] =  $fetch[$key];
        }
    }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>