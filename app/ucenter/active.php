<?php 
//统计用户经验值
require_once '../path.php';
require_once "../public/function.php";
require_once "../public/php/define.php";


function add_edit_event($type=0,$data=null){
	date_default_timezone_set("UTC");
	define("MAX_INTERVAL",600000);
	define("MIN_INTERVAL",60000);
	
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
			$id = (int)$row["id"];
			$start_time = (int)$row["start"];
			$endtime = (int)$row["end"];
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
	
		#获取客户端时区偏移 beijing = +8
		if(isset($_COOKIE["timezone"])){
			$client_timezone = (0-(int)$_COOKIE["timezone"])*60*1000;
		}
		else{
			$client_timezone = 0;
		}
		
		$this_active_time=0;//时间增量
		if($new_record){
			#新建
			$query="INSERT INTO edit ( user_id, start , end  , duration , hit , timezone )  VALUES  ( ? , ? , ? , ? , ? ,?) ";
			$sth = $dbh->prepare($query);
			#最小思考时间
			$sth->execute(array($_COOKIE["userid"] , ($currTime-MIN_INTERVAL) ,$currTime ,  MIN_INTERVAL,1 ,$client_timezone ) );
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				$error = $dbh->errorInfo();
			}
			$this_active_time=MIN_INTERVAL;
		}
		else{
			#修改
			$this_active_time=$currTime-$endtime;
			$query="UPDATE edit SET end = ? , duration = ? , hit = ? WHERE id = ? ";
			$sth = $dbh->prepare($query);
			$sth->execute( array($currTime,($currTime-$start_time), ($hit+1),$id));
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				$error = $dbh->errorInfo();
			}
			
		}

		#更新经验总量表
		#计算客户端日期 unix时间戳 以毫秒计
		$client_currtime = $currTime + $client_timezone;
		$client_date = strtotime(gmdate("Y-m-d",$client_currtime/1000))*1000;
		
		#查询是否存在
		$query = "SELECT id,duration,hit  FROM active_index WHERE user_id = ? and date = ?";
		$sth = $dbh->prepare($query);
		$sth->execute(array($_COOKIE["userid"],$client_date));
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			#更新
			$id = (int)$row["id"];
			$duration = (int)$row["duration"];
			$hit = (int)$row["hit"];
			#修改
			$query="UPDATE active_index SET duration = ? , hit = ? WHERE id = ? ";
			$sth = $dbh->prepare($query);
			$sth->execute( array(($duration+$this_active_time), ($hit+1),$id));
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				$error = $dbh->errorInfo();
			}			
		}
		else{
			#新建
			$query="INSERT INTO active_index ( user_id, date , duration , hit )  VALUES  ( ? , ? , ? , ?  ) ";
			$sth = $dbh->prepare($query);
			#最小思考时间
			$sth->execute(array($_COOKIE["userid"] , $client_date ,  MIN_INTERVAL,1 ) );
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				$error = $dbh->errorInfo();
			}
		}
		#更新经验总量表结束

		#更新log
		if($type > 0){
			$dns = "sqlite:"._FILE_DB_USER_ACTIVE_LOG_;
			$dbh_log = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
			$dbh_log->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

			$query="INSERT INTO log ( user_id, active , content , time , timezone )  VALUES  ( ? , ? , ? , ? ,? ) ";
			$sth = $dbh_log->prepare($query);
			$sth->execute(array($_COOKIE["uid"] , $type , $data, $currTime,$client_timezone ) );
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				$error = $dbh->errorInfo();
			}			
		}

		#更新log结束
	}
}


?>