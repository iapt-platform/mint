<script>



</script>
<style>
	:root {
		--new-tool-bg: #333333;
		--new-tool-content-bg: #4D4D4D;
		--new-tool-list-hover-bg: #7B7B7B;
		--new-tool-btn-border: #7B7B7B;
		--new-tool-btn: #CDCDCD;
		--new-tool-content-disabled: #989898;
	}

	*,
	*::before,
	*::after {
		box-sizing: border-box;
	}

	body {
		padding-top: 50px;
	}

	nav a,
	nav a:link,
	nav a:visited {
		color: unset;

	}

	header ul,
	header li {
		white-space: nowrap;
	}

	header {
		background: var(--new-tool-bg);
		position: fixed;
		text-align: center;
		height: 50px;
		z-index: 900;
		width: 100%;
		top: 0;
	}

	header label {
		cursor: pointer;
	}

	.head-logo {
		height: 50px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.head-logo svg {
		height: 50px;
		width: 100px;
	}

	.platform-toggle {
		position: relative;
		border-radius: 3px;
		color: white;
		white-space: nowrap;
		font-weight: 200;
		font-size: 17px;
		transition: all ease 300ms;
	}

	.platform-toggle::before {
		content: '<?php echo $_local->gui->library; ?>';
	}

	.platform-toggle a {
		color: white;
		transition: color ease 300ms;
		display: none;
	}

	.goto-platform {
		display: inline-flex;
		color: white;
		transform: scaleX(0);
		opacity: 0;
		transform-origin: top left;
		transition: all ease 250ms;
		margin: 0;
		padding: 0;
	}

	.platform-toggle::after {
		content: '';
		background-image: linear-gradient(to right, var(--new-tool-bg), var(--new-tool-btn));
		height: 1px;
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		transform: scaleX(0);
		transform-origin: left;
		transition: transform ease 300ms;
	}



	nav {
		position: absolute;
		text-align: left;
		top: 100%;
		left: 0;
		background: var(--new-tool-bg);
		width: 100%;
		transform: scale(1, 0);
		transform-origin: top;
		transition: transform 350ms ease-in-out;
		border-top: 1px solid black;
	}

	nav ul {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	nav li {
		margin: 1rem;
		line-height: 50px;
	}

	.icon_btn {
		color: var(--new-tool-btn-border);
		padding: 0.3em 0.3em;
		border: 1px solid var(--nocolor);
		border-radius: 3px;
		margin: 0 2px;
	}

	/*
	.icon_btn:hover {
		padding: 0.3em 0.3em;
		background-color: var(--new-tool-list-hover-bg);
		border: 1px solid var(--new-tool-btn-border);
		border-radius: 3px;
		margin: 0 2px;
	}*/

	.nav-toggle {
		display: none;
	}

	.nav-toggle-label {
		position: absolute;
		top: 0;
		left: 0;
		margin-left: 1rem;
		height: 100%;
		display: flex;
		align-items: center;
	}

	.nav-toggle-label span,
	.nav-toggle-label span::before,
	.nav-toggle-label span::after {
		display: block;
		background: var(--new-tool-btn);
		height: 2px;
		width: 20px;
		position: relative;
	}

	.nav-toggle-label span::before,
	.nav-toggle-label span::after {
		content: '';
		position: absolute;
	}

	.nav-toggle-label span::before {
		bottom: 7px;
	}

	.nav-toggle-label span::after {
		top: 7px;
	}

	.nav-toggle:checked~nav {
		transform: scale(1, 1);
	}

	.nav-toggle:checked~.nav-toggle-label span,
	.nav-toggle:checked~.nav-toggle-label span::before,
	.nav-toggle:checked~.nav-toggle-label span::after {
		background-color: white;
	}


	.nav-toggle:checked~nav .nav_link {
		opacity: 1;
		transition: opacity 250ms ease-in-out 250ms;
	}

	.nav_link {
		color: var(--new-tool-btn);
		font-size: 14px;
		opacity: 0;
		transition: opacity 150ms ease-in-out;
	}

	.nav_link a:hover {
		color: var(--tool-color);
	}

	.nav-right {
		position: absolute;
		align-items: center;
		top: 0;
		right: 0;
		margin-right: 1em;
		height: 100%;
		display: flex;
		text-align: left;
	}

	.header-dropdown-content {
		border-radius: 3px;
		display: none;
		position: absolute;
		background-color: var(--new-tool-content-bg);
		min-width: 60px;
		z-index: 910;
		top: calc(100% + 6px);
	}

	.header-dropdown-content::after {
		content: " ";
		position: absolute;
		bottom: 100%;
		border: 6px solid transparent;
		border-bottom-color: var(--new-tool-content-bg);
	}

	.header-dropdown-content::before {
		content: '';
		position: absolute;
		bottom: 100%;
		width: 100%;
		border: 6px solid transparent;
	}

	.header-dropdown-content li:hover {
		background-color: var(--new-tool-list-hover-bg);
		border-radius: 3px;
	}

	.header-dropdown-content li:hover .nav_link {
		color: var(--tool-color);
	}

	.header-dropdown-content li:hover .disable {
		color: var(--new-tool-content-disabled);
	}

	.right-content {
		right: 0;
		box-shadow: 0px 3px 10px 0px var(--shadow-color);
	}

	.right-content::after {
		right: 12px;
	}

	.right-content li div {
		padding: 0.7rem;
		opacity: 1;
	}

	.left-content {
		display: block;
	}

	/* 容器 <div> - 需要定位下拉内容 */
	.header-dropdown {
		position: relative;
		display: inline-flex;
	}

	#more .dropdown-content {
		right: unset;
	}

	#more .dropdown-content::after {
		all: unset;
	}

	.nav-mobile {
		display: block;
	}

	.more_btn {
		display: none;
	}

	.icon {
		fill: var(--new-tool-btn);
	}

	/* 当下拉内容显示后修改下拉按钮的背景颜色 */
	.header-dropdown:hover .icon_btn {
		background-color: var(--new-tool-list-hover-bg);
		border-color: var(--new-tool-btn-border);
	}

	.index_toolbar {
		height: 3.5em;
		width: 100%;
		top: 0;
		left: 0;
		margin: 0;
		/*text-align: center;*/
		padding: 0 10px;
		border-bottom: 1px solid var(--input-bg-color);
		background-color: var(--new-tool-bg);
		display: -webkit-none;
		display: -moz-none;
		display: none;
		-webkit-align-items: center;
		-moz-align-items: center;
		align-items: center;
		-webkit-justify-content: space-between;
		-moz-justify-content: space-between;
		justify-content: space-between;
		z-index: 2
	}

	.disable {
		color: var(--new-tool-content-disabled);
		cursor: not-allowed;
	}

	@media screen and (min-width: 840px) {
		.nav-toggle-label {
			display: none;
		}

		/*header {
			display: grid;
			grid-template-columns: 1fr auto 1fr auto 1fr;
			padding: 0 1rem;
		}*/

		header {
			display: flex;
			justify-content: space-between;
			padding: 0 1rem;
		}

		nav {
			all: unset;
			display: flex;
			align-items: center;
			height: 50px;
			text-align: left;
		}

		nav ul {
			display: flex;
		}

		nav li {
			margin: 0 0.7rem;
		}

		nav .nav_link {
			opacity: 1;
			color: var(--new-tool-btn);
			margin: 0;
			padding: 0 1em;
		}
		nav .active{
			background-color: var(--tool-bt-bg-hover-color);
		}

		.header-dropdown-content li {
			margin: 0;
		}

		.nav-mobile {
			display: none;
		}

		.more_btn {
			display: block;
		}

		.platform-toggle a {
			display:block;
		}

		.platform-toggle::before{
			display:none;
		}

		.platform-toggle:hover .goto-platform {
			cursor: pointer;
			transform: scaleX(1);
			opacity: 1;
		}

		.platform-toggle:hover a {
			color: var(--new-tool-content-disabled);
		}

		.platform-toggle:hover::after {
			transform: scaleX(1);
		}

		.platform-content {
			top: unset;
			position: relative;
			padding: 0 5px;
		}

		.platform-content::after {
			bottom: 0.5em;
			border-bottom-color: transparent;
			right: 100%;
			border-right-color: var(--new-tool-content-bg);
			all: unset;
		}

		.left-content {
			left: -20px;
			box-shadow: 0px 3px 10px 0px var(--shadow-color);
			padding: 0;
		}

		.left-content::after {
			left: 30px;
		}

		.header-dropdown-content li div {
			padding: 0.7rem;
		}

		.nav-right{
			position:relative;
			margin: 0;
		}
	}
</style>
<?php
//根据用户界面语言设置显示对应的帮助文件
$help_lang = '';
switch($_COOKIE['language']){
	case "zh-cn":
		$help_lang = "zh-Hans";
		break;
	case "zh-tw":
		$help_lang = "zh-Hans";
		break;
	default:
		$help_lang = "en-US";
}
?>
<!-- new tool bar begin-->
<header>
	<div class="head-logo">
		<svg>
			<a href="../pcdl/">
				<use xlink:href="../public/images/svg/wikipali_logo.svg#wikipali_logo"></use>
				<rect width="104" height="50" y="0" x="0" fill="rgba(0,0,0,0)" />
			</a>
		</svg>
		<div class="platform-toggle">
			<a href="../studio/" target="_blank"><?php echo $_local->gui->library; ?>
				<span class="goto-platform"> ▸ <?php echo $_local->gui->studio; ?></span></a>
		</div>
	</div>
    <?php
        $host = $_SERVER ['HTTP_HOST'];
        if(stripos('staging',$host)>0){
            echo "<span style='backgroud-color:red;color:white;padding:0.5em;font-size:120%;' title='本服务器仅仅作为功能测试之用，所有用户数据均不保留。'>测试服务器</span>";
        }
    ?>
	<input type="checkbox" id="nav-toggle" class="nav-toggle">
	<nav>
		<ul>
			<li class="nav_link" id="nav_community"><a href="../community/"><?php echo $_local->gui->latest;; ?></a></li>
			<li class="nav_link" id="nav_palicanon"><a href="../palicanon/"><?php echo $_local->gui->pali_canon; ?></a></li>
			<li class="nav_link" id="nav_course"><a href="../course/"><?php echo $_local->gui->lesson; ?></a></li>
			<li class="nav_link" id="nav_dict"><a href="../dict/"><?php echo $_local->gui->dictionary; ?></a></li>
			<li class="nav_link" id="nav_collection"><a href="../collect/"><?php echo $_local->gui->composition; ?></a></li>
			<li class="nav_link"><a href="<?php echo URL_HELP.'/'.$help_lang ?>"  target="_blank"><?php echo $_local->gui->help; ?></a></li>
			<li class="nav_link more_btn">
				<div id="more" class="dropdown" onmouseover="switchMenu(this,'nav-more')" onmouseout="hideMenu()">
					<button class="dropbtn icon_btn" style="all:unset;" onClick="switchMenu(this,'nav-more')" id="more_button">
						<?php echo $_local->gui->more; ?>
					</button>
					<ul class="header-dropdown-content left-content" style="display: none;" id="nav-more">
						<li>
							<a href="<?php echo URL_PALI_HANDBOOK.'/'.$help_lang ?>" target="_blank">
								<div class="nav_link"><?php echo $_local->gui->palihandbook; ?></div>
							</a>
						</li>
						<li>
							<a href="../calendar/index.html" target="_blank">
								<div class="nav_link"><?php echo $_local->gui->buddhist_calendar; ?></div>
							</a>
						</li>
						<li>
							<a href="../tools/unicode.html" target="_blank">
								<div class="nav_link"><?php echo $_local->gui->code_convert; ?></div>
							</a>
						</li>
						<li>
							<a href="../statistics/" target="_blank">
								<div class="nav_link"><?php echo $_local->gui->corpus_statistics; ?></div>
							</a>
						</li>
						<li>
							<a href="../calendar/history.html" target="_blank">
								<div class="nav_link"><?php echo $_local->gui->dev_history; ?></div>
							</a>
						</li>
						<li>
							<a href="../tools/kammavaca.html" target="_blank">
								<div class="nav_link"><?php echo "作持语"; ?></div>
							</a>
						</li>
					</ul>
			</li>
			<li class="nav_link nav-mobile"><a href="../calendar/index.html">
					<?php echo $_local->gui->buddhist_calendar; ?>
				</a></li>
			<li class="nav_link nav-mobile"><a href="../tools/unicode.html">
					<?php echo $_local->gui->code_convert; ?>
				</a></li>
			<li class="nav_link nav-mobile"><a href="../calendar/history.html">
					<?php echo $_local->gui->dev_history; ?>
				</a></li>
            <li class="nav_link nav-mobile"><a href="../statistics/">
                <?php echo $_local->gui->corpus_statistics; ?>
            </a></li>
			<li class="nav_link nav-mobile"><a href="../tools/kammavaca.html">
			<?php echo "作持语"; ?>
		</a></li>
			<div>
		</ul>
	</nav>
	<div class="nav-right">
		<button class="dropbtn icon_btn">
			<a href="../search/paliword.php" style="height:20px;">
				<svg t="1598275338832" class="icon" viewBox="0 0 1024 1024" style="width: 16px; height: 20px; margin:0 2px;" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="19379">
					<path d="M441.936842 824.751158c-229.052632 0-414.989474-182.810947-414.989474-408.629895C26.947368 190.356211 212.884211 7.545263 441.936842 7.545263s414.989474 182.864842 414.989474 408.629895c0 225.818947-185.936842 408.629895-414.989474 408.629895z m0-53.894737c199.518316 0 361.094737-158.881684 361.094737-354.735158 0-195.799579-161.576421-354.735158-361.094737-354.735158S80.842105 220.429474 80.842105 416.121263c0 195.853474 161.576421 354.735158 361.094737 354.735158z" p-id="19380"></path>
					<path d="M713.889684 740.513684a26.947368 26.947368 0 1 1 38.157474-38.103579l264.569263 264.784842a26.947368 26.947368 0 0 1-38.157474 38.103579l-264.569263-264.784842z" p-id="19381">
					</path>
				</svg>
			</a>
		</button>
		<?php include __DIR__."/../ucenter/user.php"; ?>
		<?php include __DIR__."/../lang/lang.php"; ?>
	</div>

	<label for="nav-toggle" class="nav-toggle-label">
		<span></span>
	</label>

</header>
<!--new tool bar end -->
