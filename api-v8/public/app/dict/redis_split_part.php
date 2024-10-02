<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if($redis!==false){
		// 打开文件并读取数据
		if (($fp = fopen(_DIR_DICT_TEXT_ . "/system/part.csv", "r")) !== false) {
            while (($data = fgets($fp)) !== false) {
				$word = explode(",",$data);
				$len = mb_strlen($word[0], "UTF-8");
				$len_correct = 1.2;
				$count2 = 1.1 + pow($word[1], 1.18);
				$conf_num = pow(1 / $count2, pow(($len - 0.5), $len_correct));
				$cf = round(1 / (1 + 640 * $conf_num), 9);
				$redis->hSet("dict://part.hash",$word[0],$cf);
            }
			fclose($fp);
			echo "do:".$redis->hLen("dict://part.hash"). PHP_EOL;
        } else {
            echo "can not open csv file. ". PHP_EOL;
        }
	}
}