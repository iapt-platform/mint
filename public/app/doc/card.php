<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';


if (isset($_GET["id"])) {
	$output["id"]=$_GET["id"];
	PDO_Connect( _FILE_DB_FILEINDEX_);
	$query = "SELECT title,create_time,user_id as owner FROM "._TABLE_FILEINDEX_."  WHERE uid = ? ";
	$result = PDO_FetchRow($query, array($_GET["id"]));
	$strData="";
	if ($result) {
		
		$_userinfo = new UserInfo();
		$name = $_userinfo->getName($result["owner"]);

		$strData .= "<div>标题：".$result["title"]."</div>";
		$strData .=  "<div>创建人：".$name["nickname"]."</div>";
		$strData .=  "<div>创建时间：".date("Y/m/d",$result["create_time"]/1000)."</div>";
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