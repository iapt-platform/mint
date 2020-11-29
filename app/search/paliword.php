<?PHP
include "../pcdl/html_head.php";
?>
<body>
<link type="text/css" rel="stylesheet" href="../search/search.css"/>

<?php
	require_once("../pcdl/head_bar.php");
	require_once("../search/toobar.php");
?>
    <style>
        #dt_pali , #dt_pali_1{
            border-bottom: 2px solid var(--link-hover-color);
        }
        #index_list{
			display:flex;
		}

		.main_view{
		padding: 0 1em;
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
	}

	.fun_frame {
		border-bottom: 1px solid gray;
		margin-right: 10px;
		margin-bottom: 10px;
	}
	.fun_frame .title{
		padding:6px;
		font-weight: 700;
	}
	.fun_frame>.content{
		padding:6px;
		overflow-y: scroll;
	}
	
	.fixed{
		position:fixed;
		right: 0;
    	top: 0;
	}
	.when_right_fixed{
		padding-right:20em;
	}


	#contents_view{
		display:flex;
	}
	#contents_div{
		flex:7;
	}
	#contents{

	}
	#contents li{
		white-space: normal;
	}
	#right_pannal{
		flex:3;
		max-width:20em;
	}
	#head_bar{
		height:unset;
	}
	#contents_foot{
		margin-bottom: 70vh;
	}

	#pre_search_word_content{
		display:block;
	}
    </style>
    <style  media="screen and (max-width:800px)">
		#index_list{
			display:block;
		}
	</style>
	<script language="javascript" src="paliword.js"></script>
	<script>
		<?php
		if(isset($_GET["key"])){
			echo " _key_word = '{$_GET["key"]}';";
		}
		
		?>
	</script>

	<div id="main_view" class="main_view">

<div id="contents_view">
	<div id="contents_div" style="padding: 0 1em 0 30px;">
		<div id="contents">
		<?php echo $_local->gui->loading; ?>...
		</div>
		<div id="contents_foot">
			<div id="contents_nav" style="">
			</div>
		</div>
	</div>

	<div id="right_pannal">
		<div class="fun_frame">
			<div style="display:flex;justify-content: space-between;">
				<div class="title">Declension</div>
				<div id="case_tools">
					<div class="select_button" onclick="onWordFilterStart()"><?php echo "Select"; ?></div>
					<div class="filter">
						<button onclick='word_search_filter()'>Filtrate</button>
						<button onclick='filter_cancel()'>取消</button>
					</div>
				</div>
			</div>
			<div id = "case_content" class="content" style="max-height:20em;">
			</div>
		</div>
		<div class="fun_frame">
			<div style="display:flex;justify-content: space-between;">
				<div class="title"><?php echo "Books"; ?></div>
				<div id="book_tools">
					<div class="select_button" onclick="onBookFilterStart()"><?php echo "Select"; ?></div>
					<div class="filter">
						<button onclick='book_search_filter()'>Filtrate</button>
						<button onclick='book_filter_cancel()'>取消</button>
					</div>
				</div>
			</div>
			<div id="book_list" class="content" >
			</div>
		</div>
	</div>
</div>

</div>


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
                    <div id="guide_pali_search_index"></div>
				</div>
			</div>
	</div>
    <script>
    search_show_history();
    guide_get("pali_search_index");
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

