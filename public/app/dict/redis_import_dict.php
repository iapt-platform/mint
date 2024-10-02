<?php
/*
将csv字典载入redis
#例:
# php redis_import_dict.php ../../dicttext/rich/rich.json
# php redis_import_dict.php ../../dicttext/system/system.json
*/
require_once __DIR__."/../config.php";
require_once __DIR__."/../install/filelist.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli") {
    if ($argc >= 2) {
		$list = $argv[1];
		if(isset($argv[2])){
			$tableNum = (int)$argv[2];
		}
		else{
			$tableNum = -1;
		}
        $redis = redis_connect();
        if ($redis == false) {
            fwrite(STDERR,"no redis connect\n") ;
            exit;
		}

		$taskList = json_decode(file_get_contents(__DIR__."/".$list));
		$dir = dirname(__DIR__."/".$list);
		if($tableNum<0){
			//全部都导入
			foreach ($taskList as $key => $task) {
				# code...
				runTask($redis,$task,$dir);
			}
		}
		else{
			//只导入指定的
			if($tableNum<count($taskList)){
				runTask($redis,$taskList[$tableNum],$dir);
			}
			else{
				fwrite(STDERR, "wrong task number task length is ".count($taskList));
			}
		}
    }
}

function runTask($redis,$task,$dir){
	$count=0;
	$redis->del($task->rediskey);
	foreach ($task->csv as $csv) {
		$csvfile = $dir."/".$csv;
		if (($fp = fopen($csvfile, "r")) !== false) {
			fwrite(STDOUT, "单词表load {$csvfile}\n");
			$row=0;
			while (($data = fgetcsv($fp)) !== false) {
				$row++;
				$data1 = $data;
				if(count($data1)>7){
					if($data1[2]==".comp." && $data1[1]===$data1[7]){
						continue;
					}
					$old = $redis->hGet($task->rediskey,$data1[$task->keycol]);
					$new = array();
					if($old){
						$new = json_decode($old,true);
						array_push($new,$data1);
					}
					else{
						$new[] = $data1;
					}
					$redis->hSet($task->rediskey,$data1[$task->keycol],json_encode($new, JSON_UNESCAPED_UNICODE));
				}
				else{
					//echo "列不足够：行：{$row} 列：".count($data1)." 数据：{$data} \n";
				}
				$count++;
				if($count%50000==0){
					sleep(1);
					fwrite(STDOUT, $count."\n");
				}
			}
			fclose($fp);
			sleep(1);
			fwrite(STDOUT,  "task : {$task->rediskey}:".$redis->hLen($task->rediskey)."\n");

		} else {
			fwrite(STDERR,  "can not open csv file. ".PHP_EOL);
		}
	}
}

?>
