<?php 
//统计用户经验值
require_once '../path.php';
require_once "../public/function.php";

define("MAX_INTERVAL",600000);
define("MIN_INTERVAL",10000);

if(isset($_COOKIE["userid"])){
	$dns = "sqlite:"._FILE_DB_USER_ACTIVE_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
	// 查询上次编辑活跃结束时间
	$query = "SELECT id, end, start,hit  FROM edit WHERE user_id = ? order by end DESC";
	$stmt = $dbh->prepare($query);
	$stmt->execute(array($_COOKIE["userid"]));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$new_record = false;
	$currTime = mTime();
    if ($row) {
		//找到，判断是否超时，超时新建，未超时修改
		$endtime = (int)$row["end"];
		$id = (int)$row["id"];
		$start_time = (int)$row["start"];
		$hit = (int)$row["hit"];
		if($currTime-$endtime>MAX_INTERVAL){
			//超时新建
			$new_record = true;
		}
		else{
			//未超时修改
			$new_record = false;
		}
    } else {
		//没找到，新建
        $new_record = true;
	}

	if($new_record){
		$query="INSERT INTO edit ( user_id, start , end  , duration , hit )  VALUES  ( ? , ? , ? , ? , ? ) ";
		$sth = $dbh->prepare($query);
		$sth->execute(array($_COOKIE["userid"] , $currTime , ($currTime+MIN_INTERVAL) , MIN_INTERVAL,1) );
		if (!$sth || ($sth && $sth->errorCode() != 0)) {
			$error = $dbh->errorInfo();
		}
	}
	else{

		$query="UPDATE edit SET end = ? , duration = ? , hit = ? WHERE id = ? ";
		$sth = $dbh->prepare($query);
		$sth->execute( array($currTime,($currTime-$start_time), ($hit+1),$id));
		if (!$sth || ($sth && $sth->errorCode() != 0)) {
			$error = $dbh->errorInfo();
		}
	}
}

?>