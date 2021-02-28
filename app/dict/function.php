<?php
require_once '../path.php';
require_once '../public/_pdo.php';
require_once '../redis/function.php';

function getRefFirstMeaning($word,$lang,$redis){
	if($redis!==false){
		$len = $redis->hLen("ref_first_mean_".$word);
		if($len===FALSE || $len==0){
			PDO_Connect(_FILE_DB_REF_, _DB_USERNAME_, _DB_PASSWORD_);
			$query = "SELECT mean,language as lang from " . _TABLE_DICT_REF_ . " where word = ?  group by language";
			$Fetch = PDO_FetchAll($query, array($word));
			foreach ($Fetch as $key => $value) {
				# code...
				$redis->hset("ref_first_mean_".$word,$value["lang"],$value["mean"]);
			}
		}
		$mean = $redis->hGet("ref_first_mean_".$word,$lang);
		if($mean!=FALSE){
			return $mean;
		}
		else{
			if($lang!="en"){
				$mean = $redis->hGet("ref_first_mean_".$word,"en");
				if($mean!=FALSE){
					return $mean;
				}
			}

			$arr_keys = $redis->hGetAll("ref_first_mean_".$word);
			if(count($arr_keys)>0){
				foreach ($arr_keys as $key => $value) {
					# code...
					return $value;
				}
			}
			else{
				return "";
			}

		}
		
	}
	else{
		PDO_Connect(_FILE_DB_REF_, _DB_USERNAME_, _DB_PASSWORD_);
		$query = "SELECT mean from " . _TABLE_DICT_REF_ . " where word = ? and language = ?  limit 0,1";
		# code...
		$mean = PDO_FetchRow($query, array($word, $lang));
		if ($mean) {
			return $mean["mean"];
		} else {
			if ($lang != "en") {
				$mean = PDO_FetchRow($query, array($word, "en"));
				if ($mean) {
					return $mean["mean"];
				} else {
					return "";
				}
			} else {
				return "";
			}
		}
				
	}

}