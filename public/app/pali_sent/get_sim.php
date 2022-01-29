<?php
/*
获取相似句子列表
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

//

if (isset($_POST["sent_id"])) {
    $dns = _FILE_DB_PALI_SENTENCE_SIM_;
    $dbh_sim = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh_sim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT sent2 FROM "._TABLE_SENT_SIM_." WHERE  sent1 = ? limit 10";
    $stmt = $dbh_sim->prepare($query);
    $stmt->execute(array($_POST["sent_id"]));
    $simList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sim = $_POST["sim"];
    $simList = json_decode($sim);

}
$output = array();

$dns = _FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT book,paragraph,word_begin as begin ,word_end as end,text ,html FROM "._TABLE_PALI_SENT_." WHERE  id = ? ";
$stmt = $dbh->prepare($query);
$count = 0;
foreach ($simList as $value) {
    # code...
    $stmt->execute(array($value["sent2"]));
    $Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($Fetch) {
        $sent = $Fetch;
        $sent["path"] = _get_para_path($Fetch["book"], $Fetch["paragraph"]);
        $output[] = $sent;
    }
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
