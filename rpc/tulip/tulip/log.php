<?php
require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function myLog()
{
    $dir = __DIR__ . '/tmp/logs';
    if (!is_dir($dir)) {
        $res = mkdir($dir, 0700, true);
        if (!$res) {
            echo "error: mkdir fail path=" . $dir;
            return 0;
        }
    }

    $log = new Logger('tulip');
    $log->pushHandler(new StreamHandler($dir . '/tulip-' . date("Y-m-d") . '.log'));
    return $log;
}
