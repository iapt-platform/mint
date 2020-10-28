<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["id"])){
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $id=$_GET["id"];
    $query = "select * from collect  where id = ? ";
    $Fetch = PDO_FetchRow($query,array($id));
    if($Fetch){
        $userinfo = new UserInfo();
        $user = $userinfo->getName($Fetch["owner"]);
        $Fetch["username"] = $user;
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
else if(isset($_GET["article"])){
    # 给文章编号，查文集信息
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $article=$_GET["article"];
    $query = "SELECT collect_id FROM article_list  WHERE article_id = ? ";
    $Fetch = PDO_FetchAll($query,array($article));
    
    /*  使用一个数组的值执行一条含有 IN 子句的预处理语句 */
    $params = array();
    foreach ($Fetch as $key => $value) {
        # code...
        $params[] = $value["collect_id"];
    }
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($params), '?'));

    $query = "SELECT * FROM collect WHERE id IN ($place_holders)";

    $Fetch = PDO_FetchAll($query,$params);
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>