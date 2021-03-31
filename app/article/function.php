<?php
require_once "../path.php";
require_once "../share/function.php";

class Article
{
    private $dbh;
	private $_redis;
    public function __construct($redis=false) {
        $this->dbh = new PDO(_FILE_DB_USER_ARTICLE_, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->_redis=$redis;
    }

    public function getInfo($id){
		$output = array();
		if($this->_redis!==false){
			if($this->_redis->exists("article://info/".$id)===1){
				$output["title"]=$this->_redis->hGet("article://info/".$id,"title");
				$output["owner"]=$this->_redis->hGet("article://info/".$id,"owner");
				$output["summary"]=$this->_redis->hGet("article://info/".$id,"summary");
				$output["status"]=$this->_redis->hGet("article://info/".$id,"status");
				$output["create_time"]=$this->_redis->hGet("article://info/".$id,"create_time");
				$output["modify_time"]=$this->_redis->hGet("article://info/".$id,"modify_time");
				return $output;
			}
		}
        $query = "SELECT title,owner,summary,status,create_time,modify_time FROM article WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $output = $stmt->fetch(PDO::FETCH_ASSOC);
        if($output){
			if($this->_redis!==false){
				$this->_redis->hSet("article://info/".$id,"title",$output["title"]);
				$this->_redis->hSet("article://info/".$id,"owner",$output["owner"]);
				$this->_redis->hSet("article://info/".$id,"summary",$output["summary"]);
				$this->_redis->hSet("article://info/".$id,"status",$output["status"]);
				$this->_redis->hSet("article://info/".$id,"create_time",$output["create_time"]);
				$this->_redis->hSet("article://info/".$id,"modify_time",$output["modify_time"]);
			}
            return $output;
        }
        else{
            return false;
        }
	}
	public function getTitle($id){

		$query = "SELECT title FROM article  WHERE id = ? ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$channal = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($channel) {
			return $channel["name"];
		} else {
			return "";
		}
	}
	public function getPower($id){
		#查询用户对此channel是否有权限		

		$iPower = 0;
		$query = "SELECT owner,status FROM article WHERE id=? and status>0 ";
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
				$iPower = 10;
			}
		}
		#查询共享权限，如果共享权限更大，覆盖上面的的
		$sharePower = share_get_res_power($_COOKIE["userid"],$id);
		if($sharePower>$iPower){
			$iPower=$sharePower;
		}
		return $iPower;
	}

}

?>