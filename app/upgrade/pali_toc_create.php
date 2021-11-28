<?php
#建立段落完成度数据库
require_once '../path.php';

$dns = _FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

//建立数据库
$_sql = file_get_contents("pali_toc.sql");
$_arr = explode(';', $_sql);
//执行sql语句
foreach ($_arr as $_value) {
    $dbh_toc->query($_value . ';');
}
echo "建立数据库成功";
