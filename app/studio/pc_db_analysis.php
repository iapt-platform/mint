<?php
include "./config.php";
include "./_pdo.php";

$get_book = $_GET["book"];
$boolList = str_getcsv($get_book, ','); /*生成書名數組*/

$countInsert = 0;
$wordlist = array();

$bookstring = "";
for ($i = 0; $i < count($boolList); $i++) {
    $bookstring .= "'" . $boolList[$i] . "'";
    if ($i < count($boolList) - 1) {
        $bookstring .= ",";
    }
}

//open database
PDO_Connect(_FILE_DB_RESRES_INDEX_);
$query = "select count(*) from (SELECT count() FROM \"toc\" WHERE (book in (" . $bookstring . ") and author='PCDS') group by book,par_num ) T"; /*查總數，并分類匯總*/
$allpar = PDO_FetchOne($query);
$query = "select count(*) from (SELECT count() FROM \"toc\" WHERE (book in (" . $bookstring . ") and author<>'PCDS') group by book,par_num ) T"; /*查有譯文的段落，并分類匯總——已完成*/
$finished = PDO_FetchOne($query);
$persent = $finished / $allpar;
print "总共：$allpar  <br />{$_local->gui->finished}：$finished  <br />百分比：$persent";
//$Fetch = PDO_FetchAll($query);
//$iFetch=count($Fetch);

/*查询结束*/
