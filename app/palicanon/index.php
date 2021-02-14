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
        #main_video_win iframe {
            width: 100%;
            height: 100%;
        }

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
        @media screen and (max-width:800px) {
            .sutta_row {
                grid-template-columns: 100px 1fr 1fr;
            }

            .sutta_tag {
                grid-column: 1 / 4;
            }
        }
    </style>
    <script>
        var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
    </script>
    <?php
    //

    require_once "../path.php";
    require_once "../public/_pdo.php";
    require_once '../media/function.php';
    require_once '../public/function.php';

    echo "<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>";
    echo "<div class='index_inner '>";
    echo "<div style='font-size:140%'>";
    echo "</div>";
    echo '<div id="main_tag"  style="">';
    echo '<span tag="sutta" title="sutta"></span>';
    echo '<span tag="vinaya"  title="vinaya"></span>';
    echo '<span tag="abhidhamma" title="abhidhamma"></span>';
    echo '<span tag="mūla" title="mūla"></span>';
    echo '<span tag="aṭṭhakathā" title="aṭṭhakathā"></span>';
    echo '<span tag="ṭīkā" title="ṭīkā"></span>';
    echo '<span tag="añña" title="añña"></span>';
    echo '</div>';
    echo '<div id="tag_selected"></div>';
    echo '<div level="0" class="tag_others"></div>';
    echo '<div level="1" class="tag_others"></div>';
    echo '<div level="2" class="tag_others"></div>';
    echo '<div level="3" class="tag_others"></div>';
    echo '<div level="4" class="tag_others"></div>';
    echo '<div level="5" class="tag_others"></div>';
    echo '<div level="100" class="tag_others"></div>';
    echo '<div level="8" class="tag_others"></div>';
    echo "</div>";
    echo '</div>';
    ?>
	<div class='index_inner'>
	<div id="chapter_shell" style="display:flex;" >

    <div id="book_list" class="chapter_list" list="1" >
    </div>

    <div id="chapter_list_1" class="chapter_list"  >
    </div>
    <div id="chapter_list_2" class="chapter_list" >
    </div>
    <div id="chapter_list_3" class="chapter_list" >
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