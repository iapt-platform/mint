<?php
require_once "../../app/path.php";


$dest_db=_FILE_DB_PALI_SENTENCE_;#目标数据库
$dest_table=_TABLE_PALI_SENT_INDEX_;#目标表名

echo "migarate pali_sent_index".PHP_EOL;


#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open dest".PHP_EOL;

#删除目标表中所有数据
$query = "DELETE FROM ".$dest_table." WHERE true";
$stmt = $PDO_DEST->prepare($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $PDO_DEST->errorInfo();
    echo "error - $error[2] ";
	exit;
}
$stmt->execute();
echo "delete dest".PHP_EOL;

#插入数据
$query = "INSERT INTO ".$dest_table." (book, para, strlen ) SELECT book,paragraph,sum(length) FROM "._TABLE_PALI_SENT_." where true group by book,paragraph;";
$stmt = $PDO_DEST->prepare($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $PDO_DEST->errorInfo();
    echo "error - $error[2] ";
	exit;
}
$stmt->execute();
echo "insert dest".PHP_EOL;

echo "done".PHP_EOL;






