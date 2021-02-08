var renderer = new marked.Renderer();
renderer.code = function (code, language) {
	if (language == "mermaid") return '<pre class="mermaid">' + code + "</pre>";
	else return "<pre><code>" + code + "</code></pre>";
};

function lesson_show(id) {
	$.get(
		"../course/lesson_get.php",
		{
			id: id,
		},
		function (data, status) {
			let arrLesson = JSON.parse(data);
			let html = "";
			for (const lesson of arrLesson) {
				html += '<div class="card" style="display:flex;margin:1em;padding:10px;">';

				html += '<div style="flex:7;">';
				html += '<div class="pd-10">';
				html +=
					'<div class="title" style="padding-bottom:5px;font-size:200%;font-weight:600;">' +
					lesson["title"] +
					"</div>";
				html += '<div style="">';
				let summary = "";
				try {
					summary = marked(lesson["summary"], { renderer: renderer });
				} catch {}
				html += '<div class="summary"  style="padding-bottom:5px;">' + summary + "</div>";
				let live = "";
				try {
					live = marked(lesson["live"], { renderer: renderer });
				} catch {}
				html += '<div class="summary"  style="padding-bottom:5px;">' + live + "</div>";
				let replay = "";
				try {
					replay = marked(lesson["replay"], { renderer: renderer });
				} catch {}
				html += '<div class="summary"  style="padding-bottom:5px;">' + replay + "</div>";
				let attachment = "";
				try {
					attachment = marked(lesson["attachment"], { renderer: renderer });
				} catch {}
				html += '<div class="summary"  style="padding-bottom:5px;">' + attachment + "</div>";
				html += "</div>";
				html += "</div>";
				html += "</div>";

				html += '<div style="flex:3;max-width:15em;">';
				let d = new Date();
				d.setTime(lesson["date"]);
				let strData = d.toLocaleDateString();
				let strTime = d.toLocaleTimeString();
				html += "<div >" + gLocal.gui.date + "：" + strData + "</div>";
				html += "<div >" + gLocal.gui.time + "：" + strTime + "</div>";
				let dt = lesson["duration"] / 60;
				let sdt = "";
				if (dt > 59) {
					sdt += Math.floor(dt / 60) + gLocal.gui.h;
				}
				let m = dt % 60;
				if (m > 0) {
					sdt += (dt % 60) + gLocal.gui.mins;
				}
				html += "<div >" + gLocal.gui.duration + "：" + sdt + "</div>";
				let now = new Date();

				let lesson_time = "";
				if (now < lesson["date"]) {
					lesson_time = gLocal.gui.not_started;
				} else if (now > lesson["date"] && now < lesson["date"] + dt * 1000) {
					lesson_time = gLocal.gui.in_progress;
				} else {
					lesson_time = gLocal.gui.already_over;
				}
				html += '<div ><span class="lesson_status">' + lesson_time + "</span></div>";

				html += "</div>";

				html += "</div>";
			}
			$("#lesson_list").html(html);
			mermaid.initialize();
		}
	);
}

function lesson_load(lesson_id) {
	$.get(
		"../course/lesson_get.php",
		{
			id: lesson_id,
		},
		function (data, status) {
			let html = "";
			let lesson_info = JSON.parse(data);
			if (lesson_info) {
				//head
				html += "<div id='course_info_head' class='course_info_block'>";

				html += "<div id='course_info_head_title'>";
				html += "<div id='course_title'>" + lesson_info.title + "</div>";
				html += "<div id='course_subtitle'>" + lesson_info.subtitle + "</div>";
				html += "</div>";
				html += "</div>";
				//end of head

				let d = new Date();
				d.setTime(lesson_info.date);
				let strData = d.toLocaleDateString();
				let strTime = d.toLocaleTimeString();
				let dt = lesson_info["duration"] / 60;
				let strDuration = "";
				if (dt > 59) {
					strDuration += Math.floor(dt / 60) + "小时";
				}
				let m = dt % 60;
				if (m > 0) {
					strDuration += (dt % 60) + "分钟";
				}

				html += "<div id='course_info_head_2' class='course_info_block'>";
				html += "<div class='info_item'>" + "<span>上课时间：<span>" + strData + " " + strTime + "</div>";
				html +=
					"<div class='info_item'>" + "<span>" + gLocal.gui.duration + "：<span>" + strDuration + "</div>";
				html += "<div class='info_item'>" + "<span>" + gLocal.gui.speaker + "：<span>";
				html +=
					"<a href='../uhome/course.php?userid=" +
					lesson_info.teacher +
					"'>" +
					lesson_info.teacher_info.nickname +
					"</a>";
				html += "</span>";
				//html += "<span>地区:缅甸</span>";
				html += "</div>";
				html += "</div>";
				// end of course_info_head_2

				//live
				if (lesson_info.live && lesson_info.live.length > 0) {
					html += "<div id='course_info_live' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.notice_live + "</h2>";
					try {
						html += marked(lesson_info.live);
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of live

				//replay
				if (lesson_info.replay && lesson_info.replay.length > 0) {
					html += "<div id='course_info_replay' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.record_replay + "</h2>";
					try {
						html += marked(lesson_info.replay);
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of replay

				//attachment
				if (lesson_info.attachment && lesson_info.attachment.length > 0) {
					html += "<div id='course_info_attachment' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.attachment + "</h2>";
					try {
						html += "<div class='course_info_content'>";
						html += note_init(lesson_info.attachment);
						html += "</div>";
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of attachment

				//content
				if (lesson_info.content && lesson_info.content.length > 0) {
					html += "<div id='course_info_content' class='course_info_block'>";
					html += "<h2>" + gLocal.gui.detaile + "</h2>";
					try {
						html += marked(lesson_info.content);
					} catch (e) {
						html += e.message;
					}
					html += "</div>";
				}
				//end of attachment
				$("#lesson_info").html(html);
				note_refresh_new();
				render_course_info(lesson_info.course_id);
				render_lesson_list(lesson_info.course_id, lesson_info.id);
			}
		}
	);
}
function render_course_info(course_id) {
	$.get(
		"../course/course_get.php",
		{
			id: course_id,
		},
		function (data, status) {
			let html = "";
			let course_info = JSON.parse(data);
			if (course_info) {
				let html = "";
				html += "<div id='parent_title'>";
				html += "<a href='../course/course.php?id=" + course_info.id + "'>";
				html += course_info.title;
				html += "</a>";
				html += "</div>";
				html += "<div id='course_button'>";
				html += "<button>关注</button>";
				html += "<button>报名</button>";
				html += "</div>";
				$("#course_info").html(html);
			}
		}
	);
}
function render_lesson_list(course_id, currLesson) {
	$.get(
		"../course/lesson_list.php",
		{
			id: course_id,
		},
		function (data, status) {
			let arrLesson = JSON.parse(data);
			let html = "";
			for (const lesson of arrLesson) {
				let currlessonStyle = "";
				if (lesson.id == currLesson) {
					currlessonStyle = " curr_lesson";
				} else {
					currlessonStyle = " not_curr_lesson";
				}
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

				html += '<div class="lesson_card ' + currlessonStyle + '" >';
				let d = new Date();
				d.setTime(lesson["date"]);
				let strData = d.toLocaleDateString();
				let strTime = d.toLocaleTimeString();
				html += '<div class="pd-10">';
				html += "<div class='datatime " + class_lesson_time + "'>" + strData + " " + strTime + "</div>";
				html +=
					'<div class="title" ><a href="../course/lesson.php?id=' +
					lesson["id"] +
					'">' +
					lesson["title"] +
					"</a></div>";

				html += "</div>";

				html += "</div>";
			}
			$("#lesson_list").html(html);
		}
	);
}
