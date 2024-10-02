<?php
//统计用户经验值
require_once __DIR__."/../config.php";
require_once __DIR__."/../public/function.php";
require_once __DIR__."/../public/php/define.php";
require_once __DIR__."/../public/snowflakeid.php";

function add_edit_event($type = 0, $data = null)
{
    $snowflake = new SnowFlakeId();

	$active_type[10] = "channel_update";
$active_type[11] = "channel_create";
$active_type[20] = "article_update";
$active_type[21] = "article_create";
$active_type[30] = "dict_lookup";
$active_type[40] = "term_update";
$active_type[42] = "term_create";
$active_type[41] = "term_lookup";
$active_type[60] = "wbw_update";
$active_type[61] = "wbw_create";
$active_type[70] = "sent_update";
$active_type[71] = "sent_create";
$active_type[80] = "collection_update";
$active_type[81] = "collection_create";
$active_type[90] = "nissaya_open";

    date_default_timezone_set("UTC");
    define("MAX_INTERVAL", 600000);
    define("MIN_INTERVAL", 60000);

    if (isset($_COOKIE["user_id"])) {
        $dns = _FILE_DB_USER_ACTIVE_;
        $dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        // 查询上次编辑活跃结束时间
        $query = "SELECT id, op_start,op_end, hit  FROM "._TABLE_USER_OPERATION_FRAME_." WHERE user_id = ? order by op_end DESC";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($_COOKIE["user_id"]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_record = false;
        $currTime = mTime();
        if ($row) {
            //找到，判断是否超时，超时新建，未超时修改
            $id = (int) $row["id"];
            $start_time = (int) $row["op_start"];
            $endtime = (int) $row["op_end"];
            $hit = (int) $row["hit"];
            if ($currTime - $endtime > MAX_INTERVAL) {
                //超时新建
                $new_record = true;
            } else {
                //未超时修改
                $new_record = false;
            }
        } else {
            //没找到，新建
            $new_record = true;
        }

        #获取客户端时区偏移 beijing = +8
        if (isset($_COOKIE["timezone"])) {
            $client_timezone = (0 - (int) $_COOKIE["timezone"]) * 60 * 1000;
        } else {
            $client_timezone = 0;
        }

        $this_active_time = 0; //时间增量
        if ($new_record) {
            #新建
            $query = "INSERT INTO "._TABLE_USER_OPERATION_FRAME_." (id, user_id, op_start , op_end  , duration , hit , timezone,created_at )  VALUES  (?, ? , ? , ? , ? , ? ,?,to_timestamp(?)) ";
            $sth = $dbh->prepare($query);
            #最小思考时间
            $sth->execute(array($snowflake->id(),$_COOKIE["user_id"], ($currTime - MIN_INTERVAL), $currTime, MIN_INTERVAL, 1, $client_timezone,($currTime - MIN_INTERVAL)/1000));
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = $dbh->errorInfo();
            }
            $this_active_time = MIN_INTERVAL;
        } else {
            #修改
            $this_active_time = $currTime - $endtime;
            $query = "UPDATE "._TABLE_USER_OPERATION_FRAME_." SET op_end = ? , duration = ? , hit = ? , updated_at=now() WHERE id = ? ";
            $sth = $dbh->prepare($query);
            $sth->execute(array($currTime, ($currTime - $start_time), ($hit + 1), $id));
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = $dbh->errorInfo();
            }
        }

        #更新经验总量表
        #计算客户端日期 unix时间戳 以毫秒计
        $client_currtime = $currTime + $client_timezone;
        $client_date = strtotime(gmdate("Y-m-d", $client_currtime / 1000)) * 1000;

        #查询是否存在
        $query = "SELECT id,duration,hit  FROM "._TABLE_USER_OPERATION_DAILY_." WHERE user_id = ? and date_int = ?";
        $sth = $dbh->prepare($query);
        $sth->execute(array($_COOKIE["user_id"], $client_date));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            #更新
            $id = (int) $row["id"];
            $duration = (int) $row["duration"];
            $hit = (int) $row["hit"];
            #修改
            $query = "UPDATE "._TABLE_USER_OPERATION_DAILY_." SET duration = ? , hit = ? , updated_at = now() WHERE id = ? ";
            $sth = $dbh->prepare($query);
            $sth->execute(array(($duration + $this_active_time), ($hit + 1), $id));
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = $dbh->errorInfo();
            }
        } else {
            #新建
            $query = "INSERT INTO "._TABLE_USER_OPERATION_DAILY_." (id, user_id, date_int , duration , hit )  VALUES  ( ? , ? , ? , ? , ?  ) ";
            $sth = $dbh->prepare($query);
            #最小思考时间
            $sth->execute(array($snowflake->id(),$_COOKIE["user_id"], $client_date,  MIN_INTERVAL, 1));
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = $dbh->errorInfo();
            }
        }
        #更新经验总量表结束

        #更新log
        if ($type > 0) {
            $dns = _FILE_DB_USER_ACTIVE_LOG_;
            $dbh_log = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
            $dbh_log->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

            $query = "INSERT INTO "._TABLE_USER_OPERATION_LOG_." (id, user_id, op_type_id ,op_type , content , create_time , timezone )  VALUES  (?, ? , ? , ? , ? , ? ,? ) ";
            $sth = $dbh_log->prepare($query);
            $sth->execute(array($snowflake->id(),$_COOKIE["user_id"], $type,$active_type[$type], $data, $currTime, $client_timezone));
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = $dbh->errorInfo();
            }
        }

        #更新log结束
    }
}
