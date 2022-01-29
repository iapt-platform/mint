<?php
#获取文集信息

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../collect/function.php';
require_once '../redis/function.php';

$redis = redis_connect();
if(isset($_GET["id"])){
	//查询权限

	$collection = new CollectInfo($redis); 
	$power = $collection->getPower($_GET["id"]);
	if($power<10){
		echo json_encode(array(), JSON_UNESCAPED_UNICODE);
        exit;
	}
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $id=$_GET["id"];
    $query = "SELECT uid as id,title, subtitle,summary,article_list,owner,setting,status,lang,create_time,modify_time from "._TABLE_COLLECTION_."  where uid = ? ";
    $Fetch = PDO_FetchRow($query,array($id));
    if($Fetch){
        $userinfo = new UserInfo();
        $user = $userinfo->getName($Fetch["owner"]);
        $Fetch["username"] = $user;
        #查询文集中文档列表
        $query = "SELECT level,article_id as article,title from "._TABLE_ARTICLE_COLLECTION_."  where collect_id = ? order by id ASC";
        $fArticle_list = PDO_FetchAll($query,array($id));    
        $Fetch["article_list"] = json_encode($fArticle_list, JSON_UNESCAPED_UNICODE);
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
else if(isset($_GET["article"])){
    # 给文章编号，查文集信息
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $article=$_GET["article"];
    $query = "SELECT collect_id FROM "._TABLE_ARTICLE_COLLECTION_."  WHERE article_id = ? ";
    $Fetch = PDO_FetchAll($query,array($article));
    
    /*  使用一个数组的值执行一条含有 IN 子句的预处理语句 */
    $params = array();
    foreach ($Fetch as $key => $value) {
        # code...
        $params[] = $value["collect_id"];
    }
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($params), '?'));

    $query = "SELECT * FROM "._TABLE_COLLECTION_." WHERE uid IN ($place_holders)";

    $Fetch = PDO_FetchAll($query,$params);
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>