<?php
/*
迁移  article 库
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
require_once __DIR__."/../../app/config.php";

#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

# 
$src_db = _FILE_SRC_USER_ARTICLE_;#源数据库
$src_table = _TABLE_SRC_ARTICLE_;#源表名

$dest_db = _FILE_DB_USER_ARTICLE_;#目标数据库
$dest_table = _TABLE_ARTICLE_;#目标表名

fwrite(STDOUT,"migarate article".PHP_EOL);
#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open user table".PHP_EOL);

#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								(
									uid,
									title,
									subtitle,
									summary,
									content,
									owner,
									owner_id,
									editor_id,
									setting,
									status,
									lang,
									create_time,
									modify_time,
									updated_at,
									created_at) 
									VALUES ( ? , ? , ?, ? , ? ,? , ? , ? , ? , ? , ? , ?,? ,?,?)";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从user数据表中读取
$query = "SELECT id  FROM ".$user_table." WHERE userid = ? ";
$stmtUser = $PDO_USER->prepare($query);

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	$stmtUser->execute(array($srcData["owner"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,time()."error,no user id {$srcData["owner"]}".PHP_EOL);
		continue;
	}
	if(strlen($srcData["owner"])>36){
		fwrite(STDERR,time().",error,user id too long {$srcData["owner"]}".PHP_EOL);
		continue;	
	}
	//查询是否已经插入
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($srcData["id"]));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if($exist){
		continue;
	}
	#插入目标表
	$created_at = date("Y-m-d H:i:s.",$srcData["create_time"]/1000).($srcData["create_time"]%1000)." UTC";
	$updated_at = date("Y-m-d H:i:s.",$srcData["modify_time"]/1000).($srcData["modify_time"]%1000)." UTC";
	$commitData = array(
			$srcData["id"],
			$srcData["title"],
			$srcData["subtitle"],
			$srcData["summary"],
			$srcData["content"],
			$srcData["owner"],
			$userId["id"],
			$userId["id"],
			$srcData["setting"],
			$srcData["status"],
			$srcData["lang"],
			$srcData["create_time"],
			$srcData["modify_time"],
			$created_at,
			$updated_at
		);
	$stmtDEST->execute($commitData);
	if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
		$error = $PDO_DEST->errorInfo();
		echo "error - $error[2] ";
		exit;
	}
	$count++;	
	$allInsertCount++;


	if($count ==10000){
		#10000行输出log 一次
		echo "finished $count".PHP_EOL;
		$count=0;
	}	
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL) ;
fwrite(STDOUT,"all done".PHP_EOL);





