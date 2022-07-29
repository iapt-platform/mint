<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

$rediskey = Redis["prefix"]."dict/user";
if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_FILE_DB_WBW_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT word from "._TABLE_DICT_WBW_." group by word";
		$stmtPli = $dbh->query($query);
		while ($word = $stmtPli->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$query = "SELECT * from "._TABLE_DICT_WBW_." where word = ? and (source = '_USER_DATA_' or source = '_USER_WBW_') ";
			$stmt = $dbh->prepare($query);
			$stmt->execute(array($word["word"]));
			if ($stmt) {
				$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$redisWord=array();
				foreach ($Fetch as  $one) {
					# code...
					$redisWord[] = array(
									$one["id"],
									$one["word"],
									$one["type"],
									$one["grammar"],
									$one["parent"],
									$one["mean"],
									$one["note"],
									$one["factors"],
									$one["factormean"],
									$one["status"],
									$one["confidence"],
									$one["creator_id"],
									$one["source"],
									$one["language"]
									);
				}
				$redis->hSet($rediskey,$word["word"],json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}
		}
	}
	fwrite(STDOUT,  "all done ".$redis->hLen($rediskey).PHP_EOL);
}

?>