<?php
require_once '../pcdl/html_head.php';
?>

<body>
	<script language="javascript" src="../pcdl/index.js"></script>

	<style>
		.content_inner {
			display: flex;
		}

		.title_bar {
			justify-content: space-between;
			display: flex;
			margin: 1em 0;
		}

		.h3 {
			font-size: 18px;
			font-weight: 700;
		}

		/* 
		.index_list_categories {

			padding: 1rem;
		}*/
		.index_list_content {
			padding: 1rem;
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

		.collect {
			color: var(--link-color);
			font-weight: 700;
		}

		#footer_nav {
			display: none;
		}

		.index_article {
			background-color: #e0e0e0;
		}

		.index_course {
			background-color: #313131;
		}

		.index_course .h3 {
			color: white;
		}

		.article_right {
			width: calc(100% - 80px);
		}

		.course_right a,
		.article_right a {
			color: var(--main-color);
		}
		.summary{
			overflow: hidden;
			text-overflow: ellipsis;
			display: -webkit-box;
			-webkit-line-clamp: 3;
			-webkit-box-orient: vertical;
		}

		.title a,
		.course_right title {
			font-size: 120%;
			font-weight: 700;
			display: -webkit-box;
			-webkit-box-orient: vertical;
			overflow: hidden;
			text-overflow: ellipsis;
			-webkit-line-clamp: 2;
		}

		#course_list_new .card {
			height: 120px;
			padding: 0;
			display: grid;
			grid-template-columns: 120px 1fr;
		}

		.card_photo {
			object-fit: cover;
			border-radius: 20px 0 0 20px;
		}

		.course_right {
			padding: 10px;
			display: flex;
			flex-direction: column;
			width: calc(100% - 10px);
		}

		.subtitle {
			margin-top: auto;
			display: -webkit-box;
			-webkit-box-orient: vertical;
			overflow: hidden;
			text-overflow: ellipsis;
			width: 100%;
			-webkit-line-clamp: 2;
		}

		#pali_pedia .card {
			background-color: #F9F9F9;
			box-shadow: unset;
			display: grid;
			grid-template-columns: 2fr 1fr;
			grid-template-areas:
				"title author"
				"summary author";
		}

		#pali_pedia .title {
			grid-area: title;
			font-size: 120%;
		}

		#pali_pedia .summary {
			grid-area: summary;
		}

		#pali_pedia .author {
			grid-area: author;
			color: gray;
		}

		.author {
			text-overflow: ellipsis;
			overflow: hidden;
			white-space: nowrap;
		}
	</style>
	<style media="screen and (min-width:800px)">
		.index_list_content {
			display: flex;
			margin: auto;
			max-width: 900px;
		}

		.index_article .index_list_content {
			background-image: url(img/books.svg);
			background-repeat: no-repeat;
		}

		.index_course .index_list_content {
			flex-direction: row-reverse;
			background-image: url(img/teachers.svg);
			background-repeat: no-repeat;
			background-position-x: right;
		}

		.title_bar {
			flex: 4;
			position: relative;
			margin: 2em;
		}

		#article_new {
			flex: 9;
		}

		#course_list_new {
			flex: 8;
		}

		#pali_pedia .card {
			margin: 0;
		}

		#pali_pedia {
			flex: 12;
			display: grid;
			grid-template-columns: 1fr 1fr;
			grid-gap: 10px;
		}

		.title_bar .h3 {
			font-size: 250%;
		}

		.title_more {
			position: absolute;
			right: 30px;
			bottom: 0;
		}

		.index_pedia .index_list_content {
			flex-direction: column;
		}

		.index_pedia .title_bar {
			display: grid;
			justify-content: unset;
			text-align: center;
		}

		.index_pedia .title_more {
			all: unset;
		}
	</style>
	<!--
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



		.article_list {}
	</style>-->
	<a name="pagetop"></a>
	<!-- tool bar begin-->
	<?php
	require_once("head_bar.php");
	?>
	<!--tool bar end -->


	<div class="index_inner">

		<div class="index_list_categories index_article">
			<div class="index_list_content">
				<div class="title_bar">
					<span class="title h3"><?php echo $_local->gui->composition; ?></span>
					<span class="title_more"><a href="../collect"><?php echo $_local->gui->more; ?></a></span>
				</div>
				<div id="article_new">
				</div>
			</div>
		</div>

		<div class="index_list_categories index_course">
			<div class="index_list_content">
				<div class="title_bar">
					<span class="title h3"><?php echo $_local->gui->lesson; ?></span>
					<span class="title_more"><a href="../course"><?php echo $_local->gui->more; ?></a></span>
				</div>
				<div id="course_list_new">
				</div>
			</div>
		</div>



		<div class="index_list_categories index_pedia">
			<div class="index_list_content">
				<div class="title_bar">
					<span class="title h3"><?php echo $_local->gui->encyclopedia; ?></span>
					<span class="title_more"><a href="../wiki"><?php echo $_local->gui->more; ?></a></span>
				</div>
				<div id="pali_pedia">
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