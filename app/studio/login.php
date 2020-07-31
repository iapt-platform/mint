<?php
require_once '../public/config.php';
require_once "../public/_pdo.php";
require_once "./public.inc";

if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
}
else{
	if(isset($_COOKIE["language"])){
		$currLanguage=$_COOKIE["language"];
	}
	else{
		$currLanguage="en";
	}
}

//load language file
include $dir_language."default.php";
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}


if(isset($_GET["device"])){
	$currDevice=$_GET["device"];
}
else{
	if(isset($_COOKIE["device"])){
		$currDevice=$_COOKIE["device"];
	}
	else{
		$currDevice="computer";
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<title><?php echo $module_gui_str['editor']['1051'];?>PCD Studio</title>
	<script language="javascript" src="js/common.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script type="text/javascript">
	
		var g_device = "computer";
		var strSertch = location.search;
		if(strSertch.length>0){
			strSertch = strSertch.substr(1);
			var sertchList=strSertch.split('&');
			for ( i in sertchList){
				var item = sertchList[i].split('=');
				if(item[0]=="device"){
					g_device=item[1];
				}
			}
		}
		if(g_device=="mobile"){
			g_is_mobile=true;
		}
		else{
			g_is_mobile=false;
		}


			var g_langrage="en";
			function menuLangrage(obj){
				g_langrage=obj.value;
				setCookie('language',g_langrage,365);
				window.location.assign("login.php?language="+g_langrage);
			}

	</script>

</head>
<body class="indexbody" onLoad="">
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
			</div>
			<div>
			
			</div>
			<div class="toolgroup1">
				<span><?php echo $module_gui_str['editor']['1050'];?></span>
				<select id="id_language" name="menu" onchange="menuLangrage(this)">
					<option value="en" >English</option>
					<option value="sinhala" >සිංහල</option>
					<option value="zh" >简体中文</option>
					<option value="tw" >繁體中文</option>
				</select>
			
			</div>
		</div>	
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
		</script>
	<div class="index_inner" style="width: 100%;">
		<div id="id_app_name"><?php echo $module_gui_str['editor']['1051'];?>
			<span style="font-size: 70%;">1.6</span><br />
			<?php if($currLanguage=="en"){ ?>
				<span style="font-size: 70%;">Pali Cannon Database Studio</span>
			<?php 
			}
			else{
			?>
				<span style="font-size: 70%;">PCD Studio</span>
			<?php
			}
			?>
		</div>
				
		<div class="fun_block">
			<h2>
				<?php
					if(isset($_GET["op"])){
						if($_GET["op"]=="new"){
				?>
				<span style="width: 15em;">
					<a href="login.php">
					<?php echo $module_gui_str['editor']['1090'];?>
					</a>
				</span>
				<span style="width: 15em;">
					<?php echo $module_gui_str['editor']['1091'];?>			
				</span>
			<?php }
		}
		else{
			?>
				<span style="width: 15em;">
					<?php echo $module_gui_str['editor']['1090'];?>
				</span>
				<span style="width: 15em;">
					<a href="login.php?op=new">
					<?php echo $module_gui_str['editor']['1091'];?>
					</a>				
				</span>
			<?php
		}
		?>
			</h2>

			<?php
			if(isset($_GET["op"])){
				if($_GET["op"]=="new"){
			?>

			<div>
				<form action="login.php" method="post">
					<div class="project_res_add_author" style="width: 80%;">
								<span style="width: 15em;"><?php echo $module_gui_str['editor']['1092'];?>: </span>
								<input type="text" name="username" />
								<span style="width: 17em;"><?php echo $module_gui_str['editor']['1096'];?> 64 <?php echo $module_gui_str['editor']['1097'];?></span>
					</div>
					<div class="project_res_add_author" style="width: 80%;">
								<span style="width: 15em;"><?php echo $module_gui_str['editor']['1093'];?>: </span>
								<input type="password" name="password" />
								<span style="width: 17em;"></span>
					</div>
					<div class="project_res_add_author" style="width: 80%;">
								<span style="width: 15em;"><?php echo $module_gui_str['editor']['1098'];?>: </span>
								<input type="password" name="password_again" />
								<span style="width: 17em;"></span>
					</div>
					<div class="project_res_add_author" style="width: 80%;">
								<span style="width: 15em;"><?php echo $module_gui_str['editor']['1094'];?>: </span>
								<input type="text" name="nickname" />
								<span style="width: 17em;"><?php echo $module_gui_str['editor']['1096'];?> 64 <?php echo $module_gui_str['editor']['1097'];?></span>
					</div>
					<div class="project_res_add_author" style="width: 80%;">
								<span style="width: 15em;"><?php echo $module_gui_str['editor']['1095'];?>: </span>
								<input type="text" name="email" />
								<span style="width: 17em;"><?php echo $module_gui_str['editor']['1096'];?> 128 <?php echo $module_gui_str['editor']['1097'];?></span>
					</div>
					<input type="hidden" name="op" value="new"/>
					<input type="submit" value=<?php echo $module_gui_str['editor']['1090'];?>>
					</form>					
			<?php
				}
				if($_GET["op"]=="logout"){
			?>
					<script type="text/javascript">
						setCookie('uid','',365);
						setCookie('username','',365);
						setCookie('userid','',365);
						setCookie('nickname','',365);
						setCookie('email','',365);
					</script>
			<?php
					echo "Logout<br>";
					echo "<a href=\"login.php\">Login</a>";
			?>
				<script>
					window.location.assign("login.php");
				</script>
			<?php
				}
				
			}
			else{
				if(isset($_POST["op"])){
					switch($_POST["op"]){
						case "login":
							$username=$_POST["username"];
							$password=$_POST["password"];
							if(empty($username)){
								echo "Error:User Name Is Empty!<br>";
							}
							if(empty($password)){
								echo "Error:Password Is Empty!<br>";
							}
							if(!empty($username) && !empty($password)){
								$md5_password=md5($password);
								$db_file = $_file_db_userinfo;
								PDO_Connect("sqlite:$db_file");
								$query = "select * from user where \"username\"=".$PDO->quote($username)." and \"password\"=".$PDO->quote($md5_password);
								$Fetch = PDO_FetchAll($query);
								$iFetch=count($Fetch);
								if($iFetch>0){//username is exite
									$uid=$Fetch[0]["id"];
									$username=$Fetch[0]["username"];
									$userid=$Fetch[0]["userid"];
									$nickname=$Fetch[0]["nickname"];
									$email=$Fetch[0]["email"];
									?>
									<script type="text/javascript">
									setCookie('uid','<?php echo $uid ?>',365);
									setCookie('username','<?php echo $username ?>',365);
									setCookie('userid','<?php echo $userid ?>',365);
									setCookie('nickname','<?php echo $nickname ?>',365);
									setCookie('email','<?php echo $email ?>',365);
									</script>
									<?php
									$newUserPath=$dir_user_base.$userid.'/';
									if(!file_exists($newUserPath)){
										echo "error:cannot find user dir:$newUserPath<br/>";
									}
									
									echo "Ok<br>";
									echo "<a href=\"index.php\">Enter</a>";
									?>
									<script>
									window.location.assign("index.php");
									</script>
									<?php
								}
								else{
									echo "username or password error<br/>";
									echo "<a href=\"login.php\">Try Again</a>";
								}
							}
							else{
								echo "<a href=\"login.php?\">Try Again</a>";
							}						
						
						break;
						case "new":
							$username=$_POST["username"];
							$password=$_POST["password"];
							$nickname=$_POST["nickname"];
							$email=$_POST["email"];
							if(empty($username)){
								echo "Error:User Name Is Empty!<br>";
							}
							if(empty($password)){
								echo "Error:Password Is Empty!<br>";
							}
							if(empty($nickname)){
								echo "Error:Nickname Is Empty!<br>";
							}
							if(!empty($username) && !empty($password) && !empty($nickname)){
								$md5_password=md5($password);
								$userid=GUIDv4();
								$db_file = $dir_db_userinfo.$file_db_userinfo;
								PDO_Connect("sqlite:$db_file");
								$query = "select * from user where \"username\"=".$PDO->quote($username);
								$Fetch = PDO_FetchAll($query);
								$iFetch=count($Fetch);
								if($iFetch>0){//username is exite
									echo "user name is exite<br>";
									echo "<a href=\"login.php?op=new\">Try Again</a>";
								}
								else{
									$query="INSERT INTO user ('id','userid','username','password','nickname','email') VALUES (NULL,".$PDO->quote($userid).",".$PDO->quote($username).",".$PDO->quote($md5_password).",".$PDO->quote($nickname).",".$PDO->quote($email).")";
									$stmt = @PDO_Execute($query);
									if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
										$error = PDO_ErrorInfo();
										print_r($error[2]);
										echo "<a href=\"login.php?op=new\">Try Again</a>";
										break;
									}
									
									$newUserPath=$dir_user_base.$userid;
									$userDirApp=$newUserPath.$dir_myApp;
									$userDirDict=$newUserPath.$dir_dict_user;
									$userDirMyDocument=$newUserPath.$dir_mydocument;
									$userDirMyPaliCanon=$newUserPath.$dir_myPaliCannon;
									if(!file_exists($newUserPath)){
										if(mkdir($newUserPath)){
											mkdir($userDirApp);
											mkdir($userDirDict);
											mkdir($userDirMyDocument);
											//copy($dir_user_templet.$dir_myApp."/config.js",$userDirApp."/config.js");
											//copy($dir_user_templet.$dir_myApp."/dictlist.json",$userDirApp."/dictlist.json");
											//copy($dir_user_templet.$dir_myApp."/style.css",$userDirApp."/style.css");
											//copy($dir_user_templet.$dir_myApp."/userinfo.js",$userDirApp."/userinfo.js");
											//copy($dir_user_templet.$dir_myApp."/userinfo.php",$userDirApp."/userinfo.php");

											//copy($dir_user_templet.$dir_dict_user."/wbw.db",$userDirDict."/wbw.db");
											//copy($dir_user_templet.$dir_dict_user."/default.db",$userDirDict."/default.db");

										}
										else{
											echo "create dir fail<br>";
										}
									}
									echo "Create Succecful<br/>";
									echo "User Name:$username<br/>";
									echo "Nickname:$nickname<br/>";
									echo "Email:$email<br/>";
									echo "<a href=\"login.php\">Login</a>";
								}
							}
							else{
								echo "<a href=\"login.php?op=new\">Try Again</a>";
							}
						break;
					}
				}
				else{
				?>

				
				<div>
					<form action="login.php" method="post">
					<div class="project_res_add_author" style="width: 70%;">
								<span style="width: 12em;"><?php echo $module_gui_str['editor']['1092'];?>: </span>
								<input type="text" name="username" size="20" style="width:initial;" value="guest" />
								<span style="width: 20em;"><?php echo $module_gui_str['editor']['1096'];?> 64 <?php echo $module_gui_str['editor']['1097'];?></span>
					</div>
					<div class="project_res_add_author" style="width: 70%;">
								<span style="width: 12em;"><?php echo $module_gui_str['editor']['1093'];?>: </span>
								<input type="password" name="password" size="20" style="width:initial;" value="guest"/>
								<span style="width: 20em;"></span>
					</div>
					<input type="hidden" name="op" value="login"/>
					<input type="submit" value=<?php echo $module_gui_str['editor']['1090'];?>>
				</form>
					
				</div>
				<?php
				}
			}
			?>

			</div>
		</div>
		
	</div>
<div class="foot_div">
<?php echo $module_gui_str['editor']['1066'];?>
</div>
</body>
</html>

