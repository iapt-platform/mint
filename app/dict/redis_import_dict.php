<?php  
require_once "../path.php";
require_once "../install/filelist.php";
require_once "../redis/function.php";

if (PHP_SAPI == "cli") {
    if ($argc >= 2) {
		$list = $argv[1];
        $redis = redis_connect();
        if ($redis == false) {
            echo "no redis connect\n";
            exit;
		}
		$count=0;
		$taskList = json_decode(file_get_contents(__DIR__."/".$list));
		$dir = dirname(__DIR__."/".$list);
		foreach ($taskList as $key => $task) {
			# code...
			$redis->del($task->rediskey);
			foreach ($task->csv as $csv) {
				$csvfile = $dir."/".$csv;
				if (($fp = fopen($csvfile, "r")) !== false) {
					echo "单词表load {$csvfile}\n";
					while (($data = fgets($fp)) !== false) {
						$data1 = explode(",",$data);
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
						$count++;
						if($count%10000==0){
							sleep(1);
							echo $count."\n";
						}
					}
					fclose($fp);
					
				} else {
					echo "can not open csv file. ";
				}	
			}
		
		}


    }
}


?>