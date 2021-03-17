<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';


if (isset($_GET["id"])) {
	$output["id"]=$_GET["id"];
	PDO_Connect( _FILE_DB_CHANNAL_);
	$query = "SELECT name,create_time,owner,summary FROM channal  WHERE id = ? ";
	$channel = PDO_FetchRow($query, array($_GET["id"]));
	$strData="";
	if ($channel) {
		
		$_userinfo = new UserInfo();
		$name = $_userinfo->getName($channel["owner"]);

		$strData .= "<div>名称：".$channel["name"]."</div>";
		$strData .=  "<div>创建人：".$name["nickname"]."</div>";
		$strData .=  "<div>创建时间：".date("Y/m/d",$channel["create_time"]/1000)."</div>";
		$strData .=  "<div>简介：".$channel["summary"]."</div>";
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