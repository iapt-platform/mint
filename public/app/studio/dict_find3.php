<?php
//查询参考字典
require_once '../public/casesuf.inc';
require_once 'dict_find_un.inc';
require_once 'sandhi.php';
require_once "../config.php";
require_once "../public/_pdo.php";

require_once '../public/load_lang.php';

$op = $_GET["op"];
$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;

$count_return = 0;
$dict_list = array();

global $PDO;
function myfunction($v1, $v2)
{
    return $v1 . "+" . $v2;
}

/*
查找某个单词是否在现有词典出现
 */
function isExsit($word)
{
    global $PDO;
    $query = "select count(*) as co from dict where \"word\" = " . $PDO->quote($word);
    $row = PDO_FetchOne($query);
    if ($row[0] == 0) {
        return false;
    } else {
        return true;
    }
}

/*
 *自动拆分复合词
 *功能：将一个单词拆分为两个部分
 *输入：想要拆的词
 *输出：数组，第一个为前半部分，第二个为后半部分，前半部分是在现有字典里搜索到的。
 *范例：
while(($split=mySplit($splitWord))!==FALSE){
array_push($part,$split[0]);
$splitWord=$split[1];
}
循环结束后$part里放的就是拆分结果

算法：从最后一个字母开始，一次去掉一个字母，然后在现有字典里搜索剩余的部分（前半部分）
如果搜索到，就返回。第二次，将剩余的部分，也就是后半部分应用相同的算法。
直到单词长度小于5
中间考虑了连音规则：
~a+i~=~i~
在拆分的时候要补上前面的元音
有时后面的词第一个辅音会重复
word+tha~=wordttha~
需要去掉后面的单词的一个辅音

 */
function mySplit($strWord)
{

    $doubleword = "kkggccjjṭṭḍḍttddppbb";
    $len = mb_strlen($strWord, "UTF-8");
    if ($len > 5) {
        for ($i = $len - 1; $i > 3; $i--) {
            $str1 = mb_substr($strWord, 0, $i, "UTF-8");
            $str2 = mb_substr($strWord, $i, null, "UTF-8");
            if (isExsit($str1)) {
                //如果字典里存在，返回拆分结果
                $left2 = mb_substr($str2, 0, 2, "UTF-8");
                //如果第二个部分有双辅音，去掉第一个辅音。因为巴利语中没有以双辅音开头的单词。
                if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                    $str2 = mb_substr($str2, 1, null, "UTF-8");
                }
                return array($str1, $str2);
            } else {
                //补上结尾的a再次查找
                $str1 = $str1 . "a";
                if (isExsit($str1)) {

                    $left2 = mb_substr($str2, 0, 2, "UTF-8");
                    if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                        $str2 = mb_substr($str2, 1, null, "UTF-8");
                    }
                    return array($str1, $str2);
                }
            }
        }
        //如果没找到。将ā变为a后再找。因为两个a复合后会变成ā
        if (mb_substr($strWord, 0, 1, "UTF-8") == "ā") {
            $strWord = 'a' . mb_substr($strWord, 1, null, "UTF-8");
            for ($i = $len - 1; $i > 3; $i--) {
                $str1 = mb_substr($strWord, 0, $i, "UTF-8");
                $str2 = mb_substr($strWord, $i, null, "UTF-8");
                //echo "$str1 + $str2 = ";
                if (isExsit($str1)) {
                    //echo "match";
                    $left2 = mb_substr($str2, 0, 2, "UTF-8");
                    if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                        $str2 = mb_substr($str2, 1, null, "UTF-8");
                    }
                    return array($str1, $str2);
                } else {
                    $str1 = $str1 . "a";
                    if (isExsit($str1)) {
                        //echo "match";
                        $left2 = mb_substr($str2, 0, 2, "UTF-8");
                        if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                            $str2 = mb_substr($str2, 1, null, "UTF-8");
                        }
                        return array($str1, $str2);
                    }
                }
            }
        }
        //如果没找到将开头的e变为i再次查找
        if (mb_substr($strWord, 0, 1, "UTF-8") == "e") {
            $strWord = 'i' . mb_substr($strWord, 1, null, "UTF-8");
            for ($i = $len - 1; $i > 3; $i--) {
                $str1 = mb_substr($strWord, 0, $i, "UTF-8");
                $str2 = mb_substr($strWord, $i, null, "UTF-8");
                if (isExsit($str1)) {
                    //echo "match";
                    $left2 = mb_substr($str2, 0, 2, "UTF-8");
                    if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                        $str2 = mb_substr($str2, 1, null, "UTF-8");
                    }
                    return array($str1, $str2);
                } else {
                    $str1 = $str1 . "a";
                    if (isExsit($str1)) {
                        $left2 = mb_substr($str2, 0, 2, "UTF-8");
                        if (mb_strpos($doubleword, $left2, 0, "UTF-8") !== false) {
                            $str2 = mb_substr($str2, 1, null, "UTF-8");
                        }
                        return array($str1, $str2);
                    }
                }
            }
        }
    }
    return (false);
}

function mySplit2($strWord)
{
    $output = array();
    $len = mb_strlen($strWord, "UTF-8");
    if ($len > 2) {
        for ($i = $len - 1; $i > 1; $i--) {
            foreach ($sandhi as $row) {
                if (mb_substr($strWord, $i, $row[3], "UTF-8") == $row[2]) {
                    $str1 = mb_substr($strWord, 0, $i - 1, "UTF-8") . $row[0];
                    $str2 = $row[1] . mb_substr($strWord, $i + $row[2], null, "UTF-8");
                    if (isExsit($str1)) {
                        array_push($output, array($str1, $str2));
                    }
                }

            }

        }

    }
    return ($output);
}

switch ($op) {
    case "pre": //预查询
        PDO_Connect(_FILE_DB_REF_INDEX_);
        echo "<wordlist>";
        $query = "select word,count from dict where \"eword\" like " . $PDO->quote($word . '%') . " OR \"word\" like " . $PDO->quote($word . '%') . "  limit 0,100";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $outXml = "<word>";
                $word = $Fetch[$i]["word"];
                $outXml = $outXml . "<pali>$word</pali>";
                $outXml = $outXml . "<count>" . $Fetch[$i]["count"] . "</count>";
                $outXml = $outXml . "</word>";
                echo $outXml;
            }
        }
        echo "</wordlist>";
        break;
    case "search":

        PDO_Connect(_FILE_DB_REF_);

        //直接查询
        $query = "select dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = " . $PDO->quote($word) . " limit 0,30";

        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        $count_return += $iFetch;
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $mean = $Fetch[$i]["mean"];
                $mean = str_replace("[[", "<a onclick=\"dict_jump(this)\">", $mean);
                $mean = str_replace("]]", "</a>", $mean);
                $dictid = $Fetch[$i]["dict_id"];
                $dict_list[$dictid] = $Fetch[$i]["shortname"];
                $outXml = "<div class='dict_word'>";
                $outXml = $outXml . "<a name='ref_dict_$dictid'></a>";
                $outXml = $outXml . "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
                $outXml = $outXml . "<div class='mean'>{$mean}</div>";
                $outXml = $outXml . "</div>";
                echo $outXml;
            }
        }
        if (substr($word, 0, 1) == "_" && substr($word, -1, 1) == "_") {
            echo "<div id='dictlist'>";
            foreach ($dict_list as $x => $x_value) {
                echo "<a href='#ref_dict_$x'>$x_value</a>";
            }
            echo "</div>";
            break;
        }
        //去除尾查
        $newWord = array();
        for ($row = 0; $row < count($case); $row++) {
            $len = mb_strlen($case[$row][1], "UTF-8");
            $end = mb_substr($word, 0 - $len, null, "UTF-8");
            if ($end == $case[$row][1]) {
                $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $case[$row][0];
                if ($base != $word) {
                    $gr = "<a onclick=\"dict_jump(this)\">" . str_replace("$", "</a> &nbsp;&nbsp;<a  onclick=\"dict_jump(this)\">", $case[$row][2]) . "</a>";
                    if (isset($newWord[$base])) {
                        $newWord[$base] .= "<br />" . $gr;
                    } else {
                        $newWord[$base] = $gr;
                    }
                }
            }
        }

        if (count($newWord) > 0) {
            foreach ($newWord as $x => $x_value) {
                $query = "select dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = " . $PDO->quote($x) . " limit 0,30";
                $Fetch = PDO_FetchAll($query);
                $iFetch = count($Fetch);
                $count_return += $iFetch;
                if ($iFetch > 0) {
                    //语法信息
                    foreach ($_local->grammastr as $gr) {
                        $x_value = str_replace($gr->id, $gr->value, $x_value);
                    }
                    echo $x . ":<div class='dict_find_gramma'>" . $x_value . "</div>";
                    for ($i = 0; $i < $iFetch; $i++) {
                        $mean = $Fetch[$i]["mean"];
                        $dictid = $Fetch[$i]["dict_id"];
                        $dict_list[$dictid] = $Fetch[$i]["shortname"];
                        $outXml = "<div class='dict_word'>";
                        $outXml = $outXml . "<a name='ref_dict_$dictid'></a>";
                        $outXml = $outXml . "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
                        $outXml = $outXml . "<div class='mean'>" . $mean . "</div>";
                        $outXml = $outXml . "</div>";
                        echo $outXml;
                    }
                }
            }
        }
        //去除尾查结束

        //模糊查
        //模糊查结束
        //查连读词
        if ($count_return < 2) {
            echo "Junction:<br />";
            $newWord = array();
            for ($row = 0; $row < count($un); $row++) {
                $len = mb_strlen($un[$row][1], "UTF-8");
                $end = mb_substr($word, 0 - $len, null, "UTF-8");
                if ($end == $un[$row][1]) {
                    $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $un[$row][0];
                    $arr_un = explode("+", $base);
                    foreach ($arr_un as $oneword) {
                        echo "<a onclick='dict_pre_word_click(\"$oneword\")'>$oneword</a> + ";
                    }
                    echo "<br />";
                }
            }
        }

        //拆复合词
        $splitWord = $word;
        $part = array();
        if ($count_return < 2) {
            echo "<div>Try to split comp:</div>";
            while (($split = mySplit($splitWord)) !== false) {
                array_push($part, $split[0]);
                $splitWord = $split[1];
            }
            if (count($part) > 0) {
                array_push($part, $splitWord);
                $newPart = ltrim(array_reduce($part, "myfunction"), "+");
                echo "<div>{$newPart}</div>";
            }
        }
        echo "不满意吗？试试强力拆分。";
        echo "<button onclick='dict_turbo_split(\"{$word}\")'>Turbo Split</button>";
        //拆复合词结束

        //查内容
        if ($count_return < 4) {
            $word1 = $org_word;
            $wordInMean = "%$org_word%";
            echo "include $org_word:<br />";
            $query = "select dict.dict_id,dict.word,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"mean\" like " . $PDO->quote($wordInMean) . " limit 0,30";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            $count_return += $iFetch;
            if ($iFetch > 0) {
                for ($i = 0; $i < $iFetch; $i++) {
                    $mean = $Fetch[$i]["mean"];
                    $pos = mb_stripos($mean, $word, 0, "UTF-8");
                    if ($pos) {
                        if ($pos > 20) {
                            $start = $pos - 20;
                        } else {
                            $start = 0;
                        }
                        $newmean = mb_substr($mean, $start, 100, "UTF-8");
                    } else {
                        $newmean = $mean;
                    }
                    $pos = mb_stripos($newmean, $word1, 0, "UTF-8");
                    $head = mb_substr($newmean, 0, $pos, "UTF-8");
                    $mid = mb_substr($newmean, $pos, mb_strlen($word1, "UTF-8"), "UTF-8");
                    $end = mb_substr($newmean, $pos + mb_strlen($word1, "UTF-8"), null, "UTF-8");
                    $heigh_light_mean = "$head<hl>$mid</hl>$end";
                    $outXml = "<div class='dict_word'>";
                    $outXml = $outXml . "<div class='word'>" . $Fetch[$i]["word"] . "</div>";
                    $outXml = $outXml . "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
                    $outXml = $outXml . "<div class='mean'>" . $heigh_light_mean . "</div>";
                    $outXml = $outXml . "</div>";
                    echo $outXml;
                }
            }
        }

        echo "<div id='dictlist'>";
        foreach ($dict_list as $x => $x_value) {
            echo "<a href='#ref_dict_$x'>$x_value</a>";
        }
        echo "</div>";

        break;
}
