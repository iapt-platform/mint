<?php

require_once __DIR__.'/../config.php';
function ucenter_get($userid, $fields = "username")
{
    //打开数据库
    $dns = _FILE_DB_USERINFO_;
    $dbh = new PDO($dns, _DB_USERNAME_,_DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT username from "._TABLE_USER_INFO_." where id= ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($userid));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null;
    if (count($fUser) > 0) {
        return ($fUser[0][$fields]);
    } else {
        return ("");
    }

}

function ucenter_getA($userid, $fields = "nickname")
{
    if(empty($userid)){
        return "";
    }
    //打开数据库
    $dns = _FILE_DB_USERINFO_;
    $dbh = new PDO($dns, _DB_USERNAME_,_DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT username,nickname FROM "._TABLE_USER_INFO_." WHERE userid= ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($userid));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh = null;
    if (count($fUser) > 0) {
        return ($fUser[0][$fields]);
    } else {
        return ("");
    }

}

class UserInfo
{
    private $dbh;
    private $buffer;
    private $log;

    public function __construct()
    {
        $dns = _FILE_DB_USERINFO_;
        $this->dbh = new PDO($dns,  _DB_USERNAME_,_DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $this->buffer = array();
        $this->log = "";
    }

    public function getName($id)
    {
        if (empty($id)) {
            return array("nickname" => "", "username" => "");
        }
        if (isset($this->buffer[$id])) {
            return $this->buffer[$id];
        }
        if ($this->dbh) {
            if(is_integer($id)){
                $query = "SELECT nickname,username FROM "._TABLE_USER_INFO_." WHERE id = ? ";
                $stmt = $this->dbh->prepare($query);
                $stmt->execute(array($id));
            }else{
                $query = "SELECT nickname,username FROM "._TABLE_USER_INFO_." WHERE  userid= ? ";
                $stmt = $this->dbh->prepare($query);
                $stmt->execute(array($id));
            }

            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($user) > 0) {
                $this->buffer[$id] = array("nickname" => $user[0]["nickname"], "username" => $user[0]["username"]);
                return $this->buffer[$id];
            } else {
                $this->buffer[$id] = array("nickname" => "", "username" => "");
                return $this->buffer[$id];
            }
        } else {
            $this->buffer[$id] = array("nickname" => "", "username" => "");
            return $this->buffer[$id];
        }
	}
	public function getId($uuid)
    {
        if (empty($uuid)) {
            return 0;
        }
        if ($this->dbh) {
            $query = "SELECT id FROM "._TABLE_USER_INFO_." WHERE  userid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($uuid));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user["id"];
            }else{
                return 0;
            }
        } else {
            return 0;
        }
	}
	public function getUserByName($name)
    {
        if (empty($name)) {
            return false;
        }
        if ($this->dbh) {
            $query = "SELECT id,userid,nickname,username FROM "._TABLE_USER_INFO_." WHERE  username= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($name));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }else{
                return false;
            }
        } else {
            return false;
        }
	}
    public function getUserList($key){
        if ($this->dbh) {
            $query = "SELECT id,userid,nickname,username FROM "._TABLE_USER_INFO_." WHERE  username like ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($key."%"));
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }
        } else {
            return false;
        }
    }
	public function check_password($userid,$password){
		if ($this->dbh) {
            $query = "SELECT username FROM "._TABLE_USER_INFO_." WHERE  userid= ? and password = ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($userid,md5($password)));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
	}

    public function signIn($username,$password){
		if ($this->dbh) {
            $query = "SELECT userid,id FROM "._TABLE_USER_INFO_." WHERE  (username= ? and password = ?) or (email= ? and password = ?) ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($username,md5($password),$username,md5($password)));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            } else {
                $query = "SELECT userid,id,password FROM "._TABLE_USER_INFO_." WHERE  username= ?";
                $stmt = $this->dbh->prepare($query);
                $stmt->execute(array($username));
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if($user){
                    $this->log .=  "username:{$username},pwd:{$user['password']}-".md5($password)."\n";
                }else{
                    $this->log .= "no user:{$username}\n";
                }
                return false;
            }
        } else {
            return false;
        }
	}

    public function getLog(){
        return $this->log;
    }
}
