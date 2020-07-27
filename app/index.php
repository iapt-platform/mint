<?php
require_once "path.php";
require_once "public/load_lang.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="text/css" rel="stylesheet" href="studio/css/font.css"/>
		<link type="text/css" rel="stylesheet" href="studio/css/style.css"/>
		<link type="text/css" rel="stylesheet" href="studio/css/color_day.css" id="colorchange" />
		<title>wikipali</title>
		<script src="public/js/comm.js"></script>
		<script src="studio/js/jquery-3.3.1.min.js"></script>
		<script src="studio/js/fixedsticky.js"></script>

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

            function file_list(){
                let username=getCookie("username");
                if(username==""){
                    $("#file_list").html("登陆后显示文件列表");
                    return; 
                }
                var d=new Date();
               
                $.get("studio/getfilelist.php",
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
                                $link="<a href='studio/editor.php?language=<?php echo $currLanguage;?>&op=opendb&fileid="+file_list[x].id+"' target='_blank'>[db]";
                            }
                            else{
                                $link="<a href='studio/editor.php?language=<?php echo $currLanguage;?>&op=open&fileid="+file_list[x].id+"' target='_blank'>";
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

		</script>
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
	</head>
	<body id="ucenter_body" onload="login_init()">
        <div id="tool_bar">
            <div>
                <svg style="height: 5em;">
                    <use xlink:href="./public/images/svg/wikipali_without_studio.svg#wikipali_without_studio"></use>
                </svg>
            </div>
            <div id="login_state" style="margin-left:auto; margin-right:1em;">
                <a href="./ucenter/index.php?language=<?php echo $currLanguage;?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_local->gui->login;//登入账户?></a>
                <a href="./ucenter/index.php?language=<?php echo $currLanguage;?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_local->gui->create_account;//建立账户?></a>
            </div>

            <div>
            <?php
            require_once 'lang/lang.php';
            ?>
            </div>
        </div>	
        <span style="background-color: silver;color: black;font-size: large;width: fit-content;align-self: center;">
            <?php echo $_local->gui->test_declare;?>
        </span>
        
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
                        <li><a href="studio/index.php" target="_block"><?php echo $_local->gui->newproject;?></a></li>
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

                <div class="help_div" style="display:none;">
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
                    <div  class="htlp_title">wikipali Libray</div>
                    <div>
                        <div class="help_fun_block">
                            <div class="title" ><a href=""  target="pcd_studio"><a href="https://www.youtube.com/playlist?list=PL_1iJBQvNPFHwP1ZL4sbhtJTnYeMiEm29" target="_blank"><?php echo $_local->gui->classroom;?></a></a></div>
                            <div >
                            巴利语  经藏  律藏  阿毗达摩藏
                            </div>
                        </div>
                        <div class="help_fun_block">
                            <div class="title" ><a href="pcdl/index.php?language=<?php echo $currLanguage;?>"  target="pcd_lib">分类（建設中）</a></div>
                            <div >
                            经藏  律藏  阿毗达摩藏 其他典籍
                            </div>
                        </div>
                        <div class="help_fun_block">
                            <div class="title" ><a href="search/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->search;?></a></div>
                            <div >
                            <a href="search/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->txt_search;?></a>
                            <br>
                            <a href="pcdl/index1.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->title_search;?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="help_div">
                    <div  class="htlp_title">
                    <?php echo $_local->gui->wikipali_suite;?>
                        <a href="" target="_blank">
                            
                        </a>
                    </div>
                    <div>
                        <div class="help_fun_block">
                            <div class="title" ><?php echo $_local->gui->encyclopedia;?></div>
                            <ul class="help_fun_block_link_list">
                                <li><a href="wiki/index.php" target="_blank">首页(建設中)</a></li>
                                <br>
                                <li><a href="wiki/index.php" target="_blank">最新(建設中)</a></li>
                            </ul>
                        </div>
                        <div class="help_fun_block">
                            <div class="title" ><a href="dict/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->dictionary;?></a></div>
                        </div>
                        <div class="help_fun_block">
                            <div class="title" ><?php echo $_local->gui->tools;?></div>
                            <ul >
                                <li><a href="calendar/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->BE;?></a></li>
                                <li><a href="statistics/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->statistical_data;?></a></li>
                                <li><a href="paliscript/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->code_convert;?></a></li>
                                <li><a href="dict_builder/index.php?language=<?php echo $currLanguage;?>" target="_blank"><?php echo $_local->gui->dict_builder;?></a></li>
                            </ul>
                        </div>                        
                    </div>
                </div>
                    

            </div>	
        </div>

        <script>
        login_init();
        file_list();
        </script>

	</body>
</html>