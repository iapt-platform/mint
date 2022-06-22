<?php
require_once "../config.php";
require_once "../share/function.php";
require_once "../db/table.php";

class Channal extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_CHANNAL_, _TABLE_CHANNEL_, _DB_USERNAME_,_DB_PASSWORD_,$redis);
    }

    public function getChannal($id){
        if(empty($id)){
            return false;
        }
        $query = "SELECT * FROM ".$this->table." WHERE uid =? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $channal = $stmt->fetch(PDO::FETCH_ASSOC);
        if($channal){
            return $channal;
        }
        else{
            return false;
        }
	}
	public function getTitle($id)
	{
		if (isset($id)) {
			$query = "SELECT name FROM ".$this->table."  WHERE  uid = ? ";
			$stmt = $this->dbh->prepare($query);
			$stmt->execute(array($id));
			$channal = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($channel) {
				return $channel["name"];
			} else {
				return "";
			}
		} else {
			return "";
		}
	}
	public function insert($data){

	}
	public function update($data){

	}
	public function delete($data){

	}
	public function getPower($id){
		#查询用户对此channel是否有权限
		if(isset($_COOKIE["user_id"])){
			$userId = $_COOKIE["user_id"];
		}
		else{
			$userId='0';
		}
		if($this->redis!==false){
			$power = $this->redis->hGet("power://channel/".$id,$userId);
			if($power!==FALSE){
				return $power;
			}
		}
		$channelPower = 0;
		$query = "SELECT owner_uid,status FROM "._TABLE_CHANNEL_." WHERE uid=? and status>0 ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$channel = $stmt->fetch(PDO::FETCH_ASSOC);
		if($channel){
			if(!isset($_COOKIE["user_id"])  ){
				#未登录用户
				if($channel["status"]==30){
					#全网公开有建议权限
					return 10;
				}
				else{
					#其他状态没有任何权限
					return 0;
				}
				
			}
			if($channel["owner_uid"]==$_COOKIE["user_uid"]){
				return 30;
			}
			else if($channel["status"]>=30){
				#全网公开的 可以提交pr
				$channelPower = 10;
			}
		}
		#查询共享权限，如果共享权限更大，覆盖上面的的
		$sharePower = share_get_res_power($_COOKIE["user_uid"],$id);
		if($sharePower>$channelPower){
			$channelPower=$sharePower;
		}
		if($this->redis){
			$this->redis->hSet("power://channel/".$id,$_COOKIE["user_uid"],$channelPower);
		}
		
		return $channelPower;
	}

}

?>