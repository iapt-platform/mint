<?php
require_once "../path.php";

require_once "../dict/turbo_split.php";
require_once "../redis/function.php";

global $result;
$myfile = fopen(_DIR_TEMP_ . "/comp.csv", "a");
$filefail = fopen(_DIR_TEMP_ . "/comp_fail.txt", "a");
$iMax = 10;
/*
$dns = "" . _FILE_DB_WORD_INDEX_;
$dbh_word = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh_word->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT * from wordindex where 1";
$stmt = $dbh_word->query($query);

while ($word = $stmt->fetch(PDO::FETCH_ASSOC))
 */
$redis = redis_connect();
if ($redis == false) {
    echo "no redis connect\n";
    exit;
}
$i = null;
$counter = 0;
while ($words = $redis->sscan("pali_word", $i)) {
    # code...

    foreach ($words as $key => $word) {
        # code...
        $arrword = split_diphthong($word);
        if (count($arrword) > 1) {
            fputcsv($myfile, array($word, implode("+", $arrword), 0.99));
        }

        foreach ($arrword as $oneword) {
            $counter++;
            $result = array(); //全局变量，递归程序的输出容器

            mySplit2($oneword, 0, $_express, 0, 0.01, 0.01, true, true);
            if (isset($_POST["debug"])) {
                echo "正切：" . count($result) . "\n";
            }
            mySplit2($oneword, 0, $_express, 0, 0.01, 0.01, false, true);
            if (isset($_POST["debug"])) {
                echo "反切：" . count($result) . "\n";
            }
            /*
            #正向切分
            mySplit2($oneword, 0, false);
            if (count($result) == 0) {
            #如果没有 逆向切分
            mySplit2($oneword, 0, false, 0, 0.8, 0.8, true);
            }
             */
            echo "{$counter}-{$oneword}:" . count($result) . "\n";
            if (count($result) > 0) {
                arsort($result); //按信心指数排序
                $iCount = 0;
                foreach ($result as $row => $value) {
                    $iCount++;
                    if ($value > 0.8) {
                        fputcsv($myfile, array($oneword, $row, $value));
                    } else {
                        fputcsv($filefail, array($oneword, $row, $value));
                    }

                    if ($iCount >= $iMax) {
                        break;
                    }
                }
            } else {
                fwrite($filefail, $oneword . "\n");
            }

        }

    }
}
while ($i = $words[0] != 0);
