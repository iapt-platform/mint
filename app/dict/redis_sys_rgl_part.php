<?php
require_once "../path.php";
require_once "../redis/function.php";

if (PHP_SAPI == "cli" || isset($_COOKIE["userid"])) 
{
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_DICT_DB_REGULAR_, "", "", array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
		$query = "select * from (SELECT pali,parts from "._TABLE_DICT_REGULAR_." where 1  order by confidence DESC ) where 1 group by pali";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$redis->hSet("dict://regular/part",$row["pali"],$row["parts"]);
		}
	}
	echo "all done ".$redis->hLen("dict://regular/part");
}

?>