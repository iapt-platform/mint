<?php
$currBook = $_GET["book"];

include "./config.php";
include "./_pdo.php";
$countInsert = 0;
$wordlist = array();

$outXml = "<index>";
echo $outXml;

//open database
PDO_Connect(_FILE_DB_PALITEXT_);
$query = "SELECT * FROM "._TABLE_PALI_TEXT_." where book = ?";

$Fetch = PDO_FetchAll($query,array($currBook));
$iFetch = count($Fetch);

if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $outXml = "<paragraph>";
        $outXml = $outXml . "<book>" . $Fetch[$i]["book"] . "</book>";
        $outXml = $outXml . "<par>" . $Fetch[$i]["paragraph"] . "</par>";
        $outXml = $outXml . "<level>" . $Fetch[$i]["level"] . "</level>";
        $outXml = $outXml . "<class>" . $Fetch[$i]["class"] . "</class>";
        $outXml = $outXml . "<title>" . mb_substr($Fetch[$i]["text"], 0, 50, "UTF-8") . "</title>";
        $outXml = $outXml . "<language>pali</language>";
        $outXml = $outXml . "<edition>CSCD4</edition>";
        $outXml = $outXml . "<subver></subver>";
        $outXml = $outXml . "</paragraph>";
        echo $outXml;
    }
}
/*查询结束*/

$outXml = "</index>";
echo $outXml;
