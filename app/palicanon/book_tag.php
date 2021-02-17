<?php
require_once '../path.php';

$tag =  str_getcsv($_GET["tag"],",");//
$arrBookTag=json_decode(file_get_contents("../public/book_tag/en.json"));
$countTag = count($tag);
$output = array();
foreach ($arrBookTag as $bookkey => $bookvalue) {
    $isfind = 0;
    foreach ($tag as $tagkey => $tagvalue) {
        if(strpos($bookvalue->tag,':'.$tagvalue.':') !== FALSE){
            $isfind++;
        }
    }
    if($isfind==$countTag){
        $output[] = array($bookvalue);
    }
}


$dns = "sqlite:"._FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$dns = "sqlite:"._FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = "sqlite:"._FILE_DB_RESRES_INDEX_;
$dbh_res = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_res->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);




foreach ($output as $key => $value) {
	# code...
		$book = (int)$value[0]->book;
		$para = (int)$value[0]->para;
		$level = (int)$value[0]->level;
	if(count($output)<100 || (count($output)>100 && $level==1)){
		$query = "SELECT * FROM pali_text WHERE book = ? and paragraph = ?";
		$stmt = $dbh_pali_text->prepare($query);
		$stmt->execute(array($book,$para));
		$paraInfo = $stmt->fetch(PDO::FETCH_ASSOC);
		if($paraInfo){	
			# 查进度
			$query = "SELECT lang, all_trans from progress_chapter where book=? and para=?";
			$stmt = $dbh_toc->prepare($query);
			$sth_toc = $dbh_toc->prepare($query);
			$sth_toc->execute(array($book,$para));
			$paraProgress = $sth_toc->fetchAll(PDO::FETCH_ASSOC);
			$output[$key][0]->progress=$paraProgress;
		
			#查标题
			if(isset($_GET["lang"])){
				$query = "SELECT title from 'index' where book=? and paragraph=? and language=?";
				$stmt = $dbh_res->prepare($query);
				$sth_title = $dbh_res->prepare($query);
				$sth_title->execute(array($book,$para,$_GET["lang"]));
				$trans_title = $sth_title->fetch(PDO::FETCH_ASSOC);
				if($trans_title){
					$output[$key][0]->trans_title=$trans_title['title'];
				}
			}
		}

	}
	
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>