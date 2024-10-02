<?php
require_once __DIR__."/../../../public/app/config.php";
set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});
$dest_db = _PG_DB_PALI_SENTENCE_;#目标数据库
$dest_table = _PG_TABLE_PALI_SENT_INDEX_;#目标表名

fwrite(STDOUT, "migarate pali_sent_index".PHP_EOL);


#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open dest".PHP_EOL);

#删除目标表中所有数据
fwrite(STDOUT,"deleting date".PHP_EOL) ;
try{
	$query = "DELETE FROM ".$dest_table;
	$stmt = $PDO_DEST->prepare($query);
	$stmt->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
fwrite(STDOUT,"deleted date".PHP_EOL) ;

#插入数据
$query = "INSERT INTO ".$dest_table." (book, para, strlen ) SELECT book,paragraph,sum(length) FROM "._PG_TABLE_PALI_SENT_." group by book,paragraph;";
try{
	$stmt = $PDO_DEST->prepare($query);
	$stmt->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
fwrite(STDOUT, "insert dest".PHP_EOL);

fwrite(STDOUT, "done".PHP_EOL);






