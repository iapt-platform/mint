<?php
#输入句子列表查询channel列表 和计算完成度
require_once "../public/function.php";
require_once "../public/_pdo.php";
require_once "../config.php";
require_once '../channal/function.php';
require_once '../ucenter/function.php';
require_once '../share/function.php';
require_once '../db/custom_book.php';
require_once '../redis/function.php';

$redis = redis_connect();

$custom_book = new CustomBookSentence($redis);

$log = "";
$timeStart = microtime(true);

$_data = array();
$output = array();
if (isset($_POST["data"])) {
    $_data = json_decode($_POST["data"], true);
} else {
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}

$_userinfo = new UserInfo();
$_channal = new Channal();

$dns = _FILE_DB_SENTENCE_;
$db_trans_sent = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$db_trans_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_PALI_SENTENCE_;
$db_pali_sent = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$db_pali_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$channal = array();

#查询有阅读权限的channel
$channal_list = array();
$channel_power=array();
if (isset($_COOKIE["userid"])) {
	//找自己的
    PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
    $query = "SELECT uid from "._TABLE_CHANNEL_." where owner_uid = ? and status >0   limit 100";
    $Fetch_my = PDO_FetchAll($query, array($_COOKIE["user_uid"]));
    foreach ($Fetch_my as $key => $value) {
        # code...
		$channal_list[] = $value["uid"];
		$channel_power[$value["uid"]]=30;
    }
	$time = microtime(true);
	$log .= $time-$timeStart ." - 找自己的结束". PHP_EOL;
	$timeStart = $time;

	# 找协作的
	$coop_channal =  share_res_list_get($_COOKIE["user_uid"],2);
	foreach ($coop_channal as $key => $value) {
		# return res_id,res_type,power res_title  res_owner_id
		$channal_list[] = $value["res_id"];
		if(isset($channel_power[$value["res_id"]])){
			if($channel_power[$value["res_id"]]<(int)$value["power"]){
				$channel_power[$value["res_id"]]=(int)$value["power"];
			}
		}
	}
	$time = microtime(true);
	$log .= $time-$timeStart ." - 找协作的结束". PHP_EOL;
	$timeStart = $time;
}
if (count($channal_list) > 0) {
	#  创建一个填充了和params相同数量占位符的字符串 
    $channel_place_holders = implode(',', array_fill(0, count($channal_list), '?'));
    $channel_query = " OR channel_uid IN ($channel_place_holders)";
} else {
    $channel_query = "";
}

# 查询有阅读权限的channel 结束
$time = microtime(true);
$log .= $time-$timeStart ." - 查询有阅读权限的channel 结束". PHP_EOL;
$timeStart = $time;

$final = array();
$article_len = 0;
foreach ($_data as $key => $value) {
    $pali_letter = array();
    # code...
    $id = $value["id"];
    $arrInfo = str_getcsv($value["data"], "@");
    $arrSent = str_getcsv($arrInfo[0], "-");
    $bookId = $arrSent[0];
    $para = $arrSent[1];
    $begin = $arrSent[2];
    $end = $arrSent[3];

    //find out translation
    $tran = "";
    try {
        # 查询句子长度
        $pali_letter["id"] = $arrInfo[0];

		if($bookId<1000){
			$query = "SELECT length FROM "._TABLE_PALI_SENT_." WHERE book= ? AND paragraph= ? AND word_begin= ? AND word_end= ?  ";
			$stmt = $db_pali_sent->prepare($query);
			$stmt->execute(array($bookId, $para, $begin, $end));
			$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($Fetch) {
				$pali_letter["len"] = $Fetch["length"];
				$article_len += $Fetch["length"];
			} else {
				$pali_letter["len"] = 0;
			}			
		}
		else{
			$sentInfo = $custom_book->getAll($bookId, $para, $begin, $end);
			$pali_letter["len"] = $sentInfo["length"];
			$article_len += $sentInfo["length"];
		}


        #公开 或 channel有权限的
        $query = "SELECT channel_uid FROM "._TABLE_SENTENCE_." WHERE book_id= ? AND paragraph= ? AND word_start= ? AND word_end= ?  AND strlen >0 and (status = 30 {$channel_query} ) group by channel_uid  limit 20 ";
        $stmt = $db_trans_sent->prepare($query);
        $parm = array($bookId, $para, $begin, $end);
        $parm = array_merge_recursive($parm, $channal_list);
        $stmt->execute($parm);
        $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($Fetch as $key => $value) {
            # code...
            $pali_letter[$value["channel_uid"]] = 1;
            if (isset($channal[$value["channel_uid"]])) {
                $channal[$value["channel_uid"]]++;
            } else {
                $channal[$value["channel_uid"]] = 1;
            }
        }

        $final[] = $pali_letter;
    } catch (Exception $e) {
        $tran = $e->getMessage();
        //echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

}

$time = microtime(true);
$log .= $time-$timeStart ." - 查询句子长度 结束 ". PHP_EOL;
$timeStart = $time;

foreach ($channal as $key => $value) {
    # 计算句子的完成分布
    $arr_sent_final = array();
    foreach ($final as $final_value) {
        # code...
        $sent_final = array();
        $sent_final["id"] = $final_value["id"];
        $sent_final["len"] = $final_value["len"];
        if (isset($final_value[$key]) && $final_value[$key] == 1) {
            $sent_final["final"] = true;
        } else {
            $sent_final["final"] = false;
        }
        $arr_sent_final[] = $sent_final;
    }
    $channalInfo = $_channal->getChannal($key);
	if($channalInfo){
        $name = $_userinfo->getName($channalInfo["owner_uid"]);
        $channalInfo["username"] = $name["username"];
        $channalInfo["nickname"] = $name["nickname"];
        $channalInfo["count"] = $value;
        $channalInfo["all"] = count($_data);
        $channalInfo["final"] = $arr_sent_final;
        $channalInfo["article_len"] = $article_len;
        $channalInfo["id"] = $key;
        $channalInfo["owner"] = $channalInfo["owner_uid"];

        if(isset($channel_power[$key])){
            $channalInfo["power"] =$channel_power[$key];
        }
        else{
            $channalInfo["power"] =10;
        }
        $output[] = $channalInfo;        
    }

}

$time = microtime(true);
$log .= $time-$timeStart ." - 计算句子的完成分布 结束 ". PHP_EOL;
$timeStart = $time;

echo json_encode($output, JSON_UNESCAPED_UNICODE);
