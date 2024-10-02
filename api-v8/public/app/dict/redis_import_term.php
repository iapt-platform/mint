<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

$rediskey = "dict://term";
if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_FILE_DB_TERM_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT word from "._TABLE_TERM_." where word !='' group by word";
		$stmtPali = $dbh->query($query);
		while ($word = $stmtPali->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$query = "SELECT * from "._TABLE_TERM_." where word = ? ";
			$stmt = $dbh->prepare($query);
			$stmt->execute(array($word["word"]));
			if ($stmt) {
				$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$redisWord=array();
				foreach ($Fetch as  $one) {
					# code...
					$redisWord[] = array($one["id"],
										$one["word"],
									"",
									"",
									"",
									$one["meaning"]."$".$one["other_meaning"],
									$one["note"],
									"",
									"",
									1,
									100,
									$one["owner"],
									"term",
									$one["language"]
									);
				}
				$redis->hSet($rediskey,$word["word"],json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}
		}
		fwrite(STDOUT,  "all done ".$redis->hLen($rediskey).PHP_EOL);
	}else{
		fwrite(STDERR,"redis connect is fail".PHP_EOL);
	}

}

?>
