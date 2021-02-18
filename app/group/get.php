<?php
//查询group 列表

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output = array();
if(isset($_GET["id"])){
    PDO_Connect("sqlite:"._FILE_DB_GROUP_);
    $id=$_GET["id"];
    $query = "SELECT * FROM group_info  WHERE id = ? ";
	$Fetch = PDO_FetchRow($query,array($id));
	if($Fetch){
		$output["info"] = $Fetch;
		if($Fetch["parent"]==0 ){
			#列出小组
			$query = "SELECT * FROM group_info  WHERE parent = ? ";
			$FetchList = PDO_FetchAll($query,array($id));
			$output["children"] = $FetchList;
		}
		else{
			$output["children"] = array();
			$query = "SELECT * FROM group_info  WHERE id = ? ";
			$parent_group = PDO_FetchRow($query,array($Fetch["parent"]));
			$output["parent"] = $parent_group;
		}
		#列出文件
		if(isset($_GET["list"]) && $_GET["list"]=="file"){
			PDO_Connect("sqlite:"._FILE_DB_FILEINDEX_);
			$query = "SELECT * FROM group_share  WHERE group_id = ? ";
			$fileList = PDO_FetchAll($query,array($id));
			$output["file"] = $fileList;
		}
	}
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>