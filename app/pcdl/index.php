<?php
require_once '../pcdl/html_head.php';
?>

<body>
	<script language="javascript" src="../pcdl/index.js"></script>

	<style>
		.content_block {
			flex: 0 0 auto;
			width: 25%;
			padding: 10px;
		}

		.index_inner {
			width: 960px;
			margin-left: auto;
			margin-right: auto;
		}

		.content_inner {
			display: flex;
		}

		.title_bar {
			border-bottom: solid 1px var(--tool-bt-border-line-color);
			justify-content: space-between;
			display: flex;
		}

		.h3 {
			font-size: 16px;
			font-weight: 700;
		}

		.index_list_categories {
			margin: 2em 0;
		}

		.index_list_categories a:hover {
			color: var(--tool-link-hover-color);
		}

		/*.index_list_categories a,a:link{
		color: var(--main-color);
	}*/
		.index_list_categories button {
			border: none;
		}

		.pd-10 {
			padding: 10px;
		}

		#footer_nav {
			display: none;
		}

		#article_new .card {
			height: 20vh;
			overflow-y: scroll;
		}

		#course_list_new .card {
			height: 15em;
			overflow-y: scroll;
		}

		#course_list_new .card .pd-10:first-child {
			height: 12em;
			overflow-y: scroll;
			padding-bottom: 2px;
		}

		#course_list_new .card .pd-10:last-child {
			padding-top: 2px;
		}
	</style>
	<style media="screen and (max-width:800px)">
		#right_pannal {
			display: none;
		}

		.when_right_fixed {
			padding-right: 0;
		}

		.index_toolbar {
			position: unset;
		}

		#pali_pedia {
			font-size: 200%;
			margin-top: auto;
			margin-bottom: auto;
			padding-left: 0.5em;
		}

		.content_inner {
			display: block;
		}
	</style>
	<a name="pagetop"></a>
	<!-- tool bar begin-->
	<?php
	require_once("head_bar.php");
	?>
	<!--tool bar end -->


	<div class="index_inner">

		<div class="index_list_categories">
			<div class="title_bar">
				<span class="title h3"><?php echo $_local->gui->composition; ?></span>
				<span class="title_more"><a href="../collect"><?php echo $_local->gui->more; ?></a></span>
			</div>
			<div class="content">
				<div id="article_new" class="content_inner">
				</div>
			</div>
		</div>

		<div class="index_list_categories">
			<div class="title_bar">
				<span class="title h3"><?php echo $_local->gui->lesson; ?></span>
				<span class="title_more"><a href="../course"><?php echo $_local->gui->more; ?></a></span>
			</div>
			<div class="content">
				<div id="course_list_new" class="content_inner">
				</div>
			</div>
		</div>



		<div class="index_list_categories">
			<div class="title_bar">
				<span class="title h3"><?php echo $_local->gui->encyclopedia; ?></span>
				<span class="title_more"><a href="../wiki"><?php echo $_local->gui->more; ?></a></span>
			</div>
			<div class="content">
				<div id="pali_pedia" class="content_inner">
				</div>
			</div>
		</div>


	</div>

	<script>
		$(document).ready(function() {
			index_onload();
		});
	</script>


	<?php
	include "../pcdl/html_foot.php";
	?>