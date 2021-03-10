<?php
/*
修改术语
 */
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once "../redis/function.php";

$redis = redis_connect();

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
	#先查询是否有权限
	$query = "SELECT id from term where guid= ? and owner = ? ";
	$stmt = $PDO->prepare($query);
	$stmt->execute(array($_POST["id"],$_COOKIE["userid"]));
	if ($stmt) {
		$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$Fetch){
			$respond['status'] = 1;
			$respond['message'] = "no power";
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;			
		}
	}
    $query = "UPDATE term SET meaning= ? ,other_meaning = ? , tag= ? ,channal = ? ,  language = ? , note = ? , receive_time= ?, modify_time= ?   where guid= ? and owner = ? ";
	$stmt = @PDO_Execute($query, 
						array($_POST["mean"],
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
	if(trim($_POST["word"])==""){
		$respond['status'] = 1;
		$respond['message'] = "pali word cannot be empty";
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}
	if(trim($_POST["mean"])==""){
		$respond['status'] = 1;
		$respond['message'] = "meaning cannot be empty";
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}
	if(trim($_POST["language"])==""){
		$respond['status'] = 1;
		$respond['message'] = "language cannot be empty";
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}
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

	#更新 redis
	if ($redis != false) {
		{
			# code...
			$query = "SELECT id,word,meaning,other_meaning,note,owner,language from term where word = ? ";
			$stmt = $PDO->prepare($query);
			$stmt->execute(array($_POST["word"]));
			if ($stmt) {
				$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$redisWord=array();
				foreach ($Fetch as  $one) {
					# code...
					$redisWord[] = array($one["id"],
										$one["word"],
									"",
									"",
									"",
									$one["meaning"]."$".$one["other_meaning"],
									$one["note"],
									"",
									"",
									1,
									100,
									$one["owner"],
									"term",
									$one["language"]
									);
				}
				$redis->hSet("dict://term",$_POST["word"],json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}				
		}
	}
	#更新redis结束

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
