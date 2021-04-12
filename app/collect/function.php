<?php
require_once '../path.php';
require_once "../share/function.php";


class CollectInfo
{
    private $dbh;
    private $buffer;
	private $_redis;

    public function __construct($redis=false) {
        $dns = ""._FILE_DB_USER_ARTICLE_;
        $this->dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
		$this->_redis=$redis;
        $buffer = array();
    }

    public function get($id){
        if(empty($id)){
            return array("title"=>"","id"=>"");
        }
        if(isset($buffer[$id])){
            return $buffer[$id];
        }
        if($this->dbh){
            $query = "SELECT id,title,owner,status,lang FROM collect WHERE id= ?";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
			$collect = $stmt->fetch(PDO::FETCH_ASSOC);
            if($collect){
                $buffer[$id] = $collect;
                return $buffer[$id];
            }
            else{
                $buffer[$id] =false;
                return $buffer[$id];
            }            
        }
        else{
            $buffer[$id] = false;
            return $buffer[$id];
        }
    }

	public function getArticleList($id){
        if(empty($id)){
            return array();
        }
        if($this->dbh){
            $query = "SELECT article_id FROM article_list WHERE collect_id= ? limit 0,1000";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
			$article_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$output=array();
			foreach ($article_list as $key => $value) {
				# code...
				$output[]=$value["article_id"];
			}
			return $output;
        }
        else{
            return array();
        }
    }

	public function getPower($id){
		#查询用户对此是否有权限	
		if(isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
		}
		else{
			$userId='0';
		}
		if($this->_redis!==false){
			$power = $this->_redis->hGet("power://collection/".$id,$userId);
			if($power!==FALSE){
				return $power;
			}
		}
		$iPower = 0;
		$query = "SELECT owner,status FROM collect WHERE id=?  ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if($result){
			if(!isset($_COOKIE["userid"])){
				#未登录用户
				if($result["status"]==30){
					#全网公开有读取和建议权限
					return 10;
				}
				else{
					#其他状态没有任何权限
					return 0;
				}
			}
			else{
				if($result["owner"]==$_COOKIE["userid"]){
					#自己的
					return 30;
				}
				else if($result["status"]>=30){
					#全网公开的 可以提交pr
					$iPower = 10;
				}			
			}
			#查询共享权限，如果共享权限更大，覆盖上面的的
			$sharePower = share_get_res_power($userId,$id);
			if($sharePower>$iPower){
				$iPower=$sharePower;
			}
			$this->_redis->hSet("power://collection/".$id,$userId,$iPower);			
		}
		return $iPower;
	}
}
?>