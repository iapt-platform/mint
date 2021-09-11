<?php
//显示log
require_once '../path.php';

if (isset($_COOKIE["uid"]) && isset($_GET["start"]) && isset($_GET["end"])) {

    $dns = "" . _FILE_DB_USER_ACTIVE_LOG_;
    $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT time , active,content,timezone  FROM log WHERE user_id = ? AND (time BETWEEN ? AND ?) ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_COOKIE["uid"], $_GET["start"], $_GET["end"]));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($row, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}
