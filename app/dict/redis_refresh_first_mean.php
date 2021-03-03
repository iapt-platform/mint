<?php
require_once "../path.php";
require_once '../public/_pdo.php';
require_once "../redis/function.php";

if (PHP_SAPI == "cli") {
	$redis = redis_connect();
	if($redis!==false){
		
		PDO_Connect(_FILE_DB_REF_, _DB_USERNAME_, _DB_PASSWORD_);
		$query = "SELECT language as lang from " . _TABLE_DICT_REF_ . " where 1  group by language";
		$Fetch = PDO_FetchAll($query);
		if(count($Fetch)>0){
			foreach ($Fetch as $key => $value) {
				# 获取字典中所有的语言
				if(!empty($value["lang"])){
					$languages[] = $value["lang"];
				}
			}
			print_r($languages);
			foreach ($languages as $thisLang) {
				# code...
				$query = "SELECT word,mean from " . _TABLE_DICT_REF_ . " where language = ? group by word";
				$meanings = PDO_FetchAll($query, array($thisLang));
				foreach ($meanings as $mean) {
					# code...
					$redis->hSet("ref_first_mean://".$thisLang,$mean["word"],$mean["mean"]);
				}
				echo $thisLang.":".$redis->hLen("ref_first_mean://".$thisLang)."\n";
			}

			$query = "SELECT word,mean from " . _TABLE_DICT_REF_ . " where 1 group by word";
			$meanings = PDO_FetchAll($query);
			foreach ($meanings as $mean) {
				# code...
				$redis->hSet("ref_first_mean://com",$mean["word"],$mean["mean"]);
			}
			echo "com:".$redis->hLen("ref_first_mean://com")."\n";
		}

	}
	else{
		echo "no redis server";
	}

}

?>