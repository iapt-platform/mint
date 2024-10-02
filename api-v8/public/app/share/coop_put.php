<?php
//查询term字典

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';
require_once "../redis/function.php";
require_once "../collect/function.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$respond['status']=0;
$respond['message']="成功";
if(isset($_POST["res_id"])){
	$redis = redis_connect();
    #打开目标数据库
    $dest_db = _PG_DB_USER_SHARE_;#目标数据库
    $dest_table = _PG_TABLE_USER_SHARE_;#目标表名
    $PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
    $PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queryInsert = "INSERT INTO ".$dest_table." 
								(
                                    id,
									res_id,
									res_type,
									cooperator_id,
									cooperator_type,
									power,
									create_time,
									modify_time,
									created_at,
									updated_at) 
									VALUES (? , ? , ? , ?, ? , ? ,? , ? , ? , ? )";
    $stmtDEST = $PDO_DEST->prepare($queryInsert);

	$data = json_decode($_POST["user_info"]);
    try {
        $PDO_DEST->beginTransaction();
        foreach ($data as $key => $user) {
            # code...
            #插入目标表
            $create_time = mTime();
            $created_at = date("Y-m-d H:i:s.",$create_time/1000).($create_time%1000)." UTC";
            $updated_at = date("Y-m-d H:i:s.",$create_time/1000).($create_time%1000)." UTC";
            $commitData = array(
                $snowflake->id(),
                $_POST["res_id"],
                $_POST["res_type"],
                $user->id,
                $user->type,
                $_POST["power"],
                $create_time,
                $create_time,
                $created_at,
                $updated_at
            );
            $stmtDEST->execute($commitData);
        }
        $PDO_DEST->commit();

        $respond['status']=0;
        $respond['message']="成功";
        if($redis){
            switch ((int)$_POST["res_type"]) {
                case 1:
                    # pcs
                    $redis->del("power://pcs/".$_POST["res_id"]);
                    break;
                case 2:
                    # channel
                    $redis->del("power://channel/".$_POST["res_id"]);
                    break;
                case 3:
                    # code...
                    $redis->del("power://article/".$_POST["res_id"]);
                    break;
                case 4:
                    # 文集
                    $redis->del("power://collection/".$_POST["res_id"]);
                    # 删除文章列表权限缓存
                    $collection = new CollectInfo($redis);
                    $articleList = $collection->getArticleList($_POST["res_id"]);
                    foreach ($articleList as $key => $value) {
                        # code...
                        $redis->del("power://article/".$value);
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    }catch (Exception $e) {
    $PDO_DEST->rollBack();
    	$respond['status']=1;
        $respond['message']="Failed: " . $e->getMessage();
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    }
}
else{
	$respond['status']=1;
	$respond['message']="no res id";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}
?>