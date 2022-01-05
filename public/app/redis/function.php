<?php
require_once __DIR__."/../config.php";
function redis_connect()
{
	try {
		//code...
		$redis = new redis();
		$r_conn = $redis->connect('127.0.0.1', 6379);
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
