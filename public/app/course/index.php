<?PHP
include "../pcdl/html_head.php";
?>

<body>

	<?php
	require_once("../pcdl/head_bar.php");
	?>
	<style>
		.padding_LR_1rem {
			padding: 0 1rem;
		}

		.content_block {
			width: 230px;
		}

		.index_inner {
			margin-left: auto;
			margin-right: auto;
		}

		.content_inner {
			display: grid;
			justify-content: center;
			grid-gap: 20px;
		}

		.collect_head_bar {
			background-color: #212121;
			height: 280px;
			padding: 30px;
			color: white;
		}

		.section_inner {
			max-width: 960px;
			margin: 0 auto;
		}

		.title_bar {
			justify-content: space-between;
			display: flex;
			margin: 1em 0;
			align-items: center;
		}

		h1 {
			font-size: 42px;
			font-weight: 700;
			margin: 0.3em 0;
		}

		h3 {
			font-size: 18px;
			margin: 0;
		}

		.index_list_categories {
			padding: 1rem;
			margin-bottom: 2rem;
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

		.card li {
			white-space: unset;
		}

		.card code {
			white-space: unset;
		}

		.teacher_photo {
			width: 230px;
			height: 178px;
			background-color: gray;
			border-radius: 8px;
		}

		.teacher_text {
			padding: 0 5px;
		}

		.teacher_text .title {
			font-size: 120%;
			font-weight: 700;
			margin: 0.5em 0;
		}

		.teacher_intro {
			display: -webkit-box;
			-webkit-box-orient: vertical;
			overflow: hidden;
			text-overflow: ellipsis;
			width: 100%;
			-webkit-line-clamp: 3;
		}

		#course_list_complete .card,
		#course_list_ongoing .card {
			height: 120px;
			padding: 0;
			display: grid;
			grid-template-columns: 120px 1fr;
		}

		.course_block {
			background-color: #f5f5f5;
			padding: 1rem;
		}

		.course_right {
			padding: 10px;
			display: flex;
			flex-direction: column;
			width: calc(100% - 10px);
		}

		.card_photo {
			object-fit: cover;
			border-radius: 20px 0 0 20px;
		}

		.course_right .title a {
			font-size: 120%;
			font-weight: 700;
			display: -webkit-box;
			-webkit-box-orient: vertical;
			overflow: hidden;
			text-overflow: ellipsis;
			-webkit-line-clamp: 2;
			color: var(--main-color);
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

		@media screen and (min-width:800px) {
			.index_list_categories {
				max-width: 760px;
				margin: 0 auto 3rem auto;
			}

			.content_inner {
				grid-template-columns: 230px 230px 230px;
			}

			h3 {
				font-size: 22px;

			}

			.course_block h3 {
				text-align: center;
				margin: 1em 0;
			}

			#course_list_ongoing,
			#course_list_complete {
				max-width: 600px;
				margin: 0 auto;
			}
		}

		@media screen and (min-width:1020px) {
			.index_list_categories {
				max-width: 1020px;
			}

			.content_inner {
				grid-template-columns: 230px 230px 230px 230px;
			}
		}
	</style>
<script src="../public/js/marked.js"></script>


	<div class="index_inner">
		<div id="course_head_bar" class="collect_head_bar">
			<div class="section_inner">
				<h1><?php echo $_local->gui->lesson ;?></h1>
				<div style="max-width:30em"><?php echo $_local->gui->lesson_intro; ?></div>
			</div>
		</div>
		<div class="index_list_categories">
			<div class="title_bar">
				<h3><?php echo $_local->gui->speaker ;?></h3>
				<span class="title_more"><a href="../course"><?php echo $_local->gui->more ?></a></span>
			</div>
			<div class="content">
				<div id="course_list_new" class="content_inner">



				</div>
			</div>
		</div>
		<script>
			$.get("../course/teacher_list.php", function(data, status) {
				let xDiv = document.getElementById("course_list_new");
				if (xDiv) {

					xDiv.innerHTML = data;
				}
			});
			/*$(".teacher_intro").each(function(){
				$(this).innerHTML=marked($(this).innerHTML);
			});*/
		</script>
		<div class="course_block">
			<h3>
				<?php echo $_local->gui->in_progress; ?>
			</h3>
			<div id="course_list_ongoing">
			</div>
		</div>

		<div class="course_block">
			<h3>
				<?php echo $_local->gui->already_over; ?>
			</h3>
			<div id="course_list_complete">
			</div>
		</div>

		<script>
			 $(document).ready(function() {
				$("#nav_course").addClass('active');
			 });

			
			$.get("../course/course_list.php", function(data, status) {
				let arrData = JSON.parse(data);
				let html_complete = "";
				let html_ongoing = "";

				for (const iterator of arrData) {
					let html = "";
					html += '<div class="card">';
					html += '<div style="height:120px;width:120px;">';
					html += '<img src="../../tmp/images/course/' + iterator.id + '.jpg" alt="cover" width="120" height="120"  class="card_photo">';
					html += '</div>';
					html += '<div class="course_right">';
					html += '<div class="title"><a href="../course/course.php?id=' + iterator.id + '">' + iterator.title + '</a></div>';

					//教师名字
					html += '<div class="author">' + gLocal.gui.speaker + '：';
					html += "<a href='../uhome/course.php?userid=" + iterator.teacher + "'>"
					html += iterator.teacher_info.nickname;
					html += "</a>";
					html += '</div>';

					html += '<div class="subtitle">' + iterator.subtitle + '</div>';

					/*let summary = "";
					try {
						summary = marked(iterator.summary);
					} catch (e) {}
					html += '<div class="summary">' + summary + '</div>';*/
					html += '</div>';
					html += '</div>';
					if (iterator.status == 40) {
						html_complete += html;
					} else if (iterator.status == 30 || iterator.status == 20) {
						html_ongoing += html;
					}
				}
				$("#course_list_complete").html(html_complete);
				$("#course_list_ongoing").html(html_ongoing);
				$("img").one("error", function() {
					$(this).attr("src", "../course/img/default.jpg");
				});
			});
		</script>

	</div>

	<?php
	include "../pcdl/html_foot.php";
	?>