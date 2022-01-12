<?php
require_once __DIR__."/../config.php";
function redis_connect()
{
	try {
		//code...
		$redis = new redis();
		$r_conn = $redis->connect(Redis["host"], Redis["port"]);
		$redis->auth(Redis["password"]);
		if ($r_conn) {
			return $redis;
		} else {
			return false;
		}		
	} catch (\Throwable $th) {
		//throw $th;
		return false;
	}

}
