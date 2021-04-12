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
			if($this->_redis->exists("article://".$id)===1){
				$output["id"]=$this->_redis->hGet("article://".$id,"id");
				$output["title"]=$this->_redis->hGet("article://".$id,"title");
				$output["subtitle"]=$this->_redis->hGet("article://".$id,"subtitle");
				$output["owner"]=$this->_redis->hGet("article://".$id,"owner");
				$output["summary"]=$this->_redis->hGet("article://".$id,"summary");
				$output["tag"]=$this->_redis->hGet("article://".$id,"tag");
				$output["status"]=$this->_redis->hGet("article://".$id,"status");
				$output["create_time"]=$this->_redis->hGet("article://".$id,"create_time");
				$output["modify_time"]=$this->_redis->hGet("article://".$id,"modify_time");
				return $output;
			}
		}
        $query = "SELECT id,title,owner,summary,tag,status,create_time,modify_time FROM article WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $output = $stmt->fetch(PDO::FETCH_ASSOC);
        if($output){
			if($this->_redis!==false){
				$this->_redis->hSet("article://".$id,"id",$output["id"]);
				$this->_redis->hSet("article://".$id,"title",$output["title"]);
				$this->_redis->hSet("article://".$id,"subtitle",$output["subtitle"]);
				$this->_redis->hSet("article://".$id,"summary",$output["summary"]);
				$this->_redis->hSet("article://".$id,"owner",$output["owner"]);
				$this->_redis->hSet("article://".$id,"tag",$output["tag"]);
				$this->_redis->hSet("article://".$id,"status",$output["status"]);
				$this->_redis->hSet("article://".$id,"create_time",$output["create_time"]);
				$this->_redis->hSet("article://".$id,"modify_time",$output["modify_time"]);
			}
            return $output;
        }
        else{
            return false;
        }
	}
    public function getContent($id){
		$output = array();
		if($this->_redis!==false){
			if($this->_redis->hExists("article://".$id,"content")===TRUE){
				$content=$this->_redis->hGet("article://".$id,"content");
				return $content;
			}
		}
        $query = "SELECT content FROM article WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $output = $stmt->fetch(PDO::FETCH_ASSOC);
        if($output){
			if($this->_redis!==false){
				$this->_redis->hSet("article://".$id,"content",$output["content"]);
			}
            return $output["content"];
        }
        else{
            return false;
        }
	}
	public function getPower($id,$collectionId=""){
		#查询用户对此是否有权限	
		if(isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
		}
		else{
			$userId=0;
		}
		if($this->_redis!==false){
			$power = $this->_redis->hGet("power://article/".$id,$userId);
			if($power!==FALSE){
				return $power;
			}
		}
		$iPower = 0;
		$query = "SELECT owner,status FROM article WHERE id=?  ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$channel = $stmt->fetch(PDO::FETCH_ASSOC);
		if($channel){
			if(!isset($_COOKIE["userid"])){
				#未登录用户
				if($channel["status"]==30){
					#全网公开有读取和建议权限
					return 10;
				}
				else{
					#其他状态没有任何权限
					return 0;
				}
			}
			else{
				if($channel["owner"]==$_COOKIE["userid"]){
					#自己的
					return 30;
				}
				else if($channel["status"]>=30){
					#全网公开的 可以提交pr
					$iPower = 10;
				}				
			}
		}
		#查询共享权限，如果共享权限更大，覆盖上面的的
		$sharePower = share_get_res_power($_COOKIE["userid"],$id);
		if($collectionId!=""){
			$sharePowerCollection = share_get_res_power($_COOKIE["userid"],$collectionId);
		}
		else{
			$sharePowerCollection =0;
		}
		if($sharePower>$iPower){
			$iPower=$sharePower;
		}
		if($sharePowerCollection>$iPower){
			$iPower=$sharePowerCollection;
		}
		$this->_redis->hSet("power://article/".$id,$_COOKIE["userid"],$iPower);
		return $iPower;
	}
}

?>