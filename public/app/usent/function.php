<?php
require_once "../config.php";
require_once "../share/function.php";
require_once "../channal/function.php";
require_once "../db/table.php";

function update_historay($sent_id, $user_id, $text, $landmark)
{
    # 更新historay
    PDO_Connect("" . _FILE_DB_USER_SENTENCE_HISTORAY_);
    $query = "INSERT INTO sent_historay (sent_id,  user_id,  text,  date, landmark) VALUES (? , ? , ? , ? , ? )";
    $stmt = PDO_Execute($query,
        array($sent_id,
            $user_id,
            $text,
            mTime(),
            $landmark,
        ));
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        /*  识别错误  */
        $error = PDO_ErrorInfo();
        return $error[2];
    } else {
        #没错误
        return "";
    }
}

class SentPr{
	private $dbh_sent;
	private $redis;
	public function __construct($redis=false) {
        $this->dbh_sent = new PDO(_FILE_DB_SENTENCE_, "", "",array(PDO::ATTR_PERSISTENT=>true));
		$this->dbh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
		$this->redis=$redis;
	}
	public function getNewPrNumber($book,$para,$begin,$end,$channel){
		if ($this->dbh_sent) {
            $query = "SELECT count(*) as ct FROM sent_pr WHERE book = ? and paragraph= ? and begin=? and end=? and channel=? and status=1 ";
            $stmt = $this->dbh_sent->prepare($query);
            $stmt->execute(array($book,$para,$begin,$end,$channel));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				return $result["ct"];
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	public function getAllPrNumber($book,$para,$begin,$end,$channel){
		if ($this->dbh_sent) {
            $query = "SELECT count(*) as ct FROM sent_pr WHERE book = ? and paragraph= ? and begin=? and end=? and channel=?  ";
            $stmt = $this->dbh_sent->prepare($query);
            $stmt->execute(array($book,$para,$begin,$end,$channel));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				return $result["ct"];
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	public function getPrData($book,$para,$begin,$end,$channel){
		if ($this->dbh_sent) {
            $query = "SELECT id,book,paragraph,begin,end,channel,text,editor,modify_time FROM sent_pr WHERE book = ? and paragraph= ? and begin=? and end=? and channel=? and status=1 limit 0,100";
            $stmt = $this->dbh_sent->prepare($query);
            $stmt->execute(array($book,$para,$begin,$end,$channel));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if($result){
				return $result;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	public function getPrDataById($id){
		if ($this->dbh_sent) {
            $query = "SELECT id,book,paragraph,begin,end,channel,text,editor,modify_time FROM sent_pr WHERE id = ? ";
            $stmt = $this->dbh_sent->prepare($query);
            $stmt->execute(array($id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				return $result;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	public function setPrData($id,$text){
		if ($this->dbh_sent) {
            $query = "UPDATE sent_pr set text=? ,modify_time=?  WHERE id = ? and editor= ? ";
            $stmt = $this->dbh_sent->prepare($query);
            $stmt->execute(array($text,mTime(),$id,$_COOKIE["userid"]));
            
			if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
				/*  识别错误  */
				$error = $this->dbh_sent->errorInfo();
				//$respond['message'] = $error[2];
				return false;
			} else {
				#没错误 更新历史记录
				return true;
			}
		}
		else{
			return false;
		}
	}
}

class Sent_his
{
	private $dbh_his;
	private $errorMsg="";
}

class Sent_DB
{
    private $dbh_sent;
	private $dbh_his;
	private $errorMsg="";
    public function __construct($redis=false) {
        $this->dbh_sent = new PDO(_FILE_DB_SENTENCE_, "", "",array(PDO::ATTR_PERSISTENT=>true));
		$this->dbh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
		
		$this->dbh_his = new PDO(_FILE_DB_USER_SENTENCE_HISTORAY_, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh_his->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
	}
	public function getError(){
		return $errorMsg;
	}

	#获取单个句子数据
	public function getSent($book,$para,$begin,$end,$channel){
		$query = "SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND channal = ?  ";
		$stmt = $this->dbh_sent->prepare($query);
		if($stmt){
			$stmt->execute(array($book,$para,$begin,$end,$channel));
			$fetchDest = $stmt->fetch(PDO::FETCH_ASSOC);
			return $fetchDest;
		}
		else{
			return false;
		}
	}
	public function getSentDefaultByLan($book,$para,$begin,$end,$lang){
		$query = "SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND channal = ?  ";
		$stmt = $this->dbh_sent->prepare($query);
		if($stmt){
			$stmt->execute(array($book,$para,$begin,$end,$channel));
			$fetchDest = $stmt->fetch(PDO::FETCH_ASSOC);
			return $fetchDest;
		}
		else{
			return false;
		}
	}

	public function update($arrData){
		/* 修改现有数据 */
	
		if (count($arrData) > 0) {
			//add_edit_event(_SENT_EDIT_, "{$oldList[0]["book"]}-{$oldList[0]["paragraph"]}-{$oldList[0]["begin"]}-{$oldList[0]["end"]}@{$oldList[0]["channal"]}");
	
			$this->dbh_sent->beginTransaction();

			if(isset($arrData[0]["book"]) && isset($arrData[0]["paragraph"]) && isset($arrData[0]["begin"]) && isset($arrData[0]["end"]) && isset($arrData[0]["channal"])){
				$query = "UPDATE sentence SET text = ? , strlen = ? , editor=?, modify_time= ? , receive_time = ?  where  book = ? and paragraph=? and [begin]=? and [end]=? and channal=?  ";
				$sth = $this->dbh_sent->prepare($query);
				foreach ($arrData as $data) {
					if(!isset($data["modify_time"])){
						$data["modify_time"] = mTime();
					}
					$sth->execute(array($data["text"],mb_strlen($data["text"],"UTF-8"),$data["editor"],$data["modify_time"],mTime(),$data["book"],$data["paragraph"],$data["begin"],$data["end"],$data["channal"]));
				}
			}
			else if(isset($arrData[0]["id"])){
				$query = "UPDATE sentence SET text = ? , strlen = ? , editor=?, modify_time= ? ,receive_time = ?   where  id= ?  ";
				$sth = $this->dbh_sent->prepare($query);
				foreach ($arrData as $data) {
					if(!isset($data["modify_time"])){
						$data["modify_time"]=mTime();
					}
					$sth->execute(array($data["text"],mb_strlen($data["text"],"UTF-8"),$data["editor"],$data["modify_time"],mTime(),$data["id"]));
				}				
			}


			$this->dbh_sent->commit();
		
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				/*  识别错误且回滚更改  */
				$this->dbh_sent->rollBack();
				$error = $this->dbh_sent->errorInfo();
				$this->errorMsg = $error[2];
				return false;
			} else {
				#没错误 添加log 
				$this->errorMsg = "";
				return true;
			}
		}
		else{
			$this->errorMsg = "";
			return true;
		}
	}

	public function insert($arrData){
		# 插入新数据 
		
		if (count($arrData) > 0) {
			//add_edit_event(_SENT_NEW_, "{$newList[0]["book"]}-{$newList[0]["paragraph"]}-{$newList[0]["begin"]}-{$newList[0]["end"]}@{$newList[0]["channal"]}");
			$this->dbh_sent->beginTransaction();
			$query = "INSERT INTO sentence (id,
											parent,
											book,
											paragraph,
											begin,
											end,
											channal,
											tag,
											author,
											editor,
											text,
											language,
											ver,
											status,
											strlen,
											modify_time,
											receive_time,
											create_time
											)
								VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
			$sth = $this->dbh_sent->prepare($query);

			#查询channel语言
			$channel_info = new Channal();
	
			foreach ($arrData as $data) {
				if($data["id"]==""){
					$data["id"] = UUID::v4();
				}
				if(!isset($data["create_time"])){
					$data["create_time"]=mTime();
				}
				if(!isset($data["modify_time"])){
					$data["modify_time"]=mTime();
				}
				$data["receive_time"]=mTime();

				$queryChannel = $channel_info->getChannal($data["channal"]);
				if ($queryChannel == false) {
					$lang = $data["language"];
					$status = 10;
				} else {
					$lang = $queryChannel["lang"];
					$status = $queryChannel["status"];
				}
				$sth->execute(array($data["id"],
					isset($data["parent"]) ? $data["parent"] : "",
					$data["book"],
					$data["paragraph"],
					$data["begin"],
					$data["end"],
					$data["channal"],
					isset($data["tag"]) ? $data["tag"] : "",
					$data["author"],
					$data["editor"],
					$data["text"],
					$lang,
					1,
					$status,
					mb_strlen($data["text"], "UTF-8"),
					$data["modify_time"],
					$data["receive_time"],
					$data["create_time"]
				));
			}
			$this->dbh_sent->commit();
	
			if (!$sth || ($sth && $sth->errorCode() != 0)) {
				/*  识别错误且回滚更改  */
				$this->dbh_sent->rollBack();
				$error = $this->dbh_sent->errorInfo();
				$this->errorMsg = $error[2];
				return false;
			} else {
				$this->errorMsg = "";
				return true;
			}
		}
		else{
			$this->errorMsg = "";
			return true;
		}
	}

	public function send_pr($arrData){
		if (count($arrData) ==0) {
			$this->errorMsg = "";
			return true;
		}
		$this->dbh_sent->beginTransaction();
		$query = "INSERT INTO sent_pr (id,
							book,
							paragraph,
							begin,
							end,
							channel,
							tag,
							author,
							editor,
							text,
							language,
							status,
							strlen,
							modify_time,
							receive_time,
							create_time
							)
							VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$stmt = $this->dbh_sent->prepare($query);

		foreach ($arrData as $data) {
			# 初始状态 1 未处理
			$stmt->execute(array(
							$data["book"],
							$data["para"],
							$data["begin"],
							$data["end"],
							$data["channal"],
							"",
							"[]",
							$data["editor"],
							$data["text"],
							$data["language"],
							1,
							mb_strlen($data["text"], "UTF-8"),
							mTime(),
							mTime(),
							mTime(),
							));
		}
		$this->dbh_sent->commit();
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			#  识别错误  
			$this->dbh_sent->rollBack();
			$error = $this->dbh_sent->errorInfo();
			$this->errorMsg = $error[2];
			return false;
		} else {
			# 没错误
			$this->errorMsg = "";
			return true;
		}
	}

	public function historay($arrData){
		if (count($arrData) ==0) {
			$this->errorMsg = "";
			return true;
		}
		$this->dbh_his->beginTransaction();
		# 更新historay
		$query = "INSERT INTO sent_historay (sent_id,  user_id,  text,  date, landmark) VALUES (? , ? , ? , ? , ? )";
		$stmt = $this->dbh_his->prepare($query);

		foreach ($arrData as $data) {
			$stmt->execute(array($data["id"],
							$data["editor"],
							$data["text"],
							mTime(),
							$data["landmark"]));
		}
		$this->dbh_his->commit();
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			/*  识别错误  */
			$this->dbh_his->rollBack();
			$error = $this->dbh_his->errorInfo();
			$this->errorMsg = $error[2];
			return false;
		} else {
			#没错误
			$this->errorMsg = "";
			return true;
		}
	}
}
