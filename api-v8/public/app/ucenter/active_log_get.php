<?php
//显示log
require_once '../config.php';

if (isset($_COOKIE["user_id"]) && isset($_GET["start"]) && isset($_GET["end"])) {
    $dns = _FILE_DB_USER_ACTIVE_LOG_;
    $dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT create_time , op_type_id,content,timezone  FROM "._TABLE_USER_OPERATION_LOG_." WHERE user_id = ? AND (create_time BETWEEN ? AND ?) ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_COOKIE["user_id"], $_GET["start"], $_GET["end"]));
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($row, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}
