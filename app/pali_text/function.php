<?php
require_once "../path.php";
require_once "../db/table.php";

class PaliText extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_PALITEXT_, "pali_text", "", "",$redis);
    }
	
	public function getPath($book,$para){
		if($this->redis!==false){
			$path = $this->redis->hGet("pali_text://path",$book."-".$para);
			if($path!==FALSE){
				return json_decode($path,true);
			}
		}
		$path = array();
		$parent = $para;
		$deep = 0;
		$sFirstParentTitle = "";
		//循环查找父标题 得到整条路径
		while ($parent > -1) {
			$query = "select * from pali_text where \"book\" = ? and \"paragraph\" = ? limit 0,1";
			$stmt = $this->dbh->prepare($query);
			$stmt->execute(array($book, $parent));
			$FetParent = $stmt->fetch(PDO::FETCH_ASSOC);

			$toc =array("book"=>$book,"para"=>$parent,"level"=>$FetParent["level"],"title"=>$FetParent["toc"]);

			$path[] = $toc;

			$parent = $FetParent["parent"];
			$deep++;
			if ($deep > 5) {
				break;
			}
		}
		if($this->redis){
			$this->redis->hSet("pali_text://path",$book."-".$para,json_encode($path,JSON_UNESCAPED_UNICODE));
		}
		return ($path);
	}

	public function getPathHtml($arrPath){
		$path="";
		foreach ($arrPath as $key => $value) {
			# code...
			$toc = "<chapter book='{$value["book"]}' para='{$value["para"]}' title='{$value["title"]}'>{$value["title"]}</chapter>";
			if ($path == "") {
				if ($value["level"] < 100) {
					$path = $toc;
				} else {
					$path = "<para book='{$value["book"]}' para='{$value["para"]}' title='{$value["title"]}'>{$value["para"]}</para>";
				}
			} else {
				$path = $toc . $path;
			}
		}
		return $path;
	}
}

class PaliBook extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_PALITEXT_, "books", "", "",$redis);
    }
	
	public function getBookTitle($book,$para){
		/*
		if($this->redis!==false){
			$result = $this->redis->hGet("pali_text://book",$book."-".$para);
			if($result!==FALSE){
				return $result;
			}
		}
		*/
		$query = "select title from books where \"book\" = ? and \"paragraph\" = ? limit 0,1";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($book, $para));
		$book = $stmt->fetch(PDO::FETCH_ASSOC);
		if($book){
			if($this->redis){
				//$this->redis->hSet("pali_text://book",$book."-".$para,$book["title"]);
			}
			return $book["title"];
		}
		else{
			return false;
		}
	}
}

?>