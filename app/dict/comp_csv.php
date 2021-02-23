<?php
require_once "../dict/troub_split.php";
global $result;
$myfile = fopen("comp.csv", "a");
$filefail = fopen("comp_fail.txt", "a");

$dns = "sqlite:" . _FILE_DB_WORD_INDEX_;
$dbh_word = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh_word->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT * from wordindex where 1";
$stmt = $dbh_word->query($query);
$iMax = 5;
while ($word = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $arrword = split_diphthong($word["word"]);
    fputcsv($myfile, array($word["word"], implode("+", $arrword), 90));
    foreach ($arrword as $oneword) {
        $result = array(); //全局变量，递归程序的输出容器
        mySplit2($oneword, 0, false);
        echo "{$oneword}:" . count($result) . "\n";
        if (count($result) > 0) {
            arsort($result); //按信心指数排序
            $iCount = 0;
            foreach ($result as $row => $value) {
                $iCount++;
                fputcsv($myfile, array($oneword, $row, $value));
                if ($iCount >= $iMax) {
                    break;
                }
            }
        } else {
            fwrite($filefail, $oneword . "\n");
        }

    }
}
