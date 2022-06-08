<?php

require_once __DIR__.'/../config.php';
function ucenter_get($userid, $fields = "username")
{
    //打开数据库
    $dns = "" . _FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "select username from user where id= ? ";
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
    //打开数据库
    $dns = _FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT username,nickname FROM user WHERE userid= ? ";
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
    public function __construct()
    {
        $dns = _FILE_DB_USERINFO_;
        $this->dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $buffer = array();
    }

    public function getName($id)
    {
        if (empty($id)) {
            return array("nickname" => "", "username" => "");
        }
        if (isset($buffer[$id])) {
            return $buffer[$id];
        }
        if ($this->dbh) {
            $query = "SELECT nickname,username FROM user WHERE id = ? or userid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id,$id));
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($user) > 0) {
                $buffer[$id] = array("nickname" => $user[0]["nickname"], "username" => $user[0]["username"]);
                return $buffer[$id];
            } else {
                $buffer[$id] = array("nickname" => "", "username" => "");
                return $buffer[$id];
            }
        } else {
            $buffer[$id] = array("nickname" => "", "username" => "");
            return $buffer[$id];
        }
	}
	public function getId($uuid)
    {
        if (empty($uuid)) {
            return 0;
        }
        if ($this->dbh) {
            $query = "SELECT id FROM user WHERE  userid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($uuid));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user["id"];
            }
        } else {
            return 0;
        }
	}
	public function check_password($userid,$password){
		if ($this->dbh) {
            $query = "SELECT username FROM user WHERE  userid= ? and password = ? ";
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
}
