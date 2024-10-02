<style>
	body {
		margin: unset;
	}

	.index_toolbar {
		position: unset;
	}

	.lab_tab {

	}

	.lab_tab>li {
		padding: 5px;
	}

	.search_toolbar {

		height: initial;
		padding: 0.7em 1em 0 1em;
		;
		background-color: var(--tool-bg-color1);
		border-bottom: none;
		color: var(--tool-color1);
		/*display: flex;*/
	}

	.search_fixed {
		position: fixed;
		top: -500px;
		width: 100%;
		display: flex;
		padding: 0.5em 1em;
	}

	ul#dict_type li {
		margin-right: 1em;
	}

	ul#dict_type a {
		color: var(--main-color);
	}

	ul#dict_type li:hover {
		background-color: var(--link-color);
		color: var(--btn-hover-color);
	}

	ul#dict_type li:hover a {
		color: var(--btn-hover-color);
	}

	.dict_word {
		display: block;
		border: none;
		border-bottom: 1px solid var(--tool-line-color);
		border-radius: 0;
		margin: 6px 0;
		padding: 5px;
	}
</style>
<!-- tool bar begin-->
<div id='search_toolbar' class="search_toolbar">
	
	<div style="display:flex;">
		<span>
			<svg class="small_icon" style=" width: 3em;height: 3em;">
				<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="../studio/svg/icon.svg#ic_search"></use>
			</svg>

		</span>
		<div class="case_dropdown">
			<div>
				<input id="dict_ref_search_input" type="input" placeholder="<?php echo $_local->gui->search; ?>" onkeyup="search_input_keyup(event,this)" style="width: 40em;max-width: 80%;font-size:140%;padding: 0.6em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="search_input_onfocus()">
			</div>
			<div id="pre_search_word_content" class="case_dropdown-content"></div>
		</div>
	</div>
	<div style="display:block;z-index: 5;">
		<ul id="dict_type" class="lab_tab" style="color:black;display: flex;">
			<?php
			if (isset($_GET["key"])) {
				$key = "?key=" . $_GET["key"];
			} else {
				$key = "";
			}
			?>
			<li id="dt_all" style="display:none;">
				<a href="../search/index.php<?php echo $key; ?>">
				<span><?php echo $_local->gui->all; /*全部*/?></span>
				<span id="search_all_num"></span>
				</a>
			</li>
			<li id="dt_pali">
				<a href="../search/paliword.php<?php echo $key; ?>">
				<span><?php echo $_local->gui->full_text; /*巴利原文*/?></span>
				<span id="search_palitext_num"></span>
				</a>
			</li>
			<li id="dt_title">
				<a href="../search/title.php<?php echo $key; ?>">
				<span><?php echo $_local->gui->title; //标题?></span>
				<span id="search_title_num"></span>
				</a>
			</li>

		</ul>
	</div>
</div>
<!--tool bar end -->

<!-- tool bar fixed begin-->
<div id='search_toolbar_1' class="search_toolbar search_fixed">
	<div style="display:flex;">
		<span>
			<?php echo $_local->gui->search; ?>
		</span>
		<div class="case_dropdown">
			<div>
				<input id="dict_ref_search_input_1" type="input" placeholder="<?php echo $_local->gui->search; ?>" onkeyup="search_input_keyup(event,this)" style="margin-left: 0.5em;min-width: 40vw;max-width: 80%;font-size:120%;padding: 0.2em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="search_input_onfocus()">
			</div>
			<div id="pre_search_word_content_1" class="case_dropdown-content"></div>
		</div>
	</div>
	<div style="display:block;z-index: 5;">
		<ul id="dict_type" class="lab_tab" style="color:black;">
			<?php
			if (isset($_GET["key"])) {
				$key = "?key=" . $_GET["key"];
			} else {
				$key = "";
			}
			?>
			<li id="dt_all_1" style="display:none;"><a href="../search/index.php<?php echo $key; ?>"><span>全部</span><span id="search_all_num_1"></span></a></li>
			<li id="dt_title_1"><a href="../search/title.php<?php echo $key; ?>"><span>标题</span><span id="search_title_num_1"></span></a></li>
			<li id="dt_pali_1"><a href="../search/paliword.php<?php echo $key; ?>"><span>巴利原文</span><span id="search_palitext_num_1"></span></a></li>
			<li id="dt_bold_1"><a href="../search/bold.php<?php echo $key; ?>"><span><?php echo $_local->gui->vannana; ?></span><span id="search_bold_num_1"></span></a></li>
			<li id="dt_trans_1" style="display:none;"><a href="../search/trans.php<?php echo $key; ?>"><span><?php echo $_local->gui->translate; ?></span><span id="search_trans_num_1"></span></a></li>
		</ul>
	</div>
</div>
<!--tool bar fixed end -->

<script>
	window.addEventListener('scroll', winScroll);

	function winScroll(e) {
		if (GetPageScroll().y > 150) {
			$("#search_toolbar_1").css("top", 0);
		} else {
			$("#search_toolbar_1").css("top", GetPageScroll().y - 150);
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
</script>