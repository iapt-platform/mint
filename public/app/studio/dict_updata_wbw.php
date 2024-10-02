<?php
require_once 'checklogin.inc';
require_once "../public/_pdo.php";
require_once "../config.php";
require_once "../redis/function.php";
require_once "../public/function.php";

$return = "";
$serverMsg = "";

$redis = redis_connect();
$input = file_get_contents("php://input");
$xml = simplexml_load_string($input);
$wordsList = $xml->xpath('//word');

PDO_Connect(_FILE_DB_WBW_,_DB_USERNAME_,_DB_PASSWORD_);

//remove the same word
foreach ($wordsList as $ws) {
    $combine = $ws->pali  . $ws->type . $ws->gramma . $ws->parent .  $ws->mean . $ws->factors . $ws->fm;
    $word[$combine] = $ws;
}

$arrInserString = array();
$arrExistWords = array();

$updateWord = array();

foreach ($word as $x => $ws) {
	
    $query = "SELECT id,ref_counter  FROM "._TABLE_DICT_USER_." WHERE
				 word = ? AND
				 type = ? AND
				 gramma = ? AND
				 mean = ? AND
				 base = ? AND
				 factors = ? AND
				 factormean = ? AND source = '_USER_WBW_'" ;
    $Fetch = PDO_FetchAll($query,array($ws->pali,$ws->type,$ws->gramma,$ws->mean,$ws->parent,$ws->factors,$ws->fm));
    $FetchNum = count($Fetch);

    if ($FetchNum == 0) {
		$updateWord["{$ws->pali}"] = 1;
		//没有找到，新建数据
        //new recorder
        $params = array(
            $ws->pali,
            $ws->type,
            $ws->gramma,
            $ws->parent,
            $ws->mean,
            $ws->note,
            $ws->factors,
            $ws->fm,
            $ws->status,
            $ws->language,
			'_USER_WBW_',
            mTime());
        array_push($arrInserString, $params);

    } else {
		#查询本人是否有此记录
		$query = "SELECT id,ref_counter  FROM "._TABLE_DICT_USER_." WHERE
		word = ? AND
		type = ? AND
		gramma = ? AND
		mean = ? AND
		base = ? AND
		factors = ? AND
		factormean = ? AND user_id = ? " ;
		$FetchMy = PDO_FetchAll($query,array($ws->pali,$ws->type,$ws->gramma,$ws->mean,$ws->parent,$ws->factors,$ws->fm,$UID));
		$FetchNumMy = count($FetchMy);
		if($FetchNumMy==0){
			$wordId = $Fetch[0]["id"];
			$ref = $Fetch[0]["ref_counter"] + 1;
			//更新引用计数
			$query = "UPDATE dict SET ref_counter= ?  where id = ? ";
			$stmt = PDO_Execute($query,array($ref,$wordId));
			if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
				$error = PDO_ErrorInfo();
				echo "error" . $error[2] . "<br>";
			}

			#增加我的记录
			$params = array(
				$ws->pali,
				$ws->type,
				$ws->gramma,
				$ws->parent,
				$ws->mean,
				$ws->note,
				$ws->factors,
				$ws->fm,
				$ws->status,
				$ws->language,
				'_WBW_',
				mTime());
			array_push($arrInserString, $params);
		}
        // "have a same recorder";


    }
}
/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query = "INSERT INTO "._TABLE_DICT_USER_." (
						  'word',
						  'type',
						  'gramma',
						  'parent',
						  'mean',
						  'note',
						  'factors',
						  'factormean',
						  'status',
						  'source',
						  'language',
						  'creator_id',
						  'create_time')
				   VALUES (?,?,?,?,?,?,?,?,?,'user',?,?,?)";
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
	#更新 redis
	if ($redis != false) {
		foreach ($updateWord as $key => $value) {
			# code...
			$query = "SELECT * from "._TABLE_DICT_USER_." where word = ? and source = '_USER_WBW_'";
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
				$redis->hSet(Redis["prefix"]."dict/user",$key,json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}				
		}
	}
	#更新redis结束

}
