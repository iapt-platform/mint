<?php 
require_once "../config.php";
require_once "../redis/function.php";
$redis = redis_connect();
if($redis==false){
	echo "redis 连接失败";
}
else{
	$keys = $redis->keys($_GET["key"]);
		$count=0;
		foreach ($keys as $key => $value) {
			# code...
			$deleted = $redis->del($value);
			$count += $deleted;
		}
		
		echo "delete ok ".$count;
}
?>