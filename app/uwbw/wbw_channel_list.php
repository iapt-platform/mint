<?php
require_once '../path.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";

$output["status"] = 0;
$output["error"] = "";
$output["data"] = "";
if (!isset($_COOKIE["userid"])) {
    $output["status"] = 1;
    $output["error"] = "#not_login";
    echo json_encode(output, JSON_UNESCAPED_UNICODE);
    exit;
}

$_book = $_POST["book"];
$_para = json_decode($_POST["para"]);
$output["para"] = $_POST["para"];
$output["book"] = $_POST["book"];

/*  创建一个填充了和params相同数量占位符的字符串 */
$place_holders = implode(',', array_fill(0, count($_para), '?'));
$params = $_para;
$params[] = $_book;

PDO_Connect("" . _FILE_DB_CHANNAL_);
$query = "SELECT * FROM channal WHERE owner = ?  LIMIT 0,100";
$FetchChannal = PDO_FetchAll($query, array($_COOKIE["userid"]));
$i = 0;
foreach ($FetchChannal as $key => $row) {
    PDO_Connect("" . _FILE_DB_USER_WBW_);

    $queryParam = $params;
    $queryParam[] = $row["id"];
    $query = "SELECT count(*) FROM wbw_block WHERE  paragraph IN ($place_holders)  AND book = ? AND channal = ? ";
    $wbwCount = PDO_FetchOne($query, $queryParam);
    $FetchChannal[$key]["wbw_para"] = $wbwCount;
    $FetchChannal[$key]["count"] = count($_para);
}
$output["data"] = $FetchChannal;
echo json_encode($output, JSON_UNESCAPED_UNICODE);
