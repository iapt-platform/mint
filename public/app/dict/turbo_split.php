<?php
require_once __DIR__.'/../public/casesuf.inc';
require_once __DIR__."/../config.php";
require_once __DIR__."/../public/_pdo.php";

require_once __DIR__."/../redis/function.php";
global $redis;
$redis = redis_connect();

// open word part db
global $dbh;
$dns = _FILE_DB_PART_;
$dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

global $path;
#当前搜索路径信心指数，如果过低，马上终止这个路径的搜索
global $currPathCf;
#内存信心指数表
global $confidence;
global $result;
global $part;
define("MAX_RESULT",100);

$part = array();

$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);
$path[] = array("", 0);

global $sandhi;
//sandhi rules table 语尾表
$sandhi[] = array("a" => "", "b" => "", "c" => "", "len" => 0, "adj_len" => 0, "advance" => false,"cf"=>1.0);
$sandhi[] = array("a" => "a", "b" => "a", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "ā", "b" => "ā", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "ā", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ā", "b" => "a", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "e", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "i", "c" => "i", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "o", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "u", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "u", "b" => "a", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "u", "b" => "u", "c" => "ū", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "u", "c" => "u", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "ī", "c" => "ī", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "ū", "c" => "ū", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "i", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "e", "b" => "a", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "i", "b" => "i", "c" => "ī", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "i", "b" => "e", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "i", "b" => "a", "c" => "ya", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "atth", "c" => "atth", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "taṃ", "b" => "n", "c" => "tann", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "api", "c" => "mpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "eva", "c" => "meva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[o]", "b" => "iva", "c" => "ova", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "o", "b" => "a", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "ādi", "c" => "ādi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a[ānaṃ]", "b" => "a", "c" => "ānama", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "a", "c" => "ma", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ṃ", "b" => "a", "c" => "m", "len" => 1, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "ā", "c" => "mā", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "u", "c" => "mu", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "[ṃ]", "b" => "h", "c" => "ñh", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ā", "b" => "[ṃ]", "c" => "am", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "[ṃ]", "c" => "am", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ī", "b" => "[ṃ]", "c" => "im", "len" => 2, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ati", "b" => "tabba", "c" => "atabba", "len" => 6, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ati", "b" => "tabba", "c" => "itabba", "len" => 6, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "iti", "b" => "a", "c" => "icca", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

$sandhi[] = array("a" => "uṃ", "b" => "a", "c" => "uma", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "u[ūnaṃ]", "b" => "a", "c" => "ūnama", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ī[īnaṃ]", "b" => "a", "c" => "īnama", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "su", "b" => "a", "c" => "sva", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

#other sandhi rule. can be use but program will be slow down
#其他连音规则，如果使用则会让程序运行变慢

$sandhi[] = array("a" => "ā", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "e", "b" => "iti", "c" => "eti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "ī", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "i", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "o", "b" => "iti", "c" => "oti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "ū", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "u", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi[] = array("a" => "ṃ", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);

$sandhi[] = array("a" => "ṃ", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>1.0);
$sandhi[] = array("a" => "ṃ", "b" => "cāti", "c" => "ñcāti", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>1.0);
$sandhi[] = array("a" => "ṃ", "b" => "cet", "c" => "ñcet", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi[] = array("a" => "ṃ", "b" => "ev", "c" => "mev", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);

/*
$sandhi2[] = array("a" => "ṃ", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>1.0);
$sandhi2[] = array("a" => "ṃ", "b" => "hi", "c" => "ñhi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>1.0);

$sandhi2[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi2[] = array("a" => "e", "b" => "iti", "c" => "eti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi2[] = array("a" => "i", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi2[] = array("a" => "o", "b" => "iti", "c" => "oti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi2[] = array("a" => "u", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
$sandhi2[] = array("a" => "ṃ", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);

$sandhi2[] = array("a" => "a", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "e", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "i", "b" => "eva", "c" => "yeva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "ī", "b" => "eva", "c" => "iyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "ī", "b" => "eva", "c" => "īyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "o", "b" => "eva", "c" => "ova", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "u", "b" => "eva", "c" => "veva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

$sandhi2[] = array("a" => "ā", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "a", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "e", "b" => "api", "c" => "epi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "ī", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "i", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "o", "b" => "api", "c" => "opi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "ū", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "u", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "u", "b" => "api", "c" => "upi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
$sandhi2[] = array("a" => "ṃ", "b" => "api", "c" => "mpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

*/
$sandhi[] = array("a" => "a", "b" => "a", "c" => "a", "len" => 1, "adj_len" => -1, "advance" => true,"cf"=>0.99);
$sandhi[] = array("a" => "ī", "b" => "", "c" => "i", "len" => 1, "adj_len" => 0, "advance" => true,"cf"=>0.9);

/*
从双元音处切开
*/
function split_diphthong($word)
{
    //diphthong table双元音表
    $search = array('aa', 'ae', 'ai', 'ao', 'au', 'aā', 'aī', 'aū', 'ea', 'ee', 'ei', 'eo', 'eu', 'eā', 'eī', 'eū', 'ia', 'ie', 'ii', 'io', 'iu', 'iā', 'iī', 'iū', 'oa', 'oe', 'oi', 'oo', 'ou', 'oā', 'oī', 'oū', 'ua', 'ue', 'ui', 'uo', 'uu', 'uā', 'uī', 'uū', 'āa', 'āe', 'āi', 'āo', 'āu', 'āā', 'āī', 'āū', 'īa', 'īe', 'īi', 'īo', 'īu', 'īā', 'īī', 'īū', 'ūa', 'ūe', 'ūi', 'ūo', 'ūu', 'ūā', 'ūī', 'ūū');
    $replace = array('a-a', 'a-e', 'a-i', 'a-o', 'a-u', 'a-ā', 'a-ī', 'a-ū', 'e-a', 'e-e', 'e-i', 'e-o', 'e-u', 'e-ā', 'e-ī', 'e-ū', 'i-a', 'i-e', 'i-i', 'i-o', 'i-u', 'i-ā', 'i-ī', 'i-ū', 'o-a', 'o-e', 'o-i', 'o-o', 'o-u', 'o-ā', 'o-ī', 'o-ū', 'u-a', 'u-e', 'u-i', 'u-o', 'u-u', 'u-ā', 'u-ī', 'u-ū', 'ā-a', 'ā-e', 'ā-i', 'ā-o', 'ā-u', 'ā-ā', 'ā-ī', 'ā-ū', 'ī-a', 'ī-e', 'ī-i', 'ī-o', 'ī-u', 'ī-ā', 'ī-ī', 'ī-ū', 'ū-a', 'ū-e', 'ū-i', 'ū-o', 'ū-u', 'ū-ā', 'ū-ī', 'ū-ū');
    //将双元音拆开
    //step 1 : split at diphthong . ~aa~ -> ~a-a~
    $word1 = str_replace($search, $replace, $word);
    //按连字符拆开处理
    $arrword = str_getcsv($word1, "-");
    return $arrword;
}

/*
用于数组连接字符串
 */
function myfunction($v1, $v2)
{
    return $v1 . "+" . $v2;
}
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}


function dict_lookup2($word){
	global $redis;
	global $case;	
    if (strlen($word) <= 0) {
        return 0;
    }
	if(mb_substr($word,0,1)==="["){
		$search = $word;
	}
	else{
		$search = str_replace("[","",$word);
		$search = str_replace("]","",$search);		
	}
	$cf = $redis->hGet("dict://part.hash",$search);
	if($cf==false){
		//去除尾查
		$newWord = array();
		for ($row = 0; $row < count($case); $row++) {
			$len = mb_strlen($case[$row][1], "UTF-8");
			$end = mb_substr($search, 0 - $len, null, "UTF-8");
			if ($end == $case[$row][1]) {
				$base = mb_substr($search, 0, mb_strlen($search, "UTF-8") - $len, "UTF-8") . $case[$row][0];
				if ($base != $search) {
					$newWord[$base] = mb_strlen($case[$row][1],"UTF-8");
				}
			}
		}
		#找到最高频的base
		$base_weight = 0;
		$isFound = false;
		if(count($newWord)>0){
			foreach ($newWord as $x => $x_value) {
				$row = $redis->hGet("dict://part.hash",$x);
				if ($row !=false) {
					$isFound=true;
					if ($row > $base_weight) {
						$base_weight = $row;
					}
				}
			}
			if($isFound){
				$base_weight*=0.9999;
				$redis->hSet("dict://part.hash",$search,$base_weight);
				if (isset($_POST["debug"])) {
					echo "查到变格：{$search}:{$base_weight}\n";
				}				
			}
		}
		return $base_weight;
	}
	else{
		if (isset($_POST["debug"])) {
			echo "查到：{$search}:{$cf}\n";
		}
		return $cf;
	}
}
function dict_lookup($word){
    if (strlen($word) <= 1) {
        return array(0,0);
    }
    global $case;
	global $dbh;

	if(mb_substr($word,0,1)==="["){
		$search = $word;
	}
	else{
		$search = str_replace("[","",$word);
		$search = str_replace("]","",$search);		
	}

    $query = "SELECT weight from part where word = ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($search));
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        return array($row[0],0);
    } else {
        //去除尾查
        $newWord = array();
        for ($row = 0; $row < count($case); $row++) {
            $len = mb_strlen($case[$row][1], "UTF-8");
            $end = mb_substr($search, 0 - $len, null, "UTF-8");
            if ($end == $case[$row][1]) {
                $base = mb_substr($search, 0, mb_strlen($search, "UTF-8") - $len, "UTF-8") . $case[$row][0];
                if ($base != $search) {
                    $newWord[$base] = mb_strlen($case[$row][1],"UTF-8");
                }
            }
        }
        #找到最高频的base
		$base_weight = 0;
		$len = 0;
        foreach ($newWord as $x => $x_value) {
            $query = "SELECT weight from part where word = ? ";
            $stmt = $dbh->prepare($query);
            $stmt->execute(array($x));
            $row = $stmt->fetch(PDO::FETCH_NUM);
            if ($row) {
                if ($row[0] > $base_weight) {
					$base_weight = $row[0];
					$len=$x_value;
                }
            }
        }
        return array($base_weight,$len);
    }
}

/*
查找某个单词是否在现有词典出现
返回信心指数
look up single word in dictionary vocabulary
return the confidence value
 */
function isExsit($word, $adj_len = 0){

    global $auto_split_times;
    global $part;
    global $confidence;
    $auto_split_times++;

    if (isset($_POST["debug"])) {
        echo "<div>正在查询：{$word}</div>";
	}
	//return dict_lookup2($word);

    $isFound = false;
    $count = 0;
    if (isset($part["{$word}"])) {
		$word_count = $part["{$word}"][0];
		$case_len = $part["{$word}"][1];
        if ($word_count > 0) {
			if (isset($_POST["debug"])) {
                echo "查到：{$word}:{$word_count}个\n";
            }
            $isFound = true;
            $count = $word_count + 1;
        }
    } else {
        $db = dict_lookup($word);
		$word_count = $db[0];
		$case_len = $db[1];
        //加入查询缓存
        $part["{$word}"] = $db;
        if ($word_count > 0) {
            if (isset($_POST["debug"])) {
                echo "查到：{$word}:{$word_count}个\n";
            }
            $isFound = true;
            $count = $word_count + 1;
        }
    }
	//fomular of confidence value 信心值计算公式
    if ($isFound) {
        if (isset($confidence["{$word}"])) {
            $cf = $confidence["{$word}"];
        } else {
			//$len = mb_strlen($word, "UTF-8") + $adj_len;
			$len = mb_strlen($word, "UTF-8") - $case_len;
            $len_correct = 1.2;
            $count2 = 1.1 + pow($count, 1.18);
            $conf_num = pow(1 / $count2, pow(($len - 0.5), $len_correct));
            $cf = round(1 / (1 + 640 * $conf_num), 9);
			//$cf = round((1-0.02*$case_len) / (1 + 640 * $conf_num), 9);
            $confidence["{$word}"] = $cf;
            if (isset($_POST["debug"])) {
                echo "信心指数：{$word}:{$cf}\n";
            }
        }
        return ($cf);

    } else {
        return (-1);
    }
}

/*
核心拆分函数

$strWord, word to be look up 要查询的词
$deep, 当前递归深度
$express=true, 快速查询
$adj_len=0 长度校正系数
$c_threshhold 信心指数阈值
 */

function mySplit2($strWord, $deep = 0, $express = false, $adj_len = 0, $c_threshhold = 0.8, $w_threshhold = 0.8, $forward = true, $sandhi_advance = false)
{
    global $path;
    global $result;
    global $sandhi;
    $output = array();
	#当前搜索路径信心指数，如果过低，马上终止这个路径的搜索
	global $currPathCf;
	if($deep == 0){
		$currPathCf = 1;
	}
    //达到最大搜索深度，返回
    if ($deep >= 16) {
        $word = "";
        $cf = 1.0;
        for ($i = 0; $i < $deep; $i++) {
            if (!empty($path[$i][0])) {
                $word .= $path[$i][0] ;
                if (isset($_POST["debug"])) {
                    $word .= "(" . $path[$i][1] . ")";
				}
				$word .= "+";
                $cf = $cf * $path[$i][1];
            }
        }
        $len = pow(mb_strlen($strWord, "UTF-8"), 3);
        $cf += (0 - $len) / ($len + 150);
        $word .= "{$strWord}";
        if ($forward == true) {
			$result[$word] = $cf;
			return;
        } else {
            $reverseWord = word_reverse($word);
			$result[$reverseWord] = $cf;
			return;
        }
        
    }
    //直接找到
    $confidence = isExsit($strWord, $adj_len);
    if ($confidence > $c_threshhold) {
        $output[] = array($strWord, "", $confidence);
	} 
	else {
        $confidence = isExsit("[" . $strWord . "]");
        if ($confidence > $c_threshhold) {
            $output[] = array("[" . $strWord . "]", "", $confidence);
        }
    }

    //如果开头有双辅音，去掉第一个辅音。因为巴利语中没有以双辅音开头的单词。
    $doubleword = "kkggccjjṭṭḍḍttddppbb";
    if (mb_strlen($strWord, "UTF-8") > 2) {
        $left2 = mb_substr($strWord, 0, 2, "UTF-8");
        if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
            $strWord = mb_substr($strWord, 1, null, "UTF-8");
        }
    }

    $len = mb_strlen($strWord, "UTF-8");
    if ($len > 2) {
        if ($forward) {
            #正向切
            for ($i = $len; $i > 1; $i--) {
                foreach ($sandhi as $key => $row) {
                    if ($sandhi_advance == false && $row["advance"] == true) {
                        //continue;
                    }
                    if (mb_substr($strWord, $i - $row["len"], $row["len"], "UTF-8") == $row["c"]) {
                        $str1 = mb_substr($strWord, 0, $i - $row["len"], "UTF-8") . $row["a"];
                        $str2 = $row["b"] . mb_substr($strWord, $i, null, "UTF-8");
                        $confidence = isExsit($str1, $adj_len)*$row["cf"];
                        if ($confidence > $c_threshhold) {
                            $output[] = array($str1, $str2, $confidence, $row["adj_len"]);
                            if (isset($_POST["debug"])) {
                                echo "插入：{$str1} 剩余{$str2} 应用：{$row["a"]}-{$row["b"]}-{$row["c"]}\n";
                            }
                            if ($express) {
                                break;
                            }
                        }

                    }
                }
            }
        } else {
            #反向切
            for ($i = 1; $i < $len - 1; $i++) {
                foreach ($sandhi as $key => $row) {
                    if ($sandhi_advance == false && $row["advance"] == true) {
                        //continue;
                    }
                    if (mb_substr($strWord, $i, $row["len"], "UTF-8") == $row["c"]) {
                        $str1 = mb_substr($strWord, 0, $i, "UTF-8") . $row["a"];
                        $str2 = $row["b"] . mb_substr($strWord, $i + $row["len"], null, "UTF-8");
                        $confidence = isExsit($str2, $adj_len)*$row["cf"];
                        if ($confidence > $c_threshhold) {
                            $output[] = array($str2, $str1, $confidence, $row["adj_len"]);
                            if (isset($_POST["debug"])) {
                                echo "插入：{$str2}\n";
                            }
                            if ($express) {
                                break;
                            }
                        }

                    }
                }
            }
        }
	}
	
	$word = "";
    if (count($output) > 0) {
        foreach ($output as $part) {
            $checked = $part[0];
            $remainder = $part[1];

            $path[$deep][0] = $checked;
			$path[$deep][1] = $part[2];

            if (empty($remainder)) {
                #全切完了
                $word = "";
                $cf = 1.0;
                for ($i = 0; $i < $deep; $i++) {
                    $word .= $path[$i][0];
                    if (isset($_POST["debug"])) {
                        $word .= "(" . $path[$i][1] . ")";
                    }
                    $word .= "+";
                    $cf = $cf * $path[$i][1];
                }

                if (isset($_POST["debug"])) {
                    $word .= $checked . "({$part[2]})";
                } else {
                    $word .= $checked;
                }
                $cf = $cf * $part[2];
                if ($cf > $w_threshhold) {
                    if ($forward == true) {
						$result[$word] = $cf;
						return;
                    } else {
                        $reverseWord = word_reverse($word);
                        $result[$reverseWord] = $cf;
						return;
                    }
                }
            } else {
				#计算当前信心指数
				$cf = 1.0;
				for ($i = 0; $i < $deep; $i++) {
					$cf = $cf * $path[$i][1];
				}
				if($cf<$w_threshhold)
				{
					if (isset($_POST["debug"])) {
						echo "信心指数过低，提前返回 {$cf}<br>";
					}
					return;

				}
				else
				{
					#接着切
					mySplit2($remainder, ($deep + 1), $express, $adj_len, $c_threshhold, $w_threshhold, $forward, $sandhi_advance);					
				}

            }
        }
	} 
	else {
        #尾巴查不到了
        $word = "";
        $cf = 1.0;
        for ($i = 0; $i < $deep; $i++) {
            $word .= $path[$i][0];
            if (isset($_POST["debug"])) {
                $word .= "(" . $path[$i][1] . ")";
            }
            $word .= "+";
            $cf = $cf * $path[$i][1];
		}
		
		$len = pow(mb_strlen($strWord, "UTF-8"), 3);
		
        if ($forward) {
            $cf =(1-$cf) * $len / ($len + 150);
        } else {
			$cf =(1-$cf) * $len / ($len + 5);
		}
		
        if (isset($_POST["debug"])) {
            $word = $word.$strWord . "(0)";
        } else {
            $word = $word .$strWord;
        }

        if ($cf > $w_threshhold) {
            if ($forward == true) {
				$result[$word] = $cf;
				return;
			} 
			else {
                $reverseWord = word_reverse($word);
				$result[$reverseWord] = $cf;
				return;
            }
        }
    }

}

function word_reverse($word)
{
    $reverse = array();
    $newword = explode("+", $word);
    $len = count($newword);
    if ($len > 0) {
        for ($i = $len - 1; $i >= 0; $i--) {
            # code...
            $reverse[] = $newword[$i];
        }
        $output = implode("+", $reverse);
        return $output;
    } else {
        return $word;
    }
}

#后处理
function split2($word){
	global $redis;

	$input = explode("+",$word);
	$newword=array();
	foreach ($input as $value) {
		$word = strstr($value,"(",true);
		if($word==false){
			$word=$value;
		}
		if(mb_strlen($word,"UTF-8")>4){
		# 先看有没有中文意思
			if($redis->hExists("dict://ref/has_mean",$word)===TRUE && mb_strlen($word,"UTF-8")<7){
				$newword[]=$word;
			}
			else{
				#如果没有查巴缅替换拆分
				if($redis->hExists("dict://pm/part",$word)===TRUE){
					$pmPart = explode("+",$redis->hGet("dict://pm/part",$word)) ;
					foreach ($pmPart as  $pm) {
						# code...
						$newword[]=$pm;
					}
				}
				else{
					#如果没有查规则变形
					if($redis->hExists("dict://regular/part",$word)===TRUE){
						$rglPart = explode("+",$redis->hGet("dict://regular/part",$word)) ;
						#看巴缅有没有第一部分
						if($redis->hExists("dict://pm/part",$rglPart[0])===TRUE){
							$pmPart = explode("+",$redis->hGet("dict://pm/part",$rglPart[0])) ;
							foreach ($pmPart as  $pm) {
								# code...
								$newword[]=$pm;
							}
						}
						else{
							#没有
							$newword[]=$rglPart[0];
						}
						$newword[]=$rglPart[1];
					}
					else{
						#还没有就认命了
						$newword[]=$word;
					}
				}
			}
		}
		else{
			$newword[]=$word;
		}

	}
	return implode("+",$newword);
}

function preSandhi($word){
	$sandhi2[] = array("a" => "ṃ", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>1.0);
	$sandhi2[] = array("a" => "ṃ", "b" => "hi", "c" => "ñhi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>1.0);

	$sandhi2[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
	$sandhi2[] = array("a" => "e", "b" => "iti", "c" => "eti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
	$sandhi2[] = array("a" => "i", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
	$sandhi2[] = array("a" => "o", "b" => "iti", "c" => "oti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
	$sandhi2[] = array("a" => "u", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);
	$sandhi2[] = array("a" => "ṃ", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.99999);

	$sandhi2[] = array("a" => "ī", "b" => "eva", "c" => "iyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ī", "b" => "eva", "c" => "īyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "u", "b" => "eva", "c" => "uyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ṃ", "b" => "eva", "c" => "ṃyeva", "len" => 5, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "i", "b" => "eva", "c" => "yeva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "o", "b" => "eva", "c" => "ova", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ṃ", "b" => "eva", "c" => "meva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "u", "b" => "eva", "c" => "veva", "len" => 4, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "a", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "e", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

	$sandhi2[] = array("a" => "a", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ā", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "e", "b" => "api", "c" => "epi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "i", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ī", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "o", "b" => "api", "c" => "opi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "u", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ū", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "u", "b" => "api", "c" => "upi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);
	$sandhi2[] = array("a" => "ṃ", "b" => "api", "c" => "mpi", "len" => 3, "adj_len" => 0, "advance" => false,"cf"=>0.9999);

	$newWord = "";
	$firstWord=$word;
	do {
		$isFound = false;
		foreach ($sandhi2 as $key => $sandhi) {
			# code...
			$len = $sandhi["len"];
			$end = mb_substr($firstWord, 0 - $len, null, "UTF-8");
			if ($end == $sandhi["c"]) {
				$word1 = mb_substr($firstWord, 0, mb_strlen($firstWord, "UTF-8") - $len, "UTF-8") .$sandhi["a"];
				$word2 = $sandhi["b"];
				$newWord = $word2 . "+" .$newWord;
				$firstWord = $word1;
				$isFound=true;
			break;
			}
		}
	} while ($isFound);
	$newWord = $firstWord . "+" .$newWord;
	return mb_substr($newWord,0,-1, "UTF-8");
}