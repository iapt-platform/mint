<?php
#从自动复合词数据库中提取数据到ridis
#已经废弃
require_once "../config.php";
require_once "../redis/function.php";

if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if ($redis != false) {
		$dbh = new PDO(_DICT_DB_COMP_, "", "", array(PDO::ATTR_PERSISTENT => true));
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT pali from "._TABLE_DICT_COMP_." where 1 group by pali";
		$stmt = $dbh->query($query);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			# code...
			$query = "SELECT parts as word from "._TABLE_DICT_COMP_." where pali=?";
			$stmtOne = $dbh->query($query);
			$stmtOne->execute(array($row["pali"]));
			$fComp = $stmtOne->fetchAll(PDO::FETCH_ASSOC);		
			$output = json_encode(array($fComp), JSON_UNESCAPED_UNICODE);
			$redis->hSet("dict://comp",$row["pali"],$output);
		}
	}
	echo "all done".$redis->hLen("dict://comp");
}

?>