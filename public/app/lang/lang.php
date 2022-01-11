<script>
	var g_language = "en";
	var g_currLink = "";

	function lang_init(strPage) {
		g_currLink = strPage;
	}

	function setLang(strLang) {
		g_language = strLang;
		setCookie('language', g_language, 365);
		if (window.location.search == "") {
			window.location.assign(location.href + "?language=" + g_language);
		} else {
			let org_parameter_str = window.location.search.substr(1);
			let arr_parameter = org_parameter_str.split("&");
			let new_parameter_str = ""
			for (let i_arr = 0; i_arr < arr_parameter.length; i_arr++) {
				if (arr_parameter[i_arr].split("=")[0] == "language") {
					arr_parameter[i_arr] = "language=" + g_language;
					new_parameter_str = arr_parameter[i_arr];
				} else {}
			}
			if (new_parameter_str == "") {
				window.location.assign(location.href + "&language=" + g_language);
			} else {
				new_parameter_str = "?" + arr_parameter.join("&");
				window.location.assign(location.pathname + new_parameter_str);
			}
		}
	}
</script>
<style>

.header-dropdown-content ul,
.header-dropdown-content li{
		list-style-type: none;
		cursor: pointer;
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

	.disable {
		color: var(--new-tool-content-disabled);
		cursor: not-allowed;
	}

	/*
	#lang_list::after {
		content: " ";
		position: absolute;
		bottom: 100%;
		right: 0;
		margin-right: 0.7em;
		border-width: 5px;
		border-style: solid;
		border-color: transparent;
		border-bottom-color: var(--tool-bg-color);
	}*/

	.icon {
		fill: var(--btn-color);
	}
</style>
<div id="lang_select" class="dropdown" onmouseover="switchMenu(this,'lang_list')" onmouseout="hideMenu()">
	<button class="dropbtn icon_btn" onClick="switchMenu(this,'lang_list')" id="lang_button">
		<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="ic_language_24px">
			<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 4.33-3.56A15.65 15.65 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z" />
		</svg>
	</button>
	<ul class="header-dropdown-content right-content" id="lang_list">
		<li>
			<a onclick="setLang('my')">
			<div class="nav_link">
				မြန်မာ
			</div>
			</a>
		</li>
		<li><a onclick="setLang('si')">
				<div class="nav_link">
					සිංහල
				</div>
			</a></li>
		<li><a onclick="setLang('en')">
				<div class="nav_link">
					English
				</div>
			</a></li>
		<li><a onclick="setLang('zh-cn')">
				<div class="nav_link">
					简体中文
				</div>
			</a></li>
		<li><a onclick="setLang('zh-tw')">
				<div class="nav_link">
					繁體中文
				</div>
			</a></li>
	</ul>
</div>