<?php
/*
get xml doc from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$id = $_GET["id"];

PDO_Connect( _FILE_DB_FILEINDEX_);
$query = "select * from fileindex where id=" . $PDO->quote($id);
$Fetch = PDO_FetchAll($query);
if (count($Fetch) > 0) {
    echo "<set>\n";
    echo $Fetch[0]["doc_info"];
    echo "\n<dict></dict>\n";
    echo "<message></message>\n";
    echo "<body>\n";

    $dh_wbw = new PDO("" . _FILE_DB_USER_WBW_, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $dh_sent = new PDO("" . _FILE_DB_SENTENCE_, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $blockList = json_decode($Fetch[0]["doc_block"]);

    foreach ($blockList as $block) {
        switch ($block->type) {
            case "6":
                {
                    $albumId = UUID::v4();
                    $query = "select * from "._TABLE_USER_WBW_BLOCK_." where id='" . $block->block_id . "'";
                    $stmt = $dh_wbw->query($query);
                    $FetchBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n<block>";
                    echo "<info>\n";

                    echo "<type>wbw</type>";
                    echo "<book>{$FetchBlock[0]["book"]}</book>";
                    echo "<paragraph>{$FetchBlock[0]["paragraph"]}</paragraph>";
                    echo "<level>100</level>";
                    echo "<title>title</title>";
                    echo "<album_id>{$block->channal}</album_id>";
                    echo "<album_guid>{$block->channal}</album_guid>";
                    echo "<author>{$FetchBlock[0]["owner"]}</author>";
                    echo "<language>{$FetchBlock[0]["lang"]}</language>";
                    echo "<version>1</version>";
                    echo "<edition>1</edition>";
                    echo "<id>{$block->block_id}</id>";
                    echo "</info>\n";
                    echo "<data>\n";
                    $query = "SELECT * FROM "._TABLE_USER_WBW_." WHERE block_id='" . $block->block_id . "' order by wid ASC";
                    $stmt = $dh_wbw->query($query);
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
                        $query = "select * from sentence where block_id='" . $block->block_id . "'";
                        $stmt = $dh_sent->query($query);
                        $sent_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($sent_data as $sent) {
                            echo "<sen>";
                            echo "<id>{$sent["id"]}</id>";
                            echo "<begin>{$sent["begin"]}</begin>";
                            echo "<end>{$sent["end"]}</end>";
                            echo "<text>{$sent["text"]}</text>";
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
