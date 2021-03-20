<?php
require_once "../path.php";

function channel_get_title($id)
{
    if (isset($id)) {
		PDO_Connect( _FILE_DB_CHANNAL_);
		$query = "SELECT name FROM channal  WHERE id = ? ";
		$channel = PDO_FetchRow($query, array($id));
		if ($channel) {
			return $channel["name"];
		} else {
			return "";
		}
    } else {
        return "";
    }
}

class Channal
{
    public $dbh;
    public function __construct() {
        $dns = ""._FILE_DB_CHANNAL_;
        $this->dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    }

    public function getChannal($id){
        $query = "SELECT * FROM channal WHERE id= ? ";
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
	
	public function getPower($id){
		#查询用户对此channel是否有权限		
		if(!isset($_COOKIE["userid"])){
			return 0;
		}
		$channelPower = 0;
		$query = "SELECT owner,status FROM channal WHERE id=? and status>0 ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$channel = $stmt->fetch(PDO::FETCH_ASSOC);
		if($channel){
			if($channel["owner"]==$_COOKIE["userid"]){
				return 30;
			}
			else if($channel["status"]>=30){
				#全网公开的 可以提交pr
				$channelPower = 10;
			}
		}

		$sharePower = share_get_res_power($_COOKIE["userid"],$id);
		if($sharePower>$channelPower){
			$channelPower=$sharePower;
		}
		return $channelPower;
	}

}

?>