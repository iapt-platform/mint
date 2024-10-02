<?php
/*
修改术语
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once "../redis/function.php";
require_once "../channal/function.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$redis = redis_connect();

#未登录不能修改
if (isset($_COOKIE["userid"]) == false) {
    $respond['status'] = 1;
    $respond['message'] = "not yet log in";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}


$respond = array("status" => 0, "message" => "");
PDO_Connect( _FILE_DB_TERM_);

$channelInfo = new Channal($redis);

if ($_POST["id"] != "" && !isset($_POST['save_as'])) {
	#更新
	#先查询是否有权限
	#是否这个术语的作者
	$query = "SELECT id,channal,owner from "._TABLE_TERM_." where guid= ? ";
	$stmt = $PDO->prepare($query);
	$stmt->execute(array($_POST["id"]));
	if ($stmt) {
		$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
		if($Fetch){
			if($Fetch['owner']!=$_COOKIE["userid"]){
				#不是这个术语的作者，查是否是channel的有编辑权限者	
				
				$channelPower = $channelInfo->getPower($Fetch['channal']);
				if($channelPower<20){
					$respond['status'] = 1;
					$respond['message'] = "no power";
					echo json_encode($respond, JSON_UNESCAPED_UNICODE);
					exit;						
				}
			}
			
		
		}else{
			$respond['status'] = 1;
			$respond['message'] = "no word";
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;				
		}
	}
    $query = "UPDATE "._TABLE_TERM_." SET meaning= ? ,other_meaning = ? , tag= ? ,  language = ? , note = ? ,  modify_time= ? , updated_at = now()  where guid= ? ";
	$stmt = @PDO_Execute($query, 
						array($_POST["mean"],
        					  $_POST["mean2"],
        					  $_POST["tag"],
        					  $_POST["language"],
        					  $_POST["note"],
        					  mTime(),
        					  $_POST["id"],
    ));
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2] . $query;
    } else {
        $respond['status'] = 0;
        $respond['message'] = $_POST["word"];
        $respond['data'] = ["guid"=>$_POST["id"],
							"word"=>$_POST["word"],
							"word_en"=>$_POST["word"],
							"meaning"=>$_POST["mean"],
							"other_meaning"=>$_POST["mean2"],
							"tag"=>$_POST["tag"],
							"channal"=>$_POST["channal"],
							"language"=>$_POST["language"],
							"note"=>$_POST["note"],
							"owner"=>$_COOKIE["userid"]
						];
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
	#先查询是否有重复数据
	if($_POST["channal"]==""){
		$query = "SELECT id from "._TABLE_TERM_." where word= ? and  language=? and tag=? and owner = ? ";
		$stmt = $PDO->prepare($query);
		$stmt->execute(array($_POST["word"],$_POST["language"],$_POST["tag"],$_COOKIE["userid"]));
	}else{
        #TODO 
		$query = "SELECT id from "._TABLE_TERM_." where word= ? and channal=?  and tag=? and owner = ? ";
		$stmt = $PDO->prepare($query);
		$stmt->execute(array($_POST["word"],$_POST["channal"],$_POST["tag"],$_COOKIE["userid"]));
	}
	if($_POST["channal"]==""){
        $owner_uid = $_COOKIE["user_uid"];
    }else{
        $channel = $channelInfo->getChannal($_POST["channal"]);
        if($channelInfo){
            $owner_uid = $channel["owner_uid"];
        }else{
            $owner_uid = $_COOKIE["user_uid"];
        }
    }
	if ($stmt) {
		$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
		if($Fetch){
			$respond['status'] = 1;
			$respond['message'] = "已经有同样的记录";
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
    $parm = [
        $snowflake->id(),
        UUID::v4(),
        $_POST["word"],
        pali2english($_POST["word"]),
        $_POST["mean"],
        $_POST["mean2"],
        $_POST["tag"],
        $_POST["channal"],
        $_POST["language"],
        $_POST["note"],
        $owner_uid,
        $_COOKIE["user_id"],
        mTime(),
        mTime()
        ];
    $query = "INSERT INTO "._TABLE_TERM_." 
    (
        id, 
        guid, 
        word, 
        word_en, 
        meaning, 
        other_meaning, 
        tag, 
        channal, 
        language,
        note,
        owner,
        editor_id,
        create_time,
        modify_time
    )
	VALUES (?, ? , ?, ?, ?, ?, ?, ?, ?, ? , ?, ?, ?, ?) ";
    $stmt = @PDO_Execute($query, $parm);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2] . $query;
    } else {
        $respond['status'] = 0;
        $respond['message'] = $_POST["word"];
        $respond['data'] = [
            "id"=>$parm[0],
            "guid"=>$parm[1],
			"word"=>$parm[2],
			"word_en"=>$parm[3],
			"meaning"=>$parm[4],
			"other_meaning"=>$parm[5],
			"tag"=>$parm[6],
			"channal"=>$parm[7],
			"language"=>$parm[8],
			"note"=>$parm[9],
			"owner"=>$parm[10]
			];

    }
}

	#更新 redis
	if ($redis != false) {
		{
			# code...
			$query = "SELECT id,word,meaning,other_meaning,note,owner,language from "._TABLE_TERM_." where word = ? ";
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
