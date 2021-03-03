<?php
require_once '../path.php';
require_once '../public/_pdo.php';
require_once '../redis/function.php';

function getRefFirstMeaning($word,$lang,$redis){
	if($redis!==false){
		/*
		$mean = $redis->hGet("ref_first_mean://".$lang,$word);
		if($mean===FALSE){
			PDO_Connect(_FILE_DB_REF_, _DB_USERNAME_, _DB_PASSWORD_);
			$query = "SELECT mean,language as lang from " . _TABLE_DICT_REF_ . " where word = ?  group by language";
			$Fetch = PDO_FetchAll($query, array($word));
			if(count($Fetch)>0){
				foreach ($Fetch as $key => $value) {
					# code...
					$redis->hSet("ref_first_mean://".$word,$value["lang"],$value["mean"]);
				}
			}
		}
		*/
		$mean = $redis->hGet("ref_first_mean://".$word,$lang);
		if($mean!=FALSE){
			return $mean;
		}
		else{
			if($lang!="en"){
				$mean = $redis->hGet("ref_first_mean://".$word,"en");
				if($mean!==FALSE){
					return $mean;
				}
			}

			$any = $redis->hGet("ref_first_mean://com",$word);
			if($any!==FALSE){
					# code...
				return $any;

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
