<?php
require_once '../path.php';

    //打开数据库
    $dns = "sqlite:"._FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    if(isset($_GET["id"])){
        $query = "select userid as id ,username,nickname from user where userid = ? ";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($_GET["id"]));
        $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else if(isset($_GET["username"])){
        $query = "select userid as id ,username,nickname,email from user where  nickname like ? limit 0,8";
        $stmt = $dbh->prepare($query);
        $username = "%".$_GET["username"]."%";
        $stmt->execute(array($username));        
        $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $fUser = array();
    }
    
    echo json_encode($fUser, JSON_UNESCAPED_UNICODE);
    $dbh=null;   
?>