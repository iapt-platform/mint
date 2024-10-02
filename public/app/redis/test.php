<?php
require_once "../config.php";
require_once "../redis/function.php";

if(php_sapi_name() !== "cli") {
    return;
}
$redis = redis_connect();
if($redis==false){
	echo "redis connect fail";
}
else{
    $redisInfo = $redis->info();
    print_r($redisInfo);
    $key = Redis["prefix"].'redis-test';
    $value = 'test content';
	$ok = $redis->set($key,$value);
    if($ok){
        echo "set ok value={$value}".PHP_EOL;
        $value2 = $redis->get($key);
        if($value === $value2){
            echo "get ok value={$value2}".PHP_EOL;
        }else{
            echo "get error value={$value2}";
        }
    }else{
        echo "set fail";
    }

}
