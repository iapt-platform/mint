<?php
require_once "../public/load_lang.php";
require_once "../config.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >
	<script>
		var gCaseTable=<?php echo file_get_contents("../public/js/case.json"); ?>
	</script>

	<script  src="./article.js"></script>
	<script  src="./term.js"></script>

	<script src="../widget/like.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/like.css"/>
	<script src="../palicanon/chapter_channel.js"></script>
	<link type="text/css" rel="stylesheet" href="../palicanon/chapter_channel.css"/>

	<script src="../widget/click_dropdown.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/click_dropdown.css"/>

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

	if(isset($_GET["id"])){
		echo "_articel_id='".$_GET["id"]."';";
		echo "_id='".$_GET["id"]."';";
	}
	if(isset($_GET["collect"])){
		echo "_collection_id='".$_GET["collect"]."';";
	}
	if(isset($_GET["collection"])){
		echo "_collection_id='".$_GET["collection"]."';";
	}

	if(isset($_GET["channal"])){
		echo "_channal='".$_GET["channal"]."';";
	}
	if(isset($_GET["channel"])){
		echo "_channal='".$_GET["channel"]."';";
	}
	if(isset($_GET["lang"])){
		echo "_lang='".$_GET["lang"]."';";
	}
	if(isset($_GET["author"])){
		echo "_author='".$_GET["author"]."';";
	}

	if(isset($_GET["book"])){
		echo "_book=".$_GET["book"].";";
	}
	if(isset($_GET["par"])){
		echo "_par=".$_GET["par"].";";
	}
	if(isset($_GET["para"])){
		echo "_par=".$_GET["para"].";";
	}
	if(isset($_GET["start"])){
		echo "_start=".$_GET["start"].";";
	}
	if(isset($_GET["begin"])){
		echo "_start=".$_GET["begin"].";";
	}
	if(isset($_GET["end"])){
		echo "_end=".$_GET["end"].";";
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
<link type="text/css" rel="stylesheet" href="pad.css" media="screen and (max-width:1280px)" />
<link type="text/css" rel="stylesheet" href="mobile.css" media="screen and (max-width:800px)" />
<link type="text/css" rel="stylesheet" href="print.css" media="print" />

<link href="../../node_modules/jquery.fancytree/dist/skin-win7/ui.fancytree.css" rel="stylesheet" type="text/css" class="skinswitcher">
<script src="../tree/jquery.fancytree.js" type="text/javascript"></script>
<script src="../article/my_collect.js" type="text/javascript"></script>
<script src="../article/article_add_dlg.js"></script>
<script src="../../node_modules/mustache/mustache.js"></script>

<?php
    require_once("../pcdl/head_bar.php");
?>
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

.float_dlg {
	display: none;
	width: 25em;
	position: absolute;
	right: 0;
	background-color: var(--btn-hover-bg-color);
	padding: 10px;
	border-radius: 5px;
	box-shadow: 0 0 10px var(--main-color);
	z-index: 1;
}

.float_dlg_left {
	display: none;
	width: 25em;
	position: absolute;
	background-color: var(--btn-hover-bg-color);
	padding: 10px;
	border-radius: 5px;
	box-shadow: 0 0 10px var(--main-color);
	z-index: 200;
}

#contents ul, li {
    margin-block-start: 0.5em;
    margin-block-end: 0.5em;
    margin-left: 18px;
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
.show_pc{
	color: var(--main-color);
}

</style>


<script>
function show_content(){
	$("#left_pannal").toggleClass("hidden");
	if($("#left_pannal").hasClass("hidden")){
		$("#main_view").removeClass("main_view_narrow");
		$(".fun_frame").removeClass("fun_frame_narrow");
		$("#contents_div").removeClass("contents_div_narrow");
		//$("#main_view").css("margin-left","0");
		//$(".fun_frame").css("width","30vw");
		//$("#contents_div").css("width","calc(70vw - 2em)");
		$("#left_pannal_inner").css("width","unset");
		localStorage.setItem('article_show_toc_'+_mode, 'hide');
	}else{
		$("#main_view").toggleClass("main_view_narrow");
		$(".fun_frame").toggleClass("fun_frame_narrow");
		$("#contents_div").toggleClass("contents_div_narrow");
		//$("#main_view").css("margin-left","20vw");
		//$(".fun_frame").css("width","20vw");
		//$("#contents_div").css("width","calc(60vw - 2em)");
		$("#left_pannal_inner").css("width","unset");
		localStorage.setItem('article_show_toc_'+_mode, 'show');
	}
}
function set_toc_visible(isVisible){
	if(isVisible){
		$("#left_pannal").removeClass("hidden");
		$("#main_view").toggleClass("main_view_narrow");
		//$("#main_view").css("margin-left","20vw");
		$(".fun_frame").toggleClass("fun_frame_narrow");
		//$(".fun_frame").css("width","20vw");
		$("#contents_div").toggleClass("contents_div_narrow");
		//$("#contents_div").css("width","calc(60vw - 2em)");
		$("#left_pannal_inner").css("width","unset");
		localStorage.setItem('article_show_toc_'+_mode, 'show');
	}else{
		$("#left_pannal").addClass("hidden");
		$("#main_view").removeClass("main_view_narrow");
		//$("#main_view").css("margin-left","0");
		$(".fun_frame").removeClass("fun_frame_narrow");
		//$(".fun_frame").css("width","30vw");
		$("#contents_div").removeClass("contents_div_narrow");
		//$("#contents_div").css("width","calc(70vw - 2em)");
		$("#left_pannal_inner").css("width","unset");
		localStorage.setItem('article_show_toc_'+_mode, 'hide');
	}
}

</script>
<div id="head_bar" >
	<div style="display:flex;">
	<button class="icon_btn" onclick="show_content()" style="color: var(--main-color);"><?php echo $_local->gui->contents; ?></button>
	</div>

	<div style="margin: auto 0;">
		<span id="head_span">
		<?php
        $link = '/pcd/article/'.$_GET["view"].'/';
        if(isset($_GET["channel"])){
            $channel = str_replace(',','_',$_GET["channel"]);
        }else if(isset($_GET["channal"])){
            $channel = str_replace(',','_',$_GET["channal"]);
        }
        switch ($_GET["view"]) {
            case 'chapter':
            case 'para':
                $link .= $_GET["book"].'-'.$_GET["par"].'?book='.$_GET["book"].'&par='.$_GET["par"];
                break;
            case 'article':
                $link .= $_GET["id"];
                if(isset($_GET['collection'])){
                    $link .= '?anthology='.$_GET["collection"];
                }
                break;
            default:
                # code...
                break;
        }
        if(isset($channel)){
            $link .='&channel='.$channel;
        }
        echo "<a href='{$link}' target='_blank'>在新版中打开</a>";
		if(isset($_GET["view"]) && $_GET["view"]=="article"){
			echo "<button class='icon_btn show_pc'  title='{$_local->gui->modify} {$_local->gui->composition_structure}'>";
			echo "<a href='../article/my_article_edit.php?id=".$_GET["id"];
			echo "' target='_blank'>{$_local->gui->modify}</a></button>";
		}
		if($_GET["view"]!=="article" && $_GET["view"]!=="collection"){
?>
	<span id="convert_article" class="icon_btn_div show_pc">

		<button id="file_add" type="button" class="icon_btn" onclick="to_article()" title=" ">
			<svg class="icon">
				<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
			</svg>
			转换为文章
		</button>
		<div id='article_add_div' class="float_dlg"></div>
	</span>
<?php
		}
			echo "<button class='icon_btn show_pc'  title='{$_local->gui->add}{$_local->gui->subfield}'>";
			echo "<a href='../article/frame.php?view=".$_GET["view"];
			if(isset($_GET["id"])){
				echo "&id=".$_GET["id"];
			}
			if(isset($_GET["collection"])){
				echo "&collection=".$_GET["collection"];
			}
			if(isset($_GET["channel"])){
				echo "&channel=".$_GET["channel"];
			}
			if(isset($_GET["lang"])){
				echo "&lang=".$_GET["lang"];
			}
			if(isset($_GET["book"])){
				echo "&book=".$_GET["book"];
			}
			if(isset($_GET["par"])){
				echo "&par=".$_GET["par"];
			}
			if(isset($_GET["start"])){
				echo "&start=".$_GET["start"];
			}
			if(isset($_GET["end"])){
				echo "&end=".$_GET["end"];
			}
			echo "'>{$_local->gui->add}{$_local->gui->subfield}</a></button>";



		?>
		<button id="btn_show_channel" class="icon_btn show_pc" onclick='show_channel_detail_pannal()'>
			<?php echo $_local->gui->contributor.$_local->gui->white_space.$_local->gui->list; ?>
		</button>
		<span>
			<?php include "../reader/right_tool_bar.php";?>
		</span>
		</span>
	</div>
</div>
<div id="left_pannal">
	<div id="left_pannal_inner" class="fun_frame" style="z-index: 99;">
		<!--<div id = "collect_title" class="title" style="text-align: right;background: #ffd70087;" onclick="show_content(this)"></div>-->
		<div id = "toc_content" class="content" style="padding-top:0;">
		</div>
	</div>
</div>

<div id="main_view" class="main_view <?php echo $classMode;?>">
	<div id="article_head" style="border-bottom: 1px solid gray;">
		<div id="head_nav" >
			<div id="head_nav_left" >
				<div id="article_path" class=""></div>
				<div id="article_path_title"></div>
			</div>
			<div id="head_nav_right" >
				<select id="select_lang" onchange="lang_changed(this)">
						<option>全部语言</option>
						<option><?php echo $_local->language->zh_cn; ?></option>
						<option><?php echo $_local->language->zh_tw; ?></option>
						<option><?php echo $_local->language->en; ?></option>
						<option><?php echo $_local->language->si; ?></option>
						<option><?php echo $_local->language->my; ?></option>
				</select>
				<div id="article_edition" style="display:flex;">
					<span  style='font-weight: 700;'>
						<?php echo $_local->gui->channels; ?>
					</span>
					<div id="edition_dropdown" class="case_dropdown">
						<span></span>
					</div>
				</div>
			</div>
		</div>
		<div id="article_title" class="term_word_head_pali"></div>
		<div id="article_subtitle"></div>
		<div id="article_author"></div>
		<div id="like_div">
			<like restype='article' resid='124'></like>
			<watch restype='article' resid='124'></watch>
		</div>
	</div>

	<div id="contents_view">
		<div id="contents_div" class="contents_div">
			<div id="summary"></div>
			<div id="contents" class="content_inner <?php echo $contentClass;?>">
				<?php echo $_local->gui->loading; ?>...
			</div>
            <div id="glossary"></div>
			<div id="contents_foot">
				<div id="contents_nav" style="display:flex;justify-content: space-between;">
					<div id="contents_nav_left" class="nav_bnt nav_left" onclick="goto_prev()">
					<svg class='icon' style='fill: var(--box-bg-color1)'>
						<use xlink:href='../../node_modules/bootstrap-icons/bootstrap-icons.svg#chevron-left'>
					</svg>
					<span id="contents_nav_left_inner" ></span>
					</div>
					<div id="contents_nav_right"  class="nav_bnt nav_right" onclick="goto_next()">
					<span id="contents_nav_right_inner" ></span>
					<svg class='icon' style='fill: var(--box-bg-color1)'>
						<use xlink:href='../../node_modules/bootstrap-icons/bootstrap-icons.svg#chevron-right'>
					</svg>

					</div>
				</div>
				<div id="contents_dicuse">

				</div>
			</div>
		</div>
		<div id="right_pannal" style="max-width: unset;">
			<div class="fun_frame" style="position: fixed;width: auto;height: calc(100vh - 250px);display: grid;">
				<div style="display:flex;justify-content: space-between;">
					<div class="title"><?php echo $_local->gui->contributor; ?></div>
					<div class="click_dropdown_div">
						<div class="channel_select_button" style="color: var(--link-color);" onclick="onChannelMultiSelectStart()"><?php echo $_local->gui->select.$_local->gui->space.$_local->gui->more.$_local->gui->space.$_local->gui->channel."⬇"; ?></div>
					</div>
				</div>
				<div class='channel_select'>
					<button onclick='onChannelChange()'><?php echo $_local->gui->confirm; ?></button>
					<button onclick='onChannelMultiSelectCancel()'><?php echo $_local->gui->cancel; ?></button>
				</div>
				<div id="channal_list" class="content" style="max-height:calc(100vh - 300px);">
				</div>
			</div>
		</div>
	</div>
</div>



<script>
	$(document).ready(function(){
		if(window.innerWidth>800){
			if(localStorage.getItem('article_show_toc_'+_mode)=="hide"){
				set_toc_visible(false);
			}else{
				set_toc_visible(true);
			}
		}else{
			set_toc_visible(false);
		}


		article_add_dlg_init("article_add_div");
	ntf_init();
	click_dropdown_init();
	note_create();
	historay_init();
    chapter_dynamic_init();
	switch (_view) {
		case "article":
			articel_load(_articel_id,_collection_id);
			if(_collection_id!=""){
				articel_load_article_list(_articel_id,_collection_id);
			}
			break;
		case "collection":
			collect_load(_collection_id);
		break;
		case "sent":
		case "para":
		case "chapter":
		case "book":
		case "series":
			palicanon_load();
			reader_get_path();
			render_toc();
		break;
		case "simsent":
		case "sim":
			palicanon_load();
			break;
        case 'term':
            term_load();
            break;
		default:
			break;
	}
    if(_view=='chapter'){
        //获取章节的channel列表
        loadChapterChannel({
            book:_book,
            para:_par,
            target:$("#article_author"),
            showchannel:_channal.split(',')
        });
    }
	});

	 window.addEventListener('scroll',winScroll);
	function winScroll(e){
		if(GetPageScroll().y>220){

		}
		else{

		}

	}
 //滚动条位置
function GetPageScroll()
{
	var pos=new Object();
	var x, y;
	if(window.pageYOffset)
	{	// all except IE
		y = window.pageYOffset;
		x = window.pageXOffset;
	} else if(document.documentElement && document.documentElement.scrollTop)
	{	// IE 6 Strict
		y = document.documentElement.scrollTop;
		x = document.documentElement.scrollLeft;
	} else if(document.body) {	// all other IE
		y = document.body.scrollTop;
		x = document.body.scrollLeft;
	}
	pos.x=x;
	pos.y=y;
	return(pos);
}
	</script>

<div class="modal_win_bg">
</div>
<div id="model_win" class="model_win_container"></div>

</body>
</html>
