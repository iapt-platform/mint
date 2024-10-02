<?php
/*
迁移  sentence pr 库
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
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

$fpError = fopen(__DIR__.'/log/'.basename($_SERVER['PHP_SELF'],'.php').".err.data.csv",'w');



$active_type[10] = "channel_update";
$active_type[11] = "channel_create";
$active_type[20] = "article_update";
$active_type[21] = "article_create";
$active_type[30] = "dict_lookup";
$active_type[40] = "term_update";
$active_type[42] = "term_create";
$active_type[41] = "term_lookup";
$active_type[60] = "wbw_update";
$active_type[61] = "wbw_create";
$active_type[70] = "sent_update";
$active_type[71] = "sent_create";
$active_type[80] = "collection_update";
$active_type[81] = "collection_create";
$active_type[90] = "nissaya_open";
#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名
# 更新索引表
$src_db=_SQLITE_DB_USER_ACTIVE_LOG_;#源数据库
$src_table=_SQLITE_TABLE_USER_OPERATION_LOG_;#源表名

$dest_db=_PG_DB_USER_ACTIVE_LOG_;#目标数据库
$dest_table=_PG_TABLE_USER_OPERATION_LOG_;#目标表名

fwrite(STDOUT,"migarate user opration log".PHP_EOL);
#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open user table".PHP_EOL);

#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest table".PHP_EOL);

#删除源数据表中全部数据
fwrite(STDOUT,"delete dest".PHP_EOL);

$query = "delete from $dest_table";
$stmtDest = $PDO_DEST->prepare($query);
$stmtDest->execute();

$queryInsert = "INSERT INTO ".$dest_table." 
								(
                                    id,
									user_id,
									op_type_id,
									op_type,
									content,
									timezone,
									create_time,
									created_at) 
									VALUES ( ? , ? , ? , ? , ? ,? , ? , to_timestamp(?))";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从user数据表中读取
$query = "SELECT id  FROM ".$user_table." WHERE userid = ? ";
$stmtUser = $PDO_USER->prepare($query);

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	#插入目标表
	$commitData = array(
            $snowflake->id(),
			$srcData["user_id"],
			$srcData["active"],
			$active_type[$srcData["active"]],
			$srcData["content"],
			$srcData["timezone"],
			$srcData["time"],
			$srcData["time"]/1000
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
fwrite(STDOUT, "all done in ".(time()-$start)."s".PHP_EOL);





