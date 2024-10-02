<?php
//全文搜索
require_once '../config.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";
require_once "../search/word_function.php";

define("word_limit", 12); //最大单词数量

$resulte = array();

if (isset($_GET["word"])) {
    $word = mb_strtolower($_GET["word"], 'UTF-8');
} else {
    echo json_encode($resulte, JSON_UNESCAPED_UNICODE);
    exit;
}
_load_book_index();

$count_return = 0;
$dict_list = array();

//计算某词在三藏中出现的次数
$arrRealWordList = countWordInPali($word, true, word_limit);

$countWord = count($arrRealWordList);
if ($countWord == 0) {
    echo "<p>没有查到。可能是拼写有问题。</p>";
    exit;
}
$strQueryWordId = "("; //实际出现的单词id查询字串
$aQueryWordList = array(); //id 为键 拼写为值的数组
$aShowWordList = array(); //拼写为键 个数为值的数组
$aShowWordIdList = array(); //拼写为键 值Id的数组
for ($i = 0; $i < $countWord; $i++) {
    $value = $arrRealWordList[$i];
    $strQueryWordId .= "'{$value["id"]}',";
    $aQueryWordList[$value["id"]] = $value["word"];
    $aShowWordList[$value["word"]] = $value["count"];
    $aShowWordIdList[$value["word"]] = $value["id"];
}
$strQueryWordId = mb_substr($strQueryWordId, 0, mb_strlen($strQueryWordId, "UTF-8") - 1, "UTF-8");
$strQueryWordId .= ")";

//显示单词列表

arsort($aShowWordList);
$i = 0;
$wordlist = array();
$wordlist_index = array();

$sutta = array();
$vinaya = array();
$abhidhamma = array();
$anna = array();

foreach ($aShowWordList as $x => $x_value) {
    $wordid = $aShowWordIdList[$x];
    $wordlist[] = $x;
    $wordlist_index[$x] = $i;

    $sutta[] = 0;
    $vinaya[] = 0;
    $abhidhamma[] = 0;
    $anna[] = 0;
    //echo $x.":".$x_value."<br />";
    $i++;
}
$resulte["wordlist"] = $wordlist;

//查找这些词出现在哪些书中
$arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
PDO_Connect(_FILE_DB_BOOK_WORD_);
if (isset($booklist)) {
    foreach ($booklist as $oneBook) {
        $aInputBook["{$oneBook}"] = 1;
    }
}
$query = "SELECT book, wordindex,count from "._TABLE_BOOK_WORD_." where \"wordindex\" in $strQueryWordId ";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);

$worddata = array();

if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $book = $Fetch[$i]["book"];

        $t1 = $arrBookType[$book - 1]->c1;
        $t2 = $arrBookType[$book - 1]->c2;
        switch ($t1) {
            case "sutta":
                $x = $aQueryWordList[$Fetch[$i]["wordindex"]];
                $sutta[$wordlist_index[$x]] += $Fetch[$i]["count"];
                break;
            case "vinaya":
                $x = $aQueryWordList[$Fetch[$i]["wordindex"]];
                $vinaya[$wordlist_index[$x]] += $Fetch[$i]["count"];
                break;
            case "abhidhamma":
                $x = $aQueryWordList[$Fetch[$i]["wordindex"]];
                $abhidhamma[$wordlist_index[$x]] += $Fetch[$i]["count"];
                break;
            case "anna":
                $x = $aQueryWordList[$Fetch[$i]["wordindex"]];
                $anna[$wordlist_index[$x]] += $Fetch[$i]["count"];
                break;
        }

    }
}
$worddata[] = array("name" => "anna", "data" => $anna);
$worddata[] = array("name" => "abhidhamma", "data" => $abhidhamma);
$worddata[] = array("name" => "vinaya", "data" => $vinaya);
$worddata[] = array("name" => "sutta", "data" => $sutta);

$resulte["data"] = $worddata;

echo json_encode($resulte, JSON_UNESCAPED_UNICODE);
