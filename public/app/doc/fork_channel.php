<?php

/*拷贝其他人的文件
 *
 *
 */
require_once '../studio/index_head.php';
?>
<body id="file_list_body" >
<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../channal/function.php";
require_once "../redis/function.php";

$redis = redis_connect();

require_once '../studio/index_tool_bar.php';

echo '<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">';

if ($_COOKIE["uid"]) {
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    echo "<h3><a href='../ucenter/index.php?op=login'>登录</a>后才可以打开文档 </h3>";
    exit;
}

if(isset($_GET["para"])){
    $_para = json_decode($_GET["para"]);
}
else{
    echo "没有 para 编号";
    exit;
}

if (isset($_GET["src_channel"]) == false) {
    echo "没有 channel 编号";
    exit;
}
    //文档信息
    $mbook = $_GET["book"];
    $paragraph = implode(",",$_para);

if (isset($_GET["dest_channel"]) == false) {
    echo '<div class="file_list_block">';
    echo "<h2>选择一个空白的版风存储新的文档</h2>";
    echo "<div>原有版本中相同段落的数据将被覆盖</div>";
    echo "<form action='fork_channel.php' method='get'>";
    echo "<input type='hidden' name='book' value='{$_GET["book"]}' />";
    echo "<input type='hidden' name='para' value='{$_GET["para"]}' />";
    echo "<input type='hidden' name='src_channel' value='{$_GET["src_channel"]}' />";
    PDO_Connect(_FILE_DB_CHANNAL_);
    $query = "select * from channal where owner = '{$_COOKIE["userid"]}'   limit 0,100";
    $Fetch = PDO_FetchAll($query);
    $i = 0;
    PDO_Connect( _FILE_DB_USER_WBW_);	
    foreach ($Fetch as $row) {
        echo '<div class="file_list_row" style="padding:5px;display:flex;">';

        echo '<div class="pd-10"  style="max-width:2em;flex:1;">';
        echo '<input name="dest_channel" value="' . $row["id"] . '" ';
        if ($i == 0) {
            echo "checked";
        }
        echo ' type="radio" />';
        echo '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["name"] . '</div>';
        echo '<div class="title" style="flex:3;padding-bottom:5px;">' . $row["lang"] . '</div>';
        echo '<div class="title" style="flex:2;padding-bottom:5px;">';
        $query = "select count(*) from "._TABLE_USER_WBW_BLOCK_." where channal = '{$row["id"]}' and book='{$mbook}' and paragraph in ({$paragraph})  limit 0,100";
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

PDO_Connect( _FILE_DB_USER_WBW_);	

$channelInfo= new Channal($redis);

$srcPower = (int)$channelInfo->getPower($_GET["src_channel"]);

{

	{

        if ($srcPower == 30) {
            //自己的文档
            echo "这是自己的文档，不能复刻。";
        } else {
            //别人的文档
            //查询以前自己是否曾经复刻
            $query = "SELECT * from "._TABLE_USER_WBW_BLOCK_." where parent_channel=? and owner=? ";
            $FetchSelf = PDO_FetchAll($query,array($_GET["src_channel"],$_COOKIE["userid"]));
            $iFetchSelf = count($FetchSelf);
            if ($iFetchSelf > 0) {
                //以前打开过
                echo "文档已经复刻";
                echo "正在<a href='../studio/editor.php?op=openchannal&book={$_GET["book"]}&para={$_para[0]}&channal={$FetchSelf[0]["channal"]}'>打开</a>文档";
                echo "<script>";
                echo "window.location.assign(\"../studio/editor.php?op=openchannal&book={$_GET["book"]}&para={$_para[0]}&channal={$FetchSelf[0]["channal"]}\");";
                echo "</script>";
            } else {
                //以前没打开过
                echo "<h3>共享的文档，正在fork...</h3>";
                echo "<div style='display:none;'>";

				{
                    //复制数据
                    //打开逐词解析数据库
                    $dns = _FILE_DB_USER_WBW_;
                    $dbhWBW = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
                    $dbhWBW->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

                    //逐词解析新数据数组
                    $arrNewBlock = array();
                    $arrNewBlockData = array();
                    $arrBlockTransform = array();

                    $blocks = $_para;
                    for ($i = 0; $i < count($blocks); $i++) {
						$query = "SELECT id from "._TABLE_USER_WBW_BLOCK_." where book= ? and paragraph = ? and channal = ? ";
						$stmt = $dbhWBW->prepare($query);
						$stmt->execute(array($_GET["book"],$iPara,$_GET["dest_channel"]));
						$fDest = $stmt->fetch(PDO::FETCH_ASSOC);
						if($fDest){
							#旧的逐词解析数据块wbw_block id 
							$destId = $fDest["id"];
						}
						#逐词解析
						$iPara = $blocks[$i];
						$query = "SELECT * from "._TABLE_USER_WBW_BLOCK_." where book= ? and paragraph = ? and channal = ? ";
						$stmt = $dbhWBW->prepare($query);
						$stmt->execute(array($_GET["book"],$iPara,$_GET["src_channel"]));
						$fBlock = $stmt->fetchAll(PDO::FETCH_ASSOC);
						if(isset($destId)){
							$newBlockId = $destId;
						}
						else{
							$newBlockId = UUID::V4();
						}
						$arrBlockTransform[$fBlock[0]["id"]] = $newBlockId;
						if (count($fBlock) > 0) {
							array_push($arrNewBlock,
								array($newBlockId,
									"",
									$_GET["dest_channel"],
									$_GET["src_channel"],
									$_COOKIE["userid"],
									$fBlock[0]["book"],
									$fBlock[0]["paragraph"],
									$fBlock[0]["style"],
									$fBlock[0]["lang"],
									$fBlock[0]["status"],
									mTime(),
									mTime(),
									mTime()
								));
						}

						$query = "SELECT * from "._TABLE_USER_WBW_." where block_id= ? ";
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

                    }

					# 查找目标block是否存在

					//删除旧的逐词解析block数据块
					$query = "DELETE from "._TABLE_USER_WBW_BLOCK_." where  paragraph = ? AND book = ? AND channal = ? ";
					$stmt = $dbhWBW->prepare($query);
					$stmt->execute(array($iPara,$_GET["book"],$_GET["dest_channel"]));
					
                    //新增逐词解析block数据块
                    if (count($arrNewBlock) > 0) {
                        $dbhWBW->beginTransaction();
                        $query = "INSERT INTO "._TABLE_USER_WBW_BLOCK_." ('id','parent_id','channal','parent_channel','owner','book','paragraph','style','lang','status','modify_time','receive_time','create_time') VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
                        $stmtNewBlock = $dbhWBW->prepare($query);
                        foreach ($arrNewBlock as $oneParam) {
                            $stmtNewBlock->execute($oneParam);
                        }
                        // 提交更改
                        $dbhWBW->commit();
                        if (!$stmtNewBlock || ($stmtNewBlock && $stmtNewBlock->errorCode() != 0)) {
                            $error = $dbhWBW->errorInfo();
                            echo "error - $error[2] <br>";
							exit;
                        } else {
                            //逐词解析block块复刻成功
                            $count = count($arrNewBlock);
                            echo "wbw block $count recorders.<br/>";
                        }
                    }

					//删除逐词解析数据块
					if(isset($destId)){
						$query = "DELETE from "._TABLE_USER_WBW_." where  block_id = ? ";
						$stmt = $dbhWBW->prepare($query);
						$stmt->execute($destId);
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
							exit;
                        } else {
                            //逐词解析 数据 复刻成功
                            $count = count($arrNewBlockData);
                            echo "new wbw $count recorders.";
                        }
                    }

                   {
                        //成功
                        echo "doc list updata 1 recorders.";
                        echo "</div>";
                        echo "<h3>复刻成功</h3>";
                        echo "正在<a href='../studio/editor.php?op=openchannal&book={$_GET["book"]}&para={$_para[0]}&channal={$_GET["dest_channel"]}'>打开</a>文档";
                        echo "<script>";
                        echo "window.location.assign(\"../studio/editor.php?op=openchannal&book={$_GET["book"]}&para={$_para[0]}&channal={$_GET["dest_channel"]}\");";
                        echo "</script>";
                    }
                }

            }
        }
    }
}

echo "</div>";
?>

</body>
</html>