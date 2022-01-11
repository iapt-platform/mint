<?php
//查询参考字典
include("../log/pref_log.php");
require_once '../config.php';
require_once '../public/_pdo.php';
require_once '../redis/function.php';
require_once '../dict/function.php';

if (isset($_GET["language"])) {
    $currLanguage = $_GET["language"];
} else {
    if (isset($_COOKIE["language"])) {
        $currLanguage = $_COOKIE["language"];
    } else {
        $currLanguage = "en";
    }
}
$currLanguage = explode("-", $currLanguage)[0];

if (isset($_GET["word"]) && !empty($_GET["word"])) {
    $word = $_GET["word"];
} else {
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
    exit;
}

$redis = redis_connect();
if($redis!==false){
	$arrWordIdx = $redis->hget("ref_dict_idx",$word);
	if($arrWordIdx===FALSE){
		PDO_Connect(_FILE_DB_REF_INDEX_);
		$query = "SELECT word,count from " . _TABLE_REF_INDEX_ . " where eword like ?  OR word like ?  limit 0,15";
		$Fetch = PDO_FetchAll($query, array($word . '%', $word . '%'));
		$arrWordIdx=json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		$redis->hset("ref_dict_idx",$word,$arrWordIdx);
	}
	$arrResult = json_decode($arrWordIdx,true);
	foreach ($arrResult as $key => $value) {
		# 获取字典里的第一个意思
		$arrResult[$key]["mean"]=getRefFirstMeaning($arrResult[$key]["word"],$currLanguage,$redis);
	}
	echo json_encode($arrResult, JSON_UNESCAPED_UNICODE);
	exit;	
}
else
{
	PDO_Connect(_FILE_DB_REF_INDEX_);
	$query = "SELECT word,count from " . _TABLE_REF_INDEX_ . " where eword like ?  OR word like ?  limit 0,15";
	$Fetch = PDO_FetchAll($query, array($word . '%', $word . '%'));

	PDO_Connect(_FILE_DB_REF_, _DB_USERNAME_, _DB_PASSWORD_);
	$query = "SELECT mean from " . _TABLE_DICT_REF_ . " where word = ? and language = ?  limit 0,1";
	foreach ($Fetch as $key => $value) {
		# code...
		$mean = PDO_FetchRow($query, array($value["word"], $currLanguage));
		if ($mean) {
			$Fetch[$key]["mean"] = $mean["mean"];
		} else {
			if ($currLanguage != "en") {
				$mean = PDO_FetchRow($query, array($value["word"], "en"));
				if ($mean) {
					$Fetch[$key]["mean"] = $mean["mean"];
				} else {
					$Fetch[$key]["mean"] = "";
				}
			} else {
				$Fetch[$key]["mean"] = "";
			}
		}
	}
	echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}


PrefLog();