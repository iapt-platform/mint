<?php
require_once '../config.php';
require_once '../public/_pdo.php';
require_once '../redis/function.php';

function getRefFirstMeaning($word,$lang,$redis){
	if(strpos($word,"[")){
		$word = strstr($word,"[",true);
	}
	if($redis!==false){
		if(mb_substr($word,0,1,"UTF-8")==="["){
			$ending = "-".mb_substr($word,1,-1,"UTF-8");
			$mean = $redis->hGet("ref_first_mean://".$lang,$ending);
			if($mean!=FALSE){
				return $mean;
			}
		}
		$mean = $redis->hGet("ref_first_mean://".$lang,$word);
		if($mean!=FALSE){
			return $mean;
		}

		if($lang!="en"){
			$mean = $redis->hGet("ref_first_mean://en",$word);
			if($mean!==FALSE){
				return $mean;
			}
		}
		#如果没有查规则变形
		if($redis->hExists("dict://regular/part",$word)===TRUE){
			$rglPart = explode("+",$redis->hGet("dict://regular/part",$word)) ;
			$mean = $mean = $redis->hGet("ref_first_mean://".$lang,$rglPart[0]);
			if($mean!=FALSE){
				return $mean;
			}
		}
		#查询其他的语言
		$any = $redis->hGet("ref_first_mean://com",$word);
		if($any!==FALSE){
				# code...
			return $any;

		}
		else{
			return "";
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
