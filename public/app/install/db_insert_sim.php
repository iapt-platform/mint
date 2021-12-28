<?php
/*
用相似句导入数据库
 */

?>
<?php
require_once '../public/_pdo.php';
require_once '../config.php';

$filelist = array();
$fileNums = 0;
$log = "";
echo "start\n";

$db_file = _DIR_PALICANON_ . "/pali_sim.db3";
PDO_Connect("$db_file");

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO sent_sim ('sent1','sent2','sim') VALUES (? , ? , ?)";
$stmt = $PDO->prepare($query);

// 打开文件并读取数据
$count = 0;
if (($fp = fopen(_DIR_TMP_ . "/pali_simsent/sim.csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        $stmt->execute($data);
        $count++;
        if ($count % 1000000 == 0) {
            echo $count . "\n";
        }
    }
    fclose($fp);
} else {
    echo "can not open csv file. ";
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();

    echo " error, $error[2] \r\n";
} else {
    echo "updata $count recorders.";
}
