<?php
/*
get xml doc from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

if (isset($_POST["book"])) {
    $book = $_POST["book"];
} else {
    echo "error: no book id";
    exit;
}

if (isset($_POST["para"])) {
    $paralist = explode(",", $_POST["para"]);
} else {
    exit;
}

if (isset($_POST["channal"])) {
    $channal = $_POST["channal"];
} else {
    exit;
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<set>\n";
echo "    <head>\n";
echo "        <type>pcdsset</type>\n";
echo "        <mode>package</mode>\n";
echo "        <ver>1</ver>\n";
echo "        <toc></toc>\n";
echo "        <style></style>\n";
echo "        <title>{$book}-{$paralist[0]}</title>\n";
echo "    </head>\n";
echo "\n<dict></dict>\n";
echo "<message></message>\n";
echo "<body>\n";

$dh_wbw = new PDO(_FILE_DB_USER_WBW_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

foreach ($paralist as $para) {

    $albumId = UUID::v4();
    $query = "SELECT uid,creator_uid,lang FROM "._TABLE_USER_WBW_BLOCK_." WHERE channel_uid=? AND book_id = ? AND paragraph = ?  ";
    $stmt = $dh_wbw->prepare($query);
    $stmt->execute(array($channal, $book, $para));
    $FetchBlock = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($FetchBlock) {
        echo "\n<block>";
        echo "<info>\n";

        echo "<type>wbw</type>";
        echo "<book>{$book}</book>";
        echo "<paragraph>{$para}</paragraph>";
        echo "<level>100</level>";
        echo "<title></title>";
        echo "<album_id></album_id>";
        echo "<album_guid></album_guid>";
        echo "<author>{$FetchBlock["creator_uid"]}</author>";
        echo "<language>{$FetchBlock["lang"]}</language>";
        echo "<version>1</version>";
        echo "<edition>1</edition>";
        echo "<id>{$FetchBlock["uid"]}</id>";
        echo "</info>\n";

        echo "<data>\n";
        $block_id = $FetchBlock["uid"];
        $query = "SELECT data from "._TABLE_USER_WBW_." where block_uid= ? order by wid ASC";
        $stmt = $dh_wbw->prepare($query);
        $stmt->execute(array($block_id));
        $wbw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($wbw_data as $word) {
            echo $word["data"];
            echo "\r\n";
        }
        echo "</data>";
        echo "</block>\n";
    }

}

echo "</body>\n";
echo "</set>";
