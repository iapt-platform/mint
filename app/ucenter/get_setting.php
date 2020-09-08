<?php
require_once '../path.php';
if(!isset($_COOKIE["userid"])){
    $setting = array();
}
else{
    $setting=json_decode(file_get_contents("../ucenter/default.json"),TRUE);
    //打开数据库
    $dns = "sqlite:"._FILE_DB_USERINFO_;
    $dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

    $query = "select setting from user where userid = ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_COOKIE["userid"]));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbh=null;      
    if(isset($fUser[0]["setting"])){
        $my_setting=json_decode($fUser[0]["setting"],TRUE);
        foreach ($setting as $key => $value) {
            if(isset($my_setting[$key])){
                $setting[$key] = $my_setting[$key];
            }
        }
    }
}

echo json_encode($setting, JSON_UNESCAPED_UNICODE);

?>