<html>
<body>
<?php
#已经废弃
function inputWordIsEmpty($inWord)
{

    if (strcmp($inWord->org, '?') == 0 && strcmp($inWord->mean, '?') == 0 && strcmp($inWord->case, '?') == 0) {
        return true;
    }

    return false;
}
?>
<?php
include "./_pdo.php";

$xmlFileName = $_GET["filename"];

$xml = simplexml_load_file("books/" . $xmlFileName);
//open database

$db_file = "dict/tpdict.db";
PDO_Connect("$db_file");
//get word list from xml documnt and updata database

$wordsSutta = $xml->xpath('//word');
echo "word number:" . count($wordsSutta) . "<br>";
$countInsert = 0;
foreach ($wordsSutta as $ws) {
    $strPali = strtolower($ws->pali);
    $strPaliInEn = $strPali;
    $strOrg = $ws->org;
    $strMean = $ws->mean;
    $strGrama = $ws->case;
    if (inputWordIsEmpty($ws)) {
        continue;
    }

    $query = "select count(*) as rownum from tptdict where \"word\"='" . $ws->pali . "' AND \"org\"='" . $ws->org . "' AND \"mean\"='" . $ws->mean . "' AND \"gramma\"='" . $ws->case . "'";
    $Fetch = PDO_FetchAll($query);
    $FetchNum = $Fetch[0]["rownum"];
    if ($FetchNum == 0) {
        $query = "INSERT INTO tptdict ('id','worden','word', 'org', 'mean', 'gramma') VALUES (null,'" . $ws->pali . "','" . $ws->pali . "','" . $ws->org . "','" . $ws->mean . "','" . $ws->case . "')";
        $stmt = @PDO_Execute($query);
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            print_r($error[2]);
            //break;
        }
        $countInsert++;
        echo "insert-" . $ws->pali . "<br>";
    }

}
echo "<b>insert:" . $countInsert . " words</b><br>";
?>
</body>
</html>