<?php
/*
迁移  article 库
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



# 
$src_db = _SQLITE_DB_USER_ARTICLE_;#源数据库
$src_table = _SQLITE_TABLE_ARTICLE_COLLECTION_;#源表名

$dest_db = _PG_DB_USER_ARTICLE_;#目标数据库
$dest_table = _PG_TABLE_ARTICLE_COLLECTION_;#目标表名

fwrite(STDOUT,"migarate _TABLE_ARTICLE_COLLECTION_".PHP_EOL);


#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest table".PHP_EOL);

#删除目标数据表中全部数据
fwrite(STDOUT,"delete dest".PHP_EOL);

$query = "delete from $dest_table where true;";
$stmtDest = $PDO_DEST->prepare($query);
$stmtDest->execute();

$queryInsert = "INSERT INTO ".$dest_table." 
								(
                                    id,
									collect_id,
									article_id,
									level,
									title,
									children,
									updated_at,
									created_at) 
									VALUES ( ? , ? , ? , ?, ? ,? , now(),now())";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;



#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." order by id ASC";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;

	#插入目标表
	$commitData = array(
            $snowflake->id(),
			$srcData["collect_id"],
			$srcData["article_id"],
			$srcData["level"],
			$srcData["title"],
			$srcData["children"]
		);
	$stmtDEST->execute($commitData);

	$count++;	
	$allInsertCount++;


	if($count ==10000){
		#10000行输出log 一次
		echo "finished $count".PHP_EOL;
		$count=0;
	}	
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL) ;
fwrite(STDOUT, "all done in ".(time()-$start)."s".PHP_EOL);

fclose($fpError);





