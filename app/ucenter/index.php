<?php
require_once '../path.php';
require_once "../public/load_lang.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

if (isset($_GET["op"])) {
    $op = $_GET["op"];
} else {
    $op = "login";
}

switch ($op) {
    case "login":
        {
            if (isset($_GET["url"])) {
                $goto_url = $_GET["url"];
            }
            break;
        }
    case "logout":
        {
            if (isset($_COOKIE["nickname"])) {
                $message_comm = $_local->gui->user . " " . $_COOKIE["nickname"] . " " . $_local->gui->loged_out;
            }
            setcookie("uid", "", time() - 60, "/");
            setcookie("username", "", time() - 60, "/");
            setcookie("userid", "", time() - 60, "/");
            setcookie("nickname", "", time() - 60, "/");
            setcookie("email", "", time() - 60, "/");
            break;
        }
    case "new":
        {
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($host, "wikipali.org") !== false) {
                echo "网站正处于开发阶段。目前不支持注册。";
                exit;
            }
            break;
        }
}

$post_nickname = "";
$post_username = "";
$post_password = "";
$post_email = "";
if (isset($_POST["op"]) && $_POST["op"] == "new") {
    $op = "new";
    $post_username = $_POST["username"];
    $post_password = $_POST["password"];
    $post_nickname = $_POST["nickname"];
    $post_email = $_POST["email"];
    if (empty($post_username)) {
        $error_username = $_local->gui->account . $_local->gui->cannot_empty;
    }
    if (empty($post_password)) {
        $error_password = $_local->gui->password . $_local->gui->cannot_empty;
    }
    if (empty($post_nickname)) {
        $error_nickname = $_local->gui->nick_name . $_local->gui->cannot_empty;
    }
    if (!empty($post_username) && !empty($post_password) && !empty($post_nickname)) {
        $md5_password = md5($post_password);
        $new_userid = UUID::v4();
        PDO_Connect("" . _FILE_DB_USERINFO_);
        $query = "select * from user where \"username\"=" . $PDO->quote($post_username);
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) { //username is existed
            $error_username = $_local->gui->account_existed;
        } else {
            $query = "INSERT INTO user ('id','userid','username','password','nickname','email') VALUES (NULL," . $PDO->quote($new_userid) . "," . $PDO->quote($post_username) . "," . $PDO->quote($md5_password) . "," . $PDO->quote($post_nickname) . "," . $PDO->quote($post_email) . ")";
            $stmt = @PDO_Execute($query);
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                $error = PDO_ErrorInfo();
                $error_comm = $error[2] . "抱歉！请再试一次";
            } else {
                //created user recorder
                $newUserPath = _DIR_USER_DOC_ . '/' . $new_userid;
                $userDirMyDocument = $newUserPath . _DIR_MYDOCUMENT_;
                if (!file_exists($newUserPath)) {
                    if (mkdir($newUserPath)) {
                        mkdir($userDirMyDocument);
                    } else {
                        $error_comm = "建立用户目录失败，请联络网站管理员。";
                    }
                }
                $message_comm = "新账户建立成功";
                $op = "login";
                unset($_POST["username"]);
            }
        }
    } else {

    }
} else {
    if (isset($_POST["username"])) {
        $_username_ok = true;
        if ($_POST["username"] == "") {
            $_username_ok = false;
            $_post_error = $_local->gui->account . $_local->gui->account_existed;
        } else if (isset($_POST["password"])) {
            $md5_password = md5($_POST["password"]);
            PDO_Connect("" . _FILE_DB_USERINFO_);
            $query = "select * from user where (\"username\"=" . $PDO->quote($_POST["username"]) . " or \"email\"=" . $PDO->quote($_POST["username"]) . " ) and \"password\"=" . $PDO->quote($md5_password);
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) { //username is exite
                $uid = $Fetch[0]["id"];
                $username = $Fetch[0]["username"];
                $userid = $Fetch[0]["userid"];
                $nickname = $Fetch[0]["nickname"];
                $email = $Fetch[0]["email"];
                setcookie("uid", $uid, time() + 60 * 60 * 24 * 365, "/");
                setcookie("username", $username, time() + 60 * 60 * 24 * 365, "/");
                setcookie("userid", $userid, time() + 60 * 60 * 24 * 365, "/");
                setcookie("nickname", $nickname, time() + 60 * 60 * 24 * 365, "/");
                setcookie("email", $email, time() + 60 * 60 * 24 * 365, "/");
                if (isset($_POST["url"])) {
                    $goto_url = $_POST["url"];
                }
                if (isset($_COOKIE["url"])) {
                    setcookie("pwd_set", "on", time() + 60, "/");
                }
                $newUserPath = _DIR_USER_DOC_ . '/' . $userid . '/';
                if (!file_exists($newUserPath)) {
                    echo "error:cannot find user dir:$newUserPath<br/>";
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
                $_post_error = $_local->gui->incorrect_ID_PASS;
            }
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, "wikipali.org") !== false) {
    echo "网站正处于开发阶段。目前不支持注册。";
}
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
				    <span id='tip_nickname' class='form_field_name'><?php echo $_local->gui->nick_name; ?></span>
					<input type="input" name="nickname" value="<?php echo $nickname; ?>" />
				</div>
					<div class="form_help">
						<?php echo $_local->gui->name_for_show; ?>
					</div>

					<div id="error_nickname" class="form_error">
						<?php
if (isset($error_nickname)) {echo $error_nickname;}
    ?>
					</div>
					<div>
					<select name="language" style="width: 100%;">
						<option><?php echo $_local->language->en; ?></option>
						<option><?php echo $_local->language->zh_cn; ?></option>
						<option><?php echo $_local->language->zh_tw; ?></option>
						<option><?php echo $_local->language->my; ?></option>
						<option><?php echo $_local->language->si; ?></option>
					</select>
					</div>

					<div>
						<span id='tip_email' class='form_field_name'><?php echo $_local->gui->email_address; ?></span>
						<input type="input" name="email"  value="<?php echo $post_email; ?>" />
					</div>

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
						<span id='tip_password' class='form_field_name'><?php echo $_local->gui->password; ?></span>
						<input type="password" name="password"  value="<?php echo $post_password; ?>" />
						<input type="password" name="repassword"  value="<?php echo $post_password; ?>" />
					</div>
					<div class="form_help">
					<?php echo $_local->gui->password_demond; ?>
					</div>

					<div id="error_password" class="form_error">
					<?php
if (isset($error_password)) {echo $error_password;}
    ?>
					</div>
					<input type="hidden" name="op" value="new" />
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
        echo '<a href="index.php?language=' . $currLanguage . '">切换账户</a>';
    } else {
        echo '<span class="form_help">' . $_local->gui->new_to_wikipali . ' ？</span><a href="index.php?language=' . $currLanguage . '&op=new">&nbsp;&nbsp;&nbsp;&nbsp;' . $_local->gui->create_account . '</a>';
    }
    ?>


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
			<div id="login_shortcut">
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