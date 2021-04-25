<?php
require_once "../path.php";
require_once "../share/function.php";
require_once "../db/table.php";

class Article extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USER_ARTICLE_, "article", "", "",$redis);
    }

    public function getInfo($id){
		$output = array();
		if($this->redis!==false){
			if($this->redis->exists("article://".$id)===1){
				$output["id"]=$this->redis->hGet("article://".$id,"id");
				$output["title"]=$this->redis->hGet("article://".$id,"title");
				$output["subtitle"]=$this->redis->hGet("article://".$id,"subtitle");
				$output["owner"]=$this->redis->hGet("article://".$id,"owner");
				$output["summary"]=$this->redis->hGet("article://".$id,"summary");
				$output["tag"]=$this->redis->hGet("article://".$id,"tag");
				$output["status"]=$this->redis->hGet("article://".$id,"status");
				$output["create_time"]=$this->redis->hGet("article://".$id,"create_time");
				$output["modify_time"]=$this->redis->hGet("article://".$id,"modify_time");
				return $output;
			}
		}
        $query = "SELECT id,title,owner,summary,tag,status,create_time,modify_time FROM article WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $output = $stmt->fetch(PDO::FETCH_ASSOC);
        if($output){
			if($this->redis!==false){
				$this->redis->hSet("article://".$id,"id",$output["id"]);
				$this->redis->hSet("article://".$id,"title",$output["title"]);
				$this->redis->hSet("article://".$id,"subtitle",$output["subtitle"]);
				$this->redis->hSet("article://".$id,"summary",$output["summary"]);
				$this->redis->hSet("article://".$id,"owner",$output["owner"]);
				$this->redis->hSet("article://".$id,"tag",$output["tag"]);
				$this->redis->hSet("article://".$id,"status",$output["status"]);
				$this->redis->hSet("article://".$id,"create_time",$output["create_time"]);
				$this->redis->hSet("article://".$id,"modify_time",$output["modify_time"]);
			}
            return $output;
        }
        else{
            return false;
        }
	}

    public function getContent($id){
		$output = array();
		if($this->redis!==false){
			if($this->redis->hExists("article://".$id,"content")===TRUE){
				$content=$this->redis->hGet("article://".$id,"content");
				return $content;
			}
		}
        $query = "SELECT content FROM article WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $output = $stmt->fetch(PDO::FETCH_ASSOC);
        if($output){
			if($this->redis!==false){
				$this->redis->hSet("article://".$id,"content",$output["content"]);
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
		if($this->redis!==false){
			$power = $this->redis->hGet("power://article/".$id,$userId);
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
		$this->redis->hSet("power://article/".$id,$_COOKIE["userid"],$iPower);
		return $iPower;
	}

}


class ArticleList extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USER_ARTICLE_, "article_list", "", "",$redis);
    }

	function upgrade($collectionId,$articleList=array()){
		if(count($articleList)==0){

		}
		# 更新 article_list 表
		$query = "DELETE FROM article_list WHERE collect_id = ? ";
		$stmt = $this->dbh->prepare($query);
		if($stmt){
			$stmt->execute($collectionId);
		}
        
		if(count($articleList)>0){
			/* 开始一个事务，关闭自动提交 */
			$PDO->beginTransaction();
			$query = "INSERT INTO article_list (collect_id, article_id,level,title) VALUES ( ?, ?, ? , ? )";
			$sth = $PDO->prepare($query);
			foreach ($articleList as $row) {
				$sth->execute(array($_POST["id"],$row->article,$row->level,$row->title));
				if($redis){
					#删除article权限缓存
					$redis->del("power://article/".$row->article);
				}
			}
			$PDO->commit();
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				/*  识别错误且回滚更改  */
				$PDO->rollBack();
				$error = PDO_ErrorInfo();
				$respond['status']=1;
				$respond['message']=$error[2];
			}
		}
	}
}
?>