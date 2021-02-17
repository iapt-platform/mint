<?PHP
require_once "../pcdl/html_head.php";
?>

<body>
    <script src="../palicanon/palicanon.js"></script>
    <script src="../term/term.js"></script>

    <?php
    require_once("../pcdl/head_bar.php");
    ?>

    <style>


        #main_tag {
            font-size: 150%;
            text-align: center;
            margin: 5em 0;
            transition: all 600ms ease;
            text-transform: capitalize;
        }

        #main_tag span {
            margin: 2px;
            padding: 2px 12px;
            font-weight: 500;
            transition-duration: 0.2s;
            cursor: pointer;
            font-size: 120%;
            border: unset;
            border-radius: 0;
            border-bottom: 2px solid var(--nocolor);
            display: inline-block;
        }

        #main_tag span:hover {
            background-color: unset;
            color: unset;
            border-color: var(--link-hover-color);
        }

        #main_tag .select {
            border-bottom: 2px solid var(--link-color);
        }

        #tag_selected {
            margin: 1em 0;
        }

        tag {
            background-color: var(--btn-color);
            margin: 2px;
            padding: 2px 12px;
            border-radius: 5px;
            border: 1px solid #fe897c;
        }

        .tag-delete {
            margin-left: 6px;
            color: #f93e3e;
            cursor: pointer;
        }

        .tag-delete:hover {
            color: red;
            font-weight: 700;
        }

        .tag_others {
            margin: 10px 0;
        }

        .canon-tag {
            background-color: #46a6d2;
            border: 0;
            border-radius: 6px;
            color: white;
            font-weight: 400;
        }

        .canon-tag:hover {
            background-color: var(--link-hover-color);
        }

        .sutta_row {
            display: grid;
            align-items: center;
            grid-template-columns: 100px 200px 100px auto;
            width: 100%;
            border-bottom: 1px solid var(--border-line-color);
        }

        .sutta_row div {
            padding: 10px;
            /*display: flex;*/
            justify-items: center;
        }

        .sutta_row:hover {
            background-color: var(--drop-bg-color);
        }

		.c_level_1 {
			padding-top: 15px;
			padding-bottom: 15px;
			background-color: var(--main-color1);
		}
		.c_level_1 .chapter_title{
			font-size:120%;
			font-weight:700;
		}
		#book_list{
			display: flex;
			flex-wrap: wrap;
		}
		.chapter_list{
			display:none;
		}
		.chapter_book{
			display:block;
		}
		.chapter_progress{
			display:block;
		}
		.parent_chapter{
			width:350px;
		}
		.parent_chapter .chapter_book,.parent_chapter .chapter_progress{
			display:none;
		}

		#select_bar {
			display: flex;
			justify-content: space-between;
		}

        @media screen and (max-width:800px) {
            .sutta_row {
                grid-template-columns: 100px 1fr 1fr;
            }

            .sutta_tag {
                grid-column: 1 / 4;
            }
        }
	</style>
	<link type="text/css" rel="stylesheet" href="../palicanon/style.css" />
	
    <script>
        var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
    </script>
    <?php
    //

    require_once "../path.php";
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
		<div><button onclick="tag_list_slide_toggle()">展开</button></div>
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