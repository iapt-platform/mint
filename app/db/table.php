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
	public function syncList($time){

	}

	public function syncGet($time){

	}
}

?>