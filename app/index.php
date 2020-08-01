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
        <meta http-equiv="refresh" content="0,pcdl/"/>


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
	</head>
	<body id="ucenter_body" >
        <div id="tool_bar">

        </div>	

        <span style="background-color: silver;color: black;font-size: large;width: fit-content;align-self: center;">
            <?php echo $_local->gui->test_declare;?>
        </span>
        
        <div id="login_body" >
            正在跳转到<a href="pcdl/">主页</a>
        </div>

        <script>

        </script>

	</body>
</html>