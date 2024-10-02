<?php
require 'checklogin.inc';
include "./config.php";
include "./_pdo.php";

$countInsert = 0;
$wordlist = array();
$tempFile = "temp.txt";

//$input = file_get_contents("php://input"); //
//$inputWordList=explode("$",$input);
$arrlength = 0; /*size of word list*/
//$dictType=$inputWordList[0];
//$dictFileName=$inputWordList[1];
//$parentLevel=$inputWordList[2];
$dictType = $_POST["type"];
$dictFileName = $_POST["filename"];
$parentLevel = $_POST["level"];
$wordlist = json_decode($_POST["data"], false);

switch ($dictType) {
    case "user":
        $dictDir = $dir_user_base . $userid . $dir_dict_user . '/';
        break;
    case "sys":
        $dictDir = $dir_dict_system;
        break;
    case "third":
        $dictDir = $dir_dict_3rd;
        break;
}

if ($dictFileName == "wbw") {
    $dictFileName = $file_dict_wbw_default;
}
if ($dictFileName == "user_default") {
    $dictFileName = $file_dict_user_default;
}
/*
for($i=3;$i<count($inputWordList);$i++){
if(!empty($inputWordList[$i])){
$words[$inputWordList[$i]]=1;
}
}
foreach($words as $word=>$value){
$wordlist[$arrlength]=$word;
$arrlength++;
}
 */
$arrlength = count($wordlist);

echo "<wordlist>";

$db_path = $dictDir;
$db_file = $db_path . $dictFileName;

//open database
PDO_Connect("$db_file");
global $PDO;
for ($x = 0; $x < $arrlength; $x++) {
    if (mb_strlen($wordlist[$x]->word) > 1) {
        //直接查
        if ($dictFileName == "wbw") {
            if ($wordlist[$x]->level == 1) {
                $query = "select * from dict where \"type\"<> \"pali\"=" . $PDO->quote($wordlist[$x]->word) . "  AND ( type <> '.n.' AND  type <> '.ti.' AND  type <> '.adj.'  AND  type <> '.pron.'  AND  type <> '.v.' )   ORDER BY rowid DESC";
            } else {
                $query = "select * from dict where \"pali\"=" . $PDO->quote($wordlist[$x]->word) . " ORDER BY rowid DESC";
            }
        } else {
            if ($wordlist[$x]->level == 1) {
                $query = "select * from dict where \"pali\"=" . $PDO->quote($wordlist[$x]->word) . "  AND ( type <> '.n.' AND  type <> '.ti.' AND  type <> '.adj.'  AND  type <> '.pron.'  AND  type <> '.v.' )";
            } else {
                $query = "select * from dict where \"pali\"=" . $PDO->quote($wordlist[$x]->word) . "";
            }
        }

        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $outXml = "<word>";
                $outXml .= "<id>" . $Fetch[$i]["id"] . "</id>";
                if (isset($Fetch[$i]["guid"])) {$outXml .= "<guid>" . $Fetch[$i]["guid"] . "</guid>";} else { $outXml .= "<guid></guid>";}
                $outXml .= "<pali>" . $Fetch[$i]["pali"] . "</pali>";
                $outXml .= "<type>" . $Fetch[$i]["type"] . "</type>";
                $outXml .= "<gramma>" . $Fetch[$i]["gramma"] . "</gramma>";
                $outXml .= "<parent>" . $Fetch[$i]["parent"] . "</parent>";
                $outXml .= "<mean>" . $Fetch[$i]["mean"] . "</mean>";
                $outXml .= "<factors>" . $Fetch[$i]["factors"] . "</factors>";
                if (isset($Fetch[$i]["part_id"])) {$outXml .= "<part_id>" . $Fetch[$i]["part_id"] . "</part_id>";} else { $outXml .= "<part_id></part_id>";}
                $outXml .= "<factormean>" . $Fetch[$i]["factormean"] . "</factormean>";
                if (isset($Fetch[$i]["note"])) {
                    $outXml .= "<note>" . $Fetch[$i]["note"] . "</note>";
                } else {
                    $outXml .= "<note></note>";
                }
                if (isset($Fetch[$i]["status"])) {
                    $outXml .= "<status>" . $Fetch[$i]["status"] . "</status>";
                } else {
                    $outXml .= "<status>256</status>";
                }
                if (isset($Fetch[$i]["enable"])) {
                    $outXml .= "<enable>" . $Fetch[$i]["enable"] . "</enable>";
                } else {
                    $outXml .= "<enable>TURE</enable>";
                }

                if (isset($Fetch[$i]["dict_name"])) {
                    $outXml .= "<dict_name>" . $Fetch[$i]["dict_name"] . "</dict_name>";
                } else {
                    $outXml .= "<dict_name>unkow</dict_name>";
                }

                if (isset($Fetch[$i]["language"])) {
                    $outXml .= "<language>" . $Fetch[$i]["language"] . "</language>";
                } else {
                    $outXml .= "<language>en</language>";
                }

                if (isset($Fetch[$i]["time"])) {
                    $outXml .= "<time>" . $Fetch[$i]["time"] . "</time>";
                } else {
                    $outXml .= "<time>0</time>";
                }
                $outXml .= "</word>";
                echo $outXml;
            }
        }
/*直接查询结束*/

    }
}

$outXml = "</wordlist>";
echo $outXml;
//fwrite($fpTemp, $outXml);
//fclose($fpTemp);

//$fpTemp = fopen($tempFile, "r") or die("Unable to open file!");
//$tempText = fread($fpTemp,filesize($tempFile));
//fclose($fpTemp);

//echo $tempText;
