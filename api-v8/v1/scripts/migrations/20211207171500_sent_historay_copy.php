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

$fpError = fopen(__DIR__.'/log/'.basename($_SERVER['PHP_SELF'],'.php').".err.data.csv",'w');

$start = time();
# 雪花id
$snowflake = new SnowFlakeId();

# 更新索引表
$src_db=_SQLITE_DB_USER_SENTENCE_HISTORAY_;#源数据库
$src_table=_SQLITE_TABLE_SENTENCE_HISTORAY_;#源表名
$dest_db=_PG_DB_USER_SENTENCE_HISTORAY_;#目标数据库
$dest_table=_PG_TABLE_SENTENCE_HISTORAY_;#目标表名

fwrite(STDOUT,"migarate sent_historay".PHP_EOL);
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								(
                                    id,
									sent_uid,
									user_uid,
									content,
									landmark,
									create_time,
									created_at) 
									VALUES ( ? , ? , ? , ? , ? , ? , to_timestamp(?))";


$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	#插入目标表

	
    #查询目标表中是否有相同数据
    $queryExsit = "SELECT id  FROM ".$dest_table." WHERE sent_uid = ? and user_uid = ? and content=? and create_time=? ";
    $getExist = $PDO_DEST->prepare($queryExsit);
    $getExist->execute(array($srcData["sent_id"],$srcData["user_id"],$srcData["text"],$srcData["date"]));
    $exist = $getExist->fetch(PDO::FETCH_ASSOC);
    if(!$exist){
        #没有相同数据

        $commitData[] = array(
                $snowflake->id(),
                $srcData["sent_id"],
                $srcData["user_id"],
                $srcData["text"],
                $srcData["landmark"],
                $srcData["date"],
                $srcData["date"]/1000
            );	
        $count++;	
        $allInsertCount++;
    }

    if($count ==10000){
        #10000行插入一次
        // 开始一个事务，关闭自动提交
        $PDO_DEST->beginTransaction();
        $stmtDEST = $PDO_DEST->prepare($queryInsert);
        foreach ($commitData as $key => $value) {
            $stmtDEST->execute($value);

        }
        // 提交更改
        $PDO_DEST->commit();
        $commitData = [];
        echo "finished $count".PHP_EOL;
        $count=0;
    }	

    if($allSrcCount % 10000 ==0){
		echo "find from src table $allSrcCount / $allInsertCount is new.".PHP_EOL;
	}
	
}
if($count>0){
	#最后的没有到10000的数据插入
	$PDO_DEST->beginTransaction();
	$stmtDEST = $PDO_DEST->prepare($queryInsert);
	foreach ($commitData as $key => $value) {
		$stmtDEST->execute($value);
	}
	// 提交更改
	$PDO_DEST->commit();
	$commitData = [];
	echo "finished $count".PHP_EOL;
}

echo "insert done $allInsertCount in $allSrcCount ".PHP_EOL;

fwrite(STDOUT, "all done in ".(time()-$start)."s".PHP_EOL);






