<?php
require_once "../public/_pdo.php";
require_once "../path.php";

$result["error"]="";
$result["data"]=array();

if(isset($_GET["book"])){
	$book = $_GET["book"];
}
else{
	$result["error"]="no param:book";
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	exit;
}
if(isset($_GET["para"])){
	$para = $_GET["para"];
}
else{
	$result["error"]="no param :para";
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	exit;
}

	PDO_Connect("sqlite:"._FILE_DB_PAGE_INDEX_);
	$query="select bookid,cspara ,book_name from cs6_para where book = ? and para=?";
	$fetch = PDO_FetchAll($query,array($book,$para));
	if(count($fetch)>0){
		$place_holders = implode(',', array_fill(0, count($fetch), '?'));
		$query="SELECT book, para from cs6_para where bookid = ? and cspara in  ($place_holders) and book_name <> ?  ";
		$param[] = $fetch[0]["bookid"];

		foreach ($fetch as $key => $value) {
			$param[] =$value["cspara"];
		}		
		$param[] = $fetch[0]["book_name"];
		$fetch = PDO_FetchAll($query,$param);
		$result["data"]=$fetch;
	}
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>