<?php
/*拷贝其他人的文件
 *
 *
 */
require_once '../studio/index_head.php';
?>
<body id="file_list_body" >
<?php
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../config.php";

require_once '../studio/index_tool_bar.php';

echo '<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">';

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
PDO_Connect("" . _FILE_DB_FILEINDEX_);
$doc_id = $_GET["doc_id"];
$query = "select * from fileindex where id= ? ";
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
    echo "<form action='fork.php' method='get'>";
    echo "<input type='hidden' name='doc_id' value='{$_GET["doc_id"]}' />";
    PDO_Connect("" . _FILE_DB_CHANNAL_);
    $query = "select * from channal where owner = '{$_COOKIE["userid"]}'   limit 0,100";
    $Fetch = PDO_FetchAll($query);
    $i = 0;
    foreach ($Fetch as $row) {
        echo '<div class="file_list_row" style="padding:5px;display:flex;">';

        echo '<div class="pd-10"  style="max-width:2em;flex:1;">';
        echo '<input name="channel" value="' . $row["id"] . '" ';
        if ($i == 0) {
            echo "checked";
        }
        echo ' type="radio" />';
        echo '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["name"] . '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["lang"] . '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        PDO_Connect(_FILE_DB_USER_WBW_);
        $query = "SELECT count(*) from "._TABLE_USER_WBW_BLOCK_." where channal = '{$row["id"]}' and book='{$mbook}' and paragraph in ({$paragraph})  limit 0,100";
        $FetchWBW = PDO_FetchOne($query);
        echo '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        if ($FetchWBW == 0) {
            echo $_local->gui->blank;
        } else {
            echo $FetchWBW . $_local->gui->para;
            echo "<a href='../studio/editor.php?op=openchannal&book=$book&para={$paraList}&channal={$row["id"]}'>open</a>";
        }
        echo '</div>';

        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        PDO_Connect(_FILE_DB_SENTENCE_);
        $query = "SELECT count(*) from sentence where channal = '{$row["id"]}' and book='{$mbook}' and paragraph in ({$paragraph})  limit 0,100";
        $FetchWBW = PDO_FetchOne($query);
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

//if(isset($_GET["doc_id"]))
{
    PDO_Connect("" . _FILE_DB_FILEINDEX_);
    $doc_id = $_GET["doc_id"];
    $query = "SELECT * from fileindex where id= ? ";
    $Fetch = PDO_FetchAll($query, array($doc_id));
    $iFetch = count($Fetch);
    if ($iFetch > 0) {
        //文档信息
        $orgFileInfo = $Fetch[0];
        $owner = $Fetch[0]["user_id"];
        $filename = $Fetch[0]["file_name"];
        $title = $Fetch[0]["title"];
        $tag = $Fetch[0]["tag"];
        $mbook = $Fetch[0]["book"];
        $paragraph = $Fetch[0]["paragraph"];

        if ($owner == $uid) {
            //自己的文档
            echo "这是自己的文档，不能复刻。";
        } else {
            //别人的文档
            //查询自己是否以前打开过
            $query = "SELECT * from fileindex where parent_id='{$doc_id}' and user_id='{$uid}' ";
            $FetchSelf = PDO_FetchAll($query);
            $iFetchSelf = count($FetchSelf);
            if ($iFetchSelf > 0) {
                //以前打开过
                echo "文档已经复刻";
                echo "正在<a href='../studio/editor.php?op=opendb&doc_id={$doc_id}'>打开</a>文档";
                echo "<script>";
                echo "window.location.assign(\"../studio/editor.php?op=opendb&doc_id={$doc_id}\");";
                echo "</script>";
            } else {
                //以前没打开过
                echo "<h3>共享的文档，正在fork...</h3>";
                echo "<div style='display:none;'>";
                //获取文件路径

                PDO_Connect("" . _FILE_DB_USERINFO_);
                $query = "select userid from user where id='{$owner}'";
                $FetchUid = PDO_FetchOne($query);
                if ($FetchUid) {
                    //$source=$dir_user_base.$FetchUid.$dir_mydocument.$filename;
                    //$dest=$dir_user_base.$userid.$dir_mydocument.$filename;
                    //复制数据
                    //打开逐词解析数据库
                    $dns = "" . _FILE_DB_USER_WBW_;
                    $dbhWBW = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
                    $dbhWBW->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

                    //打开译文数据库
                    $dns = "" . _FILE_DB_SENTENCE_;
                    $dbhSent = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
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

                    $blocks = json_decode($Fetch[0]["doc_block"]);
                    for ($i = 0; $i < count($blocks); $i++) {
                        switch ($blocks[$i]->type) {
                            case 1:
                                break;
                            case 2:
                                //译文
                                $blockid = $blocks[$i]->block_id;
                                $query = "select * from sent_block where id= ? ";
                                $stmt = $dbhSent->prepare($query);
                                $stmt->execute(array($blockid));
                                $fBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $newBlockId = UUID::V4();
                                //不复刻译文
                                //$newDocBlockList[]=array('type' => 2,'block_id' => $newBlockId);
                                $arrSentBlockTransform[$fBlock[0]["id"]] = $newBlockId;
                                if (count($fBlock) > 0) {
                                    array_push($arrSentNewBlock,
                                        array($newBlockId,
                                            $fBlock[0]["id"],
                                            $fBlock[0]["book"],
                                            $fBlock[0]["paragraph"],
                                            $_COOKIE["userid"],
                                            $fBlock[0]["lang"],
                                            $fBlock[0]["author"],
                                            $fBlock[0]["editor"],
                                            $fBlock[0]["tag"],
                                            $fBlock[0]["status"],
                                            mTime(),
                                            mTime(),
                                        ));
                                }

                                $query = "select * from sentence where block_id= ? ";
                                $stmtSent = $dbhSent->prepare($query);
                                $stmtSent->execute(array($fBlock[0]["id"]));
                                $fBlockData = $stmtSent->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($fBlockData as $value) {
                                    array_push($arrSentNewBlockData,
                                        array(UUID::V4(),
                                            $arrSentBlockTransform[$value["block_id"]],
                                            $value["book"],
                                            $value["paragraph"],
                                            $value["begin"],
                                            $value["end"],
                                            $value["channal"],
                                            $value["tag"],
                                            $value["author"],
                                            $_COOKIE["userid"],
                                            $value["text"],
                                            $value["language"],
                                            $value["ver"],
                                            $value["status"],
                                            mTime(),
                                            mTime(),
                                        ));

                                }

                                break;
                            case 3:
                                break;
                            case 4:
                                break;
                            case 5:
                                break;
                            case 6:
                                #逐词解析
                                $blockid = $blocks[$i]->block_id;
                                $query = "select * from "._TABLE_USER_WBW_BLOCK_." where id= ? ";
                                $stmt = $dbhWBW->prepare($query);
                                $stmt->execute(array($blockid));
                                $fBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $newBlockId = UUID::V4();
                                $newDocBlockList[] = array('type' => 6, 'block_id' => $newBlockId);
                                $arrBlockTransform[$fBlock[0]["id"]] = $newBlockId;
                                if (count($fBlock) > 0) {
                                    array_push($arrNewBlock,
                                        array($newBlockId,
                                            $fBlock[0]["id"],
                                            $_GET["channel"],
                                            $_COOKIE["userid"],
                                            $fBlock[0]["book"],
                                            $fBlock[0]["paragraph"],
                                            $fBlock[0]["style"],
                                            $fBlock[0]["lang"],
                                            $fBlock[0]["status"],
                                            mTime(),
                                            mTime(),
                                        ));
                                }

                                $query = "select * from "._TABLE_USER_WBW_." where block_id= ? ";
                                $stmtWBW = $dbhWBW->prepare($query);
                                $stmtWBW->execute(array($fBlock[0]["id"]));
                                $fBlockData = $stmtWBW->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($fBlockData as $value) {
                                    array_push($arrNewBlockData,
                                        array(UUID::V4(),
                                            $arrBlockTransform[$value["block_id"]],
                                            $value["book"],
                                            $value["paragraph"],
                                            $value["wid"],
                                            $value["word"],
                                            $value["data"],
                                            mTime(),
                                            mTime(),
                                            $value["status"],
                                            $_COOKIE["userid"],
                                        ));

                                }
                                break;
                            case 2:

                                break;
                        }

                    }
                    //逐词解析block数据块

                    if (count($arrNewBlock) > 0) {
                        $dbhWBW->beginTransaction();
                        $query = "INSERT INTO "._TABLE_USER_WBW_BLOCK_." ('id','parent_id','channal','owner','book','paragraph','style','lang','status','modify_time','receive_time') VALUES (?,?,?,?,?,?,?,?,?,?,?)";
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
                        $query = "INSERT INTO "._TABLE_USER_WBW_." ('id','block_id','book','paragraph','wid','word','data','modify_time','receive_time','status','owner') VALUES (?,?,?,?,?,?,?,?,?,?,?)";
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

                    //不复刻译文

                    //译文 block数据块
                    /*
                    if(count($arrSentNewBlock)>0){
                    $dbhSent->beginTransaction();
                    $query="INSERT INTO sent_block ('id','parent_id','book','paragraph','owner','lang','author','editor','tag','status','modify_time','receive_time') VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                    $stmtSentNewBlock = $dbhSent->prepare($query);
                    foreach($arrSentNewBlock as $oneParam){
                    //print_r($oneParam);
                    $stmtSentNewBlock->execute($oneParam);
                    }
                    // 提交更改
                    $dbhSent->commit();
                    if (!$stmtSentNewBlock || ($stmtSentNewBlock && $stmtSentNewBlock->errorCode() != 0)) {
                    $error = $dbhSent->errorInfo();
                    echo "error - $error[2] <br>";
                    }
                    else{
                    //译文 block块复刻成功
                    $count=count($arrNewBlock);
                    echo "wbw block $count recorders.<br/>";
                    }
                    }

                    if(count($arrSentNewBlockData)>0){
                    // 开始一个事务，逐词解析数据 关闭自动提交
                    $dbhSent->beginTransaction();
                    $query="INSERT INTO sentence ('id','block_id','book','paragraph','begin','end','channal','tag','author','editor','text','language','ver','status','modify_time','receive_time') VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?, ?)";
                    $stmtSentData = $dbhSent->prepare($query);
                    foreach($arrSentNewBlockData as $oneParam){
                    $stmtSentData->execute($oneParam);
                    }
                    // 提交更改
                    $dbhSent->commit();
                    if (!$stmtSentData || ($stmtSentData && $stmtSentData->errorCode() != 0)) {
                    $error = $dbhSent->errorInfo();
                    echo "error - $error[2] <br>";
                    }
                    else{
                    //译文 数据 复刻成功
                    $count=count($arrSentNewBlockData);
                    echo "new translation $count recorders.";
                    }
                    }
                     */

                    //插入记录到文件索引
                    $filesize = 0;
                    //服务器端文件列表
                    PDO_Connect("" . _FILE_DB_FILEINDEX_);
                    $query = "INSERT INTO fileindex ('id',
                                                       'parent_id',
                                                       'user_id',
                                                       'book',
                                                       'paragraph',
                                                       'file_name',
                                                       'title',
                                                       'tag',
                                                       'status',
                                                       'create_time',
                                                       'modify_time',
                                                       'accese_time',
                                                       'file_size',
                                                       'share',
                                                       'doc_info',
                                                       'doc_block',
                                                       'receive_time'
                                                       )
                                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    $stmt = $PDO->prepare($query);
                    $newDocId = UUID::v4();
                    $newDocInfo = $orgFileInfo;
                    $newDocInfo["id"] = $newDocId;
                    $newDocInfo["parent_id"] = $orgFileInfo["id"];
                    $newDocInfo["user_id"] = $_COOKIE["uid"];
                    $newDocInfo["doc_block"] = json_encode($newDocBlockList, JSON_UNESCAPED_UNICODE);
                    $newData = array($newDocInfo["id"],
                        $newDocInfo["parent_id"],
                        $newDocInfo["user_id"],
                        $newDocInfo["book"],
                        $newDocInfo["paragraph"],
                        $newDocInfo["file_name"],
                        $newDocInfo["title"],
                        $newDocInfo["tag"],
                        $newDocInfo["status"],
                        mTime(),
                        mTime(),
                        mTime(),
                        $newDocInfo["file_size"],
                        $newDocInfo["share"],
                        $newDocInfo["doc_info"],
                        $newDocInfo["doc_block"],
                        mTime(),
                    );
                    $stmt->execute($newData);
                    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                        $error = PDO_ErrorInfo();
                        echo "error - $error[2] <br>";
                    } else {
                        //文档列表插入成功

                        echo "doc list updata 1 recorders.";
                        echo "</div>";
                        echo "<h3>复刻成功</h3>";
                        echo "正在<a href='../studio/editor.php?op=opendb&doc_id={$newDocId}'>打开</a>文档";
                        echo "<script>";
                        echo "window.location.assign(\"../studio/editor.php?op=opendb&fileid={$newDocId}\");";
                        echo "</script>";
                    }
                } else {
                    echo "无效的文档id";
                }

            }
        }
    }
}

echo "</div>";
?>

</body>
</html>