<?php
//全文搜索 预查询
require_once '../path.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../search/word_function.php";

$word = mb_strtolower($_GET["key"], 'UTF-8');
$arrWordList = str_getcsv($word, " ");

$searching = $arrWordList[count($arrWordList) - 1];
$dbfile = _FILE_DB_WORD_INDEX_;
PDO_Connect("" . $dbfile);

if (count($arrWordList) > 1) {
    //echo "<div>";
    foreach ($arrWordList as $oneword) {
        //echo $oneword."+";
    }

} else {
    $query = "SELECT word,word_en,count,bold FROM wordindex WHERE word_en  like  ? OR word like ? limit 0,20";
    $Fetch = PDO_FetchAll($query, array("{$searching}%", "{$searching}%"));
    if (count($Fetch) < 10) {
        $Fetch1 = PDO_FetchAll($query, array("%{$searching}%", "%{$searching}%"));
        foreach ($Fetch1 as $key => $value) {
            #
            $Fetch[] = $value;
        }
    }
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
