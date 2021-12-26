<?php
#忘记
require_once '../config.php';
require_once "../public/load_lang.php";
require_once "../public/function.php";

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
			忘记密码？
			</div>
			<div class="login_new">
				<span class="form_help"><?php echo $_local->gui->have_account; ?> ？</span><a href="index.php?language=<?php echo $currLanguage; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_local->gui->login; //登入账户 ?></a>
			</div>

			<div class="login_form" style="    padding: 3em 0 3em 0;">
				<div id="message" class="form_help">
					我们将向您的注册邮箱发送电子邮件。里面包含了重置密码链接。点击链接后按照提示操作，重置账户密码。
				</div>	
				<div id="form_div">	
					<form action="../api/user.php" method="get">
						<div>
							<div>
								<span id='tip_email' class='form_field_name'><?php echo $_local->gui->email_address; ?></span>
								<input id="form_email" type="input" name="email"  value="" />
							</div>
							<div id="error_email" class="form_error"> </div>
							<div class="form_help"></div>
						</div>

						<input type="hidden" name="_method" value="reset_email" />


					</form>
					<div id="button_area">
						<button id="send"  onclick="submit()" style="background-color: var(--link-hover-color);border-color: var(--link-hover-color);" >
						<?php echo $_local->gui->continue; ?>
						</button>
					</div>
				</div>				
			</div>
		</div>
	</div>
</div>

	<script>
	login_init();
	
	function submit(){
		$("#message").text("正在发送...");
		$(this).text("正在发送...");
		$(this).prop("disabled",true);
	$.getJSON(
		"../api/user.php",
		{
			_method:"reset_email",
			email:$("#form_email").val()
		}
	).done(function (data) {
		$("#message").text(data.message);
		if(data.ok){
			$("#message").removeClass("form_error");
			$("#form_div").hide();			
		}else{
			$("#message").addClass("form_error");
			//发送失败enable发送按钮
			$(this).prop("disabled",false);
			$(this).text("再次发送");
		}
		}).fail(function(jqXHR, textStatus, errorThrown){
			$("#message").addClass("form_error");
			switch (textStatus) {
				case "timeout":
					$("#message").text(gLocal.gui["error_net_timeout"]);	
					//发送失败enable发送按钮
					$(this).prop("disabled",false);
					$(this).text("再次发送");
					break;
				case "error":
					$("#message").text(gLocal.gui["error_net_"+jqXHR.status]);
					switch (jqXHR.status) {
						case 404:
							break;
						case 500:
							break;				
						default:
							break;
					}
					break;
				case "abort":
					$("#message").text(gLocal.gui["error_net_abort"]);	
					break;
				case "parsererror":
					$("#message").text(gLocal.gui["error_net_parsererror"]);	
					console.log("delete-parsererror",jqXHR.responseText);
					break;
				default:
					break;
			}
			
		});
		}
	</script>

	</body>
</html>