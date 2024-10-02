<?php
//统计用户经验值
require_once '../config.php';
require_once "../public/function.php";

$output = array();
if (isset($_GET["userid"])) {
    $userid = $_GET["userid"];
} else if (isset($_COOKIE["user_id"])) {
    $userid = $_COOKIE["user_id"];
} else {
    exit;
}


    $dns = _FILE_DB_USER_ACTIVE_;
    $dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT date_int,duration,hit  FROM "._TABLE_USER_OPERATION_DAILY_." WHERE user_id = ? order by date_int asc";
    $sth = $dbh->prepare($query);
    $sth->execute(array($userid));
    $last = 0;
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        $curr = $last + $row["duration"] / 3600000;
        $output[] = array($row["date_int"], number_format($last, 3, ".", ""), number_format($curr, 3, ".", ""), number_format($last, 3, ".", ""), number_format($curr, 3, ".", ""), $row["hit"]);
        $last = $curr;
    }

    $json = json_encode($output);
    echo str_replace('"', '', $json);

