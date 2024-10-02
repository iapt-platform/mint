<?php
require_once '../config.php';
function media_get($idlist)
{
    //打开数据库
    $dns = "" . _FILE_DB_MEDIA_;
    $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    /*  使用一个数组的值执行一条含有 IN 子句的预处理语句 */
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($idlist), '?'));

    $query = "SELECT * FROM media WHERE id IN ($place_holders) ";
    $stmt = $dbh->prepare($query);
    $stmt->execute($idlist);
    $fMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null;
    return ($fMedia);

}
