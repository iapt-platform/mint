<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';


if (isset($_GET["id"])) {
	$output["id"]=$_GET["id"];
	PDO_Connect( _FILE_DB_USERINFO_);
    $query = "SELECT userid as id ,username,nickname,create_time FROM "._TABLE_USER_INFO_." WHERE userid=?";
	$channel = PDO_FetchRow($query, array($_GET["id"]));
	$strData="";
	if ($channel) {
		$strData .= "<div>昵称：".$channel["nickname"]."</div>";
		$strData .=  "<div>用户名：".$channel["username"]."</div>";
		$strData .=  "<div>注册时间：".date("Y/m/d",$channel["create_time"]/1000)."</div>";
		$strData .=  "<div><a href='../uhome/index.php?userid=".$channel["id"]."' target='_blank'>个人空间</a></div>";
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
