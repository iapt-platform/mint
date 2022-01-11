<?php
//获取书的目录-索引 包含缩减的正文
require_once "../config.php";
include "../public/_pdo.php";

$currBook = $_GET["book"];
if (substr($currBook, 0, 1) == "p") {
    $currBook = substr($currBook, 1);
}

echo "<index>";

//open database
PDO_Connect(_FILE_DB_PALITEXT_);
$query = "SELECT * FROM "._TABLE_PALI_TEXT_." where book = ? order by paragraph ASC";

$Fetch = PDO_FetchAll($query,array($currBook));
$iFetch = count($Fetch);

if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $level = $Fetch[$i]["level"];
        if ($level == 100) {
            $level = 0;
        }
        echo "<paragraph>";
        echo "<book>{$Fetch[$i]["book"]}</book>";
        echo "<par>{$Fetch[$i]["paragraph"]}</par>";
        echo "<level>{$level}</level>";
        echo "<class>" . $Fetch[$i]["class"] . "</class>";
        echo "<title>{$Fetch[$i]["toc"]}</title>";
        echo "<language>pali</language>";
        echo "<author>cscd4</author>";
        echo "<edition>4</edition>";
        echo "<subver></subver>";
        echo "</paragraph>";
    }
}
/*查询结束*/
echo "</index>";
