<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../redis/function.php";

echo 'discard';
return;
if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_DICT_DB_PM_, Database["user"], Database["password"], array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT pali,parts from "._TABLE_DICT_PM_." group by pali";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			if(!empty($row["parts"])){
				$redis->hSet("dict://pm/part",$row["pali"],$row["parts"]);
			}
		}
		fwrite(STDOUT, "all done ".$redis->hLen("dict://pm/part").PHP_EOL);
	}else{
		fwrite(STDERR,"redis connect is fail".PHP_EOL);
	}

}

?>
