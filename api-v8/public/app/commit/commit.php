<?php 
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../channal/function.php";
require_once "../redis/function.php";
require_once "../share/function.php";
require_once "../usent/function.php";

$respond['message'] = "";
$respond['status'] = 0;

$_data = array();
if (isset($_POST["data"])) {
    $_data = json_decode($_POST["data"], true);
} else {
	$respond['message'] = "缺少输入数据";
	$respond['status'] = 1;
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}
$channelInfo  = new Channal();
$srcChannelPower = $channelInfo->getPower($_data["src"]);
$destChannelPower = $channelInfo->getPower($_data["dest"]);


if($srcChannelPower<10){
	$respond['message'] = "源channel无权限";
	$respond['status'] = 1;
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;		
}
if($destChannelPower<10){
	$respond['message'] = "channel无权限";
	$respond['status'] = 1;
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;		
}

$db_trans_sent = new PDO(_FILE_DB_SENTENCE_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$db_trans_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT uid as id,
parent_uid as parent,
block_uid as block_id,
channel_uid as channal,
book_id as book,
paragraph,
word_start as begin,
word_end as end,
author,
editor_uid as editor,
content as text,
language,
version as ver,
status,
strlen,
modify_time,
create_time FROM "._TABLE_SENTENCE_." WHERE book_id= ? AND paragraph= ? AND word_start= ? AND word_end= ?  AND channel_uid = ?  ";
$stmt = $db_trans_sent->prepare($query);

if($stmt){
	$updateDate=array();
	$insertData=array();
	$prData=array();
	$insertHistoray=array();
	foreach ($_data["sent"] as $key => $value) {
		# code...
		$infoSrc = explode("-",$value);
		$infoDest = $infoSrc;
		$infoSrc[]=$_data["src"];
		$infoDest[]=$_data["dest"];
		$stmt->execute($infoSrc);
		$fetchSrc = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($fetchSrc) {
			# 有 源数据
			$newData = $fetchSrc;
			$newData["modify_time"]=mTime();
			$newData["channal"]=$_data["dest"];
			$newData["landmark"]="";
			$stmt->execute($infoDest);
			$fetchDest = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($fetchDest) {
				#有目标数据，比较时间
				$insert=false;
				if(isset($_data["compare"]) && $_data["compare"]=="auto"){
					if($fetchSrc["modify_time"]>$fetchDest["modify_time"]){
						$insert = true;
					}					
				}
				else{
					$insert = true;
				}
				if($insert){
					#新数据 更新
					if($destChannelPower>=20){
						#有权限 直接写入
						$newData["id"]=$fetchDest["id"];
						$updateDate[] = $newData;
						$insertHistoray[] = $newData;
					}
					else{
						#pr
						$prData[] = $newData;
					}					
				}
			}
			else{
				#没有目标数据新增
				if($destChannelPower>=20){
					#有写入权限 直接写入
					$newData["id"] = UUID::v4();
					$insertData[] = $newData;
					$insertHistoray[] = $newData;
				}
				else{
					#pr
					$prData[] = $newData;
				}
			}
		}
	}
	#到此，所有的数据已经准备好

	$sentDb = new Sent_DB();

	if($sentDb->update($updateDate)){
		$respond['update'] = count($updateDate);
	}
	else{
		$respond['message'] = $sentDb->getError();
        $respond['status'] = 1;
	}
	if($sentDb->insert($insertData)){
		$respond['insert'] = count($insertData);

	}else{
		$respond['message'] = $sentDb->getError();
        $respond['status'] = 1;
	}
	if($sentDb->send_pr($prData)){
		$respond['pr'] = count($prData);

	}else{
		$respond['message'] = $sentDb->getError();
        $respond['status'] = 1;
	}
	if($sentDb->historay($insertHistoray)){
		$respond['historay'] = count($insertHistoray);

	}else{
		$respond['message'] = $sentDb->getError();
        $respond['status'] = 1;
	}
}
else{
	$respond['message'] = $db_trans_sent->errorInfo();
	$respond['status'] = 1;
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>