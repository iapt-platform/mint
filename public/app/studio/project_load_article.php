<?php
/*
get xml doc from article
尚未完成
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$id = $_GET["id"];

PDO_Connect( _FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT * from article where id= ? ";
$Fetch = PDO_FetchAll($query,array($id));
if (count($Fetch) > 0) {
    echo "<set>\n";
    echo $Fetch[0]["doc_info"];
    echo "\n<dict></dict>\n";
    echo "<message></message>\n";
    echo "<body>\n";

    $dh_wbw = new PDO( _FILE_DB_USER_WBW_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $dh_sent = new PDO( _FILE_DB_SENTENCE_, _DB_PASSWORD_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $blockList = explode("\n", $Fetch[0]["content"]);

    foreach ($blockList as $block) {
        $info = explode("-", $block);
        switch ($block->type) {
            case "6":
                {
                    $albumId = UUID::v4();
                    $query = "SELECT book_id,paragraph,creator_uid,lang from "._TABLE_USER_WBW_BLOCK_." where uid = ? ";
                    $stmt = $dh_wbw->prepare($query);
                    $stmt->execute(array($block->block_id));
                    $FetchBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n<block>";
                    echo "<info>\n";

                    echo "<type>wbw</type>";
                    echo "<book>{$FetchBlock[0]["book_id"]}</book>";
                    echo "<paragraph>{$FetchBlock[0]["paragraph"]}</paragraph>";
                    echo "<level>100</level>";
                    echo "<title>title</title>";
                    echo "<album_id>{$block->channal}</album_id>";
                    echo "<album_guid>{$block->channal}</album_guid>";
                    echo "<author>{$FetchBlock[0]["creator_uid"]}</author>";
                    echo "<language>{$FetchBlock[0]["lang"]}</language>";
                    echo "<version>1</version>";
                    echo "<edition>1</edition>";
                    echo "<id>{$block->block_id}</id>";
                    echo "</info>\n";
                    echo "<data>\n";
                    $query = "SELECT data from "._TABLE_USER_WBW_." where block_uid=?";
                    $stmt = $dh_wbw->prepare($query);
                    $stmt->execute(array($block->block_id));
                    $wbw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($wbw_data as $word) {
                        echo $word["data"];
                        echo "\r\n";
                    }
                    echo "</data>";
                    echo "</block>\n";
                    break;
                }
            case 2:
                {
                    $albumId = UUID::v4();
                    $query = "select * from sent_block where id='" . $block->block_id . "'";
                    $stmt = $dh_sent->query($query);
                    $FetchBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($FetchBlock) > 0) {
                        echo "\n<block>\n";
                        echo "<info>\n";

                        echo "<type>translate</type>";
                        echo "<book>{$FetchBlock[0]["book"]}</book>";
                        echo "<paragraph>{$FetchBlock[0]["paragraph"]}</paragraph>";
                        echo "<level>100</level>";
                        echo "<title>title</title>";
                        echo "<album_id>{$block->channal}</album_id>";
                        echo "<album_guid>{$block->channal}</album_guid>";
                        echo "<author>{$FetchBlock[0]["author"]}</author>";
                        echo "<editor>{$FetchBlock[0]["editor"]}</editor>";
                        echo "<language>{$FetchBlock[0]["lang"]}</language>";
                        echo "<version>1</version>";
                        echo "<edition>1</edition>";
                        echo "<id>{$block->block_id}</id>";
                        echo "<parent>{$FetchBlock[0]["parent_id"]}</parent>";
                        echo "</info>\n";
                        echo "<data>\n";
                        $query = "SELECT uid,word_start,word_end,content,status from "._TABLE_SENTENCE_." where block_uid=?";
                        $stmt = $dh_sent->query($query,array($block->block_id));
                        $sent_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sent_data as $sent) {
                            echo "<sen>";
                            echo "<id>{$sent["uid"]}</id>";
                            echo "<begin>{$sent["word_start"]}</begin>";
                            echo "<end>{$sent["word_end"]}</end>";
                            echo "<text>{$sent["content"]}</text>";
                            echo "<status>{$sent["status"]}</status>";
                            echo "</sen>";
                        }
                        echo "</data>\n";
                        echo "</block>\n";
                    }
                    break;
                }
        }

    }

    echo "</body>\n";
    echo "</set>";
}
