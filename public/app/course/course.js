function course_load(course_id) {
	$.get(
		"../course/course_get.php",
		{
			id: course_id,
		},
		function (data, status) {
			let html = "";
			let course_info = JSON.parse(data);
			if (course_info) {
				//head
				html += "<div id='course_info_head' class='course_info_block'>";
				html += "<div id='course_info_head_1'>";
				html += "<div id='course_info_head_face'>";
				html += "<img src='../../tmp/images/course/" + course_info.id + ".jpg' />";
				html += "</div>";
				html += "<div id='course_info_head_title'>";
				html += "<div id='course_title'>" + course_info.title + "</div>";
				html += "<div id='course_subtitle'>" + course_info.subtitle + "</div>";
				html += "<div id='course_button'>";
				html += "<button class='disable'>" + gLocal.gui.watch + "</button>";
				html += "<button disabled class='disable'>" + gLocal.gui.sign_up + "</button>";
				html += "</div>";
				html += "</div>";
				html += "</div>";

				html += "<div id='course_info_head_2'>";
				html += "<span><span>" + gLocal.gui.speaker + ": </span>";
				html +=
					"<a href='../uhome/course.php?userid=" +
					course_info.teacher +
					"'>" +
					course_info.teacher_info.nickname +
					"</a>";
				html += "</span>";
				//html += "<span>地区:缅甸</span>";
				html += "</div>";
				// end of course_info_head_2
				html += "</div>";
				//end of head

				//summary
				if (course_info.summary.length > 0) {
					html += "<div id='course_info_summary' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.introduction + "</h2>";
					try {
						html += marked(course_info.summary);
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of summary
				//attachment
				if (course_info.attachment.length > 0) {
					html += "<div id='course_info_attachment' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.attachment + "</h2>";
					try {
						html += marked(course_info.attachment);
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of attachment

				$("#course_info").html(html);
				$("#page_title").text(course_info.title);
				$("img").one("error", function () {
					$(this).attr("src", "../course/img/default.jpg");
				});
			}
		}
	);

	$.get(
		"../course/lesson_list.php",
		{
			id: course_id,
		},
		function (data, status) {
			let arrLesson = JSON.parse(data);
			let html = "";
			for (const lesson of arrLesson) {
				//计算课程是否已经开始
				let now = new Date();
				let class_lesson_time = "";
				let dt = lesson["duration"] / 60;
				if (now < lesson["date"]) {
					class_lesson_time = "not_started";
				} else if (now > lesson["date"] && now < lesson["date"] + dt * 1000) {
					class_lesson_time = "in_progress";
				} else {
					class_lesson_time = "already_over";
				}

				html += '<div class="lesson_card" >';
				let d = new Date();
				d.setTime(lesson["date"]);
				let strData = d.toLocaleDateString();
				let strTime = d.toLocaleTimeString();
				html += "<div class='datatime " + class_lesson_time + "'>" + strData + " " + strTime + "</div>";
				html +=
					'<div class="title" ><a href="../course/lesson.php?id=' +
					lesson["id"] +
					'" style="color:var(--main-color);">' +
					lesson["title"] +
					"</a></div>";

				/*
				let dt = lesson["duration"] / 60;
				let sdt = "";
				if (dt > 59) {
					sdt += Math.floor(dt / 60) + "小时";
				}
				let m = dt % 60;
				if (m > 0) {
					sdt += (dt % 60) + "分钟";
				}
				html += "<div >" + gLocal.gui.duration + "：" + sdt + "</div>";
				

				html += '<div ><span class="lesson_status">' + lesson_time + "</span></div>";
*/

				html += "</div>";
			}
			$("#lesson_list").html(html);
		}
	);
}
