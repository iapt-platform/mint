<?php
require_once '../config.php';

//打开数据库

$dbh = new PDO(_FILE_DB_USERINFO_, _DB_USERNAME_,_DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if (isset($_GET["id"])) {
	if(isset($_GET["bio"])){
		$query = "SELECT bio,email from profile where user_id = ? ";
	}
	else{
		$query = "SELECT userid as id ,username,nickname from "._TABLE_USER_INFO_." where userid = ? ";
	}

    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_GET["id"]));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else if (isset($_GET["username"])) {
    $query = "SELECT userid as id ,username,nickname,email from "._TABLE_USER_INFO_." where  nickname like ? limit 10 offset 0";
    $stmt = $dbh->prepare($query);
    $username = "%" . $_GET["username"] . "%";
    $stmt->execute(array($username));
    $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    $fUser = array();
}

echo json_encode($fUser, JSON_UNESCAPED_UNICODE);
$dbh = null;
