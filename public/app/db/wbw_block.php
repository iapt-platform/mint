<?php
require_once "../config.php";
require_once "../db/table.php";
require_once "../channal/function.php";

class WbwBlock extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USER_WBW_, _TABLE_USER_WBW_BLOCK_, _DB_USERNAME_, _DB_PASSWORD_,$redis);
    }

	public function getPower($blockId){
		$channelInfo = new Channal($this->redis);
		$power = 0;
		$query = "SELECT channel_uid , creator_uid  from "._TABLE_USER_WBW_BLOCK_."   where uid= ?  ";
		$row = $this->fetch($query,array($blockId));
		if($row ){
			if(empty($row["channel_uid"])){
				if($row["creator_uid"]==$_COOKIE["userid"]){
					$power = 30;
				}
			}
			else{
				$power = $channelInfo->getPower($row["channel_uid"]);
			}
		}
		return $power;
	}
}