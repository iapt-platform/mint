<?php
require_once '../path.php';
function ucenter_get($userid,$fields="username"){
    //打开数据库
    $dns = "sqlite:"._FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    $query = "select username from user where id= ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($userid));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh=null;
    if(count($fUser)>0){
        return($fUser[0][$fields]);
    }
    else{
        return("");
    }

}

function ucenter_getA($userid,$fields="nickname"){
    //打开数据库
    $dns = "sqlite:"._FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    $query = "select username,nickname from user where userid= ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($userid));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh=null;
    if(count($fUser)>0){
        return($fUser[0][$fields]);
    }
    else{
        return("");
    }

}

class UserInfo
{
    public $dbh;
    public function __construct() {
        $dns = "sqlite:"._FILE_DB_USERINFO_;
        $this->dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    }

    public function getName($id){
        if($this->dbh){
            $query = "SELECT nickname FROM user WHERE userid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($user)>0){
                return $user[0]["nickname"];
            }
            else{
                return "";
            }            
        }
        else{
            return "";
        }
    }
}
?>