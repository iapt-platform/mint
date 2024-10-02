<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function myLog()
{

    $log = new Logger('tulip');
    if(isset($GLOBALS['debug']) && $GLOBALS['debug']===true){
        $level = Logger::DEBUG;
    }else{
        $level = Logger::INFO;
    }
    $log->pushHandler(new StreamHandler('php://stdout', $level));
    return $log;
}
