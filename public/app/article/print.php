<?php
require_once __DIR__."/../config.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >
	<script>
		var gCaseTable=<?php echo file_get_contents("../public/js/case.json"); ?>
	</script>

	<script  src="./article.js"></script>

	<script>
	<?php
	$_id = "";
	$_display = "";
	$_channal  = "";
	$_collect = "";

	if(isset($_GET["view"])){
		echo "_view='".$_GET["view"]."';";
	}
	else{
		echo "_view='article';";
	}

	if(isset($_GET["collection"])){
		echo "_collection_id='".$_GET["collection"]."';";
	}

	if(isset($_GET["channel"])){
		echo "_channal='".$_GET["channel"]."';";
	}


	
	if(isset($_GET["mode"]) && $_GET["mode"]=="edit" && isset($_COOKIE["userid"])){
		#登录状态下 编辑模式
		$_mode = "edit";
		echo "_mode='edit';";
		$classMode="edit_mode";
	}
	else{
		$_mode = "read";
		echo "_mode='read';";
		$classMode="read_mode";
	}
	if(isset($_GET["display"])){
		if($_mode == "edit"){
			$_display = "sent";
			echo "_display='sent';";	
		}
		else{
			$_display = $_GET["display"];
			echo "_display='".$_GET["display"]."';";	
		}
	}
	else{
		if($_mode=="read"){
			$_display = "para";
			echo "_display='para';";
		}
		else{
			$_display = "sent";
			echo "_display='sent';";			
		}

	}	
	if(isset($_GET["direction"])){
		$_direction = $_GET["direction"];
		echo "_direction='".$_GET["direction"]."';";
	}
	else{
		if($_mode=="read"){
			$_direction = "row";
			echo "_direction='row';";
		}
		else{
			$_direction = "col";
			echo "_direction='col';";
		}
	}
	$contentClass= "";
	if($_direction=="row"){
		$contentClass .= ' horizontal ';
	}
	else{
		$contentClass .= ' vertical ';
	}
	if($_display=="para"){
		$contentClass .= ' para_mode ';
	}
	else{
		$contentClass .= ' sent_mode ';
	}
	$contentClass .= " $_mode ";
	
	?>
	</script>


<link type="text/css" rel="stylesheet" href="style.css"  />

<script src="../article/my_collect.js" type="text/javascript"></script>

<script src="../../node_modules/mustache/mustache.js"></script>

<style>
ul.fancytree-container{
	border:unset;
	width: max-content;
}
.fancytree-container .active {
    font-weight: 700;
    color: var(--main-color);
	background: linear-gradient(to right, var(--link-color), var(--nocolor));
    border-radius: 5px;
}
span.fancytree-title{
	color: var(--main-color1);
}
span.fancytree-node{
	display: flex;
}


#content_toc>ul>li>span.fancytree-node{
	font-size: 120%;
    font-weight: 900;
}
#article_path chapter{
	display:unset;
}

#contents ul, li {
    margin-block-start: 0.5em;
    margin-block-end: 0.5em;
    margin-left: 7px;
}
.click_dropdown_div{
	align-self: center;
}
.channel_select_button{
	color: var(--link-color);
	
}
.channel_select_button:hover{
	text-decoration-line: underline;
	
}
.sent_tran_div a{
	white-space: normal;
	overflow-wrap: anywhere;
}
.para_tran_div a{
	white-space: normal;
	overflow-wrap: anywhere;
}

</style>



<div id="main_view" class="main_view <?php echo $classMode;?>">

</div>



<script>

$(document).ready(function(){
	note_create();
	historay_init();
    collection_load(_collection_id);
});

</script>

</body>
</html>
