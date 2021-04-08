<?php
/*
从巴利句子列表数据库中提取数据填充redis
每个句子包含 
pali 
id 
sim_count
 */
require_once "../path.php";
require_once "../redis/function.php";

if (isset($argv[1])) {
    if ($argv[1] == "del") {
        $redis = new redis();
        $r_conn = $redis->connect('127.0.0.1', 6379);
		$keys = $redis->keys('pali_sent_*');
		$count=0;
		foreach ($keys as $key => $value) {
			# code...
			$deleted = $redis->del($value);
			$count += $deleted;
		}
		
		echo "delete ok ".$count;
    }
} else {

    $dbh = new PDO(_FILE_DB_PALI_SENTENCE_, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

	$db_pali_sent_sim = new PDO(_FILE_DB_PALI_SENTENCE_SIM_, "", "", array(PDO::ATTR_PERSISTENT => true));
	$db_pali_sent_sim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $query = "SELECT id, book,paragraph, begin,end ,html FROM pali_sent WHERE 1 ";
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
				echo $count . "-".$sent["book"] . "_" . $sent["paragraph"] . "\n";
			}
			$result = $redis->hSet('pali://sent/' . $sent["book"] . "_" . $sent["paragraph"] . "_" . $sent["begin"] . "_" . $sent["end"], "pali", $sent["html"]);
			if($result===FALSE){
				echo "hSet error \n";
			}
			$result = $redis->hSet('pali://sent/' . $sent["book"] . "_" . $sent["paragraph"] . "_" . $sent["begin"] . "_" . $sent["end"], "id", $sent["id"]);	

			$query = "SELECT count FROM 'sent_sim_index' WHERE sent_id = ? ";
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
        echo "完成 ". count($redis->keys("pali://sent/*"));
    } else {
        echo "连接redis失败";
    }
}
