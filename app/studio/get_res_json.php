<?php
/*
获取某书的资源列表
输入：book id
      album id
	  paragraph
输出：资源列表的json数据
*/
require_once "../studio/checklogin.inc";
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../studio/public.inc";


if(isset($_GET["book"])){
	$book=$_GET["book"];
}
else{
	echo "no book id";
	exit;
}
if(substr($book,0,1)=='p'){
	$book=substr($book,1);
}


if(isset($_GET["paragraph"])){
	$paragraph = $_GET["paragraph"];
}
else{
	$paragraph = -1;
}

function format_file_size($size){
	if($size<102){
		$str_size=$size."B";
	}
	else if($size<(1024*102)){
		$str_size=sprintf("%.1f KB",$size/1024);
	}
	else{
		$str_size=sprintf("%.1f MB",$size/(1024*1024));
	}
	return($str_size);
}

	$db_file = $dir_palicanon.'res.db3';
	PDO_Connect("sqlite:$db_file");

	$res=array();
	//查书
	if($paragraph==-1){
		//相关专辑
		$query = "select * from album where album.book='$book' ";
		$Fetch_ather = PDO_FetchAll($query);
		$iFetchAther=count($Fetch_ather);
		if($iFetchAther>0){
			foreach($Fetch_ather as $one_album){
				array_push($res,array("book"=>$book,
									"album_id"=>$one_album["id"],
									"paragraph"=>-1,
									"type"=>$one_album["type"],
									"title"=>$one_album["title"],
									"author"=>$one_album["author"]
									));
				if($one_album["type"]==1){
					array_push($res,array("book"=>$book,
										"album_id"=>$one_album["id"],
										"paragraph"=>-1,
										"type"=>6,
										"title"=>$one_album["title"],
										"author"=>$one_album["author"]
										));		
				}
			}
		}
		//相关专辑结束
	}
	else{
		//查书中的一个段
		$db_file = $dir_palicanon.'res.db3';
		PDO_Connect("sqlite:$db_file");			
		$query = "select * from 'index' where book='{$book}' and paragraph='{$paragraph}' and type < '5' ";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			//可用资源
			foreach($Fetch as $one_album){
				array_push($res,array("book"=>$book,
									  "album_id"=>$one_album["album"],
									  "paragraph"=>$paragraph,
									  "type"=>$one_album["type"],
									  "title"=>$one_album["title"],
									  "author"=>$one_album["author"],
									  "editor"=>$one_album["editor"],
									  "edition"=>$one_album["edition"],
									  "language"=>$one_album["language"],
									  "level"=>$one_album["level"]
									  ));
				if($one_album["type"]==1){
					array_push($res,array("book"=>$book,
										"album_id"=>UUID(),
										"paragraph"=>$paragraph,
										"type"=>6,
										"title"=>$one_album["title"],
										"author"=>$one_album["author"],
										"editor"=>$one_album["editor"],
									  "edition"=>$one_album["edition"],
									  "language"=>$one_album["language"],
									  "level"=>$one_album["level"]
										));

				}
			}
		}
		//查共享文档
		$db_file = _FILE_DB_FILEINDEX_;
		PDO_Connect("sqlite:$db_file");
		$query = "select * from fileindex where book='$book' and paragraph=$paragraph  and status>0 and share>0 order by create_time";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			foreach($Fetch as $one_file){
					array_push($res,array("type"=>"share",
										  "id"=>$one_file["id"],
										  "title"=>$one_file["title"],
										  "author"=>$one_file["user_id"]
										  ));					
			}
		}
		//查我的文档
		if($UID!=""){
			$query = "select * from fileindex where book='$book' and paragraph=$paragraph and status!=0 and user_id='{$UID}' order by create_time DESC";
			$Fetch = PDO_FetchAll($query);
			$iFetch=count($Fetch);
			if($iFetch>0){
				foreach($Fetch as $one_file){
						array_push($res,array("type"=>"mydoc",
											  "id"=>$one_file["id"],
											  "title"=>$one_file["title"],
											  "file_name"=>$one_file["file_name"],
											  "author"=>$one_file["user_id"]
											  ));					
				}
			}
		}
	}

	echo json_encode($res, JSON_UNESCAPED_UNICODE);
	
	?>

