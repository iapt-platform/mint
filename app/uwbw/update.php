<?php
/*
get xml doc from db
 */
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/active.php";
require_once "../redis/function.php";

$respond['status'] = 0;
$respond['message'] = "";

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

    PDO_Connect(_FILE_DB_USER_WBW_);

    /* 开始一个事务，关闭自动提交 */
    $PDO->beginTransaction();
    $query = "UPDATE wbw SET data= ?  , receive_time= ?  , modify_time= ?   where block_id= ?  and wid= ?  ";
    $sth = $PDO->prepare($query);

    foreach ($aData as $data) {
        $sth->execute(array($data->data, mTime(), $data->time, $data->block_id, $data->word_id));
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
	if (count($aData) ==1){
		$redis = redis_connect();
		try {
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
