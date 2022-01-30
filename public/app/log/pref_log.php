<?php
require_once("../config.php");
require_once("../redis/function.php");


$logstart = microtime(true)*1000;
$iTime = time();
$strstart = date("h:i:sa");
function PrefLog(){
    $delay = microtime(true)*1000-$GLOBALS['logstart'];
    $redis = redis_connect();
    $timeMinute = intval(time()/60);
    $timeSecond = time();
    if($redis){
        $key= "pref/";
        $keyAll = $key."all/".$timeMinute;
        UpdateCache($redis,$keyAll,$delay);
        $keyApi = $key.$_SERVER['PHP_SELF']."/".$timeMinute;
        UpdateCache($redis,$keyApi,$delay);

        $key= "pref-s/";
        $keyAll = $key."all/".$timeSecond;
        UpdateCache($redis,$keyAll,$delay,30);
        $keyApi = $key.$_SERVER['PHP_SELF']."/".$timeSecond;
        UpdateCache($redis,$keyApi,$delay,30);

        $keyApiName = "pref-hour/api/".$_SERVER['PHP_SELF'];
        $redis->set($keyApiName,1);
        $redis->expire($keyApiName,3600);
    }
	$file = fopen(_DIR_LOG_."/pref_".date("Y-m-d").".log","a");
	if($file){
		fputcsv($file,[$_SERVER['PHP_SELF'],$GLOBALS['strstart'],sprintf("%d",microtime(true)*1000-$GLOBALS['logstart']),$_SERVER['REMOTE_ADDR']]);
		fclose($file);
	}
}

function UpdateCache($redis,$key,$delay,$expire=3600){

        if($redis->exists($key)){
            $redis->hSet($key,"count",$redis->hGet($key,"count")+1);
            $redis->hSet($key,"delay",$redis->hGet($key,"delay")+$delay);
        }else{
            #没有，创建
            $redis->hSet($key,"count",1);
            $redis->hSet($key,"delay",$delay);
            $redis->expire($key,$expire);
        }
}

?>