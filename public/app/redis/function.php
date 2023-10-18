<?php
require_once __DIR__."/../config.php";
function redis_connect()
{
	try {
        $server = $_SERVER['SERVER_NAME'];
        if(strpos($server,'wikipali') === false){
            //local dev
            $redis = new redis();
            $r_conn = $redis->connect(Redis["host"], Redis["port"]);
        }else{
            $redis = new RedisCluster(NULL,Array(Redis["host"].':'.Redis["port"]));
            $r_conn = true;
        }
		if ($r_conn) {
			if(Redis["password"] !== ""){
				$redis->auth(Redis["password"]);
			}
			return $redis;
		} else {
			return false;
		}
	} catch (\Throwable $th) {
		//throw $th;
		return false;
	}

}
