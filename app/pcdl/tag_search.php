<?php
include "./config.php";
include "./_pdo.php";

if (isset($_POST["tag"])) {
    $tag = $_POST["tag"];
} else {
    echo "no tag";
    exit;
}
if (isset($_POST["order"])) {
    $order = $_POST["order"];
} else {
    $order = "";
}

PDO_Connect(_FILE_DB_RESRES_INDEX_);
$tag_string = '{' . $tag . '}';
$query = "SELECT * from \""._TABLE_RES_INDEX_."\" where tag like '%$tag_string%' ";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $book = $Fetch[$i]["book"];
        $album = $Fetch[$i]["album"];
        $paragraph = $Fetch[$i]["paragraph"];
        $title = $Fetch[$i]["title"];
        $summary = $Fetch[$i]["summary"];
        switch ($book) {
            case 1024:
                $sType = "摘";
                break;
            case 1025:
                $sType = "音";
                break;
            case 1026:
                break;
            default:
                $sType = "经";
                break;
        }
        $link = "onclick=\"index_render_res_list($book,$album,$paragraph)\"";
        echo "<div class='list_item'>";
        echo "<div class='book_block' $link>";
        echo "<div class='book_block_cover'>$sType</div>";
        echo "<div  class='book_block_body'>";
        echo "<div class='book_block_title'>$title</div>";
        echo "<div class='book_block_summary'>$summary</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

}
