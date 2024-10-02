<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__."/../config.php";
require_once __DIR__."/../setting.php";

class SnowFlakeId{
	protected $snowflake;
	function __construct() {
		$this->snowflake = new \Godruoyi\Snowflake\Snowflake(SnowFlake["DatacenterId"], SnowFlake["WorkerId"]);
		$this->snowflake->setStartTimeStamp(strtotime(_SnowFlakeDate_)*1000);
	}
	public function id(){
		return $this->snowflake->id();
	}
}