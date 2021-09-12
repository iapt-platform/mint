<?php
require_once "../path.php";
require_once "../db/table.php";
require_once "../public/function.php";
// Require Composer's autoloader.
require_once '../../vendor/autoload.php';
require_once '../config.php';

// Using Medoo namespace.
use Medoo\Medoo;

// Require Composer's autoloader.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/*
CREATE TABLE likes (
    id            INTEGER      PRIMARY KEY AUTOINCREMENT,
    like_type     VARCHAR (16) NOT NULL,
    resource_type VARCHAR (32) NOT NULL,
    resource_id   CHAR (36)    NOT NULL,
    user_id       INTEGER      NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP     NOT NULL //只做初始化,更新时不自动更新
);
*/
class User extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USERINFO_, "user", "", "",$redis);
    }

	public function  index(){
		$where["like_type"] = "like";
		$where["resource_type"] = $_GET["type"];
		$where["resource_id"] = explode($_GET["id"],",");
		echo json_encode($this->_index(["resource_id","user_id"],$where), JSON_UNESCAPED_UNICODE);
	}
	
	public function  list(){
		if(!isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
		}

		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		foreach ($data as $key => $value) {
			# code...
			$data[$key]['like']=$this->medoo->count($this->table,[
											'like_type'=>$value['like_type'],
											'resource_type'=>$value['resource_type'],
											'resource_id'=>$value['resource_id'],
											  ]);
		}
		if(isset($_COOKIE["userid"])){
			$userId = $_COOKIE["userid"];
			foreach ($data as $key => $value) {
				# code...
				$data[$key]['me']=$this->medoo->count($this->table,[
												'like_type'=>$value['like_type'],
												'resource_type'=>$value['resource_type'],
												'resource_id'=>$value['resource_id'],
												'user_id'=>$userId,
												]);
			}
		}

		$this->result["ok"]=true;
		$this->result["message"]="";
		$this->result["data"]=$data;
		echo json_encode($this->result, JSON_UNESCAPED_UNICODE);

	}


	public function  create(){
		if(!isset($_COOKIE["userid"])){
			return;
		}
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		$data["user_id"] = $_COOKIE["userid"];
		$isExist = $this->medoo->has("likes",$data);
		if(!$isExist){
			echo json_encode($this->_create($data,["like_type","resource_type","resource_id","user_id"]), JSON_UNESCAPED_UNICODE);
		}
		else{
			$this->result["ok"]=false;
			$this->result["message"]="is exist";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
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

	#发送密码重置邮件
	public function reset_password_send_email(){
		$email = $_GET["email"];
		$isExist = $this->medoo->has($this->table,["email"=>$email]);
		if($isExist){
			$resetToken = UUID::v4();
			$ok = $this->_update(["reset_password_token"=>$resetToken],["reset_password_token"],["email"=>$email]);
			if($ok){
				#send email
				$resetLink="https://www.wikipali.org/ucenter/reset.php?token=".$resetToken;
				$resetString="https://www.wikipali.org/ucenter/reset.php?token=".$resetToken;
		
					// 打开文件并读取数据
				$irow=0;
				$strSubject = "";
				$strBody = "";
				if(($fp=fopen("../ucenter/reset_pwd_letter.html", "r"))!==FALSE){
					while(($data=fgets($fp))!==FALSE){
						$irow++;
						if($irow==1){
							$strSubject = $data; 
						}else{
							$strBody .= $data; 
						}
					}
					fclose($fp);
				}
				else{
					$this->result["ok"] = false;
					$this->result["message"] = "can not load email file.";
					echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
					return;
				}
		
				$strBody = str_replace("%resetLink%",$resetLink,$strBody);
				$strBody = str_replace("%resetString%",$resetString,$strBody);
		
				//TODO sendmail
		
				//Create an instance; passing `true` enables exceptions
				$mail = new PHPMailer(true);
		
				try {
					//Server settings
					$mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
					$mail->isSMTP();                                            //Send using SMTP
					$mail->Host       = Email["Host"];                     //Set the SMTP server to send through
					$mail->SMTPAuth   = Email["SMTPAuth"];                                   //Enable SMTP authentication
					$mail->Username   = Email["Username"];                     //SMTP username
					$mail->Password   = Email["Password"];                               //SMTP password
					$mail->SMTPSecure = Email["SMTPSecure"];            //Enable implicit TLS encryption
					$mail->Port       = Email["Port"];                                    //TCP port to connect to 465; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		
					//Recipients
					$mail->setFrom(Email["From"], Email["Sender"]);
					$mail->addAddress($email);     //Add a recipient Name is optional
		
					//Content
					$mail->isHTML(true);                                  //Set email format to HTML
					$mail->Subject = $strSubject;
					$mail->Body    = $strBody;
					$mail->AltBody = $strBody;
		
					$mail->send();
					#邮件发送成功，修改数据库
					$this->_update(["reset_password_sent_at"=>Medoo::raw('datetime(<now>)')],["reset_password_sent_at"],["email"=>$email]);
					//邮件地址脱敏
					$show_email = mb_substr($email,0,2,"UTF-8") . "****" . strstr($email,"@");
					$this->result["message"] = 'Message has been sent to your email : ' . $show_email;
					echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
					return;

				} catch (Exception $e) {
					$this->result["ok"] = false;
					$this->result["message"] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
					echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
					return;
				}
			}else{
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				return;
			}
		}else{
			$this->result["ok"]=false;
			$this->result["message"]="invalid email";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}

	#重置密码
	public function reset_password($username,$password,$token){
		$isExist = $this->medoo->has($this->table,["user_name"=>$username,"token"=>$token]);
		if($isExist){
			#reset password
			$ok = $this->_update(["password"=>$password],"password",["user_name"=>$username]);
			if($ok){
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				
			}else{
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
			}
		}else{
			$this->result["ok"]=false;
			$this->result["message"]="invalid token";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}
}

?>