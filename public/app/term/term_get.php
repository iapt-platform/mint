<?php
/*
查询term字典
输入单词列表
输出查到的结果
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../channal/function.php';

if(isset($_POST["readonly"])){
    $readonly = $_POST["readonly"];
}else{
    $readonly = true;
}
PDO_Connect(_FILE_DB_TERM_);

$output = array();
if (isset($_POST["words"])) {
    $wordlist = json_decode($_POST["words"]);
    if ($readonly == "false" && !empty($_POST["channal"])) {
        $channal = explode(",", $_POST["channal"]);
        $channal_info = new Channal();
        $channal_owner = array();
        foreach ($channal as $key => $value) {
            # code...
            $info = $channal_info->getChannal($value);
            if ($info) {
                $channal_owner[$info["owner_uid"]] = 1;
            }
        }
        /*  创建一个填充了和params相同数量占位符的字符串 */
        $place_holders = implode(',', array_fill(0, count($channal), '?'));
        $owner_holders = implode(',', array_fill(0, count($channal_owner), '?'));
        $query = "SELECT guid,word,meaning,other_meaning,owner,channal,language,tag ,note  FROM "._TABLE_TERM_." WHERE channal IN ($place_holders) OR owner IN ($owner_holders)";
        foreach ($channal_owner as $key => $value) {
            # code...
            $channal[] = $key;
        }
        $fetch = PDO_FetchAll($query, $channal);
        $userinfo = new UserInfo();
        $user = array();
        foreach ($channal_owner as $key => $value) {
            # code...
            $user[$key] = $userinfo->getName($key);
        }
        foreach ($fetch as $key => $value) {
            # code...
            if (isset($user[$fetch[$key]["owner"]])) {
                $fetch[$key]["user"] = $user[$fetch[$key]["owner"]];
            } else {
                $fetch[$key]["user"] = array("nickname" => "", "username" => "");
            }
            $output[] = $fetch[$key];
        }
    } else {
        foreach ($wordlist as $key => $value) {
            # code...
            $pali = $value->pali;
            $parm = array();
            $parm[] = $pali;

            $otherCase = "";
            if ($value->channal != "") {
                $otherCase .= " channal = ? ";
                $parm[] = $value->channal;
            }

            if ($value->editor != "") {
                if ($otherCase != "") {
                    $otherCase .= " OR ";
                }
                $otherCase .= " owner = ? ";
                $parm[] = $value->editor;
            }

            if ($value->lang != "") {
                if ($otherCase != "") {
                    $otherCase .= " OR ";
                }
                $otherCase .= " language = ? ";
                $parm[] = $value->lang;
            }

            if ($otherCase == "") {
                $query = "SELECT guid,word,meaning,other_meaning,owner,channal,language,tag ,note FROM "._TABLE_TERM_." WHERE word = ? ";
            } else {
                $query = "SELECT guid,word,meaning,other_meaning,owner,channal,language,tag ,note FROM "._TABLE_TERM_." WHERE word = ? AND ( $otherCase )";
            }

            $fetch = PDO_FetchAll($query, $parm);
            $userinfo = new UserInfo();
            foreach ($fetch as $key => $value) {
				# code...
				if(isset($_COOKIE["userid"])){
					$currUserUid = $_COOKIE["userid"];
				}else{
					$currUserUid = "";
				}
				if($value["owner"]==$currUserUid){
					$fetch[$key]["readonly"]=false;
				}
				else{
					$fetch[$key]["readonly"]=true;
				}
                $fetch[$key]["user"] = $userinfo->getName($value["owner"]);
                $output[] = $fetch[$key];
            }
        }
    }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
