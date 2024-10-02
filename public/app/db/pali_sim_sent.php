<?php
require_once "../config.php";
require_once "../db/table.php";

class PaliSimSentence extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_PALI_SENTENCE_SIM_, _TABLE_SENT_SIM_, "", "",$redis);
    }

	public function getSimById($id)
	{

			if($this->redis!==false){
				$result = $this->redis->hGet('pali://sim/id' , $id );
				if($result!==FALSE){
					return  json_decode($result,true);
				}
			}
			$query = "SELECT sent2 as id  FROM ".$this->table." WHERE  sent1 = ? ";
			$stmt = $this->dbh->prepare($query);
			if($stmt){
				$stmt->execute(array($id));
				$simList = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$output =  json_encode($simList, JSON_UNESCAPED_UNICODE);
				if($this->redis){
					$this->redis->hSet('pali://sim/id',$id, $output);
				}
				
				return $simList;
			}
			else{
				return false;
			}

	}
	public function getInfo($id)
	{
		$query = "SELECT book,paragraph, begin,end from pali_sent where id = ? ";
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