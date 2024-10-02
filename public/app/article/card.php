<?php
//

require_once "../config.php";
require_once '../redis/function.php';
require_once "../article/function.php";
require_once '../ucenter/function.php';


if (isset($_GET["id"])) {
	$output["id"]=$_GET["id"];
	$redis = redis_connect();
	$article = new Article($redis); 
	$result = $article->getInfo($_GET["id"]);
	if ($result) {
		$_userinfo = new UserInfo();
		$name = $_userinfo->getName($result["owner"]);
		$strData .= "<div>标题：".$result["title"]."</div>";
		$strData .=  "<div>创建人：".$name["nickname"]."</div>";
		$strData .=  "<div>创建时间：".date("Y/m/d",$result["create_time"]/1000)."</div>";
		$strData .=  "<div>简介：".$result["summary"]."</div>";
	} else {
		$strData .=  "unkow";
	}
	$output["data"] = $strData;
} else {
	$output["id"]=0;
	$output["data"] = "unkow";
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>