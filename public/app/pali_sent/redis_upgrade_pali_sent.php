<?php
/*
从巴利句子列表数据库中提取数据填充redis
每个句子包含 
pali 
id 
sim_count
 */
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

if (isset($argv[1])) {
    if ($argv[1] == "del") {
        $redis = redis_connect();
        if($redis){
			$keys = $redis->keys('pali_sent_*');
			$count=0;
			foreach ($keys as $key => $value) {
				# code...
				$deleted = $redis->del($value);
				$count += $deleted;
			}
			
			fwrite(STDOUT,"delete ok ".$count.PHP_EOL) ;			
		}else{
			fwrite(STDERR, "redis connect error ".PHP_EOL);			

		}
    }
} else {

    $dbh = new PDO(_FILE_DB_PALI_SENTENCE_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

	$db_pali_sent_sim = new PDO(_FILE_DB_PALI_SENTENCE_SIM_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
	$db_pali_sent_sim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $query = "SELECT id, book,paragraph, word_begin as begin ,word_end as end ,html FROM "._TABLE_PALI_SENT_." WHERE true ";
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    $redis = redis_connect();
	$stringSize = 0;
	$count = 0;
    if ($redis) {
        while ($sent = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$count++;
            $stringSize += strlen($sent["html"]);
            if ($stringSize > 50000000) {
                sleep(1);
                $stringSize = 0;  
			}
			if($count%10000==0){
				fwrite(STDOUT, $count . "-".$sent["book"] . "_" . $sent["paragraph"] . "\n");
			}
			$result = $redis->hSet('pali://sent/' . $sent["book"] . "_" . $sent["paragraph"] . "_" . $sent["begin"] . "_" . $sent["end"], "pali", $sent["html"]);
			if($result===FALSE){
				fwrite(STDERR, "hSet error \n");
			}
			$result = $redis->hSet('pali://sent/' . $sent["book"] . "_" . $sent["paragraph"] . "_" . $sent["begin"] . "_" . $sent["end"], "id", $sent["id"]);	

			$query = "SELECT count FROM "._TABLE_SENT_SIM_INDEX_." WHERE sent_id = ? ";
			$sth = $db_pali_sent_sim->prepare($query);
			$sth->execute(array($sent["id"]));
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$pali_sim = $row["count"];
			} else {
				$pali_sim = 0;
			}
			$result = $redis->hSet('pali://sent/' . $sent["book"] . "_" . $sent["paragraph"] . "_" . $sent["begin"] . "_" . $sent["end"], "sim_count", $pali_sim);			
        }
        fwrite(STDOUT, "完成 ". count($redis->keys("pali://sent/*")).PHP_EOL);
    } else {
        fwrite(STDERR, "连接redis失败".PHP_EOL);
    }
}
