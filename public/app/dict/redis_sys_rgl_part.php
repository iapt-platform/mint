<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli" || isset($_COOKIE["userid"]))
{
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_DICT_DB_REGULAR_, Database["user"], Database["password"], array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

		$query = "SELECT * from (SELECT pali,parts from "._TABLE_DICT_REGULAR_." order by confidence DESC ) group by pali";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$redis->hSet("dict://regular/part",$row["pali"],$row["parts"]);
		}
		fwrite(STDOUT,  "all done ".$redis->hLen("dict://regular/part").PHP_EOL);
	}else{
		fwrite(STDERR,"redis connect is fail".PHP_EOL);
	}
}

?>
