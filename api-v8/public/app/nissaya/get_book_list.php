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
add_edit_event(_NISSAYA_FIND_, "{$book}-{$para}-{$begin}-{$end}");

PDO_Connect("" . _FILE_DB_PAGE_INDEX_);
$query = "SELECT * from m where book=? and para=?";
$Fetch = PDO_FetchRow($query, array($book, $para));
    $query = "SELECT * from book_match where book_vri=? and vol=?";
    $Fetch_nsy_book = PDO_FetchRow($query, array($Fetch["book"], $Fetch["page1"]));
    if ($Fetch_nsy_book) {
        $prefix = $Fetch_nsy_book["table"];
		
        $query = "select nsyid from {$prefix}_pagematch where bookid = ? and bookpagenumber = ? group by nsyid";
        $Fetch_nsy_book = PDO_FetchAll($query, array($Fetch_nsy_book["bookid"],$Fetch["page2"]));
		foreach ($Fetch_nsy_book as $key => $book) {
			# code...
			$query = "select nsyname from {$prefix}_pagematch where nsyid = ? ";
			$Fetch_nsy_book[$key]['name'] = PDO_FetchRow($query, array($book["nsyid"]))['nsyname'];
			$Fetch_nsy_book[$key]['type'] = $prefix;
		}
		$result["data"] =  json_encode($Fetch_nsy_book, JSON_UNESCAPED_UNICODE);
        
    } else {
        $result["error"] = "error: in table book_match";
    }
echo json_encode($result, JSON_UNESCAPED_UNICODE);
