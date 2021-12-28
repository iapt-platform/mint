<?php
$currBook = $_GET["book"];
$currParagraph = $_GET["paragraph"];
$author = $_GET["author"];
$editor = $_GET["editor"];

$currVer = $_GET["ver"];

include "./config.php";
include "./_pdo.php";
$countInsert = 0;
$wordlist = array();
$tempFile = "temp.txt";

$guid = com_create_guid();
$outXml = "<pkg>";
$outXml .= "<block>";
$outXml .= "<info><id>$guid</id><book>$currBook</book><paragraph>$currParagraph</paragraph><ver>$currVer</ver><type>wbw</type><author>pcds</author><language>com</language></info>";
$outXml .= "<data>";
echo $outXml;

$db_file = "../appdata/palicanon/templet/" . $currBook . "_tpl.db3";
//open database
PDO_Connect("$db_file");

$query = "SELECT * FROM \"main\" WHERE \"book\" = " . $PDO->quote($currBook) . " AND \"paragraph\" = " . $PDO->quote($currParagraph) . "  ORDER BY vri ";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $outXml = "<word>";
        $outXml = $outXml . "<id>" . $Fetch[$i]["wid"] . "</id>";
        $outXml = $outXml . "<pali>" . $Fetch[$i]["word"] . "</pali>";
        $outXml = $outXml . "<real>" . $Fetch[$i]["real"] . "</real>";
        $outXml = $outXml . "<type>" . $Fetch[$i]["type"] . "</type>";
        $outXml = $outXml . "<gramma>" . $Fetch[$i]["gramma"] . "</gramma>";
        $outXml = $outXml . "<case>" . $Fetch[$i]["type"] . "#" . $Fetch[$i]["gramma"] . "</case>";
        $outXml = $outXml . "<mean>" . $Fetch[$i]["mean"] . "</mean>";
        $outXml = $outXml . "<note>" . $Fetch[$i]["note"] . "</note>";
        $outXml = $outXml . "<org>" . $Fetch[$i]["part"] . "</org>";
        $outXml = $outXml . "<om>" . $Fetch[$i]["partmean"] . "</om>";
        $outXml = $outXml . "<bmc>" . $Fetch[$i]["bmc"] . "</bmc>";
        $outXml = $outXml . "<bmt>" . $Fetch[$i]["bmt"] . "</bmt>";
        $outXml = $outXml . "<un>" . $Fetch[$i]["un"] . "</un>";
        $outXml = $outXml . "<style>" . $Fetch[$i]["style"] . "</style>";
        $outXml = $outXml . "</word>";
        echo $outXml;
    }
}
/*直接查询结束*/

$outXml = "</data></block></pkg>";
echo $outXml;
