<?PHP
require_once "../pcdl/html_head.php";
?>

<body>
	<a name="toc_root"></a>
<?php
if (!(isset($_GET["builtin"]) && $_GET["builtin"] == 'true')) {
    require_once "../pcdl/head_bar.php";
}
?>
	<script language="javascript" src="./dict.js"></script>

	<link type="text/css" rel="stylesheet" href="./css/style.css" >
	<link type="text/css" rel="stylesheet" href="./css/style_mobile.css" media="screen and (max-width:800px)">
<style>
guide.grammar_tag {
    display: unset;
    background: unset;
    /*color: unset;*/
    background-color: var(--btn-color);
    padding: 0;
    border-radius: 3px;
	margin: 0;

}
guide.grammar_tag:hover {
	/*color: unset;*/
	text-decoration: underline;
}
.dict_word:hover  guide.grammar_tag {
	/*text-decoration: underline;*/
}
</style>

	<!-- tool bar begin-->
	<div id='search_toolbar' class="search_toolbar">
		<div class='section_inner' style="display:flex;justify-content: space-between;">
			<div id="left_menu_button">
				<button id="left_toc" type="button" class="icon_btn" onclick="setNaviVisibility('table_of_content')" title="Dict List">
				<svg class="icon">
					<use xlink:href="./icon.svg#icon_toc"></use>
				</svg>
				<span class="icon_notify" id="icon_notify_table_of_content">
				</span>
				</button>
			</div>
			<div style="flex:6;">
				<div style="display:flex;">
					<div style="align-self: center;">
						<guide gid="dict_search_input"></guide>
					</div>
					<div style="width:90%;">
						<input id="dict_ref_search_input" type="text" autocomplete="off" placeholder="<?php echo $_local->gui->dict_searching_placehold; ?> " onkeyup="dict_input_keyup(event,this)" style="" onfocus="dict_input_onfocus()" />
					</div>
				</div>

				<div id="pre_search_result" >
					<div id="pre_search_word" class="pre_serach_block">
						<div id="pre_search_word_title" class="pre_serach_block_title">
							<div id="pre_search_word_title_left">
							<?php #echo $_local->gui->vocabulary_list; ?>
							</div>
							<div id="pre_search_word_title_right"></div>
						</div>
						<div id="pre_search_word_content" class="pre_serach_content">
						</div>
					</div>
				</div>
			</div>

			<span style="display:flex;">
			<!--
				<button id="trubo_split" onclick="trubo_split()" >
					<?php echo $_local->gui->turbo_split; /*强力拆分*/ ?>
				</button>
				<guide gid="comp_split"></guide>
				-->
			</span>
			<div></div>
		</div>
		<div id="manual_split"></div>
		<div id="result_msg"></div>
		<div style="display:block;z-index: 5;text-align:center;">

		</div>
	</div>
	<!--tool bar end -->

	<!-- tool bar fixed begin-->
	<div id='search_toolbar_1' class="search_toolbar search_fixed">
		<div style="display:flex;">
			<span>
				<?php echo $_local->gui->dictionary; ?>
			</span>
			<div>
				<div>
					<input id="dict_ref_search_input_1" type="input" placeholder="<?php echo $_local->gui->search; ?>" onkeyup="dict_input_keyup(event,this)" style="margin-left: 0.5em;width: 40em;max-width: 80%;font-size:140%;padding: 0.3em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="dict_input_onfocus()">
				</div>
				<div id="pre_search_result_1" style="position: absolute;max-width: 100%; width: 50em;background-color: var(--btn-color);z-index: 51;display: none;">
					<div class="pre_serach_block">
						<div class="pre_serach_block_title">
							<div id="pre_search_word_title_left_1"><?php echo $_local->gui->vocabulary_list; ?></div>
							<div id="pre_search_word_title_right_1"></div>
						</div>
						<div id="pre_search_word_content_1" class="pre_serach_content">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="display:block;z-index: 5;">
		</div>
	</div>
	<!--tool bar fixed end -->
	<div id="tool_btn" class="right_tool_btn" style="display: none;">
		<button style="border: 2px solid var(--nocolor); background-color: unset;"><a href="#toc_root">
				<svg t="1596888255722" class="icon" style="height: 3em; width: 3em;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4368" width="200" height="200">
					<path d="M510.4 514.2m-449.2 0a449.2 449.2 0 1 0 898.4 0 449.2 449.2 0 1 0-898.4 0Z" fill="#8a8a8a" p-id="4369"></path>
					<path d="M719.4 314.5h-416c-8.5 0-15.4-6.9-15.4-15.4v-4.8c0-8.5 6.9-15.4 15.4-15.4h416.1c8.5 0 15.4 6.9 15.4 15.4v4.8c-0.1 8.4-7 15.4-15.5 15.4z" fill="#ffffff" p-id="4370"></path>
					<path d="M494.1 735.1v-416c0-8.5 6.9-15.4 15.4-15.4h4.8c8.5 0 15.4 6.9 15.4 15.4v416.1c0 8.5-6.9 15.4-15.4 15.4h-4.8c-8.4-0.1-15.4-7-15.4-15.5z" fill="#ffffff" p-id="4371"></path>
					<path d="M672.5 503.1l-165-165c-6-6-6-15.8 0-21.7l3.4-3.4c6-6 15.8-6 21.7 0l165 165c6 6 6 15.8 0 21.7l-3.4 3.4c-6 6-15.7 6-21.7 0z" fill="#ffffff" p-id="4372"></path>
					<path d="M325.2 478l165-165c6-6 15.8-6 21.7 0l3.4 3.4c6 6 6 15.8 0 21.7l-165 165c-6 6-15.8 6-21.7 0l-3.4-3.4c-5.9-6-5.9-15.7 0-21.7z" fill="#ffffff" p-id="4373"></path>
				</svg>
			</a>
		</button>
	</div>

	<div style="position: fixed;max-height: calc(100vh - 89px - 3.6em);width:100vw;overflow: scroll;">
		<div id="main_view" class='section_inner' >
			<div id='dict_list_shell' style="display:none" onclick='setNaviVisibility()'>
				<div id='dict_list' class='dict_list_off'></div>
			</div>
			<div id="main_result">
				<div id="search_info">
					<div id='search_result_shell'></div>
					<div id="word_parts">
						<div id="input_parts" style="font-size: 1.1em;padding: 2px 1em;"></div>
					</div>
				</div>
				
				<div id="part_mean_shell">
					<div id="part_mean"></div>
				</div>
				<div id="dict_search_result"></div>
			</div>
			<div id="right_bar"></div>
		</div>
	</div>
	<script>	
		$(document).ready(function() {
			$("#nav_dict").addClass('active');
		});
<?php
if (isset($_GET["key"]) && !empty($_GET["key"])) {
    echo "var _key='{$_GET["key"]}';\n";
    echo "search_on_load(\"{$_GET["key"]}\")";
}
?>

//window.addEventListener('scroll', winScroll);

function winScroll(e) {
	if (GetPageScroll().y > 150) {
		$("#search_toolbar_1").css("top", 0);
	} else {
		$("#search_toolbar_1").css("top", GetPageScroll().y - 150);
	}
	if (GetPageScroll().y > $(window).height() * 0.9) {
		$("#tool_btn").show();
	} else {
		$("#tool_btn").hide();
	}

}
//滚动条位置
function GetPageScroll() {
	var pos = new Object();
	var x, y;
	if (window.pageYOffset) { // all except IE
		y = window.pageYOffset;
		x = window.pageXOffset;
	} else if (document.documentElement && document.documentElement.scrollTop) { // IE 6 Strict
		y = document.documentElement.scrollTop;
		x = document.documentElement.scrollLeft;
	} else if (document.body) { // all other IE
		y = document.body.scrollTop;
		x = document.body.scrollLeft;
	}
	pos.x = x;
	pos.y = y;
	return (pos);
}

ntf_init(1);
</script>

	<?php
include "../pcdl/html_foot.php";
?>