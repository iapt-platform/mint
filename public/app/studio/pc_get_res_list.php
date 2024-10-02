<?php
$get_book = $_GET["book"];
$get_par_list = $_GET["par_list"];
$get_res_type = $_GET["res_type"];

include "./config.php";
include "./_pdo.php";
$countInsert = 0;
$parList = str_getcsv($get_par_list, ',');

$parstring = "";
for ($i = 0; $i < count($parList); $i++) {
    $parstring .= "'" . $parList[$i] . "'";
    if ($i < count($parList) - 1) {
        $parstring .= ",";
    }
}

$outXml = "<index>";
echo $outXml;
switch ($get_res_type) {
    case "translate":
        $tableName = "data";
        break;
    case "wbw":
        $tableName = "main";
        break;

}
$db_file = "../appdata/palicanon/" . $get_res_type . "/" . $get_book . "_" . $get_res_type . ".db3";

//open database
PDO_Connect("$db_file");
//for($iPar=0;$iPar<count($parList);$iPar++)
{

    //$query = "select * FROM ".$tableName." where \"paragraph\"=".$PDO->quote($parList[$iPar]);
    $query = "select * FROM " . $tableName . " where paragraph in (" . $parstring . ")";

    $Fetch = PDO_FetchAll($query);
    $iFetch = count($Fetch);

    if ($iFetch > 0) {
        for ($i = 0; $i < $iFetch; $i++) {
            $outXml = "<res>";
            $outXml = $outXml . "<book>$get_book</book>";
            $outXml = $outXml . "<par>" . $Fetch[$i]["paragraph"] . "</par>";
            $outXml = $outXml . "<type>$get_res_type</type>";
            $outXml = $outXml . "<language>" . $Fetch[$i]["language"] . "</language>";
            $outXml = $outXml . "<author>" . $Fetch[$i]["author"] . "</author>";
            $outXml = $outXml . "<editor>" . $Fetch[$i]["editor"] . "</editor>";
            $outXml = $outXml . "<revision>" . $Fetch[$i]["revision"] . "</revision>";
            $outXml = $outXml . "<edition>" . $Fetch[$i]["edition"] . "</edition>";
            $outXml = $outXml . "<subver>" . $Fetch[$i]["subver"] . "</subver>";
            switch ($get_res_type) {
                case "translate":
                    $outXml = $outXml . "<text>" . $Fetch[$i]["toc"] . "</text>";
                    break;
            }
            $outXml = $outXml . "</res>";
            echo $outXml;
        }
    }
}
/*查询结束*/

$outXml = "</index>";
echo $outXml;
