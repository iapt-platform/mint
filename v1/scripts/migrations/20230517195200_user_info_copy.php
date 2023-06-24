<?php
/*
迁移  user_info 表
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
require_once __DIR__."/../../../public/app/config.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});
$start = time();

$fpError = fopen(__DIR__.'/log/'.basename($_SERVER['PHP_SELF'],'.php').".err.data.csv",'w');

#
$src_db = _SQLITE_DB_USERINFO_;#源数据库
$src_table = _SQLITE_TABLE_USER_INFO_;#源表名

$dest_db = _PG_DB_USERINFO_;#目标数据库
$dest_table = _PG_TABLE_USER_INFO_;#目标表名

fwrite(STDOUT,"migarate user info".PHP_EOL);

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
                                    userid,
                                    path,
                                    username,
									password,
									nickname,
									email,
									create_time,
									modify_time,
									receive_time,
									setting,
									reset_password_token,
									reset_password_sent_at,
									confirmation_token,
									confirmed_at,
									confirmation_sent_at,
									unconfirmed_email,
									created_at,
									updated_at
                                    )
									VALUES (
                                        ? , ? , ? , ? , ?,
                                        ? , ? , ? , ? , ?,
                                        ? , ? , ? , ? , ?,
                                        ? , ? , ? , ?
                                        )";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

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

	//查询是否已经插入
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE id = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($srcData["id"]));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if($exist){
		continue;
	}
    $created_at = date("Y-m-d H:i:s.",$srcData["create_time"]/1000).($srcData["create_time"]%1000)." UTC";
	$updated_at = date("Y-m-d H:i:s.",$srcData["modify_time"]/1000).($srcData["modify_time"]%1000)." UTC";
    if($srcData["email"]==="test@test.com"){
        $email = $srcData["username"].$srcData["email"];
        echo "Email Exist ".$srcData["username"].PHP_EOL;
    }else if($srcData["email"]===""){
        $email = $srcData["username"]."@email.com";
        echo "Email empty ".$srcData["username"].PHP_EOL;
    }else{
        $email = $srcData["email"];
    }
	#插入目标表
	$commitData = array(
			$srcData["id"],
			$srcData["userid"],
			$srcData["path"],
			$srcData["username"],
			$srcData["password"],
			$srcData["nickname"],
			$email,
			$srcData["create_time"],
			$srcData["modify_time"],
			$srcData["receive_time"],
			$srcData["setting"],
			$srcData["reset_password_token"],
			$srcData["reset_password_sent_at"],
			$srcData["confirmation_token"],
			$srcData["confirmed_at"],
			$srcData["confirmation_sent_at"],
			$srcData["unconfirmed_email"],
			$created_at,
			$updated_at
		);
	$stmtDEST->execute($commitData);
	$count++;
	$allInsertCount++;

    echo "finished {$count} - {$srcData["username"]}".PHP_EOL;

}

$stmtDEST = $PDO_DEST->prepare("alter sequence user_infos_id_seq restart with 400");
$stmtDEST->execute();

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL) ;
fwrite(STDOUT, "all done in ".(time()-$start)."s".PHP_EOL);

fclose($fpError);




