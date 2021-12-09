<?php
require_once '../config.php';
//打开数据库
$dns = "" . _FILE_DB_MEDIA_;
$dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if (isset($_GET["id"])) {
    $query = "SELECT * FROM media WHERE id = ? limit 0 , 1";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_GET["id"]));
    $fMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null;
    if (count($fMedia) > 0) {
        $imgLink = $fMedia[0]["link"];
        if (substr($imgLink, 0, 6) == "media:") {
            echo _DIR_USER_IMG_ . '/' . substr($imgLink, 6);
        } else {
            echo $imgLink;
        }
    } else {
        echo "";
    }
} else {
    $query = "SELECT id , link  FROM media WHERE type = '3' order by create_time DESC limit 0 , 10 ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array());
    $fMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null;
    if (count($fMedia) > 0) {

        $imgLink = $fMedia[0]["link"];
        if (substr($imgLink, 0, 6) == "media:") {
            echo _DIR_USER_IMG_ . '/' . substr($imgLink, 6);
        } else {
            echo $imgLink;
        }
    } else {
        echo "";
    }
}
