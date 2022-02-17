<?php
#重置密码
require_once '../config.php';
require_once "../public/load_lang.php";
require_once "../public/function.php";
require_once "../redis/function.php";
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="text/css" rel="stylesheet" href="../studio/css/font.css"/>
		<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
		<link type="text/css" rel="stylesheet" href="../studio/css/color_day.css" id="colorchange" />
		<title>wikipali reset password</title>
		<script src="../public/js/comm.js"></script>
		<script src="../studio/js/jquery-3.3.1.min.js"></script>
		<script src="../studio/js/fixedsticky.js"></script>
		<script src="../ucenter/sign.js"></script>
		<script>
		<?php require_once '../public/load_lang_js.php'; ?>
		</script>
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

			<div class="title">
			wikipali sign up
			</div>
			<div class="login_new">
				<span class="form_help"><?php echo $_local->gui->have_account; ?> ？</span><a href="index.php?language=<?php echo $currLanguage; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_local->gui->login; //登入账户 ?></a>
			</div>
			<div class="form_error">
			<?php
			if (!isset($_GET["invite"])) {
				echo "Only for invited person";
				exit;
			}else{
				$redis = redis_connect();
				if ($redis == false) {
					echo "Server Error，please try again.<br> no_redis_connect";
					exit;
				}
				$code = $redis->exists("invitecode://".$_REQUEST["invite"]);
				if(!$code){
					echo "Invalid invide code or code expired.";
					exit;
				}
				$invite_email = $redis->get("invitecode://".$_REQUEST["invite"]);				
			?>
			</div>
		<div class="login_form" style="    padding: 3em 0 3em 0;">
			<div class="form_help" id="message"> </div>	
			<div id="form_div">
				<form id="user_create_form" action="#" method="post">
					<div>
						<div>
							<span id='tip_username' class='form_field_name'><?php echo $_local->gui->account; ?></span>
							<input type="input" id="username" name="username" maxlength="32" value="" />
						</div>
						<div id="error_username" class="form_error"> </div>
						<div class="form_help"> <?php echo $_local->gui->account_demond; ?> </div>
					</div>

					<div>
						<span id='tip_email' class='viewswitch_on'><?php echo $_local->gui->email_address; ?></span>
						<input type="input" id="email" name="email" disabled value="<?php echo $invite_email; ?>" />
						<div id="error_email" class="form_error"> </div>
					</div>

					<div>
						<div>
							<span id='tip_password' class='form_field_name'><?php echo $_local->gui->password; ?></span>
							<input type="password" id="password"  maxlength="32"  name="password" placeholder="<?php echo $_local->gui->password; ?>" value="" />
							<input type="password" id="repassword" maxlength="32"  name="repassword" placeholder="<?php echo $_local->gui->password_again; ?>" value="" />
						</div>
						<div class="form_help">
						<?php echo $_local->gui->password_demond; ?>
						</div>
						<div id="error_password" class="form_error"> </div>
					</div>

						<div>
							<span id='tip_language' class='viewswitch_on'><?php echo "惯常使用的语言 Usual Language"; ?></span>
							<select id="lang" name="language" style="width: 100%;">
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
							<div>
								<span id='tip_nickname' class='form_field_name'><?php echo $_local->gui->nick_name; ?></span>
								<input type="input" id="nickname"  maxlength="32"  name="nickname" placehoder="" value="" />
							</div>
							<div class="form_help">
							</div>
							<div id="error_password" class="form_error"> </div>
						</div>

						<input type="hidden" id="invite" name="invite" value="<?php echo $_REQUEST["invite"]; ?>" />
				</form>
				<div id="button_area">
					<button  onclick="submit()" style="background-color: var(--link-hover-color);border-color: var(--link-hover-color);" >
					<?php echo $_local->gui->continue; ?>
					</button>
				</div>	
			</div>	
		</div>
			<?php
			}
			?>
		</div>
	</div>
</div>

<script>
	login_init();

	$("#username").on("change",function(){
		$("#nickname").attr("placeholder",$("#username").val());
	})
	
	
	
</script>

	</body>
</html>