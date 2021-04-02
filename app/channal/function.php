<?php
require_once "../path.php";
require_once "../share/function.php";

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
    private $dbh;
    public function __construct() {
        $this->dbh = new PDO(_FILE_DB_CHANNAL_, "", "",array(PDO::ATTR_PERSISTENT=>true));
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
	public function getTitle($id)
	{
		if (isset($id)) {
			$query = "SELECT name FROM channal  WHERE id = ? ";
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
	public function getPower($id){
		#查询用户对此channel是否有权限		

		$channelPower = 0;
		$query = "SELECT owner,status FROM channal WHERE id=? and status>0 ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$channel = $stmt->fetch(PDO::FETCH_ASSOC);
		if($channel){
			if(!isset($_COOKIE["userid"])  ){
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
			if($channel["owner"]==$_COOKIE["userid"]){
				return 30;
			}
			else if($channel["status"]>=30){
				#全网公开的 可以提交pr
				$channelPower = 10;
			}
		}
		#查询共享权限，如果共享权限更大，覆盖上面的的
		$sharePower = share_get_res_power($_COOKIE["userid"],$id);
		if($sharePower>$channelPower){
			$channelPower=$sharePower;
		}
		return $channelPower;
	}

}

?>