<?PHP
include "../pcdl/html_head.php";
?>
<body>

<?php
	require_once("../pcdl/head_bar.php");
	require_once("../search/toobar.php");
?>
    <style>
        #dt_bold{
            border-bottom: 2px solid var(--link-hover-color);
        }
        .dict_bold_main{
            display:flex;
        }
        .parent_fix{
            height: 100%;
            position: absolute;
        }
        .kid_scroll{
            overflow: scroll;
        }
        #index_list{
			display:flex;
		}
    </style>
    <style  media="screen and (max-width:800px)">
		#index_list{
			display:block;
		}
	</style>
	<script language="javascript" src="bold.js"></script>

	<div id="dict_ref_search_result" style="background-color:white;color:black;">
	</div>
    <div id="index_list">
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
					<div>最近搜索</div>
                    <div id="search_histray"></div>
				</div>
			</div>
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
					<div>热搜</div>
                    <div id="title_hot"></div>
				</div>
			</div>
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
                    <div id="guide_bold_search_index"></div>
				</div>
			</div>
	</div>
    <script>
    search_show_history();
    guide_get("bold_search_index");
    </script>	
    <?php
    if(!empty($_GET["key"])){
        echo "<script>";
        echo "dict_pre_word_click(\"{$_GET["key"]}\")";
        echo "</script>";
    }
    ?>
<?php
include "../pcdl/html_foot.php";
?>

