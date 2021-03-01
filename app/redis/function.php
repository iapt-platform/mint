<?php
function redis_connect()
{
    $redis = new redis();
    $r_conn = $redis->connect('127.0.0.1', 6379);
    if ($r_conn) {
        return $redis;
    } else {
        return false;
    }
}
