<?php
require_once __DIR__."/../../redis/function.php";

Class Controller{
	protected $redis;
	protected $response;

	function __construct($redis=true) {
		if($redis){
			$this->redis = redis_connect();
		}
		$response = ["ok"=>true,"message"=>"","data"=>null];
	}

	public function error($message){
		$this->response = ["ok"=>false,"message"=>$message,"data"=>null];
		echo json_encode($this->response, JSON_UNESCAPED_UNICODE);
	}

	public function ok($data){
		$this->response = ["ok"=>true,"message"=>"","data"=>$data];
		echo json_encode($this->response, JSON_UNESCAPED_UNICODE);
	}
}