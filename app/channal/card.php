<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';
require_once '../public/load_lang.php';


if (isset($_GET["id"])) {
	$output["id"]=$_GET["id"];
	PDO_Connect( _FILE_DB_CHANNAL_);
	$query = "SELECT name,create_time,owner,summary,lang FROM channal  WHERE id = ? ";
	$channel = PDO_FetchRow($query, array($_GET["id"]));
	$strData="";
	if ($channel) {
		$_userinfo = new UserInfo();
		$name = $_userinfo->getName($channel["owner"]);

		$strData .= "<div>{$_local->gui->name}：".$channel["name"]."</div>";
		$strData .=  "<div>{$_local->gui->owner}：".$name["nickname"]."</div>";
		$strData .=  "<div>{$_local->gui->created_time}：".date("Y/m/d",$channel["create_time"]/1000)."</div>";
		$strData .=  "<div>{$_local->gui->language}：".$channel["lang"]."</div>";
		$strData .=  "<div>{$_local->gui->introduction}：".$channel["summary"]."</div>";
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