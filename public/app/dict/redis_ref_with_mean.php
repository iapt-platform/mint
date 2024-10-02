<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dns =  _FILE_DB_REF_;
		$dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
		$query = "SELECT word from "._TABLE_DICT_REF_." where dict_id in (3,4,6,7,8,10,12,13,15,18,19,21,22,23,24) group by word";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$redis->hSet("dict://ref/has_mean",$row["word"],"1");
		}
	}
	fwrite(STDOUT,  "all done ".$redis->hLen("dict://ref/has_mean").PHP_EOL);
}

?>