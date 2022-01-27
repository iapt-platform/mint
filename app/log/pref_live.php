<?php
require_once(__DIR__."/../config.php");
require_once(__DIR__."/../redis/function.php");


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
    $times = 10;
    $key= "pref-s/";
    $redis = redis_connect();
    $currTime = time();
    if($redis){
        $begin = $currTime - $times - 1;
        $value = 0;
        for ($i=$begin; $i <= $currTime; $i++) { 
            $keyAll = $key.$api."/".$i;
            if($redis->exists($keyAll)){
                if($item == 'average'){
                    $value += intval($redis->hGet($keyAll,'delay') / $redis->hGet($keyAll,'count'));
                }else{
                    $value += (int)$redis->hGet($keyAll,$item);
                }
            }
        }
        $value = $value/$times;
        echo $value;
    }else{
        echo 'redis error';
    }