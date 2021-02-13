<?php
#升级段落完成度数据库
require_once '../path.php';


$dns = "sqlite:"._FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$dns = "sqlite:"._FILE_DB_SENTENCE_;
$dbh_sent = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$dbh_pali_sent = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_pali_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


$dns = "sqlite:"._FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$valid_book = array();

#第一步 查询有多少书有译文
$query = "SELECT book from sentence where strlen>0 and begin<>''  and language<>'' and book <1000  group by book";
$stmt = $dbh_sent->prepare($query);
$stmt->execute();
$valid_book = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "book:".count($valid_book)."<br>\n";

#第一步 查询语言
$query = "SELECT language from sentence where strlen>0 and begin<>''  and language<>'' and book <1000   group by language";
$stmt = $dbh_sent->prepare($query);
$stmt->execute();
$result_lang = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "lang:".count($result_lang)."<br>\n";

$query = "DELETE FROM progress WHERE 1";
$sth_toc = $dbh_toc->prepare($query);
$sth_toc->execute();
$query = "DELETE FROM progress_chapter WHERE 1";
$sth_toc = $dbh_toc->prepare($query);
$sth_toc->execute();

/* 开始一个事务，关闭自动提交 */
$dbh_toc->beginTransaction();
$query = "INSERT INTO progress (book, para , lang , all_strlen , public_strlen) VALUES (?, ?, ? , ? ,? )";
$sth_toc = $dbh_toc->prepare($query);
foreach ($result_lang as $lang) {
	# 第二步 生成para progress 1,2,15,zh-tw
	
	#查询该语言有多少段
	$query = "select book,paragraph from sentence where strlen>0 and language= ? and book<1000 group by book,paragraph";
	$stmt = $dbh_sent->prepare($query);
	$stmt->execute(array($lang["language"]));
	$result_para = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result_para as $para) {
		
		# 查询每个段落的等效巴利语字符数
		$query = "select begin from sentence where strlen>0 and language= ? and book = ? and paragraph = ? and begin<>'' group by begin,end";
		$stmt = $dbh_sent->prepare($query);
		$stmt->execute(array($lang["language"],$para["book"],$para["paragraph"]));
		$result_sent = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(count($result_sent)>0){
			echo "book:{$para["book"]} para: {$para["paragraph"]}\n";
			#查询这些句子的总共等效巴利语字符数
			$place_holders = implode(',', array_fill(0, count($result_sent), '?'));
			$query = "select sum(length) as strlen from pali_sent where book = ? and paragraph = ? and begin in ($place_holders)";
			$sth = $dbh_pali_sent->prepare($query);
			$param = array();
			$param[] = $para["book"];
			$param[] = $para["paragraph"];
			foreach ($result_sent as $sent) {
				# code...
				$param[] = (int)$sent["begin"];
			}
			$sth->execute($param);
			$result_strlen = $sth->fetch(PDO::FETCH_ASSOC);
			if($result_strlen){
				$para_strlen = $result_strlen["strlen"];
			}
			else{
				$para_strlen = 0;
			}
			$sth_toc->execute(array($para["book"],$para["paragraph"],$lang["language"],$para_strlen,0));
		}
	}
}
$dbh_toc->commit();
if (!$sth_toc || ($sth_toc && $sth_toc->errorCode() != 0)) {
	/*  识别错误且回滚更改  */
	$sth_toc->rollBack();
	$error = $dbh_toc->errorInfo();
	echo "error:".$error[2]."\n";
}

#第三步生成 段落完成度库
/* 开始一个事务，关闭自动提交 */
$dbh_toc->beginTransaction();
$query = "INSERT INTO progress_chapter (book, para , lang , all_trans,public) VALUES (?, ?, ? , ? ,? )";
$sth_toc = $dbh_toc->prepare($query);

foreach ($valid_book as $key => $book) {
	echo "doing chapter in book ".$book["book"] ."\n";
	# code...
	$query = "SELECT paragraph , chapter_len from pali_text where level < 8 and book = ?";
	$stmt = $dbh_pali_text->prepare($query);
	$stmt->execute(array($book["book"]));
	$result_chapter = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result_chapter as $key => $chapter) {
		# 查询巴利字符数
		$query = "SELECT sum(strlen) as pali_strlen from pali_sent_index where book = ? and para between ? and ? ";
		$stmt = $dbh_pali_sent->prepare($query);
		$stmt->execute(array($book["book"],$chapter["paragraph"],$chapter["paragraph"]+$chapter["chapter_len"]));
		$result_chapter_strlen = $stmt->fetch(PDO::FETCH_ASSOC);
		if($result_chapter_strlen){
			$pali_strlen = (int)$result_chapter_strlen["pali_strlen"];
			# 译文等效字符数
			foreach ($result_lang as $lang) {
				$query = "SELECT sum(all_strlen) as all_strlen from progress where book = ? and (para between ? and ? )and lang = ?";
				$stmt = $dbh_toc->prepare($query);
				$stmt->execute(array($book["book"],$chapter["paragraph"],$chapter["paragraph"]+$chapter["chapter_len"],$lang["language"]));
				$result_chapter_trans_strlen = $stmt->fetch(PDO::FETCH_ASSOC);
				if($result_chapter_trans_strlen){
					$tran_strlen = (int)$result_chapter_trans_strlen["all_strlen"];
					if($tran_strlen>0){
						$progress = $tran_strlen/$pali_strlen;
						$sth_toc->execute(array($book["book"],$chapter["paragraph"],$lang["language"],$progress,0));
						//echo "{$book["book"]},{$chapter["paragraph"]},{$lang["language"]},{$progress}\n";
					}

				}
				#插入段落数据
			}			
		}
	}
}
$dbh_toc->commit();
if (!$sth_toc || ($sth_toc && $sth_toc->errorCode() != 0)) {
	/*  识别错误且回滚更改  */
	$sth_toc->rollBack();
	$error = $dbh_toc->errorInfo();
	echo "error:".$error[2]."\n";
}

?>