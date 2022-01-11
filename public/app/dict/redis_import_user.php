<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

$rediskey = "dict://user";
if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_FILE_DB_WBW_, "", "", array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT pali from dict where pali !='' group by pali";
		$stmtPli = $dbh->query($query);
		while ($word = $stmtPli->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$query = "SELECT * from dict where pali = ? ";
			$stmt = $dbh->prepare($query);
			$stmt->execute(array($word["pali"]));
			if ($stmt) {
				$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$redisWord=array();
				foreach ($Fetch as  $one) {
					# code...
					$redisWord[] = array($one["id"],
										$one["pali"],
									$one["type"],
									$one["gramma"],
									$one["parent"],
									$one["mean"],
									$one["note"],
									$one["factors"],
									$one["factormean"],
									$one["status"],
									$one["confidence"],
									$one["creator"],
									$one["dict_name"],
									$one["language"]
									);
				}
				$redis->hSet($rediskey,$word["pali"],json_encode($redisWord,JSON_UNESCAPED_UNICODE));
			}
		}
	}
	echo "all done ".$redis->hLen($rediskey);
}

?>