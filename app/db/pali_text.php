<?php
require_once "../path.php";
require_once "../db/table.php";

class PaliText extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_PALITEXT_, "pali_text", "", "",$redis);
    }

	public function index(){
		$view = $_GET["view"];
		switch ($view) {
			case 'toc':
				# code...
				$book = $_GET["book"];
				$par = $_GET["par"];

				do {
					# code...
					$parent = $this->medoo->get(
					$this->table,
					["parent","paragraph","chapter_len"],
					["book"=>$book,"paragraph"=>$par]
					);
					$par = $parent["parent"];
				} while ($parent["parent"] > -1);
				$this->_index(["book","paragraph","level","toc","next_chapter","parent"],["level[<]"=>8,"book"=>$book,"paragraph[>]"=>$parent["paragraph"],"paragraph[<]"=>$parent["paragraph"]+$parent["chapter_len"]]);
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				break;
			default:
				# code...
				break;
		}
	}

	public function getTitle($book,$para)
	{
		if (isset($book) && isset($para)) {
			if($this->redis!==false){
				$title = $this->redis->hGet("para_title://pali","{$book}-{$para}");
				if($title!==FALSE){
					return $title;
				}
			}
			$title="";
			$query = "select * from pali_text where book = ? and paragraph = ?";
			$stmt = $this->dbh->prepare($query);
			$stmt->execute(array($book,$para));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($result) {
				if($result["level"]>0 && $result["level"]<8){
					$title= $result["toc"];
				}
				else{
					$query = "select * from pali_text where book = ? and paragraph = ?";
					$stmt = $this->dbh->prepare($query);
					$stmt->execute(array($book,$result["parent"]));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($result) {
						$title= $result["toc"];
					}
					else{
						$title= "";
					}
				}
				
			} else {
				$title= "";
			}
			if($this->redis){
				$this->redis->hSet("para_title://pali","{$book}-{$para}",$title);
			}
			return $title;
		} else {
			$title= "";
		}
	}


}

?>