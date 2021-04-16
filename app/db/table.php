<?php
class Table
{
    protected $dbh;
	protected $table;
    protected $redis;
    function __construct($db,$table,$user="",$password="",$redis=false) {
        $this->dbh = new PDO($db, $user, $password,array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->redis = $redis;
		$this->table = $table;

    }
}

?>