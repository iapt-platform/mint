<?php
require_once '../config.php';
require_once "../share/function.php";
require_once "../db/table.php";
require_once "../article/function.php";

class CollectInfo
{
    private $dbh;
    private $buffer;
	private $_redis;
	private $errorMsg;
    public function __construct($redis=false) {
        $dns = _FILE_DB_USER_ARTICLE_;
        $this->dbh = new PDO($dns, _DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
		$this->_redis=$redis;
        $buffer = array();
    }

	public function getError(){
		return $this->errorMsg;
	}

    public function get($id){
        if(empty($id)){
            return array("title"=>"","id"=>"");
        }
        if(isset($buffer[$id])){
            return $buffer[$id];
        }
        if($this->dbh){
            $query = "SELECT uid as id,title,owner,status,lang,article_list FROM "._TABLE_COLLECTION_." WHERE uid= ?";
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
            $query = "SELECT article_id FROM "._TABLE_ARTICLE_COLLECTION_." WHERE collect_id= ? limit 1000";
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
		if(isset($_COOKIE["user_uid"])){
			$userId = $_COOKIE["user_uid"];
		}
		else{
			$userId='0';
		}
		/*
		if($this->_redis!==false){
			$power = $this->_redis->hGet("power://collection/".$id,$userId);
			if($power!==FALSE){
				return $power;
			}
		}
		*/
		$iPower = 0;
		$query = "SELECT owner,status FROM "._TABLE_COLLECTION_." WHERE uid=?  ";
		$stmt = $this->dbh->prepare($query);
		$stmt->execute(array($id));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if($result){
			if(!isset($_COOKIE["user_uid"])){
				#未登录用户
				if($result["status"]==30){
					#全网公开有读取和建议权限
					$iPower =  10;
				}
				else{
					#其他状态没有任何权限
					$iPower =  0;
				}
			}
			else{
				if($result["owner"]==$_COOKIE["user_uid"]){
					#自己的
					$iPower =  30;
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
			if($this->_redis!==false){
				$this->_redis->hSet("power://collection/".$id,$userId,$iPower);	
			}		
		}
		return $iPower;
	}

	public function update($arrData){
		/* 修改现有数据 */
	
		if (count($arrData) > 0) {
			$this->dbh->beginTransaction();
			$query="UPDATE "._TABLE_COLLECTION_." SET title = ? , 
									   subtitle = ? , 
									   summary = ?, 
									   article_list = ?  ,  
									   status = ? , 
									   lang = ? , 
									   updated_at= now()  , 
									   modify_time= ?   
									   where  uid = ?  ";
			$sth = $this->dbh->prepare($query);
			foreach ($arrData as $data) {
				$sth->execute(array(
									$data["title"] , 
									$data["subtitle"] ,
									$data["summary"], 
									$data["article_list"] , 
									$data["status"] , 
									$data["lang"] ,  
									$data["modify_time"] , 
									$data["id"])
								);
			}
			$this->dbh->commit();
		
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				/*  识别错误且回滚更改  */
				$this->dbh->rollBack();
				$error = $this->dbh->errorInfo();
				$this->errorMsg = $error[2];
				return false;
			} else {
				#没错误 添加log 
				$this->errorMsg = "";
				//更新列表
				$ArticleList = new ArticleList($this->_redis);
				foreach ($arrData as $data) {
					$arrList = json_decode($data["article_list"],true);
					$ArticleList->upgrade($data["id"],$arrList);
				}
				return true;
			}
		}
		else{
			$this->errorMsg = "";
			return true;
		}
	}

	public function insert($arrData){
		/* 插入新数据 */
		$respond=array("status"=>0,"message"=>"");
		if (count($arrData) > 0) {
			$this->dbh->beginTransaction();
			$query="INSERT INTO "._TABLE_COLLECTION_." ( uid,  title  , subtitle  , summary , article_list , owner, lang  , status  , create_time , modify_time    )  VALUES  (  ? , ? , ?  , ? , ? , ? , ? , ? , ? , ? ) ";
			$sth = $this->dbh->prepare($query);
			$newDataList=array();
			foreach ($arrData as $data) {
				$newData = array();
				if(isset($data["title"]) && !empty($data["title"])){
					$newData["title"]=$data["title"];
					if(isset($data["id"])){
						$newData["id"]=$data["id"];
					}
					else{
						$newData["id"]=UUID::v4();
					}
					if(isset($data["subtitle"])){
						$newData["subtitle"]=$data["subtitle"];
					}
					else{
						$newData["subtitle"]="";
					}
					if(isset($data["summary"])){
						$newData["summary"]=$data["summary"];
					}
					else{
						$newData["summary"]="";
					}	
					if(isset($data["article_list"])){
						$newData["article_list"]=$data["article_list"];
					}
					else{
						$newData["article_list"]="";
					}	
					if(isset($data["owner"])){
						$newData["owner"]=$data["owner"];
					}
					else{
						$newData["owner"]=$_COOKIE["user_uid"];
					}	
					if(isset($data["lang"])){
						$newData["lang"]=$data["lang"];
					}
					else{
						$newData["lang"]="";
					}
					if(isset($data["status"])){
						$newData["status"]=$data["status"];
					}
					else{
						$newData["status"]="1";
					}
					if(isset($data["create_time"])){
						$newData["create_time"]=$data["create_time"];
					}
					else{
						$newData["create_time"]=mTime();
					}
					if(isset($data["modify_time"])){
						$newData["modify_time"]=$data["modify_time"];
					}
					else{
						$newData["modify_time"]=mTime();
					}
					$newDataList[]=$newData;
					$sth->execute(
							array(
								$newData["id"] , 
								$newData["title"] , 
								$newData["subtitle"] ,
								$newData["summary"], 
								$newData["article_list"] , 
								$newData["owner"] , 
								$newData["lang"] , 
								$newData["status"] , 
								$newData["create_time"] ,  
								$newData["modify_time"] 
								)
							);				
				}
				else{
					$this->errorMsg="标题不能为空";
				}
			}
			$this->dbh->commit();
	
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				/*  识别错误且回滚更改  */
				$this->dbh->rollBack();
				$error = $this->dbh->errorInfo();
				$this->errorMsg = $error[2];
				return false;
			} else {
				$this->errorMsg = "";
				//更新列表
				$ArticleList = new ArticleList($this->_redis);
				foreach ($newDataList as $data) {
					$arrList = json_decode($data["article_list"],true);
					$ArticleList->upgrade($data["id"],$arrList);
				}
				return true;
			}
		}
		else{
			$this->errorMsg = "";
			return true;
		}
	}
}
?>