<?php
/*
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
// Require Composer's autoloader.
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__."/../../app/config.php";
require_once __DIR__."/../../app/public/snowflakeid.php";

# 更新索引表
#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

$src_db=_SRC_DB_USER_WBW_;#源数据库
$src_table=_TABLE_SRC_USER_WBW_BLOCK_;#源表名

$dest_db=_FILE_DB_USER_WBW_;#目标数据库
$dest_table=_TABLE_USER_WBW_BLOCK_;#目标表名

# 雪花id
$snowflake = new SnowFlakeId();

fwrite(STDOUT, "migarate wbw_block".PHP_EOL);
#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open user table".PHP_EOL);
#从user数据表中读取
$query = "SELECT id  FROM ".$user_table." WHERE userid = ? ";
$stmtUser = $PDO_USER->prepare($query);


#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT, "open src".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT, "open dest".PHP_EOL);

// 开始一个事务，关闭自动提交

fwrite(STDOUT, "begin Transaction".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								   (
									id,
									uid, 
									parent_id , 
									channel_uid, 
									parent_channel_uid,
									creator_uid,
									editor_id,
									book_id,
									paragraph,
									style,
									lang,
									status,
									create_time,
									modify_time,
									created_at,
									updated_at
									) 
									VALUES ( ?,? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,?,to_timestamp(?),to_timestamp(?))";


$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	$stmtUser->execute(array($srcData["owner"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,"no user id {$srcData["owner"]}".PHP_EOL);
		continue;
	}

	#插入目标表
	if(empty($srcData["parent_id"])){
		$srcData["parent_id"] = NULL;
	}
	if(empty($srcData["channal"])){
		$srcData["channal"] = NULL;
	}
	if(empty($srcData["status"])){
		$srcData["status"] = 10;
	}
	if(substr($srcData["book"],0,1)==="p"){
		$srcData["book"] = (int)substr($srcData["book"],1);
	}
	
	if(strlen($srcData["id"])>10 && strlen($srcData["owner"])>30){
		$uuid = $srcData["id"];
		#查询目标表中是否有相同数据
		$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
		$getExist = $PDO_DEST->prepare($queryExsit);
		$getExist->execute(array($uuid));
		$exist = $getExist->fetch(PDO::FETCH_ASSOC);
		if(!$exist){
			#没有相同数据
			$commitData[] = array(
					$snowflake->id(),
					$uuid,
					trim($srcData["parent_id"],"{}"),
					trim($srcData["channal"],"{}"),
					trim($srcData["parent_channel"],"{}"),
					trim($srcData["owner"],"{}"),
					$userId["id"],
					(int)$srcData["book"],
					(int)$srcData["paragraph"],
					$srcData["style"],
					$srcData["lang"],
					(int)$srcData["status"],
					$srcData["create_time"],
					$srcData["modify_time"],
					$srcData["create_time"]/1000,
					$srcData["modify_time"]/1000
				);	
			$count++;	
			$allInsertCount++;
		}

		if($count ==10000){
			#10000行插入一次
			$PDO_DEST->beginTransaction();
			$stmtDEST = $PDO_DEST->prepare($queryInsert);
			foreach ($commitData as $key => $value) {
				$stmtDEST->execute($value);
				if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
					$error = $PDO_DEST->errorInfo();
					fwrite(STDERR, "error - $error[2] ");
					exit;
				}
			}
			// 提交更改
			$PDO_DEST->commit();
			$commitData = [];
			fwrite(STDOUT, "finished $count".PHP_EOL);
			$count=0;
		}	
	}
}
if($count>0){
	#最后的剩余的数据插入
	$PDO_DEST->beginTransaction();
	$stmtDEST = $PDO_DEST->prepare($queryInsert);
	foreach ($commitData as $key => $value) {
		$stmtDEST->execute($value);
		if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
			$error = $PDO_DEST->errorInfo();
			fwrite(STDERR, "error - $error[2] ");
			exit;
		}
	}
	// 提交更改
	$PDO_DEST->commit();
	$commitData = [];
	fwrite(STDOUT, "finished $count".PHP_EOL);
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL);



fwrite(STDOUT,"all done".PHP_EOL);






