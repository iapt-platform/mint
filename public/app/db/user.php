<?php
require_once "../config.php";
require_once "../db/table.php";
require_once "../db/channel.php";
require_once "../public/function.php";
// Require Composer's autoloader.
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Using Medoo namespace.
use Medoo\Medoo;

// Require Composer's autoloader.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/*
CREATE TABLE users (
    id            INTEGER      PRIMARY KEY AUTOINCREMENT,
    like_type     VARCHAR (16) NOT NULL,
    resource_type VARCHAR (32) NOT NULL,
    resource_id   CHAR (36)    NOT NULL,
    user_id       INTEGER      NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP     NOT NULL //只做初始化,更新时不自动更新
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL //自动更新
);
*/
class PCD_User extends Table
{
    function __construct($redis=false) {
		parent::__construct(_FILE_DB_USERINFO_, _TABLE_USER_INFO_, _DB_USERNAME_,_DB_PASSWORD_,$redis);
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
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//验证邀请码
		if(isset($data["invite"])){
			if ($this->redis == false) {
				$this->result["ok"]=false;
				$this->result["message"]="no_redis_connect";
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				return;
			}
			$redisKey = "invitecode://".$data["invite"];
			$code = $this->redis->exists($redisKey);
			if(!$code){
				$this->result["ok"]=false;
				$this->result["message"]="invite_code_invalid";
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				return;
			}
			$data["email"] = $this->redis->get($redisKey);
		}else{
			$this->result["ok"]=false;
			$this->result["message"]="no_invite_code";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
			return;
		}
		//验证用户名有效性
		if(!$this->isValidUsername($data["username"])){
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
			return;
		}
		//验证昵称有效性
		if(!$this->isValidNickName($data["nickname"])){
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
			return;
		}
		$isExist = $this->medoo->has($this->table,["username"=>$data["username"]]);
		if(!$isExist){
			if(!$this->isValidEmail($data["email"])){
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				return;
			}
			$isExist = $this->medoo->has($this->table,["email"=>$data["email"]]);
			if(!$isExist){
				if(!$this->isValidPassword($data["password"])){
					echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
					return;
				}
				$data["userid"] = UUID::v4();
				$data["password"] = md5($data["password"]);
				$data["create_time"] = mTime();
				$data["modify_time"] = mTime();
				$data["setting"] = "{}";
				$result = $this->_create($data,["userid","username","email","password","nickname","setting","create_time","modify_time"]);
				if($result["ok"]){
                    $newUserId = $this->medoo->get(
                        $this->table,
                        'id',
                        ["userid"=>$data['userid']]
                    );
					$channel = new Channel($this->redis);
					$newChannel1 = $channel->create([
                                                    "owner_uid"=>$data["userid"],
                                                    "editor_id"=>$newUserId,
													"lang"=>$data["lang"],
													"name"=>$data["username"],
													"lang"=>$data["lang"],
													"status"=>30,
													"summary"=>""
													]);
					$newChannel2 = $channel->create([
                                                    "owner_uid"=>$data["userid"],
                                                    "editor_id"=>$newUserId,
													"lang"=>$data["lang"],
													"name"=>"draft",
													"lang"=>$data["lang"],
													"status"=>10,
													"summary"=>""
													]);
					echo json_encode($newChannel1, JSON_UNESCAPED_UNICODE);
					//删除
					$this->redis->del($redisKey);
				}else{
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
				}

			}else{
				$this->result["ok"]=false;
				$this->result["message"]="email_is_exist";
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
			}
		}
		else{
			$this->result["ok"]=false;
			$this->result["message"]="account_is_exist";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}



	#发送密码重置邮件
	public function reset_password_send_email(){
		$email = $_GET["email"];
		$isExist = $this->medoo->has($this->table,["email"=>$email]);
		if($isExist){
			$resetToken = UUID::v4();
            $query = "UPDATE   ".$this->table." SET reset_password_token = ? WHERE email = ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($resetToken,$email));
            $ok = true;
			//$ok = $this->_update(["reset_password_token"=>$resetToken],["reset_password_token"],["email"=>$email]);
			if($ok){
				#send email
				$resetLink="https://".$_SERVER['SERVER_NAME']."/app/ucenter/reset.php?token=".$resetToken;
				$resetString="https://".$_SERVER['SERVER_NAME']."/app/ucenter/reset.php";

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

				$strBody = str_replace("%ResetLink%",$resetLink,$strBody);
				$strBody = str_replace("%ResetString%",$resetString,$strBody);

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
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
					$mail->Port       = Email["Port"];                                    //TCP port to connect to 465; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
					$mail->CharSet =  'UTF-8';
					$mail->Encoding = 'base64';
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
			$this->result["message"]="::invalid_email";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}

	#重置密码
	public function reset_password(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		$isExist = $this->medoo->has($this->table,["username"=>$data["username"],"reset_password_token"=>$data["reset_password_token"]]);
		if($isExist){
			#reset password
			if(!$this->isValidPassword($data["password"])){
				echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
				return;
			}
			$ok = $this->_update(["password"=>md5($data["password"])],["password"],["username"=>$data["username"]]);
			if($ok){
				#成功后删除reset_password_token
				$ok = $this->_update(["reset_password_token"=>null,
									  "reset_password_sent_at"=>null],
									  null,
									  ["username"=>$data["username"]]);
			}
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}else{
			$this->result["ok"]=false;
			$this->result["message"]="::invalid_token";
			echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
		}
	}

    public function signin(){
        $isExist = $this->medoo->has($this->table,["username"=>$_REQUEST["username"],'password'=>md5($_REQUEST["password"])]);
        if(!$isExist){
            $isExist = $this->medoo->has($this->table,["email"=>$_REQUEST["username"],'password'=>md5($_REQUEST["password"])]);
            if(!$isExist){
                $this->result["ok"]=false;
                $this->result["message"]="wrong username or password";
                echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
            }else{
                $uid = $this->medoo->get( $this->table, 'userid', ["email"=>$_REQUEST["username"]] );
            }
        }else{
            $uid = $this->medoo->get( $this->table, 'userid', ["username"=>$_REQUEST["username"]] );
        }
        //JWT

        $key = APP_KEY;
        $payload = [
            'nbf' => time(),
            'exp' => time()+60*60*24*365,
            'uid' => $uid
        ];
        $jwt = JWT::encode($payload,$key,'HS512');
        //End of JWT
        // set cookie
        if(empty($_SERVER["HTTPS"])){
            //本地开发
            setcookie("user_uid", $uid,["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
//            setcookie("user_id", $Fetch[0]["id"], ["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
            setcookie("token", $jwt, ["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
        }else{
            //服务器运行
            setcookie("user_uid", $uid, ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
//            setcookie("user_id", $Fetch[0]["id"], ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
            setcookie("token", $jwt, ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
        }
        $this->result["ok"]=true;
        $this->result["data"]=['token'=>$jwt];
        echo json_encode($this->result, JSON_UNESCAPED_UNICODE);
    }

	private function isValidPassword($password){
		if(mb_strlen($password,"UTF-8")<6){
			$this->result["ok"]=false;
			$this->result["message"]="::password_too_short";
			return false;
		}
		if(mb_strlen($password,"UTF-8")>32){
			$this->result["ok"]=false;
			$this->result["message"]="::password_too_long";
			return false;
		}
		if(strpos($password," ")!==false){
			$this->result["ok"]=false;
			$this->result["message"]="::password_invaild_symbol";
			return false;
		}
		return true;
	}
	private function isValidUsername($username){
		if(mb_strlen($username,"UTF-8")>32){
			$this->result["ok"]=false;
			$this->result["message"]="::username_too_long";
			return false;
		}
		if(mb_strlen($username,"UTF-8")<4){
			$this->result["ok"]=false;
			$this->result["message"]="::username_too_short";
			return false;
		}
		if(preg_match("/@|\s|\/|[A-Z]/",$username)!==0){
			$this->result["ok"]=false;
			$this->result["message"]="::username_invaild_symbol";
			return false;
		}
		return true;
	}
	private function isValidNickName($nickname){
		if(mb_strlen($nickname,"UTF-8")>32){
			$this->result["ok"]=false;
			$this->result["message"]="::nickname_too_long";
			return false;
		}
		if(mb_strlen($nickname,"UTF-8")<1){
			$this->result["ok"]=false;
			$this->result["message"]="::nickname_too_short";
			return false;
		}
		return true;
	}
	private function isValidEmail($email){
		$isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
		if($isValid===false){
			$this->result["ok"]=false;
			$this->result["message"]="::invaild_email";
		}
		return $isValid;
	}

}

?>
