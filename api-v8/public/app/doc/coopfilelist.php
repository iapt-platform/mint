<?php
/*
获取我的文档 文件列表
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/function.php";

if($_COOKIE["uid"]){
	$uid=$_COOKIE["uid"];
}
else{
	echo "尚未登录";
	exit;
}

    PDO_Connect(_FILE_DB_FILEINDEX_);
    $query = "SELECT * from power where user = ? order by modify_time DESC";
    $Fetch = PDO_FetchAll($query,array($_COOKIE["userid"]));
    $result=array();
    foreach($Fetch as $row){
        $query = "SELECT * from "._TABLE_FILEINDEX_." where uid = ?  ";
        $FetchFile = PDO_FetchAll($query,array($row['doc_id']));
        if(count($FetchFile)>0){
            $FetchFile[0]["power"]=$row["power"];
            $FetchFile[0]["power_status"]=$row["status"];
            $FetchFile[0]["power_create_time"]=$row["create_time"];
            $FetchFile[0]["power_modify_time"]=$row["modify_time"];
            $FetchFile[0]["user_name"]=ucenter_get($FetchFile[0]["user_id"],"");
            $query = "SELECT uid from "._TABLE_FILEINDEX_." where parent_id = ? and user_id = ? ";
            $FetchFile[0]["my_doc_id"] = PDO_FetchOne($query,array($row['doc_id'],$uid));
            $FetchFile[0]["path"] = _get_para_path($FetchFile[0]["book"],$FetchFile[0]["paragraph"]);
            $result[] = $FetchFile[0];
        }
    }
	echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>
