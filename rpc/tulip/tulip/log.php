<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function myLog(){
    $log = new Logger('tulip');
    $log->pushHandler(new StreamHandler(__DIR__ . '/logs/tulip-' . date("Y-m-d") . '.log'));
    return $log;
}
