<?php
require_once '../path.php';

$dns = "sqlite:"._FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$dns = "sqlite:"._FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if(isset($_GET["book"])){
	$book = $_GET["book"];
}
if(isset($_GET["para"])){
	$para = $_GET["para"];
}

$query = "SELECT chapter_len FROM pali_text WHERE book = ? and paragraph = ?";
$stmt = $dbh_pali_text->prepare($query);
$stmt->execute(array($book,$para));
$paraInfo = $stmt->fetch(PDO::FETCH_ASSOC);
if($paraInfo){
	$query = "SELECT level FROM pali_text WHERE book = ? and (paragraph between ? and ?) and level<8 order by level ASC limit 0,1";
	$stmt = $dbh_pali_text->prepare($query);
	$stmt->execute(array($book,$para+1,$para+$paraInfo["chapter_len"]));
	$paraMax = $stmt->fetch(PDO::FETCH_ASSOC);
	if($paraMax){
		$query = "SELECT book, paragraph, level , toc as title FROM pali_text WHERE book = ? and (paragraph between ? and ?) and level = ?  limit 0,1000";
		$stmt = $dbh_pali_text->prepare($query);
		$stmt->execute(array($book,$para,$para+$paraInfo["chapter_len"],$paraMax["level"]));
		$paraList = $stmt->fetchAll(PDO::FETCH_ASSOC);	
		foreach ($paraList as $key => $value) {
			# 查进度
			$query = "SELECT lang, all_trans from progress_chapter where book=? and para=?";
			$stmt = $dbh_toc->prepare($query);
			$sth_toc = $dbh_toc->prepare($query);
			$sth_toc->execute(array($book,$para));
			$paraProgress = $sth_toc->fetchAll(PDO::FETCH_ASSOC);
			$paraList[$key]["progress"]=$paraProgress;
		}
		echo json_encode($paraList, JSON_UNESCAPED_UNICODE);
	}
	else{
		echo json_encode(array(), JSON_UNESCAPED_UNICODE);
	}
	
}
else{
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}


?>