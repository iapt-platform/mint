<?php
include "./_pdo.php";
$mode = $_GET["mode"];
switch ($mode) {
    case "xml":
        $xmlFileName = $_GET["filename"];
        break;
    case "word":
        $word = $_GET["word"];
        break;
    case "words":
        break;
}

$dictFileName = $_GET["dict"];

if ($mode == "") {
    $mode = "xml";
}

$countInsert = 0;
$wordlist = array();
switch ($mode) {
    case "xml":
        $xml = simplexml_load_file($xmlFileName);
        //get word list from xml documnt
        $wordsSutta = $xml->xpath('//word');

        /*prepare words from xmldoc and remove same words*/
        foreach ($wordsSutta as $ws) {
            $pali = $ws->pali;
            $arrlength = count($wordlist);
            $found = false;
            for ($x = 0; $x < $arrlength; $x++) {
                if (strcmp($pali, $wordlist[$x]) == 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $srcWord = str_replace("-", "", lcfirst($pali));
                $srcWord = str_replace("’", "", lcfirst($pali));
                $srcWord = str_replace(".", "", lcfirst($pali));
                $srcWord = str_replace("ŋ", "ṃ", lcfirst($pali));
                $wordlist[$countInsert] = str_replace("-", "", lcfirst($pali)); /*首字符转为小写 first letter convert to lowercase */
                $countInsert++;
            }
        }
        $arrlength = count($wordlist); /*size of word list*/
        break;
    case "word":
        $wordlist[0] = $word;
        $arrlength = 1;
        break;
    case "words":
        break;
    default:
}

/*
prepare case ending table
in the csv file,one record have one kinds of case
so we combine the same form of case ending
 */
$caseRowCounter = 0;
$casetable = array();
if (($handle = fopen('caseend.csv', 'r')) !== false) {
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        $find = false;
        for ($i = 0; $i < count($casetable); $i++) {
            if ($casetable[$i][0] == $data[0] && $casetable[$i][1] == $data[1]) {
                $casetable[$i][2] = $casetable[$i][2] . "$" . $data[2];
                $find = true;
                break;
            }
        }
        if ($find == false) {
            $casetable[$caseRowCounter] = $data;
            $caseRowCounter++;
        }
    }
}

$outXml = "<wordlist>";
$db_path = "dict/";
$db_file = $db_path . $dictFileName;

//open database
PDO_Connect("$db_file");
for ($x = 0; $x < $arrlength; $x++) {
    if (mb_strlen($wordlist[$x]) > 1) {
        //先直接查，查不到再用语尾表查
        $query = "select * from dict where \"pali\"='" . $wordlist[$x] . "' ORDER BY rowid DESC ";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $outXml = $outXml . "<word>";
                $outXml = $outXml . "<pali>" . $wordlist[$x] . "</pali>";
                $outXml = $outXml . "<type>" . $Fetch[$i]["type"] . "</type>";
                $outXml = $outXml . "<gramma>" . $Fetch[$i]["gramma"] . "</gramma>";
                $outXml = $outXml . "<parent>" . $Fetch[$i]["parent"] . "</parent>";
                $outXml = $outXml . "<mean>" . $Fetch[$i]["mean"] . "</mean>";
                $outXml = $outXml . "<factors>" . $Fetch[$i]["factors"] . "</factors>";
                $outXml = $outXml . "<factormean>" . $Fetch[$i]["factormean"] . "</factormean>";
                $outXml = $outXml . "</word>";
            }
        } /*直接查询结束*/

        /*全部查询结束*/
    }
}

$outXml = $outXml . "</wordlist>";
echo $outXml;
