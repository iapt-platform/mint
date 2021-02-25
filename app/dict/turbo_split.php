<?php
require_once '../public/casesuf.inc';
//require_once '../studio/dict_find_un.inc';
//require_once '../studio/sandhi.php';
require_once "../path.php";
require_once "../public/_pdo.php";
// open word part db
global $dbh;
$dns = "" . _FILE_DB_PART_;
$dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

global $path;
global $confidence;
global $result;
global $part;
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
$sandhi[] = array("a" => "", "b" => "", "c" => "", "len" => 0, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "a", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ā", "b" => "ā", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "ā", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ā", "b" => "a", "c" => "ā", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "e", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "i", "c" => "i", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "o", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "u", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "a", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "u", "c" => "ū", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "u", "c" => "u", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "ī", "c" => "ī", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "ū", "c" => "ū", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "i", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "e", "b" => "a", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "i", "c" => "ī", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "e", "c" => "e", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "a", "c" => "ya", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "atth", "c" => "atth", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "taṃ", "b" => "n", "c" => "tann", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "api", "c" => "mpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "eva", "c" => "meva", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[o]", "b" => "iva", "c" => "ova", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "o", "b" => "a", "c" => "o", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "ādi", "c" => "ādi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a[ānaṃ]", "b" => "a", "c" => "ānama", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "a", "c" => "ma", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ṃ", "b" => "a", "c" => "m", "len" => 1, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "ā", "c" => "mā", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "u", "c" => "mu", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "[ṃ]", "b" => "h", "c" => "ñh", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ā", "b" => "[ṃ]", "c" => "am", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "[ṃ]", "c" => "im", "len" => 2, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ati", "b" => "tabba", "c" => "atabba", "len" => 6, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ati", "b" => "tabba", "c" => "itabba", "len" => 6, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "iti", "b" => "a", "c" => "icca", "len" => 4, "adj_len" => 0, "advance" => false);

$sandhi[] = array("a" => "uṃ", "b" => "a", "c" => "uma", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u[ūnaṃ]", "b" => "a", "c" => "ūnama", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī[īnaṃ]", "b" => "a", "c" => "īnama", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "su", "b" => "a", "c" => "sva", "len" => 3, "adj_len" => 0, "advance" => false);

#other sandhi rule. can be use but program will be slow down
#其他连音规则，如果使用则会让程序运行变慢

$sandhi[] = array("a" => "ā", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "iti", "c" => "āti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "e", "b" => "iti", "c" => "eti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "iti", "c" => "īti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "o", "b" => "iti", "c" => "oti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ū", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "iti", "c" => "ūti", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ṃ", "b" => "iti", "c" => "nti", "len" => 3, "adj_len" => 0, "advance" => false);

$sandhi[] = array("a" => "ṃ", "b" => "ca", "c" => "ñca", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ṃ", "b" => "cāti", "c" => "ñcāti", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ṃ", "b" => "cet", "c" => "ñcet", "len" => 4, "adj_len" => 0, "advance" => false);

/*
$sandhi[] = array("a" => "a", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ā", "b" => "eva", "c" => "āyeva", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "e", "b" => "eva", "c" => "eva", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "eva", "c" => "yeva", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "eva", "c" => "iyeva", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "eva", "c" => "īyeva", "len" => 5, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "o", "b" => "eva", "c" => "ova", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "eva", "c" => "veva", "len" => 3, "adj_len" => 0, "advance" => false);

$sandhi[] = array("a" => "a", "b" => "eva", "c" => "evā", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "e", "b" => "eva", "c" => "evā", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "eva", "c" => "yevā", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "eva", "c" => "yevā", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "eva", "c" => "iyevā", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "eva", "c" => "īyevā", "len" => 4, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "o", "b" => "eva", "c" => "ovā", "len" => 4, "adj_len" => 0, "advance" => false);

$sandhi[] = array("a" => "ā", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "a", "b" => "api", "c" => "āpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "e", "b" => "api", "c" => "epi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ī", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "i", "b" => "api", "c" => "īpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "o", "b" => "api", "c" => "opi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ū", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "api", "c" => "ūpi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "u", "b" => "api", "c" => "upi", "len" => 3, "adj_len" => 0, "advance" => false);
$sandhi[] = array("a" => "ṃ", "b" => "api", "c" => "mpi", "len" => 3, "adj_len" => 0, "advance" => false);
 */
$sandhi[] = array("a" => "a", "b" => "a", "c" => "a", "len" => 1, "adj_len" => -1, "advance" => true);
$sandhi[] = array("a" => "ī", "b" => "", "c" => "i", "len" => 1, "adj_len" => 0, "advance" => true);

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

function dict_lookup($word)
{
    if (strlen($word) <= 1) {
        return 0;
    }
    global $case;
    global $dbh;
    $str = strstr($word, "[");
    if ($str === false) {
        $search = $word;
    } else {
        $search = $str;
    }
    $query = "SELECT weight from part where word = ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($search));
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        return $row[0];
    } else {
        //去除尾查
        $newWord = array();
        for ($row = 0; $row < count($case); $row++) {
            $len = mb_strlen($case[$row][1], "UTF-8");
            $end = mb_substr($word, 0 - $len, null, "UTF-8");
            if ($end == $case[$row][1]) {
                $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $case[$row][0];
                if ($base != $word) {
                    $newWord[$base] = 1;
                }
            }
        }
        #找到最高频的base
        $base_weight = 0;
        foreach ($newWord as $x => $x_value) {
            $query = "SELECT weight from part where word = ? ";
            $stmt = $dbh->prepare($query);
            $stmt->execute(array($x));
            $row = $stmt->fetch(PDO::FETCH_NUM);
            if ($row) {
                if ($row[0] > $base_weight) {
                    $base_weight = $row[0];
                }
            }
        }
        return $base_weight;
    }
}

/*
查找某个单词是否在现有词典出现
返回信心指数
look up single word in dictionary vocabulary
return the confidence value
 */
function isExsit($word, $adj_len = 0)
{

    global $auto_split_times;
    global $result;
    global $part;
    global $confidence;
    $auto_split_times++;

    if (isset($_POST["debug"])) {
        echo "<div>正在查询：{$word}</div>";
    }
    $isFound = false;
    $count = 0;
    if (isset($part["{$word}"])) {
        if ($part["{$word}"] > 0) {
            $isFound = true;
            $count = $part["{$word}"] + 1;
        }
    } else {
        $db = dict_lookup($word);

        //加入查询缓存
        $part["{$word}"] = $db;
        if ($db > 0) {
            if (isset($_POST["debug"])) {
                echo "查到：{$word}:{$db}个\n";
            }
            $isFound = true;
            $count = $db + 1;
        }
    }
//fomular of confidence value 信心值计算公式
    if ($isFound) {
        if (isset($confidence["{$word}"])) {
            $cf = $confidence["{$word}"];
        } else {
            $len = mb_strlen($word, "UTF-8") + $adj_len;
            $len_correct = 1.2;
            $count2 = 1.1 + pow($count, 1.18);
            $conf_num = pow(1 / $count2, pow(($len - 0.5), $len_correct));
            $cf = round(1 / (1 + 640 * $conf_num), 9);

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

    //达到最大搜索深度，返回
    if ($deep >= 16) {
        $word = "";
        $cf = 1.0;
        for ($i = 0; $i < $deep; $i++) {
            if (!empty($path[$i][0])) {
                $word .= $path[$i][0] . "+";
                if (isset($_POST["debug"])) {
                    $word .= "(" . $path[$i][1] . ")-";
                }
                $cf = $cf * $path[$i][1];
            }
        }
        $len = pow(mb_strlen($strWord, "UTF-8"), 3);
        $cf += (0 - $len) / ($len + 150);
        $word .= "{$strWord}";
        if ($forward == true) {
            $result[$word] = $cf;
        } else {
            $reverseWord = word_reverse($word);
            $result[$reverseWord] = $cf;
        }
        return;
    }
    //直接找到
    $confidence = isExsit($strWord, $adj_len);
    if ($confidence >= 0) {
        $output[] = array($strWord, "", $confidence);
    } else {
        $confidence = isExsit("[" . $strWord . "]");
        if ($confidence >= 0) {
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
                        continue;
                    }
                    if (mb_substr($strWord, $i - $row["len"], $row["len"], "UTF-8") == $row["c"]) {
                        $str1 = mb_substr($strWord, 0, $i - $row["len"], "UTF-8") . $row["a"];
                        $str2 = $row["b"] . mb_substr($strWord, $i, null, "UTF-8");
                        $confidence = isExsit($str1, $adj_len);
                        if ($row["advance"] == true) {
                            $confidence = $confidence * 0.99;
                        }
                        if ($confidence > $c_threshhold) {
                            $output[] = array($str1, $str2, $confidence, $row["adj_len"]);
                            if (isset($_POST["debug"])) {
                                echo "插入：{$str1}\n";
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
                        continue;
                    }
                    if (mb_substr($strWord, $i, $row["len"], "UTF-8") == $row["c"]) {
                        $str1 = mb_substr($strWord, 0, $i, "UTF-8") . $row["a"];
                        $str2 = $row["b"] . mb_substr($strWord, $i + $row["len"], null, "UTF-8");
                        $confidence = isExsit($str2, $adj_len);
                        if ($row["advance"] == true) {
                            $confidence = $confidence * 0.99;
                        }
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
                $cf = $cf + $part[2] * 0.1;
                if ($cf > $w_threshhold) {
                    if ($forward == true) {
                        $result[$word] = $cf;
                    } else {
                        $reverseWord = word_reverse($word);
                        $result[$reverseWord] = $cf;
                    }
                }
            } else {
                #接着切
                mySplit2($remainder, ($deep + 1), $express, $adj_len, $c_threshhold, $w_threshhold, $forward, $sandhi_advance);
            }
        }
    } else {
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
            $cf += (0 - $len) / ($len + 150);
        } else {
            $cf += (0 - $len) / ($len + 5);
        }
        if (isset($_POST["debug"])) {
            $word .= $strWord . "(0)";
        } else {
            $word .= $strWord;
        }

        if ($cf > $w_threshhold) {
            if ($forward == true) {
                $result[$word] = $cf;
            } else {
                $reverseWord = word_reverse($word);
                $result[$reverseWord] = $cf;
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
