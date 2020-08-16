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

function ucenter_getA($userid,$fields){
    //打开数据库
    $dns = "sqlite:"._FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    $query = "select username from user where userid= ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($userid));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh=null;
    if(count($fUser)>0){
        return($fUser[0]["username"]);
    }
    else{
        return("");
    }

}
?>