<?php
 $username = "";
 $userid = "";
 $nickname = "";
 $email = "";
 
 $USER_ID = "";
 $UID = "";
 $USER_NAME = "";
 $NICK_NAME = "";
if(isset($_COOKIE["userid"])){
//已经登陆
 $USER_ID = $_COOKIE["userid"];
 $UID = $_COOKIE["uid"];
 $USER_NAME = $_COOKIE["username"];

 
 $username = $_COOKIE["username"];
 $userid = $_COOKIE["userid"];
 $nickname = $_COOKIE["nickname"];
 if(isset($_COOKIE["email"])){$email = $_COOKIE["email"];}
}
else{
	//尚未登陆
?>
<html>
	<head>
		<title>wikipali login</title>
		<meta http-equiv="refresh" content="0,../ucenter/index.php"/>
	</head>
	
	<body>
		<br>
		<br>
		<p align="center"><a href="../ucenter/index.php">您尚未登陆。正在自动转向登陆页面。也可以单击此处登陆。</a></p>
    </body>
</html>
<?php
exit;
}
?>