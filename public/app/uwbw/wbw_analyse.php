<?php
/*
逐词解析数据库数据分析
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect("" . _FILE_DB_USER_WBW_,_DB_USERNAME_,_DB_PASSWORD_);

$query = "SELECT book_id,paragraph,wid,data,modify_time, creator_uid from "._TABLE_USER_WBW_." where  true";

$sth = $PDO->prepare($query);
$sth->execute();

$udict = new PDO("" . _FILE_DB_USER_DICT_, "", "", array(PDO::ATTR_PERSISTENT => true));
$udict->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/* 开始一个事务，关闭自动提交 */
$udict->beginTransaction();
$query = "INSERT INTO udict ('userid',
                            'pali',
                            'book',
                            'paragraph',
                            'wid',
                            'type',
                            'data',
                            'confidence',
                            'lang',
                            'modify_time')
                    VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";
$stmt = $udict->prepare($query);

$i = 0;
while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
    try {
        $xmlString = "<root>" . $result["data"] . "</root>";
        //echo  $xmlString."<br>";
        $xmlWord = simplexml_load_string($xmlString);
        $wordsList = $xmlWord->xpath('//word');
        foreach ($wordsList as $word) {
            $pali = $word->real->__toString();
            foreach ($word as $key => $value) {
                $strValue = $value->__toString();
                if ($strValue !== "?" && $strValue !== "" && $strValue !== ".ctl." && $strValue !== ".a." && $strValue !== " " && mb_substr($strValue, 0, 3, "UTF-8") !== "[a]" && $strValue !== "_un_auto_factormean_" && $strValue !== "_un_auto_mean_") {
                    $iType = 0;
                    $lang = 'pali';
                    switch ($key) {
                        case 'type':
                            $iType = 1;
                            break;
                        case 'gramma':
                            $iType = 2;
                            break;
                        case 'mean':
                            $iType = 3;
                            $lang = getLanguageCode($strValue);
                            break;
                        case 'org':
                            $iType = 4;
                            break;
                        case 'om':
                            $iType = 5;
                            $lang = getLanguageCode($strValue);
                            break;
                        case 'parent':
                            $iType = 6;
                            break;
                    }
                    if ($iType > 0) {
                        $wordData = array($result["creator_uid"],
                            $pali, $result["book_id"],
                            $result["paragraph"],
                            $result["wid"],
                            $iType,
                            $strValue,
                            100,
                            $lang,
                            $result["modify_time"],
                        );
                        //print_r($wordData);
                        $stmt->execute($wordData);
                    }
                }
            }
        }

    } catch (Throwable $e) {
        echo "Captured Throwable: " . $e->getMessage();
    }
    $i++;
    if ($i > 10) {
        //break;
    }

}
//其他字典
$db_file_list = array();
array_push($db_file_list, _DIR_DICT_SYSTEM_ . "/sys_regular.db");
array_push($db_file_list, _DIR_DICT_SYSTEM_ . "/sys_irregular.db");
array_push($db_file_list, _DIR_DICT_SYSTEM_ . "/union.db");
array_push($db_file_list, _DIR_DICT_SYSTEM_ . "/comp.db");

array_push($db_file_list, _DIR_DICT_3RD_ . "/pm.db");
array_push($db_file_list, _DIR_DICT_3RD_ . "/bhmf.db");
array_push($db_file_list, _DIR_DICT_3RD_ . "/shuihan.db");
array_push($db_file_list, _DIR_DICT_3RD_ . "/concise.db");
array_push($db_file_list, _DIR_DICT_3RD_ . "/uhan_en.db");
foreach ($db_file_list as $db_file) {
    if ($debug) {
        echo "dict connect:$db_file<br>";
    }
    $dbh = new PDO("" . $db_file, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $query = "select * from dict where 1";
    $sth = $dbh->prepare($query);
    $sth->execute();
    $i = 0;
    while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($result["mean"])) {
            $arrMean = explode('$', $result["mean"]);
            foreach ($arrMean as $key => $value) {
                $word = trim($value, " \t\n\r\0\x0B\.");
                if (!empty($word)) {
                    $wordData = array($result["dict_name"],
                        $result["pali"],
                        0,
                        0,
                        0,
                        3,
                        $word,
                        $result["confidence"],
                        $result["lang"],
                        1,
                    );
                    //print_r($wordData);
                    $stmt->execute($wordData);
                }
            }
        }

        if (!empty($result["type"])) {
            $wordData = array($result["dict_name"],
                $result["pali"],
                0,
                0,
                0,
                1,
                $result["type"],
                $result["confidence"],
                'pali',
                1,
            );
            $stmt->execute($wordData);
            //print_r($wordData);

        }

        if (!empty($result["gramma"])) {
            $wordData = array($result["dict_name"],
                $result["pali"],
                0,
                0,
                0,
                2,
                $result["gramma"],
                $result["confidence"],
                'pali',
                1,
            );
            $stmt->execute($wordData);
            //print_r($wordData);
        }

        if (!empty($result["parts"])) {
            $wordData = array($result["dict_name"],
                $result["pali"],
                0,
                0,
                0,
                4,
                $result["parts"],
                $result["confidence"],
                'pali',
                1,
            );
            //print_r($wordData);
            $stmt->execute($wordData);
        }
        if (!empty($result["partmean"])) {
            $wordData = array($result["dict_name"],
                $result["pali"],
                0,
                0,
                0,
                5,
                $result["partmean"],
                $result["confidence"],
                $result["lang"],
                1,
            );
            //print_r($wordData);
            $stmt->execute($wordData);
        }

        if (!empty($result["parent"])) {
            $wordData = array($result["dict_name"],
                $result["pali"],
                0,
                0,
                0,
                6,
                $result["parent"],
                $result["confidence"],
                'pali',
                1,
            );
            //print_r($wordData);
            $stmt->execute($wordData);
        }

        if ($i > 10) {
            //break;
        }

        $i++;
    }

}

/* 提交更改 */
$udict->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $udict->errorInfo();
    echo "error - $error[2] <br>";
} else {
    echo "updata index $i  recorders.";
}
