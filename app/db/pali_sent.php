<?php
require_once "../config.php";
require_once "../db/table.php";

class PaliSentence extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_PALI_SENTENCE_, _TABLE_PALI_SENT_, "", "",$redis);
    }

	public function getId($book,$para,$start,$end)
	{
		if (isset($book) && isset($para)) 
		{
			if($this->redis!==false){
				$result = $this->redis->hGet('pali://sent/' . $book . "_" . $para . "_" . $start . "_" . $end, "id");
				if($result!==FALSE){
					return $result;
				}
			}
			$id=0;
			$query = "SELECT id from ".$this->table." where book = ? and paragraph = ? and begin=? and end=?";
			$stmt = $this->dbh->prepare($query);
			$stmt->execute(array($book,$para,$start,$end));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($result) {
				$id= $result["id"];
			}
			if($this->redis){
				$this->redis->hSet('pali://sent/' . $book . "_" . $para . "_" . $start . "_" . $end, "id",$id);
			}
			return $id;
		} else {
			return 0;
		}
	}
	public function getInfo($id)
	{
		$query = "SELECT book,paragraph, word_begin as begin ,word_end as end from ".$this->table." where id = ? ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result;
		}
		return false;
	}

}

?>