<?php
require_once "../config.php";
require_once "../db/table.php";
/*
CREATE TABLE likes (
    id            INTEGER      PRIMARY KEY AUTOINCREMENT,
    like_type     VARCHAR (16) NOT NULL,
    resource_type VARCHAR (32) NOT NULL,
    resource_id   CHAR (36)    NOT NULL,
    user_id       INTEGER      NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP     NOT NULL //只做初始化,更新时不自动更新
);
*/
class Like extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_LIKE_, "likes", "", "",$redis);
    }

	public function  index(){
		$where["like_type"] = "like";
		$where["resource_type"] = $_GET["type"];
		$where["resource_id"] = explode($_GET["id"],",");
		echo json_encode($this->_index(["resource_id","user_id"],$where), JSON_UNESCAPED_UNICODE);
	}
	
	public function  list(){
		if(!isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
		}

		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		foreach ($data as $key => $value) {
			# code...
			$data[$key]['like']=$this->medoo->count($this->table,[
											'like_type'=>$value['like_type'],
											'resource_type'=>$value['resource_type'],
											'resource_id'=>$value['resource_id'],
											  ]);
		}
		if(isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
			foreach ($data as $key => $value) {
				# code...
				$data[$key]['me']=$this->medoo->count($this->table,[
												'like_type'=>$value['like_type'],
												'resource_type'=>$value['resource_type'],
												'resource_id'=>$value['resource_id'],
												'user_id'=>$userId,
												]);
			}
		}

		$this->result["ok"]=true;
		$this->result["message"]="";
		$this->result["data"]=$data;
		echo json_encode($this->result, JSON_UNESCAPED_UNICODE);

	}


	public function  create(){
		if(!isset($_COOKIE["userid"])){
			return;
		}
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		$data["user_id"] = $_COOKIE["userid"];
		$isExist = $this->medoo->has("likes",$data);
		if(!$isExist){
			echo json_encode($this->_create($data,["like_type","resource_type","resource_id","user_id"]), JSON_UNESCAPED_UNICODE);
		}
		else{
			$this->result["ok"]=false;
			$this->result["message"]="is exist";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}
	
	public function  delete(){
		if(!isset($_COOKIE["userid"])){
			return;
		}
		$where["like_type"] = $_GET["like_type"];
		$where["resource_type"] = $_GET["resource_type"];
		$where["resource_id"] = $_GET["resource_id"];
		$where["user_id"] = $_COOKIE["userid"];
		$row = $this->_delete($where);
		if($row["data"]>0){
			$this->result["data"] = $where;
		}else{
			$this->result["ok"]=false;
			$this->result["message"]="no delete";			
		}
		echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
	}
}

?>