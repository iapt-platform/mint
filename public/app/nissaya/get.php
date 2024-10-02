<?php
require_once "../public/_pdo.php";
require_once "../config.php";
require_once "../ucenter/active.php";

$result["error"] = "";
$result["data"] = array();

if (isset($_GET["book"])) {
    $book = $_GET["book"];
} else {
    $result["error"] = "no param";
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
if (isset($_GET["para"])) {
    $para = $_GET["para"];
} else {
    $result["error"] = "no param";
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET["begin"])) {
    $begin = $_GET["begin"];
} else {
    $begin = 0;
}
if (isset($_GET["end"])) {
    $end = $_GET["end"];
} else {
    $end = 0;
}
if (isset($_GET["nsyid"])) {
    $nsyid = substr($_GET["nsyid"],1) ;
} else {
    $nsyid = '';
}
add_edit_event(_NISSAYA_FIND_, "{$book}-{$para}-{$begin}-{$end}");

PDO_Connect("" . _FILE_DB_PAGE_INDEX_);
$query = "SELECT * from m where book=? and para=?";
$Fetch = PDO_FetchAll($query, array($book, $para));

foreach ($Fetch as $key => $value) {
    $query = "SELECT * from book_match where book_vri=? and vol=?";
    $Fetch_nsy_book = PDO_FetchRow($query, array($value["book"], $value["page1"]));
    if ($Fetch_nsy_book) {
        $prefix = $Fetch_nsy_book["table"];
        $query = "SELECT * from {$prefix}_pagematch where bookid=? and bookpagenumber=? and nsyid = ?";
        $Fetch_nsy_index = PDO_FetchRow($query, array($Fetch_nsy_book["bookid"], $value["page2"],$nsyid ));
        $Fetch_nsy_index["dir"] = $prefix;
        $result["data"][] = $Fetch_nsy_index;
    } else {
        $result["error"] = "error: in table book_match";
    }
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
