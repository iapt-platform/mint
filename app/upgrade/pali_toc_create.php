<?php
#升级段落完成度数据库
require_once '../path.php';


$dns = "sqlite:"._FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

//建立数据库
$_sql = file_get_contents("pali_toc.sql");
$_arr = explode(';', $_sql);
//执行sql语句
foreach ($_arr as $_value) {
	$dbh_toc->query($_value.';');
}
echo $dns."建立数据库成功";

?>