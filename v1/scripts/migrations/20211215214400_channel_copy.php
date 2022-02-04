<?php
/*
迁移  sentence pr 库
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
require_once __DIR__.'/../../../public/vendor/autoload.php';
require_once __DIR__."/../../../public/app/config.php";
require_once __DIR__."/../../../public/app/public/snowflakeid.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});
$start = time();
# 雪花id
$snowflake = new SnowFlakeId();

#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名


# 更新索引表
$src_db = _SQLITE_DB_CHANNAL_;#源数据库
$src_table = _SQLITE_TABLE_CHANNEL_;#源表名

$dest_db = _PG_DB_CHANNAL_;#目标数据库
$dest_table = _PG_TABLE_CHANNEL_;#目标表名

fwrite(STDOUT,"migarate channel".PHP_EOL);
#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open user table".PHP_EOL);

#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								(
									id,
									uid,
									owner_uid,
									editor_id,
									name,
									summary,
									status,
									lang,
									setting,
									create_time,
									modify_time,
									updated_at,
									created_at) 
									VALUES ( ? ,? , ? ,  ?, ? , ? ,? , ? , ? , ? , ? , to_timestamp(?), to_timestamp(?))";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从user数据表中读取
$query = "SELECT id ,userid FROM ".$user_table." WHERE userid = ? or username = ? ";
$stmtUser = $PDO_USER->prepare($query);

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;

	if($srcData["owner"]=='test6'){
		$srcData["owner"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["owner"]=='test28'){
		$srcData["owner"] = 'df0ad9bc-c0cd-4cd9-af05-e43d23ed57f0';
	}
	if($srcData["owner"]=='290fd808-2f46-4b8c-b300-0367badd67ed'){
		$srcData["owner"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["owner"]=='BA837178-9ABD-4DD4-96A0-D2C21B756DC4'){
		$srcData["owner"] = 'ba5463f3-72d1-4410-858e-eadd10884713';
	}
	$stmtUser->execute(array($srcData["owner"],$srcData["owner"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,"no user id {$srcData["owner"]}".PHP_EOL);
		continue;
	}

	if(strlen($srcData["owner"])>36){
		fwrite(STDERR,"user id too long {$srcData["owner"]}".PHP_EOL);
		continue;	
	}
	$uuid = $srcData["id"];
	#查询目标表中是否有相同数据
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($uuid));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if($exist){
		#有相同数据
		continue;
	}
		
	#插入目标表
	$commitData = array(
			$snowflake->id(),
			$srcData["id"],
			$userId["userid"],
			$userId["id"],
			$srcData["name"],
			$srcData["summary"],
			$srcData["status"],
			$srcData["lang"],
			'',
			$srcData["create_time"],
			$srcData["modify_time"],
			$srcData["create_time"]/1000,
			$srcData["modify_time"]/1000
		);	
	$stmtDEST->execute($commitData);
	$count++;	
	$allInsertCount++;


	if($count ==10000){
		#10000行插入一次
		echo "finished $count".PHP_EOL;
		$count=0;
	}	
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL) ;
fwrite(STDOUT,"all done".PHP_EOL);






