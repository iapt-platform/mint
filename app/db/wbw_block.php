<?php
require_once "../path.php";
require_once "../db/table.php";
require_once "../channal/function.php";

class WbwBlock extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USER_WBW_, _TABLE_USER_WBW_BLOCK_, "", "",$redis);
    }

	public function getPower($blockId){
		$channelInfo = new Channal($this->redis);
		$power = 0;
		$query = "SELECT channal,owner from "._TABLE_USER_WBW_BLOCK_."   where id= ?  ";
		$row = $this->fetch($query,array($blockId));
		if($row ){
			if(empty($row["channal"])){
				if($row["owner"]==$_COOKIE["user_uid"]){
					$power = 30;
				}
			}
			else{
				$power = $channelInfo->getPower($row["channal"]);
			}
		}
		return $power;
	}
}