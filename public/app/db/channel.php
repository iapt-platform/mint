<?php
/*
废弃
*/
require_once "../config.php";
require_once "../db/table.php";
require_once "../public/function.php";

class Channel extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_CHANNAL_, _TABLE_CHANNEL_, _DB_USERNAME_,_DB_PASSWORD_,$redis);
    }

	public function  index(){
		switch ($_GET["view"]) {
			case 'studio':
				# code...
				break;
			case 'user':
				# code...
				break;
			default:
				# code...
				break;
		}
		$where["like_type"] = "like";
		$where["resource_type"] = $_GET["type"];
		$where["resource_id"] = explode($_GET["id"],",");
		echo json_encode($this->_index(["id","name","lang","status"],$where), JSON_UNESCAPED_UNICODE);
	}

	public function create($data=null){
		if($data===null){
			if(!isset($_COOKIE["userid"])){
				return;
			}
			$json = file_get_contents('php://input');
			$data = json_decode($json,true);
			$data["owner_uid"] = $_COOKIE["user_uid"];
			$data["editor_id"] = $_COOKIE["user_id"];
		}

		$isExist = $this->medoo->has($this->table,["owner_uid"=>$data["owner_uid"],"name"=>$data["name"]]);
		if(!$isExist){
			$data["id"] = $this->SnowFlake->id();
			$data["uid"] = UUID::v4();
			$data["create_time"] = mTime();
			$data["modify_time"] = mTime();
			$result =  $this->_create($data,["id","uid","owner_uid",'editor_id',"lang","name","summary","status","create_time","modify_time"]);
		}
		else{
			$this->result["ok"]=false;
			$this->result["message"]="is exist";
			$result = $this->result;
		}
		if($data===null){
			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		}else{
			return $result;
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