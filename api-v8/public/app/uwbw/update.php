<?php
/*
get xml doc from db
 */
include("../log/pref_log.php");
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/active.php";
require_once "../redis/function.php";
require_once "../channal/function.php";
require_once "../db/wbw_block.php";


$respond['status'] = 0;
$respond['message'] = "";
$redis = redis_connect();
$channelInfo = new Channal($redis);
$_WbwBlock = new WbwBlock($redis);

if (isset($_POST["data"])) {
    $aData = json_decode($_POST["data"]);
} else {
    $respond['status'] = 1;
    $respond['message'] = "no data";
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($aData) > 0) {
    add_edit_event(_WBW_EDIT_, "{$aData[0]->book}-{$aData[0]->para}-{$aData[0]->word_id}");

    PDO_Connect(_FILE_DB_USER_WBW_,_DB_USERNAME_,_DB_PASSWORD_);

	#确定block id 的写入权限
	$listBlockId=array();
	foreach ($aData as $data) {
        $listBlockId[$data->block_id]=0;
    }
	#查询channel 的 权限
	$maxPower=0;
	foreach ($listBlockId as $key => $value) {
		$listBlockId[$key] = $_WbwBlock->getPower($key);
		if($listBlockId[$key]>$maxPower){
			$maxPower = $listBlockId[$key];
		}
	}

    /* 开始一个事务，关闭自动提交 */
    $PDO->beginTransaction();
    $query = "UPDATE "._TABLE_USER_WBW_." SET data= ?  , editor_id = ? , status = ?, modify_time= ? , updated_at = now()  where block_uid= ?  and wid= ?  ";
    $sth = $PDO->prepare($query);

    foreach ($aData as $data) {
		if($listBlockId[$data->block_id]>=20){
            if(!isset($data->status) || empty($data->status)){
                $status = 7;
            }else{
                $status = (int)$data->status;
            }
			$sth->execute(array($data->data, $_COOKIE['uid'], $status , mTime(), $data->block_id, $data->word_id));
		}
    }
    $PDO->commit();

    $respond = array("status" => 0, "message" => "");

    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        /*  识别错误且回滚更改  */
        $PDO->rollBack();
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];

    } else {
        $respond['status'] = 0;
        $respond['message'] = "成功";
    }
	
	if($maxPower<20){
		$respond['status'] = 1;
        $respond['message'] = "没有修改权限";
	}
	if (count($aData) ==1){
		try {
			#将数据插入redis 作为自动匹配最新数据
			if($redis){
				$xmlString = "<root>" . $data->data . "</root>";
				$xmlWord = simplexml_load_string($xmlString);
				$wordsList = $xmlWord->xpath('//word');
				foreach ($wordsList as $word) {
					$pali = $word->real->__toString();
					$newword[]=array(	"0",
									$pali,
									$word->type->__toString(),
									$word->gramma->__toString(),
									$word->parent->__toString(),
									$word->mean->__toString(),
									"",
									$word->org->__toString(),
									$word->om->__toString(),
									"7",
									"100",
									"my",
									"none"
					);
					$redis->hSet("wbwdict://new/".$_COOKIE["userid"],$pali,json_encode($newword, JSON_UNESCAPED_UNICODE));
				}
			}

	
		} catch (Throwable $e) {
			echo "Captured Throwable: " . $e->getMessage();
		}
	}
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
} else {
    $respond['status'] = 1;
    $respond['message'] = "no data";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}

PrefLog();