<?php
#新增群组或项目
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$respond = array("status" => 0, "message" => "ok");
set_exception_handler(function($e){
    $respond['status'] = 1;
    $respond['message'] = $e->getFile().$e->getLine().$e->getMessage();
	exit;
});


if (isset($_COOKIE["userid"])) {
    PDO_Connect(_FILE_DB_GROUP_);
    try{
        $PDO->beginTransaction();
        #先查询是否有重复的组名
        $query = "SELECT id FROM "._TABLE_GROUP_INFO_."  WHERE name = ? ";
        $Fetch = PDO_FetchRow($query, array($_POST["name"]));
        if ($Fetch) {
            $respond['status'] = 1;
            $respond['message'] = "错误:有相同的组名称,请选择另一个名称。";
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $query = "INSERT INTO "._TABLE_GROUP_INFO_." ( id, uid  , name  , description  , owner ,create_time,modify_time )
                            VALUES  ( ?, ? , ? , ? , ? , ?  ,? ) ";
        $sth = $PDO->prepare($query);
        $newid = UUID::v4();
        $sth->execute(array($snowflake->id(),$newid,  $_POST["name"], "",  $_COOKIE["user_uid"], mTime(), mTime()));


        #将创建者添加到成员中
        $query = "INSERT INTO "._TABLE_GROUP_MEMBER_." (id,  user_id  , group_id  , power , group_name , level ,  status )
            VALUES  ( ? , ? , ? , ? , ? , ?  ,? ) ";
        $sth = $PDO->prepare($query);
        $sth->execute(array($snowflake->id(),$_COOKIE["user_uid"], $newid, 0, $_POST["name"], 0, 1));
        $PDO->commit();    
    }catch (Exception $e) {
        $PDO->rollBack();
    	$respond['status']=1;
        $respond['message']="Failed: " . $e->getMessage();
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    }

}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
