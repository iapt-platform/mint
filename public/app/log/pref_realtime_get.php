<?php
require_once("../config.php");
require_once("../redis/function.php");

echo "Time, Value".PHP_EOL;
if(isset($_GET["item"])){
    $item = $_GET["item"];
}else{
    return 1;
}
if(isset($_GET["api"])){
    $api = $_GET["api"];
}else{
    $api = "all";
}
    $key= "pref/";
    $redis = redis_connect();
    $currMinute = intval(time()/60);
    if($redis){
        $begin = $currMinute - 60;
        for ($i=$begin; $i < $currMinute; $i++) { 
            $keyAll = $key.$api."/".$i;
            if($redis->exists($keyAll)){
                if($item == 'average'){
                    $value = $redis->hGet($keyAll,'delay') / $redis->hGet($keyAll,'count');
                }else{
                    $value = $redis->hGet($keyAll,$item);
                }
            }else{
                $value = 0;
            }
            $time = date("Y-m-d\TH:i:s.u\Z",$i*60);
            echo "{$time},{$value}".PHP_EOL;
        }
    }else{
        echo "Time, Value";
    }