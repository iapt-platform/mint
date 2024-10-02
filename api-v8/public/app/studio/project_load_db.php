<?php
/*
get xml doc from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$id = $_GET["id"];

PDO_Connect( _FILE_DB_FILEINDEX_);
$query = "SELECT * from "._TABLE_FILEINDEX_." where uid=?";
$Fetch = PDO_FetchAll($query,array($id));
if (count($Fetch) > 0) {
    echo "<set>\n";
    echo $Fetch[0]["doc_info"];
    echo "\n<dict></dict>\n";
    echo "<message></message>\n";
    echo "<body>\n";

    $dh_wbw = new PDO(_FILE_DB_USER_WBW_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $dh_sent = new PDO(_FILE_DB_SENTENCE_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $blockList = json_decode($Fetch[0]["doc_block"]);

    foreach ($blockList as $block) {
        switch ($block->type) {
            case "6":
                {
                    $albumId = UUID::v4();
                    $query = "SELECT uid, book_id,paragraph,channel_uid , creator_uid,lang from "._TABLE_USER_WBW_BLOCK_." where uid = ? ";
                    $stmt = $dh_wbw->prepare($query);
                    $stmt->execute(array($block->block_id));
                    $FetchBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
					#TODO 处理没找到数据的问题
                    echo "\n<block>";
                    echo "<info>\n";
                    echo "<type>wbw</type>";
                    echo "<book>{$FetchBlock[0]["book_id"]}</book>";
                    echo "<paragraph>{$FetchBlock[0]["paragraph"]}</paragraph>";
                    echo "<level>100</level>";
                    echo "<title>title</title>";
                    echo "<album_id>{$FetchBlock[0]["channel_uid"]}</album_id>";
                    echo "<album_guid>{$FetchBlock[0]["channel_uid"]}</album_guid>";
                    echo "<author>{$FetchBlock[0]["creator_uid"]}</author>";
                    echo "<language>{$FetchBlock[0]["lang"]}</language>";
                    echo "<version>1</version>";
                    echo "<edition>1</edition>";
                    echo "<id>{$FetchBlock[0]["uid"]}</id>";
                    echo "</info>\n";
                    echo "<data>\n";
                    $query = "SELECT data FROM "._TABLE_USER_WBW_." WHERE block_uid=? order by wid ASC";
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
                    $query = "SELECT book_id,paragraph,author,editor_uid,lang,parent_uid from "._TABLE_SENTENCE_BLOCK_." where uid=?";
                    $stmt = $dh_sent->query($query,array($block->block_id));
                    $FetchBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($FetchBlock) > 0) {
                        echo "\n<block>\n";
                        echo "<info>\n";

                        echo "<type>translate</type>";
                        echo "<book>{$FetchBlock[0]["book_id"]}</book>";
                        echo "<paragraph>{$FetchBlock[0]["paragraph"]}</paragraph>";
                        echo "<level>100</level>";
                        echo "<title>title</title>";
                        echo "<album_id></album_id>";
                        echo "<album_guid>channel</album_guid>";
                        echo "<author>{$FetchBlock[0]["author"]}</author>";
                        echo "<editor>{$FetchBlock[0]["editor_uid"]}</editor>";
                        echo "<language>{$FetchBlock[0]["lang"]}</language>";
                        echo "<version>1</version>";
                        echo "<edition>1</edition>";
                        echo "<id>{$block->block_id}</id>";
                        echo "<parent>{$FetchBlock[0]["parent_uid"]}</parent>";
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
