<?php
require_once '../config.php';
require_once "../public/load_lang.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../redis/function.php";

// Require Composer's autoloader.
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_REQUEST["op"])) {
    $op = $_REQUEST["op"];
} else {
    $op = "login";
}

switch ($op) {
    case "login":
		if (isset($_GET["url"])) {
			$goto_url = $_GET["url"];
		}
		break;
    case "logout":
		if (isset($_COOKIE["username"])) {
			$message_comm = $_local->gui->user . " " . $_COOKIE["username"] . " " . $_local->gui->loged_out;
		}
		setcookie("user_uid", "", time() - 60, "/");
		setcookie("user_id", "", time() - 60, "/");
		setcookie("token", "", time() - 60, "/");

		setcookie("uid", "", time() - 60, "/");
		setcookie("username", "", time() - 60, "/");
		setcookie("userid", "", time() - 60, "/");
		setcookie("nickname", "", time() - 60, "/");
		setcookie("email", "", time() - 60, "/");

		break;
    case "new":
		$host = $_SERVER['HTTP_HOST'];
		//if (strpos($host, "wikipali.org") !== false)
		{
			if(isset($_REQUEST["invite"])){
				$redis = redis_connect();
				if ($redis == false) {
					echo "no redis connect\n";
					exit;
				}
				$code = $redis->exists("invitecode://".$_REQUEST["invite"]);
				if(!$code){
					echo "无效的邀请码，或邀请码已经过期。";
					exit;
				}
				$invite_email = $redis->get("invitecode://".$_REQUEST["invite"]);
			}else{
				echo "无邀请码";
				exit;
			}
		}
		break;
}

$post_nickname = "";
$post_username = "";
$post_password = "";
$post_email = "";
if (isset($_POST["op"]) && $_POST["op"] == "new") {
	PDO_Connect( _FILE_DB_USERINFO_ , _DB_USERNAME_ , _DB_PASSWORD_);
	//建立账号
    $op = "new";
    $post_username = trim($_POST["username"]);
    $post_password = trim($_POST["password"]);
    $post_nickname = trim($_POST["nickname"]);
    $post_email = trim($_POST["email"]);
	$post_error = false;
    if (empty($post_username)) {
        $error_username = $_local->gui->account . $_local->gui->cannot_empty;
		$post_error = true;
    }
	else{
        $query = "SELECT count(*) as co from "._TABLE_USER_INFO_." where username = ?" ;
        $iFetch = PDO_FetchOne($query,array($post_username));
        if ($iFetch > 0) { //username is existed
            $error_username = $_local->gui->account_existed;
			$post_error = true;
        }
	}
	if (empty($post_email)) {
        $error_email = $_local->gui->email . $_local->gui->cannot_empty;
		$post_error = true;
    }else{
		$query = "SELECT count(*) as co from "._TABLE_USER_INFO_." where email = ?" ;
		$iFetch = PDO_FetchOne($query,array($post_email));
		if ($iFetch > 0) { //username is existed
			$error_email = $_local->gui->email . "已经存在";
			$post_error = true;
		}
	}
    if (empty($post_password)) {
        $error_password = $_local->gui->password . $_local->gui->cannot_empty;
		$post_error = true;
    }else{
		if(strlen($post_password)<6){
			$error_password = $_local->gui->password . "过短";
			$post_error = true;
		}
	}

    if (empty($post_nickname)) {
        $error_nickname = $_local->gui->nick_name . $_local->gui->cannot_empty;
		$post_error = true;
    }

    if (!$post_error) {
        $md5_password = md5($post_password);
        $new_userid = UUID::v4();

				$query = "INSERT INTO "._TABLE_USER_INFO_." ('id','userid','username','password','nickname','email') VALUES (NULL," . $PDO->quote($new_userid) . "," . $PDO->quote($post_username) . "," . $PDO->quote($md5_password) . "," . $PDO->quote($post_nickname) . "," . $PDO->quote($post_email) . ")";
				$stmt = @PDO_Execute($query);
				if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
					$error = PDO_ErrorInfo();
					$error_comm = $error[2] . "系统错误，抱歉！请再试一次";
				} else {
					$message_comm = "新账户建立成功";
					$op = "login";
					unset($_POST["username"]);
					//TODO create channel

					//TODO create studio
				}

    }
} else {
	//登录
    if (isset($_POST["username"])) {
        $_username_ok = true;
        if ($_POST["username"] == "") {
            $_username_ok = false;
            $_post_error = $_local->gui->account . $_local->gui->account_existed;
        } else if (isset($_POST["password"])) {
            $md5_password = md5($_POST["password"]);
            PDO_Connect(_FILE_DB_USERINFO_);
            $query = "SELECT * from "._TABLE_USER_INFO_." where (\"username\"=" . $PDO->quote($_POST["username"]) . " or \"email\"=" . $PDO->quote($_POST["username"]) . " ) and \"password\"=" . $PDO->quote($md5_password);
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
				//验证成功
                $uid = $Fetch[0]["id"];
                $username = $Fetch[0]["username"];
                $user_uuid = $Fetch[0]["userid"];
                $nickname = $Fetch[0]["nickname"];
                $email = $Fetch[0]["email"];
				$ExpTime = time() + 60 * 60 * 24 * 365;
                //JWT
                $key = APP_KEY;
                $payload = [
                    'nbf' => time(),
                    'exp' => $ExpTime,
                    'uid' => $user_uuid,
                    'id' => $uid
                ];
                $jwt = JWT::encode($payload,$key,'HS512');
                //End of JWT
                // set cookie
				if(empty($_SERVER["HTTPS"])){
                    //本地开发
					setcookie("user_uid", $user_uuid,["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
					setcookie("user_id", $Fetch[0]["id"], ["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
					setcookie("token", $jwt, ["expires"=>$ExpTime,"path"=>"/","secure"=>false,"httponly"=>true]);
				}else{
                    //服务器运行
					setcookie("user_uid", $user_uuid, ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
					setcookie("user_id", $Fetch[0]["id"], ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
					setcookie("token", $jwt, ["expires"=>$ExpTime,"path"=>"/","secure"=>true,"httponly"=>true]);
				}
				#给js用的
				setcookie("uid", $uid, time()+60*60*24*365,"/");
				setcookie("username", $username, time()+60*60*24*365,"/");
				setcookie("userid", $user_uuid, time()+60*60*24*365,"/");
				setcookie("nickname", $nickname, time()+60*60*24*365,"/");
				setcookie("email", $email, time()+60*60*24*365,"/");



                if (isset($_POST["url"])) {
                    $goto_url = $_POST["url"];
                }
				#设置新密码
                if (isset($_COOKIE["url"])) {
                    setcookie("pwd_set", "on", time() + 60, "/");
                }
                ?>


<!DOCTYPE html>
<html>
	<head>

		<title>wikipali starting</title>
		<?php
		if (isset($goto_url)) {
                    $goto = $goto_url;
                } else {
                    $goto = "../studio/index.php";
                }
            ?>
		<meta http-equiv="refresh" content="0,<?php echo $goto; ?>"/>
        <script>
            localStorage.setItem('token',"<?php echo $jwt; ?>");
        </script>
	</head>

	<body>

		<br>
		<br>
		<p align="center"><a href="../studio/index.php">Auto Redirecting to Homepage! IF NOT WORKING, CLICK HERE</a></p>

    </body>
</html>
<?php

                exit;
            } else {
				//用户名不存在
                $_post_error = $_local->gui->incorrect_ID_PASS;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="text/css" rel="stylesheet" href="../studio/css/font.css"/>
		<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
		<link type="text/css" rel="stylesheet" href="../studio/css/color_day.css" id="colorchange" />
		<title>wikipali login</title>
		<script src="../public/js/comm.js"></script>
		<script src="../studio/js/jquery-3.3.1.min.js"></script>
		<script src="../studio/js/fixedsticky.js"></script>
<style>
#login_body{
	display: flex;
	padding: 2em;
	margin: auto;
}
#login_left {
	padding-right: 12em;
	padding-top: 5em;
}
.title{
	font-size: 150%;
	margin-top: 1em;
	margin-bottom: 0.5em;
}
#login_form{
	padding: 2em 0 1em 0;
}
#tool_bar {
	padding: 1em;
	display: flex;
	justify-content: space-between;
}
#login_shortcut {
	display: flex;
	flex-direction: column;
	padding: 2em 0;
}
#login_shortcut button{
	height:3em;
}
#button_area{
	text-align: right;
	    padding: 1em 0;
}
.form_help{
	font-weight: 400;
	color: var(--bookx);
}
.login_form input{
	margin-top:2em;
	padding:0.5em 0.5em;
}
.login_form select{
	margin-top:2em;
	padding:0.5em 0.5em;
}
.login_form input[type="submit"]{
	margin-top:2em;
	padding:0.1em 0.5em;
}

.form_error{
	color:var(--error-text);
}
#login_form_div{
	width:30em;
}

#ucenter_body {
	display: flex;
	flex-direction: column;
	margin: 0;
	padding: 0;
	background-color: var(--tool-bg-color3);
	color: var(--btn-color);
}
.icon_big {
	height: 2em;
	width: 2em;
	fill: var(--btn-color);
	transition: all 0.2s ease;
}
.form_field_name{
	position: absolute;
	margin-left: 7px;
	margin-top: 2em;
	color: var(--btn-border-line-color);
	-webkit-transition-duration: 0.4s;
	-moz-transition-duration: 0.4s;
	transition-duration: 0.4s;
	transform: translateY(0.5em);
}
.viewswitch_on {
	position: absolute;
	margin-left: 7px;
	margin-top: 1.5em;
	color: var(--bookx);
	-webkit-transition-duration: 0.4s;
	-moz-transition-duration: 0.4s;
	transition-duration: 0.4s;
	transform: translateY(-15px);
}

</style>
		<script>

		function login_init(){
			$("input").focus(function(){
				let name = $(this).attr("name");
				var objNave = document.getElementById("tip_"+name);
				objNave.className = "viewswitch_on";
			});
			$(".form_field_name").click(function(){
				let id = $(this).attr("id");
				var objNave = document.getElementById(id);
				objNave.className = "viewswitch_on";
				let arrId=id.split("_");
				document.getElementById('input_'+arrId[1]).focus();
			});

		}
		</script>
	<link type="text/css" rel="stylesheet" href="mobile.css" media="screen and (max-width:800px)">
	</head>
	<body id="ucenter_body" onload="login_init()">
	<div id="tool_bar">
		<div>
		</div>
		<div>
			<?php
			require_once '../lang/lang.php';
			?>
		</div>
	</div>
	<div id="login_body" >

	<div id="login_left">
		<div  >
			<svg  style="height: 8em;width: 25em;">
				<use xlink:href="../public/images/svg/wikipali_login_page.svg#logo_login"></use>
			</svg>
		</div>
		<div style="    padding: 1em 0 0 3.5em;font-weight: 400;">
		<?php echo $_local->gui->pali_literature_platform; ?>
		<ul style="padding-left: 1.2em;">
			<li><?php echo $_local->gui->online_dict_db; ?></li>
			<li><?php echo $_local->gui->user_data_share; ?></li>
			<li><?php echo $_local->gui->cooperate_edit; ?></li>
		</ul>
		</div>
	</div>
	<div id="login_right">
		<div id = "login_form_div" class="fun_block" >

		<?php

if (isset($error_comm)) {
    echo '<div class="form_error">';
    echo $error_comm;
    echo '</div>';
}
if (isset($message_comm)) {
    echo '<div class="form_help">';
    echo $message_comm;
    echo '</div>';
}
if ($op == "new") {
    //新建账号
    ?>
			<div class="title">
			<?php echo $_local->gui->join_wikipali; ?>
			</div>
			<div class="login_new">
				<span class="form_help"><?php echo $_local->gui->have_account; ?> ？</span><a href="index.php?language=<?php echo $currLanguage; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_local->gui->login; //登入账户 ?></a>
			</div>
			<div class="login_form" style="    padding: 3em 0 3em 0;">
			<form action="index.php" method="post">
				<div>

				<div>
						<span id='tip_username' class='form_field_name'><?php echo $_local->gui->account; ?></span>
						<input type="input" name="username"  value="<?php echo $post_username; ?>" />
					</div>
					<div id="error_username" class="form_error">
					<?php
					if (isset($error_username)) {echo $error_username;}
					?>
					</div>
					<div class="form_help">
						<?php echo $_local->gui->account_demond; ?>
					</div>

					<div>
						<span id='tip_email' class='form_field_name'><?php echo $_local->gui->email_address; ?></span>
						<input type="input" name="email"  value="<?php echo $post_email; ?>" />
						<div id="error_email" class="form_error">
						<?php
						if (isset($error_email)) {echo $error_email;}
						?>
						</div>
					</div>

					<div>
						<span id='tip_password' class='form_field_name'><?php echo $_local->gui->password; ?></span>
						<input type="password" name="password" placeholder="<?php echo $_local->gui->password; ?>" value="<?php echo $post_password; ?>" />
						<input type="password" name="repassword" placeholder="<?php echo $_local->gui->password_again; ?>" value="<?php echo $post_password; ?>" />
					</div>
					<div class="form_help">
					<?php echo $_local->gui->password_demond; ?>
					</div>
					<div id="error_password" class="form_error">
					<?php
					if (isset($error_password)) {echo $error_password;}
					?>
					</div>

					<div>

						<span id='tip_language' class='viewswitch_on'><?php echo "惯常使用的语言"; ?></span>
						<select name="language" style="width: 100%;">
						<?php
						$currLang = $_COOKIE["language"];
						$langList = [
										"en"=>$_local->language->en,
										"zh-cn"=>$_local->language->zh_cn,
										"zh-tw"=>$_local->language->zh_tw,
										"my"=>$_local->language->my,
										"si"=>$_local->language->si,
						];
						foreach ($langList as $key => $value) {
							# code...
							if($currLang==$key){
								$selected = " selected";
							}else{
								$selected = "";
							}
							echo "<option value='{$key}' {$selected}>{$value}</option>";
						}
						?>
						</select>
					</div>

					<div>
						<span id='tip_nickname' class='form_field_name'><?php echo $_local->gui->nick_name; ?></span>
						<input type="input" name="nickname" value="<?php echo $post_nickname; ?>" />
					</div>
					<?php
						if (isset($error_nickname)) {
							echo '<div id="error_nickname" class="form_error">';
							echo $error_nickname;
							echo '</div>';
						}
						else{
							echo '<div class="form_help">';
							echo $_local->gui->name_for_show;
							echo '</div>';

						}
					?>

					<input type="hidden" name="op" value="new" />
					<input type="hidden" name="invite" value="<?php echo $_REQUEST["invite"]; ?>" />
				</div>
				<div id="button_area">
					<input type="submit" value="<?php echo $_local->gui->continue; ?>" style="background-color: var(--link-hover-color);border-color: var(--link-hover-color);" />
				</div>
			</form>
			</div>

		<?php
} else {
    ?>
			<div class="title">
			<?php
if (isset($_POST["username"]) && $_username_ok == true) {
        echo $_POST["username"];
    } else {
        echo $_local->gui->login;
    }
    ?>
			</div>
			<div class="login_new">
<?php
    if (isset($_POST["username"]) && $_username_ok == true) {
        //已经输入用户名
        echo '<a href="index.php?language=' . $currLanguage . '">切换账户</a>';
    } else {
        echo '<span class="form_help">' . $_local->gui->new_to_wikipali . ' ？</span><a href="index.php?language=' . $currLanguage . '&op=new">&nbsp;&nbsp;&nbsp;&nbsp;' . $_local->gui->create_account . '</a>';
    }
    ?>

			<a href="forgot_pwd.php">忘记密码</a>
			<div class="login_form" style="padding: 3em 0 3em 0;">
			<form action="index.php" method="post">
				<div>
<?php
    if (isset($goto_url)) {
        echo "<input type=\"hidden\" name=\"url\" value=\"{$goto_url}\"  />";
    } else if (isset($_POST["url"])) {
        echo "<input type=\"hidden\" name=\"url\" value=\"{$_POST["url"]}\"  />";
    }
    if (isset($_POST["username"]) && $_username_ok == true) {
        echo "<span id='tip_password' class='form_field_name'>" . $_local->gui->password . "</span>";
        echo '<input type="password" name="password" />';
        echo "<input type=\"hidden\" name=\"username\" value=\"{$_POST["username"]}\"  />";
        if (isset($_post_error)) {
            echo '<div id="error_nikename" class="form_error">';
            echo $_post_error;
            echo '</div>';
        }
    } else {
        echo "<span id='tip_username' class='form_field_name'>" . $_local->gui->account . "/" . $_local->gui->e_mail . "</span>";
        echo '<input type="input" name="username" id="input_username" />';
        if (isset($_post_error)) {
            echo '<div id="error_nikename" class="form_error">';
            echo $_post_error;
            echo '</div>';
        }
    }
    ?>
				</div>
				<div id="button_area">
					<input type="submit" value="<?php echo $_local->gui->continue; ?>" style="background-color: var(--link-hover-color);border-color: var(--link-hover-color);" />
				</div>
				</form>
			</div>

			<div id="login_shortcut" style="display:none;">
				<button class="form_help"><?php echo $_local->gui->login_with_google; ?>&nbsp;
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#google_logo"></use>
					</svg>
				</button>
				<button class="form_help"><?php echo $_local->gui->login_with_facebook; ?>&nbsp;
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#facebook_logo"></use>
					</svg>
				</button>
				<button class="form_help"><?php echo $_local->gui->login_with_wechat; ?>&nbsp;
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#wechat_logo"></use>
					</svg>
				</button>
			</div>

			<?php
}
?>

		</div>
	</div>
	</div>
	<script>
	login_init();
	</script>
	</body>
</html>
