<?php
		/*
        $table->beginTransaction($query)
			  ->set($date)
			  ->commit();
		*/
require_once "../redis/function.php";

class Table
{
    protected $dbh;
	protected $table;
    protected $redis;
    protected $errorMessage;
	protected $field_setting;
    function __construct($db,$table,$user="",$password="",$redis=false) {
        $this->dbh = new PDO($db, $user, $password,array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->redis = $redis;
		$this->table = $table;
    }
	
	public function setField($setting){
		$this->field_setting = $setting;
	}
	protected function fetch($query,$params){
		if (isset($params)) {
			$stmt = $this->dbh->prepare($query);
			if($stmt){
				$stmt->execute($params);
			}
			
		} else {
			$stmt = $PDO->query($query);
		}
		if($stmt){
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
		else{
			return false;
		}
	}

	function execute($query, $params=null){
		if (isset($params)) {
			$stmt = $this->dbh->prepare($query);
			if($stmt){
				$stmt->execute($params);
				return $stmt;				
			}
			else{
				return false;
			}

		} else {
			return $this->dbh->query($query);
		}
	}


	public function syncList($time){

	}

	public function syncGet($time){

	}
}

?>