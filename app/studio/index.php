<?php
require_once '../ucenter/login.php';
require_once '../public/config.php';
require_once '../public/load_lang.php';


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
	<title><?php echo $_local->gui->pcd_studio; ?></title>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="js/index_mydoc.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="../public/js/jquery.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script src="../doc/coop.js"></script>
	<script src="../public/js/notify.js"></script>
	<script src="../public/js/comm.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>

	<script type="text/javascript">
	<?php require_once '../public/load_lang_js.php';//加载js语言包?>
		
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
			window.location.assign("index.php?language="+g_langrage);
		}
	
	var gCurrPage="index";
	</script>
	<style>

	</style>
</head>
<body id="file_list_body" onLoad="indexInit()">

	<?php
	require_once 'index_tool_bar.php';
	?>
	<script>
		document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
	</script>
		
        <style>
            #login_body{
                display: flex;
                padding: 2em;
            }
            #login_left {
                padding-left: 4em;
                flex: 5;
            }
            #login_right{
                flex: 5;
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

            .help_div{
                margin-bottom: 2em;
                width: 28em;
            }
            .htlp_title{
                font-size:140%;
                margin-bottom: 0.5em;
            }
            .help_fun_block{
                background-color: var(--tool-bg-color);
                color: var(--tool-color);
                padding: 4px 4px 4px 12px;
                max-width: 95%;
                border-radius: 4px;
                margin-bottom: 0.5em;
            }
            .help_fun_block .title{
                font-size:120%;
                margin-top:0.5em;
                margin-bottom:0.5em;
            }
            .help_fun_block_link_list li{
                display:inline;
            }
        </style>

<style  media="screen and (max-width:767px)">
            #login_body {
                flex-direction: column;
                padding: 0.2em;
            }

            #login_left {
                padding-right: 0;
                padding-top: 6em;
            }

            #login_form_div{
                width:100%;
            }

            #login_right {
                margin-top: 2em;
                margin-left: 10px;
                margin-right: 8px;
            }
            .fun_block{
                max-width: 100%;
            }

        </style>



	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">

	<div id="login_body" >

<div id="login_left">
	<div class="help_div">
		<div class="htlp_title">
		</div>
		<ul style="padding-left: 1.2em;">
			<li><?php echo $_local->gui->pali_literature_platform;?></li>
			<li><?php echo $_local->gui->user_data_share;?></li>
			<li><?php echo $_local->gui->cooperate_edit;?></li>
		</ul>

	</div>
	<div class="help_div">
		<div class="htlp_title"><?php echo $_local->gui->start;?></div>
		<ul style="list-style-type: none;">
			<li><a href="../studio/index_pc.php" target="_block"><?php echo $_local->gui->newproject;?></a></li>
			<li><a href="" target="_block">自学教程（建設中）</a></li>
		</ul>
	</div>
	<div class="help_div">
		<div class="htlp_title">
		<?php echo $_local->gui->recent_scan;?>
		</div>
		<ul id="file_list" style="list-style-type: none;">
		</ul>
	</div>
	<div class="help_div">
		<div class="htlp_title">
		<?php echo $_local->gui->help;?>
		</div>
		<ul style="list-style-type: none;">
			<li><?php echo $_local->gui->function_introduce;?>&nbsp;&nbsp;&nbsp;
				<a href="https://www.youtube.com/playlist?list=PL_1iJBQvNPFFNLOaZh2u3VwDYUyJuK_xa" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youtube_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youku_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#tudou_logo"></use>
					</svg>
				</a>
			</li>
			<li><?php echo $_local->gui->project_introduce;?>&nbsp;&nbsp;&nbsp;
				<a href="https://www.youtube.com/playlist?list=PL_1iJBQvNPFHT6UisME_cOSts5fFecK14" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youtube_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youku_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#tudou_logo"></use>
					</svg>
				</a>
			</li>
			<li><?php echo $_local->gui->project_introduce_inbrief;?>&nbsp;&nbsp;&nbsp;
				<a href="https://www.youtube.com/playlist?list=PLgavmc8e-GuWR-FKOr-7RfnUSWX82ED0q" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youtube_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#youku_logo"></use>
					</svg>
				</a>&nbsp;
				<a href="" target="_block" style="display: none;">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#tudou_logo"></use>
					</svg>
				</a>
			</li>
			<li><a href="" target="_block">wikipali论坛（企劃中）</a></li>
			<li>
				<!--<a href="https://github.com/iapt-platform/PCD-internet1.0#wikipali-demo" target="_block">-->
				<?php echo $_local->gui->help_doc;?>
				</a>&nbsp;&nbsp;&nbsp;
				<a href="https://iapt-platform.github.io/PCD-internet1.0/" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#github_logo"></use>
					</svg>
				</a>
				<a href="https://gitee.com/sakya__kosalla/PCD-internet1.0#wikipali-demo" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#gitee_logo"></use>
					</svg>
				</a>
			</li>
			<li>
				<!--<a href="https://github.com/iapt-platform/PCD-internet1.0" target="_block">-->
				<?php echo $_local->gui->code_add;?>
				</a>&nbsp;&nbsp;&nbsp;
				<a href="https://github.com/iapt-platform/PCD-internet1.0" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#github_logo"></use>
					</svg>
				</a>
				<a href="https://gitee.com/sakya__kosalla/PCD-internet1.0" target="_block">
					<svg class="icon">
						<use xlink:href="./studio/svg/icon.svg#gitee_logo"></use>
					</svg>
				</a>
			</li>
		</ul>
	</div>
</div>	

<div id="login_right">

	<div class="help_div">
		<div  class="htlp_title">个性化设置</div>
		<div>
			<div class="help_fun_block">
				<div class="title" >语言</div>
				<div >
				界面语言：<a>English</a> <a>简体中文</a> <a>繁体中文</a> <a>更多</a>
				</div>
				<div >
				常用译文语言：<a>English</a> <a>简体中文</a> <a>繁体中文</a> <a>更多</a>
				</div>
				<div >
				巴利脚本：<a>Roma</a> <a>sinhala</a> <a>mymar</a>
				</div>
			</div>
			<div class="help_fun_block">
				<div class="title" >外观</div>
				<div >
				颜色搭配：<a>静夜</a> <a>白色</a> <a>黄昏</a> <a>更多</a>
				</div>
			</div>
		</div>
	</div>       


	<div class="help_div">
		<div  class="htlp_title">
		统计
		<?php //echo $_local->gui->wikipali_suite;?>
		</div>
		<div>
			<div class="help_fun_block">
				<div class="title" >译经楼</div>
				<ul class="help_fun_block_link_list">
					<li style="display:block;">已发表的文集：2</li>
					<li style="display:block;">已发表的文章：12</li>
					<li style="display:block;">私有文档：234</li>
					<li style="display:block;">圣典百科：245</li>
				</ul>
			</div>
			<div class="help_fun_block">
				<div class="title" >藏经阁</div>
				<ul class="help_fun_block_link_list" style="display:unset;">
					<li style="display:block;">文集：2</li>
					<li style="display:block;">文章：12</li>
					<li style="display:block;">百科：245</li>
				</ul>
			</div>
			<div class="help_fun_block">
				<div class="title" >圣书院</div>
				<ul class="help_fun_block_link_list" >
					<li style="display:block;">创建课程：2</li>
					<li style="display:block;">主讲课程：12</li>
					<li style="display:block;">参与课程：245</li>
				</ul>
			</div>

		</div>
	</div>
		

</div>	
</div>

<script>
            function file_list(){
                let username=getCookie("username");
                if(username==""){
                    $("#file_list").html("登陆后显示文件列表");
                    return; 
                }
                var d=new Date();
               
                $.get("../studio/getfilelist.php",
                {
                    t:d.getTime(),
                    keyword:"",
                    status:"all",
                    orderby:"accese_time",
                    order:"DESC",
                    currLanguage:$("#id_language").val()
                },
                function(data,status){
                    var strFilelist="";
                    let count=5;
                    try{
                        let file_list = JSON.parse(data);
                        let html="";
                        
                        if(file_list.length<count){
                            count=file_list.length;
                        }
                        for(let x=0;x<count;x++){
                            if(file_list[x].doc_info && file_list[x].doc_info.length>1){
                                $link="<a href='../studio/editor.php?language=<?php echo $currLanguage;?>&op=opendb&fileid="+file_list[x].id+"' target='_blank'>[db]";
                            }
                            else{
                                $link="<a href='../studio/editor.php?language=<?php echo $currLanguage;?>&op=open&fileid="+file_list[x].id+"' target='_blank'>";
                            }

                            strFilelist += "<li>"+$link+file_list[x].title+"</a></li>";
                        }
                        if(file_list.length>count){
                            strFilelist += "<li><a href='studio/' targe='_blank'>更多</a></li>";
                        }
                        $("#file_list").html(strFilelist);
                    }
                    catch(e){
                        
                    }
                    
                    
                    
                });
            }
file_list();
</script>
		
	</div>
	
	<div class="foot_div">
	<?php echo $module_gui_str['editor']['1066'];?>
	</div>


	<style>
		#rs_doc_coop_win{
			min-height: 2em;
			width: 20em;
			position: absolute;
			background-color: var(--tool-bg-color1);
			padding: 8px;
			border-radius: 4px;
		}
		</style>
	<div id="rs_doc_coop_shell">
	<div id="rs_doc_coop_win" >
	<div id="rs_doc_coop_win_inner" >

	</div>
	<div id="rs_doc_coop_win_foot" >
		<button onclick="file_coop_win_close()">关闭</button>
	</div>
	</div>
	</div>





</body>
</html>

