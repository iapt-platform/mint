<?php
/*
转换pcs 到数据库格式

 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

require_once '../studio/index_head.php';
echo '<body id="file_list_body" >';
echo '<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">';

echo "<h2>转换PCS 到数据库格式</h2>";

if ($_COOKIE["uid"]) {
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    echo "<h3><a href='../ucenter/index.php?op=login'>登录</a>后才可以打开文档 </h3>";
    exit;
}
if (isset($_GET["doc_id"]) == false) {
    echo "没有 文档编号";
    exit;
}
PDO_Connect(_FILE_DB_FILEINDEX_);
$doc_id = $_GET["doc_id"];
$query = "SELECT book,paragraph from "._TABLE_FILEINDEX_." where uid= ? ";
$Fetch = PDO_FetchAll($query, array($doc_id));
$iFetch = count($Fetch);
if ($iFetch > 0) {
    //文档信息
    $mbook = $Fetch[0]["book"];
    $paragraph = $Fetch[0]["paragraph"];
}

if (isset($_GET["channel"]) == false) {
    echo '<div class="file_list_block">';
    echo "<h2>选择一个空白的版风存储新的文档</h2>";
    echo "<form action='pcs2db.php' method='get'>";
    echo "<input type='hidden' name='doc_id' value='{$_GET["doc_id"]}' />";
    PDO_Connect( _FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
    $query = "SELECT uid,name,lang,status,create_time from "._TABLE_CHANNEL_." where owner_uid = ?   limit 100";
    $Fetch = PDO_FetchAll($query,$_COOKIE["user_uid"]);
    $i = 0;
    foreach ($Fetch as $row) {
        echo '<div class="file_list_row" style="padding:5px;display:flex;">';

        echo '<div class="pd-10"  style="max-width:2em;flex:1;">';
        echo '<input name="channel" value="' . $row["uid"] . '" ';
        if ($i == 0) {
            echo "checked";
        }
        echo ' type="radio" />';
        echo '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["name"] . '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["lang"] . '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        PDO_Connect("" . _FILE_DB_USER_WBW_);
        $query = "SELECT count(*) from "._TABLE_USER_WBW_BLOCK_." where channel_uid = '{$row["uid"]}' and book_id='{$mbook}' and paragraph in ({$paragraph})  limit 100";
        $FetchWBW = PDO_FetchOne($query);
        echo '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        if ($FetchWBW == 0) {
            echo $_local->gui->blank;
        } else {
            echo $FetchWBW . $_local->gui->para;
            echo "<a href='../studio/editor.php?op=openchannal&book=$book&para={$paraList}&channal={$row["uid"]}'>open</a>";
        }
        echo '</div>';

        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_, _DB_PASSWORD_);
        $query = "SELECT count(*) from "._TABLE_SENTENCE_." where channel_uid = ? and book_id= ?  and paragraph in ({$paragraph})  limit 100";
        $FetchWBW = PDO_FetchOne($query,array($row["uid"],$mbook));
        echo '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        if ($FetchWBW == 0) {
            echo $_local->gui->blank;
        } else {
            echo $FetchWBW . $_local->gui->para;
        }
        echo '</div>';

        echo '<div class="summary"  style="flex:1;padding-bottom:5px;">' . $row["status"] . '</div>';
        echo '<div class="author"  style="flex:1;padding-bottom:5px;">' . $row["create_time"] . '</div>';

        echo '</div>';
        $i++;
    }
    echo "<input type='submit' />";
    echo "</form>";
    echo "</div>";
    exit;
}

$dir = _DIR_USER_DOC_ . '/' . $_COOKIE["userid"] . _DIR_MYDOCUMENT_;
PDO_Connect(_FILE_DB_FILEINDEX_);
$query = "SELECT file_name, doc_info, modify_time from "._TABLE_FILEINDEX_." where uid=? ";
$Fetch = PDO_FetchRow($query, array($_GET["doc_id"]));

if ($Fetch === false) {
    echo "数据库中查不到文件";
    exit;
} else {
    $file_modify_time = $Fetch["modify_time"];
    if (empty($Fetch["doc_info"])) {
        $file = $dir . '/' . $Fetch["file_name"];
    } else {
        echo "已经是数据库格式了。无需转换";
        exit;
    }
}
echo "File Name:{$file}<br>";
if (!file_exists($file)) {
    echo "文件不存在";
    exit;
}
$xml = simplexml_load_file($file);
if ($xml == false) {
    echo "载入pcs文件错误。文件名：{$file}";
    exit;
}
$xml_head = $xml->xpath('//head')[0];
$strHead = "<head>";
$strHead .= "<type>{$xml_head->type}</type>";
$strHead .= "<mode>{$xml_head->mode}</mode>";
$strHead .= "<ver>{$xml_head->ver}</ver>";
$strHead .= "<toc>{$xml_head->toc}</toc>";
$strHead .= "<style>{$xml_head->style}</style>";
$strHead .= "<doc_title>{$xml_head->doc_title}</doc_title>";
$strHead .= "<tag>{$xml_head->tag}</tag>";
$strHead .= "<book>{$xml_head->book}</book>";
$strHead .= "<paragraph>{$xml_head->paragraph}</paragraph>";
$strHead .= "</head>";

$dataBlock = $xml->xpath('//block');

{

    //复制数据
    //打开逐词解析数据库
    $dns = _FILE_DB_USER_WBW_;
    $dbhWBW = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbhWBW->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //打开译文数据库
    $dns = _FILE_DB_SENTENCE_;
    $dbhSent = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbhSent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //逐词解析新数据数组
    $arrNewBlock = array();
    $arrNewBlockData = array();
    $arrBlockTransform = array();

    //译文新数据数组
    $arrSentNewBlock = array();
    $arrSentNewBlockData = array();
    $arrSentBlockTransform = array();

    $newDocBlockList = array();
    foreach ($dataBlock as $block) {
        switch ($block->info->type) {
            case "translate":
                echo "wbw:" . $block->info->book . "<br>";
                break;
            case "wbw":
                echo "translate:" . $block->info->book . "<br>";
                break;
        }
    }

    foreach ($dataBlock as $block) {
        switch ($block->info->type) {
            case 1:
                break;
            case "translate":
                //译文
                $blockid = UUID::V4();
                //译文不加入块列表 因为译文用channel解决 不需要渲染译文块
                //$newDocBlockList[]=array('type' => 2,'block_id' => $blockid);
                $arrSentBlockTransform["{$blockid}"] = $blockid;
                //if(count($fBlock)>0)
                {
                    array_push($arrSentNewBlock, array($blockid,
                        "",
                        $block->info->book,
                        $block->info->paragraph,
                        $_COOKIE["userid"],
                        $block->info->language,
                        $block->info->author,
                        "",
                        "",
                        "1",
                        $file_modify_time,
                        mTime(),
                    ));
                }
                foreach ($block->data->children() as $sen) {
                    if (isset($sen->begin)) {
                        $sent_begin = $sen->begin;
                    } else {
                        $sent_begin = "";
                    }
                    if (isset($sen->end)) {
                        $sent_end = $sen->end;
                    } else {
                        $sent_end = "";
                    }
                    if (isset($sen->text) && strlen(trim($sen->text)) > 0) {
                        $paraText = $sen->text;
                        if ($block->info->level > 0 && $block->info->level < 8) {
                            $toc .= $sen->text;
                        }
                        array_push($arrSentNewBlockData,
                            array(UUID::V4(),
                                $blockid,
                                $block->info->book,
                                $block->info->paragraph,
                                $sent_begin,
                                $sent_end,
                                $block->info->author,
                                $_COOKIE["userid"],
                                $paraText,
                                mb_strlen($paraText, "UTF-8"),
                                $block->info->language,
                                "1",
                                "7",
                                $file_modify_time,
                                mTime(),
                                $_GET["channel"],
                            ));
                    }
                }
                break;
            case "wbw":
                $blockid = UUID::V4();
                $newDocBlockList[] = array('type' => 6, 'block_id' => $blockid);
                $arrBlockTransform["{$blockid}"] = $blockid;
                {
                    array_push($arrNewBlock,
                        array($blockid,
                            "",
                            $_GET["channel"],
                            $_COOKIE["userid"],
                            $block->info->book,
                            $block->info->paragraph,
                            "",
                            $block->info->language,
                            "",
                            $file_modify_time
                        ));
                }

                $currWordId = "";
                $currWordReal = "";
                $currWordStatus = "";
                $sWordData = "";
                $iWordCount = 0;
                foreach ($block->data->children() as $word) {
                    $word_id = explode("-", $word->id)[2];
                    $arrWordId = explode("_", $word_id);
                    $realWordId = $arrWordId[0];
                    if ($realWordId != $currWordId) {
                        if ($iWordCount > 0) {
                            array_push($arrNewBlockData, array(UUID::V4(),
                                $blockid,
                                $block->info->book,
                                $block->info->paragraph,
                                $currWordId,
                                $currWordReal,
                                $sWordData,
                                $file_modify_time,
                                mTime(),
                                $currWordStatus,
                                $_COOKIE["userid"],
                            ));
                            $sWordData = "";
                        }
                        $currWordId = $realWordId;
                        $currWordReal = $word->real;
                        $currWordStatus = $word->status;
                    }

                    $sWordData .= "<word>";
                    $sWordData .= "<pali>{$word->pali}</pali>";
                    $sWordData .= "<real>{$word->real}</real>";
                    $sWordData .= "<id>{$word->id}</id>";
                    $sWordData .= "<type status='0'>{$word->type}</type>";
                    $sWordData .= "<gramma status='0'>{$word->gramma}</gramma>";
                    $sWordData .= "<mean status='0'>{$word->mean}</mean>";
                    $sWordData .= "<org status='0'>{$word->org}</org>";
                    $sWordData .= "<om status='0'>{$word->om}</om>";
                    $sWordData .= "<case status='0'>{$word->case}</case>";
                    $sWordData .= "<note status='0'>{$word->note}</note>";
                    $sWordData .= "<style>{$word->style}</style>";
                    $sWordData .= "<status>{$word->status}</status>";
                    $sWordData .= "<parent>{$word->parent}</parent>";
                    if (isset($word->bmc)) {
                        $sWordData .= "<bmc>{$word->bmc}</bmc>";
                    }
                    if (isset($word->bmt)) {
                        $sWordData .= "<bmt>{$word->bmt}</bmt>";
                    }
                    if (isset($word->un)) {
                        $sWordData .= "<un>{$word->un}</un>";
                    }
                    if (isset($word->lock)) {
                        $sWordData .= "<lock>{$word->lock}</lock>";
                    }
                    $sWordData .= "</word>";

                    $iWordCount++;
                }
                array_push($arrNewBlockData,
                    array(UUID::V4(),
                        $blockid,
                        $block->info->book,
                        $block->info->paragraph,
                        $word_id,
                        $word->real,
                        $sWordData,
                        $file_modify_time,
                        $word->status,
                        $_COOKIE["userid"],
                    ));
                break;
            case 2:

                break;
        }

    }

    //逐词解析block数据块结束

    #插入逐词解析块数据
    if (count($arrNewBlock) > 0) {
        $dbhWBW->beginTransaction();
        $query = "INSERT INTO "._TABLE_USER_WBW_BLOCK_." (
										 uid ,
										 parent_id ,
										 channel_uid ,
										 creator_uid ,
										 book_id ,
										 paragraph ,
										 style ,
										 lang ,
										 status ,
										 modify_time ,
										updated_at
										)
										VALUES (?,?,?,?,?,?,?,?,?,?,now())";
        $stmtNewBlock = $dbhWBW->prepare($query);
        foreach ($arrNewBlock as $oneParam) {
            $stmtNewBlock->execute($oneParam);
        }
        // 提交更改
        $dbhWBW->commit();
        if (!$stmtNewBlock || ($stmtNewBlock && $stmtNewBlock->errorCode() != 0)) {
            $error = $dbhWBW->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            //逐词解析block块复刻成功
            $count = count($arrNewBlock);
            echo "wbw block $count recorders.<br/>";
        }
    }

    if (count($arrNewBlockData) > 0) {
        // 开始一个事务，逐词解析数据 关闭自动提交
        $dbhWBW->beginTransaction();
        $query = "INSERT INTO "._TABLE_USER_WBW_." ( uid , block_uid , book_id , paragraph , wid , word , data , modify_time , status , creator_uid ,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,now())";
        $stmtWbwData = $dbhWBW->prepare($query);
        foreach ($arrNewBlockData as $oneParam) {
            $stmtWbwData->execute($oneParam);
        }
        // 提交更改
        $dbhWBW->commit();
        if (!$stmtWbwData || ($stmtWbwData && $stmtWbwData->errorCode() != 0)) {
            $error = $dbhWBW->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            //逐词解析 数据 复刻成功
            $count = count($arrNewBlockData);
            echo "new wbw $count recorders.";
        }
    }

    //译文 block数据块

    if (count($arrSentNewBlock) > 0) {
        $dbhSent->beginTransaction();
        $query = "INSERT INTO "._TABLE_SENTENCE_BLOCK_." (uid , parent_uid , book_id , paragraph , owner_uid , lang , author , editor_uid  , status , modify_time , create_time') VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $stmtSentNewBlock = $dbhSent->prepare($query);
        foreach ($arrSentNewBlock as $oneParam) {
            //print_r($oneParam);
            $stmtSentNewBlock->execute($oneParam);
        }
        // 提交更改
        $dbhSent->commit();
        if (!$stmtSentNewBlock || ($stmtSentNewBlock && $stmtSentNewBlock->errorCode() != 0)) {
            $error = $dbhSent->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            //译文 block块复刻成功
            $count = count($arrNewBlock);
            echo "wbw block $count recorders.<br/>";
        }
    }

    if (count($arrSentNewBlockData) > 0) {
        // 开始一个事务，逐词解析数据 关闭自动提交
        $dbhSent->beginTransaction();
        $query = "INSERT INTO "._TABLE_SENTENCE_." ( uid , block_uid , book_id , paragraph , word_start , word_end , author , editor_uid , content , strlen , language , version , status , modify_time , create_time , channel_uid') VALUES (?  , ? , ? , ? ,?, ? , ? , ? , ? , ? , ? , ? , ? , ?, ? , ? ,?)";
        $stmtSentData = $dbhSent->prepare($query);
        foreach ($arrSentNewBlockData as $oneParam) {
            $stmtSentData->execute($oneParam);
        }
        // 提交更改
        $dbhSent->commit();
        if (!$stmtSentData || ($stmtSentData && $stmtSentData->errorCode() != 0)) {
            $error = $dbhSent->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            //译文 数据 复刻成功
            $count = count($arrSentNewBlockData);
            echo "new translation $count recorders.";
        }
    }

    //插入记录到文件索引
    $filesize = 0;
    //服务器端文件列表
    PDO_Connect( _FILE_DB_FILEINDEX_);

    $query = "UPDATE "._TABLE_FILEINDEX_." SET 'doc_info' = ? , 'doc_block' = ?  WHERE id = ? ";
    $stmt = $PDO->prepare($query);
    $newData = array(
        $strHead,
        json_encode($newDocBlockList, JSON_UNESCAPED_UNICODE),
        $_GET["doc_id"],
    );
    $stmt->execute($newData);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        echo "error - $error[2] <br>";
    } else {
        //文档列表插入成功
        echo "doc list updata 1 recorders.";
        echo "<a href='../studio/editor.php?op=opendb&doc_id={$_GET["doc_id"]}'>在编辑器中打开</a>";
    }

}
?>

</div>
</body>
</html>
