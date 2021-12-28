<?php 
 $username = "";
 $userid = "";
 $nickname = "";
 $email = "";
 $USER_ID = "";
 $UID = "";
 $USER_NAME = "";
 $NICK_NAME = "";
if(isset($_COOKIE["username"]) && !empty($_COOKIE["username"])){
 $USER_ID = $_COOKIE["userid"];
 $UID = $_COOKIE["uid"];
 $USER_NAME = $_COOKIE["username"];
 $NICK_NAME = $_COOKIE["nickname"];
 
 $username = $_COOKIE["username"];
 $userid = $_COOKIE["userid"];
 $nickname = $_COOKIE["nickname"];
 if(isset($_COOKIE["email"])){$email = $_COOKIE["email"];}
}
else{
	?>
<html>
<body>
Login...
<script>
window.location.assign("login.php");
</script>
</body>
</html>
<?php
return;
}

?>
