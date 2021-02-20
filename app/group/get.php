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
			#顶级组 列出小组
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
		#列出组文件
		{
			PDO_Connect("sqlite:"._FILE_DB_FILEINDEX_);
			$query = "SELECT * FROM power  WHERE user = ? ";
			$fileList = PDO_FetchAll($query,array($id));
			foreach ($fileList as $key => $value) {
				# code...
				$query = "SELECT title FROM fileindex  WHERE id = ? ";
				$file = PDO_FetchRow($query,array($value["doc_id"]));
				if($file){
					$fileList[$key]["title"]=$file["title"];
				}
				else{
					$fileList[$key]["title"]="";
				}
			}
			$output["file"] = $fileList;
		}
	}
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>