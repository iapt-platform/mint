<?php
//require 'checklogin.inc';
require '../app/config.php';
if(isset($_GET["uid"])){
	$uid=$_GET["uid"];
}
else{
	echo "no user id";
	exit;
}

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
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}
else{
	include $dir_language."default.php";
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
	<title><?php echo $module_gui_str['editor']['1051'];?>PCD Reader</title>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script type="text/javascript">
	
		var xmlhttp;
		function showUserFilaList()
		{

		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		var orderby="update_time";
		var order="desc";
		var d=new Date();
		xmlhttp.onreadystatechange=serverResponse;
		xmlhttp.open("GET","getfilelist.php?t="+d.getTime()+"&uid=<?php echo $uid;?>&orderby="+orderby+"&order="+order,true);
		xmlhttp.send();
		}

		function serverResponse()
		{
			
			if (xmlhttp.readyState==4)// 4 = "loaded"
			{
			  if (xmlhttp.status==200)
				{// 200 = "OK"
				var arrFileList = xmlhttp.responseText.split(",");
				var fileList="";

				fileList=xmlhttp.responseText;
				document.getElementById('userfilelist').innerHTML=fileList;
				}
			  else
				{
				document.getElementById('userfilelist')="Problem retrieving data:" + xmlhttp.statusText;
				}
			}
		}
		
			var g_language="en";
			function menuLangrage(obj){
				g_language=obj.value;
				setCookie('language',g_language,365);
				window.location.assign("index.php?language="+g_language);
			}
		function indexInit(){
			showUserFilaList();
		}
	</script>

</head>
<body class="indexbody" onLoad="indexInit()">
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
			Visuddhinanda的个人空间
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
			
			<?php 
				echo $_local->gui->welcome;
				echo "<a href=\"setting.php?item=account\">";
				//echo urldecode($_COOKIE["nickname"]);
				echo "</a>";
				echo $_local->gui->to_the_dhamma;
				echo "<a href='login.php?op=logout'>";
				echo $_local->gui->logout;
				echo "</a>";
			?>
			
			</div>
		</div>	
		<!--tool bar end -->
		<script>
			document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
		</script>
		
	<div style="width:100%;">
		<div id="wizard_palicannon_par_select">
				<a name="toc_root"></a>
				<div id="wizard_palicannon_par_select_toc" class="fixedsticky">
					<div id="uhome_nav">
						<ul>
							<li>律藏(2)</li>
							<li>长部(0)</li>
							<li>中部(6)</li>
							<li>相应部(206)</li>
							<li>增支部(0)</li>
							<li>小部(0)</li>
							<li>律藏(0)</li>
							<li>其他(0)</li>
						</ul>
						<br />
						<ul>
							<li>本周(2)</li>
							<li>本月(0)</li>
							<li>今年(6)</li>
							<li>2018(206)</li>
						</ul>
						<br />
						<div>
						标签：<br>
						故事 法句 持戒
						</div>
					</div>				
				</div>
				<div id="wizard_palicannon_par_select_text">
					<div id="wizard_palicannon_par_select_text_body">
						<div id="userfilelist">
						<?php echo $_local->gui->loading;?>
						</div>					
					</div>
				</div>
		</div>				
	</div>
<div class="foot_div">
<?php echo $_local->gui->poweredby;?>
</div>
</body>
</html>

