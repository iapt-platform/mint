<?PHP
require_once "../pcdl/html_head.php";
?>

<body>
    <script src="../palicanon/palicanon.js"></script>
    <script src="../term/term.js"></script>

    <?php
require_once "../pcdl/head_bar.php";
?>

	<link type="text/css" rel="stylesheet" href="../palicanon/style.css" />
	<link type="text/css" rel="stylesheet" href="../palicanon/style_mobile.css" media="screen and (max-width:800px)">


    <script>
        var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
    </script>
    <?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';
require_once '../public/function.php';
?>
    <div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>
    <div class='index_inner '>
    <div style='font-size:140%'>
    </div>
    <div id="main_tag"  style="">
    <span tag="sutta" title="sutta"></span>
    <span tag="vinaya"  title="vinaya"></span>
    <span tag="abhidhamma" title="abhidhamma"></span>
    <span tag="mūla" title="mūla"></span>
    <span tag="aṭṭhakathā" title="aṭṭhakathā"></span>
    <span tag="ṭīkā" title="ṭīkā"></span>
    <span tag="añña" title="añña"></span>
	</div>

	<div id="select_bar" >
		<div id="tag_selected"></div>
		<div>
			<button onclick="tag_list_slide_toggle(this)">
				⮝
			</button>
		</div>
	</div>
	<div>

		<div id="tag_list">
			<div level="0" class="tag_others"></div>
			<div level="1" class="tag_others"></div>
			<div level="2" class="tag_others"></div>
			<div level="3" class="tag_others"></div>
			<div level="4" class="tag_others"></div>
			<div level="5" class="tag_others"></div>
			<div level="100" class="tag_others"></div>
			<div level="8" class="tag_others"></div>
		</div>
	</div>
    </div>
    </div>

	<div class='index_inner'>

	<div id="chapter_shell" class="chapter_list" >
	<div id="list_shell_1" class="show" level="1">
		<ul id="list-1" class="grid" level="1" >
		</ul>
	</div>

	<div id="list_shell_2" level="2">
		<ul id="list-2" class="hidden" level="2"  >
		</ul>
	</div>

	<div id="list_shell_3" level="3">
		<ul id="list-3" class="hidden" level="3" >
		</ul>
	</div>

	<div id="list_shell_4" level="4">
		<ul id="list-4" class="hidden" level="4" >
		</ul>
	</div>

	<div id="list_shell_5" level="5">
		<ul id="list-5" class="hidden" level="5" >
		</ul>
	</div>

	<div id="list_shell_6" level="6">
		<ul id="list-6" class="hidden" level="6" >
		</ul>
	</div>

	<div id="list_shell_7" level="7">
		<ul id="list-7" class="hidden" level="7" >
		</ul>
	</div>

	<div id="list_shell_8" level="8">
		<ul id="list-8" class="hidden" level="8" >
		</ul>
	</div>

    </div>
    </div>

    <script>
        $(document).ready(function() {
            palicanon_onload();
        });
    </script>
    <?php
include "../pcdl/html_foot.php";
?>