<?php
#已经废弃
require_once '../config.php';

date_default_timezone_set("UTC");
$logfile = "update_user_active_time_last.txt";
$last = file_get_contents($logfile);
$start = strtotime($last . " +1 day");
$end = strtotime($last . " +2 day");
$today = strtotime("today");

$dbh = new PDO(_FILE_DB_USER_ACTIVE_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dbh_index = new PDO(_FILE_DB_USER_ACTIVE_INDEX_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/* 开始一个事务，关闭自动提交 */
$dbh_index->beginTransaction();
$query = "INSERT INTO "._TABLE_SRC_USER_OPERATION_DAILY_." (user_id, date_int , duration , hit) VALUES (?, ?, ? , ?)";
$sth_index = $dbh_index->prepare($query);

$runing = $last;
while ($end <= $today) {
    $runing = gmdate("Y-m-d", $start);
    echo "runing:" . $runing . "\n";

    # do one day
    $query = "SELECT user_id, sum(duration) as time , sum(hit) as hit  FROM "._TABLE_SRC_USER_OPERATION_FRAME_." WHERE end between ? and ?  group by user_id ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($start * 1000, $end * 1000));
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_id = $result["user_id"];
        $time = $result["time"];
        $hit = $result["hit"];
        $sth_index->execute(array($user_id, $start * 1000, $time, $hit));
        echo "$user_id - $time - $hit \n";
    }
    $start = $end;
    $end = strtotime(gmdate("Y-m-d", $end) . " +1 day");
}
$dbh_index->commit();
if (!$sth_index || ($sth_index && $sth_index->errorCode() != 0)) {
    /*  识别错误且回滚更改  */
    $sth_index->rollBack();
    $error = $dbh_index->errorInfo();
    echo "error:" . $error[2] . "\n";
}

echo "run until:" . $runing . "\n";
echo file_put_contents($logfile, $runing);
