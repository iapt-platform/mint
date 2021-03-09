<?php
require_once 'checklogin.inc';
require_once "../public/_pdo.php";
require_once "../path.php";
require_once "../redis/function.php";

$redis = redis_connect();
$input = file_get_contents("php://input");

$return = "";
$serverMsg = "";

$xml = simplexml_load_string($input);

PDO_Connect(_FILE_DB_WBW_);
PDO_Execute("PRAGMA synchronous = OFF");
PDO_Execute("PRAGMA journal_mode = WAL");
PDO_Execute("PRAGMA foreign_keys = ON");
PDO_Execute("PRAGMA busy_timeout = 5000");

$wordsList = $xml->xpath('//word');
//$serverMsg+= "word count:".count($wordsList)."<br>";

//remove the same word
foreach ($wordsList as $ws) {
    $combine = $ws->pali . $ws->guid . $ws->type . $ws->gramma . $ws->parent . $ws->parent_id . $ws->mean . $ws->factors . $ws->fm . $ws->part_id;
    $word[$combine] = $ws;
}

$arrInserString = array();
$arrExistWords = array();

$updateWord = array();

foreach ($word as $x => $ws) {
	
    $query = "SELECT id,ref_counter  FROM dict WHERE
				\"guid\"= ? AND
				\"pali\"= ? AND
				\"type\"= ? AND
				\"gramma\"= ? AND
				\"mean\"= ? AND
				\"parent\"= ? AND
				\"parent_id\"= ? AND
				\"factors\"= ? AND
				\"factormean\"= ? AND
				\"part_id\"= ?" ;
    $Fetch = PDO_FetchAll($query,array($ws->guid,$ws->pali,$ws->type,$ws->gramma,$ws->mean,$ws->parent,$ws->parent_id,$ws->factors,$ws->fm,$ws->part_id));
    $FetchNum = count($Fetch);

    if ($FetchNum == 0) {
		$updateWord["{$ws->pali}"] = 1;
		//没有找到，新建数据
        //new recorder
        $params = array($ws->guid,
            $ws->pali,
            $ws->type,
            $ws->gramma,
            $ws->parent,
            $ws->parent_id,
            $ws->mean,
            $ws->note,
            $ws->factors,
            $ws->fm,
            $ws->part_id,
            $ws->status,
            $ws->language,
            $UID,
            time());
        array_push($arrInserString, $params);

    } else {
        // "have a same recorder";
        $wordId = $Fetch[0]["id"];
        $ref = $Fetch[0]["ref_counter"] + 1;
        //更新引用计数
        $query = "UPDATE dict SET ref_counter='$ref' where id=" . $PDO->quote($wordId);
        $stmt = @PDO_Execute($query);
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo "error" . $error[2] . "<br>";
        }
        //去掉已经有的索引
        $query = "select count(*)  from user_index where word_index={$wordId} and user_id={$UID}";
        $num = PDO_FetchOne($query);
        if ($num == 0) {
            array_push($arrExistWords, $Fetch[0]["id"]);
        }
    }
}
/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query = "INSERT INTO dict ('id',
						  'guid',
						  'pali',
						  'type',
						  'gramma',
						  'parent',
						  'parent_id',
						  'mean',
						  'note',
						  'factors',
						  'factormean',
						  'part_id',
						  'status',
						  'dict_name',
						  'language',
						  'creator',
						  'time')
				   VALUES (null,?,?,?,?,?,?,?,?,?,?,?,?,'user',?,?,?)";
$stmt = $PDO->prepare($query);
foreach ($arrInserString as $oneParam) {
    $stmt->execute($oneParam);
}
/* 提交更改 */
$PDO->commit();
$lastid = $PDO->lastInsertId();

if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {
	//成功
    $count = count($arrInserString);
    echo "updata $count recorders.";
    //更新索引表

    $iFirst = $lastid - $count + 1;
    for ($i = 0; $i < $count; $i++) {
        array_push($arrExistWords, $iFirst + $i);
    }
    if (count($arrExistWords) > 0) {
        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO user_index ('id','word_index','user_id','create_time')
								VALUES (null,?,{$UID},?)";
        $stmt = $PDO->prepare($query);
        foreach ($arrExistWords as $oneId) {
            $stmt->execute(array($oneId, time()));
        }
        /* 提交更改 */
        $PDO->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo "error - $error[2] <br>";
        } else {
            echo "updata index " . count($arrExistWords) . " recorders.";
        }
    } else {
        echo "updata index 0";
	}
	
	#更新 redis
	if ($redis != false) {
		foreach ($updateWord as $key => $value) {
			# code...
			$query = "SELECT * from dict where pali = ? ";
			$stmt = $PDO->prepare($query);
			$stmt->execute(array($key));
			if ($stmt) {
				$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$redisWord=array();
				foreach ($Fetch as  $one) {
					# code...
					$redisWord[] = array($one["id"],
										$one["pali"],
									$one["type"],
									$one["gramma"],
									$one["parent"],
									$one["mean"],
									$one["note"],
									$one["factors"],
									$one["factormean"],
									$one["status"],
									$one["confidence"],
									$one["creator"],
									$one["dict_name"],
									$one["language"]
									);
				}
				$redis->hSet("dict://user",$key,json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}				
		}
	}
	#更新redis结束

}
