<?php
$cookie_name = 'name';

$ExpTime = time() + 60 * 60 * 24 * 365;

setcookie("curr-time", time(), $ExpTime,"/");

setcookie("user_id", "user_id", $ExpTime,"/");
setcookie("user_uid", "user_uid", $ExpTime,"/","",false,true);

setcookie("user_id_1", $cookie_name,["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
setcookie("user_uid_1", $cookie_name,["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);

setcookie("uid", $cookie_name, $ExpTime,"/");
setcookie("userid", $cookie_name, $ExpTime,"/");
setcookie("username", $cookie_name, $ExpTime,"/");
setcookie("nickname", $cookie_name, $ExpTime,"/");
setcookie("email", $cookie_name, $ExpTime,"/");
?>

<html>
<body>
user_id = <?php echo $_COOKIE['user_id']  ?><br />
user_uid = <?php echo $_COOKIE['user_uid']  ?><br />
user_uid_1 = <?php echo $_COOKIE['user_uid_1']  ?><br />
</body>
</html>
